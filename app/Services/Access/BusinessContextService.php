<?php

namespace App\Services\Access;

use App\Models\ActivityLog;
use App\Models\ContextSwitchLog;
use App\Models\Godown;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class BusinessContextService
{
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

    public function permittedGodowns(User $user, int $shopId): Collection
    {
        $query = $user->isSuperAdmin()
            ? Godown::query()
            : $user->godowns()->wherePivot('is_active', true);

        return $query
            ->where('godowns.is_active', true)
            ->where('godowns.shop_id', $shopId)
            ->orderBy('godowns.name')
            ->get(['godowns.id', 'godowns.shop_id', 'godowns.name', 'godowns.code']);
    }

    public function resolveDefaultContext(User $user): array
    {
        $shops = $this->permittedShops($user);
        $requestedShopId = (int) session('active_shop_id');
        $shopId = $shops->contains('id', $requestedShopId)
            ? $requestedShopId
            : $this->defaultShopId($user, $shops);

        $godowns = $shopId ? $this->permittedGodowns($user, $shopId) : collect();
        $requestedGodownId = (int) session('active_godown_id');
        $godownId = $godowns->contains('id', $requestedGodownId)
            ? $requestedGodownId
            : $this->defaultGodownId($user, $godowns);

        return [
            'shop_id' => $shopId ?: null,
            'godown_id' => $godownId ?: null,
            'shops' => $shops,
            'godowns' => $godowns,
        ];
    }

    public function synchronize(User $user): array
    {
        $context = $this->resolveDefaultContext($user);

        session()->put([
            'active_shop_id' => $context['shop_id'],
            'active_godown_id' => $context['godown_id'],
        ]);

        return $context;
    }

    public function switchShop(User $user, int $shopId): array
    {
        $shops = $this->permittedShops($user);
        if (! $shops->contains('id', $shopId)) {
            abort(403, 'You do not have access to the selected shop.');
        }

        $fromShopId = $this->activeShopId();
        $fromGodownId = $this->activeGodownId();
        $godowns = $this->permittedGodowns($user, $shopId);
        $godownId = $godowns->contains('id', $fromGodownId)
            ? $fromGodownId
            : $this->defaultGodownId($user, $godowns);

        session()->put([
            'active_shop_id' => $shopId,
            'active_godown_id' => $godownId ?: null,
        ]);

        $this->recordSwitch($user, $fromShopId, $shopId, $fromGodownId, $godownId);

        return $this->responseData($shopId, $godownId, $godowns);
    }

    public function switchGodown(User $user, ?int $godownId): array
    {
        $shopId = $this->activeShopId();
        if (! $shopId) {
            throw ValidationException::withMessages(['shop_id' => 'Select an active shop first.']);
        }

        $godowns = $this->permittedGodowns($user, $shopId);
        if ($godownId && ! $godowns->contains('id', $godownId)) {
            abort(403, 'You do not have access to the selected godown.');
        }

        $fromGodownId = $this->activeGodownId();
        session()->put('active_godown_id', $godownId);
        $this->recordSwitch($user, $shopId, $shopId, $fromGodownId, $godownId);

        return $this->responseData($shopId, $godownId, $godowns);
    }

    public function activeShopId(): ?int
    {
        return session('active_shop_id') ? (int) session('active_shop_id') : null;
    }

    public function activeGodownId(): ?int
    {
        return session('active_godown_id') ? (int) session('active_godown_id') : null;
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

    private function responseData(int $shopId, ?int $godownId, Collection $godowns): array
    {
        return [
            'active_shop_id' => $shopId,
            'active_godown_id' => $godownId,
            'godowns' => $godowns->map(fn (Godown $godown) => [
                'id' => $godown->id,
                'name' => $godown->name,
                'code' => $godown->code,
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
