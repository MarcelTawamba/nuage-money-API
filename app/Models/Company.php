<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Company
 *
 * @property int $id
 * @property string $name
 * @property int $user_id
 * @property string $company_type
 * @property string $address
 * @property string $phone_number
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Client> $clients
 * @property-read int|null $clients_count
 * @method static \Illuminate\Database\Eloquent\Builder|Company newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Company newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Company query()
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereCompanyType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Company whereUserId($value)
 * @mixin \Eloquent
 */
class Company extends Model
{

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $nuage_env = env("NUAGE_ENV");

        if(strtoupper($nuage_env) === strtoupper("SANDBOX")) {
            $this->connection = env("AUTH_DB_CONNECTION", "mysql");
        }

    }
    public $table = 'companies';

    public $fillable = [
        'name',
        "user_id",
        'company_type',
        'address',
        'phone_number',
        "web_site"
    ];

    protected $casts = [
        'name' => 'string',
        'company_type' => 'string',
        'address' => 'string',
        'phone_number' => 'string',
        "web_site"=>"string"

    ];

    public static array $rules = [
        'name' => 'required|min:4',
        'company_type' => 'required',
        'address' => 'required'
    ];


    public function clients(): HasMany
    {
        return $this->hasMany(Client::class,'company_id');

    }

    public function wallets_nuage(): array
    {
        $wallets= [];

        foreach ($this->clients as $client){

            $wa = $client->wallet;

            if ($wa != null){
                foreach($wa->wallets as $wallet){
                    $wallets[]=$wallet;
                }
            }

        }


        return $wallets;

    }


}
