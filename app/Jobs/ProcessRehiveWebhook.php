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
        Log::info('Processing Rehive webhook job:', $this->webhookData);

        $event = $this->webhookData['event'];
        $data = $this->webhookData['data'];

        switch ($event) {
            case RehiveEventType::INITIATE_NGN_PAYOUT:
            case RehiveEventType::INITIATE_GHS_PAYOUT:
            case RehiveEventType::INITIATE_ZAR_PAYOUT:
            case RehiveEventType::INITIATE_KES_PAYOUT:
            case RehiveEventType::INITIATE_UGX_PAYOUT:
            case RehiveEventType::INITIATE_RWF_PAYOUT:
            case RehiveEventType::INITIATE_XOF_PAYOUT:
            case RehiveEventType::INITIATE_XAF_PAYOUT:
                $startButtonAfricaPaymentHelper->initPayout($data);
                break;
            // Add more cases for other event types here
            default:
                Log::warning('Unhandled Rehive webhook event received.', ['event' => $event]);
                break;
        }
    }
}
