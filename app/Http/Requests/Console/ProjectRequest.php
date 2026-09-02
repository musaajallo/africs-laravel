<?php

namespace App\Http\Requests\Console;

use App\Support\ProjectMeta;
use App\Support\Settings;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->filled('budget_currency')) {
            $this->merge(['budget_currency' => strtoupper((string) $this->input('budget_currency'))]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'client_id' => ['required', Rule::exists('clients', 'id')->withoutTrashed()],
            'service_line' => ['required', Rule::in(ProjectMeta::serviceLineKeys())],
            'status' => ['required', Rule::in(ProjectMeta::statusKeys())],
            'description' => ['nullable', 'string', 'max:5000'],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'budget_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'budget_currency' => ['nullable', 'required_with:budget_amount', Rule::in(Settings::enabledCurrencies())],
            'owner_id' => ['nullable', Rule::exists('users', 'id')],

            'members' => ['array'],
            'members.*.user_id' => ['required', 'distinct', Rule::exists('users', 'id')],
            'members.*.role' => ['nullable', 'string', 'max:80'],

            'tags' => ['array'],
            'tags.*' => ['string', 'max:50'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function projectAttributes(): array
    {
        return $this->safe()->except(['members', 'tags']);
    }

    /**
     * @return array<int, string|null> user_id => role
     */
    public function memberRoles(): array
    {
        return collect($this->input('members', []))
            ->filter(fn ($m) => filled($m['user_id'] ?? null))
            ->mapWithKeys(fn ($m) => [(int) $m['user_id'] => ['role' => $m['role'] ?? null]])
            ->all();
    }

    /**
     * @return list<string>
     */
    public function tagNames(): array
    {
        return collect($this->input('tags', []))
            ->map(fn ($t) => trim((string) $t))
            ->filter()
            ->values()
            ->all();
    }
}
