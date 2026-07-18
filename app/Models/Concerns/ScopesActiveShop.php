<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait ScopesActiveShop
{
    public function scopeForActiveShop(Builder $query): Builder
    {
        $shopId = (int) session('active_shop_id');

        if (! $shopId && auth()->user()?->isSuperAdmin()) {
            return $query;
        }

        return $shopId
            ? $query->where($this->qualifyColumn('shop_id'), $shopId)
            : $query->whereRaw('1 = 0');
    }
}
