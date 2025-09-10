<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CountryAvaillable
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CountryAvaillable newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CountryAvaillable newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CountryAvaillable query()
 * @method static \Illuminate\Database\Eloquent\Builder|CountryAvaillable whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CountryAvaillable whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CountryAvaillable whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CountryAvaillable whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CountryAvaillable whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CountryAvaillable extends Model
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $nuage_env = env("NUAGE_ENV");

        if(strtoupper($nuage_env) === strtoupper("SANDBOX")) {
            $this->connection = env("AUTH_DB_CONNECTION", "mysql");
        }

    }

    public $table = 'country_availlables';

    public $fillable = [
        'name',
        'code'
    ];

    protected $casts = [
        'name' => 'string',
        'code' => 'string'
    ];

    public static array $rules = [
        'name' => 'required|unique:country_availlables',

    ];

    public  function  toArray(): array
    {
        $new_array = [];

        $new_array["name"] = $this->name;
        $new_array["code"] = $this->code;
        return $new_array;
    }


}
