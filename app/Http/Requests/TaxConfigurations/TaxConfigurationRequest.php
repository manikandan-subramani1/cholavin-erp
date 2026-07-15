<?php

namespace App\Http\Requests\TaxConfigurations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaxConfigurationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $action = $this->route('id') ? 'update' : 'create';

        return $this->user()?->can('tax-configurations.'.$action) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:190'],
            'tax_type' => ['required', Rule::in([
                0 => 'GST',
                1 => 'IGST',
                2 => 'CESS',
                3 => 'None',
            ])],
            'registration_number' => ['required', 'string', 'max:190'],
            'effective_from' => ['required', 'date'],
            'default_rate' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in([
                0 => 'Active',
                1 => 'Inactive',
            ])],
        ];
    }
}
