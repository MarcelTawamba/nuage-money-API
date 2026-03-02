<?php

namespace App\Jobs;

use App\Classes\YellowCardPaymentHelper;
use App\Models\Achat;
use App\Models\YellowCardCollection;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckYellowCardTransactionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 10;
    public $backoff = 60;

    protected $achat;

    /**
     * Create a new job instance.
     *
     * @param Achat $achat
     */
    public function __construct(Achat $achat)
    {
        $this->achat = $achat;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // If the transaction is already in a final state, don't do anything
        if (in_array($this->achat->status, [\App\Enums\PaymentStatus::SUCCESSFUL, \App\Enums\PaymentStatus::FAILED])) {
            return;
        }

        $result = [];
        if ($this->achat->requestable_type == YellowCardCollection::class) {
            $result = YellowCardPaymentHelper::checkRequestCollection($this->achat);
        } else {
            $result = YellowCardPaymentHelper::checkRequestPayout($this->achat);
        }

        // If the status is still not final, re-dispatch the job with a delay
        // We only do this if the check itself was "successful" (API call worked)
        if (isset($result['success']) && $result['success']) {
            $finalStatus = [
                \App\Enums\PaymentStatus::SUCCESSFUL, 
                \App\Enums\PaymentStatus::FAILED
            ];
            
            if (!in_array($result['status'] ?? '', $finalStatus)) {
                // Re-dispatch if not at max attempts
                if ($this->attempts() < $this->tries) {
                    $this->release(60); // Retry in 60 seconds
                }
            }
        }
    }
}
