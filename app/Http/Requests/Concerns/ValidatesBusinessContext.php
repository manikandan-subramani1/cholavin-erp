<?php

namespace App\Http\Requests\Concerns;

use App\Models\Godown;
use App\Models\ReferenceMaster;
use Illuminate\Validation\Validator;

trait ValidatesBusinessContext
{
    protected function validateBusinessContext(Validator $validator, ?string $dateField = null, bool $godownRequired = false): void
    {
        $shopId = session('active_shop_id') ? (int) session('active_shop_id') : null;
        $godownId = session('active_godown_id') ? (int) session('active_godown_id') : null;
        $financialYearId = session('active_financial_year_id') ? (int) session('active_financial_year_id') : null;

        if (! $shopId) {
            $validator->errors()->add('shop_id', 'Select a specific shop before saving this transaction.');
        }
        if (! $financialYearId) {
            $validator->errors()->add('financial_year_id', 'Select a financial year before saving this transaction.');
        }
        if ($godownRequired && ! $godownId) {
            $validator->errors()->add('godown_id', 'Select an inventory location before saving this transaction.');
        }

        if ($godownRequired && $shopId && $godownId) {
            $linked = Godown::query()->whereKey($godownId)->where('is_active', true)
                ->where(fn ($query) => $query->where('shop_id', $shopId)
                    ->orWhereHas('shops', fn ($shops) => $shops->where('shops.id', $shopId)))
                ->exists();
            if (! $linked) {
                $validator->errors()->add('godown_id', 'The inventory location is not linked to the active shop.');
            }
        }

        if ($financialYearId && $dateField && $this->filled($dateField)) {
            $year = ReferenceMaster::query()
                ->whereKey($financialYearId)
                ->where('type', 'financial_year')
                ->where('is_active', true)
                ->first();
            $date = $this->date($dateField)?->toDateString();
            $start = data_get($year?->metadata, 'start_date');
            $end = data_get($year?->metadata, 'end_date');
            if (! $year || ($start && $date < $start) || ($end && $date > $end)) {
                $validator->errors()->add($dateField, 'The transaction date must be within the active financial year.');
            }
        }
    }
}
