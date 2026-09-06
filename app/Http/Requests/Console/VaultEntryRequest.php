<?php

namespace App\Http\Requests\Console;

use App\Support\Rbac;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VaultEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(Rbac::PERM_VAULT_MANAGE) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'folder_id' => ['nullable', Rule::exists('vault_folders', 'id')],
            'related_subscription_id' => ['nullable', 'integer'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:5000'],
            'url' => ['nullable', 'string', 'max:2048'],
            'notes' => ['nullable', 'string', 'max:20000'],
            'totp_secret' => ['nullable', 'string', 'max:512'],

            'custom' => ['array', 'max:30'],
            'custom.*.label' => ['required_with:custom.*.value', 'nullable', 'string', 'max:120'],
            'custom.*.value' => ['nullable', 'string', 'max:5000'],
            'custom.*.secret' => ['boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function entryAttributes(): array
    {
        $data = $this->safe()->except('custom');

        $data['custom_fields'] = collect($this->input('custom', []))
            ->map(fn ($f) => [
                'label' => trim((string) ($f['label'] ?? '')),
                'value' => (string) ($f['value'] ?? ''),
                'secret' => filter_var($f['secret'] ?? false, FILTER_VALIDATE_BOOL),
            ])
            ->filter(fn ($f) => $f['label'] !== '')
            ->values()
            ->all();

        return $data;
    }
}
