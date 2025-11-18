<?php

namespace App\Classes;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Achat;
use App\Models\PayOutRequest;
use App\Services\VALR\ValrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ValrPaymentHelper extends GeneralPaymentHelper
{
    /**
     * Initialize payment via Bridge
     * @param array $input
     * @return JsonResponse
     */
    public static function initPayment(array $input): JsonResponse
    {
        // TODO: Implement Bridge payment initialization logic
        return response()->json([
            'success' => false,
            'message' => 'initPayment not implemented for BridgePaymentHelper',
        ]);
    }

    /**
     * Check request payments via Bridge
     * @param mixed $request
     * @return mixed
     */
    public static function checkRequestPayments($request)
    {
        // TODO: Implement Bridge payment status checking logic
        return [
            'success' => false,
            'message' => 'checkRequestPayments not implemented for BridgePaymentHelper',
        ];
    }

    /**
     * Check request payout via Bridge
     * @param mixed $request
     * @return mixed
     */
    public static function checkRequestPayout($request)
    {
        // TODO: Implement Bridge payout status checking logic
        return [
            'success' => false,
            'message' => 'checkRequestPayout not implemented for BridgePaymentHelper',
        ];
    }

    /**
     * Initialize payout via VALR
     * @param array $input
     * @return JsonResponse
     */
    public static function initPayout(array $input): JsonResponse
    {
        Log::info('Initiating VALR payout');

        // Setup user, client, and wallet
        $setup = self::setupUserAndWallet($input);
        $client = $setup['client'];
        $wallet = $setup['wallet'];

        // Create Achat record
        $new_achat = self::createAchatForPayout($client->id, $input, 'VALR-');

        if (env('NUAGE_ENV', 'SANDBOX') == 'SANDBOX') {
            $result = [
                'success' => true,
                'message' => 'VALR payout initiated (sandbox)',
                'data' => 'processing',
            ];
        } else {
            $valrService = new ValrService();

            // For VALR payouts, we need to:
            // 1. Convert fiat to crypto if needed
            // 2. Withdraw crypto to destination address

            // Check if metadata contains crypto withdrawal address
            if (!empty($input['metadata']['crypto_address']) && 
                !empty($input['metadata']['crypto_currency'])) {
                
                $cryptoCurrency = strtoupper($input['metadata']['crypto_currency']);
                $withdrawalAddress = $input['metadata']['crypto_address'];
                
                try {
                    // Get account balances to check if we have enough crypto
                    $balanceResponse = $valrService->getBalances();

                    if (is_object($balanceResponse) && method_exists($balanceResponse, 'successful') && $balanceResponse->successful()) {
                        $balances = $balanceResponse->json();
                        $cryptoBalance = collect($balances)->firstWhere('currency', $cryptoCurrency);

                        if (!$cryptoBalance || $cryptoBalance['available'] < $input['amount']) {
                            // Need to buy crypto first using simple order
                            $pair = $cryptoCurrency . $input['currency']; // e.g., BTCZAR

                            $orderResponse = $valrService->placeSimpleOrder(
                                $pair,
                                $input['currency'],
                                $input['amount'],
                                'BUY'
                            );

                            if (empty($orderResponse['success']) || !$orderResponse['success']) {
                                throw new \Exception('Failed to buy crypto: ' . ($orderResponse['message'] ?? json_encode($orderResponse)));
                            }
                        }

                        // Now withdraw the crypto
                        $withdrawResponse = $valrService->withdraw(
                            $cryptoCurrency,
                            $input['amount'],
                            $withdrawalAddress
                        );

                        if (is_object($withdrawResponse) && method_exists($withdrawResponse, 'successful') && $withdrawResponse->successful()) {
                            $result = [
                                'success' => true,
                                'message' => 'Crypto withdrawal initiated',
                                'data' => $withdrawResponse->json(),
                            ];
                        } else {
                            throw new \Exception('Withdrawal failed: ' . (is_object($withdrawResponse) && method_exists($withdrawResponse, 'body') ? $withdrawResponse->body() : 'Unknown error'));
                        }
                    } else {
                        throw new \Exception('Failed to fetch balances');
                    }
                } catch (\Exception $e) {
                    Log::error('VALR payout error', ['error' => $e->getMessage()]);
                    $result = [
                        'success' => false,
                        'message' => $e->getMessage(),
                    ];
                }
            } else {
                $result = [
                    'success' => false,
                    'message' => 'Missing crypto_address or crypto_currency in metadata for VALR payout',
                ];
            }
        }

        if ($result['success']) {
            $new_pay_out_request = new PayOutRequest;
            $new_pay_out_request->service = PaymentMethod::VALR;
            $new_pay_out_request->account_name = $input['user_name'];
            $new_pay_out_request->account_number = $input['metadata']['crypto_address'] ?? null;
            $new_pay_out_request->status = PaymentStatus::CREATED;
            $new_pay_out_request->save();

            $new_achat->requestable()->associate($new_pay_out_request);
            $new_achat->status = PaymentStatus::CREATED;
            $new_achat->save();

            return response()->json([
                'pay_token' => $new_achat->ref_id,
                'amount' => -1 * $new_achat->amount,
                'ref_id' => $new_achat->user_ref_id,
                'payment_method' => PaymentMethod::VALR,
                'status' => $new_achat->status,
                'success' => true,
            ]);
        }

        return response()->json([
            'pay_token' => $new_achat->ref_id,
            'ref_id' => $new_achat->user_ref_id,
            'amount' => -1 * $new_achat->amount,
            'status' => PaymentStatus::FAILED,
            'message' => $result['message'] ?? 'Payment has failed',
            'success' => false,
        ]);
    }

    public static function generateMomentTime(): string
    {
        return 'VALR-' . parent::UUID();
    }
}
