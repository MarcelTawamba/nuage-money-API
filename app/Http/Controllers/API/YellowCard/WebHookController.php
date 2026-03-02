<?php

namespace App\Http\Controllers\API\YellowCard;

use App\Enums\PaymentStatus;
use App\Events\PayInSuccessEvent;
use App\Events\PayOutFailureEvent;
use App\Events\PayOutSuccessEvent;
use App\Http\Controllers\Controller;
use App\Models\Achat;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WebHookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        Log::info("YellowCardWebHookController called", [
            "Req" => $request->all()
        ]);

        $secret = env('YELLOWCARD_WEBHOOK_SECRET');
        $requestBody = $request->getContent();
        $signature = $request->header('x-yellowcard-signature');

        // Only validate signature if secret is configured
        if (!empty($secret) && !$this->isValidSignature($secret, $requestBody, $signature)) {
            Log::warning('Invalid YellowCard webhook signature received.');
            return response()->json(['error' => 'Invalid signature'], Response::HTTP_FORBIDDEN);
        }

        $result = json_decode($requestBody);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Invalid JSON received from YellowCard webhook.', ['body' => $requestBody]);
            return response()->json(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        Log::info("YellowCardWebHookController Data is OK and received", [
            "Data" => $result
        ]);

        // YellowCard webhook structure may vary, adapting based on common patterns
        $event = $result->event ?? $result->type ?? null;
        $data = $result->data ?? $result;

        if (!$event) {
            Log::warning('YellowCard webhook missing event type');
            return response()->json(['success' => true], Response::HTTP_OK);
        }

        try {
            switch ($event) {
                case 'payout.successful':
                case 'payout.completed':
                case 'payout.success':
                    $this->handlePayoutSuccess($data);
                    break;
                case 'payout.failed':
                case 'payout.failure':
                    $this->handlePayoutFailure($data);
                    break;
                case 'payout.pending':
                case 'payout.processing':
                    $this->handlePayoutPending($data);
                    break;
                case 'collection.successful':
                case 'collection.completed':
                case 'collection.success':
                case 'charge.success':
                    $this->handleCollectionSuccess($data);
                    break;
                case 'collection.failed':
                case 'collection.failure':
                case 'charge.failed':
                    $this->handleCollectionFailure($data);
                    break;
                default:
                    Log::warning('Unhandled YellowCard webhook event received.', ['event' => $event]);
                    break;
            }
        } catch (\Exception $e) {
            Log::error('Error handling YellowCard webhook event', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], Response::HTTP_OK);
        }

        return response()->json(['success' => true], Response::HTTP_OK);
    }

    private function handlePayoutSuccess($data)
    {
        $reference = $data->reference ?? $data->transaction_reference ?? null;
        
        if (!$reference) {
            Log::error('YellowCard payout success webhook missing reference');
            return;
        }

        $achat = Achat::where('ref_id', $reference)->first();
        
        if (!$achat) {
            Log::warning('YellowCard payout success webhook: Achat not found', ['reference' => $reference]);
            return;
        }

        if ($achat->status === PaymentStatus::SUCCESSFUL) {
            Log::info('YellowCard payout already marked as successful', ['reference' => $reference]);
            return;
        }

        $achat->status = PaymentStatus::SUCCESSFUL;
        $achat->save();

        if ($achat->requestable) {
            $achat->requestable->status = PaymentStatus::SUCCESSFUL;
            $achat->requestable->save();
        }

        PayOutSuccessEvent::dispatch($achat);

        Log::info('YellowCard payout marked as successful', ['reference' => $reference]);
    }

    private function handlePayoutFailure($data)
    {
        $reference = $data->reference ?? $data->transaction_reference ?? null;
        
        if (!$reference) {
            Log::error('YellowCard payout failure webhook missing reference');
            return;
        }

        $achat = Achat::where('ref_id', $reference)->first();
        
        if (!$achat) {
            Log::warning('YellowCard payout failure webhook: Achat not found', ['reference' => $reference]);
            return;
        }

        $achat->status = PaymentStatus::FAILED;
        $achat->save();

        if ($achat->requestable) {
            $achat->requestable->status = PaymentStatus::FAILED;
            $achat->requestable->save();
        }

        PayOutFailureEvent::dispatch($achat);

        Log::info('YellowCard payout marked as failed', ['reference' => $reference]);
    }

    private function handlePayoutPending($data)
    {
        $reference = $data->reference ?? $data->transaction_reference ?? null;
        
        if (!$reference) {
            Log::error('YellowCard payout pending webhook missing reference');
            return;
        }

        $achat = Achat::where('ref_id', $reference)->first();
        
        if (!$achat) {
            Log::warning('YellowCard payout pending webhook: Achat not found', ['reference' => $reference]);
            return;
        }

        $achat->status = PaymentStatus::PENDING;
        $achat->save();

        if ($achat->requestable) {
            $achat->requestable->status = PaymentStatus::PENDING;
            $achat->requestable->save();
        }

        Log::info('YellowCard payout marked as pending', ['reference' => $reference]);
    }

    private function handleCollectionSuccess($data)
    {
        $reference = $data->reference ?? $data->transaction_reference ?? null;
        
        if (!$reference) {
            Log::error('YellowCard collection success webhook missing reference');
            return;
        }

        $achat = Achat::where('ref_id', $reference)->first();
        
        if (!$achat) {
            Log::warning('YellowCard collection success webhook: Achat not found', ['reference' => $reference]);
            return;
        }

        if ($achat->status === PaymentStatus::SUCCESSFUL) {
            Log::info('YellowCard collection already marked as successful', ['reference' => $reference]);
            return;
        }

        $achat->status = PaymentStatus::SUCCESSFUL;
        $achat->save();

        if ($achat->requestable) {
            $achat->requestable->status = PaymentStatus::SUCCESSFUL;
            $achat->requestable->save();
        }

        PayInSuccessEvent::dispatch($achat);

        Log::info('YellowCard collection marked as successful', ['reference' => $reference]);
    }

    private function handleCollectionFailure($data)
    {
        $reference = $data->reference ?? $data->transaction_reference ?? null;
        
        if (!$reference) {
            Log::error('YellowCard collection failure webhook missing reference');
            return;
        }

        $achat = Achat::where('ref_id', $reference)->first();
        
        if (!$achat) {
            Log::warning('YellowCard collection failure webhook: Achat not found', ['reference' => $reference]);
            return;
        }

        $achat->status = PaymentStatus::FAILED;
        $achat->save();

        if ($achat->requestable) {
            $achat->requestable->status = PaymentStatus::FAILED;
            $achat->requestable->save();
        }

        Log::info('YellowCard collection marked as failed', ['reference' => $reference]);
    }

    private function isValidSignature($secret, $data, $signature): bool
    {
        if (empty($secret) || empty($signature)) {
            return false;
        }
        
        // YellowCard typically uses HMAC SHA256 or SHA512
        // Adjust based on actual YellowCard documentation
        $calculatedSignature = hash_hmac('sha256', $data, $secret);
        
        // Try SHA512 if SHA256 doesn't match
        if (!hash_equals($calculatedSignature, $signature)) {
            $calculatedSignature = hash_hmac('sha512', $data, $secret);
        }
        
        return hash_equals($calculatedSignature, $signature);
    }
}
