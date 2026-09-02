<?php

namespace Tests\Feature\Console;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use App\Support\Rbac;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ProjectManagementTest extends TestCase
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

    public function test_a_console_user_without_the_permission_cannot_see_projects(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Rbac::PERM_CONSOLE_ACCESS);

        $this->actingAs($user)->get('/console/projects')->assertForbidden();
    }

    public function test_the_list_shows_open_projects_by_default_and_filters_work(): void
    {
        Project::factory()->status('active')->create();
        Project::factory()->status('proposed')->create();
        Project::factory()->status('completed')->create();

        $this->actingAs($this->manager())
            ->get('/console/projects')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Console/Projects/Index')
                ->has('projects.data', 2));

        $this->actingAs($this->manager())
            ->get('/console/projects?status=completed')
            ->assertInertia(fn ($page) => $page->has('projects.data', 1));
    }

    public function test_manager_can_create_a_project_with_a_team_and_tags(): void
    {
        $manager = $this->manager();
        $client = Client::factory()->create();
        $designer = User::factory()->create();

        $this->actingAs($manager)->post('/console/projects', [
            'name' => 'Brand refresh',
            'client_id' => $client->id,
            'service_line' => 'design',
            'status' => 'active',
            'owner_id' => $manager->id,
            'starts_on' => '2026-09-01',
            'ends_on' => '2026-11-30',
            'budget_amount' => 250000,
            'budget_currency' => 'gmd',
            'members' => [
                ['user_id' => $designer->id, 'role' => 'Lead designer'],
                ['user_id' => $manager->id, 'role' => 'PM'],
            ],
            'tags' => ['Retainer'],
        ])->assertRedirect();

        $project = Project::firstWhere('name', 'Brand refresh');
        $this->assertSame('design', $project->service_line);
        $this->assertSame('GMD', $project->budget_currency);
        $this->assertSame($manager->id, $project->created_by);
        $this->assertEqualsCanonicalizing([$designer->id, $manager->id], $project->members->pluck('id')->all());
        $this->assertSame('Lead designer', $project->members->firstWhere('id', $designer->id)->pivot->role);
        $this->assertSame(['Retainer'], $project->tags->pluck('name')->all());
        $this->assertTrue(Activity::forSubject($project)->where('event', 'created')->exists());
    }

    public function test_update_syncs_the_team(): void
    {
        $manager = $this->manager();
        $keep = User::factory()->create();
        $drop = User::factory()->create();
        $add = User::factory()->create();

        $project = Project::factory()->create();
        $project->members()->sync([$keep->id => ['role' => 'A'], $drop->id => ['role' => 'B']]);

        $this->actingAs($manager)->put("/console/projects/{$project->id}", [
            'name' => $project->name,
            'client_id' => $project->client_id,
            'service_line' => $project->service_line,
            'status' => 'on_hold',
            'members' => [
                ['user_id' => $keep->id, 'role' => 'A2'],
                ['user_id' => $add->id, 'role' => 'C'],
            ],
        ])->assertRedirect();

        $project->refresh();
        $this->assertSame('on_hold', $project->status);
        $this->assertEqualsCanonicalizing([$keep->id, $add->id], $project->members->pluck('id')->all());
    }

    public function test_validation_rejects_a_bad_date_range_and_a_budget_without_currency(): void
    {
        $client = Client::factory()->create();

        $this->actingAs($this->manager())->post('/console/projects', [
            'name' => 'X',
            'client_id' => $client->id,
            'service_line' => 'business',
            'status' => 'proposed',
            'starts_on' => '2026-06-01',
            'ends_on' => '2026-01-01',
            'budget_amount' => 1000,
        ])->assertSessionHasErrors(['ends_on', 'budget_currency']);
    }

    public function test_a_project_can_be_archived_and_restored(): void
    {
        $manager = $this->manager();
        $project = Project::factory()->create();

        $this->actingAs($manager)->delete("/console/projects/{$project->id}")->assertRedirect('/console/projects');
        $this->assertSoftDeleted($project);

        $this->actingAs($manager)->put("/console/projects/{$project->id}/restore")
            ->assertRedirect("/console/projects/{$project->id}");
        $this->assertNotSoftDeleted($project);
    }

    public function test_the_client_page_lists_its_projects(): void
    {
        $manager = $this->manager();
        $client = Client::factory()->create();
        Project::factory()->for($client)->create(['name' => 'On the client page']);

        $this->actingAs($manager)
            ->get("/console/clients/{$client->id}")
            ->assertInertia(fn ($page) => $page
                ->has('client.projects', 1)
                ->where('client.projects.0.name', 'On the client page'));
    }

    public function test_the_api_lists_and_creates_projects_by_ability(): void
    {
        Project::factory()->count(2)->create();
        $client = Client::factory()->create();
        $user = User::factory()->create();
        $user->givePermissionTo([Rbac::PERM_PROJECTS_VIEW, Rbac::PERM_PROJECTS_MANAGE]);
        $rw = $user->createToken('rw', ['projects.view', 'projects.manage'])->plainTextToken;

        $this->withToken($rw)->getJson('/api/v1/projects')->assertOk()->assertJsonCount(2, 'data');

        $this->withToken($rw)->postJson('/api/v1/projects', [
            'name' => 'API project',
            'client_id' => $client->id,
            'service_line' => 'technology',
            'status' => 'proposed',
        ])->assertCreated()->assertJsonPath('data.service_line', 'technology');
    }

    public function test_a_view_only_api_token_cannot_create_a_project(): void
    {
        $client = Client::factory()->create();
        $user = User::factory()->create();
        $user->givePermissionTo([Rbac::PERM_PROJECTS_VIEW, Rbac::PERM_PROJECTS_MANAGE]);
        $ro = $user->createToken('ro', ['projects.view'])->plainTextToken;

        $this->withToken($ro)->postJson('/api/v1/projects', [
            'name' => 'X', 'client_id' => $client->id, 'service_line' => 'business', 'status' => 'proposed',
        ])->assertForbidden();
    }
}
