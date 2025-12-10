<?php

namespace App\Services\VALR;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ValrService
{
    private $base_url;

    private $api_key;

    private $api_secret;

    public function __construct()
    {
        $this->base_url = env("VALR_BASE_URL", "https://api.valr.com");
        $this->api_key = env("VALR_API_KEY");
        $this->api_secret = env("VALR_API_SECRET");
    }

    /**
     * Generate VALR API signature
     * @param int $timestamp
     * @param string $verb - HTTP method (GET, POST, DELETE, etc.)
     * @param string $path - API path without base URL
     * @param string $body - Request body (empty string for GET requests)
     * @param string|null $subaccountId - Optional subaccount ID for impersonation
     * @return string
     */
    private function generateSignature(
        int $timestamp, 
        string $verb, 
        string $path, 
        string $body = "", 
        ?string $subaccountId = null)
    {
        $payload = $timestamp . strtoupper($verb) . $path . $body;
        if ($subaccountId) {
            $payload .= $subaccountId;
        }
        return hash_hmac('sha512', $payload, $this->api_secret);
    }

    /**
     * @param string $verb - HTTP method
     * @param string $path - API path without base URL
     * @param array $body - Request body data
     * @param string|null $subaccountId - Optional subaccount ID for impersonation
     * @return \Illuminate\Http\Client\PendingRequest
     */
    private function http(
        string $verb, 
        string $path, 
        array $body = [], 
        ?string $subaccountId = null)
    {
        $timestamp = intval(microtime(true) * 1000);
        $bodyString = empty($body) ? "" : json_encode($body);
        $signature = $this->generateSignature($timestamp, $verb, $path, $bodyString, $subaccountId);

        $headers = [
            'Content-Type' => 'application/json',
            'X-VALR-API-KEY' => $this->api_key,
            'X-VALR-SIGNATURE' => $signature,
            'X-VALR-TIMESTAMP' => $timestamp
        ];

        if ($subaccountId) {
            $headers['X-VALR-SUBACCOUNT-ID'] = $subaccountId;
        }

        return Http::withHeaders($headers);
    }

    /**
     * Get account balances
     * @param array $currencies - Optional list of currencies to filter by (e.g., ['USDT', 'USDC', 'ZAR'])
     * @return array
     */
    public function getBalances(array $currencies = [])
    {
        $path = "/v1/account/balances";
        $response = $this->http('GET', $path)->get($this->base_url . $path)->json();
        
        // If no currencies specified, return full response
        if (empty($currencies)) {
            return $response;
        }
        
        // Filter the data array to only include specified currencies
        if (isset($response['data']) && is_array($response['data'])) {
            $response['data'] = array_filter($response['data'], function($balance) use ($currencies) {
                return in_array($balance['currency'], $currencies);
            });
            
            // Re-index the array to ensure it's a proper sequential array
            $response['data'] = array_values($response['data']);
        }
        
        return $response;
    }

    /**
     * Get market data for all pairs
     * @return array
     */
    public function getMarketData(string $currencyPair)
    {
        $path = "/v1/marketdata/{$currencyPair}/orderBook/full";
        return $this->http('GET', $path)->get($this->base_url . $path)->json();
    }

