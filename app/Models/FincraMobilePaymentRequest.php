<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\FincraMobilePaymentRequest
 *
 * @property int $id
 * @property string $client_id
 * @property string $country_code
 * @property string $currency_code
 * @property float $amount
 * @property string $customer_name
 * @property string $customer_email
 * @property string $payment_method
 * @property string $msidn
 * @property string $ref_id
 * @property string $user_ref_id
 * @property string $pay_token
 * @property string $status
 * @property string $reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|FincraMobilePaymentRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FincraMobilePaymentRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FincraMobilePaymentRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|FincraMobilePaymentRequest whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FincraMobilePaymentRequest whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FincraMobilePaymentRequest whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FincraMobilePaymentRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FincraMobilePaymentRequest whereCurrencyCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FincraMobilePaymentRequest whereCustomerEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FincraMobilePaymentRequest whereCustomerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FincraMobilePaymentRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FincraMobilePaymentRequest whereMsidn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FincraMobilePaymentRequest wherePayToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FincraMobilePaymentRequest wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FincraMobilePaymentRequest whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FincraMobilePaymentRequest whereRefId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FincraMobilePaymentRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FincraMobilePaymentRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FincraMobilePaymentRequest whereUserRefId($value)
 * @mixin \Eloquent
 */
class FincraMobilePaymentRequest extends Model
{
    use HasFactory;
}
