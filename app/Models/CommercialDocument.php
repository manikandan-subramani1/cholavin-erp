<?php

namespace App\Models;

use App\Models\Concerns\ScopesUserLocations;
use Illuminate\Database\Eloquent\Model;

class CommercialDocument extends Model
{
    use ScopesUserLocations;

    protected $guarded = [];
    protected function casts(): array
    {
        return ['document_date' => 'date', 'due_date' => 'date', 'subtotal' => 'decimal:2', 'discount_amount' => 'decimal:2', 'tax_amount' => 'decimal:2', 'expense_amount' => 'decimal:2', 'round_off' => 'decimal:2', 'total_amount' => 'decimal:2', 'paid_amount' => 'decimal:2', 'balance_amount' => 'decimal:2'];
    }
    public function shop() { return $this->belongsTo(Shop::class); }
    public function godown() { return $this->belongsTo(Godown::class); }
    public function party() { return $this->belongsTo(Party::class); }
    public function items() { return $this->hasMany(CommercialDocumentItem::class); }
    public function payments() { return $this->hasMany(Payment::class); }
    public function delivery() { return $this->hasOne(Delivery::class); }
}
