<?php

namespace App\Services\StartButton;

use App\Models\Achat;
use App\Models\StartButton\Conversion;
use App\Enums\PaymentStatus;
use Illuminate\Support\Facades\Log;

class ConversionService
{
    public function handle(object $data)
    {
        // Create a new conversion record
        $conversion = Conversion::create([
            'transaction_id' => $data->transaction->_id,
            'transaction_type' => $data->transaction->transType,
            'status' => $data->transaction->status,
            'from_amount' => ($data->transaction->fromAmount ?? 0) / 100,
            'to_amount' => ($data->transaction->toAmount ?? 0) / 100,
            'from_currency' => $data->transaction->fromCurrency ?? null,
            'to_currency' => $data->transaction->toCurrency ?? null,
            'merchant_id' => $data->transaction->merchantId,
            'transaction_reference' => $data->transaction->transactionReference,
            'amount' => $data->transaction->amount / 100,
            'currency' => $data->transaction->currency,
            'fee_amount' => ($data->transaction->feeAmount ?? 0) / 100,
            'authorization_code' => $data->authorizationCode ?? null,
        ]);

        Log::channel('slack')->info('StartButton Conversion record created', ['conversion' => $conversion]);

        // Find the original Achat record
        $achat = Achat::where('ref_id', $conversion->transaction_reference)->first();

        if ($achat instanceof Achat) {
            $newStatus = PaymentStatus::getStatus($conversion->status);

            $achat->status = $newStatus;
            if ($achat->requestable) {
                $achat->requestable->status = $newStatus;
            }

            // We might need specific events for conversions

            $achat->save();
            if ($achat->requestable) {
                $achat->requestable->save();
            }
        }
    }
}
