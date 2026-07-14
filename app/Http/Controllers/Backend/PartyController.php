<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Parties\PartyRequest;
use App\Models\Party;
use App\Models\ReferenceMaster;
use App\Models\Setting;
use App\Services\PartyService;
use App\Services\PdfService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class PartyController extends Controller
{
    public function __construct(private readonly PartyService $parties) {}

    public function index(Request $request, string $type): View|JsonResponse
    {
        [$partyType, $title] = $this->type($type);
        Gate::authorize($type.'.view');

        if ($request->ajax()) {
            return DataTables::eloquent($this->parties->filteredQuery($partyType, $request))
                ->addIndexColumn()
                ->addColumn('current_balance', function (Party $party) use ($partyType) {
                    $opening = (float) $party->opening_balance * ($party->balance_type === ($partyType === 'customer' ? 'receivable' : 'payable') ? 1 : -1);
                    return round($opening + (float) $party->positive_balance - (float) $party->negative_balance - (float) $party->unallocated_payments, 2);
                })
                ->editColumn('credit_limit', fn (Party $party) => number_format((float) $party->credit_limit, 2))
                ->editColumn('opening_balance', fn (Party $party) => number_format((float) $party->opening_balance, 2).' '.ucfirst($party->balance_type))
                ->editColumn('is_active', fn (Party $party) => '<span class="badge bg-'.($party->is_active ? 'success' : 'secondary').'">'.($party->is_active ? 'Active' : 'Inactive').'</span>')
                ->addColumn('address_text', fn (Party $party) => $party->addresses->first()?->address ?: '—')
                ->rawColumns(['is_active'])->toJson();
        }

        return view('backend.parties.index', [
            'partyType' => $type,
            'title' => $title,
            'groups' => ReferenceMaster::query()->ofType($partyType.'_group')->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(Request $request, string $type, Party $party): JsonResponse
    {
        [$partyType] = $this->type($type);
        Gate::authorize($type.'.view');
        $this->guardParty($party, $partyType);

        return ResponseHelper::success('Party loaded.', $this->parties->profile($party, $partyType) + [
            'permissions' => [
                'update' => $request->user()->can($type.'.update'),
                'delete' => $request->user()->can($type.'.delete') && ! $party->documents()->exists(),
            ],
        ]);
    }

    public function transactions(Request $request, string $type, Party $party): JsonResponse
    {
        [$partyType] = $this->type($type);
        Gate::authorize($type.'.view');
        $this->guardParty($party, $partyType);

        return DataTables::of($this->parties->transactionQuery($party, $request))
            ->addIndexColumn()
            ->editColumn('source_type', fn ($row) => str($row->source_type)->replace('_', ' ')->title())
            ->editColumn('transaction_date', fn ($row) => date('d-m-Y', strtotime($row->transaction_date)))
            ->editColumn('due_date', fn ($row) => $row->due_date ? date('d-m-Y', strtotime($row->due_date)) : '-')
            ->editColumn('total', fn ($row) => number_format((float) $row->total, 2))
            ->editColumn('balance', fn ($row) => number_format((float) $row->balance, 2))
            ->editColumn('status', function ($row) {
                if ($row->record_kind === 'payment') return '<span class="badge bg-success-subtle text-success">Completed</span>';
                if ($row->status === 'draft') return '<span class="badge bg-warning-subtle text-warning">Draft</span>';
                return (float) $row->balance > 0
                    ? '<span class="badge bg-danger-subtle text-danger">Outstanding</span>'
                    : '<span class="badge bg-success-subtle text-success">Paid / Settled</span>';
            })
            ->addColumn('action', function ($row) use ($request) {
                if ($row->record_kind !== 'document') return '';
                $module = collect(config('erp_modules.documents'))->search(fn ($config) => $config['type'] === $row->source_type);
                if (! $module || ! $request->user()->can($module.'.print')) return '';
                return '<a class="btn btn-sm btn-soft-secondary" target="_blank" href="'.route('admin.documents.print', [$module, $row->source_id]).'"><i class="ri-printer-line"></i></a>';
            })
            ->rawColumns(['status', 'action'])
            ->toJson();
    }

    public function store(PartyRequest $request, string $type): JsonResponse
    {
        [$partyType, $title] = $this->type($type);
        $party = $this->parties->create($request->validated(), $partyType);
        return ResponseHelper::success(str($title)->singular().' created successfully.', $party, 201);
    }

    public function update(PartyRequest $request, string $type, Party $party): JsonResponse
    {
        [$partyType, $title] = $this->type($type);
        $this->guardParty($party, $partyType);
        $party = $this->parties->update($party, $request->validated(), $partyType);
        return ResponseHelper::success(str($title)->singular().' updated successfully.', $party);
    }

    public function destroy(string $type, Party $party): JsonResponse
    {
        [$partyType, $title] = $this->type($type);
        Gate::authorize($type.'.delete');
        $this->guardParty($party, $partyType);
        abort_if($party->documents()->exists(), 422, 'This party has transactions and cannot be deleted.');
        $party->delete();
        return ResponseHelper::success(str($title)->singular().' deleted successfully.');
    }

    public function pdf(Request $request, string $type, PdfService $pdf): Response
    {
        [$partyType, $title] = $this->type($type);
        Gate::authorize($type.'.export');
        $records = $this->parties->filteredQuery($partyType, $request)->orderBy('name')->get();
        $settings = Setting::values();
        return $pdf->reportDownload('pdf.modules.parties', ['records' => $records, 'title' => $title, 'filters' => $request->all(), 'company' => ['name' => $settings['company_name'] ?? 'Cholavin', 'address' => $settings['company_address'] ?? ''], 'generatedAt' => now()], $type.'-'.now()->format('Ymd-His').'.pdf', $title.' Report', 'L');
    }

    private function type(string $type): array
    {
        abort_unless(in_array($type, ['customers', 'suppliers'], true), 404);
        return [$type === 'customers' ? 'customer' : 'supplier', str($type)->title()->toString()];
    }

    private function guardParty(Party $party, string $partyType): void
    {
        abort_unless($party->shop_id === (int) session('active_shop_id') && in_array($party->type, [$partyType, 'both'], true), 404);
    }
}
