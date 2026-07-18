<?php

namespace App\Services\Access;

use App\Models\ActivityLog;
use App\Models\ContextSwitchLog;
use App\Models\Godown;
use App\Models\ReferenceMaster;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class BusinessContextService
{
    public function permittedFinancialYears(User $user): Collection
    {
        $query = $user->isSuperAdmin()
            ? ReferenceMaster::query()
            : $user->financialYears()->wherePivot('is_active', true);

        return $query
            ->where('reference_masters.type', 'financial_year')
            ->where('reference_masters.is_active', true)
            ->orderByDesc('reference_masters.code')
            ->get(['reference_masters.id', 'reference_masters.name', 'reference_masters.code', 'reference_masters.metadata']);
    }

    public function permittedShops(User $user): Collection
    {
        if ($user->isSuperAdmin()) {
            return Shop::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'code']);
        }

        return $user->shops()
            ->where('shops.is_active', true)
            ->wherePivot('is_active', true)
            ->orderBy('shops.name')
            ->get(['shops.id', 'shops.name', 'shops.code']);
    }

    public function permittedGodowns(User $user, ?int $shopId = null): Collection
    {
        $query = $user->isSuperAdmin()
            ? Godown::query()
            : $user->godowns()->wherePivot('is_active', true);

        return $query
            ->where('godowns.is_active', true)
            ->when($shopId, fn ($query) => $query->where(function ($query) use ($shopId): void {
                $query->where('godowns.shop_id', $shopId)
                    ->orWhereHas('shops', fn ($shops) => $shops->where('shops.id', $shopId));
            }))
            ->orderBy('godowns.name')
            ->get(['godowns.id', 'godowns.shop_id', 'godowns.name', 'godowns.code']);
    }

    public function permittedShopsForGodown(User $user, int $godownId): Collection
    {
        $godown = $this->permittedGodowns($user)->firstWhere('id', $godownId);
        if (! $godown) {
            return collect();
        }

        $shopIds = $godown->shops()->pluck('shops.id');
        if ($godown->shop_id) {
            $shopIds->push((int) $godown->shop_id);
        }

        return $this->permittedShops($user)
            ->whereIn('id', $shopIds->unique()->all())
            ->values();
    }

    public function resolveDefaultContext(User $user): array
    {
        $shops = $this->permittedShops($user);
        $financialYears = $this->permittedFinancialYears($user);
        $financialYearId = $this->resolveFinancialYearId($user, $financialYears);
        if ($user->isSuperAdmin() && session('all_shops_context') === true) {
            return [
                'shop_id' => null,
                'godown_id' => null,
                'financial_year_id' => $financialYearId,
                'shops' => $shops,
                'godowns' => $this->permittedGodowns($user),
                'financial_years' => $financialYears,
            ];
        }

        $requestedShopId = (int) session('active_shop_id');
        $shopId = $shops->contains('id', $requestedShopId)
            ? $requestedShopId
            : $this->defaultShopId($user, $shops);

        $allGodowns = $this->permittedGodowns($user);
        $godownsForShop = $shopId ? $this->permittedGodowns($user, $shopId) : $allGodowns;
        $requestedGodownId = (int) session('active_godown_id');
        $godownId = $godownsForShop->contains('id', $requestedGodownId)
            ? $requestedGodownId
            : $this->defaultGodownId($user, $godownsForShop);

        $shopsForGodown = $godownId
            ? $this->permittedShopsForGodown($user, $godownId)
            : $shops;

        if ($godownId && ! $shopsForGodown->contains('id', $shopId)) {
            $shopId = $this->defaultShopId($user, $shopsForGodown);
        }

        return [
            'shop_id' => $shopId ?: null,
            'godown_id' => $godownId ?: null,
            'financial_year_id' => $financialYearId,
            'shops' => $shopsForGodown,
            'godowns' => $allGodowns,
            'financial_years' => $financialYears,
        ];
    }

    public function synchronize(User $user): array
    {
        $context = $this->resolveDefaultContext($user);

        session()->put([
            'active_shop_id' => $context['shop_id'],
            'active_godown_id' => $context['godown_id'],
            'active_financial_year_id' => $context['financial_year_id'],
        ]);

        return $context;
    }

    public function switchShop(User $user, ?int $shopId): array
    {
        if (! $shopId) {
            abort_unless($user->isSuperAdmin(), 403, 'Only Super Admin can use the all-shops context.');
            $fromShopId = $this->activeShopId();
            $fromGodownId = $this->activeGodownId();
            session()->put(['active_shop_id' => null, 'active_godown_id' => null, 'all_shops_context' => true]);
            $this->recordSwitch($user, $fromShopId, null, $fromGodownId, null);

            return $this->responseData($user, null, null);
        }

        $shops = $this->permittedShops($user);
        if (! $shops->contains('id', $shopId)) {
            abort(403, 'You do not have access to the selected shop.');
        }

        $fromGodownId = $this->activeGodownId();
        if ($fromGodownId && ! $this->permittedShopsForGodown($user, $fromGodownId)->contains('id', $shopId)) {
            abort(403, 'The selected shop is not linked to the active godown.');
        }

        $fromShopId = $this->activeShopId();

        session()->put('active_shop_id', $shopId);
        session()->forget('all_shops_context');

        $this->recordSwitch($user, $fromShopId, $shopId, $fromGodownId, $fromGodownId);

        return $this->responseData($user, $shopId, $fromGodownId);
    }

    public function switchGodown(User $user, ?int $godownId): array
    {
        $godowns = $this->permittedGodowns($user);
        if ($godownId && ! $godowns->contains('id', $godownId)) {
            abort(403, 'You do not have access to the selected godown.');
        }

        if (! $godownId && $user->isSuperAdmin()) {
            $fromShopId = $this->activeShopId();
            $fromGodownId = $this->activeGodownId();
            session()->put(['active_shop_id' => null, 'active_godown_id' => null, 'all_shops_context' => true]);
            $this->recordSwitch($user, $fromShopId, null, $fromGodownId, null);

            return $this->responseData($user, null, null);
        }

        $shops = $godownId
            ? $this->permittedShopsForGodown($user, $godownId)
            : $this->permittedShops($user);

        if ($godownId && $shops->isEmpty()) {
            throw ValidationException::withMessages([
                'godown_id' => 'No permitted shop is linked to the selected godown.',
            ]);
        }

        $fromShopId = $this->activeShopId();
        $fromGodownId = $this->activeGodownId();
        $shopId = $shops->contains('id', $fromShopId)
            ? $fromShopId
            : $this->defaultShopId($user, $shops);

        session()->put([
            'active_shop_id' => $shopId,
            'active_godown_id' => $godownId,
        ]);
        session()->forget('all_shops_context');
        $this->recordSwitch($user, $fromShopId, $shopId, $fromGodownId, $godownId);

        return $this->responseData($user, $shopId, $godownId);
    }

    public function switchFinancialYear(User $user, int $financialYearId): array
    {
        $financialYears = $this->permittedFinancialYears($user);
        abort_unless($financialYears->contains('id', $financialYearId), 403, 'You do not have access to the selected financial year.');

        $fromId = $this->activeFinancialYearId();
        session()->put('active_financial_year_id', $financialYearId);

        if ($fromId !== $financialYearId) {
            ActivityLog::create([
                'user_id' => $user->id,
                'event' => 'financial-year.switched',
                'module' => 'financial-years',
                'action' => 'switch',
                'shop_id' => $this->activeShopId(),
                'godown_id' => $this->activeGodownId(),
                'financial_year_id' => $financialYearId,
                'old_values' => ['financial_year_id' => $fromId],
                'new_values' => ['financial_year_id' => $financialYearId],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }

        return $this->responseData($user, $this->activeShopId(), $this->activeGodownId());
    }

    public function activeShopId(): ?int
    {
        return session('active_shop_id') ? (int) session('active_shop_id') : null;
    }

    public function activeGodownId(): ?int
    {
        return session('active_godown_id') ? (int) session('active_godown_id') : null;
    }

    public function activeFinancialYearId(): ?int
    {
        return session('active_financial_year_id') ? (int) session('active_financial_year_id') : null;
    }

    private function resolveFinancialYearId(User $user, Collection $financialYears): ?int
    {
        $requestedId = $this->activeFinancialYearId();
        if ($requestedId && $financialYears->contains('id', $requestedId)) {
            return $requestedId;
        }

        if (! $user->isSuperAdmin()) {
            $defaultId = $user->financialYears()
                ->wherePivot('is_active', true)
                ->wherePivot('is_default', true)
                ->value('reference_masters.id');
            if ($defaultId && $financialYears->contains('id', (int) $defaultId)) {
                return (int) $defaultId;
            }
        }

        $today = now()->toDateString();
        $current = $financialYears->first(function (ReferenceMaster $year) use ($today): bool {
            $start = data_get($year->metadata, 'start_date');
            $end = data_get($year->metadata, 'end_date');

            return $start && $end && $start <= $today && $end >= $today;
        });

        return $current?->id ?? $financialYears->first()?->id;
    }

    private function defaultShopId(User $user, Collection $shops): ?int
    {
        if (! $user->isSuperAdmin()) {
            $defaultId = $user->shops()
                ->wherePivot('is_active', true)
                ->wherePivot('is_default', true)
                ->value('shops.id');

            if ($defaultId && $shops->contains('id', (int) $defaultId)) {
                return (int) $defaultId;
            }
        }

        return $shops->first()?->id;
    }

    private function defaultGodownId(User $user, Collection $godowns): ?int
    {
        if (! $user->isSuperAdmin()) {
            $defaultId = $user->godowns()
                ->wherePivot('is_active', true)
                ->wherePivot('is_default', true)
                ->value('godowns.id');

            if ($defaultId && $godowns->contains('id', (int) $defaultId)) {
                return (int) $defaultId;
            }
        }

        return $godowns->first()?->id;
    }

    private function responseData(User $user, ?int $shopId, ?int $godownId): array
    {
        $shops = $godownId
            ? $this->permittedShopsForGodown($user, $godownId)
            : $this->permittedShops($user);
        $godowns = $this->permittedGodowns($user);
        $financialYears = $this->permittedFinancialYears($user);

        return [
            'active_shop_id' => $shopId,
            'active_godown_id' => $godownId,
            'active_financial_year_id' => $this->activeFinancialYearId(),
            'shops' => $shops->map(fn (Shop $shop) => [
                'id' => $shop->id,
                'name' => $shop->name,
                'code' => $shop->code,
            ])->values(),
            'godowns' => $godowns->map(fn (Godown $godown) => [
                'id' => $godown->id,
                'name' => $godown->name,
                'code' => $godown->code,
            ])->values(),
            'financial_years' => $financialYears->map(fn (ReferenceMaster $year) => [
                'id' => $year->id,
                'name' => $year->name,
                'code' => $year->code,
            ])->values(),
            'reload' => true,
        ];
    }

    private function recordSwitch(User $user, ?int $fromShopId, ?int $toShopId, ?int $fromGodownId, ?int $toGodownId): void
    {
        if ($fromShopId === $toShopId && $fromGodownId === $toGodownId) {
            return;
        }

        ContextSwitchLog::create([
            'user_id' => $user->id,
            'from_shop_id' => $fromShopId,
            'to_shop_id' => $toShopId,
            'from_godown_id' => $fromGodownId,
            'to_godown_id' => $toGodownId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);

        ActivityLog::create([
            'user_id' => $user->id,
            'event' => $fromShopId !== $toShopId ? 'context.shop_switched' : 'context.godown_switched',
            'module' => 'access-control',
            'action' => 'switch',
            'shop_id' => $toShopId,
            'godown_id' => $toGodownId,
            'financial_year_id' => $this->activeFinancialYearId(),
            'method' => request()->method(),
            'route' => request()->route()?->getName(),
            'url' => request()->fullUrl(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => ['shop_id' => $fromShopId, 'godown_id' => $fromGodownId],
            'new_values' => ['shop_id' => $toShopId, 'godown_id' => $toGodownId],
            'created_at' => now(),
        ]);
    }
}
