<?php

namespace App\Jobs;

use App\Services\RehiveOfframpService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessRehiveWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $webhookData;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $webhookData)
    {
        $this->webhookData = $webhookData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(RehiveOfframpService $rehiveOfframpService)
    {
        Log::info('Processing Rehive webhook job:', $this->webhookData);

        if ($this->webhookData['event'] === 'transaction.execute') {
            // For now, we'll just call the RehiveOfframpService.
            // In the future, we can add logic to select the correct payment provider.
            // like decide between Fincra, StartButton or any other Payment service provider PSP.
            $rehiveOfframpService->processTransaction($this->webhookData);
        }
    }
}
