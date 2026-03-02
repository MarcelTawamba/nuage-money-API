<?php

namespace App\Services\YellowCard;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Exception;

class YellowCardService
{
    private $base_url;
    private $api_key;
    private $secret_key;
    private $channels_cache_key = 'yellowcard_channels';
    private $channels_cache_ttl = 3600; // 1 hour retention

    public function __construct()
    {
        $this->base_url = rtrim(env("YELLOWCARD_BASE_URL", "https://sandbox.api.yellowcard.io"), '/');
        $this->api_key = env("YELLOWCARD_API_KEY");
        $this->secret_key = env("YELLOWCARD_API_SECRET");
    }

    /**
     * Sanitize data for logging by masking PII
     * @param array $data
     * @return array
     */
    private function sanitizeForLogging(array $data): array
    {
        $sanitized = $data;
        
        // Fields to mask
        $piiFields = [
            'phone', 'email', 'name', 'firstName', 'lastName', 'address',
            'idNumber', 'additionalIdNumber', 'accountNumber', 'accountName',
            'dob', 'bvn', 'nin'
        ];
        
        foreach ($piiFields as $field) {
            if (isset($sanitized[$field]) && !empty($sanitized[$field])) {
                $value = $sanitized[$field];
                // Keep first 2 and last 2 characters, mask the middle
                if (strlen($value) > 4) {
                    $sanitized[$field] = substr($value, 0, 2) . '***' . substr($value, -2);
                } else {
                    $sanitized[$field] = '***';
                }
            }
        }
        
        // Recursively sanitize nested arrays
        if (isset($sanitized['sender']) && is_array($sanitized['sender'])) {
            $sanitized['sender'] = $this->sanitizeForLogging($sanitized['sender']);
        }
        if (isset($sanitized['recipient']) && is_array($sanitized['recipient'])) {
            $sanitized['recipient'] = $this->sanitizeForLogging($sanitized['recipient']);
        }
        if (isset($sanitized['destination']) && is_array($sanitized['destination'])) {
            $sanitized['destination'] = $this->sanitizeForLogging($sanitized['destination']);
        }
        if (isset($sanitized['source']) && is_array($sanitized['source'])) {
            $sanitized['source'] = $this->sanitizeForLogging($sanitized['source']);
        }
        if (isset($sanitized['bankInfo']) && is_array($sanitized['bankInfo'])) {
            $sanitized['bankInfo'] = $this->sanitizeForLogging($sanitized['bankInfo']);
        }
        
        return $sanitized;
    }

    /**
     * Generate HMAC authentication headers
     * @param string $path - Request path (e.g., "/business/channels")
     * @param string $method - HTTP method (e.g., "GET", "POST")
     * @param array|null $body - Request body for POST/PUT requests
     * @return array
     */
    private function generateAuthHeaders(string $path, string $method, ?array $body = null): array
    {
        $timestamp = gmdate('Y-m-d\TH:i:s.v\Z');
        
        // Start HMAC with timestamp, path, and method
        $message = $timestamp . $path . $method;
        
        // If body exists, hash it and append to message
        if ($body !== null && !empty($body)) {
            $bodyJson = json_encode($body);
            $bodyHash = base64_encode(hash('sha256', $bodyJson, true));
            $message .= $bodyHash;
        }
        
        // Generate HMAC signature
        $signature = base64_encode(hash_hmac('sha256', $message, $this->secret_key, true));
        
        return [
            'X-YC-Timestamp' => $timestamp,
            'Authorization' => 'YcHmacV1 ' . $this->api_key . ':' . $signature,
            'Content-Type' => 'application/json'
        ];
    }

