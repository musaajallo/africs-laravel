<?php

namespace Tests\Feature\Console;

use App\Models\Asset;
use App\Models\User;
use App\Support\Rbac;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    protected function manager(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Rbac::ROLE_CONSOLE_MANAGER);

        return $user;
    }

    protected function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'MacBook Pro 14',
            'category' => 'laptop',
            'status' => 'spare',
        ], $overrides);
    }

    public function test_a_console_user_without_permission_cannot_see_assets(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Rbac::PERM_CONSOLE_ACCESS);

        $this->actingAs($user)->get('/console/assets')->assertForbidden();
    }

    public function test_the_list_shows_active_assets_by_default(): void
    {
        Asset::factory()->status('in_use')->create();
        Asset::factory()->status('spare')->create();
        Asset::factory()->status('retired')->create();

        $this->actingAs($this->manager())
            ->get('/console/assets')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Console/Assets/Index')
                ->has('assets.data', 2));
    }

    public function test_manager_can_add_an_asset(): void
    {
        $this->actingAs($this->manager())
            ->post('/console/assets', $this->payload([
                'serial_number' => 'C02XX1234',
                'purchase_cost' => '85000',
                'purchase_currency' => 'GMD',
            ]))
            ->assertRedirect();

        $this->assertDatabaseHas('assets', ['name' => 'MacBook Pro 14', 'serial_number' => 'C02XX1234']);
    }

    public function test_serial_numbers_are_unique(): void
    {
        Asset::factory()->create(['serial_number' => 'DUP-1']);

        $this->actingAs($this->manager())
            ->post('/console/assets', $this->payload(['serial_number' => 'DUP-1']))
            ->assertSessionHasErrors('serial_number');
    }

    public function test_assigning_an_asset_records_it_and_marks_it_in_use(): void
    {
        $asset = Asset::factory()->status('spare')->create();
        $holder = User::factory()->create();

        $this->actingAs($this->manager())
            ->post("/console/assets/{$asset->id}/assign", ['user_id' => $holder->id])
            ->assertRedirect();

        $asset->refresh();
        $this->assertSame($holder->id, $asset->assigned_to);
        $this->assertSame('in_use', $asset->status);
        $this->assertDatabaseHas('asset_assignments', [
            'asset_id' => $asset->id,
            'user_id' => $holder->id,
            'returned_on' => null,
        ]);
    }

    public function test_reassigning_closes_the_previous_assignment(): void
    {
        $asset = Asset::factory()->create();
        $first = User::factory()->create();
        $second = User::factory()->create();
        $manager = $this->manager();

        $this->actingAs($manager)->post("/console/assets/{$asset->id}/assign", ['user_id' => $first->id]);
        $this->actingAs($manager)->post("/console/assets/{$asset->id}/assign", ['user_id' => $second->id]);

        $this->assertSame($second->id, $asset->fresh()->assigned_to);
        $this->assertSame(1, $asset->assignments()->whereNull('returned_on')->count());
        $this->assertSame(2, $asset->assignments()->count());
    }

    public function test_returning_an_asset_clears_the_holder(): void
    {
        $asset = Asset::factory()->create();
        $holder = User::factory()->create();
        $manager = $this->manager();

        $this->actingAs($manager)->post("/console/assets/{$asset->id}/assign", ['user_id' => $holder->id]);
        $this->actingAs($manager)->post("/console/assets/{$asset->id}/unassign")->assertRedirect();

        $asset->refresh();
        $this->assertNull($asset->assigned_to);
        $this->assertSame('spare', $asset->status);
        $this->assertNull($asset->assignments()->whereNull('returned_on')->first());
    }

    public function test_retiring_a_held_asset_closes_its_assignment(): void
    {
        $asset = Asset::factory()->create();
        $holder = User::factory()->create();
        $manager = $this->manager();

        $this->actingAs($manager)->post("/console/assets/{$asset->id}/assign", ['user_id' => $holder->id]);
        $this->actingAs($manager)->put("/console/assets/{$asset->id}/status", ['status' => 'retired'])->assertRedirect();

        $asset->refresh();
        $this->assertSame('retired', $asset->status);
        $this->assertNull($asset->assigned_to);
        $this->assertNull($asset->assignments()->whereNull('returned_on')->first());
    }

    public function test_remove_and_restore(): void
    {
        $asset = Asset::factory()->create();
        $manager = $this->manager();

        $this->actingAs($manager)->delete("/console/assets/{$asset->id}")->assertRedirect();
        $this->assertSoftDeleted($asset);

        $this->actingAs($manager)->put("/console/assets/{$asset->id}/restore")->assertRedirect();
        $this->assertNotSoftDeleted($asset->fresh());
    }

    public function test_the_api_lists_assets(): void
    {
        $user = $this->manager();
        Asset::factory()->create();

        $token = $user->createToken('t', ['assets.view'])->plainTextToken;
        $this->withToken($token)->getJson('/api/v1/assets')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_the_api_creates_an_asset(): void
    {
        $token = $this->manager()->createToken('t', ['assets.manage'])->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/assets', $this->payload(['name' => 'Dell Monitor']))
            ->assertCreated()->assertJsonPath('data.name', 'Dell Monitor');
    }

    public function test_the_api_write_needs_the_manage_ability(): void
    {
        $token = $this->manager()->createToken('t', ['assets.view'])->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/assets', $this->payload())->assertForbidden();
    }
}
