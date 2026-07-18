<?php

namespace App\Http\Requests\Documents;

use App\Http\Requests\Concerns\ValidatesBusinessContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CommercialDocumentRequest extends FormRequest
{
    use ValidatesBusinessContext;

    public function authorize(): bool
    {
        $action = $this->route('document') ? 'update' : 'create';
        return $this->user()?->can($this->route('module').'.'.$action) ?? false;
    }

    public function rules(): array
    {
        $module = config('erp_modules.documents.'.$this->route('module'));
        abort_unless($module, 404);
        $shopId = (int) session('active_shop_id');

        return [
            'party_id' => [Rule::requiredIf(filled($module['party_type'])), 'nullable', Rule::exists('parties', 'id')->where('shop_id', $shopId)->whereIn('type', [$module['party_type'], 'both'])->where('is_active', true)],
            'document_date' => ['required', 'date'], 'due_date' => ['nullable', 'date', 'after_or_equal:document_date'],
            'status' => ['required', Rule::in(['draft', 'posted'])], 'reference_number' => ['nullable', 'string', 'max:100'],
            'expense_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'round_off' => ['nullable', 'numeric', 'between:-10,10'], 'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'], 'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0', 'decimal:0,3'], 'items.*.rate' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'], 'items.*.description' => ['nullable', 'string', 'max:255'],
            'items.*.unit' => ['nullable', 'string', 'max:40'], 'items.*.batch_number' => ['nullable', 'string', 'max:80'],
            'items.*.expiry_date' => ['nullable', 'date'],
        ];
    }

    public function after(): array
    {
        $module = config('erp_modules.documents.'.$this->route('module'));

        return [fn (Validator $validator) => $this->validateBusinessContext(
            $validator,
            'document_date',
            (bool) ($module['stock_effect'] ?? false),
        )];
    }
}



