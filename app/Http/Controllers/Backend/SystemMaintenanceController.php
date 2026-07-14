<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Maintenance\DataImportRequest;
use App\Services\DataMaintenanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Yajra\DataTables\Facades\DataTables;

class SystemMaintenanceController extends Controller
{
    public function __construct(private readonly DataMaintenanceService $maintenance) {}

    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('maintenance.view');
        if ($request->ajax()) return DataTables::of($this->maintenance->backups())->addIndexColumn()->addColumn('action', fn ($row) => '<a class="btn btn-sm btn-soft-primary" href="'.route('admin.maintenance.backups.download', $row['name']).'">Download</a>')->rawColumns(['action'])->toJson();
        return view('backend.maintenance.index', ['errorLog' => $this->maintenance->errorLog()]);
    }

    public function backup(): JsonResponse
    {
        Gate::authorize('maintenance.create');
        return ResponseHelper::success('Backup created successfully.', $this->maintenance->createBackup(), 201);
    }

    public function download(string $name): BinaryFileResponse
    {
        Gate::authorize('maintenance.export');
        return $this->maintenance->downloadBackup($name);
    }

    public function export(string $dataset): BinaryFileResponse
    {
        Gate::authorize('maintenance.export');
        return $this->maintenance->export($dataset);
    }

    public function import(DataImportRequest $request): JsonResponse
    {
        $count = $this->maintenance->import((string) $request->input('dataset'), $request->file('file'));
        return ResponseHelper::success("{$count} records imported successfully.", ['count' => $count]);
    }
}