    /**
     * Get available payment channels
     * GET /business/channels
     * @param string|null $country - Country code (e.g., 'NG', 'GH', 'KE')
     * @param bool $forceRefresh - Force refresh cache
     * @return array
     */
    public function getChannels(?string $country = null, bool $forceRefresh = false)
    {
        $cacheKey = $this->channels_cache_key . ($country ? "_$country" : '');
        
        if (!$forceRefresh && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        $path = "/business/channels";
        $params = $country ? ['country' => $country] : [];
        $endpoint = $this->base_url . $path;
        $headers = $this->generateAuthHeaders($path, 'GET');
        
        $response = Http::withHeaders($headers)->get($endpoint, $params);
        $data = $response->json();
        
        // Cache if we got a valid response (YellowCard returns channels directly)
        if (isset($data['channels']) && is_array($data['channels'])) {
            Cache::put($cacheKey, $data, $this->channels_cache_ttl);
        }
        
        return $data;
    }
    
    /**
     * Get channels suitable for widget operations
     * @param string|null $currency - Filter by currency
     * @param string|null $transactionType - Filter by transaction type (Buy/Sell)
     * @return array
     */
    public function getWidgetChannels(?string $currency = null, ?string $transactionType = null): array
    {
        $response = $this->getChannels();
        
        // Handle nested data structure
        $channelsData = $response['data'] ?? $response;
        if (!isset($channelsData['channels']) || !is_array($channelsData['channels'])) {
            return [];
        }
        
        $channels = $channelsData['channels'];
        
        return array_values(array_filter($channels, function ($channel) use ($currency, $transactionType) {
            // Must have active API status
            if (!isset($channel['apiStatus']) || $channel['apiStatus'] !== 'active') {
                return false;
            }
            
            // Must have active widget status
            if (!isset($channel['widgetStatus']) || $channel['widgetStatus'] !== 'active') {
                return false;
            }
            
            // Filter by currency if specified
            if ($currency !== null && isset($channel['currency']) && $channel['currency'] !== $currency) {
                return false;
            }
            
            // Filter by transaction type if specified
            if ($transactionType !== null) {
                $expectedRampType = $transactionType === 'Buy' ? 'deposit' : 'withdraw';
                if (!isset($channel['rampType']) || $channel['rampType'] !== $expectedRampType) {
                    return false;
                }
            }
            
            return true;
        }));
    }
    
    /**
     * Find a specific channel by ID
     * @param string $channelId
     * @return array|null
     */
    public function findChannel(string $channelId): ?array
    {
        $response = $this->getChannels();
        
        // Handle nested data structure
        $channelsData = $response['data'] ?? $response;
        if (!isset($channelsData['channels']) || !is_array($channelsData['channels'])) {
            return null;
        }
        
        foreach ($channelsData['channels'] as $channel) {
            if (isset($channel['id']) && $channel['id'] === $channelId) {
                return $channel;
            }
        }
        
        return null;
    }
    
    /**
     * Validate widget quote request parameters
     * @param array $params - Request parameters
     * @return array - ['valid' => bool, 'error' => string|null, 'channel' => array|null]
     */
    public function validateWidgetQuoteParams(array $params): array
    {
        $channelId = $params['channelId'] ?? null;
        $transactionType = $params['transactionType'] ?? null;
        $currency = $params['currency'] ?? null;
        $localAmount = $params['localAmount'] ?? null;
        $cryptoAmount = $params['cryptoAmount'] ?? null;
        
        // Find channel
        $channel = $this->findChannel($channelId);
        
        if (!$channel) {
            return [
                'valid' => false,
                'error' => 'Channel not found',
                'channel' => null
            ];
        }
        
        // Check API status
        if (!isset($channel['apiStatus']) || $channel['apiStatus'] !== 'active') {
            return [
                'valid' => false,
                'error' => 'Channel API is disabled',
                'channel' => $channel
            ];
        }
        
        // Check widget status
        if (!isset($channel['widgetStatus']) || $channel['widgetStatus'] !== 'active') {
            return [
                'valid' => false,
                'error' => 'Channel not available for widget use',
                'channel' => $channel
            ];
        }
        
        // Validate transaction type matches ramp type
        if ($transactionType) {
            $expectedRampType = $transactionType === 'Buy' ? 'deposit' : 'withdraw';
            if (isset($channel['rampType']) && $channel['rampType'] !== $expectedRampType) {
                return [
                    'valid' => false,
                    'error' => "Invalid transaction type. Channel is for '{$channel['rampType']}', but '{$transactionType}' was requested",
                    'channel' => $channel
                ];
            }
        }
        
        // Validate currency matches
        if ($currency && isset($channel['currency']) && $channel['currency'] !== $currency) {
            return [
                'valid' => false,
                'error' => "Currency mismatch. Channel accepts '{$channel['currency']}', but '{$currency}' was provided",
                'channel' => $channel
            ];
        }
        
        // Validate required parameters based on transaction type
        if ($transactionType === 'Sell' && !$cryptoAmount) {
            return [
                'valid' => false,
                'error' => 'cryptoAmount is required for Sell transactions',
                'channel' => $channel
            ];
        }
        
        if ($transactionType === 'Buy' && !$localAmount) {
            return [
                'valid' => false,
                'error' => 'localAmount is required for Buy transactions',
                'channel' => $channel
            ];
        }
        
        // Validate amount limits for Buy transactions
        if ($transactionType === 'Buy' && $localAmount) {
            $minAmount = $channel['widgetMin'] ?? $channel['min'] ?? 0;
            $maxAmount = $channel['widgetMax'] ?? $channel['max'] ?? 0;
            
            if ($minAmount > 0 && $localAmount < $minAmount) {
                return [
                    'valid' => false,
                    'error' => "Amount below minimum. Minimum: {$minAmount} {$channel['currency']}",
                    'channel' => $channel
                ];
            }
            
            if ($maxAmount > 0 && $localAmount > $maxAmount) {
                return [
                    'valid' => false,
                    'error' => "Amount exceeds maximum. Maximum: {$maxAmount} {$channel['currency']}",
                    'channel' => $channel
                ];
            }
        }
        
        // Validate amount limits for Sell transactions (using cryptoAmount)
        if ($transactionType === 'Sell' && $cryptoAmount) {
            $cryptoMin = $channel['cryptoMinLimit'] ?? null;
            $cryptoMax = $channel['cryptoMaxLimit'] ?? null;
            
            if ($cryptoMin && $cryptoAmount < $cryptoMin) {
                return [
                    'valid' => false,
                    'error' => "Crypto amount below minimum. Minimum: {$cryptoMin}",
                    'channel' => $channel
                ];
            }
            
            if ($cryptoMax && $cryptoAmount > $cryptoMax) {
                return [
                    'valid' => false,
                    'error' => "Crypto amount exceeds maximum. Maximum: {$cryptoMax}",
                    'channel' => $channel
                ];
            }
        }
        
        return [
            'valid' => true,
            'error' => null,
            'channel' => $channel
        ];
    }

    /**
     * Get exchange rates
     * GET /business/rates
     * @param string|null $currency - Optional currency code to filter rates (e.g., 'NGN', 'KES')
     * @return array
     */
    public function getExchangeRates(?string $currency = null)
    {
        $path = "/business/rates";
        $params = $currency ? ['currency' => $currency] : [];
        $endpoint = $this->base_url . $path;
        $headers = $this->generateAuthHeaders($path, 'GET');
        
        $response = Http::withHeaders($headers)->get($endpoint, $params);
        
        return $response->json();
    }

    /**
     * Get payment networks (mobile money providers, etc.)
     * GET /business/networks
     * @param string|null $country - Country code filter (optional)
     * @return array
     */
    public function getNetworks(?string $country = null)
    {
        $path = "/business/networks";
        $params = $country ? ['country' => $country] : [];
        $endpoint = $this->base_url . $path;
        $headers = $this->generateAuthHeaders($path, 'GET');
        
        $response = Http::withHeaders($headers)->get($endpoint, $params);
        
        return $response->json();
    }

    /**
     * Get account information and balances
     * GET /business/account
     * @return array
     */
    public function getAccount()
    {
        $path = "/business/account";
        $endpoint = $this->base_url . $path;
        $headers = $this->generateAuthHeaders($path, 'GET');
        
        $response = Http::withHeaders($headers)->get($endpoint);
        
        return $response->json();
    }

    /**
     * Get payments list
     * GET /business/payments
     * @param array $filters - Optional filters (status, startDate, endDate, etc.)
     * @return array
     */
    public function getPayments(array $filters = [])
    {
        $path = "/business/payments";
        $endpoint = $this->base_url . $path;
        $headers = $this->generateAuthHeaders($path, 'GET');
        
        $response = Http::withHeaders($headers)->get($endpoint, $filters);
        
        return $response->json();
    }

    /**
     * Get payment by ID
     * GET /business/payments/{id}
     * @param string $paymentId - Payment ID
     * @return array
     */
    public function getPayment(string $paymentId)
    {
        $path = "/business/payments/" . $paymentId;
        $endpoint = $this->base_url . $path;
        $headers = $this->generateAuthHeaders($path, 'GET');
        
        $response = Http::withHeaders($headers)->get($endpoint);
        $status = $response->status();
        $isSuccess = $status >= 200 && $status < 300;

        $responseData = $response->json();
        Log::info('YellowCard getPayment response', [
            'paymentId' => $paymentId,
            'status' => $status,
            'payment_status' => $responseData['status'] ?? 'unknown',
            'isSuccess' => $isSuccess
        ]);

        return [
            'success' => $isSuccess,
            'status' => $status,
            'data' => $responseData,
            'message' => $isSuccess ? 'Payment retrieved successfully' : ($response->json()['message'] ?? 'Failed to retrieve payment')
        ];
    }

    /**
     * Verify bank account details
     * POST /business/details/bank
     * @param string $accountNumber - Bank account number
     * @param string $networkId - Network ID
     * @return array
     */
    public function verifyBankDetails(string $accountNumber, string $networkId)
    {
        $path = "/business/details/bank";
        $payload = [
            'accountNumber' => $accountNumber,
            'networkId' => $networkId
        ];
        $endpoint = $this->base_url . $path;
        $headers = $this->generateAuthHeaders($path, 'POST', $payload);
        
        $response = Http::withHeaders($headers)->post($endpoint, $payload);
        
        return $response->json();
    }

    /**
     * Submit a Payment
     * POST /business/payments
     * @param array $payload - Payment details
     * @return array
     */
    public function submitPayment(array $payload)
    {
        // Fetch required data
        $channelsData = $this->getChannels();
        $networksData = $this->getNetworks();
        //$ratesData = $this->getExchangeRates();

        $channels = $channelsData['channels'] ?? [];
        $networks = $networksData['networks'] ?? [];
        //$rates = $ratesData['rates'] ?? [];

        // Filter active withdraw channels
        $activeChannels = array_filter($channels, function($c) {
            return isset($c['status']) && $c['status'] === 'active' 
                && isset($c['rampType']) && $c['rampType'] === 'withdraw';
        });

        if (empty($activeChannels)) {
            throw new Exception('No active withdrawal channels available');
        }

        // Log available channels for debugging
        Log::info('YellowCard available withdraw channels', [
            'requested_currency' => $payload['currency'] ?? null,
            'requested_country' => $payload['sender']['country'] ?? null,
            'account_type' => $payload['destination']['accountType'] ?? null,
            'channels_count' => count($activeChannels),
            'channels' => array_map(function($c) {
                return [
                    'id' => $c['id'] ?? null,
                    'channelType' => $c['channelType'] ?? null,
                    'currency' => $c['currency'] ?? null,
                    'country' => $c['country'] ?? null,
                    'name' => $c['name'] ?? null
                ];
            }, array_values($activeChannels))
        ]);

        // Get the first active channel or use provided channelId
        $selectedChannel = null;
        $requestedCurrency = $payload['currency'] ?? null;
        $senderCountry = $payload['sender']['country'] ?? null;
        
        if (isset($payload['destination']['accountType'])) {
            foreach ($activeChannels as $channel) {
                // Match by account type (bank/momo), currency, and country
                $matchesType = $channel['channelType'] === $payload['destination']['accountType'];
                $matchesCurrency = !$requestedCurrency || $channel['currency'] === $requestedCurrency;
                $matchesCountry = !$senderCountry || $channel['country'] === $senderCountry;
                
                if ($matchesType && $matchesCurrency && $matchesCountry) {
                    $selectedChannel = $channel;
                    break;
                }
            }
            
            // Special case: NGN with bank type - try P2P channel as alternative
            if (!$selectedChannel && $requestedCurrency === 'NGN' && $payload['destination']['accountType'] === 'bank') {
                foreach ($activeChannels as $channel) {
                    if ($channel['channelType'] === 'p2p' && $channel['currency'] === 'NGN' && $channel['country'] === 'NG') {
                        $selectedChannel = $channel;
                        Log::info('YellowCard: Using NGN P2P channel for bank withdrawal (no NGN bank channel available)');
                        break;
                    }
                }
            }
            
            // Fallback: try matching just by account type and currency (ignore country)
            if (!$selectedChannel) {
                foreach ($activeChannels as $channel) {
                    $matchesType = $channel['channelType'] === $payload['destination']['accountType'];
                    $matchesCurrency = !$requestedCurrency || $channel['currency'] === $requestedCurrency;
                    
                    if ($matchesType && $matchesCurrency) {
                        $selectedChannel = $channel;
                        Log::warning('YellowCard: Using fallback channel (country mismatch)', [
                            'requested_country' => $senderCountry,
                            'channel_country' => $channel['country'] ?? null
                        ]);
                        break;
                    }
                }
            }
            
            // Final fallback: use first channel matching account type only
            if (!$selectedChannel) {
                foreach ($activeChannels as $channel) {
                    if ($channel['channelType'] === $payload['destination']['accountType']) {
                        $selectedChannel = $channel;
                        Log::warning('YellowCard: Using basic fallback channel (type match only)');
                        break;
                    }
                }
            }
            
            if (!$selectedChannel) {
                throw new Exception("No matching channel found for account type: {$payload['destination']['accountType']}, currency: {$requestedCurrency}, country: {$senderCountry}");
            }
        } else {
            $selectedChannel = reset($activeChannels);
        }

        // Filter active networks that support this channel
        $supportedNetworks = array_filter($networks, function($n) use ($selectedChannel) {
            return isset($n['status']) && $n['status'] === 'active' 
                && isset($n['channelIds']) && in_array($selectedChannel['id'], $n['channelIds']);
        });

        if (empty($supportedNetworks)) {
            throw new Exception('No supported networks available for selected channel' . $selectedChannel['id'] ?? '');
        }

        // Try to find a network matching the channel's country/currency
        $selectedNetwork = null;
        $channelCountry = $selectedChannel['country'] ?? null;
        $channelCurrency = $selectedChannel['currency'] ?? null;
        
        // First priority: network matching both country and currency
        foreach ($supportedNetworks as $network) {
            $networkCountry = $network['country'] ?? null;
            if ($channelCountry && $networkCountry === $channelCountry) {
                $selectedNetwork = $network;
                break;
            }
        }
        
        // Fallback: exclude "ALL" country networks and pick first specific country network
        if (!$selectedNetwork) {
            foreach ($supportedNetworks as $network) {
                $networkCountry = $network['country'] ?? null;
                if ($networkCountry && $networkCountry !== 'ALL') {
                    $selectedNetwork = $network;
                    Log::warning('YellowCard: Using fallback network (different country)', [
                        'channel_country' => $channelCountry,
                        'network_country' => $networkCountry
                    ]);
                    break;
                }
            }
        }
        
        // Last resort: use first network (even if "ALL")
        if (!$selectedNetwork) {
            $selectedNetwork = reset($supportedNetworks);
            Log::warning('YellowCard: Using generic network (no country-specific match found)');
        }

        // Log network details for debugging
        Log::info('YellowCard selected network', [
            'networkId' => $selectedNetwork['id'] ?? null,
            'code' => $selectedNetwork['code'] ?? null,
            'accountNumberType' => $selectedNetwork['accountNumberType'] ?? null,
            'type' => $selectedNetwork['type'] ?? null,
            'country' => $selectedNetwork['country'] ?? null,
            'name' => $selectedNetwork['name'] ?? null
        ]);

        // Determine accountType - must be exactly 'bank' or 'momo'
        $accountType = 'bank'; // Default
        $networkType = strtolower($selectedNetwork['accountNumberType'] ?? '');
        
        if ($networkType === 'momo' || $networkType === 'mobilemoney' || 
            stripos($selectedNetwork['name'] ?? '', 'mobile money') !== false ||
            stripos($selectedNetwork['type'] ?? '', 'mobile') !== false) {
            $accountType = 'momo';
        }

        // Build destination object per API requirements
        // Required: accountNumber, accountType, networkId, accountName
        $destination = [
            'accountNumber' => $payload['destination']['accountNumber'],
            'accountType' => $payload['destination']['accountType'] ?? $accountType,
            'networkId' => $selectedNetwork['id']
        ];
        
        // Hardcode networkId in Sandbox environment for testing (similar to collections logic)
        if (strpos($this->base_url, 'sandbox') !== false) {
            // Use a known working Nigeria bank network for NGN payouts
            $destination['networkId'] = '3d4d08c1-4811-4fee-9349-a302328e55c1'; // Stanbic Ibtc Bank
            Log::info('YellowCard: Using hardcoded networkId for sandbox payout');
        }

        // Add optional fields if available
        if (isset($selectedNetwork['code'])) {
            $destination['accountBank'] = $selectedNetwork['code'];
        }
        if (isset($selectedNetwork['name'])) {
            $destination['networkName'] = $selectedNetwork['name'];
        }
        // Only add country if it's a specific country (not "ALL")
        if (isset($selectedNetwork['country']) && $selectedNetwork['country'] !== 'ALL') {
            $destination['country'] = $selectedNetwork['country'];
        }

        // Add optional destination fields from payload if provided
        if (isset($payload['destination']['phoneNumber'])) {
            $destination['phoneNumber'] = $payload['destination']['phoneNumber'];
        }

        // Verify bank details to get account name if not provided
        if (!isset($payload['destination']['accountName'])) {
            // Skip verification in sandbox - use a test name
            if (strpos($this->base_url, 'sandbox') !== false) {
                $destination['accountName'] = 'Test Account Holder';
                Log::info('YellowCard: Using test account name for sandbox');
            } else {
                $verificationResult = $this->verifyBankDetails(
                    $destination['accountNumber'],
                    $destination['networkId']
                );
                
                if (isset($verificationResult['accountName'])) {
                    $destination['accountName'] = $verificationResult['accountName'];
                } else {
                    throw new Exception('Failed to verify bank account details');
                }
            }
        } else {
            $destination['accountName'] = $payload['destination']['accountName'];
        }

        // Process and validate sender data (similar to collection recipient validation)
        $senderData = $payload['sender'];
        
        // Format DOB to mm/dd/yyyy if needed
        if (isset($senderData['dob'])) {
            $dob = $senderData['dob'];
            $timestamp = strtotime($dob);
            if ($timestamp) {
                $senderData['dob'] = date('m/d/Y', $timestamp);
            }
        }
        
        // Validate Nigeria KYC requirements
        $senderCountry = strtoupper($senderData['country'] ?? '');
        if ($senderCountry === 'NG') {
            // Ensure both NIN and BVN are provided
            if (empty($senderData['idType']) || empty($senderData['additionalIdType'])) {
                throw new Exception('For Nigeria payments: Both idType and additionalIdType are required (NIN and BVN)');
            }
            
            // Ensure they are different
            if ($senderData['idType'] === $senderData['additionalIdType']) {
                throw new Exception('For Nigeria payments: idType and additionalIdType must be different (one NIN, one BVN)');
            }
            
            // Ensure we have both NIN and BVN
            $hasNIN = $senderData['idType'] === 'NIN' || $senderData['additionalIdType'] === 'NIN';
            $hasBVN = $senderData['idType'] === 'BVN' || $senderData['additionalIdType'] === 'BVN';
            
            if (!$hasNIN || !$hasBVN) {
                throw new Exception('For Nigeria payments: You must provide both NIN and BVN (one as idType, one as additionalIdType)');
            }
            
            // Ensure additionalIdNumber is provided
            if (empty($senderData['additionalIdNumber'])) {
                throw new Exception('For Nigeria payments: additionalIdNumber is required');
            }
        }

        // Build final payment request
        $request = [
            'sequenceId' => Str::uuid()->toString(),
            'channelId' => $selectedChannel['id'],
            'currency' => $payload['currency'] ?? $selectedChannel['currency'],
            'country' => $senderData['country'] ?? $selectedChannel['country'],
            'reason' => $payload['reason'],
            'destination' => $destination,
            'sender' => $senderData,
            'forceAccept' => $payload['forceAccept'] ?? true,
            'customerType' => $payload['customerType'] ?? 'retail',
            'customerUID' => (string) $payload['customerUID']
        ];
        
        // Override channelId for NGN in sandbox (use P2P channel since no bank channel exists)
        // DISABLED: Using dynamically selected channel instead
        // if (strpos($this->base_url, 'sandbox') !== false && $request['currency'] === 'NGN') {
        //     $request['channelId'] = 'fe8f4989-3bf6-41ca-9621-ffe2bc127569';
        //     Log::info('YellowCard: Using hardcoded NGN P2P channel for sandbox');
        // }

        // Add amount (either USD or local currency) - one is required
        if (isset($payload['amount'])) {
            $request['amount'] = (int) $payload['amount'];
        } elseif (isset($payload['localAmount'])) {
            $request['localAmount'] = (int) $payload['localAmount'];
        } else {
            throw new Exception('Either amount or localAmount is required');
        }
        
        // Remove null values from the request (YellowCard API rejects null values)
        $request = $this->removeNullValues($request);

        $path = "/business/payments";
        $endpoint = $this->base_url . $path;
        $headers = $this->generateAuthHeaders($path, 'POST', $request);
        
        Log::info('YellowCard submitPayment request', [
            'sequence_id' => $request['sequenceId'] ?? 'unknown',
            'currency' => $request['currency'] ?? 'unknown',
            'amount' => $request['localAmount'] ?? $request['amount'] ?? 'unknown'
        ]);

        $response = Http::withHeaders($headers)->post($endpoint, $request);
        $status = $response->status();
        $isSuccess = $status >= 200 && $status < 300;
        
        $responseData = $response->json();
        Log::info('YellowCard submitPayment response', [
            'status' => $status,
            'payment_id' => $responseData['id'] ?? 'unknown',
            'payment_status' => $responseData['status'] ?? 'unknown',
            'isSuccess' => $isSuccess
        ]);

        return [
            'success' => $isSuccess,
            'status' => $status,
            'data' => $responseData,
            'message' => $isSuccess ? 'Payment request submitted successfully' : ($response->json()['message'] ?? 'Failed to create payment')
        ];
    }

    /**
     * Get the estimated network fee for sending the specified stablecoin
     * POST /custody/sends/fee
     * @param string $token - Token/network identifier (e.g., 'USDT_TRC20', 'USDC_SOL')
     * @return array
     */
    public function getNetworkFee(string $token)
    {
        $path = "/custody/sends/fee";
        $payload = ['token' => $token];
        $endpoint = $this->base_url . $path;
        $headers = $this->generateAuthHeaders($path, 'POST', $payload);
        
        $response = Http::withHeaders($headers)->post($endpoint, $payload);
        
        return $response->json();
    }

    /**
     * Get widget quote for crypto transactions
     * POST /business/widget/quote
     * @param array $params - Quote parameters (currency, localAmount, cryptoAmount)
     * @param bool $skipValidation - Skip pre-validation (for testing/debugging)
     * @return array
     */
    public function getWidgetQuote(array $params = [], bool $skipValidation = false)
    {
        // Pre-validate parameters if not skipped
        if (!$skipValidation) {
            $validation = $this->validateWidgetQuoteParams($params);
            
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'data' => [
                        'code' => 'ValidationError',
                        'message' => $validation['error']
                    ]
                ];
            }
        }
        
