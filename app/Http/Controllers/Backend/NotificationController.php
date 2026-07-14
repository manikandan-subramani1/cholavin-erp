<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class NotificationController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('notifications.view');
        if ($request->ajax()) {
            return DataTables::eloquent(UserNotification::query()
                ->where(fn ($query) => $query->whereNull('user_id')->orWhere('user_id', $request->user()->id))
                ->where(fn ($query) => $query->whereNull('shop_id')->orWhere('shop_id', session('active_shop_id')))
                ->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')))
                ->latest())
                ->addIndexColumn()
                ->editColumn('type', fn ($row) => str($row->type)->replace('_', ' ')->title())
                ->editColumn('created_at', fn ($row) => $row->created_at->format('d-m-Y h:i A'))
                ->addColumn('status', fn ($row) => $row->read_at ? 'Read' : 'Unread')
                ->addColumn('action', fn ($row) => $row->read_at ? '' : '<button class="btn btn-sm btn-soft-primary mark-notification-read" data-url="'.route('admin.notifications.read', $row).'">Mark read</button>')
                ->rawColumns(['action'])
                ->toJson();
        }

        return view('backend.notifications.index');
    }

    public function generate(Request $request, NotificationService $notifications): JsonResponse
    {
        Gate::authorize('notifications.update');
        $count = $notifications->refreshFor($request->user(), (int) session('active_shop_id'));

        return ResponseHelper::success('Operational alerts refreshed.', ['created_or_updated' => $count]);
    }

    public function read(Request $request, UserNotification $notification): JsonResponse
    {
        Gate::authorize('notifications.update');
        abort_unless(! $notification->user_id || $notification->user_id === $request->user()->id, 403);
        $notification->update(['read_at' => now()]);

        return ResponseHelper::success('Notification marked as read.');
    }
}
