<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Access\StoreGodownRequest;
use App\Http\Requests\Access\StoreShopRequest;
use App\Http\Requests\Access\UpdateGodownRequest;
use App\Http\Requests\Access\UpdateShopRequest;
use App\Models\Godown;
use App\Models\Shop;
use App\Services\Access\AccessControlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class LocationController extends Controller
{
    public function __construct(private readonly AccessControlService $access) {}

    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('shops.view');

        if ($request->ajax()) {
            return $request->string('entity')->toString() === 'godowns'
                ? $this->godownTable($request)
                : $this->shopTable($request);
        }

        return view('backend.access.locations', [
            'shops' => Shop::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'locationSummary' => $this->summary(),
        ]);
    }

    public function showShop(Shop $shop): JsonResponse
    {
        Gate::authorize('shops.view');

        return ResponseHelper::success('Shop loaded.', $shop->only(['id', 'name', 'code', 'address', 'is_active']));
    }

    public function storeShop(StoreShopRequest $request): JsonResponse
    {
        $shop = $this->access->createShop($request->validated());

        return ResponseHelper::success('Shop created successfully.', ['id' => $shop->id], 201);
    }

    public function updateShop(UpdateShopRequest $request, Shop $shop): JsonResponse
    {
        $shop = $this->access->updateShop($shop, $request->validated());

        return ResponseHelper::success('Shop updated successfully.', ['id' => $shop->id]);
    }

    public function destroyShop(Shop $shop): JsonResponse
    {
        Gate::authorize('shops.delete');
        abort_if($shop->users()->exists() || $shop->linkedGodowns()->exists() || $shop->godowns()->exists(), 422, 'Remove assigned users and godowns first.');
        $shop->delete();

        return ResponseHelper::success('Shop deleted successfully.');
    }

    public function showGodown(Godown $godown): JsonResponse
    {
        Gate::authorize('godowns.view');
        $godown->load('shops:id,name');

        return ResponseHelper::success('Godown loaded.', $godown->only(['id', 'name', 'code', 'address', 'is_active']) + [
            'shop_ids' => $godown->shops->modelKeys(),
        ]);
    }

    public function storeGodown(StoreGodownRequest $request): JsonResponse
    {
        $godown = $this->access->createGodown($request->validated());

        return ResponseHelper::success('Godown created successfully.', ['id' => $godown->id], 201);
    }

    public function updateGodown(UpdateGodownRequest $request, Godown $godown): JsonResponse
    {
        $godown = $this->access->updateGodown($godown, $request->validated());

        return ResponseHelper::success('Godown updated successfully.', ['id' => $godown->id]);
    }

    public function destroyGodown(Godown $godown): JsonResponse
    {
        Gate::authorize('godowns.delete');
        abort_if($godown->users()->exists(), 422, 'Remove assigned users first.');
        $godown->delete();

        return ResponseHelper::success('Godown deleted successfully.');
    }

    private function shopTable(Request $request): JsonResponse
    {
        $query = Shop::query()->withCount(['users', 'linkedGodowns'])
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->boolean('status')));

        return DataTables::eloquent($query)->addIndexColumn()
            ->addColumn('record_status', fn (Shop $shop) => $this->status($shop->is_active))
            ->addColumn('action', fn (Shop $shop) => $this->shopActions($request, $shop))
            ->rawColumns(['record_status', 'action'])->with('summary', $this->summary())->toJson();
    }

    private function godownTable(Request $request): JsonResponse
    {
        $query = Godown::query()->with('shops:id,name')->withCount('users')
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->boolean('status')))
            ->when($request->filled('shop_id'), fn ($query) => $query->whereHas('shops', fn ($shops) => $shops->whereKey($request->integer('shop_id'))));

        return DataTables::eloquent($query)->addIndexColumn()
            ->addColumn('shop_names', fn (Godown $godown) => e($godown->shops->pluck('name')->join(', ') ?: 'Independent'))
            ->addColumn('record_status', fn (Godown $godown) => $this->status($godown->is_active))
            ->addColumn('action', fn (Godown $godown) => $this->godownActions($request, $godown))
            ->rawColumns(['record_status', 'action'])->with('summary', $this->summary())->toJson();
    }

    private function summary(): array
    {
        return [
            'shops' => Shop::query()->count(),
            'active_shops' => Shop::query()->where('is_active', true)->count(),
            'godowns' => Godown::query()->count(),
            'active_godowns' => Godown::query()->where('is_active', true)->count(),
        ];
    }

    private function status(bool $active): string
    {
        return '<span class="badge '.($active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary').'">'.($active ? 'Active' : 'Inactive').'</span>';
    }

    private function shopActions(Request $request, Shop $shop): string
    {
        $html = '<div class="d-flex justify-content-end gap-1">';
        if ($request->user()->can('shops.update')) $html .= '<button class="btn btn-sm btn-soft-primary edit-shop" data-url="'.route('admin.shops.show', $shop).'" type="button"><i class="ri-edit-line"></i></button>';
        if ($request->user()->can('shops.delete') && ! $shop->users_count && ! $shop->linked_godowns_count) $html .= '<button class="btn btn-sm btn-soft-danger delete-location" data-url="'.route('admin.shops.destroy', $shop).'" type="button"><i class="ri-delete-bin-line"></i></button>';
        return $html.'</div>';
    }

    private function godownActions(Request $request, Godown $godown): string
    {
        $html = '<div class="d-flex justify-content-end gap-1">';
        if ($request->user()->can('godowns.update')) $html .= '<button class="btn btn-sm btn-soft-primary edit-godown" data-url="'.route('admin.godowns.show', $godown).'" type="button"><i class="ri-edit-line"></i></button>';
        if ($request->user()->can('godowns.delete') && ! $godown->users_count) $html .= '<button class="btn btn-sm btn-soft-danger delete-location" data-url="'.route('admin.godowns.destroy', $godown).'" type="button"><i class="ri-delete-bin-line"></i></button>';
        return $html.'</div>';
    }
}