        $path = "/business/widget/quote";
        $endpoint = $this->base_url . $path;
        $headers = $this->generateAuthHeaders($path, 'POST', $params);
        
        $response = Http::withHeaders($headers)->post($endpoint, $params);
        
        return $response->json();
    }

    /**
     * Get collection by ID
     * GET /business/collections/{id}
     * @param string $collectionId - Collection ID
     * @return array
     */
    public function getCollection(string $collectionId)
    {
        $path = "/business/collections/" . $collectionId;
        $endpoint = $this->base_url . $path;
        $headers = $this->generateAuthHeaders($path, 'GET');
        
        $response = Http::withHeaders($headers)->get($endpoint);
        $status = $response->status();
        $isSuccess = $status >= 200 && $status < 300;

        $responseData = $response->json();
        Log::info('YellowCard getCollection response', [
            'collectionId' => $collectionId,
            'status' => $status,
            'collection_status' => $responseData['status'] ?? 'unknown',
            'isSuccess' => $isSuccess
        ]);

        return [
            'success' => $isSuccess,
            'status' => $status,
            'data' => $response->json(),
            'message' => $isSuccess ? 'Collection retrieved successfully' : ($response->json()['message'] ?? 'Failed to retrieve collection')
        ];
    }

    /**
     * Submit Collection Request - Collect payments FROM customers (deposits/pay-ins)
     * POST /business/collections
     * @param array $payload - Collection request details
     * @return array
     */
    public function submitCollection(array $payload)
    {
        // Fetch required data
        $channelsData = $this->getChannels();
        $networksData = $this->getNetworks();

        $channels = $channelsData['channels'] ?? [];
        $networks = $networksData['networks'] ?? [];

        // Log raw data for debugging
        Log::info('YellowCard collection raw data', [
            'channels_count' => count($channels),
            'networks_count' => count($networks),
            'provided_networkId' => $payload['source']['networkId'] ?? null
        ]);

        // Filter active deposit channels (for collecting payments)
        $activeChannels = array_filter($channels, function($c) {
            return isset($c['status']) && $c['status'] === 'active' 
                && isset($c['rampType']) && $c['rampType'] === 'deposit';
        });

        if (empty($activeChannels)) {
            throw new Exception('No active deposit channels available');
        }

        $selectedChannel = null;
        $selectedNetwork = null;

        // If networkId is provided, find the network first, then find channels that support it
        if (isset($payload['source']['networkId'])) {
            $networkId = $payload['source']['networkId'];
            
            // Find the network
            foreach ($networks as $network) {
                if (isset($network['id']) && $network['id'] === $networkId && $network['status'] === 'active') {
                    $selectedNetwork = $network;
                    break;
                }
            }
            
            if (!$selectedNetwork) {
                throw new Exception("Network with ID '{$networkId}' not found or inactive");
            }
            
            // Find channels that support this network
            $networkChannelIds = $selectedNetwork['channelIds'] ?? [];
            
            foreach ($activeChannels as $channel) {
                if (in_array($channel['id'], $networkChannelIds)) {
                    $selectedChannel = $channel;
                    break;
                }
            }
            
            if (!$selectedChannel) {
                throw new Exception("No active deposit channel found for network '{$selectedNetwork['name']}' (ID: {$networkId})");
            }
        } 
        // If channelId is provided, use it and find a matching network
        elseif (isset($payload['channelId'])) {
            foreach ($activeChannels as $channel) {
                if ($channel['id'] === $payload['channelId']) {
                    $selectedChannel = $channel;
                    break;
                }
            }
            
            if (!$selectedChannel) {
                throw new Exception("Channel with ID '{$payload['channelId']}' not found or inactive");
            }
            
            // For bank channels, try to find a network (may be required in sandbox)
            if ($selectedChannel['channelType'] === 'bank') {
                Log::info('Looking for bank network', [
                    'channelId' => $selectedChannel['id'],
                    'totalNetworks' => count($networks)
                ]);
                
                // Try to find a network for this bank channel
                foreach ($networks as $network) {
                    Log::info('Checking network', [
                        'networkId' => $network['id'] ?? 'NO_ID',
                        'networkName' => $network['name'] ?? 'NO_NAME',
                        'networkStatus' => $network['status'] ?? 'NO_STATUS',
                        'hasChannelIds' => isset($network['channelIds']),
                        'channelIdsCount' => count($network['channelIds'] ?? [])
                    ]);
                    
                    if (isset($network['status']) && $network['status'] === 'active') {
                        $networkChannelIds = $network['channelIds'] ?? [];
                        
                        Log::info('Active network - checking channel match', [
                            'lookingFor' => $selectedChannel['id'],
                            'inArray' => in_array($selectedChannel['id'], $networkChannelIds),
                            'totalChannelIds' => count($networkChannelIds),
                            'first10ChannelIds' => array_slice($networkChannelIds, 0, 10)
                        ]);
                        
                        if (in_array($selectedChannel['id'], $networkChannelIds)) {
                            $selectedNetwork = $network;
                            Log::info('Bank network found', [
                                'networkId' => $network['id'],
                                'networkName' => $network['name']
                            ]);
                            break;
                        }
                    }
                }
                
                if (!$selectedNetwork) {
                    Log::info('No network found for bank channel, proceeding without network', ['channelId' => $selectedChannel['id']]);
                }
            } else {
                // Find a network that supports this channel (required for non-bank channels)
                foreach ($networks as $network) {
                    if (isset($network['status']) && $network['status'] === 'active') {
                        $networkChannelIds = $network['channelIds'] ?? [];
                        if (in_array($selectedChannel['id'], $networkChannelIds)) {
                            $selectedNetwork = $network;
                            break;
                        }
                    }
                }
                
                if (!$selectedNetwork) {
                    throw new Exception("No active network found for channel '{$selectedChannel['channelType']}'");
                }
            }
        }
        // Auto-select: Find first channel that has a supporting network
        else {
            foreach ($activeChannels as $channel) {
                foreach ($networks as $network) {
                    if (isset($network['status']) && $network['status'] === 'active') {
                        $networkChannelIds = $network['channelIds'] ?? [];
                        if (in_array($channel['id'], $networkChannelIds)) {
                            $selectedChannel = $channel;
                            $selectedNetwork = $network;
                            break 2; // Break out of both loops
                        }
                    }
                }
            }
            
            if (!$selectedChannel || !$selectedNetwork) {
                throw new Exception('No valid channel-network pair found for collections');
            }
        }

        Log::info('YellowCard selected channel and network', [
            'channelId' => $selectedChannel['id'] ?? null,
            'channelType' => $selectedChannel['channelType'] ?? null,
            'country' => $selectedChannel['country'] ?? null,
            'currency' => $selectedChannel['currency'] ?? null,
            'networkId' => $selectedNetwork['id'] ?? null,
            'networkName' => $selectedNetwork['name'] ?? 'N/A (bank channel)',
            'networkCode' => $selectedNetwork['code'] ?? null
        ]);

        // Determine accountType - must be exactly 'bank' or 'momo' for YellowCard API
        $providedType = strtolower($payload['source']['accountType'] ?? '');
        $accountType = 'bank'; // Default
        
        // 1. If user explicitly provided a supported type, use it
        if ($providedType === 'momo' || $providedType === 'bank') {
            $accountType = $providedType;
        } 
        // 2. else, try to detect from the selected network
        else {
            if ($selectedNetwork) {
                $networkType = strtolower($selectedNetwork['accountNumberType'] ?? '');
                if ($networkType === 'momo' || $networkType === 'mobilemoney' || 
                    stripos($selectedNetwork['name'] ?? '', 'mobile money') !== false) {
                    $accountType = 'momo';
                }
            }
        }

        // Build source object (where money comes FROM)
        $source = [
            'accountType' => $accountType
        ];
        
        // Add networkId only if network is selected (required for mobile money, optional for bank)
        if ($selectedNetwork && isset($selectedNetwork['id'])) {
            $source['networkId'] = $selectedNetwork['id'];
        }

        // Add accountNumber if provided and not empty (optional for bank collections in production)
        if (!empty($payload['source']['accountNumber'])) {
            $source['accountNumber'] = $payload['source']['accountNumber'];
        }
        
        // Hardcode networkId to a specific value in Sandbox environment for testing
        if (strpos($this->base_url, 'sandbox') !== false) {
            $source['networkId'] = '20823163-f55c-4fa5-8cdb-d59c5289a137';
        }
        
        // Remove any null or empty values from source
        $source = array_filter($source, function($value) {
            return $value !== null && $value !== '';
        });

        // Build final collection request
        $recipientData = $payload['recipient'];

        // Handle split name: concatenate firstName and lastName into name for the API
        if (isset($recipientData['firstName']) || isset($recipientData['lastName'])) {
            $firstName = $recipientData['firstName'] ?? '';
            $lastName = $recipientData['lastName'] ?? '';
            $recipientData['name'] = trim($firstName . ' ' . $lastName);
            unset($recipientData['firstName'], $recipientData['lastName']);
        }
        
        // Format date from yyyy-mm-dd to mm/dd/yyyy if needed
        if (isset($recipientData['dob'])) {
            $dob = $recipientData['dob'];
            // Ensure it is in mm/dd/yyyy format
            $timestamp = strtotime($dob);
            if ($timestamp) {
                $recipientData['dob'] = date('m/d/Y', $timestamp);
            }
        }
        
        // Remove additional ID fields if not Nigeria (only Nigeria requires them)
        // Using case-insensitive check to be safe
        $country = strtoupper($recipientData['country'] ?? '');
        if ($country !== 'NG') {
            unset($recipientData['additionalIdType'], $recipientData['additionalIdNumber']);
        } else {
            // For Nigeria, ensure both NIN and BVN are provided
            if (empty($recipientData['idType']) || empty($recipientData['additionalIdType'])) {
                throw new Exception('For Nigeria: Both idType and additionalIdType are required (NIN and BVN)');
            }
            
            // Ensure they are different
            if ($recipientData['idType'] === $recipientData['additionalIdType']) {
                throw new Exception('For Nigeria: idType and additionalIdType must be different (one NIN, one BVN)');
            }
            
            // Ensure we have both NIN and BVN
            $hasNIN = $recipientData['idType'] === 'NIN' || $recipientData['additionalIdType'] === 'NIN';
            $hasBVN = $recipientData['idType'] === 'BVN' || $recipientData['additionalIdType'] === 'BVN';
            
            if (!$hasNIN || !$hasBVN) {
                throw new Exception('For Nigeria: You must provide both NIN and BVN (one as idType, one as additionalIdType)');
            }
            
            // Ensure additionalIdNumber is provided
            if (empty($recipientData['additionalIdNumber'])) {
                throw new Exception('For Nigeria: additionalIdNumber is required');
            }
        }
        
        // Also remove business fields if customerType is retail
        if (($payload['customerType'] ?? 'retail') === 'retail') {
            unset($recipientData['businessName'], $recipientData['businessId']);
        }
        
        Log::info('YellowCard - Recipient before internal filter', [
            'country_detected' => $country,
            'has_additionalIdType' => isset($recipientData['additionalIdType']),
            'has_additionalIdNumber' => isset($recipientData['additionalIdNumber']),
            'idType' => $recipientData['idType'] ?? 'MISSING',
            'recipient_raw' => $recipientData
        ]);
        
        // Filter recipient data - only include fields with actual values
        // Required fields for retail: name, email, phone, country, dob, address, idType, idNumber
        $requiredFields = ['name', 'email', 'phone', 'country', 'dob', 'address', 'idType', 'idNumber'];
        $filteredRecipient = [];
        
        foreach ($recipientData as $key => $value) {
            // Only include fields that have actual non-null, non-empty values
            // This prevents sending "phone": null to YellowCard
            if ($value !== null && $value !== '') {
                $filteredRecipient[$key] = $value;
            }
        }
        
        
        $request = [
            'sequenceId' => $payload['sequenceId'] ?? Str::uuid()->toString(),
            'channelId' => $selectedChannel['id'],
            'source' => $source,
            'recipient' => $filteredRecipient,
            'forceAccept' => true, // Set to true for sandbox testing
            'customerType' => $payload['customerType'] ?? 'retail',
            'customerUID' => (strpos($this->base_url, 'sandbox') !== false) 
                ? ($payload['customerUID'] . '_' . uniqid()) 
                : (string) $payload['customerUID']
        ];
        
        Log::info('YellowCard - Recipient after filter', [
            'recipient_filtered' => $filteredRecipient,
            'has_phone_after_filter' => isset($filteredRecipient['phone']),
            'phone_after_filter' => $filteredRecipient['phone'] ?? 'MISSING'
        ]);

        // Add amount (either USD or local currency) - one is required
        Log::info('YellowCard - Amount debug in service', [
            'has_amount' => isset($payload['amount']),
            'amount_value' => $payload['amount'] ?? 'NOT SET',
            'has_localAmount' => isset($payload['localAmount']),
            'localAmount_value' => $payload['localAmount'] ?? 'NOT SET'
        ]);
        
        if (isset($payload['amount']) && $payload['amount'] !== null) {
            $request['amount'] = (int) $payload['amount'];
        } elseif (isset($payload['localAmount']) && $payload['localAmount'] !== null) {
            $request['localAmount'] = (int) $payload['localAmount'];
        } else {
            throw new Exception('Either amount or localAmount is required');
        }

        // Add redirectUrl when forceAccept is true (required by YellowCard)
        if (($payload['forceAccept'] ?? true) && isset($payload['redirectUrl']) && !empty($payload['redirectUrl'])) {
            $request['redirectUrl'] = $payload['redirectUrl'];
        }

        $path = "/business/collections";
        $endpoint = $this->base_url . $path;
        $headers = $this->generateAuthHeaders($path, 'POST', $request);
        
        Log::info('YellowCard submitCollection request', [
            'sequenceId' => $request['sequenceId'],
            'channelId' => $request['channelId'],
            'customerUID' => $request['customerUID'],
            'amount' => $request['amount'] ?? null,
            'localAmount' => $request['localAmount'] ?? null,
            'recipient' => $request['recipient'],
            'source' => $request['source']
        ]);
        
        Log::info('YellowCard - FULL request object being sent', $request);

        $response = Http::withHeaders($headers)->post($endpoint, $request);
        $status = $response->status();
        $isSuccess = $status >= 200 && $status < 300;

        Log::info('YellowCard submitCollection response', [
            'status' => $status,
            'body' => $response->json(),
            'isSuccess' => $isSuccess
        ]);

        return [
            'success' => $isSuccess,
            'status' => $status,
            'data' => $response->json(),
            'message' => $isSuccess ? 'Collection request submitted successfully' : ($response->json()['message'] ?? 'Failed to submit collection request')
        ];
    }
    
    /**
     * Recursively remove null values from array
     * YellowCard API rejects null values even for optional fields
     */
    private function removeNullValues(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->removeNullValues($value);
            } elseif (is_null($value)) {
                unset($data[$key]);
            }
        }
        return $data;
    }
}
