<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContextSwitchLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'from_shop_id',
        'to_shop_id',
        'from_godown_id',
        'to_godown_id',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
