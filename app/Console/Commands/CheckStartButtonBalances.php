<?php

namespace App\Console\Commands;

use App\Classes\StartButtonAfricaPaymentHelper;
use App\Notifications\LowBalanceWarning;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class CheckStartButtonBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'balances:check-startbutton';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check StartButton balances and send a warning if they are low';

    /**
     * Execute the console command.
     */
    public function handle(StartButtonAfricaPaymentHelper $startButtonAfricaPaymentHelper)
    {
        $thresholds = config('balance_thresholds.startbutton');

        foreach ($thresholds as $currency => $threshold) {
            $balance = $startButtonAfricaPaymentHelper->getWalletBalance($currency);

            if ($balance < $threshold) {
                Notification::route('mail', 'mtawamba@nuage.money')
                    ->notify(new LowBalanceWarning($currency, $balance, $threshold));
            }
        }

        $this->info('StartButton balances checked successfully.');
    }
}
