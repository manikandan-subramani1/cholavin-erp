<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StockTransferRequest;
use App\Models\Godown;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Services\StockTransferService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class StockTransferController extends Controller
{
    public function __construct(private readonly StockTransferService $transfers) {}

    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('stock.view');

        if ($request->ajax()) {
            $query = $this->filteredQuery($request);

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->editColumn('transfer_date', fn (StockTransfer $transfer) => $transfer->transfer_date->format('d-m-Y'))
                ->addColumn('route', fn (StockTransfer $transfer) => e($transfer->fromGodown->name.' → '.$transfer->toGodown->name))
                ->addColumn('record_status', fn (StockTransfer $transfer) => '<span class="badge '.($transfer->status === 'completed' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning').'">'.str($transfer->status)->title().'</span>')
                ->rawColumns(['record_status'])
                ->with('summary', $this->summary($query))
                ->toJson();
        }

        $godowns = Godown::query()
            ->where('is_active', true)
            ->where(function ($query): void {
                $query->where('shop_id', session('active_shop_id'))
                    ->orWhereHas('shops', fn ($shops) => $shops->where('shops.id', session('active_shop_id')));
            })->orderBy('name')->get(['id', 'name']);

        return view('backend.inventory.transfers', [
            'godowns' => $godowns,
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku']),
            'transferSummary' => $this->summary($this->filteredQuery($request)),
        ]);
    }

    public function store(StockTransferRequest $request): JsonResponse
    {
        $transfer = $this->transfers->create($request->validated());

        return ResponseHelper::success('Stock transfer created successfully.', $transfer, 201);
    }

    private function filteredQuery(Request $request): Builder
    {
        return StockTransfer::query()
            ->with(['fromGodown:id,name', 'toGodown:id,name'])
            ->withCount('items')
            ->forActiveShop()
            ->when(session('active_financial_year_id'), fn ($query) => $query->where('financial_year_id', session('active_financial_year_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('godown_id'), fn ($query) => $query->where(fn ($match) => $match->where('from_godown_id', $request->integer('godown_id'))->orWhere('to_godown_id', $request->integer('godown_id'))))
            ->when($request->filled('from_date'), fn ($query) => $query->whereDate('transfer_date', '>=', $request->date('from_date')))
            ->when($request->filled('to_date'), fn ($query) => $query->whereDate('transfer_date', '<=', $request->date('to_date')));
    }

    private function summary(Builder $query): array
    {
        $ids = (clone $query)->pluck('stock_transfers.id');

        return [
            'records' => $ids->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
            'drafts' => (clone $query)->where('status', 'draft')->count(),
            'quantity' => round((float) DB::table('stock_transfer_items')->whereIn('stock_transfer_id', $ids)->sum('quantity'), 3),
        ];
    }
}
