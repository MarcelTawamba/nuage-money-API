<?php

namespace App\Services\Korapay;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KorapayService
{
    private $base_url;
    private $public_key;
    private $secret_key;

    public function __construct()
    {
        $this->base_url = env("KORAPAY_BASE_URL", "https://api.korapay.com/merchant/api/v1");
        $this->public_key = env("KORAPAY_PUBLIC_KEY");
        $this->secret_key = env("KORAPAY_SECRET_KEY");
    }

    /**
     * @return \Illuminate\Http\Client\PendingRequest
     */
    private function http()
    {
        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->secret_key
        ]);
    }

    /**
     * Get account balances
     * @return array
     */
    public function getBalances()
    {
        // Per intro doc: https://developers.korapay.com/docs/balance-api
        // Endpoint: GET https://api.korapay.com/merchant/api/v1/balances
        
        $path = "/balances";
        $response = $this->http()->get($this->base_url . $path);
        
        Log::info('Korapay getBalances response', [
            'status' => $response->status(),
            'body' => $response->json()
        ]);

        return $response->json();
    }

    /**
     * Resolve Bank Account
     * @param array $payload
     * @return array
     */
    public function resolveBankAccount(array $payload)
    {
        // Docs: https://developers.korapay.com/docs/bank-account-verification-for-nigerian-and-kenyan-banks
        // Endpoint: POST /misc/banks/resolve
        // Required: bank (code), account (number), currency (optional?)
        
        $path = "/misc/banks/resolve";
        
        // Map common keys to Korapay specific keys if needed, or rely on caller to match.
        // It's safer to map if we know the input.
        // However, I will assume the input payload is already formatted or I can reformat here.
        // Based on my Controller, I receive bank_code and account_number. 
        // Docs say body should have: bank, account.
        
        $body = [
            'bank' => $payload['bank_code'] ?? $payload['bank'], 
            'account' => $payload['account_number'] ?? $payload['account'],
            'currency' => $payload['currency'] ?? 'NGN' // Defaulting to NGN as common use case? Or check requirement.
        ];

        $response = $this->http()->post($this->base_url . $path, $body);
        
        return $response->json();
    }

    /**
     * Get List of Banks
     * @param string $countryCode
     * @return array
     */
    public function getBanks(string $countryCode = 'NG')
    {
        // Endpoint: GET /misc/banks?countryCode=NG
        $path = "/misc/banks";
        $response = $this->http()->get($this->base_url . $path, [
            'countryCode' => $countryCode
        ]);
        
        return $response->json();
    }

    /**
     * Initiate Payout (Transfer)
     * @param array $payload (reference, amount, currency, destination[type, bank_account/mobile_money...], customer[email])
     * @return array
     */
    public function initiateTransfer(array $payload)
    {
        // Docs: https://developers.korapay.com/docs/payout-via-api
        // Endpoint: POST /transactions/disburse
        
        $path = "/transactions/disburse";
        
        Log::info('Korapay initiateTransfer request', ['payload' => $payload]);

        $response = $this->http()->post($this->base_url . $path, $payload);

        Log::info('Korapay initiateTransfer response', [
            'status' => $response->status(),
            'body' => $response->json()
        ]);

        return $response->json();
    }

    /**
     * Get Transfer Status
     * @param string $reference
     * @return array
     */
    public function getTransferStatus(string $reference)
    {
        // Docs: https://developers.korapay.com/docs/get-transaction-status
        // Endpoint: GET /transactions/{reference}
        
        $path = "/transactions/" . $reference;
        $response = $this->http()->get($this->base_url . $path);
        
        return $response->json();
    }

    /**
     * Initialize Checkout (Pay-in)
     * @param array $payload (reference, amount, currency, customer[name, email], redirect_url, notification_url, narration, channels, default_channel, metadata)
     * @return array
     */
    public function initializeCheckout(array $payload)
    {
        // Docs: https://developers.korapay.com/docs/checkout-redirect
        // Endpoint: POST /charges/initialize
        
        $path = "/charges/initialize";
        
        Log::info('Korapay initializeCheckout request', ['payload' => $payload]);

        $response = $this->http()->post($this->base_url . $path, $payload);
        
        Log::info('Korapay initializeCheckout response', [
            'status' => $response->status(),
            'body' => $response->json()
        ]);

        return $response->json();
    }

    /**
     * Verify Transaction
     * @param string $reference
     * @return array
     */
    public function verifyTransaction(string $reference)
    {
        // Docs: GET /charges/{reference}
        $path = "/charges/" . $reference;
        $response = $this->http()->get($this->base_url . $path);
        return $response->json();
    }
}
