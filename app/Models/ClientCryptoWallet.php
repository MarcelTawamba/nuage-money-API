<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientCryptoWallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'crypto_asset_id',
        'blockradar_address_id',
        'deposit_address',
        'balance',
        'is_active',
        'last_deposit_at',
        'last_withdrawal_at',
    ];

    protected $casts = [
        'balance' => 'decimal:18',
        'is_active' => 'boolean',
        'last_deposit_at' => 'datetime',
        'last_withdrawal_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function cryptoAsset(): BelongsTo
    {
        return $this->belongsTo(CryptoAsset::class, 'crypto_asset_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByClient($query, string $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->balance, $this->cryptoAsset->decimals, '.', ',');
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'blockchain' => $this->cryptoAsset->blockchain_name,
            'asset' => $this->cryptoAsset->asset_symbol,
            'deposit_address' => $this->deposit_address,
            'balance' => $this->formatted_balance,
            'network' => $this->cryptoAsset->network,
            'is_active' => $this->is_active,
            'last_deposit_at' => $this->last_deposit_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
