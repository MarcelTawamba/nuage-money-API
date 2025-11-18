<?php

namespace App\Console\Commands;

use App\Models\SystemLedger;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletType;
use App\Notifications\LowBalanceWarning;
use App\Services\Fincra\FincraService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class CheckFincraBalances extends Command
{
    protected $signature = 'balances:check-fincra';
    protected $description = 'Check Fincra balances and send warnings if low';

    public function handle()
    {
        try {
            $fincraService = new FincraService();
            $thresholds = config('balance_thresholds.fincra');
            
            Log::info('Checking Fincra wallet balances');
            
            $wallets = $fincraService->getWalletBalance();
            
            if (!$wallets) {
                $this->error('Failed to fetch Fincra wallet balances');
                Log::error('Fincra wallet balance fetch failed');
                return;
            }

            $adminUser = User::where('service_provider', 'Fincra')->first();
            
            if (!$adminUser) {
                $this->error('Fincra admin user not found. Please run: php artisan db:seed --class=PSPAdminUsersSeeder');
                return;
            }

            $walletMap = [];

            foreach ($wallets as $wallet) {
                if (isset($wallet->currency)) {
                    $currency = $wallet->currency;
                    $balance = floatval($wallet->availableBalance ?? 0);
                    
                    $walletMap[$currency] = $balance;
                    
                    Log::info("Processing Fincra $currency: $balance");

                    $walletType = WalletType::firstOrCreate(['name' => $currency], ['decimals' => 2]);

                    Wallet::updateOrCreate(
                        [
                            'user_id' => $adminUser->id,
                            'user_type' => SystemLedger::class,
                            'wallet_type_id' => $walletType->id,
                        ],
                        ['balance' => $balance]
                    );
                }
            }

            foreach ($thresholds as $currency => $threshold) {
                $balance = $walletMap[$currency] ?? 0;

                if ($balance < $threshold) {
                    $this->warn("Low balance for $currency: $balance < $threshold");
                    
                    Notification::route('mail', env('BALANCE_ALERT_EMAIL', 'mtawamba@nuage.money'))
                        ->notify(new LowBalanceWarning('Fincra', $currency, $balance, $threshold));
                    
                    Log::warning('Fincra low balance alert', [
                        'currency' => $currency,
                        'balance' => $balance,
                        'threshold' => $threshold
                    ]);
                } else {
                    $this->info("$currency balance OK: $balance");
                }
            }

            $this->info('Fincra balances checked and saved successfully.');
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::error('Fincra balance check error', ['error' => $e->getMessage()]);
        }
    }
}
