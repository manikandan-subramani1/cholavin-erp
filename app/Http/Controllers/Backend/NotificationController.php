<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\CommercialDocument;
use App\Models\Delivery;
use App\Models\InventoryBalance;
use App\Models\StockTransfer;
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
                ->when($request->filled('read_status'), fn ($query) => $request->string('read_status')->toString() === 'read' ? $query->whereNotNull('read_at') : $query->whereNull('read_at'))
                ->latest())
                ->addIndexColumn()
                ->editColumn('type', fn ($row) => str($row->type)->replace('_', ' ')->title())
                ->editColumn('created_at', fn ($row) => $row->created_at->format('d-m-Y h:i A'))
                ->addColumn('record_status', fn ($row) => '<span class="badge '.($row->read_at ? 'bg-secondary-subtle text-secondary' : 'bg-warning-subtle text-warning').'">'.($row->read_at ? 'Read' : 'Unread').'</span>')
                ->addColumn('action', fn ($row) => $row->read_at ? '' : '<button class="btn btn-sm btn-soft-primary mark-notification-read" data-url="'.route('admin.notifications.read', $row).'">Mark read</button>')
                ->rawColumns(['record_status', 'action'])
                ->toJson();
        }

        return view('backend.notifications.index');
    }

    public function badges(Request $request): JsonResponse
    {
        $user = $request->user();

        $counts = [
            'low_stock' => $user->can('stock.view')
                ? InventoryBalance::query()
                    ->accessibleBy($user)
                    ->join('products', 'products.id', '=', 'inventory_balances.product_id')
                    ->whereColumn('inventory_balances.quantity', '<=', 'products.reorder_level')
                    ->count()
                : 0,
            'pending_delivery' => $user->can('deliveries.view')
                ? Delivery::query()
                    ->forActiveShop()
                    ->whereNotIn('status', ['delivered', 'returned', 'cancelled'])
                    ->count()
                : 0,
            'overdue_payments' => $user->can('payments.view') || $user->can('accounts.view') || $user->can('reports.view')
                ? CommercialDocument::query()
                    ->accessibleBy($user)
                    ->where('status', 'posted')
                    ->where('balance_amount', '>', 0)
                    ->whereDate('due_date', '<', today())
                    ->count()
                : 0,
            'pending_approvals' => $user->can('stock.transfer')
                ? StockTransfer::query()
                    ->forActiveShop()
                    ->whereIn('status', ['draft', 'requested', 'pending'])
                    ->count()
                : 0,
        ];

        return ResponseHelper::success('Sidebar badges loaded.', $counts, refresh: [
            'datatable' => false,
            'summary' => false,
            'drawer' => false,
        ]);
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
