<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'sub_title',
        'description',
        'image',
        'price',
        'unit',
        'is_active',
        'show_on_homepage',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'show_on_homepage' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            if (! $product->slug) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function imageUrl(): string
    {
        return $this->image ? asset($this->image) : asset('frontend/assets/img/products/1.png');
    }
}
