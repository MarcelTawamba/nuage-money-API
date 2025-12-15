<?php

namespace Database\Factories;

use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApiKeyFactory extends Factory
{
    protected $model = ApiKey::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'company_id' => null,
            'key_hash' => Hash::make(Str::random(32)),
            'key_prefix' => Str::random(8),
            'name' => $this->faker->words(3, true),
            'environment' => $this->faker->randomElement(['test', 'live']),
            'rate_limit_tier' => $this->faker->randomElement(['free', 'basic', 'premium', 'enterprise']),
            'is_active' => true,
            'last_used_at' => null,
            'expires_at' => null,
        ];
    }

    public function inactive(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }

    public function expired(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'expires_at' => now()->subDay(),
            ];
        });
    }

    public function live(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'environment' => 'live',
            ];
        });
    }

    public function test(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'environment' => 'test',
            ];
        });
    }
}
