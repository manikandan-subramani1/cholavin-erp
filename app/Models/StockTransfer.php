<?php

namespace App\Models;

use App\Models\Concerns\ScopesActiveShop;
use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    use ScopesActiveShop;
    protected $guarded = [];
    protected function casts(): array { return ['transfer_date' => 'date']; }
    public function fromGodown() { return $this->belongsTo(Godown::class, 'from_godown_id'); }
    public function toGodown() { return $this->belongsTo(Godown::class, 'to_godown_id'); }
    public function items() { return $this->hasMany(StockTransferItem::class); }
}
