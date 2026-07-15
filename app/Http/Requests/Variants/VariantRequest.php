<?php

namespace App\Http\Requests\Variants;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        $action = $this->route('id') ? 'update' : 'create';

        return $this->user()?->can('variants.'.$action) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:190'],
            'variant_type' => ['required', Rule::in([
                0 => 'Packing',
                1 => 'Color',
                2 => 'Size',
                3 => 'Quality',
            ])],
            'code' => ['required', 'string', 'max:190'],
            'sort_order' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in([
                0 => 'Active',
                1 => 'Inactive',
            ])],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
