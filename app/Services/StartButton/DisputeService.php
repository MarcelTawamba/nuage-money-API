<?php

namespace App\Services\StartButton;

use App\Models\Achat;
use App\Models\StartButton\Dispute;
use App\Enums\PaymentStatus;
use Illuminate\Support\Facades\Log;

class DisputeService
{
    public function handle(object $data)
    {
        // Create a new dispute record
        $dispute = Dispute::create([
            'transaction_reference' => $data->transactionReference,
            'dispute_reference' => $data->disputeReference,
            'currency' => $data->currency,
            'amount' => $data->amount / 100,
            'elapse_time' => $data->elapseTime ?? null,
            'type' => $data->type,
            'customer_email' => $data->customerEmail,
            'status' => $data->status,
            'reason' => $data->reason,
        ]);

        Log::channel('slack')->info('StartButton Dispute record created', ['dispute' => $dispute]);

        // Find the original Achat record
        $achat = Achat::where('ref_id', $dispute->transaction_reference)->first();

        if ($achat instanceof Achat) {
            Log::info('Achat record related to the dispute', ['achat' => $achat]);
        }
    }
}
