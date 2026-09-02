<?php

namespace App\Http\Requests\Console;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_primary' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function contactAttributes(): array
    {
        return [
            'name' => trim($this->input('name')),
            'title' => $this->input('title'),
            'email' => $this->input('email'),
            'phone' => $this->input('phone'),
            'is_primary' => $this->boolean('is_primary'),
            'notes' => $this->input('notes'),
        ];
    }
}
