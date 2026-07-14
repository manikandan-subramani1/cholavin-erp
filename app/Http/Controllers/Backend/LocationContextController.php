<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Access\SwitchGodownContextRequest;
use App\Http\Requests\Access\SwitchShopContextRequest;
use App\Services\Access\BusinessContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class LocationContextController extends Controller
{
    public function godowns(Request $request, BusinessContextService $contexts): JsonResponse
    {
        Gate::authorize('godowns.switch');

        $shopId = (int) $request->integer('shop_id');
        abort_unless($contexts->permittedShops($request->user())->contains('id', $shopId), 403);

        return ResponseHelper::success('Available godowns loaded.', [
            'godowns' => $contexts->permittedGodowns($request->user(), $shopId)
                ->map->only(['id', 'name', 'code'])
                ->values(),
        ]);
    }

    public function switchShop(SwitchShopContextRequest $request, BusinessContextService $contexts): JsonResponse
    {
        Gate::authorize('shops.switch');

        return ResponseHelper::success(
            'Working shop changed successfully.',
            $contexts->switchShop($request->user(), $request->integer('shop_id'))
        );
    }

    public function switchGodown(SwitchGodownContextRequest $request, BusinessContextService $contexts): JsonResponse
    {
        Gate::authorize('godowns.switch');

        return ResponseHelper::success(
            'Working godown changed successfully.',
            $contexts->switchGodown(
                $request->user(),
                $request->filled('godown_id') ? $request->integer('godown_id') : null
            )
        );
    }
}
