<?php

namespace App\Http\Requests\InvoiceSequences;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvoiceSequenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $action = $this->route('id') ? 'update' : 'create';

        return $this->user()?->can('invoice-sequences.'.$action) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:190'],
            'prefix' => ['required', 'string', 'max:190'],
            'next_number' => ['required', 'numeric', 'min:0'],
            'padding' => ['required', 'numeric', 'min:0'],
            'reset_period' => ['required', Rule::in([
                0 => 'Never',
                1 => 'Monthly',
                2 => 'Financial Year',
            ])],
            'status' => ['required', Rule::in([
                0 => 'Active',
                1 => 'Inactive',
            ])],
        ];
    }
}
