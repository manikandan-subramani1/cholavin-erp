<?php

namespace App\Models;

use App\Models\Concerns\ScopesUserLocations;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use ScopesUserLocations;
    protected $guarded = [];
    protected function casts(): array { return ['movement_date' => 'date', 'expiry_date' => 'date', 'quantity' => 'decimal:3', 'rate' => 'decimal:4', 'value' => 'decimal:2']; }
    public function product() { return $this->belongsTo(Product::class); }
    public function document() { return $this->belongsTo(CommercialDocument::class, 'commercial_document_id'); }
}
