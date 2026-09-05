<?php

namespace Tests\Feature\Console;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Proforma;
use App\Models\User;
use App\Support\Rbac;
use App\Support\Settings;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceManagementTest extends TestCase
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
            'due_date' => '2026-10-01',
            'tax_label' => 'VAT',
            'tax_rate' => 15,
            'lines' => [
                ['description' => 'Retainer', 'quantity' => '1', 'unit_price' => '1000.00'],
            ],
        ], $overrides);
    }

    public function test_permission_gate(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(Rbac::PERM_CONSOLE_ACCESS);

        $this->actingAs($user)->get('/console/invoices')->assertForbidden();
    }

    public function test_manager_creates_an_invoice_with_number_and_totals(): void
    {
        $this->actingAs($this->manager())
            ->post('/console/invoices', $this->payload())
            ->assertRedirect();

        $invoice = Invoice::first();

        $this->assertSame('INV-'.now()->year.'-0001', $invoice->number);
        $this->assertSame('1000.00', $invoice->subtotal);
        $this->assertSame('150.00', $invoice->tax_total);
        $this->assertSame('1150.00', $invoice->total);
    }

    public function test_proformas_and_invoices_number_independently(): void
    {
        Proforma::factory()->withLine()->create(); // does not touch the invoice sequence

        $this->actingAs($this->manager())->post('/console/invoices', $this->payload())->assertRedirect();

        $this->assertSame('INV-'.now()->year.'-0001', Invoice::first()->number);
    }

    public function test_only_a_draft_invoice_can_be_edited(): void
    {
        $sent = Invoice::factory()->status('sent')->withLine()->create();

        $this->actingAs($this->manager())->get("/console/invoices/{$sent->id}/edit")->assertForbidden();
    }

    public function test_status_transitions_and_archive_restore(): void
    {
        $invoice = Invoice::factory()->status('draft')->withLine()->create();
        $manager = $this->manager();

        $this->actingAs($manager)->put("/console/invoices/{$invoice->id}/status", ['status' => 'paid'])->assertRedirect();
        $this->assertSame('paid', $invoice->fresh()->status);

        $this->actingAs($manager)->delete("/console/invoices/{$invoice->id}")->assertRedirect();
        $this->assertSoftDeleted($invoice);

        $this->actingAs($manager)->put("/console/invoices/{$invoice->id}/restore")->assertRedirect();
        $this->assertNotSoftDeleted($invoice->fresh());
    }

    public function test_converting_a_proforma_creates_a_linked_draft_invoice(): void
    {
        Settings::put(['billing' => ['payment_terms_days' => 14]]);

        $proforma = Proforma::factory()->status('accepted')->create([
            'currency' => 'GMD',
            'tax_label' => 'VAT',
            'tax_rate' => 10,
        ]);
        $proforma->lines()->create(['description' => 'Build', 'quantity' => '2', 'unit_price' => '400', 'position' => 0]);
        $proforma->load('lines');
        $proforma->saveWithTotals();

        $this->actingAs($this->manager())
            ->post("/console/proformas/{$proforma->id}/convert")
            ->assertRedirect();

        $proforma->refresh();
        $invoice = Invoice::first();

        $this->assertSame('converted', $proforma->status);
        $this->assertSame($invoice->id, $proforma->converted_invoice_id);
        $this->assertSame($proforma->id, $invoice->proforma_id);
        $this->assertSame('draft', $invoice->status);
        $this->assertSame('800.00', $invoice->subtotal);
        $this->assertSame('880.00', $invoice->total);
        $this->assertCount(1, $invoice->lines);
    }

    public function test_a_proforma_can_be_converted_from_any_status(): void
    {
        $proforma = Proforma::factory()->status('draft')->withLine()->create();

        $this->actingAs($this->manager())
            ->post("/console/proformas/{$proforma->id}/convert")
            ->assertRedirect();

        $this->assertSame('converted', $proforma->fresh()->status);
        $this->assertSame(1, Invoice::count());
    }

    public function test_a_proforma_cannot_be_converted_twice(): void
    {
        $proforma = Proforma::factory()->status('accepted')->withLine()->create();
        $proforma->load('lines');

        $proforma->convertToInvoice();

        $this->actingAs($this->manager())
            ->post("/console/proformas/{$proforma->id}/convert")
            ->assertSessionHasErrors('status');

        $this->assertSame(1, Invoice::count());
    }

    public function test_the_pdf_streams_inline_and_downloads_on_request(): void
    {
        $invoice = Invoice::factory()->withLine()->create();
        $manager = $this->manager();

        $inline = $this->actingAs($manager)->get("/console/invoices/{$invoice->id}/pdf");
        $inline->assertOk();
        $this->assertSame('application/pdf', $inline->headers->get('content-type'));
        $this->assertStringContainsString('inline', $inline->headers->get('content-disposition'));

        $download = $this->actingAs($manager)->get("/console/invoices/{$invoice->id}/pdf?download=1");
        $this->assertStringContainsString('attachment', $download->headers->get('content-disposition'));
    }

    public function test_a_paid_invoice_has_a_receipt(): void
    {
        $invoice = Invoice::factory()->status('sent')->withLine('Work', '1', '1000')->create();
        $payment = Payment::factory()->create(['client_id' => $invoice->client_id, 'amount' => '1000']);
        $payment->applyAllocations([['invoice_id' => $invoice->id, 'amount' => '1000']]);

        $response = $this->actingAs($this->manager())->get("/console/invoices/{$invoice->id}/receipt");

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_an_unpaid_invoice_has_no_receipt(): void
    {
        $invoice = Invoice::factory()->status('sent')->withLine()->create();

        $this->actingAs($this->manager())
            ->get("/console/invoices/{$invoice->id}/receipt")
            ->assertNotFound();
    }

    public function test_the_api_converts_a_proforma(): void
    {
        $proforma = Proforma::factory()->status('sent')->withLine()->create();
        $proforma->load('lines');

        $token = $this->manager()->createToken('t', ['invoices.manage'])->plainTextToken;

        $this->withToken($token)
            ->postJson("/api/v1/proformas/{$proforma->id}/convert")
            ->assertCreated()
            ->assertJsonPath('data.status', 'draft');

        $this->assertSame('converted', $proforma->fresh()->status);
    }
}
