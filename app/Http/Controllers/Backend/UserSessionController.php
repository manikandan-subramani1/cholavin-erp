<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class UserSessionController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('sessions.view');

        if ($request->ajax()) {
            $query = DB::table('sessions')
                ->leftJoin('users', 'users.id', '=', 'sessions.user_id')
                ->leftJoin('roles', 'roles.id', '=', 'users.role_id')
                ->leftJoin('shops', 'shops.id', '=', 'sessions.active_shop_id')
                ->leftJoin('godowns', 'godowns.id', '=', 'sessions.active_godown_id')
                ->select([
                    'sessions.id',
                    'sessions.user_id',
                    'sessions.ip_address',
                    'sessions.user_agent',
                    'sessions.last_activity',
                    'users.name as user_name',
                    'roles.name as role_name',
                    'shops.name as shop_name',
                    'godowns.name as godown_name',
                ])
                ->when($request->filled('user_id'), fn ($query) => $query->where('sessions.user_id', $request->integer('user_id')))
                ->when($request->filled('status'), function ($query) use ($request) {
                    $cutoff = now()->subMinutes(config('session.lifetime'))->timestamp;
                    $request->string('status')->toString() === 'active'
                        ? $query->where('sessions.last_activity', '>=', $cutoff)
                        : $query->where('sessions.last_activity', '<', $cutoff);
                });

            $currentSessionId = $request->session()->getId();
            $canRevoke = $request->user()->can('sessions.revoke');

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('last_activity', fn ($row) => date('d M Y, h:i A', $row->last_activity))
                ->addColumn('status', function ($row) use ($currentSessionId) {
                    if ($row->id === $currentSessionId) {
                        return '<span class="badge bg-primary">Current</span>';
                    }

                    $active = $row->last_activity >= now()->subMinutes(config('session.lifetime'))->timestamp;

                    return '<span class="badge bg-'.($active ? 'success' : 'secondary').'">'.($active ? 'Active' : 'Expired').'</span>';
                })
                ->addColumn('action', function ($row) use ($canRevoke, $currentSessionId) {
                    if (! $canRevoke || $row->id === $currentSessionId) {
                        return '';
                    }

                    return '<button type="button" class="btn btn-sm btn-danger revoke-session" data-url="'.route('admin.sessions.destroy', $row->id).'">Force logout</button>';
                })
                ->rawColumns(['status', 'action'])
                ->toJson();
        }

        return view('backend.access.sessions', [
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function destroy(Request $request, string $sessionId): JsonResponse
    {
        Gate::authorize('sessions.revoke');
        abort_if(hash_equals($request->session()->getId(), $sessionId), 422, 'You cannot revoke your current session here.');

        $deleted = DB::table('sessions')->where('id', $sessionId)->delete();
        abort_unless($deleted, 404, 'The session no longer exists.');

        LoginHistory::query()
            ->where('session_id', $sessionId)
            ->whereNull('logged_out_at')
            ->update(['event' => 'session.revoked', 'logged_out_at' => now()]);

        return ResponseHelper::success('The selected session was revoked.');
    }

    public function destroyUser(Request $request, User $user): JsonResponse
    {
        Gate::authorize('sessions.revoke');

        $query = DB::table('sessions')->where('user_id', $user->id);
        if ($request->user()->is($user)) {
            $query->where('id', '!=', $request->session()->getId());
        }
        $sessionIds = $query->pluck('id');
        $query->delete();

        LoginHistory::query()
            ->whereIn('session_id', $sessionIds)
            ->whereNull('logged_out_at')
            ->update(['event' => 'session.revoked', 'logged_out_at' => now()]);

        return ResponseHelper::success('User sessions were revoked.', ['revoked_count' => $sessionIds->count()]);
    }
}
