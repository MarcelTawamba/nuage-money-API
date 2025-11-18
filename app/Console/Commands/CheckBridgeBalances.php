<?php

namespace App\Console\Commands;

use App\Models\SystemLedger;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletType;
use App\Notifications\LowBalanceWarning;
use App\Services\Bridge\BridgeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class CheckBridgeBalances extends Command
{
    protected $signature = 'balances:check-bridge';
    protected $description = 'Check Bridge liquidity balances and send warnings if low';

    public function handle()
    {
        try {
            $bridgeService = new BridgeService();
            $thresholds = config('balance_thresholds.bridge');
            
            Log::info('Checking Bridge liquidity balances');
            
            $response = $bridgeService->getLiquidityBalances();
            
            if (!$response->successful()) {
                $this->error('Failed to fetch Bridge balances');
                Log::error('Bridge balance fetch failed', ['response' => $response->body()]);
                return;
            }

            $liquidityData = $response->json();
            
            $adminUser = User::where('service_provider', 'Bridge')->first();
            
            if (!$adminUser) {
                $this->error('Bridge admin user not found. Please run: php artisan db:seed --class=PSPAdminUsersSeeder');
                return;
            }

            $walletMap = [];

            // Bridge returns liquidity balances per currency
            if (isset($liquidityData['balances']) && is_array($liquidityData['balances'])) {
                foreach ($liquidityData['balances'] as $balanceItem) {
                    if (isset($balanceItem['currency'])) {
                        $currency = $balanceItem['currency'];
                        $available = floatval($balanceItem['available'] ?? 0);
                        
                        $walletMap[$currency] = $available;
                        
                        Log::info("Processing Bridge $currency: $available");

                        $walletType = WalletType::firstOrCreate(['name' => $currency], ['decimals' => 2]);

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
            }

            foreach ($thresholds as $currency => $threshold) {
                $balance = $walletMap[$currency] ?? 0;

                if ($balance < $threshold) {
                    $this->warn("Low balance for $currency: $balance < $threshold");
                    
                    Notification::route('mail', env('BALANCE_ALERT_EMAIL', 'mtawamba@nuage.money'))
                        ->notify(new LowBalanceWarning('Bridge', $currency, $balance, $threshold));
                    
                    Log::warning('Bridge low balance alert', [
                        'currency' => $currency,
                        'balance' => $balance,
                        'threshold' => $threshold
                    ]);
                } else {
                    $this->info("$currency balance OK: $balance");
                }
            }

            $this->info('Bridge balances checked and saved successfully.');
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::error('Bridge balance check error', ['error' => $e->getMessage()]);
        }
    }
}
