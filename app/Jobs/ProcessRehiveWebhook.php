<?php

namespace App\Jobs;

use App\Classes\StartButtonAfricaPaymentHelper;
use App\Enums\RehiveEventType;
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
    public function handle(StartButtonAfricaPaymentHelper $startButtonAfricaPaymentHelper)
    {
        Log::info('***JOB: Processing Rehive webhook event: ', $this->webhookData['subtype']);

        $event = $this->webhookData['subtype'];
        $data = [
            'ref_id' => $this->webhookData['id'],
            'service' => $this->webhookData['creator']['id'],
            'amount' => $this->webhookData['total_amount'],
            'currency' => $this->webhookData['currency']['code'],
            'country' => $this->webhookData['metadata']['location'] ?? 'Nigeria',
        ];

        switch ($event) {
            case RehiveEventType::WITHDRAW_MANUAL:
                $startButtonAfricaPaymentHelper->initPayout($data);
                break;
            // Add more cases for other event types here
            default:
                Log::warning('Unhandled Rehive webhook event received.', ['event' => $event]);
                break;
        }
    }
}
