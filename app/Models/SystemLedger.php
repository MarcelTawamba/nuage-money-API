<?php

namespace App\Models;

use CoreProc\WalletPlus\Models\Traits\HasWallets;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\SystemLeger
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \CoreProc\WalletPlus\Models\Wallet> $wallets
 * @property-read int|null $wallets_count
 * @method static \Illuminate\Database\Eloquent\Builder|SystemLedger newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemLedger newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemLedger query()
 * @method static \Illuminate\Database\Eloquent\Builder|SystemLedger whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemLedger whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemLedger whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemLedger whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SystemLedger whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SystemLedger extends Model
{
    use HasWallets;
    public $table = 'system_ledgers';

    public $fillable = [
        'name',
        'description'
    ];

    protected $casts = [
        'name' => 'string',
        'description' => 'string'
    ];

    public static array $rules = [
        'name' => 'required|unique:App\Models\SystemLedger'
    ];

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
        return $this->morphMany(Wallet::class, 'user');
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
