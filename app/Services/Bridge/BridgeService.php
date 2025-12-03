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
     * Create KYC link for new customer
     * @param string $fullName - Full name of individual or business legal name
     * @param string $email - Email address of customer
     * @param string $type - individual or business
     * @param array $additionalData - Optional data (endorsements, redirect_uri)
     * @param string|null $idempotencyKey - Optional existing key for retries
     * @return array
     */
    public function createKycLink(string $fullName, string $email, string $type = "individual", array $additionalData = [], ?string $idempotencyKey = null)
    {
        $endpoint = $this->base_url . "/kyc_links";
        $data = array_merge([
            'full_name' => $fullName,
            'email' => $email,
            'type' => $type
        ], $additionalData);

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
     * @param string $chain - blockchain: base, ethereum, solana
     * @param string|null $idempotencyKey - Optional existing key for retries
     * @return array
     */
    public function createWallet(string $customerId, string $chain = "ethereum", ?string $idempotencyKey = null)
    {
        $endpoint = $this->base_url . "/customers/{$customerId}/wallets";
        $data = ['chain' => $chain];

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
     * @param string $onBehalfOf - Customer ID on whose behalf the transfer is made
     * @param array $source - Source configuration (currency, payment_rail, external_account_id, bridge_wallet_id, from_address, etc.)
     * @param array $destination - Destination configuration (currency, payment_rail, external_account_id, bridge_wallet_id, to_address, wire_message, etc.)
     * @param string|null $amount - Amount as decimal string (required unless flexible_amount feature is enabled)
     * @param string|null $clientReferenceId - Client reference ID
     * @param string|null $developerFee - Developer fee as decimal string
     * @param string|null $developerFeePercent - Developer fee percent as decimal string (0.0 to 100.0)
     * @param bool|null $dryRun - Validate transfer route without creating transfer
     * @param array|null $features - Transfer features (flexible_amount, static_template, allow_any_from_address)
     * @param string|null $idempotencyKey - Optional existing key for retries
     * @return array
     */
    public function createTransfer(
        string $onBehalfOf,
        array $source,
        array $destination,
        ?string $amount = null,
        ?string $clientReferenceId = null,
        ?string $developerFee = null,
        ?string $developerFeePercent = null,
        ?bool $dryRun = null,
        ?array $features = null,
        ?string $idempotencyKey = null
    ) {
        $endpoint = $this->base_url . "/transfers";
        
        $data = [
            'on_behalf_of' => $onBehalfOf,
            'source' => $source,
            'destination' => $destination
        ];

        if ($amount !== null) {
            $data['amount'] = $amount;
        }

        if ($clientReferenceId !== null) {
            $data['client_reference_id'] = $clientReferenceId;
        }

        if ($developerFee !== null) {
            $data['developer_fee'] = $developerFee;
        }

        if ($developerFeePercent !== null) {
            $data['developer_fee_percent'] = $developerFeePercent;
        }

        if ($dryRun !== null) {
            $data['dry_run'] = $dryRun;
        }

        if ($features !== null) {
            $data['features'] = $features;
        }

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
     * @param string $currency - Currency code (usd, eur, mxn, brl, etc.)
     * @param string $accountType - Account type (us, iban, swift, clabe, pix)
     * @param array $accountData - Account data including bank details, owner info, address, etc.
     * @param string|null $idempotencyKey - Optional existing key for retries
     * @return array
     */
    public function createExternalAccount(
        string $customerId,
        string $currency,
        string $accountType,
        array $accountData = [],
        ?string $idempotencyKey = null
    ) {
        $endpoint = $this->base_url . "/customers/{$customerId}/external_accounts";
        $data = array_merge([
            'currency' => $currency,
            'account_type' => $accountType
        ], $accountData);

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
     * Create a virtual account for customer
     * @param string $customerId
     * @param array $source - Source configuration (currency: usd, eur, mxn, brl)
     * @param array $destination - Destination configuration (currency, payment_rail, address)
     * @param string|null $developerFeePercent - Developer fee percent as decimal string
     * @param string|null $idempotencyKey - Optional existing key for retries
     * @return array
     */
    public function createVirtualAccount(
        string $customerId,
        array $source,
        array $destination,
        ?string $developerFeePercent = null,
        ?string $idempotencyKey = null
    ) {
        $endpoint = $this->base_url . "/customers/{$customerId}/virtual_accounts";
        $data = [
            'source' => $source,
            'destination' => $destination
        ];

        if ($developerFeePercent !== null) {
            $data['developer_fee_percent'] = $developerFeePercent;
        }

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
     * Create a card account for customer
     * @param string $customerId
     * @param array $cardAccountData - Card account data (currency, chain, crypto_account, client_reference_id, etc.)
     * @param string|null $idempotencyKey - Optional existing key for retries
     * @return array
     */
    public function createCard(string $customerId, array $cardAccountData = [], ?string $idempotencyKey = null)
    {
        $endpoint = $this->base_url . "/customers/{$customerId}/card_accounts";
        return $this->postWithIdempotency($endpoint, $cardAccountData, 'create_card_account', $idempotencyKey);
    }

    /**
     * List customer card accounts
     * @param string $customerId
     * @param int $limit
     * @param string $cursor - Pagination cursor
     * @return array
     */
    public function getCustomerCards(string $customerId, int $limit = 100, string $cursor = null)
    {
        $endpoint = $this->base_url . "/customers/{$customerId}/card_accounts";
        $params = ['limit' => $limit];

        if ($cursor) {
            $params['cursor'] = $cursor;
        }

        return $this->http()->get($endpoint, $params);
    }

    /**
     * Get card account details
     * @param string $cardAccountId
     * @return array
     */
    public function getCard(string $cardAccountId)
    {
        $endpoint = $this->base_url . "/card_accounts/{$cardAccountId}";
        return $this->http()->get($endpoint);
    }

    /**
     * Update card account
     * @param string $cardAccountId
     * @param array $data
     * @return array
     */
    public function updateCard(string $cardAccountId, array $data)
    {
        $endpoint = $this->base_url . "/card_accounts/{$cardAccountId}";
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
     * Create liquidation address for customer
     * @param string $customerId
     * @param array $data - Liquidation address data (currency, chain, destination details, etc.)
     * @param string|null $idempotencyKey - Optional existing key for retries
     * @return array
     */
    public function createLiquidationAddress(string $customerId, array $data, ?string $idempotencyKey = null)
    {
        $endpoint = $this->base_url . "/customers/{$customerId}/liquidation_addresses";
        return $this->postWithIdempotency($endpoint, $data, 'create_liquidation_address', $idempotencyKey);
    }

    /**
     * List customer liquidation addresses
     * @param string $customerId
     * @param int $limit
     * @param string $cursor - Pagination cursor
     * @return array
     */
    public function getCustomerLiquidationAddresses(string $customerId, int $limit = 100, string $cursor = null)
    {
        $endpoint = $this->base_url . "/customers/{$customerId}/liquidation_addresses";
        $params = ['limit' => $limit];

        if ($cursor) {
            $params['cursor'] = $cursor;
        }

        return $this->http()->get($endpoint, $params);
    }

    /**
     * Get liquidation address details
     * @param string $customerId
     * @param string $liquidationAddressId
     * @return array
     */
    public function getLiquidationAddress(string $customerId, string $liquidationAddressId)
    {
        $endpoint = $this->base_url . "/customers/{$customerId}/liquidation_addresses/{$liquidationAddressId}";
        return $this->http()->get($endpoint);
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
