<?php

namespace App\Console\Commands;

use App\Notifications\LowBalanceWarning;
use App\Services\StartButton\AfricaService;
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
    public function handle(AfricaService $startButtonAfricaService)
    {
        $thresholds = config('balance_thresholds.startbutton');
        $walletBalanceResponse = $startButtonAfricaService->getWalletBalance();
        $wallets = [];
        if ($walletBalanceResponse['success']) {
            $wallets = $walletBalanceResponse['data'];
        }

        // Create a map of wallets with currency as the key
        $walletMap = [];
        foreach ($wallets as $wallet) {
            if (isset($wallet['currency'])) {
                $walletMap[$wallet['currency']] = $wallet['availableBalance'];
            }
        }

        foreach ($thresholds as $currency => $threshold) {
            $balance = $walletMap[$currency] ?? 0;

            if ($balance < $threshold) {
                Notification::route('mail', 'mtawamba@nuage.money')
                    ->notify(new LowBalanceWarning($currency, $balance, $threshold));
            }
        }

        $this->info('StartButton balances checked successfully.');
    }
}
