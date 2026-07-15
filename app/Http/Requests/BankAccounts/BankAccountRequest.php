<?php

namespace App\Http\Requests\BankAccounts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BankAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        $action = $this->route('id') ? 'update' : 'create';

        return $this->user()?->can('bank-accounts.'.$action) ?? false;
    }

    public function rules(): array
    {
        return [
            'bank_name' => ['required', 'string', 'max:190'],
            'account_name' => ['required', 'string', 'max:190'],
            'account_number' => ['required', 'string', 'max:190'],
            'ifsc_code' => ['required', 'string', 'max:190'],
            'branch' => ['required', 'string', 'max:190'],
            'opening_balance' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in([
                0 => 'Active',
                1 => 'Inactive',
            ])],
        ];
    }
}
