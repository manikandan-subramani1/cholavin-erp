<?php

namespace App\Http\Requests\Products;

use App\Http\Requests\Concerns\ValidatesBusinessContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class OpeningStockRequest extends FormRequest
{
    use ValidatesBusinessContext;

    public function authorize(): bool
    {
        return ($this->user()?->can('update', $this->route('product')) ?? false)
            && ($this->user()?->can('stock.update') ?? false);
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'numeric', 'gt:0'],
            'rate' => ['required', 'numeric', 'min:0'],
            'movement_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function after(): array
    {
        return [fn (Validator $validator) => $this->validateBusinessContext($validator, 'movement_date', true)];
    }
}
