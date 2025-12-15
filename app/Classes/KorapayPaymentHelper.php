<?php

namespace App\Classes;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Achat;
use App\Models\PayOutRequest;
use App\Services\Korapay\KorapayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class KorapayPaymentHelper extends GeneralPaymentHelper
{
    /**
     * Initialize payment via Korapay
     * @param array $input
     * @return JsonResponse
     */
    public static function initPayment(array $input): JsonResponse
    {
        // Payout/Payin logic for payment initialization if needed
        return response()->json([
            'success' => false,
            'message' => 'initPayment not implemented for KorapayPaymentHelper',
        ]);
    }

    /**
     * Check request payments via Korapay
     * @param mixed $request
     * @return mixed
     */
    public static function checkRequestPayments($request)
    {
         return [
            'success' => false,
            'message' => 'checkRequestPayments not implemented for KorapayPaymentHelper',
        ];
    }

    /**
     * Check request payout via Korapay
     * @param mixed $request
     * @return mixed
     */
    public static function checkRequestPayout($request)
    {
        return [
            'success' => false,
            'message' => 'checkRequestPayout not implemented for KorapayPaymentHelper',
        ];
    }

    /**
     * Initialize payout via Korapay
     * @param array $input
     * @return JsonResponse
     */
    public static function initPayout(array $input): JsonResponse
    {
        Log::info('Initiating Korapay payout');

        // Setup user, client, and wallet (Inherited helper method)
        $setup = self::setupUserAndWallet($input);
        $client = $setup['client'];

        // Create Achat record
        $new_achat = self::createAchatForPayout($client->id, $input, 'KORAPAY-');

        if (env('NUAGE_ENV', 'SANDBOX') == 'SANDBOX') {
            $result = [
                'success' => true,
                'message' => 'Korapay payout initiated (sandbox)',
                'data' => 'processing',
            ];
        } else {
            $korapayService = new KorapayService();

            try {
                // Prepare destination
                // Usually Bank or Mobile Money.
                // Input metadata should contain bank details.
                
                $destination = $input['metadata']['destination'] ?? [];
                
                if (empty($destination)) {
                     // Try to construct from separate fields if flat
                     if (!empty($input['metadata']['bank_code']) && !empty($input['metadata']['account_number'])) {
                         $destination = [
                             'type' => 'bank_account',
                             'amount' => $input['amount'],
                             'currency' => $input['currency'],
                             'bank_account' => [
                                 'bank' => $input['metadata']['bank_code'],
                                 'account' => $input['metadata']['account_number']
                             ],
                             'customer' => [
                                'email' => $input['user_email']
                             ]
                         ];
                     }
                }

                if (empty($destination)) {
                    throw new \Exception('Missing destination information for Korapay payout');
                }
                
                $payload = [
                    'reference' => $new_achat->ref_id,
                    'destination' => $destination,
                    'amount' => $input['amount'],
                    'currency' => $input['currency'],
                    'narration' => $input['metadata']['narration'] ?? 'Payout from Nuage',
                    'customer' => [
                        'name' => $input['user_name'],
                        'email' => $input['user_email']
                    ]
                ];

                $response = $korapayService->initiateTransfer($payload);

                if (isset($response['status']) && $response['status'] === true) {
                     $result = [
                        'success' => true,
                        'message' => 'Korapay payout initiated',
                        'data' => $response['data'],
                    ];
                } else {
                    $errorMessage = $response['message'] ?? 'Unknown error from Korapay';
                    throw new \Exception('Transfer failed: ' . $errorMessage);
                }

            } catch (\Exception $e) {
                Log::error('Korapay payout error', ['error' => $e->getMessage()]);
                $result = [
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }
        }

        if ($result['success']) {
            $new_pay_out_request = new PayOutRequest;
            $new_pay_out_request->service = PaymentMethod::KORAPAY;
            $new_pay_out_request->account_name = $input['user_name'];
            $new_pay_out_request->account_number = $input['metadata']['account_number'] ?? 'N/A';
            $new_pay_out_request->status = PaymentStatus::CREATED;
            $new_pay_out_request->save();

            $new_achat->requestable()->associate($new_pay_out_request);
            $new_achat->status = PaymentStatus::PENDING; // or CREATED
            $new_achat->save();

            return response()->json([
                'pay_token' => $new_achat->ref_id,
                'amount' => -1 * $new_achat->amount,
                'ref_id' => $new_achat->user_ref_id,
                'payment_method' => PaymentMethod::KORAPAY,
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
        return 'KORAPAY-' . parent::UUID();
    }
}
