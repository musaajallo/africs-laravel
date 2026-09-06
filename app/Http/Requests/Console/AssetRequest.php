<?php

namespace App\Http\Requests\Console;

use App\Support\AssetMeta;
use App\Support\Settings;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AssetRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->filled('purchase_currency')) {
            $this->merge(['purchase_currency' => strtoupper((string) $this->input('purchase_currency'))]);
        }

        if (! $this->filled('depreciation_method')) {
            $this->merge(['depreciation_method' => 'none']);
        }

        foreach (['serial_number', 'asset_tag'] as $field) {
            if ($this->input($field) !== null && trim((string) $this->input($field)) === '') {
                $this->merge([$field => null]);
            }
        }
    }

    public function rules(): array
    {
        $asset = $this->route('asset');

        return [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(AssetMeta::categoryKeys())],
            'make' => ['nullable', 'string', 'max:120'],
            'model' => ['nullable', 'string', 'max:120'],
            'manufactured_on' => ['nullable', 'date', 'before_or_equal:today'],
            'serial_number' => ['nullable', 'string', 'max:120', Rule::unique('assets', 'serial_number')->ignore($asset)->withoutTrashed()],
            'asset_tag' => ['nullable', 'string', 'max:60', Rule::unique('assets', 'asset_tag')->ignore($asset)->withoutTrashed()],
            'status' => ['required', Rule::in(AssetMeta::statusKeys())],
            'condition' => ['nullable', Rule::in(AssetMeta::conditionKeys())],

            'purchased_on' => ['nullable', 'date', 'after_or_equal:manufactured_on'],
            'in_service_on' => ['nullable', 'date'],
            'purchase_cost' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'purchase_currency' => ['nullable', 'required_with:purchase_cost', Rule::in(Settings::enabledCurrencies())],
            'supplier' => ['nullable', 'string', 'max:160'],
            'warranty_until' => ['nullable', 'date'],

            'depreciation_method' => ['required', Rule::in(AssetMeta::depreciationMethodKeys())],
            'useful_life_months' => ['nullable', 'required_if:depreciation_method,straight_line', 'integer', 'min:1', 'max:600'],
            'depreciation_rate' => ['nullable', 'required_if:depreciation_method,reducing_balance', 'numeric', 'gt:0', 'max:100'],
            'salvage_value' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],

            'location' => ['nullable', 'string', 'max:160'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $cost = $this->input('purchase_cost');
            $salvage = $this->input('salvage_value');

            if ($cost !== null && $salvage !== null && (float) $salvage > (float) $cost) {
                $validator->errors()->add('salvage_value', 'The salvage value cannot exceed the purchase cost.');
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function assetAttributes(): array
    {
        return $this->safe()->all();
    }
}
