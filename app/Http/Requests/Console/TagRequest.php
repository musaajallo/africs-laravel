<?php

namespace App\Http\Requests\Console;

use App\Support\TagColors;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TagRequest extends FormRequest
{
    public function rules(): array
    {
        $tagId = $this->route('tag')?->id;

        return [
            'name' => [
                'required', 'string', 'max:50',
                Rule::unique('tags', 'name')->ignore($tagId),
            ],
            'color' => ['required', Rule::in(TagColors::KEYS)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function tagAttributes(): array
    {
        return [
            'name' => trim($this->string('name')->value()),
            'color' => $this->input('color'),
        ];
    }
}
