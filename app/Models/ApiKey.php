<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApiKey extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'key_hash',
        'key_prefix',
        'name',
        'environment',
        'rate_limit_tier',
        'is_active',
        'last_used_at',
        'expires_at',
    ];

    protected $hidden = [
        'key_hash',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function scopes(): BelongsToMany
    {
        return $this->belongsToMany(ApiScope::class, 'api_key_scopes', 'api_key_id', 'scope_id')
            ->withTimestamps();
    }

    public function usageLogs(): HasMany
    {
        return $this->hasMany(ApiUsageLog::class);
    }

    public function hasScope(string $scope): bool
    {
        if ($this->scopes()->where('name', '*')->exists()) {
            return true;
        }

        return $this->scopes()->where('name', $scope)->exists();
    }

    public function hasResource(string $resource, string $action = 'read'): bool
    {
        if ($this->scopes()->where('name', '*')->exists()) {
            return true;
        }

        return $this->scopes()
            ->where(function ($query) use ($resource, $action) {
                $query->where('resource', $resource)
                    ->where(function ($q) use ($action) {
                        $q->where('action', $action)
                          ->orWhere('action', '*');
                    });
            })
            ->exists();
    }

    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return $this->is_active && !$this->isExpired();
    }

    public function updateLastUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }
}
