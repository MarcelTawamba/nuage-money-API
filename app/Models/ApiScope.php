<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ApiScope extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'resource',
        'action',
    ];

    public function apiKeys(): BelongsToMany
    {
        return $this->belongsToMany(ApiKey::class, 'api_key_scopes', 'scope_id', 'api_key_id')
            ->withTimestamps();
    }
}
