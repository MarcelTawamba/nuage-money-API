<?php

namespace App\Jobs;

use App\Classes\StartButtonAfricaPaymentHelper;
use App\Models\Achat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckStartButtonTransactionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        StartButtonAfricaPaymentHelper::checkRequestPayout($this->achat);
    }
}
