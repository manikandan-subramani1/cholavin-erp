<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferenceMaster extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['percentage' => 'decimal:4', 'metadata' => 'array', 'is_active' => 'boolean'];
    }

    public function parent() { return $this->belongsTo(self::class, 'parent_id'); }
    public function children() { return $this->hasMany(self::class, 'parent_id'); }
    public function shop() { return $this->belongsTo(Shop::class); }

    public function scopeOfType($query, string $type) { return $query->where('type', $type); }

    public function scopeForCurrentShop($query)
    {
        $shopId = (int) session('active_shop_id');

        return $query->where(fn ($query) => $query->whereNull('shop_id')->when($shopId, fn ($query) => $query->orWhere('shop_id', $shopId)));
    }
}
