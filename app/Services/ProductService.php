<?php

namespace App\Services;

use App\Helpers\Helper;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductPriceHistory;
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

    public function updatePrices(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $prices = collect($data)->only(['purchase_price', 'sale_price', 'wholesale_price', 'retail_price'])->all();
            $product->update($prices);
            ProductPriceHistory::create($prices + [
                'product_id' => $product->id,
                'reason' => $data['reason'] ?? null,
                'created_by' => auth()->id(),
            ]);

            return $product->refresh();
        });
    }

    public function uploadGalleryImage(Product $product, UploadedFile $image, array $data): ProductImage
    {
        return DB::transaction(function () use ($product, $image, $data) {
            $uploaded = Helper::uploadImage($image, 'products');
            $primary = (bool) ($data['is_primary'] ?? false) || ! $product->images()->exists();
            if ($primary) {
                $product->images()->update(['is_primary' => false]);
                $product->update(['image' => $uploaded['name']]);
            }

            return $product->images()->create([
                'path' => $uploaded['name'],
                'alt_text' => $data['alt_text'] ?? $product->name,
                'is_primary' => $primary,
                'sort_order' => (int) $product->images()->max('sort_order') + 1,
                'created_by' => auth()->id(),
            ]);
        });
    }

    public function generateBarcode(Product $product): Product
    {
        if ($product->barcode) return $product;
        $base = str_pad((string) $product->id, 12, '0', STR_PAD_LEFT);
        $sum = 0;
        foreach (str_split($base) as $index => $digit) $sum += (int) $digit * ($index % 2 === 0 ? 1 : 3);
        $barcode = $base.((10 - ($sum % 10)) % 10);
        $product->update(['barcode' => $barcode]);
        return $product->refresh();
    }

    public function postOpeningStock(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $shopId = (int) session('active_shop_id');
            $godownId = (int) session('active_godown_id');
            if (! $shopId || ! $godownId) {
                throw ValidationException::withMessages(['quantity' => 'Select an active shop and godown before posting opening stock.']);
            }
            if ($product->stockMovements()->where('shop_id', $shopId)->where('godown_id', $godownId)->where('type', 'opening_stock')->exists()) {
                throw ValidationException::withMessages(['quantity' => 'Opening stock has already been posted for this item and location. Use stock adjustment instead.']);
            }

            $movement = $this->inventory->move(
                $shopId, $godownId, $product, (float) $data['quantity'], (float) $data['rate'],
                'opening_stock', $data['movement_date'], 'OPEN-'.$product->id.'-'.now()->format('YmdHis')
            );
            if (! empty($data['notes'])) $movement->update(['notes' => $data['notes']]);
            $product->update(['opening_stock' => $data['quantity']]);
            return $product->refresh();
        });
    }

    public function duplicate(Product $product): Product
    {
        return DB::transaction(function () use ($product) {
            $copy = $product->replicate(['slug', 'sku', 'barcode', 'image', 'opening_stock']);
            $copy->name = $product->name.' Copy';
            $copy->slug = $this->uniqueSlug($copy->name);
            $copy->sku = null;
            $copy->barcode = null;
            $copy->image = null;
            $copy->opening_stock = 0;
            $copy->is_active = false;
            $copy->save();
            return $copy;
        });
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
