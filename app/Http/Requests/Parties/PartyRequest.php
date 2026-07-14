<?php

namespace App\Http\Requests\Parties;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PartyRequest extends FormRequest
{
    public function authorize(): bool
    {
        $module = $this->route('type');
        $action = $this->route('party') ? 'update' : 'create';

        return in_array($module, ['customers', 'suppliers'], true) && ($this->user()?->can($module.'.'.$action) ?? false);
    }

    public function rules(): array
    {
        $party = $this->route('party');
        $shopId = session('active_shop_id');
        $groupType = $this->route('type') === 'customers' ? 'customer_group' : 'supplier_group';

        return [
            'name' => ['required', 'string', 'min:2', 'max:190'],
            'code' => ['required', 'string', 'max:60', Rule::unique('parties')->where('shop_id', $shopId)->ignore($party)],
            'group_id' => ['nullable', Rule::exists('reference_masters', 'id')->where('type', $groupType)],
            'mobile' => ['nullable', 'string', 'min:7', 'max:30'],
            'email' => ['nullable', 'email', 'max:190'],
            'gstin' => ['nullable', 'string', 'max:30'],
            'pan' => ['nullable', 'string', 'max:20'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'balance_type' => ['required', Rule::in(['receivable', 'payable'])],
            'address' => ['nullable', 'string', 'max:1000'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
