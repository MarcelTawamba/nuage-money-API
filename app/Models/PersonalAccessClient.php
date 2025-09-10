<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\PersonalAccessClient as PassportPersonalAccessClient;

/**
 * App\Models\PersonalAccessClient
 *
 * @property int $id
 * @property string $client_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Client|null $client
 * @method static \Illuminate\Database\Eloquent\Builder|PersonalAccessClient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PersonalAccessClient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PersonalAccessClient query()
 * @method static \Illuminate\Database\Eloquent\Builder|PersonalAccessClient whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PersonalAccessClient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PersonalAccessClient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PersonalAccessClient whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PersonalAccessClient extends PassportPersonalAccessClient
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->connection = env("AUTH_DB_CONNECTION", "mysql");
    }
}
