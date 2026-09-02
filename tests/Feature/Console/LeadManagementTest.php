<?php

namespace Tests\Feature\Console;

use App\Models\Client;
use App\Models\Lead;
use App\Models\User;
use App\Support\Rbac;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class LeadManagementTest extends TestCase
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

    public function test_a_console_user_without_the_permission_cannot_see_leads(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Rbac::PERM_CONSOLE_ACCESS);

        $this->actingAs($user)->get('/console/leads')->assertForbidden();
    }

    public function test_the_inbox_hides_converted_leads_by_default(): void
    {
        Lead::factory()->count(2)->create();
        Lead::factory()->status('converted')->create();

        $this->actingAs($this->manager())
            ->get('/console/leads')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Console/Leads/Index')
                ->has('leads.data', 2));
    }

    public function test_triage_updates_status_owner_and_notes_and_is_logged(): void
    {
        $manager = $this->manager();
        $lead = Lead::factory()->create();

        $this->actingAs($manager)->put("/console/leads/{$lead->id}", [
            'status' => 'contacted',
            'owner_id' => $manager->id,
            'notes' => 'Called, waiting on a brief.',
        ])->assertRedirect();

        $lead->refresh();
        $this->assertSame('contacted', $lead->status);
        $this->assertSame($manager->id, $lead->owner_id);
        $this->assertSame('Called, waiting on a brief.', $lead->notes);
        $this->assertTrue(Activity::forSubject($lead)->where('event', 'updated')->exists());
    }

    public function test_converting_a_company_lead_creates_an_organisation_client_with_a_contact(): void
    {
        $manager = $this->manager();
        $lead = Lead::factory()->create([
            'name' => 'Awa Touray',
            'company' => 'Timbooktoo Ltd',
            'email' => 'awa@timbooktoo.gm',
            'phone' => '+220 700 0000',
        ]);
        $lead->update(['owner_id' => $manager->id]);

        $this->actingAs($manager)
            ->post("/console/leads/{$lead->id}/convert")
            ->assertRedirect();

        $client = Client::firstWhere('name', 'Timbooktoo Ltd');
        $this->assertNotNull($client);
        $this->assertSame('organisation', $client->type);
        $this->assertSame($manager->id, $client->owner_id);

        $contact = $client->contacts()->firstOrFail();
        $this->assertSame('Awa Touray', $contact->name);
        $this->assertTrue($contact->is_primary);

        $lead->refresh();
        $this->assertSame('converted', $lead->status);
        $this->assertSame($client->id, $lead->converted_client_id);
    }

    public function test_converting_a_personal_lead_creates_an_individual_client(): void
    {
        $manager = $this->manager();
        $lead = Lead::factory()->create(['name' => 'Fatou Njie', 'company' => null, 'email' => 'fatou@example.com']);

        $this->actingAs($manager)->post("/console/leads/{$lead->id}/convert")->assertRedirect();

        $client = Client::firstWhere('name', 'Fatou Njie');
        $this->assertSame('individual', $client->type);
        $this->assertSame('fatou@example.com', $client->email);
        $this->assertSame(0, $client->contacts()->count());
    }

    public function test_an_already_converted_lead_cannot_be_converted_again(): void
    {
        $manager = $this->manager();
        $client = Client::factory()->create();
        $lead = Lead::factory()->status('converted')->create(['converted_client_id' => $client->id]);

        $this->actingAs($manager)->post("/console/leads/{$lead->id}/convert")->assertForbidden();
    }

    public function test_a_lead_can_be_deleted(): void
    {
        $manager = $this->manager();
        $lead = Lead::factory()->create();

        $this->actingAs($manager)->delete("/console/leads/{$lead->id}")->assertRedirect('/console/leads');
        $this->assertModelMissing($lead);
    }

    public function test_the_api_exposes_leads_read_only_with_the_view_ability(): void
    {
        Lead::factory()->count(3)->create();
        $user = User::factory()->create();
        $user->givePermissionTo(Rbac::PERM_LEADS_VIEW);
        $plain = $user->createToken('t', ['leads.view'])->plainTextToken;

        $this->withToken($plain)
            ->getJson('/api/v1/leads')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure(['data' => [['id', 'name', 'status']]]);
    }
}
