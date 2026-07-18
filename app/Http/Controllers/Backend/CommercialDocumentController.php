<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Documents\CommercialDocumentRequest;
use App\Models\CommercialDocument;
use App\Models\Product;
use App\Models\Setting;
use App\Services\CommercialDocumentService;
use App\Services\PdfService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class CommercialDocumentController extends Controller
{
    public function __construct(private readonly CommercialDocumentService $documents) {}

    public function index(Request $request, string $module): View|JsonResponse
    {
        $config = $this->module($module); Gate::authorize($module.'.view');
        if ($request->ajax()) {
            $query = $this->documents->filteredQuery($config['type'], $request);

            return DataTables::eloquent($query)
                ->addIndexColumn()->editColumn('document_date', fn ($row) => $row->document_date->format('d-m-Y'))
                ->editColumn('total_amount', fn ($row) => number_format((float)$row->total_amount, 2))
                ->editColumn('balance_amount', fn ($row) => number_format((float)$row->balance_amount, 2))
                ->editColumn('status', fn ($row) => '<span class="badge bg-'.($row->status === 'posted' ? 'success' : 'warning').'">'.ucfirst($row->status).'</span>')
                ->addColumn('action', function ($row) use ($module, $request) {
                    $buttons = $request->user()->can($module.'.print')
                        ? '<a class="btn btn-sm btn-soft-secondary" href="'.route('admin.documents.print', [$module, $row]).'">Print</a> '
                        : '';
                    if ($row->status === 'posted') {
                        $message = rawurlencode("{$row->number} - Amount Rs. {$row->total_amount}");
                        $buttons .= '<a class="btn btn-sm btn-soft-success" target="_blank" rel="noopener" href="https://wa.me/?text='.$message.'">WhatsApp</a> ';
                        $buttons .= '<a class="btn btn-sm btn-soft-info" href="mailto:?subject='.rawurlencode($row->number).'&body='.$message.'">Email</a> ';
                    }
                    if ($row->status === 'draft' && $request->user()->can($module.'.update')) $buttons .= '<button class="btn btn-sm btn-soft-primary edit-document" data-id="'.$row->id.'">Edit</button> ';
                    if ($row->status === 'draft' && $request->user()->can($module.'.delete')) $buttons .= '<button class="btn btn-sm btn-soft-danger delete-document" data-url="'.route('admin.documents.destroy', [$module, $row]).'">Delete</button>';
                    return $buttons;
                })->rawColumns(['status','action'])->with('summary', $this->summary($query))->toJson();
        }
        $query = $this->documents->filteredQuery($config['type'], $request);

        $viewData = [
            'moduleKey' => $module,
            'module' => $config,
            'documentSummary' => $this->summary($query),
        ];
        if ($module === 'pos-billing') {
            $viewData['posProducts'] = Product::query()
                ->where('is_active', true)
                ->withSum(['stockBalances as available_stock' => fn ($stock) => $stock
                    ->when(session('active_shop_id'), fn ($stock) => $stock->where('shop_id', session('active_shop_id')))
                    ->when(! session('active_shop_id') && ! $request->user()->isSuperAdmin(), fn ($stock) => $stock->whereRaw('1 = 0'))
                    ->when(session('active_godown_id'), fn ($stock) => $stock->where('godown_id', session('active_godown_id')))], 'quantity')
                ->orderBy('name')
                ->limit(60)
                ->get(['id', 'name', 'sku', 'image', 'sale_price', 'price']);
        }

        return view('backend.documents.'.$module.'.index', $viewData);
    }

    public function show(string $module, CommercialDocument $document): JsonResponse
    {
        $config = $this->module($module); Gate::authorize($module.'.view'); $this->guard($document, $config);
        return ResponseHelper::success('Document loaded.', $document->load(['party:id,name,code', 'items.product:id,name']));
    }

    public function store(CommercialDocumentRequest $request, string $module): JsonResponse
    {
        $document = $this->documents->create($request->validated(), $this->module($module));
        return ResponseHelper::success('Document created successfully.', $document, 201);
    }

    public function update(CommercialDocumentRequest $request, string $module, CommercialDocument $document): JsonResponse
    {
        $config = $this->module($module); $this->guard($document, $config);
        return ResponseHelper::success('Document updated successfully.', $this->documents->update($document, $request->validated(), $config));
    }

    public function destroy(string $module, CommercialDocument $document): JsonResponse
    {
        $config = $this->module($module); Gate::authorize($module.'.delete'); $this->guard($document, $config);
        abort_unless($document->status === 'draft', 409, 'Posted documents cannot be deleted.'); $document->delete();
        return ResponseHelper::success('Document deleted successfully.');
    }

    public function pdf(Request $request, string $module, PdfService $pdf): Response
    {
        $config = $this->module($module); Gate::authorize($module.'.export'); $records = $this->documents->filteredQuery($config['type'], $request)->orderByDesc('document_date')->get(); $settings = Setting::values();
        return $pdf->reportDownload('pdf.modules.documents', ['records'=>$records,'module'=>$config,'filters'=>$request->all(),'company'=>['name'=>$settings['company_name']??'Cholavin','address'=>$settings['company_address']??''],'generatedAt'=>now()], $module.'-'.now()->format('Ymd-His').'.pdf', $config['title'].' Report','L');
    }

    public function print(string $module, CommercialDocument $document, PdfService $pdf): Response
    {
        $config=$this->module($module); Gate::authorize($module.'.print'); $this->guard($document,$config); $settings=Setting::values(); $document->load(['party','shop','godown','items.product']);
        return $pdf->reportDownload('pdf.modules.document', ['document'=>$document,'module'=>$config,'company'=>['name'=>$settings['company_name']??'Cholavin','address'=>$settings['company_address']??''],'generatedAt'=>now()], $document->number.'.pdf', $config['title'].' '.$document->number);
    }

    private function module(string $module): array { $config=config('erp_modules.documents.'.$module); abort_unless($config,404); return $config; }
    private function summary(Builder $query): array
    {
        $row = (clone $query)->select([])
            ->selectRaw('COUNT(*) as records')
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'posted' THEN total_amount ELSE 0 END), 0) as posted_total")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'posted' THEN balance_amount ELSE 0 END), 0) as outstanding")
            ->selectRaw("SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as drafts")
            ->first();

        return [
            'records' => (int) ($row->records ?? 0),
            'posted_total' => round((float) ($row->posted_total ?? 0), 2),
            'outstanding' => round((float) ($row->outstanding ?? 0), 2),
            'drafts' => (int) ($row->drafts ?? 0),
        ];
    }
    private function guard(CommercialDocument $document, array $config): void
    {
        $shopId = session('active_shop_id') ? (int) session('active_shop_id') : null;
        $shopVisible = request()->user()?->isSuperAdmin() && ! $shopId
            ? true
            : $document->shop_id === $shopId;
        $financialYearVisible = ! session('active_financial_year_id')
            || $document->financial_year_id === (int) session('active_financial_year_id');

        abort_unless($document->type === $config['type'] && $shopVisible && $financialYearVisible, 404);
    }
}
