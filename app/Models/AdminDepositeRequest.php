<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AdminDepositeRequest
 *
 * @property int $id
 * @property string $description
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|AdminDepositeRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdminDepositeRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdminDepositeRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|AdminDepositeRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminDepositeRequest whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminDepositeRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminDepositeRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdminDepositeRequest whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AdminDepositeRequest extends Model
{
    use HasFactory;
}
