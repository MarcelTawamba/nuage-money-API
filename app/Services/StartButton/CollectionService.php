<?php

namespace App\Services\StartButton;

use App\Models\Achat;
use App\Models\StartButton\Collection;
use App\Enums\PaymentStatus;
use App\Events\PayInFailureEvent;
use App\Events\PayInSuccessEvent;
use Illuminate\Support\Facades\Log;

class CollectionService
{
    public function handle(object $data)
    {
        // Create a new collection record
        $collection = Collection::create([
            'transaction_id' => $data->transaction->_id,
            'transaction_type' => $data->transaction->transType,
            'status' => $data->transaction->status,
            'merchant_id' => $data->transaction->merchantId,
            'transaction_reference' => $data->transaction->transactionReference ?? null,
            'customer_email' => $data->transaction->customerEmail,
            'user_transaction_reference' => $data->transaction->userTransactionReference ?? null,
            'payment_code' => $data->transaction->paymentCode ?? null,
            'gateway_reference' => $data->transaction->gatewayReference ?? null,
            'fee_amount' => ($data->transaction->feeAmount ?? 0) / 100,
            'narration' => $data->transaction->narration ?? null,
            'amount' => $data->transaction->amount / 100,
            'currency' => $data->transaction->currency,
            'authorization_code' => $data->authorizationCode ?? null,
            'extra_information' => isset($data->extraInformation) ? json_encode($data->extraInformation) : null,
            'payer_information' => isset($data->payerInformation) ? json_encode($data->payerInformation) : null,
        ]);

        Log::channel('slack')->info('StartButton Collection record created', ['collection' => $collection]);

        // Find the original Achat record
        $achat = Achat::where('ref_id', $collection->user_transaction_reference)->first();

        if ($achat instanceof Achat) {
            $newStatus = PaymentStatus::getStatus($collection->status);

            $achat->status = $newStatus;
            if ($achat->requestable) {
                $achat->requestable->status = $newStatus;
            }

            if ($newStatus == PaymentStatus::SUCCESSFUL) {
                PayInSuccessEvent::dispatch($achat);
                Log::info('Dispatched PayInSuccessEvent for Achat ID: ' . $achat->id);
            } elseif ($newStatus == PaymentStatus::FAILED) {
                PayInFailureEvent::dispatch($achat);
                Log::info('Dispatched PayInFailureEvent for Achat ID: ' . $achat->id);
            }

            $achat->save();
            if ($achat->requestable) {
                $achat->requestable->save();
            }
        }
    }
}
