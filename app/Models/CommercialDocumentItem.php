<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommercialDocumentItem extends Model
{
    protected $guarded = [];
    protected function casts(): array { return ['quantity' => 'decimal:3', 'rate' => 'decimal:2', 'discount_amount' => 'decimal:2', 'tax_rate' => 'decimal:4', 'tax_amount' => 'decimal:2', 'line_total' => 'decimal:2', 'expiry_date' => 'date']; }
    public function document() { return $this->belongsTo(CommercialDocument::class, 'commercial_document_id'); }
    public function product() { return $this->belongsTo(Product::class); }
}
