<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class RateLimitTier extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'requests_per_minute',
        'requests_per_day',
        'requests_per_month',
        'monthly_price',
        'currency',
        'features',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'monthly_price' => 'decimal:2',
        'requests_per_minute' => 'integer',
        'requests_per_day' => 'integer',
        'requests_per_month' => 'integer',
    ];

    /**
     * Get all API keys using this tier
     */
    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class, 'rate_limit_tier', 'name');
    }

    /**
     * Check if this tier has unlimited access
     */
    public function isUnlimited(): bool
    {
        return $this->requests_per_minute === null 
            && $this->requests_per_day === null 
            && $this->requests_per_month === null;
    }

    /**
     * Get all active tiers (cached for 1 hour)
     */
    public static function getAllTiers(): array
    {
        return Cache::remember('rate_limit_tiers', 3600, function () {
            return self::where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->keyBy('name')
                ->toArray();
        });
    }

    /**
     * Get a specific tier by name (cached)
     */
    public static function getTierByName(string $name): ?array
    {
        $tiers = self::getAllTiers();
        return $tiers[$name] ?? null;
    }

    /**
     * Get tier limits in the format expected by middleware
     */
    public function getLimits(): array
    {
        return [
            'per_minute' => $this->requests_per_minute,
            'per_day' => $this->requests_per_day,
            'per_month' => $this->requests_per_month,
        ];
    }

    /**
     * Clear the cache when tiers are updated
     */
    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('rate_limit_tiers');
        });

        static::deleted(function () {
            Cache::forget('rate_limit_tiers');
        });
    }
}
