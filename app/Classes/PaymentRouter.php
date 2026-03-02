<?php

namespace App\Classes;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PaymentRouter
{
    public function __construct()
    {
    }

    /**
     * Route payout to the appropriate provider based on metadata and requirements
     * @param array $data - Webhook data
     * @return JsonResponse
     */
    public function routePayout(array $data): JsonResponse
    {
        Log::info('PaymentRouter: Routing payout request', [
            'currency' => $data['currency'],
            'amount' => $data['amount'],
            'country' => $data['country'],
            'has_metadata' => !empty($data['metadata'])
        ]);

        // Validate metadata exists and has destination details
        if (empty($data['metadata'])) {
            Log::warning('Missing metadata in payout request, defaulting to StartButton');
            return StartButtonAfricaPaymentHelper::initPayout($data);
        }

        // Check if any provider can handle this payout based on metadata
        if (!$this->hasAnyProviderRequirements($data)) {
            Log::error('Missing required payout destination details in metadata', [
                'metadata' => $data['metadata'],
                'ref_id' => $data['ref_id'] ?? null
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Missing required payout destination details. Please provide bank account, mobile money, or crypto address information.',
                'ref_id' => $data['ref_id'] ?? null,
                'pay_token' => null,
                'status' => 'failed'
            ]);
        }

        // Route to the most appropriate provider based on metadata requirements
        // Priority order: VALR (crypto) > Bridge (multi-method) > StartButton (fallback)
        $provider = $this->selectBestProvider($data);
        
        Log::info('Selected provider for payout', [
            'provider' => $provider,
            'currency' => $data['currency'],
            'amount' => $data['amount']
        ]);

        // Route to appropriate payment helper
        return $this->routeToProvider($provider, $data);
    }

    /**
     * Select the best provider based on metadata requirements and configuration
     * @param array $data
     * @return string
     */
    private function selectBestProvider(array $data): string
    {
        // Priority 1: VALR for crypto payouts
        if ($this->hasValrRequirements($data) && $this->isValrConfigured()) {
            return 'valr';
        }

        // Priority 2: Bridge for multi-rail payouts (bank, wallet, crypto)
        if ($this->hasBridgeRequirements($data) && $this->isBridgeConfigured()) {
            return 'bridge';
        }

        // Priority 3: YellowCard for African markets (bank/mobile money)
        if ($this->hasYellowCardRequirements($data) && $this->isYellowCardConfigured()) {
            return 'yellowcard';
        }

        // Priority 4: Korapay for African markets (bank/mobile money)
        if ($this->hasKorapayRequirements($data) && $this->isKorapayConfigured()) {
            return 'korapay';
        }

        // Priority 5: Fincra for fiat bank/mobile money payouts
        // if ($this->hasFincraRequirements($data) && $this->isFincraConfigured()) {
        //     return 'fincra';
        // }

        // Priority 6: StartButton as fallback for bank/mobile money
        return 'startbutton';
    }

    /**
     * Route to the selected payment provider
     * @param string $provider
     * @param array $data
     * @return JsonResponse
     */
    private function routeToProvider(string $provider, array $data): JsonResponse
    {
        return match($provider) {
            'valr' => $this->routeToValr($data),
            'bridge' => $this->routeToBridge($data),
            'yellowcard' => $this->routeToYellowCard($data),
            'korapay' => $this->routeToKorapay($data),
            //'fincra' => $this->routeToFincra($data),
            'startbutton' => $this->routeToStartButton($data),
            default => $this->routeToStartButton($data), // Fallback to StartButton
        };
    }

    /**
     * Route to VALR for payout
     */
    private function routeToValr(array $data): JsonResponse
    {
        Log::info('Routing to VALR');
        
        // Check if VALR configuration is available
        if (!$this->isValrConfigured()) {
            Log::warning('VALR not configured, falling back to StartButton');
            return $this->routeToStartButton($data);
        }

        // Ensure we have the necessary metadata for VALR
        if (!$this->hasValrRequirements($data)) {
            Log::warning('Missing VALR requirements, falling back to StartButton');
            return $this->routeToStartButton($data);
        }

        try {
            return ValrPaymentHelper::initPayout($data);
        } catch (\Exception $e) {
            Log::error('VALR payout failed, falling back to StartButton', [
                'error' => $e->getMessage()
            ]);
            return $this->routeToStartButton($data);
        }
    }