    /**
     * Place a limit order
     * @param string $side - BUY or SELL
     * @param float $quantity
     * @param float|null $price - Optional price (if null, will use market price)
     * @param string $pair - Trading pair e.g., BTCZAR
     * @param string $postOnly - true or false
     * @param string|null $customerOrderId - Optional custom order ID
     * @return array
     */
    public function placeLimitOrder(
        string $side,
        float $quantity,
        ?float $price,
        string $pair,
        string $postOnly = "false",
        ?string $customerOrderId = null
    ): array {
        $path = "/v1/orders/limit";
        $data = [
            'side' => $side,
            'quantity' => $quantity,
            'price' => $price,
            'pair' => $pair,
            'postOnly' => $postOnly
        ];

        if ($customerOrderId) {
            $data['customerOrderId'] = $customerOrderId;
        }

        $response = $this->http('POST', $path, $data)->post($this->base_url . $path, $data);
        
        // Check if the request was successful
        if (!$response->successful()) {
            Log::error('VALR API Error - Place Limit Order failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception('Failed to place limit order: ' . $response->body());
        }

        $orderId = $response->json()['id'];
        
        // Validate that we received an order ID
        if (empty($orderId)) {
            Log::error('VALR API Error - No order ID received', [
                'response' => $response->body()
            ]);
            throw new \Exception('No order ID received from VALR API');
        }

        return $this->getOrderStatus($pair, $orderId);
    }

    /**
     * Place a market order
     * @param string $side - BUY or SELL
     * @param float|null $baseAmount - Amount in base currency (for BUY)
     * @param float|null $quoteAmount - Amount in quote currency (for SELL)
     * @param string $pair - Trading pair e.g., BTCZAR
     * @param string|null $customerOrderId - Optional custom order ID
     * @return array
     */
    public function placeMarketOrder(
        string $side,
        string $pair,
        ?float $baseAmount = null,
        ?float $quoteAmount = null,
        ?string $customerOrderId = null
    ) {
        $path = "/v1/orders/market";
        $data = [
            'side' => $side,
            'pair' => $pair
        ];

        if ($baseAmount) {
            $data['baseAmount'] = $baseAmount;
        }

        if ($quoteAmount) {
            $data['quoteAmount'] = $quoteAmount;
        }

        if ($customerOrderId) {
            $data['customerOrderId'] = $customerOrderId;
        }

        return $this->http('POST', $path, $data)->post($this->base_url . $path, $data)->json();
    }

    /**
     * Get order status by order ID
     * @param string $orderId
     * @return array
     */
    public function getOrderStatus(string $currencyPair, string $orderId)
    {
        $path = "/v1/orders/{$currencyPair}/orderid/{$orderId}";
        return $this->http('GET', $path)->get($this->base_url . $path)->json();
    }

    /**
     * Get all open orders
     * @return array
     */
    public function getOpenOrders()
    {
        $path = "/v1/orders/open";
        return $this->http('GET', $path)->get($this->base_url . $path)->json();
    }

    /**
     * Cancel an order
     * @param string $orderId
     * @return array
     */
    public function cancelOrder(string $orderId)
    {
        $path = "/v1/orders/order";
        $data = ['orderId' => $orderId];
        return $this->http('DELETE', $path, $data)->delete($this->base_url . $path, $data)->json();
    }

    /**
     * Get trade history
     * @param string|null $pair - Optional trading pair filter
     * @param int $limit - Number of records to return
     * @return array
     */
    public function getTradeHistory(?string $pair = null, int $limit = 100)
    {
        $path = "/v1/account/trades";
        $params = ['limit' => $limit];
        
        if ($pair) {
            $params['pair'] = $pair;
        }

        $queryString = http_build_query($params);
        $fullPath = $path . '?' . $queryString;
        
        return $this->http('GET', $fullPath)->get($this->base_url . $fullPath)->json();
    }

    /**
     * Get deposit history
     * @param string|null $currency - Optional currency filter
     * @param int $skip - Number of records to skip
     * @param int $limit - Number of records to return
     * @return array
     */
    public function getDepositHistory(?string $currency = null, int $skip = 0, int $limit = 100)
    {
        $path = "/v1/account/deposits/history";
        $params = [
            'skip' => $skip,
            'limit' => $limit
        ];

        if ($currency) {
            $params['currency'] = $currency;
        }

        $queryString = http_build_query($params);
        $fullPath = $path . '?' . $queryString;

        return $this->http('GET', $fullPath)->get($this->base_url . $fullPath)->json();
    }

    /**
     * Get withdrawal history
     * @param string|null $currency - Optional currency filter
     * @param int $skip - Number of records to skip
     * @param int $limit - Number of records to return
     * @return array
     */
    public function getWithdrawalHistory(
        ?string $currency = null, 
        int $skip = 0, 
        int $limit = 100)
    {
        $path = "/v1/account/withdrawals/history";
        $params = [
            'skip' => $skip,
            'limit' => $limit
        ];

        if ($currency) {
            $params['currency'] = $currency;
        }

        $queryString = http_build_query($params);
        $fullPath = $path . '?' . $queryString;

        return $this->http('GET', $fullPath)->get($this->base_url . $fullPath)->json();
    }

    /**
     * Initiate a cryptocurrency withdrawal
     * @param string $currency
     * @param float $amount
     * @param string $address - Withdrawal address
     * @return array
     */
    public function withdraw(string $currency, float $amount, string $address)
    {
        $path = "/v1/account/withdraw";
        $data = [
            'currency' => $currency,
            'amount' => $amount,
            'address' => $address
        ];

        return $this->http('POST', $path, $data)->post($this->base_url . $path, $data)->json();
    }

    /**
     * Get withdrawal status
     * @param string $withdrawalId
     * @return array
     */
    public function getWithdrawalStatus(string $withdrawalId)
    {
        $path = "/v1/account/withdrawals/{$withdrawalId}";
        return $this->http('GET', $path)->get($this->base_url . $path)->json();
    }

    /**
     * Get deposit address for a currency
     * @param string $currency
     * @return array
     */
    public function getDepositAddress(string $currency)
    {
        $path = "/v1/account/deposit/address/{$currency}";
        return $this->http('GET', $path)->get($this->base_url . $path)->json();
    }

    /**
     * Get transaction history
     * @param int $skip - Number of records to skip
     * @param int $limit - Number of records to return
     * @return array
     */
    public function getTransactionHistory(int $skip = 0, int $limit = 100)
    {
        $path = "/v1/account/transactionhistory";
        $params = [
            'skip' => $skip,
            'limit' => $limit
        ];

        $queryString = http_build_query($params);
        $fullPath = $path . '?' . $queryString;

        return $this->http('GET', $fullPath)->get($this->base_url . $fullPath)->json();
    }

    /**
     * Get market summary for all pairs
     * @return array
     */
    public function getMarketSummary()
    {
        $path = "/v1/public/marketsummary";
        return $this->http('GET', $path)->get($this->base_url . $path)->json();
    }

    /**
     * Get Market Summary for currencyPair
     * @param string $currencyPair
     */
    public function getMarketSummaryForCurrencyPair(string $currencyPair)
    {
        $path = "/v1/public/{$currencyPair}/marketsummary";
        return $this->http('GET', $path)->get($this->base_url . $path)->json();
    }

    /**
     * Get order book for a currency pair
     * @param string $currencyPair - e.g., BTCZAR
     * @return array
     */
    public function getOrderBook(string $currencyPair)
    {
        $path = "/v1/public/{$currencyPair}/orderbook";
        return $this->http('GET', $path)->get($this->base_url . $path)->json();
    }

    /**
     * Get recent trades for a currency pair
     * @param string $currencyPair - e.g., BTCZAR
     * @param int $limit - Number of trades to return
     * @return array
     */
    public function getPublicTrades(string $currencyPair, int $limit = 100)
    {
        $path = "/v1/public/{$currencyPair}/trades";
        $params = ['limit' => $limit];
        $queryString = http_build_query($params);
        $fullPath = $path . '?' . $queryString;

        return $this->http('GET', $fullPath)->get($this->base_url . $fullPath)->json();
    }

    /**
     * Get simple quote for buying or selling crypto
     * @param string $currencyPair - e.g., BTCZAR
     * @param string $payInCurrency - Currency to pay in
     * @param float $payAmount - Amount to pay
     * @param string $side - BUY or SELL
     * @return array
     */
    public function getSimpleQuote(
        string $currencyPair, 
        string $payInCurrency, 
        float $payAmount, 
        string $side)
    {
        $path = "/v1/simple/{$currencyPair}/quote";
        $data = [
            'payInCurrency' => $payInCurrency,
            'payAmount' => (string) $payAmount,
            'side' => $side
        ];

        $response = $this->http('POST', $path, $data)->post($this->base_url . $path, $data);
        
        Log::info('VALR getSimpleQuote response', [
            'status' => $response->status(),
            'body' => $response->body(),
            'json' => $response->json()
        ]);

        return $response->json();
    }

    /**
     * Execute simple buy/sell order
     * @param string $currencyPair - e.g., BTCZAR
     * @param string $payInCurrency - Currency to pay in
     * @param float $payAmount - Amount to pay
     * @param string $side - BUY or SELL
     * @return array
     */
    public function placeSimpleOrder(
        string $currencyPair, 
        string $payInCurrency, 
        float $payAmount, 
        string $side)
    {
        $path = "/v1/simple/{$currencyPair}/order";
        $data = [
            'payInCurrency' => $payInCurrency,
            'payAmount' => $payAmount,
            'side' => $side
        ];

        return $this->http('POST', $path, $data)->post($this->base_url . $path, $data)->json();
    }

    /**
     * Get order history summary
     * @param int $skip - Number of records to skip
     * @param int $limit - Number of records to return
     * @return array
     */
    public function getOrderHistory(int $skip = 0, int $limit = 100)
    {
        $path = "/v1/account/orderhistory";
        $params = [
            'skip' => $skip,
            'limit' => $limit
        ];
        $queryString = http_build_query($params);
        $fullPath = $path . '?' . $queryString;

        return $this->http('GET', $fullPath)->get($this->base_url . $fullPath)->json();
    }

    /**
     * Get detailed order history with fills
     * @param string $orderId
     * @return array
     */
    public function getOrderHistoryDetail(string $orderId)
    {
        $path = "/v1/account/orderhistory/detail/{$orderId}";
        return $this->http('GET', $path)->get($this->base_url . $path)->json();
    }

    /**
     * Make a payment (VALR Pay)
     * @param float $amount - Payment amount (required)
     * @param string $currency - Currency code (required)
     * @param string|null $recipientEmail - Recipient email address
     * @param string|null $recipientCellNumber - Recipient cell number
     * @param string|null $recipientPayId - Recipient Pay ID
     * @param string|null $recipientNote - Note to recipient
     * @param string|null $senderNote - Note for sender
     * @param bool $anonymous - Whether payment is anonymous
     * @return array
     */
    public function makePayment(
        float $amount,
        string $currency,
        ?string $recipientEmail = null,
        ?string $recipientCellNumber = null,
        ?string $recipientPayId = null,
        ?string $recipientNote = null,
        ?string $senderNote = null,
        bool $anonymous = false
    ) {
        $path = "/v1/pay";
        $data = [
            'amount' => $amount,
            'currency' => $currency,
            'anonymous' => $anonymous ? 'true' : 'false'
        ];

        // Add recipient identifier (at least one is required)
        if ($recipientEmail) {
            $data['recipientEmail'] = $recipientEmail;
        }
        if ($recipientCellNumber) {
            $data['recipientCellNumber'] = $recipientCellNumber;
        }
        if ($recipientPayId) {
            $data['recipientPayId'] = $recipientPayId;
        }

        // Add optional fields
        if ($recipientNote) {
            $data['recipientNote'] = $recipientNote;
        }
        if ($senderNote) {
            $data['senderNote'] = $senderNote;
        }

        Log::info('VALR makePayment request', [
            'data' => $data,
            'path' => $path,
            'fullUrl' => $this->base_url . $path
        ]);

        $response = $this->http('POST', $path, $data)->post($this->base_url . $path, $data);
        
        Log::info('VALR makePayment response', [
            'status' => $response->status(),
            'body' => $response->body(),
            'json' => $response->json()
        ]);

        return $response->json();
    }

    /**
     * Get linked bank accounts for a currency
     * @param string $currencyCode - Currency code (e.g., ZAR, USD)
     * @return array
     */
    public function getLinkedBankAccounts(string $currencyCode)
    {
        $path = "/v1/wallet/fiat/{$currencyCode}/accounts";
        
        Log::info('VALR getLinkedBankAccounts request', [
            'currencyCode' => $currencyCode,
            'path' => $path,
            'fullUrl' => $this->base_url . $path
        ]);

        $response = $this->http('GET', $path)->get($this->base_url . $path);
        
        Log::info('VALR getLinkedBankAccounts response', [
            'status' => $response->status(),
            'body' => $response->body(),
            'json' => $response->json()
        ]);

        return $response->json();
    }

    /**
     * Make a fiat withdrawal to a linked bank account
     * @param string $currencyCode
     * @param float $amount
     * @param string $linkedBankAccountId
     * @return array
     */
    public function makeFiatWithdrawal(
        string $currencyCode, 
        float $amount, 
        string $linkedBankAccountId): array
    {
        $path = "/v1/wallet/fiat/{$currencyCode}/withdraw";
        
        $payload = [
            'amount' => (string) $amount,
            'linkedBankAccountId' => $linkedBankAccountId
        ];
        
        Log::info('VALR makeFiatWithdrawal request', [
            'currencyCode' => $currencyCode,
            'path' => $path,
            'fullUrl' => $this->base_url . $path,
            'payload' => $payload
        ]);

        $response = $this->http('POST', $path, $payload)
            ->post($this->base_url . $path, $payload);
        
        Log::info('VALR makeFiatWithdrawal response', [
            'status' => $response->status(),
            'body' => $response->body(),
            'json' => $response->json()
        ]);

        return $response->json();
    }

    /**
     * Get crypto wallet deposit address for a currency
     * @param string $currencyCode
     * @return array
     */
    public function getCryptoCurrencyWalletAddress(string $currencyCode): array
    {
        $path = "/v1/wallet/crypto/{$currencyCode}/deposit/address";
        
        Log::info('VALR getCryptoCurrencyWalletAddress request', [
            'currencyCode' => $currencyCode,
            'path' => $path,
            'fullUrl' => $this->base_url . $path
        ]);

        $response = $this->http('GET', $path)->get($this->base_url . $path);
        
        Log::info('VALR getCryptoCurrencyWalletAddress response', [
            'status' => $response->status(),
            'body' => $response->body(),
            'json' => $response->json()
        ]);

        return $response->json();
    }

    /**
     * Make a crypto withdrawal
     * @param string $currencyCode
     * @param string $amount
     * @param string|null $address - Withdrawal address (use this OR addressBookId, not both)
     * @param string|null $addressBookId - Address book ID (use this OR address, not both)
     * @param string $beneficiaryName
     * @param bool $isCorporate
     * @param bool $isSelfHosted
     * @param string|null $serviceProviderName - Service provider name (use this OR serviceProviderId, not both)
     * @param string|null $serviceProviderId - Service provider ID (use this OR serviceProviderName, not both)
     * @param string|null $networkType
     * @param bool|null $allowBorrow
     * @return array
     */
    public function makeCryptoWithdrawal(
        string $currencyCode,
        string $amount,
        ?string $address = null,
        ?string $addressBookId = null,
        string $beneficiaryName = '',
        bool $isCorporate = false,
        bool $isSelfHosted = false,
        ?string $serviceProviderName = null,
        ?string $serviceProviderId = null,
        ?string $networkType = null,
        ?bool $allowBorrow = null
    ): array {
        // Validate that only one of address or addressBookId is provided
        if ($address !== null && $addressBookId !== null) {
            throw new \InvalidArgumentException('Cannot provide both address and addressBookId. Use one or the other.');
        }
        
        if ($address === null && $addressBookId === null) {
            throw new \InvalidArgumentException('Must provide either address or addressBookId.');
        }
        
        // Validate that only one of serviceProviderName or serviceProviderId is provided
        if ($serviceProviderName !== null && $serviceProviderId !== null) {
            throw new \InvalidArgumentException('Cannot provide both serviceProviderName and serviceProviderId. Use one or the other.');
        }
        
        if ($serviceProviderName === null && $serviceProviderId === null) {
            throw new \InvalidArgumentException('Must provide either serviceProviderName or serviceProviderId.');
        }
        
        // Validate address is whitelisted before attempting withdrawal (only if address is provided)
        if ($address !== null) {
            $whitelistedEntry = $this->validateWhitelistedAddress(trim($address), trim($currencyCode), $networkType);
            
            if ($whitelistedEntry === null) {
                Log::error('VALR makeCryptoWithdrawal: Address not found in whitelist', [
                    'address' => $address,
                    'currency' => $currencyCode,
                    'networkType' => $networkType
                ]);
                // Still proceed but log the warning - VALR will reject if truly not whitelisted
            } else {
                Log::info('VALR makeCryptoWithdrawal: Address validated in whitelist', [
                    'whitelistedEntry' => $whitelistedEntry
                ]);
            }
        }
        
        $path = "/v1/wallet/crypto/{$currencyCode}/withdraw";
        
        $payload = [
            'amount' => trim($amount),
            'beneficiaryName' => trim($beneficiaryName),
            'isCorporate' => $isCorporate,
            'isSelfHosted' => $isSelfHosted
        ];

        // Add EITHER address OR addressBookId (VALR requires one, not both)
        if ($address !== null) {
            $payload['address'] = trim($address); // Trim whitespace
        } elseif ($addressBookId !== null) {
            $payload['addressBookId'] = trim($addressBookId);
        }

        // Add EITHER serviceProviderName OR serviceProviderId (VALR requires one, not both)
        if ($serviceProviderName !== null) {
            $payload['serviceProviderName'] = trim($serviceProviderName);
        } elseif ($serviceProviderId !== null) {
            $payload['serviceProviderId'] = trim($serviceProviderId);
        }

        // Add optional fields
        if ($networkType !== null) {
            $payload['networkType'] = trim($networkType);
        }
        if ($allowBorrow !== null) {
            $payload['allowBorrow'] = $allowBorrow;
        }
        
        Log::info('VALR makeCryptoWithdrawal request', [
            'currencyCode' => $currencyCode,
            'path' => $path,
            'fullUrl' => $this->base_url . $path,
            'payload' => $payload,
            'addressLength' => $address !== null ? strlen($address) : null,
            'addressTrimmedLength' => $address !== null ? strlen(trim($address)) : null,
            'hasWhitespace' => $address !== null ? ($address !== trim($address)) : null,
            'usingAddressBookId' => $addressBookId !== null
        ]);

        $response = $this->http('POST', $path, $payload)
            ->post($this->base_url . $path, $payload);
        
        Log::info('VALR makeCryptoWithdrawal response', [
            'status' => $response->status(),
            'body' => $response->body(),
            'json' => $response->json()
        ]);

        // Check if the request was successful
        if (!$response->successful()) {
            Log::error('VALR API Error - Make Crypto Withdrawal failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception('Failed to make crypto withdrawal: ' . $response->body());
        }

        $responseData = $response->json();
        
        // Validate that we received a withdrawal ID
        if (!isset($responseData['id']) || empty($responseData['id'])) {
            Log::error('VALR API Error - No withdrawal ID received', [
                'response' => $response->body()
            ]);
            throw new \Exception('No withdrawal ID received from VALR API');
        }

        return $this->getCryptoWithdrawalStatus($currencyCode, $responseData['id']);
    }

    public function getCryptoWithdrawalStatus(string $currencyCode, string $withdrawId): array {
        $path = "/v1/wallet/crypto/{$currencyCode}/withdraw/{$withdrawId}";
        return $this->http('GET', $path)->get($this->base_url . $path)->json();
    }

    /**
     * Get list of crypto service providers
     * @return array
     */
    public function getCryptoServiceProviders(): array
    {
        $path = "/v1/wallet/crypto/service-providers";
        
        Log::info('VALR getCryptoServiceProviders request', [
            'path' => $path,
            'fullUrl' => $this->base_url . $path
        ]);

        $response = $this->http('GET', $path)->get($this->base_url . $path);
        
        Log::info('VALR getCryptoServiceProviders response', [
            'status' => $response->status(),
            'body' => $response->body(),
            'json' => $response->json()
        ]);

        return $response->json();
    }

    /**
     * Get a list of currencies supported by VALR
     * @return array
     */
    public function getCurrencies(): array {
        $path = "/v1/public/currencies";
        return $this->http('GET', $path)->get($this->base_url . $path)->json();
    }

    /**
     * Get list of currency pairs
     * @return array
     */
    public function getCurrencyPairs(): array
    {
        $path = "/v1/public/pairs";

        Log::info('VALR getCurrencyPairs request', [
            'path' => $path,
            'fullUrl' => $this->base_url . $path
        ]);

        $response = $this->http('GET', $path)->get($this->base_url . $path);
        
        Log::info('VALR getCurrencyPairs response', [
            'status' => $response->status(),
            'body' => $response->body(),
            'json' => $response->json()
        ]);

        return $response->json();
    }

    /**
     * Get Withdrawal config Info
     * @return array
     */
    public function getWithdrawalConfigInfoForCryptoCurrency(string $currencyCode): array
    {
        $path = "/v1/wallet/crypto/{$currencyCode}/withdraw";
        return $this->http('GET', $path)->get($this->base_url . $path)->json();
    }

    /**
     * Get crypto withdrawal history
     * @param int $skip - Number of records to skip
     * @param int $limit - Number of records to return
     * @param string|null $currency - Optional currency filter
     * @param string|null $startTime - Optional start time (ISO 8601 format)
     * @param string|null $endTime - Optional end time (ISO 8601 format)
     * @return array
     */
    public function getCryptoWithdrawalHistory(
        int $skip = 0,
        int $limit = 10,
        ?string $currency = null,
        ?string $startTime = null,
        ?string $endTime = null
    ): array {
        $path = "/v1/wallet/crypto/withdraw/history";
        
        $params = [
            'skip' => $skip,
            'limit' => $limit
        ];

        if ($currency !== null) {
            $params['currency'] = $currency;
        }
        if ($startTime !== null) {
            $params['startTime'] = $startTime;
        }
        if ($endTime !== null) {
            $params['endTime'] = $endTime;
        }

        $queryString = http_build_query($params);
        $fullPath = $path . '?' . $queryString;
        
        Log::info('VALR getCryptoWithdrawalHistory request', [
            'path' => $fullPath,
            'fullUrl' => $this->base_url . $fullPath,
            'params' => $params
        ]);

        $response = $this->http('GET', $fullPath)->get($this->base_url . $fullPath);
        
        Log::info('VALR getCryptoWithdrawalHistory response', [
            'status' => $response->status(),
            'body' => $response->body(),
            'json' => $response->json()
        ]);

        return $response->json();
    }

    /**
     * Get whitelisted crypto withdrawal address book
     * @return array
     */
    public function getCryptoAddressBook(): array
    {
        $path = "/v1/wallet/crypto/address-book";
        
        Log::info('VALR getCryptoAddressBook request', [
            'path' => $path,
            'fullUrl' => $this->base_url . $path
        ]);

        $response = $this->http('GET', $path)->get($this->base_url . $path);
        
        Log::info('VALR getCryptoAddressBook response', [
            'status' => $response->status(),
            'body' => $response->body(),
            'json' => $response->json()
        ]);

        return $response->json();
    }

    /**
     * Validate if an address is whitelisted in the address book
     * @param string $address - The withdrawal address to check
     * @param string $currencyCode - The currency code (e.g., BTC, USDT)
     * @param string|null $networkType - Optional network type (e.g., ERC20, TRC20, Ethereum)
     * @return array|null - Returns the address book entry if found, null otherwise
     */
    public function validateWhitelistedAddress(string $address, string $currencyCode, ?string $networkType = null): ?array
    {
        $addressBook = $this->getCryptoAddressBook();
        
        // Normalize the address for comparison (trim whitespace, standardize case)
        $normalizedAddress = trim($address);
        
        Log::info('VALR validateWhitelistedAddress: Searching', [
            'address' => $normalizedAddress,
            'currency' => $currencyCode,
            'networkType' => $networkType,
            'totalAddressBookEntries' => count($addressBook)
        ]);
        
        // Search for the address in the address book
        foreach ($addressBook as $entry) {
            // Check if currency matches
            if (isset($entry['currency']) && strtoupper($entry['currency']) !== strtoupper($currencyCode)) {
                continue;
            }
            
            // Check if address matches (case-insensitive for Ethereum addresses)
            if (!isset($entry['address']) || strcasecmp(trim($entry['address']), $normalizedAddress) !== 0) {
                continue;
            }
            
            // If networkType is provided, validate it matches
            if ($networkType !== null) {
                if (!isset($entry['networkType']) || strcasecmp($entry['networkType'], $networkType) !== 0) {
                    Log::warning('VALR validateWhitelistedAddress: Address found but network mismatch', [
                        'address' => $address,
                        'currency' => $currencyCode,
                        'requestedNetwork' => $networkType,
                        'whitelistedNetwork' => $entry['networkType'] ?? 'NOT_SET',
                        'addressBookEntry' => $entry
                    ]);
                    continue;
                }
            }
            
            // Found a match!
            Log::info('VALR validateWhitelistedAddress: Address found in whitelist', [
                'address' => $address,
                'currency' => $currencyCode,
                'networkType' => $networkType,
                'addressBookEntry' => $entry
            ]);
            return $entry;
        }
        
        Log::warning('VALR validateWhitelistedAddress: Address NOT found in whitelist', [
            'address' => $address,
            'currency' => $currencyCode,
            'networkType' => $networkType,
            'addressBookCount' => count($addressBook)
        ]);
        
        return null;
    }
}
