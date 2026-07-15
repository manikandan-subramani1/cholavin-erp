<?php

namespace App\Http\Requests\NumberSequences;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NumberSequenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $action = $this->route('id') ? 'update' : 'create';

        return $this->user()?->can('number-sequences.'.$action) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:190'],
            'module' => ['required', 'string', 'max:190'],
            'prefix' => ['required', 'string', 'max:190'],
            'current_number' => ['required', 'numeric', 'min:0'],
            'suffix' => ['nullable', 'string', 'max:190'],
            'status' => ['required', Rule::in([
                0 => 'Active',
                1 => 'Inactive',
            ])],
        ];
    }
}
