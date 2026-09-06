<?php

namespace Tests\Feature\Console;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Proforma;
use App\Models\User;
use App\Support\Analytics;
use App\Support\Rbac;
use App\Support\Settings;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Inertia;
use Tests\TestCase;

class DashboardInsightsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        Settings::flushCache();
        // Keep partial-reload requests from 409-ing on an asset-version mismatch.
        Inertia::version(null);
    }

    protected function manager(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Rbac::ROLE_CONSOLE_MANAGER);

        return $user;
    }

    public function test_the_dashboard_advertises_the_insights_tab_but_defers_the_payload(): void
    {
        $this->actingAs($this->manager())
            ->get('/console')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Console/Dashboard')
                ->where('canInsights', true)
                ->where('insightsRange', '90d')
                ->missing('insights'));
    }

    public function test_a_console_user_without_finance_permissions_cannot_see_insights(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Rbac::PERM_CONSOLE_ACCESS);

        $this->actingAs($user)
            ->get('/console')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('canInsights', false));
    }

    public function test_the_insights_payload_resolves_on_a_partial_reload(): void
    {
        $client = Client::factory()->create(['currency' => 'GMD']);
        $invoice = Invoice::factory()->create([
            'client_id' => $client->id,
            'status' => 'paid',
            'currency' => 'GMD',
            'base_total' => '100000',
            'issue_date' => now()->subDays(20),
        ]);
        Payment::factory()->create([
            'client_id' => $client->id,
            'currency' => 'GMD',
            'fx_rate' => '1',
            'amount' => '40000',
            'paid_on' => now()->subDays(10),
        ]);

        $this->actingAs($this->manager());

        $html = $this->get('/console')->getContent();
        preg_match('/data-page="([^"]+)"/', $html, $m);
        $version = json_decode(html_entity_decode($m[1], ENT_QUOTES), true)['version'] ?? null;

        $this->get('/console?range=30d', [
            'X-Inertia' => true,
            'X-Inertia-Version' => $version,
            'X-Inertia-Partial-Component' => 'Console/Dashboard',
            'X-Inertia-Partial-Data' => 'insights',
        ])
            ->assertOk()
            ->assertJsonPath('props.insights.currency', 'GMD')
            ->assertJsonPath('props.insights.range.key', '30d')
            ->assertJsonPath('props.insights.revenue.collected', 40000);
    }

    public function test_analytics_falls_back_to_a_safe_range_and_handles_empty_data(): void
    {
        $insights = (new Analytics)->insights('nonsense');

        $this->assertSame('90d', $insights['range']['key']);
        $this->assertSame(0.0, $insights['revenue']['collected']);
        $this->assertNull($insights['clients']['avg_ltv']);
        $this->assertNull($insights['clients']['cac']);
    }

    public function test_win_rate_counts_only_decided_proformas(): void
    {
        $client = Client::factory()->create();

        Proforma::factory()->create(['client_id' => $client->id, 'status' => 'accepted', 'issue_date' => now()->subDays(5)]);
        Proforma::factory()->create(['client_id' => $client->id, 'status' => 'declined', 'issue_date' => now()->subDays(5)]);
        Proforma::factory()->create(['client_id' => $client->id, 'status' => 'draft', 'issue_date' => now()->subDays(5)]);

        $insights = (new Analytics)->insights('30d');

        $this->assertSame(2, $insights['sales']['proforma_decided']);
        $this->assertSame(0.5, $insights['sales']['proforma_win_rate']);
    }
}
