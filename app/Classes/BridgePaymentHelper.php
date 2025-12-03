<?php

namespace App\Classes;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Achat;
use App\Models\PayOutRequest;
use App\Services\Bridge\BridgeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class BridgePaymentHelper extends GeneralPaymentHelper
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
     * Initialize payout via Bridge
     * @param array $input
     * @return JsonResponse
     */
    public static function initPayout(array $input): JsonResponse
    {
        Log::info('Initiating Bridge payout');

        // Setup user, client, and wallet
        $setup = self::setupUserAndWallet($input);
        $client = $setup['client'];
        $wallet = $setup['wallet'];

        // Create Achat record
        $new_achat = self::createAchatForPayout($client->id, $input, 'BRIDGE-');

        if (env('NUAGE_ENV', 'SANDBOX') == 'SANDBOX') {
            $result = [
                'success' => true,
                'message' => 'Bridge payout initiated (sandbox)',
                'data' => 'processing',
            ];
        } else {
            $bridgeService = new BridgeService();

            try {
                // Check if customer exists or create one
                $customerId = $input['metadata']['bridge_customer_id'] ?? null;
                
                if (!$customerId) {
                    // Create customer
                    $customerResponse = $bridgeService->createCustomer(
                        $input['user_email'],
                        'individual',
                        [
                            'first_name' => $input['first_name'],
                            'last_name' => $input['last_name'],
                        ]
                    );

                    // Ensure $customerResponse is not an array and has successful() method
                    if (!is_array($customerResponse) && method_exists($customerResponse, 'successful') && $customerResponse->successful()) {
                        $customerData = $customerResponse->json();
                        $customerId = $customerData['id'];
                    } else {
                        throw new \Exception('Failed to create Bridge customer');
                    }
                }

                // Get or create wallet for the customer
                $walletsResponse = $bridgeService->getCustomerWallets($customerId);
                $walletId = null;
                
                if (is_object($walletsResponse) && method_exists($walletsResponse, 'successful') && $walletsResponse->successful()) {
                    $wallets = $walletsResponse->json();
                    // Find wallet with matching currency
                    $wallet = collect($wallets['data'] ?? [])->firstWhere('currency', $input['currency']);
                    $walletId = $wallet['id'] ?? null;
                }
                
                if (!$walletId) {
                    throw new \Exception('No wallet found for currency: ' . $input['currency']);
                }

                // Prepare source configuration
                $source = [
                    'payment_rail' => $input['metadata']['source_payment_rail'] ?? 'ethereum',
                    'currency' => $input['currency'],
                ];

                // Add bridge_wallet_id if available
                if ($walletId) {
                    $source['bridge_wallet_id'] = $walletId;
                }

                // Prepare destination configuration based on destination type
                $destination = [
                    'currency' => $input['metadata']['dest_currency'] ?? $input['currency'],
                ];

                if (!empty($input['metadata']['dest_external_account_id'])) {
                    // Transfer to external bank account
                    $destination['payment_rail'] = $input['metadata']['dest_payment_rail'] ?? 'ach';
                    $destination['external_account_id'] = $input['metadata']['dest_external_account_id'];
                } elseif (!empty($input['metadata']['dest_wallet_id'])) {
                    // Transfer to another Bridge wallet
                    $destination['payment_rail'] = $input['metadata']['dest_payment_rail'] ?? 'ethereum';
                    $destination['bridge_wallet_id'] = $input['metadata']['dest_wallet_id'];
                } elseif (!empty($input['metadata']['dest_crypto_address'])) {
                    // Transfer to crypto address
                    $destination['payment_rail'] = $input['metadata']['dest_payment_rail'] ?? 'ethereum';
                    $destination['to_address'] = $input['metadata']['dest_crypto_address'];
                } else {
                    throw new \Exception('Missing destination information for Bridge transfer');
                }

                // Create transfer with new API structure
                $transferResponse = $bridgeService->createTransfer(
                    $customerId,
                    $source,
                    $destination,
                    (string) $input['amount'],
                    $input['metadata']['client_reference_id'] ?? null,
                    $input['metadata']['developer_fee'] ?? null,
                    $input['metadata']['developer_fee_percent'] ?? null
                );

                if (is_object($transferResponse) && method_exists($transferResponse, 'successful') && $transferResponse->successful()) {
                    $transferData = $transferResponse->json();
                    $result = [
                        'success' => true,
                        'message' => 'Bridge transfer initiated',
                        'data' => $transferData,
                    ];
                } else {
                    $errorMessage = is_object($transferResponse) && method_exists($transferResponse, 'body')
                        ? $transferResponse->body()
                        : json_encode($transferResponse);
                    throw new \Exception('Transfer failed: ' . $errorMessage);
                }
            } catch (\Exception $e) {
                Log::error('Bridge payout error', ['error' => $e->getMessage()]);
                $result = [
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }
        }

        if ($result['success']) {
            $new_pay_out_request = new PayOutRequest;
            $new_pay_out_request->service = PaymentMethod::BRIDGE;
            $new_pay_out_request->account_name = $input['user_name'];
            $new_pay_out_request->account_number = $input['metadata']['dest_external_account_id'] 
                ?? $input['metadata']['dest_wallet_id']
                ?? $input['metadata']['dest_crypto_address']
                ?? null;
            $new_pay_out_request->status = PaymentStatus::CREATED;
            $new_pay_out_request->save();

            $new_achat->requestable()->associate($new_pay_out_request);
            $new_achat->status = PaymentStatus::CREATED;
            $new_achat->save();

            return response()->json([
                'pay_token' => $new_achat->ref_id,
                'amount' => -1 * $new_achat->amount,
                'ref_id' => $new_achat->user_ref_id,
                'payment_method' => PaymentMethod::BRIDGE,
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
        return 'BRIDGE-' . parent::UUID();
    }
}
