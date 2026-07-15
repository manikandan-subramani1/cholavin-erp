<?php

namespace App\Http\Requests\InvoiceTemplates;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvoiceTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $action = $this->route('id') ? 'update' : 'create';

        return $this->user()?->can('invoice-templates.'.$action) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:190'],
            'template_type' => ['required', Rule::in([
                0 => 'Sales',
                1 => 'Purchase',
                2 => 'POS',
                3 => 'Delivery',
            ])],
            'paper_size' => ['required', Rule::in([
                0 => 'A4',
                1 => 'A5',
                2 => 'Thermal 80mm',
                3 => 'Thermal 58mm',
            ])],
            'header_text' => ['nullable', 'string', 'max:190'],
            'footer_text' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in([
                0 => 'Active',
                1 => 'Inactive',
            ])],
        ];
    }
}
