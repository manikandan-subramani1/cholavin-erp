<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accounts\PaymentRequest;
use App\Models\CommercialDocument;
use App\Models\Party;
use App\Models\Payment;
use App\Models\ReferenceMaster;
use App\Models\Setting;
use App\Services\PaymentService;
use App\Services\PdfService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class PaymentController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('payments.view');

        if ($request->ajax()) {
            $query = $this->filteredQuery($request);

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->orderColumn('DT_RowIndex', false)
                ->editColumn('payment_date', fn (Payment $payment) => $payment->payment_date->format('d-m-Y'))
                ->editColumn('amount', fn (Payment $payment) => number_format((float) $payment->amount, 2))
                ->addColumn('actions', fn (Payment $payment) => '<button type="button" class="btn btn-sm btn-soft-primary" data-payment-action="view"><i class="ri-eye-line"></i></button>')
                ->rawColumns(['actions'])
                ->with('summary', $this->summary($query))
                ->toJson();
        }

        $query = $this->filteredQuery($request);

        return view('backend.accounts.payments', [
            'methods' => ReferenceMaster::ofType('payment_method')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
            'parties' => Party::query()->forActiveShop()->orderBy('name')->get(['id', 'name', 'code']),
            'documents' => CommercialDocument::query()
                ->accessibleBy($request->user())
                ->where('financial_year_id', session('active_financial_year_id'))
                ->where('balance_amount', '>', 0)
                ->latest('document_date')
                ->get(['id', 'number']),
            'paymentSummary' => $this->summary($query),
        ]);
    }

    public function store(PaymentRequest $request, PaymentService $service): JsonResponse
    {
        return ResponseHelper::success('Payment recorded successfully.', $service->create($request->validated()), 201);
    }

    public function pdf(Request $request, PdfService $pdf): Response
    {
        Gate::authorize('payments.export');
        $rows = $this->filteredQuery($request)->orderByDesc('payment_date')->get()->map(fn (Payment $payment) => [
            'number' => $payment->number,
            'payment_date' => $payment->payment_date->format('d-m-Y'),
            'type' => str($payment->type)->replace('_', ' ')->title(),
            'party' => $payment->party?->name ?? '—',
            'method' => $payment->method?->name ?? '—',
            'reference_number' => $payment->reference_number ?: '—',
            'amount' => number_format((float) $payment->amount, 2),
        ]);
        $settings = Setting::values();

        return $pdf->reportDownload('pdf.modules.reference-masters', [
            'rows' => $rows,
            'columns' => ['number' => 'Number', 'payment_date' => 'Date', 'type' => 'Type', 'party' => 'Party', 'method' => 'Method', 'reference_number' => 'Reference', 'amount' => 'Amount'],
            'filters' => $request->only(['type', 'party_id', 'payment_method_id', 'from_date', 'to_date', 'search']),
            'company' => ['name' => $settings['company_name'] ?? 'Cholavin', 'address' => $settings['company_address'] ?? ''],
            'generatedAt' => now(),
        ], 'payments-'.now()->format('Ymd-His').'.pdf', 'Payments Report', 'L');
    }

    private function filteredQuery(Request $request): Builder
    {
        $search = is_array($request->input('search')) ? data_get($request->input('search'), 'value') : $request->input('search');

        return Payment::query()
            ->select(['id', 'shop_id', 'godown_id', 'financial_year_id', 'party_id', 'payment_method_id', 'number', 'payment_date', 'type', 'reference_number', 'amount'])
            ->with(['party:id,name', 'method:id,name'])
            ->accessibleBy($request->user())
            ->when(session('active_financial_year_id'), fn ($query) => $query->where('financial_year_id', session('active_financial_year_id')))
            ->when($search, fn ($query) => $query->where(fn ($query) => $query
                ->where('number', 'like', "%{$search}%")
                ->orWhere('reference_number', 'like', "%{$search}%")
                ->orWhereHas('party', fn ($party) => $party->where('name', 'like', "%{$search}%"))))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')->toString()))
            ->when($request->filled('party_id'), fn ($query) => $query->where('party_id', $request->integer('party_id')))
            ->when($request->filled('payment_method_id'), fn ($query) => $query->where('payment_method_id', $request->integer('payment_method_id')))
            ->when($request->filled('from_date'), fn ($query) => $query->whereDate('payment_date', '>=', $request->date('from_date')))
            ->when($request->filled('to_date'), fn ($query) => $query->whereDate('payment_date', '<=', $request->date('to_date')));
    }

    private function summary(Builder $query): array
    {
        $row = (clone $query)->select([])
            ->selectRaw('COUNT(*) as records')
            ->selectRaw("COALESCE(SUM(CASE WHEN type IN ('cash_receipt','bank_receipt','customer_collection','income') THEN amount ELSE 0 END), 0) as money_in")
            ->selectRaw("COALESCE(SUM(CASE WHEN type IN ('cash_payment','bank_payment','supplier_payment') THEN amount ELSE 0 END), 0) as money_out")
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as expenses")
            ->first();

        return [
            'records' => (int) ($row->records ?? 0),
            'money_in' => round((float) ($row->money_in ?? 0), 2),
            'money_out' => round((float) ($row->money_out ?? 0), 2),
            'expenses' => round((float) ($row->expenses ?? 0), 2),
        ];
    }
}
