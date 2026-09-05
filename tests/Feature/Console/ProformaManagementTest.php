<?php

namespace Tests\Feature\Console;

use App\Models\Client;
use App\Models\ExchangeRate;
use App\Models\Proforma;
use App\Models\Project;
use App\Models\User;
use App\Support\Rbac;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProformaManagementTest extends TestCase
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
            'client_id' => Client::factory()->create()->id,
            'currency' => 'GMD',
            'issue_date' => '2026-09-01',
            'valid_until' => '2026-10-01',
            'tax_label' => 'VAT',
            'tax_rate' => 15,
            'lines' => [
                ['description' => 'Design work', 'quantity' => '2', 'unit_price' => '500.00'],
                ['description' => 'Hosting setup', 'quantity' => '1', 'unit_price' => '250.00'],
            ],
        ], $overrides);
    }

    public function test_a_console_user_without_permission_cannot_see_proformas(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Rbac::PERM_CONSOLE_ACCESS);

        $this->actingAs($user)->get('/console/proformas')->assertForbidden();
    }

    public function test_the_list_shows_open_proformas_by_default(): void
    {
        Proforma::factory()->status('draft')->create();
        Proforma::factory()->status('sent')->create();
        Proforma::factory()->status('declined')->create();
        Proforma::factory()->status('converted')->create();

        $this->actingAs($this->manager())
            ->get('/console/proformas')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Console/Proformas/Index')
                ->has('proformas.data', 2));
    }

    public function test_manager_creates_a_proforma_with_a_generated_number_and_computed_totals(): void
    {
        $this->actingAs($this->manager())
            ->post('/console/proformas', $this->payload())
            ->assertRedirect();

        $proforma = Proforma::first();

        $this->assertSame('PRO-'.now()->year.'-0001', $proforma->number);
        $this->assertSame('draft', $proforma->status);
        $this->assertSame('1250.00', $proforma->subtotal);   // 2*500 + 250
        $this->assertSame('187.50', $proforma->tax_total);   // 15% of 1250
        $this->assertSame('1437.50', $proforma->total);
        $this->assertSame('1437.50', $proforma->base_total); // GMD is the base
        $this->assertCount(2, $proforma->lines);
    }

    public function test_a_foreign_currency_proforma_snapshots_the_fx_rate_and_base_total(): void
    {
        ExchangeRate::factory()->for_currency('USD')->on('2026-08-01')->create(['rate' => '70']);

        $this->actingAs($this->manager())
            ->post('/console/proformas', $this->payload([
                'currency' => 'USD',
                'tax_rate' => 0,
                'lines' => [['description' => 'Retainer', 'quantity' => '1', 'unit_price' => '100.00']],
            ]))
            ->assertRedirect();

        $proforma = Proforma::first();

        $this->assertSame('USD', $proforma->currency);
        $this->assertSame('70.0000000000', (string) $proforma->fx_rate);
        $this->assertSame('100.00', $proforma->total);
        $this->assertSame('7000.00', $proforma->base_total);
    }

    public function test_a_foreign_currency_with_no_rate_on_file_is_rejected(): void
    {
        $this->actingAs($this->manager())
            ->post('/console/proformas', $this->payload(['currency' => 'EUR']))
            ->assertSessionHasErrors('fx_rate');
    }

    public function test_a_project_from_another_client_is_rejected(): void
    {
        $otherProject = Project::factory()->create();

        $this->actingAs($this->manager())
            ->post('/console/proformas', $this->payload(['project_id' => $otherProject->id]))
            ->assertSessionHasErrors('project_id');
    }

    public function test_only_a_draft_can_be_edited(): void
    {
        $sent = Proforma::factory()->status('sent')->withLine()->create();

        $this->actingAs($this->manager())
            ->get("/console/proformas/{$sent->id}/edit")
            ->assertForbidden();
    }

    public function test_manager_can_move_a_proforma_through_its_lifecycle(): void
    {
        $proforma = Proforma::factory()->status('draft')->withLine()->create();

        $this->actingAs($this->manager())
            ->put("/console/proformas/{$proforma->id}/status", ['status' => 'sent'])
            ->assertRedirect();

        $this->assertSame('sent', $proforma->fresh()->status);
    }

    public function test_a_converted_proforma_is_locked_against_status_changes(): void
    {
        $proforma = Proforma::factory()->status('converted')->withLine()->create();

        $this->actingAs($this->manager())
            ->put("/console/proformas/{$proforma->id}/status", ['status' => 'sent'])
            ->assertSessionHasErrors('status');
    }

    public function test_archive_and_restore(): void
    {
        $proforma = Proforma::factory()->withLine()->create();

        $this->actingAs($this->manager())->delete("/console/proformas/{$proforma->id}")->assertRedirect();
        $this->assertSoftDeleted($proforma);

        $this->actingAs($this->manager())->put("/console/proformas/{$proforma->id}/restore")->assertRedirect();
        $this->assertNotSoftDeleted($proforma->fresh());
    }

    public function test_the_pdf_downloads(): void
    {
        $proforma = Proforma::factory()->withLine()->create();

        $response = $this->actingAs($this->manager())->get("/console/proformas/{$proforma->id}/pdf");

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_the_api_creates_and_lists_proformas(): void
    {
        $user = $this->manager();
        $client = Client::factory()->create();

        $create = $user->createToken('t', ['proformas.manage'])->plainTextToken;
        $response = $this->withToken($create)->postJson('/api/v1/proformas', $this->payload([
            'client_id' => $client->id,
            'tax_rate' => 0,
            'lines' => [['description' => 'Work', 'quantity' => '3', 'unit_price' => '100']],
        ]));

        $response->assertCreated()->assertJsonPath('data.total', '300.00');
    }

    public function test_the_api_list_needs_the_view_ability(): void
    {
        $user = $this->manager();
        Proforma::factory()->withLine()->create();

        $token = $user->createToken('t', ['clients.view'])->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/proformas')->assertForbidden();
    }
}
