<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\StartButtonPayInRequest
 *
 * @property int $id
 * @property string $email
 * @property string $payment_link
 * @property string $status
 * @property string $reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|StartButtonPayInRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StartButtonPayInRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StartButtonPayInRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|StartButtonPayInRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StartButtonPayInRequest whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StartButtonPayInRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StartButtonPayInRequest wherePaymentLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StartButtonPayInRequest whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StartButtonPayInRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StartButtonPayInRequest whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class StartButtonPayInRequest extends Model
{
    use HasFactory;

    function  toArray()
    {
        return [
            "email"=>$this->email,
        ];
    }
}
