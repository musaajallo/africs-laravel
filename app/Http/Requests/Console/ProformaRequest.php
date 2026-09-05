<?php

namespace App\Http\Requests\Console;

use App\Models\Project;
use App\Support\ExchangeRates;
use App\Support\Settings;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ProformaRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $currency = strtoupper((string) $this->input('currency'));

        $merge = ['currency' => $currency];

        // If the client didn't supply an FX rate, seed it from the latest
        // recorded rate for the issue date (1 when the currency is the base).
        if (! $this->filled('fx_rate') && $currency !== '') {
            $merge['fx_rate'] = ExchangeRates::toBase(
                $currency,
                $this->date('issue_date') ?: now(),
            );
        }

        $this->merge($merge);
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', Rule::exists('clients', 'id')->withoutTrashed()],
            'project_id' => ['nullable', Rule::exists('projects', 'id')->withoutTrashed()],
            'currency' => ['required', 'string', 'size:3', Rule::in(Settings::enabledCurrencies())],
            'fx_rate' => ['required', 'numeric', 'gt:0', 'max:99999999.9999999999'],
            'issue_date' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'tax_label' => ['nullable', 'string', 'max:20'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'terms' => ['nullable', 'string', 'max:5000'],

            'lines' => ['required', 'array', 'min:1'],
            'lines.*.description' => ['required', 'string', 'max:500'],
            'lines.*.quantity' => ['required', 'numeric', 'gt:0', 'max:999999.999'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $projectId = $this->input('project_id');

            if ($projectId && ! Project::where('id', $projectId)
                ->where('client_id', $this->input('client_id'))
                ->exists()) {
                $validator->errors()->add('project_id', 'That project belongs to a different client.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'fx_rate.required' => 'No exchange rate is on file for this currency and date — enter one.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function documentAttributes(): array
    {
        return $this->safe()->only([
            'client_id', 'project_id', 'currency', 'fx_rate', 'issue_date',
            'valid_until', 'tax_label', 'tax_rate', 'notes', 'terms',
        ]);
    }

    /**
     * @return list<array{description: string, quantity: string, unit_price: string, position: int}>
     */
    public function lineRows(): array
    {
        return collect($this->input('lines', []))
            ->values()
            ->map(fn ($line, $i) => [
                'description' => trim((string) $line['description']),
                'quantity' => (string) $line['quantity'],
                'unit_price' => (string) $line['unit_price'],
                'position' => $i,
            ])
            ->all();
    }
}
