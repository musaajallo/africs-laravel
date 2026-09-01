<?php

namespace App\Http\Requests\Console;

use App\Support\Rbac;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('users', 'username')->ignore($userId)],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'roles' => ['array'],
            'roles.*' => [Rule::exists('roles', 'name')],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $target = $this->route('user');
            $newRoles = $this->input('roles', []);

            if (in_array(Rbac::ROLE_SUPER_ADMIN, $newRoles, true)
                && ! $this->user()->hasRole(Rbac::ROLE_SUPER_ADMIN)) {
                $validator->errors()->add('roles', 'Only a super-admin can grant the super-admin role.');
            }

            // Don't let a user strip their own super-admin role (lock-out guard).
            if ($this->user()->is($target)
                && $target->hasRole(Rbac::ROLE_SUPER_ADMIN)
                && ! in_array(Rbac::ROLE_SUPER_ADMIN, $newRoles, true)) {
                $validator->errors()->add('roles', 'You cannot remove your own super-admin role.');
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function userAttributes(): array
    {
        $attributes = [
            'name' => $this->string('name')->trim()->value(),
            'username' => $this->string('username')->trim()->value(),
            'email' => $this->string('email')->trim()->lower()->value(),
        ];

        if ($this->filled('password')) {
            $attributes['password'] = $this->string('password')->value();
        }

        return $attributes;
    }

    public function roles(): array
    {
        return array_values(array_unique($this->input('roles', [])));
    }
}
