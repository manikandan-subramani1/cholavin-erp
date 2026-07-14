<?php

namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $product = $this->route('product');
        return $product
            ? ($this->user()?->can('update', $product) ?? false)
            : ($this->user()?->can('create', \App\Models\Product::class) ?? false);
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;
        $master = fn (string $type) => Rule::exists('reference_masters', 'id')->where('type', $type)->where('is_active', true);

        return [
            'name' => ['required', 'string', 'min:2', 'max:190'],
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($productId)],
            'barcode' => ['nullable', 'string', 'max:100', Rule::unique('products', 'barcode')->ignore($productId)],
            'category_id' => ['nullable', $master('category')],
            'brand_id' => ['nullable', $master('brand')],
            'variant_id' => ['nullable', $master('variant')],
            'grade_id' => ['nullable', $master('grade')],
            'unit_id' => ['nullable', $master('unit')],
            'tax_rate_id' => ['nullable', $master('tax_rate')],
            'hsn_sac_id' => ['nullable', $master('hsn_sac')],
            'sub_title' => ['nullable', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:10000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'opening_stock' => [$productId ? 'prohibited' : 'nullable', 'numeric', 'min:0'],
            'reorder_level' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'show_on_homepage' => ['nullable', 'boolean'],
        ];
    }
}
