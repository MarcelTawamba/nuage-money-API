<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\FincraBank
 *
 * @OA\Schema (
 *      schema="FincraBank",
 *      required={"name","code"},
 *      @OA\Property(
 *          property="name",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="code",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="currency",
 *          description="",
 *          readOnly=false,
 *          nullable=true,
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
 * @property string $name
 * @property string $code
 * @property string|null $currency
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|FincraBank newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FincraBank newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FincraBank query()
 * @method static \Illuminate\Database\Eloquent\Builder|FincraBank whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FincraBank whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FincraBank whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FincraBank whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FincraBank whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FincraBank whereUpdatedAt($value)
 * @mixin \Eloquent
 */class FincraBank extends Model
{
    public $table = 'fincra_banks';

    public $fillable = [
        'name',
        'code',
        'currency'
    ];

    protected $casts = [
        'name' => 'string',
        'code' => 'string',
        'currency' => 'string'
    ];

    public static array $rules = [
        'name' => 'required|string|max:255',
        'code' => 'required|string|max:255',
        'currency' => 'nullable|string|max:255',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    
}
