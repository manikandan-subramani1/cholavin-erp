<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPriceHistory extends Model
{
    protected $guarded = [];
    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2', 'sale_price' => 'decimal:2',
            'wholesale_price' => 'decimal:2', 'retail_price' => 'decimal:2',
        ];
    }
    public function product() { return $this->belongsTo(Product::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
