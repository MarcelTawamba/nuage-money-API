<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\AuthCode as PassportAuthCode;

/**
 * App\Models\AuthCode
 *
 * @property string $id
 * @property int $user_id
 * @property string $client_id
 * @property string|null $scopes
 * @property bool $revoked
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property-read \App\Models\Client|null $client
 * @method static \Illuminate\Database\Eloquent\Builder|AuthCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AuthCode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AuthCode query()
 * @method static \Illuminate\Database\Eloquent\Builder|AuthCode whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthCode whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthCode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthCode whereRevoked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthCode whereScopes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthCode whereUserId($value)
 * @mixin \Eloquent
 */
class AuthCode extends PassportAuthCode
{
    //protected $connection = "";

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $nuage_env = env("NUAGE_ENV");

        if(strtoupper($nuage_env) === strtoupper("SANDBOX")) {
            $this->connection = env("AUTH_DB_CONNECTION", "mysql");
        }
    }
}
