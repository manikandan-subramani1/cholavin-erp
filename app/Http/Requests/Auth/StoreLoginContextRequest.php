<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoginContextRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'godown_id' => ['nullable', 'integer', 'exists:godowns,id'],
            'shop_id' => ['nullable', 'integer', 'exists:shops,id'],
            'financial_year_id' => ['nullable', 'integer', 'exists:reference_masters,id'],
        ];
    }
}
