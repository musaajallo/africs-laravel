<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ExchangeRate;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Proforma;
use App\Models\Project;
use App\Support\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Demo data for the finance modules: exchange rates, a handful of clients,
 * proformas in every state, invoices from conversions and direct, payments
 * (full and partial), and a spread of overdue balances so the receivables
 * screen has something to age. Safe to run more than once — it always adds
 * a fresh batch.
 */
class FinanceDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedExchangeRates();

        $clients = collect([
            ['name' => 'Banjul City Council', 'type' => 'government', 'currency' => 'GMD'],
            ['name' => 'Kairaba Hotel Group', 'type' => 'organisation', 'currency' => 'GMD'],
            ['name' => 'Atlantic Shipping Ltd', 'type' => 'organisation', 'currency' => 'USD'],
            ['name' => 'Senegambia Traders', 'type' => 'organisation', 'currency' => 'GMD'],
            ['name' => 'Gamtel Digital', 'type' => 'organisation', 'currency' => 'EUR'],
        ])->map(fn ($row) => Client::factory()->create([
            'name' => $row['name'],
            'type' => $row['type'],
            'category' => null,
            'currency' => $row['currency'],
            'country' => 'GM',
            'city' => 'Banjul',
            'billing_address' => "1 Independence Drive\nBanjul",
        ]));

        foreach ($clients as $client) {
            $project = Project::factory()->create([
                'client_id' => $client->id,
                'status' => 'active',
                'budget_currency' => $client->currency,
            ]);

            $currency = $client->currency;

            // --- Proformas -------------------------------------------------
            $this->makeProforma($client, $project, $currency, 'draft', ['-3 days']);
            $this->makeProforma($client, $project, $currency, 'sent', ['-12 days']);
            $accepted = $this->makeProforma($client, $project, $currency, 'accepted', ['-20 days']);

            // one accepted proforma becomes an invoice
            $converted = $accepted->fresh()->load('lines')->convertToInvoice();
            $converted->update(['status' => 'sent', 'issue_date' => now()->subDays(18)->toDateString()]);

            // --- Direct invoices, spread across the ageing buckets --------
            $paidInFull = $this->makeInvoice($client, $project, $currency, now()->subDays(40), now()->subDays(10));
            $partly = $this->makeInvoice($client, $project, $currency, now()->subDays(50), now()->subDays(20));
            $overdue30 = $this->makeInvoice($client, $project, $currency, now()->subDays(35), now()->subDays(15));
            $overdue60 = $this->makeInvoice($client, $project, $currency, now()->subDays(80), now()->subDays(45));
            $overdue90 = $this->makeInvoice($client, $project, $currency, now()->subDays(140), now()->subDays(95));
            $notDue = $this->makeInvoice($client, $project, $currency, now()->subDays(5), now()->addDays(20));

            $overdue60->update(['status' => 'overdue']);
            $overdue90->update(['status' => 'overdue']);

            // --- Payments ------------------------------------------------
            $this->pay($client, $currency, $paidInFull, $paidInFull->total, now()->subDays(6));
            $this->pay($client, $currency, $partly, $this->half($partly->total), now()->subDays(9));
            $this->pay($client, $currency, $converted, $this->half($converted->total), now()->subDays(4));
            // overdue30, overdue60, overdue90 and notDue are left outstanding
        }
    }

    private function seedExchangeRates(): void
    {
        foreach (['USD' => '70.5000000000', 'EUR' => '76.2500000000'] as $quote => $rate) {
            foreach ([120, 60, 20, 0] as $daysAgo) {
                ExchangeRate::updateOrCreate(
                    [
                        'base_currency' => 'GMD',
                        'quote_currency' => $quote,
                        'rate_date' => now()->subDays($daysAgo)->toDateString(),
                    ],
                    ['rate' => $rate, 'source' => 'manual'],
                );
            }
        }
    }

    private function makeProforma(Client $client, Project $project, string $currency, string $status, array $issue): Proforma
    {
        $proforma = new Proforma([
            'client_id' => $client->id,
            'project_id' => $project->id,
            'status' => $status,
            'currency' => $currency,
            'fx_rate' => $currency === 'GMD' ? '1' : ($currency === 'USD' ? '70.5' : '76.25'),
            'issue_date' => now()->modify($issue[0])->toDateString(),
            'valid_until' => now()->modify($issue[0])->addDays(30)->toDateString(),
            'tax_label' => 'VAT',
            'tax_rate' => 15,
        ]);
        $proforma->number = Sequence::next('proforma', 'PRO');
        $proforma->save();

        $proforma->lines()->createMany([
            ['description' => 'Consulting — discovery phase', 'quantity' => '3', 'unit_price' => $this->unit($currency, 1), 'position' => 0],
            ['description' => 'Implementation', 'quantity' => '1', 'unit_price' => $this->unit($currency, 4), 'position' => 1],
        ]);
        $proforma->load('lines');
        $proforma->saveWithTotals();

        return $proforma;
    }

    private function makeInvoice(Client $client, Project $project, string $currency, Carbon $issue, Carbon $due): Invoice
    {
        $invoice = new Invoice([
            'client_id' => $client->id,
            'project_id' => $project->id,
            'status' => 'sent',
            'currency' => $currency,
            'fx_rate' => $currency === 'GMD' ? '1' : ($currency === 'USD' ? '70.5' : '76.25'),
            'issue_date' => $issue->toDateString(),
            'due_date' => $due->toDateString(),
            'tax_label' => 'VAT',
            'tax_rate' => 15,
        ]);
        $invoice->number = Sequence::next('invoice', 'INV');
        $invoice->save();

        $invoice->lines()->createMany([
            ['description' => 'Monthly retainer', 'quantity' => '1', 'unit_price' => $this->unit($currency, 3), 'position' => 0],
            ['description' => 'Additional support hours', 'quantity' => '6', 'unit_price' => $this->unit($currency, 1, 0.4), 'position' => 1],
        ]);
        $invoice->load('lines');
        $invoice->saveWithTotals();

        return $invoice->fresh();
    }

    private function pay(Client $client, string $currency, Invoice $invoice, string $amount, Carbon $paidOn): void
    {
        $payment = new Payment([
            'client_id' => $client->id,
            'currency' => $currency,
            'fx_rate' => $currency === 'GMD' ? '1' : ($currency === 'USD' ? '70.5' : '76.25'),
            'amount' => $amount,
            'method' => fake()->randomElement(['Bank transfer', 'Cash', 'Mobile money']),
            'reference' => fake()->bothify('TRX-####??'),
            'paid_on' => $paidOn->toDateString(),
        ]);
        $payment->number = Sequence::next('receipt', 'RCT');
        $payment->save();

        $payment->applyAllocations([['invoice_id' => $invoice->id, 'amount' => $amount]]);
    }

    private function unit(string $currency, float $factor, float $extra = 0): string
    {
        $base = match ($currency) {
            'USD' => 200,
            'EUR' => 180,
            default => 12000,
        };

        return number_format($base * ($factor + $extra), 2, '.', '');
    }

    private function half(string $amount): string
    {
        return number_format(((float) $amount) / 2, 2, '.', '');
    }
}
