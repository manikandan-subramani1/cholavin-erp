<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Concerns\ValidatesBusinessContext;
use App\Models\Godown;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StockTransferRequest extends FormRequest
{
    use ValidatesBusinessContext;

    public function authorize(): bool
    {
        return $this->user()?->can('stock.transfer') ?? false;
    }

    public function rules(): array
    {
        return [
            'from_godown_id' => ['required', Rule::exists('godowns', 'id')->where('is_active', true)],
            'to_godown_id' => ['required', 'different:from_godown_id', Rule::exists('godowns', 'id')->where('is_active', true)],
            'transfer_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['draft', 'completed'])],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct', Rule::exists('products', 'id')->where('is_active', true)],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.batch_number' => ['nullable', 'string', 'max:80'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $this->validateBusinessContext($validator, 'transfer_date', true);
            $shopId = (int) session('active_shop_id');
            $count = Godown::query()->whereIn('id', [$this->integer('from_godown_id'), $this->integer('to_godown_id')])
                ->where(fn ($query) => $query->where('shop_id', $shopId)->orWhereHas('shops', fn ($shops) => $shops->where('shops.id', $shopId)))
                ->count();
            if ($count !== 2) $validator->errors()->add('from_godown_id', 'Both godowns must be linked to the active shop.');
        }];
    }
}
