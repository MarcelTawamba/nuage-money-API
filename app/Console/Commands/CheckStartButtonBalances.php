<?php

namespace App\Console\Commands;

use App\Models\SystemLedger;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletType;
use App\Notifications\LowBalanceWarning;
use App\Services\StartButton\AfricaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
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
        $this->info('[START] CheckStartButtonBalances - STARTBUTTON_ROOT_URL: '.env('STARTBUTTON_ROOT_URL'));
        
        $walletBalanceResponse = $startButtonAfricaService->getWalletBalance();
        $this->info('StartButton wallet balance response: '.json_encode($walletBalanceResponse));
        
        $wallets = [];
        if (isset($walletBalanceResponse['success']) && $walletBalanceResponse['success']) {
            $wallets = $walletBalanceResponse['data'];
            $this->info('Wallets retrieved: '.count($wallets));
        } elseif (isset($walletBalanceResponse['data'])) {
            // Handle case where response has data but no explicit success flag
            $wallets = $walletBalanceResponse['data'];
            $this->info('Wallets retrieved (no success flag): '.count($wallets));
        } else {
            $responseArray = is_array($walletBalanceResponse) ? $walletBalanceResponse : $walletBalanceResponse->json();
            $this->error('API call failed - response structure: '.json_encode(array_keys($responseArray)));
            return 1;
        }

        // Create a map of wallets with currency as the key
        $walletMap = [];
        foreach ($wallets as $index => $wallet) {
            $this->info("Wallet $index structure: ".json_encode($wallet));
            if (isset($wallet->currency)) {
                $walletMap[$wallet->currency] = $wallet->availableBalance;
            } elseif (is_array($wallet) && isset($wallet['currency'])) {
                $walletMap[$wallet['currency']] = $wallet['availableBalance'];
            } else {
                $this->error("Wallet $index missing currency: ".json_encode($wallet));
            }
        }

        $adminUser = User::where('service_provider', 'StartButton')->first();

        if (!$adminUser) {
            $this->error('StartButton admin user not found. Please run the seeder.');
            return 1;
        }
        
        $this->info('Admin user found: ID='.$adminUser->id);

        // Save the balances to the database
        $this->info('Processing '.count($walletMap).' currencies');
        foreach ($walletMap as $currency => $balance) {
            $this->info("Processing currency: $currency with balance: $balance");

            $walletType = WalletType::firstOrCreate(
                [
                    'name' => $currency,
                    'decimals' => 0,
                ]
            );

            $wallet = Wallet::updateOrCreate(
                [
                    'user_id' => $adminUser->id,
                    'user_type' => SystemLedger::class,
                    'wallet_type_id' => $walletType->id,
                ],
                [
                    'raw_balance' => $balance / 100,
                ]
            );
            $this->info("Saved wallet ID={$wallet->id}, user_type={$wallet->user_type}, raw_balance={$wallet->raw_balance}");
        }

        // foreach ($thresholds as $currency => $threshold) {
        //     $balance = $walletMap[$currency] ?? 0;

        //     if ($balance < $threshold) {
        //         Notification::route('mail', env('BALANCE_ALERT_EMAIL', 'mtawamba@nuage.money'))
        //             ->notify(new LowBalanceWarning('StartButton', $currency, $balance, $threshold));
        //     }
        // }

        $this->info('[DONE] StartButton balances checked and saved successfully.');
        return 0;
    }
}
