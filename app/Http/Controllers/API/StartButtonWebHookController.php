<?php

namespace App\Http\Controllers\API;

use App\Enums\PaymentStatus;
use App\Events\PayInFailureEvent;
use App\Events\PayInSuccessEvent;
use App\Events\PayOutFailureEvent;
use App\Events\PayOutSuccessEvent;
use App\Http\Controllers\Controller;
use App\Models\Achat;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class StartButtonWebHookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        Log::channel("slack")->info("StartButtonWebHookController called", [
            "Req" => $request->all()
        ]);

        $secret = env('STARTBUTTON_SECRET_KEY');
        $requestBody = $request->getContent();
        $signature = $request->header('x-startbutton-signature');

        if (!$this->isValidSignature($secret, $requestBody, $signature)) {
            Log::warning('Invalid StartButton webhook signature received.');
            return response()->json(['error' => 'Invalid signature'], Response::HTTP_FORBIDDEN);
        }

        $result = json_decode($requestBody);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($result->data->transaction)) {
            Log::error('Invalid JSON received from StartButton webhook.', ['body' => $requestBody]);
            return response()->json(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        Log::channel("slack")->info("StartButtonWebHookController Data is OK and received", [
            "Data" => $result
        ]);

        $achat = Achat::whereRefId($result->data->transaction->userTransactionReference)->first();

        if ($achat instanceof Achat) {
            $newStatus = PaymentStatus::getStatus($result->data->transaction->status);

            $achat->status = $newStatus;
            if ($achat->requestable) {
                $achat->requestable->status = $newStatus;
            }

            $isPayIn = $achat->amount > 0;
            $isPayOut = $achat->amount < 0;

            if ($newStatus == PaymentStatus::SUCCESSFUL) {
                if ($isPayIn) {
                    PayInSuccessEvent::dispatch($achat);
                    Log::info('Dispatched PayInSuccessEvent for Achat ID:' . $achat->id);
                } elseif ($isPayOut) {
                    PayOutSuccessEvent::dispatch($achat);
                    Log::info('Dispatched PayOutSuccessEvent for Achat ID:' . $achat->id);
                }

            } elseif ($newStatus == PaymentStatus::FAILED) {
                if ($isPayOut) {
                    PayOutFailureEvent::dispatch($achat);
                    Log::info('Dispatched PayOutFailureEvent for Achat ID:' . $achat->id);
                } elseif ($isPayIn) {
                    PayInFailureEvent::dispatch($achat);
                    Log::info('Dispatched PayInFailureEvent for Achat ID:' . $achat->id);
                }
            }

            $achat->save();
            if ($achat->requestable) {
                $achat->requestable->save();
            }
        }

        return response()->json([], Response::HTTP_OK);
    }

    private function isValidSignature($secret, $data, $signature): bool
    {
        if (empty($secret) || empty($signature)) {
            return false;
        }
        $calculatedSignature = hash_hmac('sha512', $data, $secret);
        return hash_equals($calculatedSignature, $signature);
    }
}