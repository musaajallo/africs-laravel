<?php

namespace Tests\Feature\Console;

use App\Models\User;
use App\Support\Rbac;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    protected function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Rbac::ROLE_SUPER_ADMIN);

        return $user;
    }

    public function test_console_manager_without_users_permission_cannot_see_user_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Rbac::ROLE_CONSOLE_MANAGER);

        $this->actingAs($user)->get('/console/users')->assertForbidden();
    }

    public function test_super_admin_can_list_users(): void
    {
        User::factory()->count(3)->create();

        $this->actingAs($this->superAdmin())
            ->get('/console/users')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Console/Users/Index')
                ->has('users.data', 4));
    }

    public function test_search_filters_the_user_list(): void
    {
        User::factory()->create(['name' => 'Aminata Ceesay']);
        User::factory()->create(['name' => 'Buba Sanneh']);

        $this->actingAs($this->superAdmin())
            ->get('/console/users?search=Aminata')
            ->assertInertia(fn ($page) => $page->has('users.data', 1));
    }

    public function test_super_admin_can_create_a_user_with_roles(): void
    {
        $this->actingAs($this->superAdmin())
            ->post('/console/users', [
                'name' => 'New Person',
                'username' => 'new_person',
                'email' => 'new@africs.test',
                'password' => 'password1234',
                'password_confirmation' => 'password1234',
                'roles' => [Rbac::ROLE_CONSOLE_MANAGER],
            ])
            ->assertRedirect('/console/users');

        $user = User::where('email', 'new@africs.test')->firstOrFail();
        $this->assertTrue($user->hasRole(Rbac::ROLE_CONSOLE_MANAGER));
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_update_can_change_roles_and_optionally_the_password(): void
    {
        $target = User::factory()->create();
        $target->assignRole(Rbac::ROLE_CONSOLE_MANAGER);
        $originalHash = $target->password;

        $this->actingAs($this->superAdmin())
            ->put("/console/users/{$target->id}", [
                'name' => $target->name,
                'username' => $target->username,
                'email' => $target->email,
                'password' => '',
                'password_confirmation' => '',
                'roles' => [Rbac::ROLE_CMS_MANAGER],
            ])
            ->assertRedirect('/console/users');

        $target->refresh();
        $this->assertTrue($target->hasRole(Rbac::ROLE_CMS_MANAGER));
        $this->assertFalse($target->hasRole(Rbac::ROLE_CONSOLE_MANAGER));
        $this->assertSame($originalHash, $target->password);
    }

    public function test_admin_can_deactivate_and_reactivate_a_user(): void
    {
        $admin = $this->superAdmin();
        $target = User::factory()->create();

        $this->actingAs($admin)->delete("/console/users/{$target->id}");
        $this->assertNotNull($target->fresh()->deactivated_at);

        $this->actingAs($admin)->delete("/console/users/{$target->id}");
        $this->assertNull($target->fresh()->deactivated_at);
    }

    public function test_a_deactivated_user_cannot_log_in(): void
    {
        $target = User::factory()->deactivated()->create();

        $this->post('/login', ['email' => $target->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_a_user_cannot_deactivate_their_own_account(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->delete("/console/users/{$admin->id}")->assertForbidden();
    }

    public function test_non_super_admin_cannot_grant_super_admin_role(): void
    {
        $manager = User::factory()->create();
        $manager->givePermissionTo(Rbac::PERM_USERS_MANAGE, Rbac::PERM_CONSOLE_ACCESS);

        $this->actingAs($manager)
            ->post('/console/users', [
                'name' => 'Escalate',
                'username' => 'escalate',
                'email' => 'escalate@africs.test',
                'password' => 'password1234',
                'password_confirmation' => 'password1234',
                'roles' => [Rbac::ROLE_SUPER_ADMIN],
            ])
            ->assertSessionHasErrors('roles');

        $this->assertDatabaseMissing('users', ['email' => 'escalate@africs.test']);
    }

    public function test_non_super_admin_cannot_edit_a_super_admin(): void
    {
        $manager = User::factory()->create();
        $manager->givePermissionTo(Rbac::PERM_USERS_MANAGE, Rbac::PERM_USERS_VIEW, Rbac::PERM_CONSOLE_ACCESS);
        $target = $this->superAdmin();

        $this->actingAs($manager)->get("/console/users/{$target->id}/edit")->assertForbidden();
    }
}
