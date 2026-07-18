<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Access\ListGodownsForShopRequest;
use App\Http\Requests\Access\ListShopsForGodownRequest;
use App\Http\Requests\Access\SwitchGodownContextRequest;
use App\Http\Requests\Access\SwitchFinancialYearContextRequest;
use App\Http\Requests\Access\SwitchShopContextRequest;
use App\Services\Access\BusinessContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class LocationContextController extends Controller
{
    public function shops(ListShopsForGodownRequest $request, BusinessContextService $contexts): JsonResponse
    {
        Gate::authorize('godowns.switch');

        $godownId = (int) $request->integer('godown_id');
        abort_unless($contexts->permittedGodowns($request->user())->contains('id', $godownId), 403);

        return ResponseHelper::success('Related shops loaded.', [
            'shops' => $contexts->permittedShopsForGodown($request->user(), $godownId)
                ->map->only(['id', 'name', 'code'])
                ->values(),
        ]);
    }

    public function godowns(ListGodownsForShopRequest $request, BusinessContextService $contexts): JsonResponse
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
            $contexts->switchShop(
                $request->user(),
                $request->filled('shop_id') ? $request->integer('shop_id') : null,
            )
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

    public function switchFinancialYear(SwitchFinancialYearContextRequest $request, BusinessContextService $contexts): JsonResponse
    {
        Gate::authorize('financial-years.switch');

        return ResponseHelper::success(
            'Financial year changed successfully.',
            $contexts->switchFinancialYear($request->user(), $request->integer('financial_year_id'))
        );
    }
}
