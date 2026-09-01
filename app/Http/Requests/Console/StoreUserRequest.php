<?php

namespace App\Http\Requests\Console;

use App\Support\Rbac;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9._-]+$/', 'unique:users,username'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'roles' => ['array'],
            'roles.*' => [Rule::exists('roles', 'name')],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (in_array(Rbac::ROLE_SUPER_ADMIN, $this->input('roles', []), true)
                && ! $this->user()->hasRole(Rbac::ROLE_SUPER_ADMIN)) {
                $validator->errors()->add('roles', 'Only a super-admin can grant the super-admin role.');
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function userAttributes(): array
    {
        return [
            'name' => $this->string('name')->trim()->value(),
            'username' => $this->string('username')->trim()->value(),
            'email' => $this->string('email')->trim()->lower()->value(),
            'password' => $this->string('password')->value(),
        ];
    }

    public function roles(): array
    {
        return array_values(array_unique($this->input('roles', [])));
    }
}
