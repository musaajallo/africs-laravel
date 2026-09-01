<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Rbac;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanelAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_guests_are_redirected_to_login_from_panels(): void
    {
        $this->get('/console')->assertRedirect('/login');
        $this->get('/cms')->assertRedirect('/login');
    }

    public function test_users_without_the_permission_are_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/console')->assertForbidden();
        $this->actingAs($user)->get('/cms')->assertForbidden();
    }

    public function test_console_manager_can_reach_the_console_but_not_the_cms(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Rbac::ROLE_CONSOLE_MANAGER);

        $this->actingAs($user)->get('/console')->assertOk();
        $this->actingAs($user)->get('/cms')->assertForbidden();
    }

    public function test_cms_manager_can_reach_the_cms_but_not_the_console(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Rbac::ROLE_CMS_MANAGER);

        $this->actingAs($user)->get('/cms')->assertOk();
        $this->actingAs($user)->get('/console')->assertForbidden();
    }

    public function test_super_admin_can_reach_both_panels(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Rbac::ROLE_SUPER_ADMIN);

        $this->actingAs($user)->get('/console')->assertOk();
        $this->actingAs($user)->get('/cms')->assertOk();
    }

    public function test_login_redirects_to_the_users_panel(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Rbac::ROLE_CONSOLE_MANAGER);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('console.dashboard'));
    }
}
