<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * App\Models\ToupesuPaymentRequest
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ToupesuPaymentRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ToupesuPaymentRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ToupesuPaymentRequest query()
 * @property int $id
 * @property string $msidn
 * @property string $pay_token
 * @property string $status
 * @property string $reason
 * @property string $payment_method
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ToupesuPaymentRequest whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ToupesuPaymentRequest whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ToupesuPaymentRequest whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ToupesuPaymentRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ToupesuPaymentRequest whereCurrencyCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ToupesuPaymentRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ToupesuPaymentRequest whereMsidn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ToupesuPaymentRequest wherePayToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ToupesuPaymentRequest whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ToupesuPaymentRequest whereRefId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ToupesuPaymentRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ToupesuPaymentRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ToupesuPaymentRequest whereUserRefId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ToupesuPaymentRequest whereJobTries($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ToupesuPaymentRequest wherePaymentMethod($value)
 * @mixin \Eloquent
 */
class ToupesuPaymentRequest extends Model
{
    use HasFactory;


    public $table = 'toupesu_payment_request';

    public $fillable = [

        "msidn",
        "pay_token",
        "payment_method",
        "status",
        'reason',
    ];

    protected $casts = [

        "msidn"=>"string",
        "pay_token"=>"string",
        "status"=>"string",
        'reason'=>"string",
        "payment_method"=>"string",

    ];

    public static array $rules = [

    ];

    function  toArray()
    {
        return [
            "msidn"=>$this->msidn,
            "payment_method"=>$this->payment_method,
        ];
    }

}
