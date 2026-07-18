<?php

namespace App\Http\Requests\Accounts;

use App\Http\Requests\Concerns\ValidatesBusinessContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PaymentRequest extends FormRequest
{
    use ValidatesBusinessContext;

    public function authorize(): bool
    {
        return $this->user()?->can('payments.create') ?? false;
    }

    public function rules(): array
    {
        $shopId = session('active_shop_id');
        $financialYearId = session('active_financial_year_id');

        return [
            'party_id' => ['nullable', Rule::exists('parties', 'id')->where('shop_id', $shopId)],
            'commercial_document_id' => ['nullable', Rule::exists('commercial_documents', 'id')->where('shop_id', $shopId)->where('financial_year_id', $financialYearId)],
            'payment_method_id' => ['nullable', Rule::exists('reference_masters', 'id')->where('type', 'payment_method')],
            'type' => ['required', Rule::in(['cash_receipt', 'cash_payment', 'bank_receipt', 'bank_payment', 'supplier_payment', 'customer_collection', 'expense', 'income'])],
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function after(): array
    {
        return [fn (Validator $validator) => $this->validateBusinessContext($validator, 'payment_date')];
    }
}
