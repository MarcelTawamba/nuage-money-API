<?php

namespace App\Jobs;

use App\Models\StartButtonBank;
use App\Services\StartButtonAfricaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GetStartButtonBanksToBDJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $currency;
    protected string $type;
    protected ?string $countryCode;

    /**
     * Create a new job instance.
     */
    public function __construct(string $currency, string $type = 'bank', string $countryCode = null)
    {
        $this->currency = $currency;
        $this->type = $type;
        $this->countryCode = $countryCode;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!env('STARTBUTTON_SECRET_KEY')) {
            Log::info('StartButton secret key is not set. Skipping bank list sync.');
            return;
        }

        $starButton = new StartButtonAfricaService();
        $banksRes = $starButton->getListOfBanks($this->currency, $this->type, $this->countryCode);

        if(!$banksRes["success"]) {
            Log::error('Failed to fetch bank list from StartButton', ['response' => $banksRes]);
            return;
        }

        $banks = $banksRes["data"];

        foreach ($banks as $bank) {
            if (isset($bank->id)) {
                 StartButtonBank::updateOrCreate(
                    ['startbutton_id' => $bank->id],
                    [
                        'name' => $bank->name,
                        'code' => $bank->code,
                        'currency' => $this->currency,
                    ]
                );
            }
        }

        Log::info("Done syncing banks for {$this->currency}");
    }
}
