<?php

namespace App\Http\Requests\Console;

use App\Models\Invoice;
use App\Support\ExchangeRates;
use App\Support\Money;
use App\Support\Settings;
use Brick\Math\BigDecimal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PaymentRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $currency = strtoupper((string) $this->input('currency'));
        $merge = ['currency' => $currency];

        if (! $this->filled('fx_rate') && $currency !== '') {
            $merge['fx_rate'] = ExchangeRates::toBase($currency, $this->date('paid_on') ?: now());
        }

        $this->merge($merge);
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', Rule::exists('clients', 'id')->withoutTrashed()],
            'currency' => ['required', 'string', 'size:3', Rule::in(Settings::enabledCurrencies())],
            'fx_rate' => ['required', 'numeric', 'gt:0', 'max:99999999.9999999999'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:999999999999.99'],
            'method' => ['required', 'string', Rule::in(Settings::paymentMethods())],
            'reference' => ['nullable', 'string', 'max:120'],
            'paid_on' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:5000'],

            'allocations' => ['array'],
            'allocations.*.invoice_id' => ['required', 'distinct', Rule::exists('invoices', 'id')->withoutTrashed()],
            'allocations.*.amount' => ['required', 'numeric', 'gt:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $allocations = $this->input('allocations', []);
            $currency = $this->input('currency');
            $clientId = (int) $this->input('client_id');
            $paymentId = $this->route('payment')?->id;

            $sum = BigDecimal::zero();

            foreach ($allocations as $i => $row) {
                $sum = $sum->plus($row['amount'] ?? 0);

                $invoice = Invoice::find($row['invoice_id'] ?? null);

                if (! $invoice) {
                    continue;
                }

                if ($invoice->client_id !== $clientId) {
                    $validator->errors()->add("allocations.$i.invoice_id", 'That invoice belongs to a different client.');
                }

                if ($invoice->currency !== $currency) {
                    $validator->errors()->add("allocations.$i.invoice_id", "That invoice is in {$invoice->currency}, not {$currency}.");
                }

                if (in_array($invoice->status, ['draft', 'void'], true)) {
                    $validator->errors()->add("allocations.$i.invoice_id", 'That invoice cannot take a payment yet.');
                }

                // Allowed = current balance plus whatever this payment already
                // put on the invoice (which is being replaced).
                $alreadyHere = $paymentId
                    ? (string) $invoice->allocations()->where('payment_id', $paymentId)->sum('amount')
                    : '0';
                $allowed = Money::sum([$invoice->balance(), $alreadyHere], $currency);

                if (BigDecimal::of($row['amount'] ?? 0)->isGreaterThan($allowed)) {
                    $validator->errors()->add("allocations.$i.amount", "More than the outstanding {$allowed} on {$invoice->number}.");
                }
            }

            if ($this->filled('amount') && $sum->isGreaterThan($this->input('amount'))) {
                $validator->errors()->add('allocations', 'Allocations add up to more than the payment amount.');
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function paymentAttributes(): array
    {
        return $this->safe()->only([
            'client_id', 'currency', 'fx_rate', 'amount', 'method', 'reference', 'paid_on', 'notes',
        ]);
    }

    /**
     * @return list<array{invoice_id: int, amount: string}>
     */
    public function allocationRows(): array
    {
        return collect($this->input('allocations', []))
            ->map(fn ($row) => [
                'invoice_id' => (int) $row['invoice_id'],
                'amount' => (string) $row['amount'],
            ])
            ->all();
    }
}
