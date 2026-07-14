<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartyAddress extends Model
{
    protected $guarded = [];
    protected function casts(): array { return ['is_default' => 'boolean']; }
    public function party() { return $this->belongsTo(Party::class); }
}
