<?php

namespace App\Http\Middleware;

use App\Models\Godown;
use App\Models\Shop;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SyncUserAccessContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $user->load(['role.permissions', 'permissions']);

        $shops = $user->isSuperAdmin()
            ? Shop::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'code'])
            : $user->shops()->where('is_active', true)->orderBy('name')->get(['shops.id', 'name', 'code']);

        $godowns = $user->isSuperAdmin()
            ? Godown::query()->where('is_active', true)->orderBy('name')->get(['id', 'shop_id', 'name', 'code'])
            : $user->godowns()->where('is_active', true)->orderBy('name')->get(['godowns.id', 'shop_id', 'name', 'code']);

        $shopIds = $shops->modelKeys();
        $godownIds = $godowns->modelKeys();
        $activeShopId = (int) $request->session()->get('active_shop_id');
        $activeGodownId = (int) $request->session()->get('active_godown_id');

        if (! in_array($activeShopId, $shopIds, true)) {
            $activeShopId = (int) ($shopIds[0] ?? 0);
        }

        $availableGodowns = $godowns->filter(
            fn (Godown $godown) => ! $godown->shop_id || $godown->shop_id === $activeShopId
        );

        if (! $availableGodowns->contains('id', $activeGodownId)) {
            $activeGodownId = (int) ($availableGodowns->first()?->id ?? 0);
        }

        $permissionCodes = $user->isSuperAdmin() ? collect(['*']) : $user->effectivePermissionCodes();
        $request->session()->put([
            'role_id' => $user->role_id,
            'permitted_shop_ids' => $user->isSuperAdmin() ? ['*'] : $shopIds,
            'permitted_godown_ids' => $user->isSuperAdmin() ? ['*'] : $godownIds,
            'permitted_modules' => $user->isSuperAdmin()
                ? ['*']
                : $permissionCodes->map(fn (string $code) => str($code)->before('.')->toString())->unique()->values()->all(),
            'permitted_actions' => $permissionCodes->all(),
            'active_shop_id' => $activeShopId ?: null,
            'active_godown_id' => $activeGodownId ?: null,
        ]);

        View::share([
            'headerShops' => $shops,
            'headerGodowns' => $godowns,
            'activeShopId' => $activeShopId,
            'activeGodownId' => $activeGodownId,
        ]);

        return $next($request);
    }
}
