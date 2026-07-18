<?php

namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;

class UploadProductImageRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('update', $this->route('product')) ?? false; }
    public function rules(): array
    {
        return [
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'alt_text' => ['nullable', 'string', 'max:190'],
            'is_primary' => ['nullable', 'boolean'],
        ];
    }
}
