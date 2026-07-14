<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accounts\PaymentRequest;
use App\Models\Payment;
use App\Models\ReferenceMaster;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class PaymentController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('payments.view');

        if ($request->ajax()) {
            $query = Payment::query()
                ->select([
                    'id',
                    'shop_id',
                    'godown_id',
                    'party_id',
                    'payment_method_id',
                    'number',
                    'payment_date',
                    'type',
                    'reference_number',
                    'amount',
                ])
                ->with(['party:id,name', 'method:id,name'])
                ->accessibleBy($request->user())
                ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')))
                ->when($request->filled('from_date'), fn ($query) => $query->whereDate('payment_date', '>=', $request->date('from_date')))
                ->when($request->filled('to_date'), fn ($query) => $query->whereDate('payment_date', '<=', $request->date('to_date')));

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->orderColumn('DT_RowIndex', false)
                ->editColumn('payment_date', fn (Payment $payment) => $payment->payment_date->format('d-m-Y'))
                ->editColumn('amount', fn (Payment $payment) => number_format((float) $payment->amount, 2))
                ->toJson();
        }

        return view('backend.accounts.payments', [
            'methods' => ReferenceMaster::ofType('payment_method')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function store(PaymentRequest $request, PaymentService $service): JsonResponse
    {
        return ResponseHelper::success('Payment recorded successfully.', $service->create($request->validated()), 201);
    }
}
