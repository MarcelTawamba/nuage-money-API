<?php

namespace App\Services;

use App\Services\StartButton\AfricaService;
use App\Services\VALR\ValrService;
use App\Services\Bridge\BridgeService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class RateComparisonService
{
    private const CACHE_TTL = 60; // Cache rates for 60 seconds

    /**
     * Get the best exchange rate for a currency pair
     * @param string $fromCurrency - Source currency (e.g., USD, NGN)
     * @param string $toCurrency - Target currency (e.g., ZAR, GHS)
     * @param float $amount - Amount to convert
     * @return array - ['provider' => 'valr|bridge|startbutton', 'rate' => float, 'amount_received' => float]
     */
    public function getBestRate(string $fromCurrency, string $toCurrency, float $amount): array
    {
        $cacheKey = "fx_rates_{$fromCurrency}_{$toCurrency}";
        
        $rates = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($fromCurrency, $toCurrency, $amount) {
            return $this->fetchRatesFromAllProviders($fromCurrency, $toCurrency, $amount);
        });

        if (empty($rates)) {
            Log::warning('No rates available from any provider', [
                'from' => $fromCurrency,
                'to' => $toCurrency
            ]);
            
            // Default to StartButton if no rates available
            return [
                'provider' => 'startbutton',
                'rate' => null,
                'amount_received' => null,
                'error' => 'No rates available'
            ];
        }

        // Find the best rate (highest amount received)
        $bestRate = collect($rates)->sortByDesc('amount_received')->first();

        Log::info('Best rate selected', [
            'from' => $fromCurrency,
            'to' => $toCurrency,
            'amount' => $amount,
            'best_provider' => $bestRate['provider'],
            'rate' => $bestRate['rate'],
            'all_rates' => $rates
        ]);

        return $bestRate;
    }

    /**
     * Fetch rates from all available providers
     * @param string $fromCurrency
     * @param string $toCurrency
     * @param float $amount
     * @return array
     */
    private function fetchRatesFromAllProviders(string $fromCurrency, string $toCurrency, float $amount): array
    {
        $rates = [];

        // Fetch from VALR (using stablecoin pairs for FX rates)
        try {
            $valrRate = $this->getValrRate($fromCurrency, $toCurrency, $amount);
            if ($valrRate) {
                $rates[] = $valrRate;
            }
        } catch (\Exception $e) {
            Log::error('Error fetching VALR rate', ['error' => $e->getMessage()]);
        }

        // Fetch from Bridge
        try {
            $bridgeRate = $this->getBridgeRate($fromCurrency, $toCurrency, $amount);
            if ($bridgeRate) {
                $rates[] = $bridgeRate;
            }
        } catch (\Exception $e) {
            Log::error('Error fetching Bridge rate', ['error' => $e->getMessage()]);
        }

        // Fetch from Fincra
        try {
            $fincraRate = $this->getFincraRate($fromCurrency, $toCurrency, $amount);
            if ($fincraRate) {
                $rates[] = $fincraRate;
            }
        } catch (\Exception $e) {
            Log::error('Error fetching Fincra rate', ['error' => $e->getMessage()]);
        }

        // StartButton is always available as fallback
        $rates[] = [
            'provider' => 'startbutton',
            'rate' => null, // StartButton doesn't expose rates via API
            'amount_received' => null,
            'available' => true
        ];

        return $rates;
    }

    /**
     * Get exchange rate from VALR using stablecoin pairs
     * @param string $fromCurrency
     * @param string $toCurrency
     * @param float $amount
     * @return array|null
     */
    private function getValrRate(string $fromCurrency, string $toCurrency, float $amount): ?array
    {
        $valrService = new ValrService();

        // Map fiat currencies to stablecoins for proxy rates
        $stablecoinMap = [
            'USD' => 'USDT',
            'EUR' => 'USDC', // or use USDT
        ];

        $fromStablecoin = $stablecoinMap[$fromCurrency] ?? null;
        
        // VALR primarily supports ZAR pairs
        if ($toCurrency !== 'ZAR' || !$fromStablecoin) {
            return null;
        }

        $pair = $fromStablecoin . $toCurrency; // e.g., USDTZAR

        try {
            // Get market data for the pair
            $marketDataResponse = $valrService->getMarketData();
            
            if (empty($marketDataResponse) || !is_array($marketDataResponse)) {
                return null;
            }

            $marketData = $marketDataResponse;
            
            // Find the specific pair
            $pairData = collect($marketData)->firstWhere('currencyPair', $pair);
            
            if (!$pairData) {
                return null;
            }

            // Use the last traded price or best ask
            $rate = floatval($pairData['lastTradedPrice'] ?? $pairData['askPrice'] ?? 0);
            
            if ($rate <= 0) {
                return null;
            }

            $amountReceived = $amount * $rate;

            return [
                'provider' => 'valr',
                'rate' => $rate,
                'amount_received' => $amountReceived,
                'pair' => $pair,
                'available' => true
            ];
        } catch (\Exception $e) {
            Log::error('VALR rate fetch error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get exchange rate from Bridge (if they provide conversion rates)
     * @param string $fromCurrency
     * @param string $toCurrency
     * @param float $amount
     * @return array|null
     */
    private function getBridgeRate(string $fromCurrency, string $toCurrency, float $amount): ?array
    {
        // Bridge doesn't have a public rate endpoint, but you could:
        // 1. Create a quote for a transfer to get the rate
        // 2. Use their liquidity balances to infer rates
        // For now, return null as Bridge rates need to be fetched per-transaction
        
        return null;
    }

    /**
     * Get exchange rate from Fincra using their quote API
     * @param string $fromCurrency
     * @param string $toCurrency
     * @param float $amount
     * @return array|null
     */
    private function getFincraRate(string $fromCurrency, string $toCurrency, float $amount): ?array
    {
        // Check if Fincra is configured
        if (empty(env('FINCRA_API_KEY'))) {
            return null;
        }

        $fincraService = new \App\Services\Fincra\FincraService();

        try {
            // Generate quote for conversion
            $quote = $fincraService->generateQuote(
                $fromCurrency,
                $toCurrency,
                $amount,
                'conversion',
                'fliqpay_wallet'
            );
            
            if (!$quote) {
                return null;
            }

            $rate = floatval($quote->rate ?? 0);
            $amountReceived = floatval($quote->destinationAmount ?? 0);
            
            if ($rate <= 0 || $amountReceived <= 0) {
                return null;
            }

            return [
                'provider' => 'fincra',
                'rate' => $rate,
                'amount_received' => $amountReceived,
                'quote_reference' => $quote->reference ?? null,
                'expires_at' => $quote->expireAt ?? null,
                'available' => true
            ];
        } catch (\Exception $e) {
            Log::error('Fincra rate fetch error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Check if a provider supports a currency pair
     * @param string $provider
     * @param string $fromCurrency
     * @param string $toCurrency
     * @return bool
     */
    public function providerSupportsPair(string $provider, string $fromCurrency, string $toCurrency): bool
    {
        return match($provider) {
            'valr' => $this->valrSupportsPair($fromCurrency, $toCurrency),
            'bridge' => $this->bridgeSupportsPair($fromCurrency, $toCurrency),
            'fincra' => $this->fincraSupportsPair($fromCurrency, $toCurrency),
            'startbutton' => true, // StartButton supports most African currencies
            default => false
        };
    }

    /**
     * Check if VALR supports the currency pair
     */
    private function valrSupportsPair(string $fromCurrency, string $toCurrency): bool
    {
        $supportedPairs = [
            'USD' => ['ZAR'],
            'USDT' => ['ZAR'],
            'USDC' => ['ZAR'],
            'BTC' => ['ZAR'],
            'ETH' => ['ZAR'],
        ];

        return isset($supportedPairs[$fromCurrency]) && 
               in_array($toCurrency, $supportedPairs[$fromCurrency]);
    }

    /**
     * Check if Bridge supports the currency pair
     */
    private function bridgeSupportsPair(string $fromCurrency, string $toCurrency): bool
    {
        // Bridge supports USD, EUR, MXN for virtual accounts
        // And various stablecoins for wallets
        $supportedCurrencies = ['USD', 'EUR', 'MXN', 'USDC', 'USDT'];
        
        return in_array($fromCurrency, $supportedCurrencies) && 
               in_array($toCurrency, $supportedCurrencies);
    }

    /**
     * Check if Fincra supports the currency pair
     */
    private function fincraSupportsPair(string $fromCurrency, string $toCurrency): bool
    {
        // Fincra supports: NGN, USD, EUR, GBP, GHS, KES, ZAR, and more
        $supportedCurrencies = ['NGN', 'USD', 'EUR', 'GBP', 'GHS', 'KES', 'ZAR', 'UGX', 'RWF', 'XOF', 'XAF'];
        
        return in_array($fromCurrency, $supportedCurrencies) && 
               in_array($toCurrency, $supportedCurrencies);
    }
}
