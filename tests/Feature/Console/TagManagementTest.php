<?php

namespace Tests\Feature\Console;

use App\Models\Client;
use App\Models\Tag;
use App\Models\User;
use App\Support\Rbac;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagManagementTest extends TestCase
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

    public function test_a_user_without_the_permission_cannot_open_tags(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Rbac::PERM_CONSOLE_ACCESS);

        $this->actingAs($user)->get('/console/tags')->assertForbidden();
    }

    public function test_manager_can_create_rename_recolour_and_delete_a_tag(): void
    {
        $manager = $this->manager();

        $this->actingAs($manager)
            ->post('/console/tags', ['name' => 'Retainer', 'color' => 'green'])
            ->assertRedirect('/console/tags');

        $tag = Tag::firstWhere('name', 'Retainer');
        $this->assertSame('retainer', $tag->slug);
        $this->assertSame('green', $tag->color);

        $this->actingAs($manager)
            ->put("/console/tags/{$tag->id}", ['name' => 'Retainer client', 'color' => 'blue'])
            ->assertRedirect('/console/tags');
        $tag->refresh();
        $this->assertSame('retainer-client', $tag->slug);
        $this->assertSame('blue', $tag->color);

        $this->actingAs($manager)->delete("/console/tags/{$tag->id}")->assertRedirect('/console/tags');
        $this->assertModelMissing($tag);
    }

    public function test_tag_names_must_be_unique_and_the_colour_valid(): void
    {
        Tag::create(['name' => 'VIP', 'color' => 'amber']);

        $this->actingAs($this->manager())
            ->post('/console/tags', ['name' => 'VIP', 'color' => 'purple'])
            ->assertSessionHasErrors(['name', 'color']);
    }

    public function test_client_tags_are_created_on_the_fly_and_synced(): void
    {
        $manager = $this->manager();

        $this->actingAs($manager)->post('/console/clients', [
            'name' => 'Tagged Co',
            'type' => 'organisation',
            'status' => 'active',
            'tags' => ['NGO partner', 'Retainer', ' Retainer '], // dupe + whitespace
        ])->assertRedirect();

        $client = Client::firstWhere('name', 'Tagged Co');
        $this->assertEqualsCanonicalizing(['NGO partner', 'Retainer'], $client->tags->pluck('name')->all());
        $this->assertSame(2, Tag::count());

        // update replaces the set
        $this->actingAs($manager)->put("/console/clients/{$client->id}", [
            'name' => 'Tagged Co',
            'type' => 'organisation',
            'status' => 'active',
            'tags' => ['Retainer'],
        ])->assertRedirect();

        $this->assertSame(['Retainer'], $client->fresh()->tags->pluck('name')->all());
    }

    public function test_clients_can_be_filtered_by_tag(): void
    {
        $vip = Client::factory()->create(['name' => 'Big Client']);
        $vip->syncTagsByName(['VIP']);
        Client::factory()->create(['name' => 'Small Client']);

        $this->actingAs($this->manager())
            ->get('/console/clients?tag=vip')
            ->assertInertia(fn ($page) => $page->has('clients.data', 1));
    }
}
