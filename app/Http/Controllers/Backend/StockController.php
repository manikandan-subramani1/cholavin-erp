<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Godown;
use App\Models\InventoryBalance;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Setting;
use App\Services\PdfService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class StockController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('stock.view');

        if ($request->ajax()) {
            return DataTables::eloquent($this->balanceQuery($request))
                ->addIndexColumn()
                ->addColumn('product_name', fn (InventoryBalance $balance) => e($balance->product->name))
                ->addColumn('sku', fn (InventoryBalance $balance) => e($balance->product->sku ?: '—'))
                ->addColumn('godown_name', fn (InventoryBalance $balance) => e($balance->godown->name))
                ->editColumn('expiry_date', fn (InventoryBalance $balance) => $balance->expiry_date?->format('d-m-Y') ?: '—')
                ->editColumn('quantity', fn (InventoryBalance $balance) => number_format((float) $balance->quantity, 3))
                ->editColumn('average_cost', fn (InventoryBalance $balance) => number_format((float) $balance->average_cost, 2))
                ->addColumn('value', fn (InventoryBalance $balance) => number_format((float) $balance->quantity * (float) $balance->average_cost, 2))
                ->addColumn('record_status', fn (InventoryBalance $balance) => $this->stockStatus($balance))
                ->rawColumns(['record_status'])
                ->with('summary', $this->stockSummary($request))
                ->toJson();
        }

        return view('backend.inventory.stock', [
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku']),
            'godowns' => Godown::query()
                ->where('is_active', true)
                ->when(! $request->user()->isSuperAdmin(), fn ($query) => $query->whereIn('id', $request->user()->godowns()->wherePivot('is_active', true)->pluck('godowns.id')))
                ->orderBy('name')->get(['id', 'name']),
            'stockSummary' => $this->stockSummary($request),
        ]);
    }

    public function movements(Request $request): JsonResponse
    {
        Gate::authorize('stock.view');
        $query = InventoryMovement::query()
            ->with(['product:id,name,sku'])
            ->accessibleBy($request->user())
            ->when($request->filled('product_id'), fn ($query) => $query->where('product_id', $request->integer('product_id')))
            ->when($request->filled('godown_id'), fn ($query) => $query->where('godown_id', $request->integer('godown_id')))
            ->when($request->filled('from_date'), fn ($query) => $query->whereDate('movement_date', '>=', $request->date('from_date')))
            ->when($request->filled('to_date'), fn ($query) => $query->whereDate('movement_date', '<=', $request->date('to_date')));

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('product_name', fn (InventoryMovement $movement) => e($movement->product->name))
            ->editColumn('movement_date', fn (InventoryMovement $movement) => $movement->movement_date->format('d-m-Y'))
            ->toJson();
    }

    public function pdf(Request $request, PdfService $pdf): Response
    {
        Gate::authorize('stock.export');
        $records = $this->balanceQuery($request)->orderBy('godown_id')->orderBy('product_id')->get();
        $settings = Setting::values();

        return $pdf->reportDownload('pdf.modules.stock', [
            'records' => $records,
            'filters' => $request->only(['product_id', 'godown_id', 'low_stock', 'search']),
            'company' => ['name' => $settings['company_name'] ?? 'Cholavin', 'address' => $settings['company_address'] ?? ''],
            'generatedAt' => now(),
        ], 'stock-'.now()->format('Ymd-His').'.pdf', 'Stock Report', 'L');
    }

    private function balanceQuery(Request $request): Builder
    {
        $search = is_array($request->input('search'))
            ? (string) data_get($request->input('search'), 'value', '')
            : $request->string('search')->toString();

        return InventoryBalance::query()
            ->with(['product:id,name,sku,reorder_level', 'godown:id,name'])
            ->accessibleBy($request->user())
            ->when($request->filled('product_id'), fn ($query) => $query->where('product_id', $request->integer('product_id')))
            ->when($request->filled('godown_id'), fn ($query) => $query->where('godown_id', $request->integer('godown_id')))
            ->when($request->boolean('low_stock'), fn ($query) => $query->whereColumn('quantity', '<=', DB::raw('(select reorder_level from products where products.id = inventory_balances.product_id)')))
            ->when($search !== '', function ($query) use ($search): void {
                $query->whereHas('product', fn ($products) => $products->where(fn ($match) => $match->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%")));
            });
    }

    private function stockSummary(Request $request): array
    {
        $query = $this->balanceQuery($request);
        $totals = (clone $query)
            ->selectRaw('COUNT(DISTINCT product_id) as products')
            ->selectRaw('COALESCE(SUM(quantity), 0) as quantity')
            ->selectRaw('COALESCE(SUM(quantity * average_cost), 0) as value')
            ->first();

        return [
            'products' => (int) ($totals->products ?? 0),
            'quantity' => round((float) ($totals->quantity ?? 0), 3),
            'value' => round((float) ($totals->value ?? 0), 2),
            'low_stock' => (clone $query)->where('quantity', '>', 0)->whereColumn('quantity', '<=', DB::raw('(select reorder_level from products where products.id = inventory_balances.product_id)'))->count(),
            'out_of_stock' => (clone $query)->where('quantity', '<=', 0)->count(),
        ];
    }

    private function stockStatus(InventoryBalance $balance): string
    {
        $low = (float) $balance->quantity <= (float) $balance->product->reorder_level;

        return '<span class="badge '.($low ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success').'">'.($low ? 'Low' : 'Available').'</span>';
    }
}
