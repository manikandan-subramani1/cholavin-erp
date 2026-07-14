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

        return view('backend.pages.dashboard', $analytics->build($request->user(), $days));
    }
}
