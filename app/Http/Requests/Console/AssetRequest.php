<?php

namespace App\Http\Requests\Console;

use App\Support\AssetMeta;
use App\Support\Settings;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssetRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->filled('purchase_currency')) {
            $this->merge(['purchase_currency' => strtoupper((string) $this->input('purchase_currency'))]);
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
            'serial_number' => ['nullable', 'string', 'max:120', Rule::unique('assets', 'serial_number')->ignore($asset)->withoutTrashed()],
            'asset_tag' => ['nullable', 'string', 'max:60', Rule::unique('assets', 'asset_tag')->ignore($asset)->withoutTrashed()],
            'status' => ['required', Rule::in(AssetMeta::statusKeys())],
            'condition' => ['nullable', Rule::in(AssetMeta::conditionKeys())],

            'purchased_on' => ['nullable', 'date'],
            'purchase_cost' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'purchase_currency' => ['nullable', 'required_with:purchase_cost', Rule::in(Settings::enabledCurrencies())],
            'supplier' => ['nullable', 'string', 'max:160'],
            'warranty_until' => ['nullable', 'date'],

            'location' => ['nullable', 'string', 'max:160'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function assetAttributes(): array
    {
        return $this->safe()->all();
    }
}
