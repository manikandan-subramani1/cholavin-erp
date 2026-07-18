<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ActivityLogController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('activity-logs.view');

        if ($request->ajax()) {
            $query = ActivityLog::query()->with('user:id,name')
                ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->integer('user_id')))
                ->when($request->filled('event'), fn ($query) => $query->where('event', $request->string('event')->toString()))
                ->when($request->filled('module'), fn ($query) => $query->where('module', $request->string('module')->toString()))
                ->when($request->filled('from_date'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('from_date')))
                ->when($request->filled('to_date'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('to_date')));

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->editColumn('created_at', fn (ActivityLog $log) => $log->created_at?->format('d M Y, h:i:s A'))
                ->addColumn('user_name', fn (ActivityLog $log) => e($log->user?->name ?? 'Guest'))
                ->addColumn('request', fn (ActivityLog $log) => e(trim($log->method.' '.$log->route)))
                ->addColumn('details', fn (ActivityLog $log) => '<small>'.e(json_encode($log->properties ?: [], JSON_UNESCAPED_SLASHES)).'</small>')
                ->editColumn('event', fn (ActivityLog $log) => '<span class="badge bg-info-subtle text-info">'.e($log->event).'</span>')
                ->rawColumns(['event', 'details'])
                ->toJson();
        }

        return view('backend.access.activity-logs', [
            'users' => User::orderBy('name')->get(['id', 'name']),
            'events' => ActivityLog::query()->whereNotNull('event')->distinct()->orderBy('event')->pluck('event'),
            'modules' => ActivityLog::query()->whereNotNull('module')->distinct()->orderBy('module')->pluck('module'),
        ]);
    }
}
