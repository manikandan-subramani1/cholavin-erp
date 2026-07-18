<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Access\StoreRoleRequest;
use App\Http\Requests\Access\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Access\AccessControlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    public function __construct(private readonly AccessControlService $access) {}

    public function index(Request $request): View|JsonResponse
    {
        Gate::authorize('roles.view');

        if ($request->ajax()) {
            $query = Role::query()
                ->withCount(['permissions', 'users'])
                ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->boolean('status')));

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('record_status', fn (Role $role) => '<span class="badge '.($role->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary').'">'.($role->is_active ? 'Active' : 'Inactive').'</span>')
                ->addColumn('role_type', fn (Role $role) => $role->is_super_admin ? '<span class="badge bg-danger-subtle text-danger">Super Admin</span>' : 'Standard')
                ->addColumn('action', fn (Role $role) => $this->actions($request, $role))
                ->rawColumns(['record_status', 'role_type', 'action'])
                ->with('summary', $this->summary($query))
                ->toJson();
        }

        return view('backend.access.roles', [
            'permissions' => Permission::orderBy('module')->orderBy('action')->get()->groupBy('module'),
            'roleSummary' => $this->summary(Role::query()->withCount(['permissions', 'users'])),
        ]);
    }

    public function show(Role $role): JsonResponse
    {
        Gate::authorize('roles.view');
        $role->load('permissions:id');

        return ResponseHelper::success('Role loaded.', [
            'id' => $role->id,
            'name' => $role->name,
            'is_active' => $role->is_active,
            'is_super_admin' => $role->is_super_admin,
            'permissions' => $role->permissions->modelKeys(),
        ]);
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = $this->access->createRole($request->validated());

        return ResponseHelper::success('Role created successfully.', ['id' => $role->id], 201);
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        abort_if($role->is_super_admin, 422, 'The Super Admin role cannot be changed.');
        $role = $this->access->updateRole($role, $request->validated());

        return ResponseHelper::success('Role permissions updated successfully.', ['id' => $role->id]);
    }

    public function destroy(Role $role): JsonResponse
    {
        Gate::authorize('roles.delete');
        abort_if($role->is_super_admin || $role->users()->exists(), 422, 'A protected or assigned role cannot be deleted.');
        $role->delete();

        return ResponseHelper::success('Role deleted successfully.');
    }

    private function actions(Request $request, Role $role): string
    {
        $actions = '<div class="d-flex justify-content-end gap-1">';
        if ($request->user()->can('roles.update') && ! $role->is_super_admin) {
            $actions .= '<button type="button" class="btn btn-sm btn-soft-primary edit-role" data-url="'.route('admin.roles.show', $role).'" title="Edit"><i class="ri-edit-line"></i></button>';
        }
        if ($request->user()->can('roles.delete') && ! $role->is_super_admin && $role->users_count === 0) {
            $actions .= '<button type="button" class="btn btn-sm btn-soft-danger delete-role" data-url="'.route('admin.roles.destroy', $role).'" title="Delete"><i class="ri-delete-bin-line"></i></button>';
        }

        return $actions.'</div>';
    }

    private function summary($query): array
    {
        $roleIds = (clone $query)->pluck('roles.id');

        return [
            'records' => $roleIds->count(),
            'active' => (clone $query)->where('is_active', true)->count(),
            'users' => User::query()->whereIn('role_id', $roleIds)->count(),
            'permissions' => Permission::query()->whereHas('roles', fn ($roles) => $roles->whereIn('roles.id', $roleIds))->distinct()->count(),
        ];
    }
}
