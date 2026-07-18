<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\DashboardFilterRequest;
use App\Models\Setting;
use App\Services\DashboardAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardAnalyticsService $analytics): View
    {
        Gate::authorize('dashboard.view');

        return view('backend.pages.dashboard', [
            'dashboard' => $analytics->shell($request->user()),
            'companyName' => Setting::values()['company_name'] ?? 'Cholavin ERP',
        ]);
    }

    public function kpis(DashboardFilterRequest $request, DashboardAnalyticsService $analytics): JsonResponse
    {
        return $this->success('Dashboard KPIs loaded successfully.', $analytics->kpis($request->user(), $request->validated()));
    }

    public function chart(DashboardFilterRequest $request, string $chart, DashboardAnalyticsService $analytics): JsonResponse
    {
        abort_unless(in_array($chart, $analytics->chartNames(), true), 404);

        return $this->success('Dashboard chart loaded successfully.', $analytics->chart($request->user(), $request->validated(), $chart));
    }

    public function top(DashboardFilterRequest $request, string $type, DashboardAnalyticsService $analytics): JsonResponse
    {
        return $this->success('Dashboard ranking loaded successfully.', $analytics->top($request->user(), $request->validated(), $type));
    }

    public function alerts(DashboardFilterRequest $request, DashboardAnalyticsService $analytics): JsonResponse
    {
        return $this->success('Dashboard alerts loaded successfully.', $analytics->alerts($request->user(), $request->validated()));
    }

    public function activity(DashboardFilterRequest $request, DashboardAnalyticsService $analytics): JsonResponse
    {
        return $this->success('Dashboard activity loaded successfully.', $analytics->activity($request->user(), $request->validated()));
    }

    public function tab(DashboardFilterRequest $request, string $tab, DashboardAnalyticsService $analytics): JsonResponse
    {
        return $this->success('Dashboard tab loaded successfully.', [
            'tab' => $tab,
            'html' => view('backend.pages.dashboard-tab', $analytics->tab($request->user(), $request->validated(), $tab))->render(),
        ] + $analytics->meta($request->user(), $request->validated()));
    }

    private function success(string $message, array $payload): JsonResponse
    {
        $meta = $payload['meta'] ?? [];
        unset($payload['meta']);
        $response = ResponseHelper::success($message, $payload, refresh: [
            'datatable' => false, 'summary' => false, 'drawer' => false,
        ]);
        $data = $response->getData(true);
        $data['meta'] = $meta;
        $response->setData($data);

        return $response;
    }
}
