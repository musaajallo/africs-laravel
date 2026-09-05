<?php

namespace Tests\Feature\Console;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Support\Rbac;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentManagementTest extends TestCase
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

    protected function invoice(array $overrides = []): Invoice
    {
        $invoice = Invoice::factory()->status('sent')->create($overrides);
        $invoice->lines()->create(['description' => 'Work', 'quantity' => '1', 'unit_price' => '1000', 'position' => 0]);
        $invoice->load('lines');
        $invoice->saveWithTotals();

        return $invoice->fresh();
    }

    public function test_permission_gate(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Rbac::PERM_CONSOLE_ACCESS);

        $this->actingAs($user)->get('/console/payments')->assertForbidden();
    }

    public function test_recording_a_full_payment_marks_the_invoice_paid(): void
    {
        $invoice = $this->invoice();

        $this->actingAs($this->manager())->post('/console/payments', [
            'client_id' => $invoice->client_id,
            'currency' => 'GMD',
            'amount' => '1000',
            'method' => 'Bank transfer',
            'paid_on' => now()->toDateString(),
            'allocations' => [['invoice_id' => $invoice->id, 'amount' => '1000']],
        ])->assertRedirect();

        $payment = Payment::first();
        $this->assertSame('RCT-'.now()->year.'-0001', $payment->number);
        $this->assertSame('1000.00', $payment->allocated_amount);

        $invoice->refresh();
        $this->assertSame('1000.00', $invoice->amount_paid);
        $this->assertSame('paid', $invoice->status);
    }

    public function test_a_part_payment_marks_the_invoice_partially_paid(): void
    {
        $invoice = $this->invoice();

        $this->actingAs($this->manager())->post('/console/payments', [
            'client_id' => $invoice->client_id,
            'currency' => 'GMD',
            'amount' => '400',
            'method' => 'Cash',
            'paid_on' => now()->toDateString(),
            'allocations' => [['invoice_id' => $invoice->id, 'amount' => '400']],
        ])->assertRedirect();

        $invoice->refresh();
        $this->assertSame('400.00', $invoice->amount_paid);
        $this->assertSame('partially_paid', $invoice->status);
        $this->assertSame('600.00', $invoice->balance());
    }

    public function test_one_payment_can_settle_several_invoices(): void
    {
        $client = Client::factory()->create();
        $a = $this->invoice(['client_id' => $client->id]);
        $b = $this->invoice(['client_id' => $client->id]);

        $this->actingAs($this->manager())->post('/console/payments', [
            'client_id' => $client->id,
            'currency' => 'GMD',
            'amount' => '2000',
            'method' => 'Bank transfer',
            'paid_on' => now()->toDateString(),
            'allocations' => [
                ['invoice_id' => $a->id, 'amount' => '1000'],
                ['invoice_id' => $b->id, 'amount' => '1000'],
            ],
        ])->assertRedirect();

        $this->assertSame('paid', $a->fresh()->status);
        $this->assertSame('paid', $b->fresh()->status);
    }

    public function test_allocations_cannot_exceed_the_payment_amount(): void
    {
        $invoice = $this->invoice();

        $this->actingAs($this->manager())->post('/console/payments', [
            'client_id' => $invoice->client_id,
            'currency' => 'GMD',
            'amount' => '500',
            'method' => 'Cash',
            'paid_on' => now()->toDateString(),
            'allocations' => [['invoice_id' => $invoice->id, 'amount' => '900']],
        ])->assertSessionHasErrors('allocations');
    }

    public function test_an_allocation_cannot_exceed_the_invoice_balance(): void
    {
        $invoice = $this->invoice();

        $this->actingAs($this->manager())->post('/console/payments', [
            'client_id' => $invoice->client_id,
            'currency' => 'GMD',
            'amount' => '5000',
            'method' => 'Cash',
            'paid_on' => now()->toDateString(),
            'allocations' => [['invoice_id' => $invoice->id, 'amount' => '5000']],
        ])->assertSessionHasErrors('allocations.0.amount');
    }

    public function test_a_currency_mismatch_is_rejected(): void
    {
        $invoice = $this->invoice(['currency' => 'USD', 'fx_rate' => '70']);

        $this->actingAs($this->manager())->post('/console/payments', [
            'client_id' => $invoice->client_id,
            'currency' => 'GMD',
            'amount' => '1000',
            'method' => 'Cash',
            'paid_on' => now()->toDateString(),
            'allocations' => [['invoice_id' => $invoice->id, 'amount' => '1000']],
        ])->assertSessionHasErrors('allocations.0.invoice_id');
    }

    public function test_deleting_a_payment_releases_its_invoices(): void
    {
        $invoice = $this->invoice();
        $payment = Payment::factory()->create(['client_id' => $invoice->client_id, 'amount' => '1000']);
        $payment->applyAllocations([['invoice_id' => $invoice->id, 'amount' => '1000']]);

        $this->assertSame('paid', $invoice->fresh()->status);

        $this->actingAs($this->manager())->delete("/console/payments/{$payment->id}")->assertRedirect();

        $invoice->refresh();
        $this->assertSame('0.00', $invoice->amount_paid);
        $this->assertSame('sent', $invoice->status);
        $this->assertSoftDeleted($payment);
    }

    public function test_editing_a_payments_allocation_updates_both_invoices(): void
    {
        $client = Client::factory()->create();
        $a = $this->invoice(['client_id' => $client->id]);
        $b = $this->invoice(['client_id' => $client->id]);

        $payment = Payment::factory()->create(['client_id' => $client->id, 'amount' => '1000']);
        $payment->applyAllocations([['invoice_id' => $a->id, 'amount' => '1000']]);
        $this->assertSame('paid', $a->fresh()->status);

        $this->actingAs($this->manager())->put("/console/payments/{$payment->id}", [
            'client_id' => $client->id,
            'currency' => 'GMD',
            'amount' => '1000',
            'method' => 'Bank transfer',
            'paid_on' => now()->toDateString(),
            'allocations' => [['invoice_id' => $b->id, 'amount' => '1000']],
        ])->assertRedirect();

        $this->assertSame('sent', $a->fresh()->status);
        $this->assertSame('paid', $b->fresh()->status);
    }

    public function test_receipt_pdf_downloads(): void
    {
        $payment = Payment::factory()->create();

        $response = $this->actingAs($this->manager())->get("/console/payments/{$payment->id}/pdf");

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_receivables_view_lists_outstanding_balances(): void
    {
        $client = Client::factory()->create(['name' => 'Owing Co']);
        $this->invoice(['client_id' => $client->id, 'due_date' => now()->subDays(10)->toDateString()]);

        $this->actingAs($this->manager())
            ->get('/console/receivables')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Console/Receivables/Index')
                ->where('totals.total', '1000.00')
                ->has('clients', 1));
    }

    public function test_api_records_a_payment(): void
    {
        $invoice = $this->invoice();
        $token = $this->manager()->createToken('t', ['payments.manage'])->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/payments', [
            'client_id' => $invoice->client_id,
            'currency' => 'GMD',
            'amount' => '1000',
            'method' => 'Bank transfer',
            'paid_on' => now()->toDateString(),
            'allocations' => [['invoice_id' => $invoice->id, 'amount' => '1000']],
        ])->assertCreated();

        $this->assertSame('paid', $invoice->fresh()->status);
    }
}
