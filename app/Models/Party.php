<?php

namespace App\Models;

use App\Models\Concerns\ScopesActiveShop;
use Illuminate\Database\Eloquent\Model;

class Party extends Model
{
    use ScopesActiveShop;

    protected $guarded = [];
    protected function casts(): array { return ['credit_limit' => 'decimal:2', 'opening_balance' => 'decimal:2', 'is_active' => 'boolean']; }
    public function shop() { return $this->belongsTo(Shop::class); }
    public function group() { return $this->belongsTo(ReferenceMaster::class, 'group_id'); }
    public function addresses() { return $this->hasMany(PartyAddress::class); }
    public function documents() { return $this->hasMany(CommercialDocument::class); }
    public function payments() { return $this->hasMany(Payment::class); }
}
