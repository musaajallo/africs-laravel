<?php

namespace Tests\Feature\Console;

use App\Models\User;
use App\Support\Rbac;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTokenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_a_console_user_without_the_permission_cannot_manage_tokens(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Rbac::PERM_CONSOLE_ACCESS);

        $this->actingAs($user)->get('/console/api-tokens')->assertForbidden();
    }

    public function test_a_permitted_user_can_create_and_revoke_a_token(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo([Rbac::PERM_CONSOLE_ACCESS, Rbac::PERM_API_TOKENS_MANAGE, Rbac::PERM_CLIENTS_VIEW]);

        $response = $this->actingAs($user)->post('/console/api-tokens', [
            'name' => 'Website sync',
            'abilities' => ['clients.view'],
        ]);

        $response->assertRedirect('/console/api-tokens')->assertSessionHas('plainTextToken');

        $token = $user->tokens()->firstOrFail();
        $this->assertSame('Website sync', $token->name);
        $this->assertSame(['clients.view'], $token->abilities);

        $this->actingAs($user)->delete("/console/api-tokens/{$token->id}")->assertRedirect();
        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_a_user_cannot_grant_abilities_they_do_not_hold(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo([Rbac::PERM_CONSOLE_ACCESS, Rbac::PERM_API_TOKENS_MANAGE, Rbac::PERM_CLIENTS_VIEW]);

        $this->actingAs($user)->post('/console/api-tokens', [
            'name' => 'Too much',
            'abilities' => ['clients.view', 'clients.manage'], // manage not held
        ])->assertSessionHasErrors('abilities');

        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_a_super_admin_can_grant_any_ability(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Rbac::ROLE_SUPER_ADMIN);

        $this->actingAs($admin)->post('/console/api-tokens', [
            'name' => 'Full access',
            'abilities' => ['clients.manage', 'users.manage', 'settings.manage'],
        ])->assertRedirect();

        $this->assertSame(
            ['clients.manage', 'users.manage', 'settings.manage'],
            $admin->tokens()->firstOrFail()->abilities,
        );
    }
}
