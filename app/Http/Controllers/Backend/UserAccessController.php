<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Access\StoreUserRequest;
use App\Http\Requests\Access\UpdateUserPermissionsRequest;
use App\Http\Requests\Access\UpdateUserRequest;
use App\Models\Godown;
use App\Models\Permission;
use App\Models\ReferenceMaster;
use App\Models\Role;
use App\Models\Shop;
use App\Models\User;
use App\Services\Access\AccessControlService;
use App\Services\Access\UserInvitationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class UserAccessController extends Controller
{
    public function __construct(
        private readonly AccessControlService $access,
        private readonly UserInvitationService $invitations,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('users.view');

        if ($request->ajax()) {
            $query = User::query()
                ->with(['role:id,name', 'shops:id,name', 'godowns:id,name', 'financialYears:id,name'])
                ->when($request->filled('role_id'), fn ($query) => $query->where('role_id', $request->integer('role_id')))
                ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->boolean('status')))
                ->when($request->filled('shop_id'), fn ($query) => $query->whereHas('shops', fn ($shops) => $shops->whereKey($request->integer('shop_id'))))
                ->when($request->filled('godown_id'), fn ($query) => $query->whereHas('godowns', fn ($godowns) => $godowns->whereKey($request->integer('godown_id'))));

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('identity', fn (User $user) => '<strong>'.e($user->name).'</strong><br><small class="text-muted">'.e($user->username.' · '.$user->email).'</small>')
                ->addColumn('role_name', fn (User $user) => e($user->role?->name ?? 'Unassigned'))
                ->addColumn('locations', fn (User $user) => '<small><strong>Shops:</strong> '.e($user->shops->pluck('name')->join(', ') ?: 'None').'<br><strong>Godowns:</strong> '.e($user->godowns->pluck('name')->join(', ') ?: 'None').'<br><strong>Years:</strong> '.e($user->financialYears->pluck('name')->join(', ') ?: 'None').'</small>')
                ->addColumn('record_status', fn (User $user) => '<span class="badge '.($user->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary').'">'.($user->is_active ? 'Active' : 'Inactive').'</span>')
                ->addColumn('action', fn (User $user) => $this->userActions($request, $user))
                ->filterColumn('role_name', fn ($query, string $keyword) => $query->whereHas('role', fn ($role) => $role->where('name', 'like', "%{$keyword}%")))
                ->rawColumns(['identity', 'locations', 'record_status', 'action'])
                ->with('summary', $this->summary($query))
                ->toJson();
        }

        $summaryQuery = User::query();

        return view('backend.access.users', [
            'roles' => Role::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'shops' => Shop::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'godowns' => Godown::with('shops:id,name')->where('is_active', true)->orderBy('name')->get(['id', 'shop_id', 'name']),
            'financialYears' => ReferenceMaster::ofType('financial_year')->where('is_active', true)->orderByDesc('code')->get(['id', 'name', 'code']),
            'userSummary' => $this->summary($summaryQuery),
        ]);
    }

    public function show(User $user): JsonResponse
    {
        Gate::authorize('users.view');
        $user->load(['role:id,name', 'shops:id,name', 'godowns:id,name', 'financialYears:id,name']);

        return ResponseHelper::success('User loaded.', [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'mobile' => $user->mobile,
            'role_id' => $user->role_id,
            'is_active' => $user->is_active,
            'shops' => $user->shops->modelKeys(),
            'godowns' => $user->godowns->modelKeys(),
            'financial_years' => $user->financialYears->modelKeys(),
        ]);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->access->createUser($request->validated(), $request->user()->id);
        $emailSent = $this->invitations->send($user, $request->user());

        return ResponseHelper::success(
            $emailSent
                ? 'User created and invitation email sent successfully.'
                : 'User created, but the invitation email could not be sent. Check the mail configuration.',
            ['id' => $user->id, 'email_sent' => $emailSent],
            201,
        );
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        abort_if($user->isSuperAdmin() && ! $request->user()->isSuperAdmin(), 403, 'Only Super Admin can edit this account.');
        $user = $this->access->updateUser($user, $request->validated(), $request->user()->id);

        return ResponseHelper::success('User access updated successfully.', ['id' => $user->id]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        Gate::authorize('users.delete');
        abort_if($request->user()->is($user), 422, 'You cannot deactivate your own account.');
        abort_if($user->isSuperAdmin(), 422, 'The Super Admin account cannot be deactivated.');
        $user->update(['is_active' => false]);

        return ResponseHelper::success('User deactivated successfully.');
    }

    public function permissions(User $user): View
    {
        Gate::authorize('users.update');
        $user->load(['role.permissions', 'permissions']);

        return view('backend.access.user-permissions', [
            'user' => $user,
            'permissions' => Permission::orderBy('module')->orderBy('action')->get()->groupBy('module'),
        ]);
    }

    public function updatePermissions(UpdateUserPermissionsRequest $request, User $user): JsonResponse
    {
        abort_if($user->isSuperAdmin(), 422, 'Super Admin always has full access.');
        $this->access->syncUserPermissions($user, $request->validated('overrides', []));

        return ResponseHelper::success('User-specific permissions updated immediately.', [
            'effective_permissions' => $user->effectivePermissionCodes(),
        ], refresh: ['datatable' => false, 'summary' => false, 'drawer' => true]);
    }

    private function userActions(Request $request, User $user): string
    {
        $actions = '<div class="d-flex justify-content-end gap-1">';
        if ($request->user()->can('users.update')) {
            $actions .= '<button type="button" class="btn btn-sm btn-soft-primary edit-user" data-url="'.route('admin.users.show', $user).'" title="Edit"><i class="ri-edit-line"></i></button>';
            $actions .= '<a class="btn btn-sm btn-soft-info" href="'.route('admin.users.permissions', $user).'" title="Permissions"><i class="ri-shield-user-line"></i></a>';
        }
        if ($request->user()->can('users.delete') && ! $request->user()->is($user) && ! $user->isSuperAdmin()) {
            $actions .= '<button type="button" class="btn btn-sm btn-soft-danger delete-user" data-url="'.route('admin.users.destroy', $user).'" title="Deactivate"><i class="ri-user-unfollow-line"></i></button>';
        }

        return $actions.'</div>';
    }

    private function summary($query): array
    {
        return [
            'records' => (clone $query)->count(),
            'active' => (clone $query)->where('is_active', true)->count(),
            'inactive' => (clone $query)->where('is_active', false)->count(),
            'roles' => (clone $query)->whereNotNull('role_id')->distinct()->count('role_id'),
        ];
    }
}