    /**
     * Route to Bridge for payout
     */
    private function routeToBridge(array $data): JsonResponse
    {
        Log::info('Routing to Bridge');
        
        // Check if Bridge configuration is available
        if (!$this->isBridgeConfigured()) {
            Log::warning('Bridge not configured, falling back to StartButton');
            return $this->routeToStartButton($data);
        }

        // Ensure we have the necessary metadata for Bridge
        if (!$this->hasBridgeRequirements($data)) {
            Log::warning('Missing Bridge requirements, falling back to StartButton');
            return $this->routeToStartButton($data);
        }

        try {
            return BridgePaymentHelper::initPayout($data);
        } catch (\Exception $e) {
            Log::error('Bridge payout failed, falling back to StartButton', [
                'error' => $e->getMessage()
            ]);
            return $this->routeToStartButton($data);
        }
    }

    /**
     * Route to YellowCard for payout
     */
    private function routeToYellowCard(array $data): JsonResponse
    {
        Log::info('Routing to YellowCard');
        
        if (!$this->isYellowCardConfigured()) {
            Log::warning('YellowCard not configured, falling back to StartButton');
            return $this->routeToStartButton($data);
        }

        if (!$this->hasYellowCardRequirements($data)) {
            Log::warning('Missing YellowCard requirements, falling back to StartButton');
            return $this->routeToStartButton($data);
        }

        try {
            return YellowCardPaymentHelper::initPayout($data);
        } catch (\Exception $e) {
            Log::error('YellowCard payout failed, falling back to StartButton', [
                'error' => $e->getMessage()
            ]);
            return $this->routeToStartButton($data);
        }
    }

    /**
     * Route to Korapay for payout
     */
    private function routeToKorapay(array $data): JsonResponse
    {
        Log::info('Routing to Korapay');
        
        if (!$this->isKorapayConfigured()) {
            Log::warning('Korapay not configured, falling back to StartButton');
            return $this->routeToStartButton($data);
        }

        if (!$this->hasKorapayRequirements($data)) {
            Log::warning('Missing Korapay requirements, falling back to StartButton');
            return $this->routeToStartButton($data);
        }

        try {
            return KorapayPaymentHelper::initPayout($data);
        } catch (\Exception $e) {
            Log::error('Korapay payout failed, falling back to StartButton', [
                'error' => $e->getMessage()
            ]);
            return $this->routeToStartButton($data);
        }
    }

    /**
     * Route to Fincra for payout
     */
    private function routeToFincra(array $data): JsonResponse
    {
        Log::info('Routing to Fincra');
        
        // Check if Fincra configuration is available
        if (!$this->isFincraConfigured()) {
            Log::warning('Fincra not configured, falling back to StartButton');
            return $this->routeToStartButton($data);
        }

        // Ensure we have the necessary metadata for Fincra
        if (!$this->hasFincraRequirements($data)) {
            Log::warning('Missing Fincra requirements, falling back to StartButton');
            return $this->routeToStartButton($data);
        }

        try {
            return FincraPaymentHelper::initPayout($data);
        } catch (\Exception $e) {
            Log::error('Fincra payout failed, falling back to StartButton', [
                'error' => $e->getMessage()
            ]);
            return $this->routeToStartButton($data);
        }
    }

    /**
     * Route to StartButton (default/fallback)
     */
    private function routeToStartButton(array $data): JsonResponse
    {
        Log::info('Routing to StartButton');
        return StartButtonAfricaPaymentHelper::initPayout($data);
    }

    /**
     * Check if VALR is configured
     */
    private function isValrConfigured(): bool
    {
        return !empty(env('VALR_API_KEY')) && !empty(env('VALR_API_SECRET'));
    }

    /**
     * Check if Bridge is configured
     */
    private function isBridgeConfigured(): bool
    {
        return !empty(env('BRIDGE_API_KEY'));
    }

