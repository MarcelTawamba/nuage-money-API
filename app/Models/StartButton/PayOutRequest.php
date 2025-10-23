<?php

namespace App\Models\StartButton;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\StartButton\PayOutRequest
 *
 * @property int $id
 * @property string $bank_code
 * @property string $account_number
 * @property string $account_name
 * @property string $status
 * @property string $reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PayOutRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PayOutRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PayOutRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|PayOutRequest whereAccountName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayOutRequest whereAccountNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayOutRequest whereBankCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayOutRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayOutRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayOutRequest whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayOutRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayOutRequest whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PayOutRequest extends Model
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
