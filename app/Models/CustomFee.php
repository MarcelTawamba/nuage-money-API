<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\AppFee
 *
 * @property int $id
 * @property int $company_id
 * @property int $method_id
 * @property string $fee_type
 * @property float $fee
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\Operator|null $method
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFee query()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFee whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFee whereFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFee whereFeeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFee whereMethodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFee whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CustomFee extends Model
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $nuage_env = env("NUAGE_ENV");

        if(strtoupper($nuage_env) === strtoupper("SANDBOX")) {
            $this->connection = env("AUTH_DB_CONNECTION", "mysql");
        }

    }

    public $table = 'app_fees';

    public $fillable = [
        'company_id',
        'method_id',
        'fee_type',
        'fee'
    ];

    protected $casts = [
        'company_id' => 'integer',
        'method_id' => 'integer',
        'fee_type' => 'string',
        'fee' => 'double'
    ];

    public static array $rules = [
        'company_id' => 'required',
        'method_id' => 'required',
        'fee_type' => 'required'
    ];


    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class,'company_id');
    }


    public function method(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Operator::class,'method_id');
    }

}
