<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ExchangeRateMargin
 *
 * @OA\Schema (
 *      schema="ExchangeRateMargin",
 *      required={"from_currency","to_currency","margin"},
 *      @OA\Property(
 *          property="from_currency",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="to_currency",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="margin",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="number",
 *          format="number"
 *      ),
 *      @OA\Property(
 *          property="created_at",
 *          description="",
 *          readOnly=true,
 *          nullable=true,
 *          type="string",
 *          format="date-time"
 *      ),
 *      @OA\Property(
 *          property="updated_at",
 *          description="",
 *          readOnly=true,
 *          nullable=true,
 *          type="string",
 *          format="date-time"
 *      )
 * )
 * @property int $id
 * @property string $from_currency
 * @property string $to_currency
 * @property float $margin
 * @property float $rate
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeRateMargin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeRateMargin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeRateMargin query()
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeRateMargin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeRateMargin whereFromCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeRateMargin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeRateMargin whereMargin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeRateMargin whereToCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeRateMargin whereUpdatedAt($value)
 * @mixin \Eloquent
 */class ExchangeRateMargin extends Model
{
    public $table = 'exchange_rate_margins';

    public $fillable = [
        'from_currency',
        'to_currency',
        'margin',
        'rate'
    ];

    protected $casts = [
        'from_currency' => 'string',
        'to_currency' => 'string',
        'margin' => 'double'
    ];

    public static array $rules = [
        'from_currency' => 'required',
        'to_currency' => 'required',
        'margin' => 'required',
        "rate"=>'required'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $nuage_env = env("NUAGE_ENV");

        if(strtoupper($nuage_env) === strtoupper("SANDBOX")) {
            $this->connection = env("AUTH_DB_CONNECTION", "mysql");
        }

    }


}
