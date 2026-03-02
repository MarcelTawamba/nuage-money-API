<?php

namespace Database\Seeders;

use App\Models\CryptoAsset;
use App\Services\BlockRadarService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class CryptoAssetsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $blockRadarService = app(BlockRadarService::class);
        
        // Fetch all assets from BlockRadar API
        $allAssets = $blockRadarService->getAssets();
        
        if (empty($allAssets)) {
            $this->command->error('Failed to fetch assets from BlockRadar API');
            return;
        }
        
        $this->command->info("Fetched " . count($allAssets) . " assets from BlockRadar");
        
        // Filter to only relevant assets
        $relevantAssets = $blockRadarService->filterRelevantAssets($allAssets);
        
        $this->command->info("Filtered to " . count($relevantAssets) . " relevant assets");
        
        $imported = 0;
        $skipped = 0;
        
        foreach ($relevantAssets as $asset) {
            $blockchainSlug = $asset['blockchain']['slug'];
            $masterWalletId = $blockRadarService->getMasterWalletId($blockchainSlug);
            
            if (!$masterWalletId) {
                $this->command->warn("No master wallet configured for {$blockchainSlug}, skipping {$asset['symbol']}");
                $skipped++;
                continue;
            }
            
            CryptoAsset::updateOrCreate(
                [
                    'blockradar_asset_id' => $asset['id'],
                ],
                [
                    'blockchain_name' => $asset['blockchain']['name'],
                    'blockchain_slug' => $blockchainSlug,
                    'asset_symbol' => $asset['symbol'],
                    'asset_name' => $asset['name'],
                    'network' => $asset['network'],
                    'blockradar_wallet_id' => $masterWalletId,
                    'blockradar_blockchain_id' => $asset['blockchain']['id'],
                    'contract_address' => $asset['address'],
                    'decimals' => $asset['decimals'],
                    'currency' => $asset['currency'],
                    'logo_url' => $asset['logoUrl'] ?? null,
                    'is_active' => $asset['isActive'],
                    'is_native' => $asset['isNative'],
                    'is_evm_compatible' => $asset['blockchain']['isEvmCompatible'],
                ]
            );
            
            $imported++;
            $this->command->info("✓ Imported: {$asset['name']} ({$asset['symbol']}) on {$asset['blockchain']['name']}");
        }
        
        $this->command->info("✅ Import complete: {$imported} imported, {$skipped} skipped");
    }
}
