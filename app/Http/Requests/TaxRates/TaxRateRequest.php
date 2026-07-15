<?php

namespace App\Http\Requests\TaxRates;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaxRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $action = $this->route('id') ? 'update' : 'create';

        return $this->user()?->can('tax-rates.'.$action) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:190'],
            'rate' => ['required', 'numeric', 'min:0'],
            'tax_type' => ['required', Rule::in([
                0 => 'GST',
                1 => 'IGST',
                2 => 'CGST + SGST',
                3 => 'CESS',
            ])],
            'effective_from' => ['required', 'date'],
            'status' => ['required', Rule::in([
                0 => 'Active',
                1 => 'Inactive',
            ])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
