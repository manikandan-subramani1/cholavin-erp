<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Party;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LookupController extends Controller
{
    public function products(Request $request): JsonResponse
    {
        $search = $request->string('search')->trim()->toString();
        $products = Product::query()->with('taxRate:id,percentage')->where('is_active', true)
            ->when($search, fn ($query) => $query->where(fn ($query) => $query->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%")->orWhere('barcode', $search)))
            ->orderBy('name')->limit(100)->get(['id', 'name', 'sku', 'unit', 'sale_price', 'purchase_price', 'price', 'tax_rate_id'])
            ->map(fn (Product $product) => ['id' => $product->id, 'text' => $product->name.($product->sku ? ' ('.$product->sku.')' : ''), 'sale_price' => (float) ($product->sale_price ?: $product->price), 'purchase_price' => (float) $product->purchase_price, 'unit' => $product->unit, 'tax_rate' => (float) ($product->taxRate?->percentage ?? 0)]);
        return ResponseHelper::success('Products loaded.', $products);
    }

    public function parties(Request $request): JsonResponse
    {
        $type = $request->string('type')->toString();
        abort_unless(in_array($type, ['customer', 'supplier'], true), 422);
        $search = $request->string('search')->trim()->toString();
        $parties = Party::query()->forActiveShop()->where('is_active', true)->whereIn('type', [$type, 'both'])
            ->when($search, fn ($query) => $query->where(fn ($query) => $query->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")->orWhere('mobile', 'like', "%{$search}%")))
            ->orderBy('name')->limit(100)->get(['id', 'name', 'code'])->map(fn (Party $party) => ['id' => $party->id, 'text' => $party->name.' ('.$party->code.')']);
        return ResponseHelper::success('Parties loaded.', $parties);
    }
}
