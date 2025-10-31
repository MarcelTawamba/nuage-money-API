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
        Log::info('***JOB: Processing Rehive webhook event:', ['event' => $this->webhookData['event']]);

        $subType = $this->webhookData['data']['subtype'];
        $data = [
            'ref_id' => $this->webhookData['data']['reference'] ?? $this->webhookData['id'],
            'event' => $this->webhookData['event'],
            'event_subtype' => $this->webhookData['data']['subtype'],
            'client_id' => $this->webhookData['data']['user']['id'],
            'first_name' => $this->webhookData['data']['user']['first_name'],
            'last_name' => $this->webhookData['data']['user']['last_name'],
            'user_name' => $this->webhookData['data']['user']['username'],
            'company_name' => $this->webhookData['company'],
            'company_phone_number' => $this->webhookData['data']['creator']['mobile'] ?? '+23723456789',
            'company_email' => $this->webhookData['data']['creator']['email'] ?? 'admin@nuage.money',
            'user_email' => $this->webhookData['data']['user']['email'],
            'user_phone_number' => $this->webhookData['data']['user']['mobile'] ?? '+23723456789',
            'service' => 'StartButton',
            'amount' => $this->webhookData['data']['total_amount'],
            'account_number' => $this->webhookData['data']['account'],
            'account_balance' => $this->webhookData['data']['balance'],
            'transaction_type' => $this->webhookData['data']['tx_type'],
            'currency' => $this->webhookData['data']['currency']['code'],
            'reference' => $this->webhookData['data']['reference'],
            'status' => $this->webhookData['data']['status'],
            'metadata' => $this->webhookData['data']['metadata'],
            'country' => $this->webhookData['data']['metadata']['location'] ?? 'NGA',
            'fee' => $this->webhookData['data']['fee']
        ];

        switch ($subType) {
            case RehiveEventType::WITHDRAW_MANUAL:
                $startButtonAfricaPaymentHelper->initPayout($data);
                break;
            // Add more cases for other event types here
            default:
                Log::warning('Unhandled Rehive webhook event subType received.', ['event.subType' => $subType]);
                break;
        }
    }
}
