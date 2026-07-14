<?php

namespace App\Models;

use App\Models\Concerns\ScopesActiveShop;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use ScopesActiveShop;
    protected $guarded = [];
    protected function casts(): array { return ['voucher_date' => 'date', 'total_debit' => 'decimal:2', 'total_credit' => 'decimal:2']; }
    public function lines() { return $this->hasMany(VoucherLine::class); }
    public function sourceDocument() { return $this->belongsTo(CommercialDocument::class, 'source_id')->where('source_type', 'commercial_document'); }
}
