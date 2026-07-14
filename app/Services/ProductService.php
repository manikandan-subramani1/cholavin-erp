<?php

namespace App\Services;

use App\Helpers\Helper;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductService
{
    public function __construct(private readonly InventoryService $inventory) {}

    public function create(array $data, ?UploadedFile $image): Product
    {
        return DB::transaction(function () use ($data, $image) {
            $openingStock = (float) ($data['opening_stock'] ?? 0);
            if ($openingStock > 0 && (! session('active_shop_id') || ! session('active_godown_id'))) {
                throw ValidationException::withMessages(['opening_stock' => 'Select an active shop and godown before entering opening stock.']);
            }
            $data['slug'] = $this->uniqueSlug($data['name']);
            if ($image) {
                $data['image'] = Helper::uploadImage($image, 'products')['name'];
            }
            $product = Product::create($data);
            if ($openingStock > 0) {
                $this->inventory->move(
                    (int) session('active_shop_id'),
                    (int) session('active_godown_id'),
                    $product,
                    $openingStock,
                    (float) ($product->purchase_price ?? 0),
                    'opening_stock',
                    today(),
                    'OPEN-'.$product->id,
                );
            }
            return $product->refresh();
        });
    }

    public function update(Product $product, array $data, ?UploadedFile $image): Product
    {
        return DB::transaction(function () use ($product, $data, $image) {
            unset($data['opening_stock']);
            $data['slug'] = $this->uniqueSlug($data['name'], $product->id);
            if ($image) {
                $oldImage = $product->image;
                $data['image'] = Helper::uploadImage($image, 'products')['name'];
                DB::afterCommit(fn () => Helper::unlinkImage($oldImage));
            }
            $product->update($data);
            return $product->refresh();
        });
    }

    public function delete(Product $product): void
    {
        if ($product->stockBalances()->where('quantity', '!=', 0)->exists() || $product->documentItems()->exists()) {
            throw ValidationException::withMessages(['product' => 'This product has stock or transaction history and cannot be deleted. Mark it inactive instead.']);
        }
        $image = $product->image;
        $product->delete();
        Helper::unlinkImage($image);
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'product';
        $slug = $base;
        $counter = 1;
        while (Product::query()->where('slug', $slug)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$counter++;
        }
        return $slug;
    }
}
