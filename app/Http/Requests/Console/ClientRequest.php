<?php

namespace App\Http\Requests\Console;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Shared validation for creating and updating a client. Contacts are edited
 * inline as a `contacts` array on the same form.
 */
class ClientRequest extends FormRequest
{
    /** Currencies the business invoices in. Becomes Settings-driven later. */
    public const CURRENCIES = ['GMD', 'USD', 'EUR'];

    protected function prepareForValidation(): void
    {
        $this->merge(array_filter([
            'currency' => $this->filled('currency') ? strtoupper((string) $this->input('currency')) : null,
            'country' => $this->filled('country') ? strtoupper((string) $this->input('country')) : null,
        ], fn ($value) => $value !== null));
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['company', 'individual'])],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'url', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'currency' => ['nullable', Rule::in(self::CURRENCIES)],
            'billing_address' => ['nullable', 'string', 'max:2000'],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'size:2'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'owner_id' => ['nullable', Rule::exists('users', 'id')],

            'contacts' => ['array'],
            'contacts.*.id' => ['nullable', 'integer'],
            'contacts.*.name' => ['required', 'string', 'max:255'],
            'contacts.*.title' => ['nullable', 'string', 'max:255'],
            'contacts.*.email' => ['nullable', 'email', 'max:255'],
            'contacts.*.phone' => ['nullable', 'string', 'max:50'],
            'contacts.*.is_primary' => ['boolean'],
            'contacts.*.notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function clientAttributes(): array
    {
        return $this->safe()->except('contacts');
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function contactRows(): array
    {
        $rows = $this->input('contacts', []);
        $primaryTaken = false;

        return collect($rows)
            ->filter(fn ($row) => filled($row['name'] ?? null))
            ->map(function ($row) use (&$primaryTaken) {
                $isPrimary = ! $primaryTaken && (bool) ($row['is_primary'] ?? false);
                $primaryTaken = $primaryTaken || $isPrimary;

                return [
                    'id' => $row['id'] ?? null,
                    'name' => trim($row['name']),
                    'title' => $row['title'] ?? null,
                    'email' => $row['email'] ?? null,
                    'phone' => $row['phone'] ?? null,
                    'is_primary' => $isPrimary,
                    'notes' => $row['notes'] ?? null,
                ];
            })
            ->values()
            ->all();
    }
}
