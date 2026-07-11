<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait ScopesUserLocations
{
    public function scopeAccessibleBy(Builder $query, User $user): Builder
    {
        $shopId = (int) session('active_shop_id');
        $godownId = (int) session('active_godown_id');

        if (! $shopId) {
            return $query->whereRaw('1 = 0');
        }

        $query->where($this->qualifyColumn('shop_id'), $shopId);

        return $godownId
            ? $query->where($this->qualifyColumn('godown_id'), $godownId)
            : $query;
    }
}
