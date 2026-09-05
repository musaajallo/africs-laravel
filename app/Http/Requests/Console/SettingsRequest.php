<?php

namespace App\Http\Requests\Console;

use App\Support\Settings;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettingsRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $enabled = collect($this->input('currency.enabled', []))
            ->map(fn ($c) => strtoupper((string) $c))
            ->all();

        $company = $this->input('company', []);
        $country = trim((string) ($company['country'] ?? ''));
        $company['country'] = $country === '' ? null : strtoupper($country);

        $methods = $this->input('billing.payment_methods');

        if (is_string($methods)) {
            $methods = preg_split('/\r\n|\r|\n/', $methods) ?: [];
        }

        $methods = collect(is_array($methods) ? $methods : [])
            ->map(fn ($m) => trim((string) $m))
            ->filter()
            ->values()
            ->all();

        // Keep the current list if the request didn't carry one.
        if ($methods === []) {
            $methods = Settings::paymentMethods();
        }

        $this->merge([
            'currency' => [
                'enabled' => $enabled,
                'base' => strtoupper((string) $this->input('currency.base')),
            ],
            'company' => $company,
            'billing' => array_merge((array) $this->input('billing', []), ['payment_methods' => $methods]),
        ]);
    }

    public function rules(): array
    {
        return [
            'company.name' => ['required', 'string', 'max:255'],
            'company.legal_name' => ['nullable', 'string', 'max:255'],
            'company.registration_number' => ['nullable', 'string', 'max:100'],
            'company.email' => ['nullable', 'email', 'max:255'],
            'company.phone' => ['nullable', 'string', 'max:50'],
            'company.tax_number' => ['nullable', 'string', 'max:100'],
            'company.address' => ['nullable', 'string', 'max:2000'],
            'company.city' => ['nullable', 'string', 'max:120'],
            'company.country' => ['nullable', 'string', 'size:2'],
            'company.bank_details' => ['nullable', 'string', 'max:2000'],

            'currency.enabled' => ['required', 'array', 'min:1'],
            'currency.enabled.*' => [Rule::in(Settings::SUPPORTED_CURRENCIES)],
            'currency.base' => ['required', 'string', 'size:3', Rule::in($this->input('currency.enabled', []))],

            'billing.tax_label' => ['nullable', 'string', 'max:20'],
            'billing.tax_rate' => ['nullable', 'numeric', 'between:0,100'],
            'billing.payment_terms_days' => ['nullable', 'integer', 'between:0,365'],
            'billing.payment_methods' => ['array', 'min:1'],
            'billing.payment_methods.*' => ['string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'currency.base.in' => 'The base currency must be one of the enabled currencies.',
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function groups(): array
    {
        $validated = $this->validated();

        return [
            'company' => $validated['company'] ?? [],
            'currency' => [
                'enabled' => array_values(array_unique($validated['currency']['enabled'])),
                'base' => $validated['currency']['base'],
            ],
            'billing' => [
                'tax_label' => $validated['billing']['tax_label'] ?? 'VAT',
                'tax_rate' => (float) ($validated['billing']['tax_rate'] ?? 0),
                'payment_terms_days' => (int) ($validated['billing']['payment_terms_days'] ?? 30),
                'payment_methods' => array_values(array_unique($validated['billing']['payment_methods'] ?? [])),
            ],
        ];
    }
}
