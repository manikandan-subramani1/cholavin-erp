<?php

namespace App\Http\Requests\PriceLists;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PriceListRequest extends FormRequest
{
    public function authorize(): bool
    {
        $action = $this->route('id') ? 'update' : 'create';

        return $this->user()?->can('price-lists.'.$action) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:190'],
            'customer_type' => ['required', Rule::in([
                0 => 'Retail',
                1 => 'Wholesale',
                2 => 'Distributor',
            ])],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['required', 'date'],
            'margin_percent' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in([
                0 => 'Active',
                1 => 'Inactive',
            ])],
        ];
    }
}
