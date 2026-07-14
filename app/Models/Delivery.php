<?php

namespace App\Models;

use App\Models\Concerns\ScopesActiveShop;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use ScopesActiveShop;
    protected $guarded = [];
    protected function casts(): array { return ['scheduled_at' => 'datetime', 'delivered_at' => 'datetime', 'cash_collected' => 'decimal:2', 'delivery_expense' => 'decimal:2']; }
    public function document() { return $this->belongsTo(CommercialDocument::class, 'commercial_document_id'); }
    public function vehicle() { return $this->belongsTo(ReferenceMaster::class, 'vehicle_id'); }
    public function driver() { return $this->belongsTo(ReferenceMaster::class, 'driver_id'); }
    public function route() { return $this->belongsTo(ReferenceMaster::class, 'route_id'); }
}
