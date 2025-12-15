<?php

namespace Tests\Unit;

use App\Models\ApiKey;
use App\Models\User;
use App\Services\ApiKeyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiKeyServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ApiKeyService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ApiKeyService();
    }

    public function test_generate_key_creates_valid_api_key()
    {
        $user = User::factory()->create();

        $result = $this->service->generateKey(
            $user->id,
            null,
            'Test Key',
            'test',
            []
        );

        $this->assertArrayHasKey('api_key', $result);
        $this->assertArrayHasKey('plain_key', $result);
        $this->assertInstanceOf(ApiKey::class, $result['api_key']);
        $this->assertStringStartsWith('nuage_test_', $result['plain_key']);
        $this->assertDatabaseHas('api_keys', [
            'user_id' => $user->id,
            'name' => 'Test Key',
            'environment' => 'test',
        ]);
    }

    public function test_validate_key_returns_api_key_for_valid_key()
    {
        $user = User::factory()->create();
        $result = $this->service->generateKey($user->id, null, 'Test Key', 'test', []);

        $validatedKey = $this->service->validateKey($result['plain_key']);

        $this->assertNotNull($validatedKey);
        $this->assertEquals($result['api_key']->id, $validatedKey->id);
    }

    public function test_validate_key_returns_null_for_invalid_format()
    {
        $validatedKey = $this->service->validateKey('invalid_key_format');

        $this->assertNull($validatedKey);
    }

    public function test_validate_key_returns_null_for_invalid_secret()
    {
        $user = User::factory()->create();
        $result = $this->service->generateKey($user->id, null, 'Test Key', 'test', []);
        
        $parts = explode('_', $result['plain_key']);
        $parts[3] = 'wrongsecret' . str_repeat('x', 20);
        $invalidKey = implode('_', $parts);

        $validatedKey = $this->service->validateKey($invalidKey);

        $this->assertNull($validatedKey);
    }

    public function test_validate_key_returns_null_for_inactive_key()
    {
        $user = User::factory()->create();
        $result = $this->service->generateKey($user->id, null, 'Test Key', 'test', []);
        
        $result['api_key']->update(['is_active' => false]);

        $validatedKey = $this->service->validateKey($result['plain_key']);

        $this->assertNull($validatedKey);
    }

    public function test_revoke_key_deactivates_api_key()
    {
        $user = User::factory()->create();
        $result = $this->service->generateKey($user->id, null, 'Test Key', 'test', []);

        $revoked = $this->service->revokeKey($result['api_key']->id);

        $this->assertTrue($revoked);
        $this->assertDatabaseHas('api_keys', [
            'id' => $result['api_key']->id,
            'is_active' => false,
        ]);
    }

    public function test_rotate_key_generates_new_credentials()
    {
        $user = User::factory()->create();
        $result = $this->service->generateKey($user->id, null, 'Test Key', 'test', []);
        $oldPrefix = $result['api_key']->key_prefix;

        $rotated = $this->service->rotateKey($result['api_key']->id);

        $this->assertNotNull($rotated);
        $this->assertArrayHasKey('api_key', $rotated);
        $this->assertArrayHasKey('plain_key', $rotated);
        $this->assertNotEquals($oldPrefix, $rotated['api_key']->key_prefix);
    }
}
