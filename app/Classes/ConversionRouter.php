<?php

namespace App\Classes;

use App\Services\RateComparisonService;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletType;
use App\Models\Achat;
use App\Models\ClientWallet;
use App\Enums\PaymentStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ConversionRouter
{
    private RateComparisonService $rateComparisonService;

    public function __construct()
    {
        $this->rateComparisonService = new RateComparisonService();
    }

    /**
     * Route conversion to the best provider based on exchange rates
     * @param array $data - Webhook data for conversion
     * @return JsonResponse
     */
    public function routeConversion(array $data): JsonResponse
    {
        Log::info('ConversionRouter: Processing conversion request', [
            'from_currency' => $data['metadata']['source_currency'] ?? 'unknown',
            'to_currency' => $data['metadata']['dest_currency'] ?? 'unknown',
            'amount' => $data['amount'],
            'user_id' => $data['client_id']
        ]);

        // Extract conversion details from metadata
        $sourceCurrency = strtoupper($data['metadata']['source_currency'] ?? '');
        $destCurrency = strtoupper($data['metadata']['dest_currency'] ?? '');
        $amount = abs($data['amount']);

        if (empty($sourceCurrency) || empty($destCurrency)) {
            return response()->json([
                'success' => false,
                'message' => 'Missing source or destination currency in metadata',
                'ref_id' => $data['ref_id'] ?? null
            ]);
        }

        // Get best rate for conversion
        $bestRate = $this->rateComparisonService->getBestRate(
            $sourceCurrency,
            $destCurrency,
            $amount
        );

        Log::info('Best conversion rate determined', [
            'provider' => $bestRate['provider'],
            'rate' => $bestRate['rate'],
            'amount_received' => $bestRate['amount_received']
        ]);

        // Route to appropriate provider
        return match($bestRate['provider']) {
            'fincra' => $this->convertViaFincra($data, $bestRate),
            'bridge' => $this->convertViaBridge($data, $bestRate),
            'valr' => $this->convertViaValr($data, $bestRate),
            'startbutton' => $this->convertViaStartButton($data, $bestRate),
            default => $this->convertViaFincra($data, $bestRate) // Fallback
        };
    }

    private function convertViaFincra(array $data, array $rateInfo): JsonResponse
    {
        $fincraService = new \App\Services\Fincra\FincraService();
        
        try {
            $sourceCurrency = strtoupper($data['metadata']['source_currency']);
            $destCurrency = strtoupper($data['metadata']['dest_currency']);
            $amount = abs($data['amount']);

            // Use the quote reference from rate comparison if available
            $quoteReference = $rateInfo['quote_reference'] ?? null;

            if (!$quoteReference) {
                // Generate a new quote if we don't have one
                $quote = $fincraService->generateQuote(
                    $sourceCurrency,
                    $destCurrency,
                    $amount,
                    'conversion',
                    'fliqpay_wallet'
                );
                
                if (!$quote) {
                    throw new \Exception('Failed to generate Fincra quote');
                }
                
                $quoteReference = $quote->reference;
                $amountReceived = floatval($quote->destinationAmount ?? 0);
            } else {
                $amountReceived = $rateInfo['amount_received'];
            }
            
            // Execute the conversion
            $conversion = $fincraService->convertCurrency(
                $sourceCurrency,
                $destCurrency,
                $amount,
                $quoteReference
            );
            
            // Create conversion record
            $this->recordConversion($data, 'Fincra', $rateInfo['rate'], $amountReceived, $conversion->reference ?? null);
            
            // Update user wallet balances
            $this->updateWalletBalances($data, $amountReceived);
            
            return response()->json([
                'success' => true,
                'provider' => 'fincra',
                'rate' => $rateInfo['rate'],
                'source_amount' => $amount,
                'dest_amount' => $amountReceived,
                'conversion_reference' => $conversion->reference ?? null,
                'ref_id' => $data['ref_id']
            ]);
        } catch (\Exception $e) {
            Log::error('Fincra conversion failed', ['error' => $e->getMessage()]);
            
            // Try fallback to another provider
            return $this->fallbackConversion($data, $rateInfo);
        }
    }

    private function convertViaBridge(array $data, array $rateInfo): JsonResponse
    {
        Log::info('Bridge conversion - not yet fully implemented');
        
        // Bridge doesn't have a direct conversion API
        // Would need to use transfers between wallets
        return response()->json([
            'success' => false,
            'message' => 'Bridge conversion not yet implemented, trying fallback'
        ]);
    }

    private function convertViaValr(array $data, array $rateInfo): JsonResponse
    {
        Log::info('VALR conversion - not yet fully implemented');
        
        // VALR conversion would use simple order API (instant orders)
        return response()->json([
            'success' => false,
            'message' => 'VALR conversion not yet implemented, trying fallback'
        ]);
    }

    private function convertViaStartButton(array $data, array $rateInfo): JsonResponse
    {
        Log::info('StartButton conversion - not available');
        
        // StartButton doesn't have a conversion API
        return response()->json([
            'success' => false,
            'message' => 'StartButton does not support currency conversion'
        ]);
    }

    private function fallbackConversion(array $data, array $rateInfo): JsonResponse
    {
        Log::warning('Conversion failed, no more fallback options available');
        
        return response()->json([
            'success' => false,
            'message' => 'Currency conversion failed. Please try again later.',
            'ref_id' => $data['ref_id'] ?? null
        ]);
    }

    /**
     * Record conversion transaction in database
     */
    private function recordConversion(array $data, string $provider, float $rate, float $amountReceived, ?string $providerReference): void
    {
        try {
            $achat = new Achat();
            $achat->client_id = $data['client_id'];
            $achat->amount = abs($data['amount']); // Source amount
            $achat->country = $data['country'] ?? 'CONVERSION';
            $achat->currency = $data['metadata']['source_currency'];
            $achat->user_ref_id = $data['ref_id'];
            $achat->ref_id = 'CONV-' . \Illuminate\Support\Str::random(16);
            $achat->status = PaymentStatus::SUCCESSFUL;
            $achat->metadata = json_encode([
                'type' => 'conversion',
                'provider' => $provider,
                'source_currency' => $data['metadata']['source_currency'],
                'dest_currency' => $data['metadata']['dest_currency'],
                'rate' => $rate,
                'amount_received' => $amountReceived,
                'provider_reference' => $providerReference
            ]);
            $achat->save();
            
            Log::info('Conversion recorded', [
                'achat_id' => $achat->id,
                'ref_id' => $achat->ref_id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to record conversion', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Update user wallet balances after conversion
     */
    private function updateWalletBalances(array $data, float $amountReceived): void
    {
        try {
            $user = User::find($data['client_id']);
            
            if (!$user) {
                Log::error('User not found for wallet update', ['user_id' => $data['client_id']]);
                return;
            }

            $sourceCurrency = strtoupper($data['metadata']['source_currency']);
            $destCurrency = strtoupper($data['metadata']['dest_currency']);
            $sourceAmount = abs($data['amount']);

            // Find or create source currency wallet type
            $sourceWalletType = WalletType::firstOrCreate(
                ['name' => $sourceCurrency],
                ['decimals' => 2]
            );
            
            $sourceWallet = Wallet::firstOrCreate([
                'user_type' => ClientWallet::class,
                'user_id' => $user->id,
                'wallet_type_id' => $sourceWalletType->id
            ], [
                'balance' => 0
            ]);
            
            // Decrease source wallet
            $oldSourceBalance = $sourceWallet->balance;
            $sourceWallet->balance -= $sourceAmount;
            $sourceWallet->save();
            
            Log::info('Source wallet updated', [
                'user_id' => $user->id,
                'currency' => $sourceCurrency,
                'old_balance' => $oldSourceBalance,
                'decreased_by' => $sourceAmount,
                'new_balance' => $sourceWallet->balance
            ]);

            // Find or create destination currency wallet type
            $destWalletType = WalletType::firstOrCreate(
                ['name' => $destCurrency],
                ['decimals' => 2]
            );
            
            $destWallet = Wallet::firstOrCreate([
                'user_type' => ClientWallet::class,
                'user_id' => $user->id,
                'wallet_type_id' => $destWalletType->id
            ], [
                'balance' => 0
            ]);
            
            // Increase destination wallet
            $oldDestBalance = $destWallet->balance;
            $destWallet->balance += $amountReceived;
            $destWallet->save();
            
            Log::info('Destination wallet updated', [
                'user_id' => $user->id,
                'currency' => $destCurrency,
                'old_balance' => $oldDestBalance,
                'increased_by' => $amountReceived,
                'new_balance' => $destWallet->balance
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update wallet balances', [
                'error' => $e->getMessage(),
                'user_id' => $data['client_id'] ?? null
            ]);
        }
    }
}
