<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'number' => 'INV-'.fake()->unique()->numberBetween(1000, 9999999),
            'client_id' => Client::factory(),
            'status' => 'draft',
            'currency' => 'GMD',
            'fx_rate' => 1,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'tax_label' => 'VAT',
            'tax_rate' => 0,
        ];
    }

    public function status(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }

    public function withLine(string $description = 'Consulting', string $qty = '1', string $unitPrice = '1000'): static
    {
        return $this->afterCreating(function (Invoice $invoice) use ($description, $qty, $unitPrice) {
            $invoice->lines()->create([
                'description' => $description,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'position' => $invoice->lines()->count(),
            ]);
            $invoice->load('lines');
            $invoice->saveWithTotals();
        });
    }
}
