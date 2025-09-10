<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\StartButtonPayOutRequest
 *
 * @property int $id
 * @property string $bank_code
 * @property string $account_number
 * @property string $account_name
 * @property string $status
 * @property string $reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|StartButtonPayOutRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StartButtonPayOutRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StartButtonPayOutRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|StartButtonPayOutRequest whereAccountName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StartButtonPayOutRequest whereAccountNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StartButtonPayOutRequest whereBankCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StartButtonPayOutRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StartButtonPayOutRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StartButtonPayOutRequest whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StartButtonPayOutRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StartButtonPayOutRequest whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class StartButtonPayOutRequest extends Model
{
    use HasFactory;

    function  toArray()
    {
        return [
            "bank_code"=>$this->bank_code,
            "account_number"=>$this->account_number,
            "account_name"=>$this->account_name
        ];
    }
}
