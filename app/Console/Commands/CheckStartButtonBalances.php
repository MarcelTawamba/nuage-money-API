<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\WalletType;
use \App\Models\Wallet;
use App\Notifications\LowBalanceWarning;
use App\Services\StartButton\AfricaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
        Log::info('STARTBUTTON_ROOT_URL: ' . env('STARTBUTTON_ROOT_URL'));
        $thresholds = config('balance_thresholds.startbutton');
        $walletBalanceResponse = $startButtonAfricaService->getWalletBalance();
        Log::info('StartButton wallet balance response: ' . json_encode($walletBalanceResponse));
        $wallets = [];
        if ($walletBalanceResponse['success']) {
            $wallets = $walletBalanceResponse['data'];
        }

        // Create a map of wallets with currency as the key
        $walletMap = [];
        foreach ($wallets as $wallet) {
            if (isset($wallet->currency)) {
                $walletMap[$wallet->currency] = $wallet->availableBalance;
            }
        }

        $adminUser = User::where('is_admin', true)->first();

        if (!$adminUser) {
            $adminUser = User::create([
                'name' => 'admin',
                'email' => 'admin@nuage.money',
                'password' => Hash::make(Str::random(10)),
                'is_admin' => true,
                'email_verified_at' => now(),
                'phone_number' => Str::random(10),
                'country_code' => 'US',
                'account_type' => 'individual',
            ]);
            $this->info('Admin user created.');
        }

        // Save the balances to the database
        foreach ($walletMap as $currency => $balance) {
            Log::info("Processing currency: $currency");

            $walletType = WalletType::firstOrCreate(
                [
                    'name' => $currency,
                    'decimals' => 0
                ]
            );
            Log::info("Wallet type for $currency: " . json_encode($walletType));

            $wallet = Wallet::updateOrCreate(
                [
                    'user_id' => $adminUser->id,
                    'user_type' => get_class($adminUser),
                    'wallet_type_id' => $walletType->id,
                ],
                [
                    'balance' => $balance,
                ]
            );
            Log::info("Wallet for $currency: " . json_encode($wallet));
        }

        foreach ($thresholds as $currency => $threshold) {
            $balance = $walletMap[$currency] ?? 0;

            if ($balance < $threshold) {
                Notification::route('mail', 'mtawamba@nuage.money')
                    ->notify(new LowBalanceWarning($currency, $balance, $threshold));
            }
        }

        $this->info('StartButton balances checked and saved successfully.');
    }
}
