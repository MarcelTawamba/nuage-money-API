<?php

namespace App\Models;

use CoreProc\WalletPlus\Models\Wallet as BaseWallet;
use CoreProc\WalletPlus\Models\WalletType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


/**
 * App\Models\Wallet
 *
 * @property-read \App\Models\Client|null $client
 * @property-read \App\Models\WalletType|null $currency
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transaction> $transactions
 * @property-read int|null $transactions_count
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet query()
 * @property int $id
 * @property int $client_id
 * @property int $currency_id
 * @property float $balance
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet whereBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet whereUpdatedAt($value)
 * @property string|null $user_type
 * @property int|null $user_id
 * @property int|null $wallet_type_id
 * @property int $raw_balance
 * @property-read Model|\Eloquent $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \CoreProc\WalletPlus\Models\WalletLedger> $walletLedgers
 * @property-read int|null $wallet_ledgers_count
 * @property-read WalletType|null $walletType
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet whereRawBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet whereUserType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wallet whereWalletTypeId($value)
 * @mixin \Eloquent
 */
class Wallet extends  BaseWallet
{
    public $table = 'wallets_nuage';


    public function currency(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {

        return $this->belongsTo(WalletType::class,'wallet_type_id');

    }

    public function transactions(): hasMany
    {

        return $this->hasMany(Transaction::class,'wallet_id');

    }

    public function sumPayIn(){
        return $this->transactions()->where("amount",">",0)->sum("amount");
    }

    public function sumPayOut(){
        return -1 * $this->transactions()->where("amount","<",0)->sum("amount");
    }



    public  function  toArray(): array
    {
        $new_array = [];
        $new_array["id"] = $this->id;

        if($this->user->client){
            $new_array["owner"] = $this->user->client->name;
            if($this->user->client->company == null){
                $new_array["company"] = "None";
                $new_array["user"] = "None";

            }else{
                $new_array["company"] =$this->user->client->company->name;
                $new_array["user"] = $this->user->client->user->name;
            }

        }else{
            $new_array["owner"] = $this->user->name;
            $new_array["company"] = "None";
            $new_array["user"] = "None";
        }

        $new_array["currency"] = $this->currency->name;
        $new_array["balance"] = $this->balance;
        return $new_array;
    }

    public  function  toApiArray(): array
    {
        $new_array = [];

        $new_array["currency"] = $this->currency->name;
        $new_array["balance"] = $this->balance;
        return $new_array;
    }

}
