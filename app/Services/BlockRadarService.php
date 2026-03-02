<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BlockRadarService
{
    private string $baseUrl;
    private string $apiKey;
    private string $env;

    public function __construct()
    {
        $this->baseUrl = config('services.blockradar.base_url');
        $this->apiKey = config('services.blockradar.master_api_key');
        $this->env = config('services.blockradar.env');
    }

    /**
     * Get all available assets from BlockRadar
     */
    public function getAssets(): array
    {
        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'Accept' => 'application/json',
            ])->get("{$this->baseUrl}/assets");

            if ($response->successful()) {
                return $response->json('data', []);
            }

            Log::error('BlockRadar getAssets failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('BlockRadar getAssets exception', [
                'message' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get wallet details by ID
     */
    public function getWallet(string $walletId, bool $showPrivateKey = false): ?array
    {
        try {
            $url = "{$this->baseUrl}/wallets/{$walletId}";
            if ($showPrivateKey) {
                $url .= "?showPrivateKey=true";
            }

            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'Accept' => 'application/json',
            ])->get($url);

            if ($response->successful()) {
                return $response->json('data');
            }

            return null;
        } catch (\Exception $e) {
            Log::error('BlockRadar getWallet exception', [
                'wallet_id' => $walletId,
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Create a new customer deposit address under a master wallet
     */
    public function createAddress(string $masterWalletId, array $metadata, string $name = null): ?array
    {
        try {
            $payload = [
                'metadata' => $metadata,
                'disableAutoSweep' => true,
                'enableGaslessWithdraw' => true,
                'showPrivateKey' => false,
            ];

            if ($name) {
                $payload['name'] = $name;
            }

            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/wallets/{$masterWalletId}/addresses", $payload);

            if ($response->successful()) {
                return $response->json('data');
            }

            Log::error('BlockRadar createAddress failed', [
                'wallet_id' => $masterWalletId,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('BlockRadar createAddress exception', [
                'wallet_id' => $masterWalletId,
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Filter assets to only relevant ones for Nuage
     */
    public function filterRelevantAssets(array $assets): array
    {
        $supportedBlockchains = ['base', 'solana'];
        $supportedAssets = ['USDC', 'USDT', 'cNGN', 'SOL', 'ETH'];
        $network = $this->env === 'production' ? 'mainnet' : 'testnet';

        return array_filter($assets, function ($asset) use ($supportedBlockchains, $supportedAssets, $network) {
            return in_array($asset['blockchain']['slug'], $supportedBlockchains)
                && in_array($asset['symbol'], $supportedAssets)
                && $asset['network'] === $network
                && $asset['isActive'];
        });
    }

    /**
     * Get master wallet ID for a blockchain
     */
    public function getMasterWalletId(string $blockchainSlug): ?string
    {
        $configKey = match($blockchainSlug) {
            'base' => 'base_wallet_id',
            'solana' => 'solana_wallet_id',
            default => null,
        };

        return $configKey ? config("services.blockradar.{$configKey}") : null;
    }
}
