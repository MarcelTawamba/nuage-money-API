<?php

namespace App\Models;

use CoreProc\WalletPlus\Models\Traits\HasWallets;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ClientWallet
 *
 * @property int $id
 * @property string $client_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Client|null $client
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \CoreProc\WalletPlus\Models\Wallet> $wallets
 * @property-read int|null $wallets_count
 * @method static \Illuminate\Database\Eloquent\Builder|ClientWallet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientWallet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientWallet query()
 * @method static \Illuminate\Database\Eloquent\Builder|ClientWallet whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientWallet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientWallet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ClientWallet whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ClientWallet extends Model
{
    use HasWallets;
    public $table = 'client_wallets';

    public $fillable = [

        "client_id",

    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $nuage_env = env("NUAGE_ENV");

        if(strtoupper($nuage_env) === strtoupper("SANDBOX")) {
            $this->connection = env("AUTH_DB_CONNECTION", "mysql");
        }

    }

    public function wallets()
    {

        return $this->morphMany(Wallet::class, 'user' );
    }

    /**
     * @param int|string|null $walletType Can either be the name, or the wallet type ID. Can also be null if you're not
     * using wallet types.
     * @return Wallet
     */
    public function wallet($walletType = null)
    {
        if(is_null($walletType)) {
            return $this->wallets()->whereNull('wallet_type_id')->first();
        }

        if(is_int($walletType)) {
            return $this->wallets()->where('wallet_type_id', $walletType)->first();
        }

        if(is_string($walletType)) {
            return $this->wallets()->whereHas('walletType', function($q) use ($walletType) {
                return $q->where('name', $walletType);
            })->first();
        }
    }
}
