<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\ReferenceMasterRequest;
use App\Models\ReferenceMaster;
use App\Models\Setting;
use App\Services\PdfService;
use App\Services\ReferenceMasterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ReferenceMasterController extends Controller
{
    public function __construct(private readonly ReferenceMasterService $masters)
    {
    }

    public function index(Request $request, string $module): View|JsonResponse
    {
        $config = $this->module($module);
        Gate::authorize($module.'.view');

        if ($request->ajax()) {
            return DataTables::eloquent($this->masters->filteredQuery($config['type'], $request))
                ->addIndexColumn()
                ->editColumn('percentage', fn (ReferenceMaster $master) => $master->percentage !== null ? rtrim(rtrim(number_format((float) $master->percentage, 4), '0'), '.').'%' : '—')
                ->editColumn('is_active', fn (ReferenceMaster $master) => '<span class="badge bg-'.($master->is_active ? 'success' : 'secondary').'">'.($master->is_active ? 'Active' : 'Inactive').'</span>')
                ->addColumn('action', function (ReferenceMaster $master) use ($module, $request) {
                    $buttons = '';
                    if ($request->user()->can($module.'.update')) {
                        $buttons .= '<button type="button" class="btn btn-sm btn-soft-primary edit-master" data-id="'.$master->id.'" data-name="'.e($master->name).'" data-code="'.e($master->code).'" data-description="'.e($master->description).'" data-percentage="'.e($master->percentage).'" data-parent-id="'.e($master->parent_id).'" data-active="'.($master->is_active ? 1 : 0).'">Edit</button> ';
                    }
                    if ($request->user()->can($module.'.delete') && ! $master->children()->exists()) {
                        $buttons .= '<button type="button" class="btn btn-sm btn-soft-danger delete-master" data-url="'.route('admin.masters.destroy', [$module, $master]).'">Delete</button>';
                    }

                    return $buttons;
                })
                ->rawColumns(['is_active', 'action'])
                ->toJson();
        }

        return view('backend.masters.'.$module.'.index', [
            'moduleKey' => $module,
            'module' => $config,
            'parents' => isset($config['parent_type'])
                ? ReferenceMaster::query()->ofType($config['parent_type'])->where('is_active', true)->orderBy('name')->get(['id', 'name'])
                : collect(),
        ]);
    }

    public function store(ReferenceMasterRequest $request, string $module): JsonResponse
    {
        $config = $this->module($module);
        $master = $this->masters->create($request->validated(), $config);

        return ResponseHelper::success($config['title'].' record created successfully.', $master, 201);
    }

    public function update(ReferenceMasterRequest $request, string $module, ReferenceMaster $master): JsonResponse
    {
        $config = $this->module($module);
        abort_unless($master->type === $config['type'], 404);
        $master = $this->masters->update($master, $request->validated(), $config);

        return ResponseHelper::success($config['title'].' record updated successfully.', $master);
    }

    public function destroy(string $module, ReferenceMaster $master): JsonResponse
    {
        $config = $this->module($module);
        Gate::authorize($module.'.delete');
        abort_unless($master->type === $config['type'], 404);
        abort_if($master->children()->exists(), 422, 'Remove child records before deleting this record.');
        $master->delete();

        return ResponseHelper::success($config['title'].' record deleted successfully.');
    }

    public function pdf(Request $request, string $module, PdfService $pdf): Response
    {
        $config = $this->module($module);
        Gate::authorize($module.'.export');
        $records = $this->masters->filteredQuery($config['type'], $request)->orderBy('name')->get();
        $settings = Setting::values();

        return $pdf->reportDownload('pdf.modules.reference-masters', [
            'records' => $records,
            'module' => $config,
            'filters' => $request->only(['status', 'parent_id', 'from_date', 'to_date', 'search']),
            'company' => ['name' => $settings['company_name'] ?? 'Cholavin', 'address' => $settings['company_address'] ?? ''],
            'generatedAt' => now(),
        ], $module.'-'.now()->format('Ymd-His').'.pdf', $config['title'].' Report');
    }

    private function module(string $module): array
    {
        $config = config('erp_modules.reference.'.$module);
        abort_unless($config, 404);

        return $config;
    }
}
