<?php

namespace Tests\Feature\Console;

use App\Models\Client;
use App\Models\Contact;
use App\Models\User;
use App\Support\Rbac;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientManagementTest extends TestCase
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

    public function test_a_console_user_without_the_permission_cannot_see_clients(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Rbac::PERM_CONSOLE_ACCESS);

        $this->actingAs($user)->get('/console/clients')->assertForbidden();
    }

    public function test_manager_can_list_and_search_clients(): void
    {
        Client::factory()->create(['name' => 'Kanifing Council', 'city' => 'Kanifing']);
        Client::factory()->create(['name' => 'Banjul Port Authority']);

        $this->actingAs($this->manager())
            ->get('/console/clients?search=Kanifing')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Console/Clients/Index')
                ->has('clients.data', 1));
    }

    public function test_manager_can_create_a_client_with_contacts(): void
    {
        $manager = $this->manager();

        $response = $this->actingAs($manager)->post('/console/clients', [
            'name' => 'Timbooktoo Ltd',
            'type' => 'organisation',
            'category' => 'Private company',
            'status' => 'active',
            'email' => 'hello@timbooktoo.gm',
            'currency' => 'gmd',
            'country' => 'gm',
            'contacts' => [
                ['name' => 'Awa Touray', 'title' => 'Director', 'email' => 'awa@timbooktoo.gm', 'is_primary' => true],
                ['name' => 'Modou Jallow', 'is_primary' => true],
            ],
        ]);

        $client = Client::firstWhere('name', 'Timbooktoo Ltd');
        $response->assertRedirect("/console/clients/{$client->id}");

        $this->assertSame('organisation', $client->type);
        $this->assertSame('Private company', $client->category);
        $this->assertSame('GMD', $client->currency);
        $this->assertSame('GM', $client->country);
        $this->assertSame($manager->id, $client->created_by);
        $this->assertCount(2, $client->contacts);
        // only the first flagged contact is stored as primary
        $this->assertSame(1, $client->contacts()->where('is_primary', true)->count());
        $this->assertSame('Awa Touray', $client->primaryContact()->first()->name);
    }

    public function test_update_syncs_contacts_and_fields(): void
    {
        $client = Client::factory()->create(['name' => 'Old Name']);
        $keep = Contact::factory()->for($client)->primary()->create(['name' => 'Keep Me']);
        Contact::factory()->for($client)->create(['name' => 'Drop Me']);

        $this->actingAs($this->manager())
            ->put("/console/clients/{$client->id}", [
                'name' => 'New Name',
                'type' => 'organisation',
                'status' => 'inactive',
                'contacts' => [
                    ['id' => $keep->id, 'name' => 'Keep Me Renamed', 'is_primary' => true],
                    ['name' => 'Brand New', 'is_primary' => false],
                ],
            ])
            ->assertRedirect("/console/clients/{$client->id}");

        $client->refresh();
        $this->assertSame('New Name', $client->name);
        $this->assertSame('inactive', $client->status);
        $this->assertEqualsCanonicalizing(
            ['Keep Me Renamed', 'Brand New'],
            $client->contacts->pluck('name')->all(),
        );
    }

    public function test_archiving_hides_the_client_but_keeps_it_reachable(): void
    {
        $manager = $this->manager();
        $client = Client::factory()->create();

        $this->actingAs($manager)->delete("/console/clients/{$client->id}")
            ->assertRedirect('/console/clients');
        $this->assertSoftDeleted($client);

        $this->actingAs($manager)->get('/console/clients')
            ->assertInertia(fn ($page) => $page->has('clients.data', 0));

        $this->actingAs($manager)->get("/console/clients/{$client->id}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('client.archived', true));

        $this->actingAs($manager)->put("/console/clients/{$client->id}/restore")
            ->assertRedirect("/console/clients/{$client->id}");
        $this->assertNotSoftDeleted($client);
    }

    public function test_validation_rejects_a_client_without_a_name(): void
    {
        $this->actingAs($this->manager())
            ->post('/console/clients', ['type' => 'organisation', 'status' => 'active'])
            ->assertSessionHasErrors('name');
    }

    public function test_category_must_belong_to_the_selected_type(): void
    {
        $this->actingAs($this->manager())
            ->post('/console/clients', [
                'name' => 'Wrong Category Co',
                'type' => 'government',
                'category' => 'NGO', // an organisation category, not a government one
                'status' => 'active',
            ])
            ->assertSessionHasErrors('category');
    }

    public function test_an_individual_client_has_no_category(): void
    {
        $this->actingAs($this->manager())
            ->post('/console/clients', [
                'name' => 'Fatou Njie',
                'type' => 'individual',
                'category' => 'NGO',
                'status' => 'active',
            ])
            ->assertRedirect();

        $this->assertNull(Client::firstWhere('name', 'Fatou Njie')->category);
    }

    public function test_manager_can_add_edit_and_remove_a_contact_from_the_client_page(): void
    {
        $manager = $this->manager();
        $client = Client::factory()->create();
        $existing = Contact::factory()->for($client)->primary()->create(['name' => 'First']);

        // add — flagged primary, so it steals primary from the existing one
        $this->actingAs($manager)
            ->post("/console/clients/{$client->id}/contacts", ['name' => 'Second', 'is_primary' => true])
            ->assertRedirect();

        $second = $client->contacts()->where('name', 'Second')->firstOrFail();
        $this->assertTrue($second->is_primary);
        $this->assertFalse($existing->fresh()->is_primary);

        // edit
        $this->actingAs($manager)
            ->put("/console/clients/{$client->id}/contacts/{$second->id}", ['name' => 'Second Renamed'])
            ->assertRedirect();
        $this->assertSame('Second Renamed', $second->fresh()->name);

        // remove
        $this->actingAs($manager)
            ->delete("/console/clients/{$client->id}/contacts/{$second->id}")
            ->assertRedirect();
        $this->assertModelMissing($second);
    }

    public function test_a_contact_route_is_scoped_to_its_client(): void
    {
        $manager = $this->manager();
        $contact = Contact::factory()->create(); // belongs to some other client
        $otherClient = Client::factory()->create();

        $this->actingAs($manager)
            ->put("/console/clients/{$otherClient->id}/contacts/{$contact->id}", ['name' => 'Nope'])
            ->assertNotFound();
    }
}
