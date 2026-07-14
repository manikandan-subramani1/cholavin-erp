<?php

namespace App\Http\Middleware;

use App\Services\Access\BusinessContextService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SyncUserAccessContext
{
    public function __construct(private readonly BusinessContextService $contexts)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $user->load(['role.permissions', 'permissions']);
        $context = $this->contexts->synchronize($user);
        $shopIds = $context['shops']->modelKeys();
        $godownIds = $user->isSuperAdmin()
            ? ['*']
            : $user->godowns()->wherePivot('is_active', true)->pluck('godowns.id')->all();
        $permissionCodes = $user->isSuperAdmin() ? collect(['*']) : $user->effectivePermissionCodes();

        $request->session()->put([
            'role_id' => $user->role_id,
            'permitted_shop_ids' => $user->isSuperAdmin() ? ['*'] : $shopIds,
            'permitted_godown_ids' => $godownIds,
            'permitted_modules' => $user->isSuperAdmin()
                ? ['*']
                : $permissionCodes->map(fn (string $code) => str($code)->before('.')->toString())->unique()->values()->all(),
            'permitted_actions' => $permissionCodes->all(),
        ]);

        View::share([
            'headerShops' => $context['shops'],
            'headerGodowns' => $context['godowns'],
            'activeShopId' => $context['shop_id'],
            'activeGodownId' => $context['godown_id'],
        ]);

        return $next($request);
    }
}
