<?php

namespace App\Services\Flutterwave;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Flutterwave v4 API Service
 *
 * Handles OAuth2 client_credentials token lifecycle and exposes
 * typed methods for every API surface covered by the integration:
 *   - Customers
 *   - Payment Methods (card + mobile money)
 *   - Charges (collections / pay-ins)
 *   - Direct Transfers (payouts)
 *   - Refunds
 *   - Bank Account Resolution
 */
class FlutterwaveService
{
    private const TOKEN_URL      = 'https://idp.flutterwave.com/realms/flutterwave/protocol/openid-connect/token';
    private const CACHE_KEY      = 'flutterwave_access_token';
    private const TOKEN_TTL      = 540; // cache for 9 min; token valid 10 min

    private string $baseUrl;
    private string $clientId;
    private string $clientSecret;
    private string $encryptionKey;

    public function __construct()
    {
        $this->baseUrl       = rtrim(env('FLW_BASE_URL', 'https://developersandbox-api.flutterwave.com'), '/');
        $this->clientId      = env('FLW_CLIENT_ID', '');
        $this->clientSecret  = env('FLW_CLIENT_SECRET', '');
        $this->encryptionKey = env('FLW_ENCRYPTION_KEY', '');
    }

    // -------------------------------------------------------------------------
    // Token Management
    // -------------------------------------------------------------------------

    /**
     * Return a valid Bearer access token, refreshing from Flutterwave if needed.
     */
    public function getAccessToken(): string
    {
        return Cache::remember(self::CACHE_KEY, self::TOKEN_TTL, function () {
            $response = Http::asForm()->post(self::TOKEN_URL, [
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type'    => 'client_credentials',
            ]);

            if (!$response->successful()) {
                Log::error('Flutterwave token request failed', ['body' => $response->body()]);
                throw new RuntimeException('Unable to obtain Flutterwave access token: ' . $response->body());
            }

            return $response->json('access_token');
        });
    }

    /**
     * Force a fresh token on the next call (e.g. after a 401 response).
     */
    public function forgetToken(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    // -------------------------------------------------------------------------
    // HTTP Client
    // -------------------------------------------------------------------------

    private function http(array $extraHeaders = []): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withHeaders(array_merge([
            'Authorization'    => 'Bearer ' . $this->getAccessToken(),
            'Content-Type'     => 'application/json',
            'X-Trace-Id'       => $this->traceId(),
        ], $extraHeaders));
    }

    private function traceId(): string
    {
        return 'nuage-' . substr(md5(uniqid('', true)), 0, 16);
    }

    private function idempotencyKey(): string
    {
        return 'idem-' . substr(md5(uniqid('', true)), 0, 20);
    }

    /**
     * Make an API call, retrying once with a fresh token on 401.
     */
    private function call(string $method, string $path, array $body = [], array $extra = []): array
    {
        $url      = $this->baseUrl . $path;
        $response = $this->http($extra)->$method($url, $body);

        if ($response->status() === 401) {
            $this->forgetToken();
            $response = $this->http($extra)->$method($url, $body);
        }

        $data = $response->json() ?? [];

        if (!$response->successful()) {
            Log::error('Flutterwave API error', [
                'method' => strtoupper($method),
                'url'    => $url,
                'status' => $response->status(),
                'body'   => $data,
            ]);
        }

        return $data;
    }

    // -------------------------------------------------------------------------
    // Customers
    // -------------------------------------------------------------------------

    public function createCustomer(array $payload): array
    {
        return $this->call('post', '/customers', $payload);
    }

    public function getCustomer(string $customerId): array
    {
        return $this->call('get', "/customers/{$customerId}");
    }

    // -------------------------------------------------------------------------
    // Payment Methods
    // -------------------------------------------------------------------------

    /**
     * Create a card payment method (card fields must be pre-encrypted).
     */
    public function createCardPaymentMethod(array $cardData): array
    {
        $extra = ['X-Idempotency-Key' => $this->idempotencyKey()];
        return $this->call('post', '/payment-methods', [
            'type' => 'card',
            'card' => $cardData,
        ], $extra);
    }

    /**
     * Create a mobile money payment method.
     *
     * @param  array $momo  [country_code, network, phone_number]
     */
    public function createMoMoPaymentMethod(array $momo): array
    {
        $extra = ['X-Idempotency-Key' => $this->idempotencyKey()];
        return $this->call('post', '/payment-methods', [
            'type'         => 'mobile_money',
            'mobile_money' => $momo,
        ], $extra);
    }

    // -------------------------------------------------------------------------
    // Charges (Pay-ins)
    // -------------------------------------------------------------------------

    public function createCharge(array $payload): array
    {
        $extra = ['X-Idempotency-Key' => $this->idempotencyKey()];
        return $this->call('post', '/charges', $payload, $extra);
    }

    /**
     * Authorize a pending charge (PIN, OTP, AVS, etc.)
     *
     * @param  string $chargeId
     * @param  array  $authorization  e.g. ['type' => 'pin', 'pin' => ['nonce' => '...', 'encrypted_pin' => '...']]
     */
    public function authorizeCharge(string $chargeId, array $authorization): array
    {
        return $this->call('put', "/charges/{$chargeId}", ['authorization' => $authorization]);
    }

    public function getCharge(string $chargeId): array
    {
        return $this->call('get', "/charges/{$chargeId}");
    }

    // -------------------------------------------------------------------------
    // Direct Transfers (Payouts)
    // -------------------------------------------------------------------------

    public function createTransfer(array $payload): array
    {
        $extra = ['X-Idempotency-Key' => $this->idempotencyKey()];
        return $this->call('post', '/direct-transfers', $payload, $extra);
    }

    public function getTransfer(string $transferId): array
    {
        return $this->call('get', "/direct-transfers/{$transferId}");
    }

    /**
     * Resolve a bank account before initiating a transfer.
     *
     * @param  array $payload  [bank_code, account_number, currency]
     */
    public function resolveBankAccount(array $payload): array
    {
        return $this->call('post', '/banks/account-resolve', $payload);
    }

    // -------------------------------------------------------------------------
    // Refunds
    // -------------------------------------------------------------------------

    public function createRefund(array $payload): array
    {
        $extra = ['X-Idempotency-Key' => $this->idempotencyKey()];
        return $this->call('post', '/refunds', $payload, $extra);
    }

    public function getRefund(string $refundId): array
    {
        return $this->call('get', "/refunds/{$refundId}");
    }

    /**
     * @param  int    $page
     * @param  int    $size
     * @param  string|null $from  ISO date string
     * @param  string|null $to    ISO date string
     */
    public function listRefunds(int $page = 1, int $size = 10, ?string $from = null, ?string $to = null): array
    {
        $query = array_filter(compact('page', 'size', 'from', 'to'));
        $qs    = $query ? '?' . http_build_query($query) : '';
        return $this->call('get', "/refunds{$qs}");
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** Encrypt card data using the configured encryption key. */
    public function encryptCard(array $card): array
    {
        if (empty($this->encryptionKey)) {
            throw new RuntimeException('FLW_ENCRYPTION_KEY is not set.');
        }
        return FlutterwaveEncryption::encryptCard($card, $this->encryptionKey);
    }
}
