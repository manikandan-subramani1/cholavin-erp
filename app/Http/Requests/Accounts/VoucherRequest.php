<?php

namespace App\Http\Requests\Accounts;

use App\Http\Requests\Concerns\ValidatesBusinessContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class VoucherRequest extends FormRequest
{
    use ValidatesBusinessContext;

    public function authorize(): bool
    {
        return $this->user()?->can('accounts.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['journal', 'contra', 'expense', 'income', 'opening'])],
            'voucher_date' => ['required', 'date'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'narration' => ['nullable', 'string', 'max:2000'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.ledger_account_id' => ['required', Rule::exists('ledger_accounts', 'id')->where(fn ($query) => $query->whereNull('shop_id')->orWhere('shop_id', session('active_shop_id')))],
            'lines.*.party_id' => ['nullable', Rule::exists('parties', 'id')->where('shop_id', session('active_shop_id'))],
            'lines.*.debit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.description' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $this->validateBusinessContext($validator, 'voucher_date');
            $debit = collect($this->input('lines', []))->sum(fn ($line) => (float) ($line['debit'] ?? 0));
            $credit = collect($this->input('lines', []))->sum(fn ($line) => (float) ($line['credit'] ?? 0));
            if ($debit <= 0 || abs($debit - $credit) > 0.009) {
                $validator->errors()->add('lines', 'Voucher debit and credit totals must be equal and greater than zero.');
            }
        }];
    }
}
