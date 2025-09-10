<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\FincraBankAccount
 *
 * @OA\Schema (
 *      schema="FincraBankAccount",
 *      required={"account_number","account_name","bank_code"},
 *      @OA\Property(
 *          property="account_number",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="account_name",
 *          description="",
 *          readOnly=false,
 *          nullable=false,
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="bank_code",
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
 * @property string $account_number
 * @property string $account_name
 * @property string $bank_code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|FincraBankAccount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FincraBankAccount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FincraBankAccount query()
 * @method static \Illuminate\Database\Eloquent\Builder|FincraBankAccount whereAccountName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FincraBankAccount whereAccountNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FincraBankAccount whereBankCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FincraBankAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FincraBankAccount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FincraBankAccount whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FincraBankAccount extends Model
{
    public $table = 'fincra_bank_accounts';

    public $fillable = [
        'account_number',
        'account_name',
        'bank_code'
    ];

    protected $casts = [
        'account_number' => 'string',
        'account_name' => 'string',
        'bank_code' => 'string'
    ];

    public static array $rules = [
        'account_number' => 'required|string|max:255',
        'account_name' => 'required|string|max:255',
        'bank_code' => 'required|string|max:255',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];


}
