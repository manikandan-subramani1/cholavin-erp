<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accounts\VoucherRequest;
use App\Models\LedgerAccount;
use App\Models\Setting;
use App\Models\Voucher;
use App\Services\PdfService;
use App\Services\VoucherService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class VoucherController extends Controller
{
    public function __construct(private readonly VoucherService $vouchers) {}

    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('accounts.view');
        if ($request->ajax()) {
            return DataTables::eloquent($this->filteredQuery($request))
                ->addIndexColumn()
                ->editColumn('voucher_date', fn (Voucher $voucher) => $voucher->voucher_date->format('d-m-Y'))
                ->editColumn('total_debit', fn (Voucher $voucher) => number_format((float) $voucher->total_debit, 2))
                ->toJson();
        }

        return view('backend.accounts.vouchers', [
            'accounts' => LedgerAccount::query()->where(fn ($query) => $query->whereNull('shop_id')->orWhere('shop_id', session('active_shop_id')))->where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']),
        ]);
    }

    public function store(VoucherRequest $request): JsonResponse
    {
        return ResponseHelper::success('Voucher posted successfully.', $this->vouchers->create($request->validated()), 201);
    }

    public function pdf(Request $request, PdfService $pdf): Response
    {
        Gate::authorize('accounts.export');
        $rows = $this->filteredQuery($request)->orderByDesc('voucher_date')->get()->map(fn (Voucher $voucher) => [
            'number' => $voucher->number,
            'voucher_date' => $voucher->voucher_date->format('d-m-Y'),
            'type' => str($voucher->type)->title(),
            'reference_number' => $voucher->reference_number ?: '—',
            'lines_count' => $voucher->lines_count,
            'total_debit' => number_format((float) $voucher->total_debit, 2),
        ]);
        $settings = Setting::values();

        return $pdf->reportDownload('pdf.modules.reference-masters', [
            'rows' => $rows,
            'columns' => ['number' => 'Number', 'voucher_date' => 'Date', 'type' => 'Type', 'reference_number' => 'Reference', 'lines_count' => 'Lines', 'total_debit' => 'Total'],
            'filters' => $request->only(['type', 'from_date', 'to_date', 'search']),
            'company' => ['name' => $settings['company_name'] ?? 'Cholavin', 'address' => $settings['company_address'] ?? ''],
            'generatedAt' => now(),
        ], 'vouchers-'.now()->format('Ymd-His').'.pdf', 'Voucher Report', 'L');
    }

    private function filteredQuery(Request $request): Builder
    {
        $search = is_array($request->input('search')) ? data_get($request->input('search'), 'value') : $request->input('search');

        return Voucher::query()->withCount('lines')->forActiveShop()
            ->when(session('active_financial_year_id'), fn ($query) => $query->where('financial_year_id', session('active_financial_year_id')))
            ->when($search, fn ($query) => $query->where(fn ($query) => $query
                ->where('number', 'like', "%{$search}%")
                ->orWhere('reference_number', 'like', "%{$search}%")
                ->orWhere('narration', 'like', "%{$search}%")))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')->toString()))
            ->when($request->filled('from_date'), fn ($query) => $query->whereDate('voucher_date', '>=', $request->date('from_date')))
            ->when($request->filled('to_date'), fn ($query) => $query->whereDate('voucher_date', '<=', $request->date('to_date')));
    }
}