    /**
     * Check if YellowCard is configured
     */
    private function isYellowCardConfigured(): bool
    {
        return !empty(env('YELLOWCARD_API_KEY'));
    }

    /**
     * Check if Korapay is configured
     */
    private function isKorapayConfigured(): bool
    {
        return !empty(env('KORAPAY_SECRET_KEY'));
    }

    /**
     * Check if Fincra is configured
     */
    private function isFincraConfigured(): bool
    {
        return !empty(env('FINCRA_API_KEY'));
    }

    /**
     * Check if data has VALR requirements (crypto address)
     */
    private function hasValrRequirements(array $data): bool
    {
        return !empty($data['metadata']['crypto_address']) && 
               !empty($data['metadata']['crypto_currency']);
    }

    /**
     * Check if data has Bridge requirements (destination info)
     */
    private function hasBridgeRequirements(array $data): bool
    {
        return !empty($data['metadata']['dest_external_account_id']) ||
               !empty($data['metadata']['dest_wallet_id']) ||
               !empty($data['metadata']['dest_crypto_address']);
    }

    /**
     * Check if data has YellowCard requirements (bank/mobile money for African markets)
     */
    private function hasYellowCardRequirements(array $data): bool
    {
        $africanCountries = ['NG', 'GH', 'KE', 'UG', 'ZA', 'TZ', 'RW'];
        $isAfricanMarket = in_array($data['country'] ?? '', $africanCountries);
        
        return $isAfricanMarket && (
            (!empty($data['metadata']['bank_code']) && !empty($data['metadata']['dest_account_number'])) ||
            !empty($data['metadata']['phone_number'])
        );
    }

    /**
     * Check if data has Korapay requirements (bank or mobile money details)
     */
    private function hasKorapayRequirements(array $data): bool
    {
        return (!empty($data['metadata']['bank_code']) && !empty($data['metadata']['dest_account_number'])) ||
               (!empty($data['metadata']['account_number'])) ||
               (!empty($data['metadata']['destination']));
    }

    /**
     * Check if data has Fincra requirements (bank or mobile money details)
     */
    private function hasFincraRequirements(array $data): bool
    {
        return (!empty($data['metadata']['bank_code']) && !empty($data['metadata']['dest_account_number'])) ||
               (!empty($data['metadata']['MNO']) && !empty($data['metadata']['msisdn']));
    }

    /**
     * Check if data has StartButton requirements (bank or mobile money details)
     */
    private function hasStartButtonRequirements(array $data): bool
    {
        return (!empty($data['metadata']['bank_code']) && !empty($data['metadata']['dest_account_number'])) ||
               (!empty($data['metadata']['MNO']) && !empty($data['metadata']['msisdn']));
    }

    /**
     * Check if any provider has the required metadata to process payout
     * @param array $data
     * @return bool
     */
    private function hasAnyProviderRequirements(array $data): bool
    {
        return $this->hasValrRequirements($data) ||
               $this->hasBridgeRequirements($data) ||
               $this->hasYellowCardRequirements($data) ||
               $this->hasKorapayRequirements($data) ||
            //    $this->hasFincraRequirements($data) ||
               $this->hasStartButtonRequirements($data);
    }

    /**
     * Get available providers for a payout based on metadata
     * @param array $data
     * @return array
     */
    public function getAvailableProviders(array $data): array
    {
        $providers = [];

        if ($this->hasValrRequirements($data) && $this->isValrConfigured()) {
            $providers[] = 'valr';
        }

        if ($this->hasBridgeRequirements($data) && $this->isBridgeConfigured()) {
            $providers[] = 'bridge';
        }

        if ($this->hasYellowCardRequirements($data) && $this->isYellowCardConfigured()) {
            $providers[] = 'yellowcard';
        }

        if ($this->hasKorapayRequirements($data) && $this->isKorapayConfigured()) {
            $providers[] = 'korapay';
        }

        // if ($this->hasFincraRequirements($data) && $this->isFincraConfigured()) {
        //     $providers[] = 'fincra';
        // }

        if ($this->hasStartButtonRequirements($data)) {
            $providers[] = 'startbutton';
        }

        return $providers;
    }
}
