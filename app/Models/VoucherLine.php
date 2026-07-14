<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherLine extends Model
{
    protected $guarded = [];
    protected function casts(): array { return ['debit' => 'decimal:2', 'credit' => 'decimal:2']; }
    public function voucher() { return $this->belongsTo(Voucher::class); }
    public function account() { return $this->belongsTo(LedgerAccount::class, 'ledger_account_id'); }
    public function party() { return $this->belongsTo(Party::class); }
}
