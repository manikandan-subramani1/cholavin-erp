<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransferItem extends Model
{
    protected $guarded = [];
    protected function casts(): array { return ['quantity' => 'decimal:3']; }
    public function transfer() { return $this->belongsTo(StockTransfer::class, 'stock_transfer_id'); }
    public function product() { return $this->belongsTo(Product::class); }
}
