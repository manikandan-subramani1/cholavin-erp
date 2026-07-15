<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vehicles\VehicleRequest;
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

class VehicleController extends Controller
{
    private const TYPE = 'vehicle';

    private const SLUG = 'vehicles';

    private const TITLE = 'Vehicle';

    private const CORE_FIELDS = [
        'name' => 'vehicle_number',
        'code' => 'vehicle_number',
        'status' => 'status',
    ];

    private const FIELDS = [
        0 => 'vehicle_number',
        1 => 'vehicle_type',
        2 => 'capacity',
        3 => 'owner_name',
        4 => 'insurance_expiry',
        5 => 'status',
    ];

    private const TABLE_COLUMNS = [
        'vehicle_number' => 'Vehicle Number',
        'vehicle_type' => 'Vehicle Type',
        'capacity' => 'Capacity',
        'owner_name' => 'Owner Name',
    ];

    private const SHOP_SCOPED = false;

    public function __construct(private readonly ReferenceMasterService $records) {}

    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize(self::SLUG.'.view');

        if ($request->ajax()) {
            $dataTable = DataTables::eloquent($this->records->filteredQuery(self::TYPE, $request))
                ->addIndexColumn();

            foreach (array_keys(self::TABLE_COLUMNS) as $field) {
                $dataTable->addColumn($field, fn (ReferenceMaster $record) => $this->records->fieldValue($record, $field, self::CORE_FIELDS) ?? '-');
            }

            return $dataTable
                ->addColumn('record_status', function (ReferenceMaster $record) {
                    $label = (string) $this->records->fieldValue($record, 'status', self::CORE_FIELDS);
                    $active = $record->is_active;

                    return '<span class="badge '.($active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary').'">'.e($label).'</span>';
                })
                ->addColumn('action', function (ReferenceMaster $record) use ($request) {
                    $actions = '<div class="d-flex justify-content-end gap-1">';

                    if ($request->user()->can(self::SLUG.'.update')) {
                        $actions .= '<a class="btn btn-sm btn-soft-primary" href="'.route('admin.'.self::SLUG.'.edit', $record->id).'" title="Edit"><i class="ri-edit-line"></i></a>';
                    }

                    if ($request->user()->can(self::SLUG.'.delete') && ! $record->children_exists) {
                        $actions .= '<button class="btn btn-sm btn-soft-danger delete-record" type="button" data-url="'.route('admin.'.self::SLUG.'.destroy', $record->id).'" title="Delete"><i class="ri-delete-bin-line"></i></button>';
                    }

                    return $actions.'</div>';
                })
                ->rawColumns(['record_status', 'action'])
                ->toJson();
        }

        return view('backend.'.self::SLUG.'.index');
    }

    public function create(): View
    {
        Gate::authorize(self::SLUG.'.create');

        return view('backend.'.self::SLUG.'.create', ['values' => []]);
    }

    public function store(VehicleRequest $request): JsonResponse
    {
        $record = $this->records->createForModule(
            self::TYPE,
            $request->validated(),
            self::CORE_FIELDS,
            self::FIELDS,
            self::SHOP_SCOPED,
        );

        return ResponseHelper::success(self::TITLE.' created successfully.', [
            'id' => $record->id,
            'redirect' => route('admin.'.self::SLUG.'.index'),
        ], 201);
    }

    public function edit(string $id): View
    {
        Gate::authorize(self::SLUG.'.update');
        $record = $this->records->findForModule(self::TYPE, $id);

        return view('backend.'.self::SLUG.'.edit', [
            'recordId' => (string) $record->id,
            'values' => $this->records->formValues($record, self::CORE_FIELDS, self::FIELDS),
        ]);
    }

    public function update(VehicleRequest $request, string $id): JsonResponse
    {
        $record = $this->records->findForModule(self::TYPE, $id);
        $record = $this->records->updateForModule(
            $record,
            $request->validated(),
            self::CORE_FIELDS,
            self::FIELDS,
            self::SHOP_SCOPED,
        );

        return ResponseHelper::success(self::TITLE.' updated successfully.', [
            'id' => $record->id,
            'redirect' => route('admin.'.self::SLUG.'.index'),
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        Gate::authorize(self::SLUG.'.delete');
        $record = $this->records->findForModule(self::TYPE, $id);
        abort_if($record->children()->exists(), 422, 'Remove child records before deleting this record.');
        $record->delete();

        return ResponseHelper::success(self::TITLE.' deleted successfully.');
    }

    public function pdf(Request $request, PdfService $pdf): Response
    {
        Gate::authorize(self::SLUG.'.export');
        $records = $this->records->filteredQuery(self::TYPE, $request)->orderBy('name')->get();
        $rows = $records->map(fn (ReferenceMaster $record) => collect(array_keys(self::TABLE_COLUMNS))
            ->mapWithKeys(fn (string $field) => [$field => $this->records->fieldValue($record, $field, self::CORE_FIELDS) ?? '-'])
            ->put('record_status', $this->records->fieldValue($record, 'status', self::CORE_FIELDS))
            ->all());

        return $pdf->reportDownload('pdf.modules.reference-masters', [
            'rows' => $rows,
            'columns' => self::TABLE_COLUMNS,
            'filters' => $request->only(['status', 'from_date', 'to_date', 'search']),
            'company' => ['name' => Setting::values()['company_name'] ?? 'Cholavin'],
            'generatedAt' => now(),
        ], self::SLUG.'-'.now()->format('Ymd-His').'.pdf', self::TITLE.' Report');
    }
}
