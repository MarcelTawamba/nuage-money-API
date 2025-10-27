<?php

namespace App\Services\StartButton;

use App\Enums\RehiveEventType;
use App\Models\Achat;
use App\Models\StartButton\Transfer;
use App\Enums\PaymentStatus;
use App\Events\PayOutFailureEvent;
use App\Events\PayOutSuccessEvent;
use App\Services\RehiveService;
use Illuminate\Support\Facades\Log;

class TransferService
{
    public function __construct(private RehiveService $rehiveService)
    {
    }

    public function handle(object $data)
    {
        // Create a new transfer record
        $transfer = Transfer::create([
            'transaction_id' => $data->transaction->_id,
            'transaction_type' => $data->transaction->transType,
            'status' => $data->transaction->status,
            'fee_amount' => ($data->transaction->feeAmount ?? 0) / 100,
            'merchant_id' => $data->transaction->merchantId,
            'transaction_reference' => $data->transaction->transactionReference,
            'gateway_reference' => $data->transaction->gatewayReference ?? null,
            'amount' => $data->transaction->amount / 100,
            'currency' => $data->transaction->currency,
            'recipient' => isset($data->transaction->recipient) ? json_encode($data->transaction->recipient) : null,
            'authorization_code' => $data->authorizationCode ?? null,
        ]);

        Log::channel('slack')->info('StartButton Transfer record created', ['transfer' => $transfer]);

        // Find the original Achat record
        $achat = Achat::where('ref_id', $transfer->transaction_reference)->first();

        if ($achat instanceof Achat) {
            $newStatus = PaymentStatus::getStatus($transfer->status);

            $achat->status = $newStatus;
            if ($achat->requestable) {
                $achat->requestable->status = $newStatus;
            }

            if ($newStatus == PaymentStatus::SUCCESSFUL) {
                PayOutSuccessEvent::dispatch($achat);
                Log::info('Dispatched PayOutSuccessEvent for Achat ID: ' . $achat->id);
            } elseif ($newStatus == PaymentStatus::FAILED) {
                PayOutFailureEvent::dispatch($achat);
                Log::info('Dispatched PayOutFailureEvent for Achat ID: ' . $achat->id);
            }

            $achat->save();
            if ($achat->requestable) {
                $achat->requestable->save();
            }

            // Send status update to Rehive
            if ($achat->user_ref_id) {
                $transactions = [
                    [
                        'id' => $achat->ref_id,
                        'tx_type' => 'debit',
                        'subtype' => RehiveEventType::WITHDRAW_MANUAL,
                        'account' => config('services.rehive.operational_accounts.' . $achat->currency),
                    ],
                ];
                $this->rehiveService->updateTransactionStatus($transactions, strtolower($newStatus));
            }
        }
    }
}
