<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'event', 'module', 'action', 'auditable_type', 'auditable_id',
        'shop_id', 'godown_id', 'financial_year_id', 'method', 'route', 'url', 'ip_address',
        'user_agent', 'old_values', 'new_values', 'properties', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
