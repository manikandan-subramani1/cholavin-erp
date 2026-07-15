<?php

namespace App\Http\Requests\Brands;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        $action = $this->route('id') ? 'update' : 'create';

        return $this->user()?->can('brands.'.$action) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:190'],
            'code' => ['required', 'string', 'max:190'],
            'contact_person' => ['required', 'string', 'max:190'],
            'phone' => ['required', 'string', 'max:190'],
            'status' => ['required', Rule::in([
                0 => 'Active',
                1 => 'Inactive',
            ])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
