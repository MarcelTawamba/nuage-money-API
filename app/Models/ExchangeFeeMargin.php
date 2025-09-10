<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ExchangeFeeMargin
 *
 * @OA\Schema (
 *      schema="ExchangeFeeMargin",
 *      required={"currency","amount","exchange_request"},
 *      @OA\Property(
 *          property="currency",
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
 *          property="exchange_request",
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
 * @property string $currency
 * @property float $amount
 * @property string $exchange_request
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeFeeMargin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeFeeMargin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeFeeMargin query()
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeFeeMargin whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeFeeMargin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeFeeMargin whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeFeeMargin whereExchangeRequest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeFeeMargin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExchangeFeeMargin whereUpdatedAt($value)
 * @mixin \Eloquent
 */class ExchangeFeeMargin extends Model
{
    public $table = 'exchange_fee_margins';

    public $fillable = [
        'currency',
        'amount',
        'exchange_request'
    ];

    protected $casts = [
        'currency' => 'string',
        'amount' => 'double',
        'exchange_request' => 'string'
    ];

    public static array $rules = [
        'currency' => 'required',
        'amount' => 'required',
        'exchange_request' => 'required'
    ];




}
