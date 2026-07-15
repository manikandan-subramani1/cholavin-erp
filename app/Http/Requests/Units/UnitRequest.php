<?php

namespace App\Http\Requests\Units;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        $action = $this->route('id') ? 'update' : 'create';

        return $this->user()?->can('units.'.$action) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:190'],
            'code' => ['required', 'string', 'max:190'],
            'decimal_places' => ['required', 'numeric', 'min:0'],
            'base_unit' => ['required', 'string', 'max:190'],
            'conversion_value' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in([
                0 => 'Active',
                1 => 'Inactive',
            ])],
        ];
    }
}
