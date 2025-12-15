<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\ApiScope;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApiKeyRateLimitTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Company $company;
    private string $rawKey;
    private ApiKey $apiKey;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->company = Company::factory()->create(['user_id' => $this->user->id]);

        $this->rawKey = 'nuage_test_' . Str::random(32);
        $this->apiKey = ApiKey::create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'key_hash' => hash('sha256', $this->rawKey),
            'key_prefix' => 'nuage_test',
            'name' => 'Test API Key',
            'environment' => 'test',
            'rate_limit_tier' => 'free',
            'is_active' => true,
        ]);

        $scope = ApiScope::firstOrCreate([
            'name' => 'currencies:read',
            'description' => 'Read currencies',
            'resource' => 'currencies',
            'action' => 'read',
        ]);

        $this->apiKey->scopes()->attach($scope->id);

        Cache::flush();
    }

    public function test_free_tier_rate_limit_allows_requests_within_limit()
    {
        for ($i = 0; $i < 5; $i++) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->rawKey,
            ])->getJson('/api/currencies');

            $response->assertStatus(200);
            $response->assertHeader('X-RateLimit-Limit', '10');
            $response->assertHeader('X-RateLimit-Remaining');
            $response->assertHeader('X-RateLimit-Reset');
        }
    }

    public function test_free_tier_rate_limit_blocks_requests_exceeding_limit()
    {
        for ($i = 0; $i < 10; $i++) {
            $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->rawKey,
            ])->getJson('/api/currencies');
        }

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->rawKey,
        ])->getJson('/api/currencies');

        $response->assertStatus(429);
        $response->assertJson([
            'error' => 'Too Many Requests',
        ]);
        $response->assertHeader('X-RateLimit-Limit', '10');
        $response->assertHeader('X-RateLimit-Remaining', '0');
        $response->assertHeader('Retry-After');
    }

    public function test_rate_limit_headers_are_present()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->rawKey,
        ])->getJson('/api/currencies');

        $response->assertStatus(200);
        $this->assertTrue($response->headers->has('X-RateLimit-Limit'));
        $this->assertTrue($response->headers->has('X-RateLimit-Remaining'));
        $this->assertTrue($response->headers->has('X-RateLimit-Reset'));
    }

    public function test_rate_limit_remaining_decreases_with_each_request()
    {
        $response1 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->rawKey,
        ])->getJson('/api/currencies');

        $remaining1 = (int) $response1->headers->get('X-RateLimit-Remaining');

        $response2 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->rawKey,
        ])->getJson('/api/currencies');

        $remaining2 = (int) $response2->headers->get('X-RateLimit-Remaining');

        $this->assertLessThan($remaining1, $remaining2);
    }

    public function test_basic_tier_has_higher_rate_limit()
    {
        $this->apiKey->update(['rate_limit_tier' => 'basic']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->rawKey,
        ])->getJson('/api/currencies');

        $response->assertStatus(200);
        $response->assertHeader('X-RateLimit-Limit', '60');
    }

    public function test_premium_tier_has_highest_rate_limit()
    {
        $this->apiKey->update(['rate_limit_tier' => 'premium']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->rawKey,
        ])->getJson('/api/currencies');

        $response->assertStatus(200);
        $response->assertHeader('X-RateLimit-Limit', '300');
    }

    public function test_enterprise_tier_has_unlimited_rate_limit()
    {
        $this->apiKey->update(['rate_limit_tier' => 'enterprise']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->rawKey,
        ])->getJson('/api/currencies');

        $response->assertStatus(200);
        $response->assertHeader('X-RateLimit-Limit', '999999');
    }
}
