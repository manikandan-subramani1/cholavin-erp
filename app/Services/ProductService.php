<?php

namespace App\Services;

use App\Helpers\Helper;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductService
{
    public function create(array $data, ?UploadedFile $image): Product
    {
        return DB::transaction(function () use ($data, $image) {
            $data['slug'] = $this->uniqueSlug($data['name']);
            if ($image) {
                $data['image'] = Helper::uploadImage($image, 'products')['name'];
            }
            return Product::create($data)->refresh();
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
