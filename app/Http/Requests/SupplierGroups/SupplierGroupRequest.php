<?php

namespace App\Http\Requests\SupplierGroups;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupplierGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        $action = $this->route('id') ? 'update' : 'create';

        return $this->user()?->can('supplier-groups.'.$action) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:190'],
            'code' => ['required', 'string', 'max:190'],
            'payment_days' => ['required', 'numeric', 'min:0'],
            'purchase_category' => ['required', 'string', 'max:190'],
            'status' => ['required', Rule::in([
                0 => 'Active',
                1 => 'Inactive',
            ])],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
