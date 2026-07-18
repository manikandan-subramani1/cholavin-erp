<?php

namespace App\Http\Requests\Access;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGodownRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->has('shop_ids') && $this->filled('shop_id')) {
            $this->merge(['shop_ids' => [$this->input('shop_id')]]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()?->can('godowns.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'shop_ids' => ['nullable', 'array'],
            'shop_ids.*' => ['integer', 'distinct', 'exists:shops,id'],
            'name' => ['required', 'string', 'min:2', 'max:150'],
            'code' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('godowns')->ignore($this->route('godown'))],
            'address' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
