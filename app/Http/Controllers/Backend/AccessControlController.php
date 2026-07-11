<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Godown;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccessControlController extends Controller
{
    public function users(): View
    {
        Gate::authorize('users.view');

        return view('backend.access.users', [
            'users' => User::with(['role', 'permissions', 'shops:id,name', 'godowns:id,name'])->latest()->get(),
            'roles' => Role::where('is_active', true)->orderBy('name')->get(),
            'shops' => Shop::where('is_active', true)->orderBy('name')->get(),
            'godowns' => Godown::with('shop:id,name')->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function storeUser(Request $request): RedirectResponse
    {
        Gate::authorize('users.create');
        $data = $this->validateUser($request);
        $user = User::create($data);
        $this->syncLocations($user, $request);

        return back()->with('success', 'User created successfully.');
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('users.update');
        $data = $this->validateUser($request, $user);
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $user->update($data);
        $this->syncLocations($user, $request);

        return back()->with('success', 'User access updated successfully.');
    }

    public function destroyUser(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('users.delete');
        abort_if($request->user()->is($user), 422, 'You cannot delete your own account.');
        abort_if($user->isSuperAdmin(), 422, 'The Super Admin account cannot be deleted.');
        $user->delete();

        return back()->with('success', 'User deleted successfully.');
    }

    public function userPermissions(User $user): View
    {
        Gate::authorize('users.update');
        $user->load(['role.permissions', 'permissions']);

        return view('backend.access.user-permissions', [
            'user' => $user,
            'permissions' => Permission::orderBy('module')->orderBy('action')->get()->groupBy('module'),
        ]);
    }

    public function updateUserPermissions(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('users.update');
        abort_if($user->isSuperAdmin(), 422, 'Super Admin always has full access.');

        $data = $request->validate([
            'overrides' => ['nullable', 'array'],
            'overrides.*' => ['required', Rule::in(['inherit', 'allow', 'deny'])],
        ]);

        $sync = collect($data['overrides'] ?? [])
            ->reject(fn (string $value) => $value === 'inherit')
            ->mapWithKeys(fn (string $value, string $permissionId) => [
                (int) $permissionId => ['allowed' => $value === 'allow'],
            ])->all();

        $user->permissions()->sync($sync);

        return back()->with('success', 'User-specific permissions updated immediately.');
    }

    public function roles(): View
    {
        Gate::authorize('roles.view');

        return view('backend.access.roles', [
            'roles' => Role::with(['permissions', 'users:id,role_id'])->orderBy('name')->get(),
            'permissions' => Permission::orderBy('module')->orderBy('action')->get()->groupBy('module'),
        ]);
    }

    public function storeRole(Request $request): RedirectResponse
    {
        Gate::authorize('roles.create');
        $data = $request->validate(['name' => ['required', 'string', 'max:100', 'unique:roles,name']]);
        $role = Role::create(['name' => $data['name'], 'slug' => Str::slug($data['name']), 'is_active' => true]);
        $role->permissions()->sync($request->input('permissions', []));

        return back()->with('success', 'Role created successfully.');
    }

    public function updateRole(Request $request, Role $role): RedirectResponse
    {
        Gate::authorize('roles.update');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('roles')->ignore($role)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);
        $role->update(['name' => $data['name'], 'slug' => Str::slug($data['name']), 'is_active' => $request->boolean('is_active')]);
        if (! $role->is_super_admin) {
            $role->permissions()->sync($data['permissions'] ?? []);
        }

        return back()->with('success', 'Role permissions updated successfully.');
    }

    public function destroyRole(Role $role): RedirectResponse
    {
        Gate::authorize('roles.delete');
        abort_if($role->is_super_admin || $role->users()->exists(), 422, 'A protected or assigned role cannot be deleted.');
        $role->delete();

        return back()->with('success', 'Role deleted successfully.');
    }

    public function locations(): View
    {
        Gate::authorize('shops.view');

        return view('backend.access.locations', [
            'shops' => Shop::withCount(['users', 'godowns'])->orderBy('name')->get(),
            'godowns' => Godown::with('shop:id,name')->withCount('users')->orderBy('name')->get(),
        ]);
    }

    public function storeShop(Request $request): RedirectResponse
    {
        Gate::authorize('shops.create');
        Shop::create($request->validate([
            'name' => ['required', 'string', 'max:150'], 'code' => ['required', 'string', 'max:50', 'unique:shops,code'], 'address' => ['nullable', 'string', 'max:1000'],
        ]) + ['is_active' => true]);

        return back()->with('success', 'Shop created successfully.');
    }

    public function updateShop(Request $request, Shop $shop): RedirectResponse
    {
        Gate::authorize('shops.update');
        $shop->update($request->validate([
            'name' => ['required', 'string', 'max:150'], 'code' => ['required', 'string', 'max:50', Rule::unique('shops')->ignore($shop)], 'address' => ['nullable', 'string', 'max:1000'],
        ]) + ['is_active' => $request->boolean('is_active')]);

        return back()->with('success', 'Shop updated successfully.');
    }

    public function destroyShop(Shop $shop): RedirectResponse
    {
        Gate::authorize('shops.delete');
        abort_if($shop->godowns()->exists() || $shop->users()->exists(), 422, 'Remove assigned users and godowns first.');
        $shop->delete();

        return back()->with('success', 'Shop deleted successfully.');
    }

    public function storeGodown(Request $request): RedirectResponse
    {
        Gate::authorize('godowns.create');
        Godown::create($request->validate([
            'shop_id' => ['nullable', 'exists:shops,id'], 'name' => ['required', 'string', 'max:150'], 'code' => ['required', 'string', 'max:50', 'unique:godowns,code'], 'address' => ['nullable', 'string', 'max:1000'],
        ]) + ['is_active' => true]);

        return back()->with('success', 'Godown created successfully.');
    }

    public function updateGodown(Request $request, Godown $godown): RedirectResponse
    {
        Gate::authorize('godowns.update');
        $godown->update($request->validate([
            'shop_id' => ['nullable', 'exists:shops,id'], 'name' => ['required', 'string', 'max:150'], 'code' => ['required', 'string', 'max:50', Rule::unique('godowns')->ignore($godown)], 'address' => ['nullable', 'string', 'max:1000'],
        ]) + ['is_active' => $request->boolean('is_active')]);

        return back()->with('success', 'Godown updated successfully.');
    }

    public function destroyGodown(Godown $godown): RedirectResponse
    {
        Gate::authorize('godowns.delete');
        abort_if($godown->users()->exists(), 422, 'Remove assigned users first.');
        $godown->delete();

        return back()->with('success', 'Godown deleted successfully.');
    }

    public function activityLogs(): View
    {
        Gate::authorize('activity-logs.view');

        return view('backend.access.activity-logs', ['logs' => ActivityLog::with('user:id,name')->latest('id')->paginate(50)]);
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'username' => ['required', 'string', 'max:100', Rule::unique('users')->ignore($user)],
            'email' => ['required', 'email', 'max:190', Rule::unique('users')->ignore($user)],
            'mobile' => ['nullable', 'string', 'max:30', Rule::unique('users')->ignore($user)],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
            'is_active' => ['nullable', 'boolean'],
            'shops' => ['nullable', 'array'], 'shops.*' => ['integer', 'exists:shops,id'],
            'godowns' => ['nullable', 'array'], 'godowns.*' => ['integer', 'exists:godowns,id'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }

    private function syncLocations(User $user, Request $request): void
    {
        $user->shops()->sync($request->input('shops', []));
        $user->godowns()->sync($request->input('godowns', []));
    }
}
