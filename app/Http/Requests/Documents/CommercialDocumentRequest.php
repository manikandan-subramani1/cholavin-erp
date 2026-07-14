<?php

namespace App\Http\Requests\Documents;

use App\Models\Godown;
use App\Models\Party;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CommercialDocumentRequest extends FormRequest
{
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
        $godownId = (int) session('active_godown_id');
        if ($module['stock_effect'] && (! $godownId || ! Godown::whereKey($godownId)->where('shop_id', $shopId)->where('is_active', true)->exists())) {
            abort(422, 'Select a valid active godown before creating this document.');
        }

        return [
            'party_id' => [Rule::requiredIf(filled($module['party_type'])), 'nullable', Rule::exists('parties', 'id')->where('shop_id', $shopId)->whereIn('type', [$module['party_type'], 'both'])->where('is_active', true)],
            'document_date' => ['required', 'date'], 'due_date' => ['nullable', 'date', 'after_or_equal:document_date'],
            'status' => ['required', Rule::in(['draft', 'posted'])], 'reference_number' => ['nullable', 'string', 'max:100'],
            'expense_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'round_off' => ['nullable', 'numeric', 'between:-10,10'], 'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'], 'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'], 'items.*.rate' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'], 'items.*.description' => ['nullable', 'string', 'max:255'],
            'items.*.unit' => ['nullable', 'string', 'max:40'], 'items.*.batch_number' => ['nullable', 'string', 'max:80'],
            'items.*.expiry_date' => ['nullable', 'date'],
        ];
    }
}
