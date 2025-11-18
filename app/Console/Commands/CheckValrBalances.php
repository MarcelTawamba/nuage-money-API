<?php

namespace App\Console\Commands;

use App\Models\SystemLedger;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletType;
use App\Notifications\LowBalanceWarning;
use App\Services\VALR\ValrService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class CheckValrBalances extends Command
{
    protected $signature = 'balances:check-valr';
    protected $description = 'Check VALR balances and send warnings if low';

    public function handle()
    {
        try {
            $valrService = new ValrService();
            $thresholds = config('balance_thresholds.valr');
            
            Log::info('Checking VALR account balances');
            
            $response = $valrService->getBalances();

            // If getBalances returns an array, check for an error key or expected structure
            if (!is_array($response) || isset($response['error'])) {
                $this->error('Failed to fetch VALR balances');
                Log::error('VALR balance fetch failed', ['response' => $response]);
                return;
            }

            $balances = $response;
            
            $adminUser = User::where('service_provider', 'VALR')->first();
            
            if (!$adminUser) {
                $this->error('VALR admin user not found. Please run: php artisan db:seed --class=PSPAdminUsersSeeder');
                return;
            }

            $walletMap = [];

            foreach ($balances as $balance) {
                if (isset($balance['currency'])) {
                    $currency = $balance['currency'];
                    $available = floatval($balance['available'] ?? 0);
                    
                    $walletMap[$currency] = $available;
                    
                    Log::info("Processing VALR $currency: $available");

                    $walletType = WalletType::firstOrCreate(['name' => $currency], ['decimals' => 8]);

                    Wallet::updateOrCreate(
                        [
                            'user_id' => $adminUser->id,
                            'user_type' => SystemLedger::class,
                            'wallet_type_id' => $walletType->id,
                        ],
                        ['balance' => $available]
                    );
                }
            }

            foreach ($thresholds as $currency => $threshold) {
                $balance = $walletMap[$currency] ?? 0;

                if ($balance < $threshold) {
                    $this->warn("Low balance for $currency: $balance < $threshold");
                    
                    Notification::route('mail', env('BALANCE_ALERT_EMAIL', 'mtawamba@nuage.money'))
                        ->notify(new LowBalanceWarning('VALR', $currency, $balance, $threshold));
                    
                    Log::warning('VALR low balance alert', [
                        'currency' => $currency,
                        'balance' => $balance,
                        'threshold' => $threshold
                    ]);
                } else {
                    $this->info("$currency balance OK: $balance");
                }
            }

            $this->info('VALR balances checked and saved successfully.');
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::error('VALR balance check error', ['error' => $e->getMessage()]);
        }
    }
}
