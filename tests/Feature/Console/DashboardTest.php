<?php

namespace Tests\Feature\Console;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\User;
use App\Support\Rbac;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
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

    public function test_the_dashboard_summarises_the_modules_a_user_can_see(): void
    {
        Lead::factory()->count(3)->create(['status' => 'new']);
        Lead::factory()->create(['status' => 'converted']);
        Client::factory()->count(2)->create(['status' => 'active']);

        $overdue = Invoice::factory()->status('sent')->withLine('Work', '1', '1000')->create([
            'due_date' => now()->subDays(9)->toDateString(),
        ]);

        Payment::factory()->create(['amount' => '500', 'paid_on' => now()->toDateString()]);

        $this->actingAs($this->manager())
            ->get('/console')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Console/Dashboard')
                ->where('base', 'GMD')
                ->has('metrics')
                ->has('overdueInvoices', 1)
                ->where('overdueInvoices.0.number', $overdue->number)
                ->where('overdueInvoices.0.days_overdue', 9)
                ->has('activity'));
    }

    public function test_widgets_are_hidden_when_the_user_lacks_the_permission(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo([Rbac::PERM_CONSOLE_ACCESS, Rbac::PERM_LEADS_VIEW]);

        Lead::factory()->count(2)->create(['status' => 'new']);

        $this->actingAs($user)
            ->get('/console')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('overdueInvoices', null)
                ->where('expiringProformas', null)
                ->where('activity', null)
                ->where('metrics', fn ($metrics) => collect($metrics)->pluck('key')->all() === ['leads']));
    }
}
