<?php

namespace Tests\Feature\Api;

use App\Models\Client;
use App\Models\User;
use App\Support\Rbac;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ClientApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    protected function user(array $permissions = []): User
    {
        $user = User::factory()->create();
        $user->givePermissionTo($permissions);

        return $user;
    }

    public function test_the_api_rejects_unauthenticated_requests_with_json(): void
    {
        $this->getJson('/api/v1/clients')
            ->assertUnauthorized()
            ->assertJsonStructure(['message']);
    }

    public function test_a_token_needs_the_view_ability_to_read_clients(): void
    {
        Client::factory()->count(2)->create();
        $user = $this->user([Rbac::PERM_CLIENTS_VIEW, Rbac::PERM_CLIENTS_MANAGE]);

        Sanctum::actingAs($user, ['clients.manage']); // manage but not view
        $this->getJson('/api/v1/clients')->assertForbidden();

        Sanctum::actingAs($user, ['clients.view']);
        $this->getJson('/api/v1/clients')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'name', 'type', 'status']],
                'meta' => ['total', 'per_page'],
            ])
            ->assertJsonCount(2, 'data');
    }

    public function test_reading_requires_only_view_and_writing_requires_manage(): void
    {
        $user = $this->user([Rbac::PERM_CLIENTS_VIEW]);
        Sanctum::actingAs($user, ['clients.view']);

        $this->postJson('/api/v1/clients', ['name' => 'X', 'type' => 'organisation', 'status' => 'active'])
            ->assertForbidden();
    }

    public function test_a_manage_token_can_create_update_and_archive_a_client(): void
    {
        $user = $this->user([Rbac::PERM_CLIENTS_VIEW, Rbac::PERM_CLIENTS_MANAGE]);
        Sanctum::actingAs($user, ['clients.view', 'clients.manage']);

        $create = $this->postJson('/api/v1/clients', [
            'name' => 'API Client',
            'type' => 'government',
            'category' => 'Local council',
            'status' => 'active',
            'tags' => ['Imported'],
        ])->assertCreated()->json('data');

        $this->assertSame('API Client', $create['name']);
        $this->assertSame(['Imported'], $create['tags']);
        $this->assertSame($user->id, Client::find($create['id'])->created_by);

        $this->putJson("/api/v1/clients/{$create['id']}", [
            'name' => 'API Client v2',
            'type' => 'government',
            'category' => 'Ministry',
            'status' => 'inactive',
        ])->assertOk()->assertJsonPath('data.name', 'API Client v2');

        $this->deleteJson("/api/v1/clients/{$create['id']}")->assertNoContent();
        $this->assertSoftDeleted('clients', ['id' => $create['id']]);
    }

    public function test_validation_errors_are_returned_as_json(): void
    {
        $user = $this->user([Rbac::PERM_CLIENTS_MANAGE]);
        Sanctum::actingAs($user, ['clients.manage']);

        $this->postJson('/api/v1/clients', ['type' => 'organisation'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'status']);
    }

    public function test_a_real_bearer_token_authenticates_and_reports_its_abilities(): void
    {
        $user = $this->user([Rbac::PERM_CLIENTS_VIEW]);
        $plain = $user->createToken('sync', ['clients.view'])->plainTextToken;

        $this->withToken($plain)
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.abilities', ['clients.view'])
            ->assertJsonPath('data.id', $user->id);

        $this->withToken($plain)->getJson('/api/v1/clients')->assertOk();
    }
}
