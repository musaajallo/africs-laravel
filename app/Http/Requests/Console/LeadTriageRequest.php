<?php

namespace App\Http\Requests\Console;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeadTriageRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // "converted" is set by the convert action, not chosen here.
            'status' => ['required', Rule::in(['new', 'contacted', 'qualified', 'lost'])],
            'owner_id' => ['nullable', Rule::exists('users', 'id')],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function triageAttributes(): array
    {
        return [
            'status' => $this->input('status'),
            'owner_id' => $this->input('owner_id') ?: null,
            'notes' => $this->input('notes'),
        ];
    }
}
