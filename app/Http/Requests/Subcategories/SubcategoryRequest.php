<?php

namespace App\Http\Requests\Subcategories;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubcategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $action = $this->route('id') ? 'update' : 'create';

        return $this->user()?->can('subcategories.'.$action) ?? false;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', Rule::in([
                0 => 'Rice',
                1 => 'Pulses',
                2 => 'Oil',
                3 => 'Spices',
            ])],
            'name' => ['required', 'string', 'max:190'],
            'code' => ['required', 'string', 'max:190'],
            'display_order' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in([
                0 => 'Active',
                1 => 'Inactive',
            ])],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
