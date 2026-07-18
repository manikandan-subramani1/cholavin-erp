<?php

namespace App\Http\Requests\Access;

use Illuminate\Foundation\Http\FormRequest;

class StoreShopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('shops.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:150'],
            'code' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:shops,code'],
            'address' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
