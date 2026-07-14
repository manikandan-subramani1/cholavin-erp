<?php

namespace App\Models;

use App\Models\Concerns\ScopesUserLocations;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use ScopesUserLocations;
    protected $guarded = [];
    protected function casts(): array { return ['payment_date' => 'date', 'amount' => 'decimal:2']; }
    public function party() { return $this->belongsTo(Party::class); }
    public function document() { return $this->belongsTo(CommercialDocument::class, 'commercial_document_id'); }
    public function method() { return $this->belongsTo(ReferenceMaster::class, 'payment_method_id'); }
}
