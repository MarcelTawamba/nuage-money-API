<?php

namespace App\Services;

use App\Models\ApiKey;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApiKeyService
{
    public function generateKey(int $userId, ?int $companyId, string $name, string $environment = 'test', array $scopeIds = [], string $rateLimitTier = 'free'): array
    {
        $prefix = Str::random(8);
        $secret = Str::random(32);
        $fullKey = "nuage_{$environment}_{$prefix}_{$secret}";

        $apiKey = ApiKey::create([
            'user_id' => $userId,
            'company_id' => $companyId,
            'key_hash' => Hash::make($secret),
            'key_prefix' => $prefix,
            'name' => $name,
            'environment' => $environment,
            'rate_limit_tier' => $rateLimitTier,
            'is_active' => true,
        ]);

        if (!empty($scopeIds)) {
            $apiKey->scopes()->attach($scopeIds);
        }

        return [
            'api_key' => $apiKey,
            'plain_key' => $fullKey,
        ];
    }

    public function validateKey(string $key): ?ApiKey
    {
        if (!$this->isValidFormat($key)) {
            return null;
        }

        $parts = explode('_', $key);
        if (count($parts) !== 4) {
            return null;
        }

        [, $environment, $prefix, $secret] = $parts;

        $apiKey = ApiKey::where('key_prefix', $prefix)
            ->where('environment', $environment)
            ->where('is_active', true)
            ->first();

        if (!$apiKey) {
            return null;
        }

        if ($apiKey->isExpired()) {
            return null;
        }

        if (!Hash::check($secret, $apiKey->key_hash)) {
            return null;
        }

        return $apiKey;
    }

    public function revokeKey(string $keyId): bool
    {
        $apiKey = ApiKey::find($keyId);
        
        if (!$apiKey) {
            return false;
        }

        return $apiKey->update(['is_active' => false]);
    }

    public function rotateKey(string $keyId): ?array
    {
        $apiKey = ApiKey::find($keyId);
        
        if (!$apiKey) {
            return null;
        }

        $prefix = Str::random(8);
        $secret = Str::random(32);
        $fullKey = "nuage_{$apiKey->environment}_{$prefix}_{$secret}";

        $apiKey->update([
            'key_hash' => Hash::make($secret),
            'key_prefix' => $prefix,
        ]);

        return [
            'api_key' => $apiKey->fresh(),
            'plain_key' => $fullKey,
        ];
    }

    private function isValidFormat(string $key): bool
    {
        return (bool) preg_match('/^nuage_(test|live)_[a-zA-Z0-9]{8}_[a-zA-Z0-9]{32}$/', $key);
    }
}
