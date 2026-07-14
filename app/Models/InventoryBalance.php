<?php

namespace App\Models;

use App\Models\Concerns\ScopesUserLocations;
use Illuminate\Database\Eloquent\Model;

class InventoryBalance extends Model
{
    use ScopesUserLocations;
    protected $guarded = [];
    protected function casts(): array { return ['expiry_date' => 'date', 'quantity' => 'decimal:3', 'average_cost' => 'decimal:4']; }
    public function product() { return $this->belongsTo(Product::class); }
    public function shop() { return $this->belongsTo(Shop::class); }
    public function godown() { return $this->belongsTo(Godown::class); }
}
