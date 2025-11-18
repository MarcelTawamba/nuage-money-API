<?php

namespace App\Services\Bridge;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\BridgeIdempotencyKey;
use Illuminate\Support\Str;

class BridgeService
{
    private $base_url;

    private $api_key;

    public function __construct()
    {
        $this->base_url = env("BRIDGE_BASE_URL", "https://api.bridge.xyz/v0");
        $this->api_key = env("BRIDGE_API_KEY");
    }

    /**
     * @return \Illuminate\Http\Client\PendingRequest
     */
    private function http()
    {
        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'Api-Key' => $this->api_key
        ]);
    }

    /**
     * Generate or retrieve idempotency key for a request
     * @param string $requestType
     * @param array $payload
     * @param string|null $existingKey - Use existing key for retries
     * @return string
     */
    private function getIdempotencyKey(string $requestType, array $payload, ?string $existingKey = null): string
    {
        if ($existingKey) {
            $record = BridgeIdempotencyKey::where('idempotency_key', $existingKey)->first();
            
            if ($record && $record->isExpired()) {
                Log::warning("Idempotency key {$existingKey} has expired. Generating new key for retry.");
                $existingKey = null;
            } else {
                return $existingKey;
            }
        }

        $key = (string) Str::uuid();
        
        BridgeIdempotencyKey::create([
            'idempotency_key' => $key,
            'request_type' => $requestType,
            'request_payload' => $payload,
            'status' => 'pending',
            'expires_at' => now()->addHours(24),
        ]);

        return $key;
    }

    /**
     * Update idempotency key record with response
     * @param string $key
     * @param mixed $response
     * @param string $status
     */
    private function updateIdempotencyRecord(string $key, $response, string $status = 'completed')
    {
        $record = BridgeIdempotencyKey::where('idempotency_key', $key)->first();
        
        if ($record) {
            $record->update([
                'response_data' => $response->json(),
                'status' => $status,
                'http_status_code' => $response->status(),
            ]);
        }
    }

    /**
     * Make a POST request with idempotency key
     * @param string $endpoint
     * @param array $data
     * @param string $requestType
     * @param string|null $idempotencyKey - Optional existing key for retries
     * @return mixed
     */
    private function postWithIdempotency(string $endpoint, array $data, string $requestType, ?string $idempotencyKey = null)
    {
        $key = $this->getIdempotencyKey($requestType, $data, $idempotencyKey);
        
        $record = BridgeIdempotencyKey::where('idempotency_key', $key)
            ->where('status', 'completed')
            ->first();
            
        if ($record && !$record->isExpired()) {
            Log::info("Returning cached response for idempotency key: {$key}");
            return $record->response_data;
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Api-Key' => $this->api_key,
                'Idempotency-Key' => $key
            ])->post($endpoint, $data);

            $this->updateIdempotencyRecord($key, $response, $response->successful() ? 'completed' : 'failed');

            return $response;
        } catch (\Exception $e) {
            Log::error("Bridge API request failed with idempotency key {$key}: " . $e->getMessage());
            
            $record = BridgeIdempotencyKey::where('idempotency_key', $key)->first();
            if ($record) {
                $record->update(['status' => 'failed']);
            }
            
            throw $e;
        }
    }

    /**
     * Create a new customer
     * @param string $email
     * @param string $type - individual or business
     * @param array $details - Customer details (first_name, last_name, etc.)
     * @param string|null $idempotencyKey - Optional existing key for retries
     * @return array
     */
    public function createCustomer(string $email, string $type = "individual", array $details = [], ?string $idempotencyKey = null)
    {
        $endpoint = $this->base_url . "/customers";
        $data = array_merge([
            'email' => $email,
            'type' => $type
        ], $details);

        return $this->postWithIdempotency($endpoint, $data, 'create_customer', $idempotencyKey);
    }

    /**
     * Get customer by ID
     * @param string $customerId
     * @return array
     */
    public function getCustomer(string $customerId)
    {
        $endpoint = $this->base_url . "/customers/{$customerId}";
        return $this->http()->get($endpoint);
    }

    /**
     * List all customers
     * @param int $limit
     * @param string $cursor - Pagination cursor
     * @return array
     */
    public function listCustomers(int $limit = 100, string $cursor = null)
    {
        $endpoint = $this->base_url . "/customers";
        $params = ['limit' => $limit];

        if ($cursor) {
            $params['cursor'] = $cursor;
        }

        return $this->http()->get($endpoint, $params);
    }

    /**
     * Update customer
     * @param string $customerId
     * @param array $data - Customer data to update
     * @return array
     */
    public function updateCustomer(string $customerId, array $data)
    {
        $endpoint = $this->base_url . "/customers/{$customerId}";
        return $this->http()->put($endpoint, $data);
    }

    /**
     * Create KYC link for a customer
     * @param string $customerId
     * @param string $type - kyc or kyb
     * @param string|null $idempotencyKey - Optional existing key for retries
     * @return array
     */
    public function createKycLink(string $customerId, string $type = "kyc", ?string $idempotencyKey = null)
    {
        $endpoint = $this->base_url . "/kyc_links";
        $data = [
            'customer_id' => $customerId,
            'type' => $type
        ];

        return $this->postWithIdempotency($endpoint, $data, 'create_kyc_link', $idempotencyKey);
    }

    /**
     * Get KYC link status
     * @param string $kycLinkId
     * @return array
     */
    public function getKycLinkStatus(string $kycLinkId)
    {
        $endpoint = $this->base_url . "/kyc_links/{$kycLinkId}";
        return $this->http()->get($endpoint);
    }

    /**
     * Create a wallet for a customer
     * @param string $customerId
     * @param string $network - e.g., ethereum, solana, polygon
     * @param string|null $idempotencyKey - Optional existing key for retries
     * @return array
     */
    public function createWallet(string $customerId, string $network = "ethereum", ?string $idempotencyKey = null)
    {
        $endpoint = $this->base_url . "/customers/{$customerId}/wallets";
        $data = ['network' => $network];

        return $this->postWithIdempotency($endpoint, $data, 'create_wallet', $idempotencyKey);
    }

    /**
     * List customer wallets
     * @param string $customerId
     * @return array
     */
    public function getCustomerWallets(string $customerId)
    {
        $endpoint = $this->base_url . "/customers/{$customerId}/wallets";
        return $this->http()->get($endpoint);
    }

    /**
     * Get wallet details
     * @param string $walletId
     * @return array
     */
    public function getWallet(string $walletId)
    {
        $endpoint = $this->base_url . "/wallets/{$walletId}";
        return $this->http()->get($endpoint);
    }

    /**
     * Get wallet balance
     * @param string $walletId
     * @return array
     */
    public function getWalletBalance(string $walletId)
    {
        $endpoint = $this->base_url . "/wallets/{$walletId}/balances";
        return $this->http()->get($endpoint);
    }

    /**
     * Create a transfer
     * @param string $sourceType - wallet, external_account, ach
     * @param string $sourceId
     * @param string $destinationType - wallet, external_account, crypto_address
     * @param string $destinationId
     * @param float $amount
     * @param string $currency
     * @param array $additionalData - Optional additional transfer data
     * @param string|null $idempotencyKey - Optional existing key for retries
     * @return array
     */
    public function createTransfer(
        string $sourceType,
        string $sourceId,
        string $destinationType,
        string $destinationId,
        float $amount,
        string $currency,
        array $additionalData = [],
        ?string $idempotencyKey = null
    ) {
        $endpoint = $this->base_url . "/transfers";
        $data = array_merge([
            'source' => [
                'type' => $sourceType,
                'id' => $sourceId
            ],
            'destination' => [
                'type' => $destinationType,
                'id' => $destinationId
            ],
            'amount' => $amount,
            'currency' => $currency
        ], $additionalData);

        return $this->postWithIdempotency($endpoint, $data, 'create_transfer', $idempotencyKey);
    }

    /**
     * Get transfer details
     * @param string $transferId
     * @return array
     */
    public function getTransfer(string $transferId)
    {
        $endpoint = $this->base_url . "/transfers/{$transferId}";
        return $this->http()->get($endpoint);
    }

    /**
     * List all transfers
     * @param int $limit
     * @param string $cursor - Pagination cursor
     * @param string $customerId - Optional filter by customer
     * @return array
     */
    public function listTransfers(int $limit = 100, string $cursor = null, string $customerId = null)
    {
        $endpoint = $this->base_url . "/transfers";
        $params = ['limit' => $limit];

        if ($cursor) {
            $params['cursor'] = $cursor;
        }

        if ($customerId) {
            $params['customer_id'] = $customerId;
        }

        return $this->http()->get($endpoint, $params);
    }

    /**
     * Create an external account (bank account)
     * @param string $customerId
     * @param string $accountNumber
     * @param string $routingNumber
     * @param string $type - checking or savings
     * @param array $additionalData
     * @param string|null $idempotencyKey - Optional existing key for retries
     * @return array
     */
    public function createExternalAccount(
        string $customerId,
        string $accountNumber,
        string $routingNumber,
        string $type = "checking",
        array $additionalData = [],
        ?string $idempotencyKey = null
    ) {
        $endpoint = $this->base_url . "/customers/{$customerId}/external_accounts";
        $data = array_merge([
            'account_number' => $accountNumber,
            'routing_number' => $routingNumber,
            'type' => $type
        ], $additionalData);

        return $this->postWithIdempotency($endpoint, $data, 'create_external_account', $idempotencyKey);
    }

    /**
     * List customer external accounts
     * @param string $customerId
     * @return array
     */
    public function getCustomerExternalAccounts(string $customerId)
    {
        $endpoint = $this->base_url . "/customers/{$customerId}/external_accounts";
        return $this->http()->get($endpoint);
    }

    /**
     * Get external account details
     * @param string $externalAccountId
     * @return array
     */
    public function getExternalAccount(string $externalAccountId)
    {
        $endpoint = $this->base_url . "/external_accounts/{$externalAccountId}";
        return $this->http()->get($endpoint);
    }

    /**
     * Create a payment
     * @param string $customerId
     * @param float $amount
     * @param string $currency
     * @param string $destinationType
     * @param string $destinationId
     * @param array $additionalData
     * @param string|null $idempotencyKey - Optional existing key for retries
     * @return array
     */
    public function createPayment(
        string $customerId,
        float $amount,
        string $currency,
        string $destinationType,
        string $destinationId,
        array $additionalData = [],
        ?string $idempotencyKey = null
    ) {
        $endpoint = $this->base_url . "/payments";
        $data = array_merge([
            'customer_id' => $customerId,
            'amount' => $amount,
            'currency' => $currency,
            'destination' => [
                'type' => $destinationType,
                'id' => $destinationId
            ]
        ], $additionalData);

        return $this->postWithIdempotency($endpoint, $data, 'create_payment', $idempotencyKey);
    }

    /**
     * Get payment details
     * @param string $paymentId
     * @return array
     */
    public function getPayment(string $paymentId)
    {
        $endpoint = $this->base_url . "/payments/{$paymentId}";
        return $this->http()->get($endpoint);
    }

    /**
     * List all payments
     * @param int $limit
     * @param string $cursor - Pagination cursor
     * @return array
     */
    public function listPayments(int $limit = 100, string $cursor = null)
    {
        $endpoint = $this->base_url . "/payments";
        $params = ['limit' => $limit];

        if ($cursor) {
            $params['cursor'] = $cursor;
        }

        return $this->http()->get($endpoint, $params);
    }

    /**
     * Create a virtual account for customer
     * @param string $customerId
     * @param string $currency - USD, EUR, MXN
     * @param array $additionalData
     * @param string|null $idempotencyKey - Optional existing key for retries
     * @return array
     */
    public function createVirtualAccount(string $customerId, string $currency = "USD", array $additionalData = [], ?string $idempotencyKey = null)
    {
        $endpoint = $this->base_url . "/customers/{$customerId}/virtual_accounts";
        $data = array_merge([
            'currency' => $currency
        ], $additionalData);

        return $this->postWithIdempotency($endpoint, $data, 'create_virtual_account', $idempotencyKey);
    }

    /**
     * List customer virtual accounts
     * @param string $customerId
     * @param int $limit
     * @param string $cursor - Pagination cursor
     * @return array
     */
    public function getCustomerVirtualAccounts(string $customerId, int $limit = 100, string $cursor = null)
    {
        $endpoint = $this->base_url . "/customers/{$customerId}/virtual_accounts";
        $params = ['limit' => $limit];

        if ($cursor) {
            $params['cursor'] = $cursor;
        }

        return $this->http()->get($endpoint, $params);
    }

    /**
     * Get virtual account details
     * @param string $virtualAccountId
     * @return array
     */
    public function getVirtualAccount(string $virtualAccountId)
    {
        $endpoint = $this->base_url . "/virtual_accounts/{$virtualAccountId}";
        return $this->http()->get($endpoint);
    }

    /**
     * Update virtual account
     * @param string $virtualAccountId
     * @param array $data
     * @return array
     */
    public function updateVirtualAccount(string $virtualAccountId, array $data)
    {
        $endpoint = $this->base_url . "/virtual_accounts/{$virtualAccountId}";
        return $this->http()->put($endpoint, $data);
    }

    /**
     * Deactivate virtual account
     * @param string $virtualAccountId
     * @param string|null $idempotencyKey - Optional existing key for retries
     * @return array
     */
    public function deactivateVirtualAccount(string $virtualAccountId, ?string $idempotencyKey = null)
    {
        $endpoint = $this->base_url . "/virtual_accounts/{$virtualAccountId}/deactivate";
        return $this->postWithIdempotency($endpoint, [], 'deactivate_virtual_account', $idempotencyKey);
    }

    /**
     * Reactivate virtual account
     * @param string $virtualAccountId
     * @param string|null $idempotencyKey - Optional existing key for retries
     * @return array
     */
    public function reactivateVirtualAccount(string $virtualAccountId, ?string $idempotencyKey = null)
    {
        $endpoint = $this->base_url . "/virtual_accounts/{$virtualAccountId}/reactivate";
        return $this->postWithIdempotency($endpoint, [], 'reactivate_virtual_account', $idempotencyKey);
    }

    /**
     * Create a card for customer
     * @param string $customerId
     * @param array $cardData - Card configuration data
     * @param string|null $idempotencyKey - Optional existing key for retries
     * @return array
     */
    public function createCard(string $customerId, array $cardData = [], ?string $idempotencyKey = null)
    {
        $endpoint = $this->base_url . "/customers/{$customerId}/cards";
        return $this->postWithIdempotency($endpoint, $cardData, 'create_card', $idempotencyKey);
    }

    /**
     * List customer cards
     * @param string $customerId
     * @param int $limit
     * @param string $cursor - Pagination cursor
     * @return array
     */
    public function getCustomerCards(string $customerId, int $limit = 100, string $cursor = null)
    {
        $endpoint = $this->base_url . "/customers/{$customerId}/cards";
        $params = ['limit' => $limit];

        if ($cursor) {
            $params['cursor'] = $cursor;
        }

        return $this->http()->get($endpoint, $params);
    }

    /**
     * Get card details
     * @param string $cardId
     * @return array
     */
    public function getCard(string $cardId)
    {
        $endpoint = $this->base_url . "/cards/{$cardId}";
        return $this->http()->get($endpoint);
    }

    /**
     * Update card
     * @param string $cardId
     * @param array $data
     * @return array
     */
    public function updateCard(string $cardId, array $data)
    {
        $endpoint = $this->base_url . "/cards/{$cardId}";
        return $this->http()->put($endpoint, $data);
    }

    /**
     * Get liquidity balances
     * @return array
     */
    public function getLiquidityBalances()
    {
        $endpoint = $this->base_url . "/liquidity/balances";
        return $this->http()->get($endpoint);
    }

    /**
     * Create liquidation request
     * @param array $data - Liquidation request data
     * @param string|null $idempotencyKey - Optional existing key for retries
     * @return array
     */
    public function createLiquidation(array $data, ?string $idempotencyKey = null)
    {
        $endpoint = $this->base_url . "/liquidation";
        return $this->postWithIdempotency($endpoint, $data, 'create_liquidation', $idempotencyKey);
    }

    /**
     * Get all transactions for a customer
     * @param string $customerId
     * @param int $limit
     * @param string $cursor - Pagination cursor
     * @return array
     */
    public function getCustomerTransactions(string $customerId, int $limit = 100, string $cursor = null)
    {
        $endpoint = $this->base_url . "/customers/{$customerId}/transactions";
        $params = ['limit' => $limit];

        if ($cursor) {
            $params['cursor'] = $cursor;
        }

        return $this->http()->get($endpoint, $params);
    }
}
