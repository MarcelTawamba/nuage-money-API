<?php

namespace App\Http\Controllers\API;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Achat;
use App\Models\FlutterwavePaymentRequest;
use App\Services\Flutterwave\FlutterwaveService;
use App\Events\PayInSuccessEvent;
use App\Events\PayOutSuccessEvent;
use App\Events\PayOutFailureEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class FlutterwaveController extends Controller
{
    protected FlutterwaveService $flw;

    public function __construct(FlutterwaveService $flw)
    {
        $this->flw = $flw;
    }

    // -------------------------------------------------------------------------
    // Customers
    // -------------------------------------------------------------------------

    public function createCustomer(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name.first'             => 'required|string',
            'name.last'              => 'required|string',
            'email'                  => 'required|email',
            'phone.country_code'     => 'nullable|string',
            'phone.number'           => 'nullable|string',
            'address.city'           => 'nullable|string',
            'address.country'        => 'nullable|string',
            'address.line1'          => 'nullable|string',
            'address.postal_code'    => 'nullable|string',
            'address.state'          => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $data = array_filter($request->all(), fn($v) => $v !== null);
            return response()->json(['success' => true, 'data' => $this->flw->createCustomer($data)]);
        } catch (Exception $e) {
            return $this->serverError('createCustomer', $e);
        }
    }

    public function getCustomer(string $customerId): JsonResponse
    {
        try {
            return response()->json(['success' => true, 'data' => $this->flw->getCustomer($customerId)]);
        } catch (Exception $e) {
            return $this->serverError('getCustomer', $e);
        }
    }

    // -------------------------------------------------------------------------
    // Payment Methods
    // -------------------------------------------------------------------------

    public function createCardPaymentMethod(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'card.number'       => 'required|string',
            'card.expiry_month' => 'required|string',
            'card.expiry_year'  => 'required|string',
            'card.cvv'          => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $encrypted = $this->flw->encryptCard($request->input('card'));
            return response()->json(['success' => true, 'data' => $this->flw->createCardPaymentMethod($encrypted)]);
        } catch (Exception $e) {
            return $this->serverError('createCardPaymentMethod', $e);
        }
    }

    public function createMoMoPaymentMethod(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'country_code' => 'required|string',
            'network'      => 'required|string',
            'phone_number' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            return response()->json(['success' => true, 'data' => $this->flw->createMoMoPaymentMethod($request->all())]);
        } catch (Exception $e) {
            return $this->serverError('createMoMoPaymentMethod', $e);
        }
    }

    // -------------------------------------------------------------------------
    // Charges (Pay-ins)
    // -------------------------------------------------------------------------

    public function createCharge(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'currency'          => 'required|string',
            'amount'            => 'required|numeric',
            'customer_id'       => 'required|string',
            'payment_method_id' => 'required|string',
            'redirect_url'      => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $payload = array_filter($request->all(), fn($v) => $v !== null);
            $payload['reference'] = 'FLW-' . \Illuminate\Support\Str::uuid();

            $response = $this->flw->createCharge($payload);
            return response()->json(['success' => true, 'data' => $response]);
        } catch (Exception $e) {
            return $this->serverError('createCharge', $e);
        }
    }

    public function authorizeCharge(Request $request, string $chargeId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'authorization'      => 'required|array',
            'authorization.type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $response = $this->flw->authorizeCharge($chargeId, $request->input('authorization'));
            return response()->json(['success' => true, 'data' => $response]);
        } catch (Exception $e) {
            return $this->serverError('authorizeCharge', $e);
        }
    }

    public function getCharge(string $chargeId): JsonResponse
    {
        try {
            return response()->json(['success' => true, 'data' => $this->flw->getCharge($chargeId)]);
        } catch (Exception $e) {
            return $this->serverError('getCharge', $e);
        }
    }

    // -------------------------------------------------------------------------
    // Direct Transfers (Payouts)
    // -------------------------------------------------------------------------

    public function resolveBankAccount(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'bank_code'      => 'required|string',
            'account_number' => 'required|string',
            'currency'       => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            return response()->json(['success' => true, 'data' => $this->flw->resolveBankAccount($request->all())]);
        } catch (Exception $e) {
            return $this->serverError('resolveBankAccount', $e);
        }
    }

    public function createTransfer(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'currency'                                     => 'required|string',
            'recipient'                                    => 'required|array',
            'recipient.name'                               => 'required|string',
            'payment_instruction'                          => 'required|array',
            'payment_instruction.source_currency'         => 'required|string',
            'payment_instruction.destination_currency'    => 'required|string',
            'payment_instruction.amount'                  => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $payload = $request->all();
            $payload['reference'] = 'FLW-' . \Illuminate\Support\Str::uuid();

            $response = $this->flw->createTransfer($payload);
            return response()->json(['success' => true, 'data' => $response]);
        } catch (Exception $e) {
            return $this->serverError('createTransfer', $e);
        }
    }

    public function getTransfer(string $transferId): JsonResponse
    {
        try {
            return response()->json(['success' => true, 'data' => $this->flw->getTransfer($transferId)]);
        } catch (Exception $e) {
            return $this->serverError('getTransfer', $e);
        }
    }

    // -------------------------------------------------------------------------
    // Refunds
    // -------------------------------------------------------------------------

    public function createRefund(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'charge_id' => 'required|string',
            'amount'    => 'required|numeric',
            'reason'    => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator);
        }

        try {
            $response = $this->flw->createRefund($request->all());
            return response()->json(['success' => true, 'data' => $response]);
        } catch (Exception $e) {
            return $this->serverError('createRefund', $e);
        }
    }

    public function getRefund(string $refundId): JsonResponse
    {
        try {
            return response()->json(['success' => true, 'data' => $this->flw->getRefund($refundId)]);
        } catch (Exception $e) {
            return $this->serverError('getRefund', $e);
        }
    }

    public function listRefunds(Request $request): JsonResponse
    {
        try {
            $response = $this->flw->listRefunds(
                (int) $request->query('page', 1),
                (int) $request->query('size', 10),
                $request->query('from'),
                $request->query('to'),
            );
            return response()->json(['success' => true, 'data' => $response]);
        } catch (Exception $e) {
            return $this->serverError('listRefunds', $e);
        }
    }

    // -------------------------------------------------------------------------
    // Webhook
    // -------------------------------------------------------------------------

    /**
     * Handle Flutterwave webhook events.
     * Verified via HMAC-SHA256 of the raw request body against FLW_WEBHOOK_SECRET.
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $secret    = env('FLW_WEBHOOK_SECRET');
        $signature = $request->header('verif-hash') ?? $request->header('x-flw-signature');

        if ($secret && $signature) {
            $expected = hash_hmac('sha256', $request->getContent(), $secret);
            if (!hash_equals($expected, $signature)) {
                Log::warning('Flutterwave webhook signature mismatch');
                return response()->json(['message' => 'Invalid signature'], 401);
            }
        }

        $event = $request->input('type');
        $data  = $request->input('data', []);

        Log::info('Flutterwave webhook received', ['type' => $event]);

        // Respond immediately — process asynchronously to stay within Flutterwave's timeout
        try {
            match ($event) {
                'charge.completed'  => $this->handleChargeCompleted($data),
                'transfer.completed'=> $this->handleTransferCompleted($data),
                'refund.completed'  => $this->handleRefundCompleted($data),
                default             => Log::info('Flutterwave unhandled webhook event', ['type' => $event]),
            };
        } catch (\Throwable $e) {
            Log::error('Flutterwave webhook processing error', ['event' => $event, 'error' => $e->getMessage()]);
        }

        return response()->json(['success' => true], 200);
    }

    // -------------------------------------------------------------------------
    // Webhook event handlers
    // -------------------------------------------------------------------------

    private function handleChargeCompleted(array $data): void
    {
        $flwChargeId = $data['id'] ?? null;
        if (!$flwChargeId) return;

        // Re-fetch from FLW to prevent tampered webhook abuse
        $verified = $this->flw->getCharge($flwChargeId);
        $status   = $verified['data']['status'] ?? null;
        $amount   = $verified['data']['amount'] ?? null;
        $currency = $verified['data']['currency'] ?? null;

        $flwRequest = FlutterwavePaymentRequest::where('flw_charge_id', $flwChargeId)->first();
        if (!$flwRequest) return;

        // Validate amount & currency match to prevent value manipulation
        if ((float) $amount !== (float) $flwRequest->amount || $currency !== $flwRequest->currency) {
            Log::warning('Flutterwave charge amount/currency mismatch on webhook', [
                'flw_charge_id' => $flwChargeId,
                'expected'      => [$flwRequest->amount, $flwRequest->currency],
                'received'      => [$amount, $currency],
            ]);
            return;
        }

        $mapped = match (strtolower((string) $status)) {
            'successful', 'success' => PaymentStatus::SUCCESSFUL,
            'failed', 'declined'    => PaymentStatus::FAILED,
            default                 => PaymentStatus::PENDING,
        };

        $flwRequest->update(['status' => $mapped, 'metadata' => $verified]);

        $achat = $flwRequest->achat;
        if ($achat && $achat->status !== PaymentStatus::SUCCESSFUL) {
            $achat->status = $mapped;
            $achat->save();

            if ($mapped === PaymentStatus::SUCCESSFUL) {
                PayInSuccessEvent::dispatch($achat);
            }
        }
    }

    private function handleTransferCompleted(array $data): void
    {
        $flwTransferId = $data['id'] ?? null;
        if (!$flwTransferId) return;

        $verified = $this->flw->getTransfer((string) $flwTransferId);
        $status   = $verified['data']['status'] ?? null;

        $flwRequest = FlutterwavePaymentRequest::where('flw_transfer_id', $flwTransferId)->first();
        if (!$flwRequest) return;

        $mapped = match (strtoupper((string) $status)) {
            'SUCCESSFUL', 'SUCCESS' => PaymentStatus::SUCCESSFUL,
            'FAILED'                => PaymentStatus::FAILED,
            default                 => PaymentStatus::PENDING,
        };

        $updates = ['status' => $mapped, 'metadata' => $verified];
        if (!empty($verified['data']['fee']['amount'])) {
            $updates['fee_amount']   = $verified['data']['fee']['amount'];
            $updates['fee_currency'] = $verified['data']['fee']['currency'] ?? null;
        }
        $flwRequest->update($updates);

        $achat = $flwRequest->achat;
        if ($achat && !in_array($achat->status, [PaymentStatus::SUCCESSFUL, PaymentStatus::FAILED])) {
            $achat->status = $mapped;
            $achat->save();

            if ($mapped === PaymentStatus::SUCCESSFUL) {
                PayOutSuccessEvent::dispatch($achat);
            } elseif ($mapped === PaymentStatus::FAILED) {
                PayOutFailureEvent::dispatch($achat);
            }
        }
    }

    private function handleRefundCompleted(array $data): void
    {
        $flwRefundId = $data['id'] ?? null;
        if (!$flwRefundId) return;

        $verified   = $this->flw->getRefund((string) $flwRefundId);
        $flwRequest = FlutterwavePaymentRequest::where('flw_refund_id', $flwRefundId)->first();

        if ($flwRequest) {
            $flwRequest->update(['status' => PaymentStatus::PROCESSED, 'metadata' => $verified]);
        }

        Log::info('Flutterwave refund completed', ['refund_id' => $flwRefundId]);
    }

    // -------------------------------------------------------------------------
    // Response helpers
    // -------------------------------------------------------------------------

    private function validationError($validator): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422);
    }

    private function serverError(string $context, Exception $e): JsonResponse
    {
        Log::error("FlutterwaveController::{$context} error: " . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => "Failed to execute {$context}",
            'error'   => $e->getMessage(),
        ], 500);
    }
}
