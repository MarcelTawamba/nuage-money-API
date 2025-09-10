<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * App\Models\Transaction
 *
 * @property-read Model|\Eloquent $requestable
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction query()
 * @property int $id
 * @property string $reference
 * @property int $wallet_id
 * @property float $balance_after
 * @property float $balance_before
 * @property float $amount
 * @property float $description
 * @property string $achatable_type
 * @property int $achatable_id
 * @property boolean $refund
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereRequestableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereRequestableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereWalletBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereWalletId($value)
 * @property-read \App\Models\Wallet|null $wallet
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereFees($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereInitial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereOperatorFees($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereRefund($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereBalanceAfter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereBalanceBefore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereDescription($value)
 * @property-read Model|\Eloquent $achatable
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereAchatableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereAchatableType($value)
 * @mixin \Eloquent
 */
class Transaction extends Model
{
    public $table = 'transaction';

    public $fillable = [
        'reference',
        'wallet_id',
        'balance_before',
        'balance_after',
        'amount',
        'achatable_type',
        'achatable_id',
        "description",
        "refund",
    ];

    protected $casts = [
        'reference' => 'string',
        'wallet_id' => 'integer',
        'wallet_balance' => 'double',
        'amount' => 'double',
        'requestable_type' => 'string',
        'requestable_id' => 'integer',
        "fees"=>"double",
        "operator_fees"=>"double",
        "refund"=> "boolean",
        'initial' => 'integer',
    ];

    public static array $rules = [
        'reference' => 'required',
        'wallet_id' => 'required',
        'balance_before' => 'numeric',
        'balance_after' => 'numeric',
        'amount' => 'numeric',
        'achatable_type' => 'required',
        'achatable_id' => 'required',
        "fees"=>"required",
        "operator_fees"=>"required",
    ];


    public function achatable(): MorphTo
    {
        return $this->morphTo();
    }

    public function wallet(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Wallet::class,'wallet_id');
    }

    public  function  source()
    {


       if($this->achatable->requestable_type == ToupesuPaymentRequest::class){
           return  ["msidn"=>$this->achatable->requestable->msidn,"method"=>$this->achatable->requestable->payment_method] ;
       }else if($this->achatable->requestable_type == StartButtonPayInRequest::class){
           return  ["email"=>$this->achatable->requestable->email,"name"=>"unknown"] ;
       }else if($this->achatable->requestable_type == StartButtonPayOutRequest::class){

           return  [

               "bank_code"=>$this->achatable->requestable->bank_code,
               "account_name"=>$this->achatable->requestable->account_name,
               "account_number"=>$this->achatable->requestable->account_number,

           ] ;
       }
       else{
           return [];
       }

    }

    public  function  toApiArray(): array
    {
        $new_array = [];
        $new_array["transaction_id"] = $this->id;
        $new_array["wallet"] = $this->wallet->currency->name;
        $new_array["ref_id"] = $this->achatable->user_ref_id;
        $new_array["pay_token"] = $this->reference;
        $new_array["amount"] = abs($this->amount) ;
        $new_array["source"] = $this->source() ;
        $new_array["date"] = $this->created_at ;
        return $new_array;
    }

    public  function  toArray(): array
    {
        $new_array = parent::toArray();

        $new_array["wallet"] = $this->wallet->currency->name;
        if($this->wallet->user->client){
            $new_array["app"] = $this->wallet->user->client->name;
        }else{
            $new_array["app"] = $this->wallet->user->name;
        }

        $new_array["source"] = $this->source() ;
        $new_array["date"] = $this->created_at ;
        return $new_array;
    }



}
