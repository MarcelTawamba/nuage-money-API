<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * App\Models\Achat
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Achat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Achat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Achat query()
 * @property int $id
 * @property string $client_id
 * @property string $country
 * @property string $currency
 * @property double $amount
 * @property string $ref_id
 * @property string $user_ref_id
 * @property string $status
 * @property int $job_tries
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Achat whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Achat whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Achat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Achat whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Achat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Achat whereJobTries($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Achat whereRefId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Achat whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Achat whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Achat whereUserRefId($value)
 * @property string $requestable_type
 * @property int $requestable_id
 * @property-read Model|\Eloquent $requestable
 * @method static \Illuminate\Database\Eloquent\Builder|Achat whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Achat whereRequestableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Achat whereRequestableType($value)
 * @mixin \Eloquent
 */
class Achat extends Model
{
    public $table = 'achats';

    public $fillable = [
        'client_id',
        'country',
        'currency',
        "amount",
        'ref_id',
        'user_ref_id',
        'status',
        "job_tries",
        'requestable_type' => 'required',
        'requestable_id' => 'required',
    ];

    protected $casts = [
        'client_id' => 'string',
        'country' => 'string',
        'currency' => 'string',
        'ref_id' => 'string',
        'user_ref_id' => 'string',
        'status' => 'string'
    ];

    public static array $rules = [
        'client_id' => 'required',
        'country' => 'required',
        'currency' => 'required',
        'ref_id' => 'required',
        'user_ref_id' => 'required'
    ];

    public function requestable(): MorphTo
    {
        return $this->morphTo();
    }
    public function toArrayStat(): array
    {


        $attributes = [];

        // Add the HTTP method used to access the API endpoint
        $attributes['date'] = $this->date;
        $attributes['currency'] = strtoupper($this->currency);
        $attributes['amount'] = abs($this->amount) ;
        return $attributes;

    }

}
