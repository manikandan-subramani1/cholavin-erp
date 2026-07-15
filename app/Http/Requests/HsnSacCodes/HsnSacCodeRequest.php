<?php

namespace App\Http\Requests\HsnSacCodes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HsnSacCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $action = $this->route('id') ? 'update' : 'create';

        return $this->user()?->can('hsn-sac-codes.'.$action) ?? false;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:2000'],
            'tax_rate' => ['required', Rule::in([
                0 => '0%',
                1 => '5%',
                2 => '12%',
                3 => '18%',
                4 => '28%',
            ])],
            'type' => ['required', Rule::in([
                0 => 'Goods',
                1 => 'Service',
            ])],
            'status' => ['required', Rule::in([
                0 => 'Active',
                1 => 'Inactive',
            ])],
        ];
    }
}
