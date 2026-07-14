<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Delivery\DeliveryRequest;
use App\Models\Delivery;
use App\Models\ReferenceMaster;
use App\Services\DeliveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class DeliveryController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('deliveries.view');
        if ($request->ajax()) {
            return DataTables::eloquent(Delivery::query()
                ->with(['document:id,number,party_id', 'document.party:id,name', 'vehicle:id,name', 'driver:id,name', 'route:id,name'])
                ->forActiveShop()
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status'))))
                ->addIndexColumn()
                ->addColumn('document_number', fn ($row) => $row->document->number)
                ->addColumn('party_name', fn ($row) => $row->document->party?->name)
                ->editColumn('scheduled_at', fn ($row) => $row->scheduled_at?->format('d-m-Y h:i A'))
                ->editColumn('status', fn ($row) => '<span class="badge bg-info">'.str($row->status)->replace('_', ' ')->title().'</span>')
                ->addColumn('action', fn ($row) => '<button class="btn btn-sm btn-soft-primary edit-delivery" data-id="'.$row->id.'">Update</button>')
                ->rawColumns(['status', 'action'])
                ->toJson();
        }

        $masters = ReferenceMaster::query()->whereIn('type', ['vehicle', 'driver', 'delivery_route'])->where('is_active', true)->orderBy('name')->get()->groupBy('type');
        return view('backend.delivery.index', [
            'vehicles' => $masters->get('vehicle', collect()),
            'drivers' => $masters->get('driver', collect()),
            'routes' => $masters->get('delivery_route', collect()),
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

    private function guard(Delivery $delivery): void
    {
        abort_unless($delivery->shop_id === (int) session('active_shop_id'), 404);
    }
}
