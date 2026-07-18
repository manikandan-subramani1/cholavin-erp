<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Services\DashboardAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardAnalyticsService $analytics): View
    {
        Gate::authorize('dashboard.view');

        $days = in_array($request->integer('period'), [7, 30, 90, 365], true)
            ? $request->integer('period')
            : 30;

        $data = $analytics->build($request->user(), $days);
        $data['chartData'] = [
            'trend' => $data['trend'],
            'products' => [
                'labels' => $data['topProducts']->pluck('name')->values(),
                'revenue' => $data['topProducts']->pluck('revenue')->map(fn ($value) => round((float) $value, 2))->values(),
                'cost' => $data['topProducts']->pluck('cost')->map(fn ($value) => round((float) $value, 2))->values(),
                'profit' => $data['topProducts']->pluck('profit')->map(fn ($value) => round((float) $value, 2))->values(),
            ],
            'stock_categories' => [
                'labels' => $data['stock']['categories']->pluck('name')->values(),
                'values' => $data['stock']['categories']->pluck('value')->values(),
            ],
            'stock_godowns' => [
                'labels' => $data['stock']['godowns']->pluck('name')->values(),
                'values' => $data['stock']['godowns']->pluck('value')->values(),
            ],
        ];

        return view('backend.pages.dashboard', $data);
    }
}
