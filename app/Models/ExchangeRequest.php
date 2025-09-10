<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ExchangeRequest
 *
 * @OA\Schema (
 *      schema="ExchangeRequest",
 *      required={"from_currency","to_currency","amount","market_rate","status"},
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
 *          property="amount",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="number",
 *          format="number"
 *      ),
 *      @OA\Property(
 *          property="market_rate",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="number",
 *          format="number"
 *      ),
 *      @OA\Property(
 *          property="rate",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
 *          type="number",
 *          format="number"
 *      ),
 *      @OA\Property(
 *          property="status",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
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
 * * @property string $client_id
 * @property float $amount
 * @property float $market_rate
 * @property float $rate
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeRequest whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeRequest whereFromCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeRequest whereMarketRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeRequest whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeRequest whereToCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeRequest whereUpdatedAt($value)
 * @mixin \Eloquent
 */class ExchangeRequest extends Model
{
    public $table = 'exchange_requests';

    public $fillable = [
        'from_currency',
        'to_currency',
        'amount',
        "client_id",
        'market_rate',
        'rate',
        'status'
    ];

    protected $casts = [
        'from_currency' => 'string',
        'to_currency' => 'string',
        'amount' => 'double',
        'market_rate' => 'double',
        'rate' => 'double',
        'status' => 'string'
    ];

    public static array $rules = [
        'from_currency' => 'required',
        'to_currency' => 'required',
        'amount' => 'required',
        "service"=>"required"
    ];


}
