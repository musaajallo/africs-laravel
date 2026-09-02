<?php

namespace Tests\Feature\Console;

use App\Models\Client;
use App\Models\User;
use App\Support\Rbac;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ActivityLogTest extends TestCase
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

    public function test_a_user_without_the_permission_cannot_open_the_activity_log(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Rbac::PERM_CONSOLE_ACCESS);

        $this->actingAs($user)->get('/console/activity')->assertForbidden();
    }

    public function test_creating_and_editing_a_client_is_logged_with_the_causer(): void
    {
        $manager = $this->manager();

        $this->actingAs($manager)->post('/console/clients', [
            'name' => 'Logged Co',
            'type' => 'organisation',
            'status' => 'active',
        ]);

        $client = Client::firstWhere('name', 'Logged Co');

        $created = Activity::forSubject($client)->where('event', 'created')->firstOrFail();
        $this->assertSame('Client created', $created->description);
        $this->assertTrue($manager->is($created->causer));

        $this->actingAs($manager)->put("/console/clients/{$client->id}", [
            'name' => 'Renamed Co',
            'type' => 'organisation',
            'status' => 'active',
        ]);

        $updated = Activity::forSubject($client)->where('event', 'updated')->firstOrFail();
        $this->assertSame('Client details updated', $updated->description);
        $this->assertSame('Renamed Co', $updated->attribute_changes['attributes']['name']);
        $this->assertSame('Logged Co', $updated->attribute_changes['old']['name']);
    }

    public function test_archive_restore_and_contact_changes_are_logged_against_the_client(): void
    {
        $manager = $this->manager();
        $client = Client::factory()->create();

        $this->actingAs($manager)->post("/console/clients/{$client->id}/contacts", ['name' => 'Sana Camara']);
        $this->actingAs($manager)->delete("/console/clients/{$client->id}");
        $this->actingAs($manager)->put("/console/clients/{$client->id}/restore");

        $descriptions = Activity::forSubject($client)->pluck('description');

        $this->assertTrue($descriptions->contains(fn ($d) => str_contains($d, 'Sana Camara')));
        $this->assertTrue($descriptions->contains('Client archived'));
        $this->assertTrue($descriptions->contains('Client restored'));
    }

    public function test_tag_changes_are_logged(): void
    {
        $manager = $this->manager();
        $client = Client::factory()->create();

        $this->actingAs($manager)->put("/console/clients/{$client->id}", [
            'name' => $client->name,
            'type' => $client->type,
            'status' => $client->status,
            'tags' => ['VIP', 'Retainer'],
        ]);

        $this->assertTrue(
            Activity::forSubject($client)->where('event', 'tags')->exists(),
        );
    }

    public function test_the_activity_page_and_the_client_page_show_entries(): void
    {
        $manager = $this->manager();
        $client = Client::factory()->create(['name' => 'Feed Co']);

        $this->actingAs($manager)
            ->get('/console/activity')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Console/Activity/Index')
                ->has('activities.data'));

        $this->actingAs($manager)
            ->get("/console/clients/{$client->id}")
            ->assertInertia(fn ($page) => $page->has('activity'));
    }
}
