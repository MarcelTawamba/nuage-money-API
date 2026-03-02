<?php

namespace App\Classes;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Events\PayInSuccessEvent;
use App\Events\PayOutFailureEvent;
use App\Events\PayOutSuccessEvent;
use App\Jobs\CheckYellowCardTransactionJob;
use App\Models\Achat;
use App\Models\Client;
use App\Models\ClientWallet;
use App\Models\Company;
use App\Models\PayOutRequest;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletType;
use App\Services\YellowCard\YellowCardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class YellowCardPaymentHelper extends GeneralPaymentHelper
{
    /**
     * Initiate a PAYOUT via YellowCard
     * @param array $input
     * @return JsonResponse
     */
    public static function initPayout(array $input): JsonResponse
    {
        Log::info('YellowCard: Initiating payout');
        
        $user = User::firstOrCreate(
            ['email' => $input['user_email']],
            [
                'name' => $input['first_name'] . ' ' . $input['last_name'],
                'password' => bcrypt(Str::random(10)),
                'country_code' => $input['country'],
                'phone_number' => $input['user_phone_number'],
            ]
        );

        $company = Company::firstOrCreate(
            ['name' => $input['company_name']],
            [
                'user_id' => $user->id,
                'company_type' => 'fintech',
                'address' => '55 University Avenue, Suite 1100, Toronto, Ontario M5J 2H7',
                'phone_number' => $input['company_phone_number'],
            ]
        );

        $client = Client::firstOrCreate(
            ['user_id' => $user->id],
            [
                'user_id' => $user->id,
                'name' => $input['first_name'] . ' ' . $input['last_name'],
                'company_id' => $company->id,
                'secret' => Str::random(40),
                'redirect' => '/',
                'personal_access_client' => false,
                'password_client' => false,
                'revoked' => false,
                'is_live' => true,
                'main_wallet' => $input['currency'],
            ]
        );

        $clientWallet = ClientWallet::firstOrCreate(['client_id' => $client->id]);

        $walletType = WalletType::firstOrCreate(['name' => $input['currency']], ['decimals' => 0]);

        $wallet = Wallet::firstOrNew(
            [
                'user_type' => ClientWallet::class,
                'user_id' => $client->id,
                'wallet_type_id' => $walletType->id,
            ]
        );

        if (!$wallet->exists) {
            $wallet->raw_balance = $input['account_balance'];
            $wallet->save();
        }

        $new_achat = self::createAchatForPayout($clientWallet->client_id, $input, 'YellowCard-');

        $payoutData = [
            'reference' => $new_achat->ref_id,
            'amount' => abs($input['amount']),
            'currency' => strtoupper($new_achat->currency),
            'country' => $new_achat->country,
        ];

        $paymentMethod = '';

        if (env('NUAGE_ENV', 'SANDBOX') == 'SANDBOX') {
            $result = [
                'success' => true,
                'message' => 'payout initiated',
                'data' => ['status' => 'processing'],
            ];
        } else {
            $yellowCardService = new YellowCardService();

            if (!empty($input['metadata']['bank_code']) && !empty($input['metadata']['dest_account_number'])) {
                $verificationData = [
                    'bank_code' => $input['metadata']['bank_code'],
                    'account_number' => $input['metadata']['dest_account_number'],
                    'currency' => $input['currency'],
                ];
                
                $account = self::verifyAccount($verificationData, $yellowCardService);
                Log::info('YellowCard Bank Account verification result:', ['result' => $account]);

                if (!$account['success']) {
                    return response()->json($account);
                }

                $payoutData['recipient'] = [
                    'type' => 'bank_account',
                    'bank_code' => $input['metadata']['bank_code'],
                    'account_number' => $input['metadata']['dest_account_number'],
                    'account_name' => $input['metadata']['dest_account_name'] ?? '',
                ];
                $paymentMethod = PaymentMethod::YELLOWCARD_BANK;
                
            } elseif (!empty($input['metadata']['phone_number'])) {
                $payoutData['recipient'] = [
                    'type' => 'mobile_money',
                    'phone_number' => $input['metadata']['phone_number'],
                    'provider' => $input['metadata']['mobile_provider'] ?? '',
                ];
                $paymentMethod = PaymentMethod::YELLOWCARD_MOBILE;
                
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required bank or mobile money details for YellowCard payout.',
                ]);
            }

            $payoutData['customer'] = [
                'name' => $input['user_name'] ?? ($input['first_name'] . ' ' . $input['last_name']),
                'email' => $input['user_email'],
            ];

            $payoutData['callback_url'] = url('/api/yellowcard-callback');

            Log::info('YellowCard MakeTransfer Request:', ['Request' => $payoutData]);
            $result = $yellowCardService->initiatePayout($payoutData);
            Log::info('YellowCard MakeTransfer Response:', ['Response' => $result]);
        }

        if ($result['success']) {
            $wallet->raw_balance -= abs($input['amount']);
            $wallet->save();

            $new_pay_out_request = new PayOutRequest();
            $new_pay_out_request->yellowcard_id = $result['data']['id'] ?? null;
            $new_pay_out_request->service = $paymentMethod;
            $new_pay_out_request->account_name = $input['user_name'] ?? ($input['first_name'] . ' ' . $input['last_name']);
            $new_pay_out_request->account_number = $input['metadata']['dest_account_number'] ?? $input['metadata']['phone_number'] ?? '';
            $new_pay_out_request->status = PaymentStatus::CREATED;
            $new_pay_out_request->bank_code = $input['metadata']['bank_code'] ?? null;
            $new_pay_out_request->mno = $input['metadata']['mobile_provider'] ?? null;
            $new_pay_out_request->msisdn = $input['metadata']['phone_number'] ?? null;
            $new_pay_out_request->save();

            $new_achat->requestable()->associate($new_pay_out_request);
            $new_achat->status = PaymentStatus::CREATED;
            $new_achat->save();

            CheckYellowCardTransactionJob::dispatch($new_achat)->delay(now()->addSeconds(40));

            Log::info('YellowCard: Saving transaction', ['Transaction' => $new_achat]);
            self::saveTransaction($new_achat);

            return response()->json([
                'pay_token' => $new_achat->ref_id,
                'amount' => -1 * $new_achat->amount,
                'ref_id' => $new_achat->user_ref_id,
                'payment_method' => $paymentMethod,
                'status' => $new_achat->status,
                'success' => true,
            ]);
        }

        Log::info('YellowCard: Error when making payout', ['Data' => $result]);

        return response()->json([
            'pay_token' => $new_achat->ref_id,
            'ref_id' => $new_achat->user_ref_id,
            'amount' => -1 * $new_achat->amount,
            'status' => PaymentStatus::FAILED,
            'message' => $result['message'] ?? 'Payment has failed, try later',
            'success' => false,
        ]);
    }

    /**
     * Check payout status
     * @param Achat $achat
     * @return array
     */
    public static function checkRequestPayout(Achat $achat): array
    {
        $yellowCardId = $achat->ref_id;
        $idSource = 'ref_id (fallback)';
        
        // If it's a YellowCardPayment, we should use the yellowcard_id (UUID) if available
        if ($achat->requestable_type == \App\Models\YellowCardPayment::class && $achat->requestable) {
            if (!empty($achat->requestable->yellowcard_id)) {
                $yellowCardId = $achat->requestable->yellowcard_id;
                $idSource = 'requestable->yellowcard_id';
            } elseif (isset($achat->requestable->response_data['id'])) {
                $yellowCardId = $achat->requestable->response_data['id'];
                $idSource = 'requestable->response_data[id]';
            }
        }
        // Legacy: If it's a PayOutRequest, we should use the yellowcard_id (UUID) if available
        elseif ($achat->requestable_type == \App\Models\PayOutRequest::class && $achat->requestable) {
            if (!empty($achat->requestable->yellowcard_id)) {
                $yellowCardId = $achat->requestable->yellowcard_id;
                $idSource = 'requestable->yellowcard_id';
            }
        }

        if (env('NUAGE_ENV', 'SANDBOX') === 'SANDBOX') {
            Log::info('YellowCard checkRequestPayout: Using SANDBOX mode with hardcoded response');
            $result = [
                'success' => true,
                'status' => 200,
                'data' => ['status' => 'successful'],
            ];
        } else {
            Log::info('YellowCard checkRequestPayout: Using LIVE mode, calling real API', [
                'yellowcard_id' => $yellowCardId
            ]);
            $yellowCardService = new YellowCardService();
            $result = $yellowCardService->getPayment($yellowCardId);
        }

        Log::info('YellowCard checkRequestPayout response', [
            'lookup_id' => $yellowCardId,
            'id_source' => $idSource,
            'achat_id' => $achat->id,
            'result_success' => $result['success'] ?? null,
            'result_http_status' => $result['status'] ?? null,
            'payment_status_from_api' => $result['data']['status'] ?? 'NOT_FOUND'
        ]);

        if ($result['success']) {
            $status = strtoupper($result['data']['status'] ?? 'pending');
            $oldStatus = $achat->status;
            $newStatus = PaymentStatus::getStatus($status);
            
            Log::info('YellowCard checkRequestPayout status mapping', [
                'api_status_raw' => $result['data']['status'] ?? 'NOT_FOUND',
                'api_status_uppercase' => $status,
                'old_achat_status' => $oldStatus,
                'new_achat_status' => $newStatus,
                'is_final' => in_array($newStatus, [PaymentStatus::SUCCESSFUL, PaymentStatus::FAILED])
            ]);
            
            $achat->status = $newStatus;
            $achat->save();
            
            if ($achat->requestable) {
                // Update the linked request model status
                $achat->requestable->status = $status;
                
                // Extract and store fee data if this is a YellowCardPayment
                if ($achat->requestable_type == \App\Models\YellowCardPayment::class) {
                    self::updateYellowCardPaymentFees($achat->requestable, $result['data']);
                }
                
                $achat->requestable->save();
            }

            // Trigger success event if transitioned to successful
            if ($newStatus == PaymentStatus::SUCCESSFUL && $oldStatus != PaymentStatus::SUCCESSFUL) {
                Log::info('YellowCard payout success - dispatching PayOutSuccessEvent', [
                    'achat_id' => $achat->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus
                ]);
                PayOutSuccessEvent::dispatch($achat);
            }

            // If it transitioned to failed, trigger the failure event
            if ($newStatus == PaymentStatus::FAILED && $oldStatus != PaymentStatus::FAILED) {
                PayOutFailureEvent::dispatch($achat);
            }

            $achat->save();

            return [
                'pay_token' => $achat->ref_id,
                'amount' => $achat->amount,
                'status' => $achat->status,
                'ref_id' => $achat->user_ref_id,
                'success' => true,
            ];
        }

        return [
            'pay_token' => $achat->ref_id,
            'amount' => $achat->amount,
            'status' => $achat->status,
            'ref_id' => $achat->user_ref_id,
            'payment_method' => $achat->requestable->service,
        ];
    }

    /**
     * Verify account before payout
     * @param array $input
     * @param YellowCardService $service
     * @return array
     */
    public static function verifyAccount(array $input, YellowCardService $service): array
    {
        if (env('NUAGE_ENV', 'SANDBOX') == 'SANDBOX') {
            return [
                'success' => true,
                'message' => 'Account available',
            ];
        }

        $account = $service->resolveBankAccount($input);

        if ($account['success']) {
            return [
                'success' => true,
                'data' => 'Account valid',
                'message' => 'Account available',
            ];
        }

        return [
            'success' => false,
            'message' => $account['message'] ?? 'Account not resolved',
        ];
    }

    /**
     * Initiate a PAYIN via YellowCard
     * Satisfies GeneralPaymentHelper abstract method
     */
    public static function initPayment(array $input): JsonResponse
    {
        // This is primarily handled in ClientController@fundWallet currently
        // but we'll stub it here for future refactoring and class completeness
        return response()->json([
            'success' => false,
            'message' => 'Pay-in via YellowCard should be initiated through the fundWallet method in ClientController'
        ]);
    }

    /**
     * Check pay-in status
     * Satisfies GeneralPaymentHelper abstract method
     */
    public static function checkRequestPayments(Achat $achat): array
    {
        return self::checkRequestCollection($achat);
    }

    /**
     * Check collection status
     * @param Achat $achat
     * @return array
     */
    public static function checkRequestCollection(Achat $achat): array
    {
        $yellowCardId = $achat->ref_id;
        $idSource = 'ref_id (fallback)';
        
        // If it's a YellowCardCollection, we should use the yellowcard_id (UUID) if available
        if ($achat->requestable_type == \App\Models\YellowCardCollection::class && $achat->requestable) {
            if (!empty($achat->requestable->yellowcard_id)) {
                $yellowCardId = $achat->requestable->yellowcard_id;
                $idSource = 'requestable->yellowcard_id';
            } elseif (isset($achat->requestable->response_data['id'])) {
                $yellowCardId = $achat->requestable->response_data['id'];
                $idSource = 'requestable->response_data[id]';
            }
        }

        $yellowCardService = new YellowCardService();
        $result = $yellowCardService->getCollection($yellowCardId);

        Log::info('YellowCard checkRequestCollection response', [
            'lookup_id' => $yellowCardId,
            'id_source' => $idSource,
            'achat_id' => $achat->id,
            'Data' => $result
        ]);

        if ($result['success']) {
            $status = strtoupper($result['data']['status'] ?? 'pending');
            $oldStatus = $achat->status;
            $newStatus = PaymentStatus::getStatus($status);
            
            $achat->status = $newStatus;
            $achat->save();
            
            if ($achat->requestable) {
                // Update the linked request model status
                $achat->requestable->status = $status;
                
                // Extract and store fee data if this is a YellowCardCollection
                if ($achat->requestable_type == \App\Models\YellowCardCollection::class) {
                    // YellowCardCollection might need similar fee extraction
                    // For now, we'll update response_data at minimum
                    if (!$achat->requestable->response_data) {
                        $achat->requestable->response_data = $result['data'];
                    }
                }
                
                $achat->requestable->save();
            }

            // If it transitioned to successful, trigger the wallet update event
            if ($newStatus == PaymentStatus::SUCCESSFUL && $oldStatus != PaymentStatus::SUCCESSFUL) {
                PayInSuccessEvent::dispatch($achat);
            }

            return [
                'pay_token' => $achat->ref_id,
                'amount' => $achat->amount,
                'status' => $achat->status,
                'ref_id' => $achat->user_ref_id,
                'success' => true,
            ];
        }

        return [
            'pay_token' => $achat->ref_id,
            'amount' => $achat->amount,
            'status' => $achat->status,
            'ref_id' => $achat->user_ref_id,
            'success' => false,
        ];
    }

    public static function generateMomentTime(): string
    {
        return 'YellowCard-' . parent::UUID();
    }

    /**
     * Extract and update fee data from YellowCard API response
     * @param \App\Models\YellowCardPayment $payment
     * @param array $data
     * @return void
     */
    private static function updateYellowCardPaymentFees($payment, array $data): void
    {
        // Extract fee information
        $payment->fee_amount_local = $data['serviceFeeAmountLocal'] ?? null;
        $payment->fee_amount_usd = $data['serviceFeeAmountUSD'] ?? null;
        $payment->service_fee_id = $data['serviceFeeId'] ?? null;
        
        // Extract exchange rate and original amount
        $payment->exchange_rate = $data['rate'] ?? null;
        $payment->amount_usd = $data['amount'] ?? null;
        
        // Extract transaction metadata
        $payment->provider = $data['provider'] ?? null;
        $payment->attempt = $data['attempt'] ?? 1;
        $payment->withdrawal_id = $data['withdrawalId'] ?? null;
        $payment->reason = $data['reason'] ?? null;
        
        // Extract destination details
        if (isset($data['destination'])) {
            $destination = $data['destination'];
            $payment->destination_account_name = $destination['accountName'] ?? null;
            $payment->destination_account_number = $destination['accountNumber'] ?? null;
            $payment->destination_bank_code = $destination['accountBank'] ?? null;
            $payment->destination_bank_name = $destination['networkName'] ?? null;
            $payment->destination_network_id = $destination['networkId'] ?? null;
        }
        
        Log::info('YellowCard: Updated payment with fee data', [
            'payment_id' => $payment->id,
            'yellowcard_id' => $payment->yellowcard_id,
            'fee_local' => $payment->fee_amount_local,
            'fee_usd' => $payment->fee_amount_usd,
            'rate' => $payment->exchange_rate
        ]);
    }
}
