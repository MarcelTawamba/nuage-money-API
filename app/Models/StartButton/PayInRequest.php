<?php

namespace App\Models\StartButton;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\StartButton\PayInRequest
 *
 * @property int $id
 * @property string $email
 * @property string $payment_link
 * @property string $status
 * @property string $reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PayInRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PayInRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PayInRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|PayInRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayInRequest whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayInRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayInRequest wherePaymentLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayInRequest whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayInRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PayInRequest whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PayInRequest extends Model
{
    use HasFactory;

    function  toArray()
    {
        return [
            "email"=>$this->email,
        ];
    }
}
