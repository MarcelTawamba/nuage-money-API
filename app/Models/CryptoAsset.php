<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CryptoAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'blockchain_name',
        'blockchain_slug',
        'asset_symbol',
        'asset_name',
        'network',
        'blockradar_asset_id',
        'blockradar_wallet_id',
        'blockradar_blockchain_id',
        'contract_address',
        'decimals',
        'currency',
        'logo_url',
        'is_active',
        'is_native',
        'is_evm_compatible',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_native' => 'boolean',
        'is_evm_compatible' => 'boolean',
        'decimals' => 'integer',
    ];

    public function clientWallets(): HasMany
    {
        return $this->hasMany(ClientCryptoWallet::class, 'crypto_asset_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeTestnet($query)
    {
        return $query->where('network', 'testnet');
    }

    public function scopeMainnet($query)
    {
        return $query->where('network', 'mainnet');
    }

    public function scopeByBlockchain($query, string $blockchain)
    {
        return $query->where('blockchain_slug', $blockchain);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->asset_name} ({$this->asset_symbol}) on {$this->blockchain_name}";
    }

    public function getDisplayNameAttribute(): string
    {
        return "{$this->asset_symbol} - {$this->blockchain_name} ({$this->network})";
    }
}
