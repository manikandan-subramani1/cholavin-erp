<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Delivery\DeliveryRequest;
use App\Models\CommercialDocument;
use App\Models\Delivery;
use App\Models\ReferenceMaster;
use App\Models\Setting;
use App\Services\DeliveryService;
use App\Services\PdfService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class DeliveryController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('deliveries.view');
        if ($request->ajax()) {
            $query = $this->filteredQuery($request);

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('document_number', fn ($row) => $row->document->number)
                ->addColumn('party_name', fn ($row) => $row->document->party?->name)
                ->editColumn('scheduled_at', fn ($row) => $row->scheduled_at?->format('d-m-Y h:i A'))
                ->editColumn('status', fn ($row) => '<span class="badge bg-info">'.str($row->status)->replace('_', ' ')->title().'</span>')
                ->addColumn('action', fn ($row) => '<button class="btn btn-sm btn-soft-primary edit-delivery" data-url="'.route('admin.deliveries.show', $row).'">Update</button>')
                ->rawColumns(['status', 'action'])
                ->with('summary', $this->summary($query))
                ->toJson();
        }

        $masters = ReferenceMaster::query()->whereIn('type', ['vehicle', 'driver', 'delivery_route'])->where('is_active', true)->orderBy('name')->get()->groupBy('type');
        return view('backend.delivery.index', [
            'vehicles' => $masters->get('vehicle', collect()),
            'drivers' => $masters->get('driver', collect()),
            'routes' => $masters->get('delivery_route', collect()),
            'documents' => CommercialDocument::query()->accessibleBy($request->user())->whereIn('type', ['sales_invoice', 'delivery_challan', 'pos_invoice'])->latest('document_date')->get(['id', 'number']),
            'deliverySummary' => $this->summary($this->filteredQuery($request)),
        ]);
    }

    public function show(Delivery $delivery): JsonResponse
    {
        Gate::authorize('deliveries.view');
        $this->guard($delivery);
        return ResponseHelper::success('Delivery loaded.', $delivery->load(['document.party', 'vehicle', 'driver', 'route']));
    }

    public function store(DeliveryRequest $request, DeliveryService $service): JsonResponse
    {
        $delivery = $service->save($request->safe()->except('proof'), proof: $request->file('proof'));
        return ResponseHelper::success('Delivery assigned successfully.', $delivery, 201);
    }

    public function update(DeliveryRequest $request, Delivery $delivery, DeliveryService $service): JsonResponse
    {
        Gate::authorize('deliveries.update');
        $this->guard($delivery);
        $delivery = $service->save($request->safe()->except('proof'), $delivery, $request->file('proof'));
        return ResponseHelper::success('Delivery updated successfully.', $delivery);
    }

    public function pdf(Request $request, PdfService $pdf): Response
    {
        Gate::authorize('deliveries.export');
        $rows = $this->filteredQuery($request)->orderByDesc('scheduled_at')->get()->map(fn (Delivery $delivery) => [
            'document' => $delivery->document->number,
            'party' => $delivery->document->party?->name ?? '—',
            'vehicle' => $delivery->vehicle?->name ?? '—',
            'driver' => $delivery->driver?->name ?? '—',
            'route' => $delivery->route?->name ?? '—',
            'scheduled_at' => $delivery->scheduled_at?->format('d-m-Y h:i A') ?? '—',
            'status' => str($delivery->status)->replace('_', ' ')->title(),
        ]);
        $settings = Setting::values();

        return $pdf->reportDownload('pdf.modules.reference-masters', [
            'rows' => $rows,
            'columns' => ['document' => 'Document', 'party' => 'Party', 'vehicle' => 'Vehicle', 'driver' => 'Driver', 'route' => 'Route', 'scheduled_at' => 'Scheduled', 'status' => 'Status'],
            'filters' => $request->only(['status', 'vehicle_id', 'driver_id', 'from_date', 'to_date', 'search']),
            'company' => ['name' => $settings['company_name'] ?? 'Cholavin', 'address' => $settings['company_address'] ?? ''],
            'generatedAt' => now(),
        ], 'deliveries-'.now()->format('Ymd-His').'.pdf', 'Delivery Report', 'L');
    }

    private function guard(Delivery $delivery): void
    {
        abort_unless($delivery->shop_id === (int) session('active_shop_id'), 404);
    }

    private function filteredQuery(Request $request): Builder
    {
        return Delivery::query()
            ->with(['document:id,number,party_id', 'document.party:id,name', 'vehicle:id,name', 'driver:id,name', 'route:id,name'])
            ->forActiveShop()
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('vehicle_id'), fn ($query) => $query->where('vehicle_id', $request->integer('vehicle_id')))
            ->when($request->filled('driver_id'), fn ($query) => $query->where('driver_id', $request->integer('driver_id')))
            ->when($request->filled('from_date'), fn ($query) => $query->whereDate('scheduled_at', '>=', $request->date('from_date')))
            ->when($request->filled('to_date'), fn ($query) => $query->whereDate('scheduled_at', '<=', $request->date('to_date')));
    }

    private function summary(Builder $query): array
    {
        $row = (clone $query)->select([])
            ->selectRaw('COUNT(*) as records')
            ->selectRaw("SUM(CASE WHEN status IN ('pending','assigned') THEN 1 ELSE 0 END) as pending")
            ->selectRaw("SUM(CASE WHEN status = 'out_for_delivery' THEN 1 ELSE 0 END) as in_transit")
            ->selectRaw("SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered")
            ->first();

        return [
            'records' => (int) ($row->records ?? 0),
            'pending' => (int) ($row->pending ?? 0),
            'in_transit' => (int) ($row->in_transit ?? 0),
            'delivered' => (int) ($row->delivered ?? 0),
        ];
    }
}
