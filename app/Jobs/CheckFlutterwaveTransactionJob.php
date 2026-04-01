<?php

namespace App\Jobs;

use App\Classes\FlutterwavePaymentHelper;
use App\Enums\PaymentStatus;
use App\Models\Achat;
use App\Models\FlutterwavePaymentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckFlutterwaveTransactionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 10;
    public int $backoff = 60;

    protected Achat $achat;

    public function __construct(Achat $achat)
    {
        $this->achat = $achat;
    }

    public function handle(): void
    {
        $this->achat->refresh();

        // Nothing to do if already settled
        if (in_array($this->achat->status, [PaymentStatus::SUCCESSFUL, PaymentStatus::FAILED])) {
            return;
        }

        try {
            $isCharge = $this->achat->requestable_type === FlutterwavePaymentRequest::class
                && $this->achat->requestable?->type === 'charge';

            $result = $isCharge
                ? FlutterwavePaymentHelper::checkRequestPayments($this->achat)
                : FlutterwavePaymentHelper::checkRequestPayout($this->achat);

            $finalStatuses = [PaymentStatus::SUCCESSFUL, PaymentStatus::FAILED];

            if (isset($result['success']) && $result['success']
                && !in_array($result['status'] ?? '', $finalStatuses)
                && $this->attempts() < $this->tries
            ) {
                $this->release(60);
            }
        } catch (\Throwable $e) {
            Log::error('CheckFlutterwaveTransactionJob error', [
                'achat_id' => $this->achat->id,
                'attempt'  => $this->attempts(),
                'error'    => $e->getMessage(),
            ]);

            if ($this->attempts() < $this->tries) {
                $this->release(60);
            }
        }
    }
}
