<?php

namespace App\Http\Requests\Console;

use App\Support\Rbac;
use App\Support\Settings;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExchangeRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(Rbac::PERM_EXCHANGE_RATES_MANAGE) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('currency')) {
            $this->merge(['currency' => strtoupper((string) $this->input('currency'))]);
        }
    }

    public function rules(): array
    {
        $foreign = array_values(array_diff(Settings::enabledCurrencies(), [Settings::baseCurrency()]));

        return [
            'currency' => ['required', 'string', 'size:3', Rule::in($foreign)],
            'rate' => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
            'rate_date' => ['required', 'date', 'before_or_equal:today'],
        ];
    }
}
