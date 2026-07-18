<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\PdfService;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reports) {}

    public function index(Request $request, string $report): View|JsonResponse
    {
        $title = $this->title($report);
        Gate::authorize('reports.view');
        if ($request->ajax()) {
            $query = $this->reports->query($report, $request);
            $summary = (clone $query)
                ->selectRaw('COUNT(*) as records')
                ->selectRaw('COALESCE(SUM(debit), 0) as debit')
                ->selectRaw('COALESCE(SUM(credit), 0) as credit')
                ->selectRaw('COALESCE(SUM(amount), 0) as amount')
                ->first();

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('description', fn ($row) => str($row->description)->replace('_', ' ')->title())
                ->editColumn('amount', fn ($row) => number_format((float) $row->amount, 2))
                ->with('summary', [
                    'records' => (int) ($summary->records ?? 0),
                    'debit' => round((float) ($summary->debit ?? 0), 2),
                    'credit' => round((float) ($summary->credit ?? 0), 2),
                    'amount' => round((float) ($summary->amount ?? 0), 2),
                ])
                ->toJson();
        }

        return view('backend.reports.index', compact('report', 'title'));
    }

    public function pdf(Request $request, string $report, PdfService $pdf): Response
    {
        $title = $this->title($report);
        Gate::authorize('reports.export');
        $records = $this->reports->query($report, $request)->orderByDesc('report_date')->get();
        $settings = Setting::values();

        return $pdf->reportDownload('pdf.modules.generic-report', [
            'records' => $records,
            'title' => $title,
            'filters' => $request->only(['from_date', 'to_date', 'party_id', 'search']),
            'company' => ['name' => $settings['company_name'] ?? 'Cholavin', 'address' => $settings['company_address'] ?? ''],
            'generatedAt' => now(),
        ], $report.'-'.now()->format('Ymd-His').'.pdf', $title, 'L');
    }

    private function title(string $report): string
    {
        abort_unless(isset(ReportService::REPORTS[$report]), 404);

        return ReportService::REPORTS[$report];
    }
}
