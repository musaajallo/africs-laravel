<?php

namespace App\Http\Requests\Console;

use App\Support\Rbac;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApiTokenRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80'],
            'abilities' => ['required', 'array', 'min:1'],
            'abilities.*' => [Rule::in(Rbac::apiAbilities())],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user = $this->user();

            if ($user->hasRole(Rbac::ROLE_SUPER_ADMIN)) {
                return;
            }

            $granting = $this->input('abilities', []);
            $held = $user->getAllPermissions()->pluck('name')->all();

            if (array_diff($granting, $held) !== []) {
                $validator->errors()->add(
                    'abilities',
                    'You can only grant abilities you hold yourself.',
                );
            }
        });
    }

    /**
     * @return list<string>
     */
    public function abilities(): array
    {
        return array_values(array_unique($this->input('abilities', [])));
    }
}
