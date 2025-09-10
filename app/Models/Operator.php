<?php

namespace App\Models;

use CoreProc\WalletPlus\Models\WalletType;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Fees
 *
 * @property int $id
 * @property int $country_id
 * @property int $currency_id
 * @property string $method_class
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $type
 * @property string $method_name
 * @property string $method_type
 * @property float $fees
 * @property string $fee_type
 * @property float $operator_fees
 * @property string $operator_fee_type
 * @property-read \App\Models\CountryAvaillable|null $country
 * @property-read WalletType|null $currency
 * @method static \Illuminate\Database\Eloquent\Builder|Operator newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Operator newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Operator query()
 * @method static \Illuminate\Database\Eloquent\Builder|Operator whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Operator whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Operator whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Operator whereFeeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Operator whereFees($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Operator whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Operator whereMethodClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Operator whereMethodName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Operator whereMethodType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Operator whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Operator whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Operator whereOperatorFeeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Operator whereOperatorFees($value)
 * @mixin \Eloquent
 */
class Operator extends Model
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $nuage_env = env("NUAGE_ENV");

        if(strtoupper($nuage_env) === strtoupper("SANDBOX")) {
            $this->connection = env("AUTH_DB_CONNECTION", "mysql");
        }

    }

    public $table = 'fees_table';

    public $fillable = [
        'country_id',
        'currency_id',
        'method_name',
        "method_class",
        "method_type",
        "type",
        "operator_fee_type",
        'operator_fees',
        "fee_type",
        'fees'
    ];

    protected $casts = [
        'country_id' => 'integer',
        'currency_id' => 'integer',
        'method_class' => 'string',
        'method_name' => 'string',
        'type' => 'string',
        'fee_type' => 'string',
        'method_type' => 'string',
        'fees' => 'float',
        "operator_fee_type"=>"string",
        'operator_fees'=>"float"
    ];

    public static array $rules = [
        'country_id' => 'required',
        'currency_id' => 'required',
        'method_class' => 'required',
        'method_name' => 'required',
        'type' => 'required',
        'method_type' => 'required',
        'operator_fee_type' => 'required',
        'operator_fees' => 'numeric',
    ];

    public function country(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {

        return $this->belongsTo(CountryAvaillable::class,"country_id");
    }

    public function currency(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {

        return $this->belongsTo(WalletType::class,"currency_id");
    }

    public  function  toArray(): array
    {
        $new_array = [];

        $new_array["name"] = $this->method_name;
        $new_array["key"] = $this->method_class;
        $new_array["currency"] = $this->currency->name;
        $new_array["country"] = $this->country->code;
        $new_array["fees"] = $this->fees;
        $new_array["fees_type"] = $this->fee_type;
        $new_array["method"] = $this->method_type;
        $new_array["request_type"] = $this->type;

        return $new_array;
    }

    public function methodName()
    {
        $name = explode('_',$this->method_type);
        $na = '';

        foreach ($name as $item){
            $na .= strtoupper($item) . " ";
        }

        return $na;
    }

}
