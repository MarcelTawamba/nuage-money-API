<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\ApiScope;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiKeyManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\ApiScopesSeeder']);
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_user_can_list_their_api_keys()
    {
        ApiKey::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/api-keys');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'name', 'environment', 'prefix', 'rate_limit_tier', 'is_active']
                ]
            ]);
    }

    public function test_user_can_create_api_key()
    {
        $response = $this->postJson('/api/api-keys', [
            'name' => 'My Test Key',
            'environment' => 'test',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'name', 'key', 'environment']
            ]);

        $this->assertDatabaseHas('api_keys', [
            'user_id' => $this->user->id,
            'name' => 'My Test Key',
            'environment' => 'test',
        ]);
    }

    public function test_user_can_view_specific_api_key()
    {
        $apiKey = ApiKey::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/api/api-keys/{$apiKey->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $apiKey->id,
                    'name' => $apiKey->name,
                ]
            ]);
    }

    public function test_user_can_update_api_key()
    {
        $apiKey = ApiKey::factory()->create(['user_id' => $this->user->id]);

        $response = $this->putJson("/api/api-keys/{$apiKey->id}", [
            'name' => 'Updated Key Name',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('api_keys', [
            'id' => $apiKey->id,
            'name' => 'Updated Key Name',
        ]);
    }

    public function test_user_can_revoke_api_key()
    {
        $apiKey = ApiKey::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/api-keys/{$apiKey->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('api_keys', [
            'id' => $apiKey->id,
        ]);
    }

    public function test_user_can_regenerate_api_key()
    {
        $apiKey = ApiKey::factory()->create(['user_id' => $this->user->id]);
        $oldPrefix = $apiKey->key_prefix;

        $response = $this->postJson("/api/api-keys/{$apiKey->id}/regenerate");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['id', 'name', 'key', 'environment']
            ]);

        $apiKey->refresh();
        $this->assertNotEquals($oldPrefix, $apiKey->key_prefix);
    }

    public function test_user_cannot_view_other_users_api_keys()
    {
        $otherUser = User::factory()->create();
        $apiKey = ApiKey::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->getJson("/api/api-keys/{$apiKey->id}");

        $response->assertStatus(404);
    }

    public function test_user_can_list_available_scopes()
    {
        $response = $this->getJson('/api/api-scopes');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'name', 'description', 'resource', 'action']
                ]
            ]);
    }
}
