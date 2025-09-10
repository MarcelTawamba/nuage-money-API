<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ToupesuAccessToken
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ToupesuAccessToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ToupesuAccessToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ToupesuAccessToken query()
 * @property int $id
 * @property string $token_string
 * @property int $expired_in
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ToupesuAccessToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ToupesuAccessToken whereExpiredIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ToupesuAccessToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ToupesuAccessToken whereTokenString($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ToupesuAccessToken whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ToupesuAccessToken extends Model
{
    use HasFactory;
}
