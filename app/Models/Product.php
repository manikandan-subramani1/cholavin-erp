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
        'sku',
        'barcode',
        'category_id',
        'brand_id',
        'variant_id',
        'grade_id',
        'unit_id',
        'tax_rate_id',
        'hsn_sac_id',
        'sub_title',
        'description',
        'image',
        'price',
        'purchase_price',
        'sale_price',
        'opening_stock',
        'reorder_level',
        'unit',
        'is_active',
        'show_on_homepage',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'opening_stock' => 'decimal:3',
        'reorder_level' => 'decimal:3',
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

    public function category() { return $this->belongsTo(ReferenceMaster::class, 'category_id'); }
    public function brand() { return $this->belongsTo(ReferenceMaster::class, 'brand_id'); }
    public function variant() { return $this->belongsTo(ReferenceMaster::class, 'variant_id'); }
    public function grade() { return $this->belongsTo(ReferenceMaster::class, 'grade_id'); }
    public function unitMaster() { return $this->belongsTo(ReferenceMaster::class, 'unit_id'); }
    public function taxRate() { return $this->belongsTo(ReferenceMaster::class, 'tax_rate_id'); }
    public function stockBalances() { return $this->hasMany(InventoryBalance::class); }
    public function documentItems() { return $this->hasMany(CommercialDocumentItem::class); }
}
