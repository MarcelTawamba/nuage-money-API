<?php

namespace App\Classes;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Events\PayInSuccessEvent;
use App\Events\PayOutFailureEvent;
use App\Events\PayOutSuccessEvent;
use App\Jobs\CheckFlutterwaveTransactionJob;
use App\Models\Achat;
use App\Models\FlutterwavePaymentRequest;
use App\Models\PayOutRequest;
use App\Services\Flutterwave\FlutterwaveEncryption;
use App\Services\Flutterwave\FlutterwaveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FlutterwavePaymentHelper extends GeneralPaymentHelper
{
    // -------------------------------------------------------------------------
    // GeneralPaymentHelper abstract contract
    // -------------------------------------------------------------------------

    /**
     * Initiate a pay-in (charge).
     * Supports two channels: 'mobile_money' and 'card'.
     *
     * Expected $input keys:
     *   user_email, first_name, last_name, country, currency, amount, ref_id,
     *   user_phone_number, payment_channel ('mobile_money'|'card'),
     *   metadata.momo   = [country_code, network, phone_number]
     *   metadata.card   = [number, expiry_month, expiry_year, cvv]
     *   metadata.redirect_url (optional)
     */
    public static function initPayment(array $input): JsonResponse
    {
        $setup  = self::setupUserAndWallet($input);
        $client = $setup['client'];

        $flwRequest = FlutterwavePaymentRequest::create([
            'reference'       => 'FLW-' . self::UUID(),
            'type'            => 'charge',
            'status'          => PaymentStatus::CREATED,
            'amount'          => $input['amount'],
            'currency'        => $input['currency'],
            'payment_channel' => $input['payment_channel'] ?? 'mobile_money',
        ]);

        $achat                   = self::createAchatForPayout($client->id, $input, 'FLW-');
        $achat->ref_id           = $flwRequest->reference;
        $achat->requestable_type = FlutterwavePaymentRequest::class;
        $achat->requestable_id   = $flwRequest->id;
        $achat->amount           = $input['amount']; // positive for pay-in
        $achat->status           = PaymentStatus::CREATED;
        $achat->save();

        if (self::isSandbox()) {
            return response()->json([
                'pay_token'      => $flwRequest->reference,
                'amount'         => $input['amount'],
                'ref_id'         => $input['ref_id'],
                'payment_method' => PaymentMethod::FLUTTERWAVE,
                'status'         => PaymentStatus::PENDING,
                'success'        => true,
            ]);
        }

        $service = new FlutterwaveService();

        try {
            // Step 1 — Customer
            $customerResp = $service->createCustomer([
                'name'  => [
                    'first' => $input['first_name'],
                    'last'  => $input['last_name'],
                ],
                'email' => $input['user_email'],
                'phone' => array_filter([
                    'country_code' => $input['country_code'] ?? null,
                    'number'       => $input['user_phone_number'] ?? null,
                ]),
            ]);

            $customerId = $customerResp['data']['id'] ?? null;
            if (!$customerId) {
                throw new \Exception('Failed to create Flutterwave customer: ' . ($customerResp['message'] ?? 'unknown error'));
            }
            $flwRequest->update(['flw_customer_id' => $customerId]);

            // Step 2 — Payment Method
            $channel = $input['payment_channel'] ?? 'mobile_money';

            if ($channel === 'card') {
                $encryptedCard = $service->encryptCard($input['metadata']['card']);
                $pmResp = $service->createCardPaymentMethod($encryptedCard);
            } else {
                $pmResp = $service->createMoMoPaymentMethod($input['metadata']['momo']);
            }

            $paymentMethodId = $pmResp['data']['id'] ?? null;
            if (!$paymentMethodId) {
                throw new \Exception('Failed to create Flutterwave payment method: ' . ($pmResp['message'] ?? 'unknown error'));
            }
            $flwRequest->update(['flw_payment_method_id' => $paymentMethodId]);

            // Step 3 — Charge
            $chargePayload = array_filter([
                'reference'         => $flwRequest->reference,
                'currency'          => $input['currency'],
                'amount'            => $input['amount'],
                'customer_id'       => $customerId,
                'payment_method_id' => $paymentMethodId,
                'redirect_url'      => $input['metadata']['redirect_url'] ?? null,
            ]);

            $chargeResp = $service->createCharge($chargePayload);
            $chargeId   = $chargeResp['data']['id'] ?? null;

            $flwRequest->update([
                'flw_charge_id' => $chargeId,
                'status'        => PaymentStatus::PENDING,
                'metadata'      => $chargeResp,
            ]);

            $achat->status = PaymentStatus::PENDING;
            $achat->save();

            // Dispatch polling job as webhook fallback (40s gives webhook time to arrive first)
            CheckFlutterwaveTransactionJob::dispatch($achat)->delay(now()->addSeconds(40));

            return response()->json([
                'pay_token'      => $flwRequest->reference,
                'amount'         => $input['amount'],
                'ref_id'         => $input['ref_id'],
                'payment_method' => PaymentMethod::FLUTTERWAVE,
                'status'         => PaymentStatus::PENDING,
                'next_action'    => $chargeResp['data']['next_action'] ?? null,
                'success'        => true,
            ]);
        } catch (\Exception $e) {
            Log::error('FlutterwavePaymentHelper::initPayment error', ['error' => $e->getMessage()]);

            $flwRequest->update(['status' => PaymentStatus::FAILED]);
            $achat->status = PaymentStatus::FAILED;
            $achat->save();

            return response()->json([
                'pay_token' => $flwRequest->reference,
                'ref_id'    => $input['ref_id'],
                'amount'    => $input['amount'],
                'status'    => PaymentStatus::FAILED,
                'message'   => $e->getMessage(),
                'success'   => false,
            ]);
        }
    }

    /**
     * Initiate a payout (direct transfer).
     * Supports 'mobile_money' and 'bank' transfer types.
     *
     * Expected $input keys:
     *   user_email, first_name, last_name, country, currency, amount, ref_id,
     *   user_phone_number,
     *   metadata.transfer_type ('mobile_money'|'bank')
     *   metadata.recipient = { name, mobile_money: {network, msisdn, country?} }
     *                      | { name, bank: {code, account_number, branch?, swift_code?, name?} }
     */
    public static function initPayout(array $input): JsonResponse
    {
        Log::info('FlutterwavePaymentHelper::initPayout', ['ref' => $input['ref_id'] ?? null]);

        $setup  = self::setupUserAndWallet($input);
        $client = $setup['client'];

        $achat = self::createAchatForPayout($client->id, $input, 'FLW-');

        $flwRequest = FlutterwavePaymentRequest::create([
            'reference'                => $achat->ref_id,
            'type'                     => 'transfer',
            'status'                   => PaymentStatus::CREATED,
            'amount'                   => $input['amount'],
            'currency'                 => $input['currency'],
            'payment_channel'          => $input['metadata']['transfer_type'] ?? 'bank',
            'recipient_name'           => $input['metadata']['recipient']['name'] ?? null,
            'recipient_phone'          => $input['metadata']['recipient']['mobile_money']['msisdn'] ?? null,
            'recipient_bank_code'      => $input['metadata']['recipient']['bank']['code'] ?? null,
            'recipient_account_number' => $input['metadata']['recipient']['bank']['account_number'] ?? null,
        ]);

        $achat->requestable_type = FlutterwavePaymentRequest::class;
        $achat->requestable_id   = $flwRequest->id;
        $achat->status           = PaymentStatus::CREATED;
        $achat->save();

        if (self::isSandbox()) {
            return response()->json([
                'pay_token'      => $achat->ref_id,
                'amount'         => -1 * $achat->amount,
                'ref_id'         => $input['ref_id'],
                'payment_method' => PaymentMethod::FLUTTERWAVE,
                'status'         => PaymentStatus::PENDING,
                'success'        => true,
            ]);
        }

        $service = new FlutterwaveService();

        try {
            $recipient    = $input['metadata']['recipient'];
            $transferType = $input['metadata']['transfer_type'] ?? 'bank';

            if ($transferType === 'mobile_money') {
                $recipientPayload = [
                    'name'         => $recipient['name'],
                    'mobile_money' => array_filter($recipient['mobile_money']),
                ];
            } else {
                $recipientPayload = [
                    'name' => $recipient['name'],
                    'bank' => array_filter($recipient['bank']),
                ];
            }

            $transferPayload = array_filter([
                'reference'          => $achat->ref_id,
                'currency'           => $input['currency'],
                'recipient'          => $recipientPayload,
                'payment_instruction' => [
                    'source_currency'      => $input['currency'],
                    'destination_currency' => $input['metadata']['destination_currency'] ?? $input['currency'],
                    'amount_type'          => 'source',
                    'amount'               => $input['amount'],
                    'action'               => 'instant',
                ],
            ]);

            $transferResp = $service->createTransfer($transferPayload);
            $transferId   = $transferResp['data']['id'] ?? null;

            $flwRequest->update([
                'flw_transfer_id' => $transferId,
                'status'          => PaymentStatus::PENDING,
                'metadata'        => $transferResp,
            ]);

            $achat->status = PaymentStatus::PENDING;
            $achat->save();

            // Dispatch polling job (webhook fallback)
            CheckFlutterwaveTransactionJob::dispatch($achat)->delay(now()->addSeconds(40));

            return response()->json([
                'pay_token'      => $achat->ref_id,
                'amount'         => -1 * $achat->amount,
                'ref_id'         => $input['ref_id'],
                'payment_method' => PaymentMethod::FLUTTERWAVE,
                'status'         => PaymentStatus::PENDING,
                'success'        => true,
            ]);
        } catch (\Exception $e) {
            Log::error('FlutterwavePaymentHelper::initPayout error', ['error' => $e->getMessage()]);

            $flwRequest->update(['status' => PaymentStatus::FAILED]);
            $achat->status = PaymentStatus::FAILED;
            $achat->save();

            return response()->json([
                'pay_token' => $achat->ref_id,
                'ref_id'    => $input['ref_id'],
                'amount'    => -1 * $achat->amount,
                'status'    => PaymentStatus::FAILED,
                'message'   => $e->getMessage(),
                'success'   => false,
            ]);
        }
    }

    /**
     * Poll Flutterwave for the current status of a pending charge (pay-in).
     */
    public static function checkRequestPayments($achat): array
    {
        /** @var FlutterwavePaymentRequest|null $flwRequest */
        $flwRequest = $achat->requestable;

        if (!$flwRequest || !$flwRequest->flw_charge_id) {
            return ['success' => false, 'status' => $achat->status, 'message' => 'No Flutterwave charge ID on record'];
        }

        try {
            $service  = new FlutterwaveService();
            $response = $service->getCharge($flwRequest->flw_charge_id);
            $status   = $response['data']['status'] ?? null;

            $mapped = self::mapChargeStatus($status);

            $flwRequest->update([
                'status'   => $mapped,
                'metadata' => $response,
            ]);
            $achat->status = $mapped;
            $achat->save();

            if ($mapped === PaymentStatus::SUCCESSFUL) {
                PayInSuccessEvent::dispatch($achat);
            }

            return ['success' => true, 'status' => $mapped, 'data' => $response['data'] ?? []];
        } catch (\Throwable $e) {
            Log::error('FlutterwavePaymentHelper::checkRequestPayments error', ['error' => $e->getMessage()]);
            return ['success' => false, 'status' => $achat->status, 'message' => $e->getMessage()];
        }
    }

    /**
     * Poll Flutterwave for the current status of a pending transfer (payout).
     */
    public static function checkRequestPayout($achat): array
    {
        /** @var FlutterwavePaymentRequest|null $flwRequest */
        $flwRequest = $achat->requestable;

        if (!$flwRequest || !$flwRequest->flw_transfer_id) {
            return ['success' => false, 'status' => $achat->status, 'message' => 'No Flutterwave transfer ID on record'];
        }

        try {
            $service  = new FlutterwaveService();
            $response = $service->getTransfer($flwRequest->flw_transfer_id);
            $status   = $response['data']['status'] ?? null;

            $mapped = self::mapTransferStatus($status);

            $updates = ['status' => $mapped, 'metadata' => $response];

            // Extract fee & rate data when available (mirrors YellowCard fee tracking)
            if (!empty($response['data']['fee'])) {
                $updates['fee_amount']    = $response['data']['fee']['amount'] ?? null;
                $updates['fee_currency']  = $response['data']['fee']['currency'] ?? null;
            }
            if (!empty($response['data']['rate'])) {
                $updates['exchange_rate'] = $response['data']['rate'];
            }

            $flwRequest->update($updates);
            $achat->status = $mapped;
            $achat->save();

            if ($mapped === PaymentStatus::SUCCESSFUL) {
                PayOutSuccessEvent::dispatch($achat);
            } elseif ($mapped === PaymentStatus::FAILED) {
                PayOutFailureEvent::dispatch($achat);
            }

            return ['success' => true, 'status' => $mapped, 'data' => $response['data'] ?? []];
        } catch (\Throwable $e) {
            Log::error('FlutterwavePaymentHelper::checkRequestPayout error', ['error' => $e->getMessage()]);
            return ['success' => false, 'status' => $achat->status, 'message' => $e->getMessage()];
        }
    }

    public static function generateMomentTime(): string
    {
        return 'FLW-' . parent::UUID();
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    private static function isSandbox(): bool
    {
        return env('NUAGE_ENV', 'SANDBOX') === 'SANDBOX'
            || str_contains((string) env('FLW_BASE_URL', ''), 'sandbox');
    }

    /** Map Flutterwave charge statuses → internal PaymentStatus. */
    private static function mapChargeStatus(?string $status): string
    {
        return match (strtolower((string) $status)) {
            'successful', 'success' => PaymentStatus::SUCCESSFUL,
            'failed', 'declined'    => PaymentStatus::FAILED,
            default                 => PaymentStatus::PENDING,
        };
    }

    /** Map Flutterwave transfer statuses → internal PaymentStatus. */
    private static function mapTransferStatus(?string $status): string
    {
        return match (strtoupper((string) $status)) {
            'SUCCESSFUL', 'SUCCESS' => PaymentStatus::SUCCESSFUL,
            'FAILED'                => PaymentStatus::FAILED,
            default                 => PaymentStatus::PENDING,
        };
    }
}
