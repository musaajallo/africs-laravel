<?php

namespace App\Http\Requests\Console;

use App\Support\LeadChannels;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Create or edit the lead's own details (not its triage status/owner/notes).
 */
class LeadFormRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        // Referral fields only apply when the channel is a referral.
        if ($this->input('source') !== 'referral') {
            $this->merge([
                'referred_by_client_id' => null,
                'referral_source' => null,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'message' => ['nullable', 'string', 'max:5000'],
            'source' => ['required', Rule::in(LeadChannels::keys())],
            'referred_by_client_id' => ['nullable', Rule::exists('clients', 'id')],
            'referral_source' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function leadAttributes(): array
    {
        return [
            'name' => trim($this->input('name')),
            'email' => trim($this->input('email')),
            'company' => $this->input('company') ?: null,
            'phone' => $this->input('phone') ?: null,
            'message' => (string) $this->input('message'),
            'source' => $this->input('source'),
            'referred_by_client_id' => $this->input('referred_by_client_id') ?: null,
            'referral_source' => $this->input('referral_source') ?: null,
        ];
    }
}
