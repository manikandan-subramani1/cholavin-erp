@extends('backend.layouts.app')

@section('title', 'Roles & Permissions | Cholavin ERP')

@section('content')
    <div class="page-title-box">
        <h4>Roles & Permissions</h4>
    </div>

    @include('backend.access.partials.alerts')

    @can('roles.create')
        <div class="card">
            <div class="card-body">
                <form method="post" action="{{ route('admin.roles.store') }}" class="row g-2">
                    @csrf
                    <div class="col-md-4">
                        <input name="name" class="form-control" placeholder="New role name" required>
                    </div>
                    <div class="col-md-8">
                        <button class="btn btn-primary">Create Role</button>
                    </div>
                </form>
            </div>
        </div>
    @endcan

    @foreach ($roles as $role)
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5>
                    {{ $role->name }}
                    @if ($role->is_super_admin)
                        <span class="badge bg-danger">Full access</span>
                    @endif
                </h5>
                <small>{{ $role->users->count() }} users</small>
            </div>

            <div class="card-body">
                <form method="post" action="{{ route('admin.roles.update', $role) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Role name</label>
                            <input name="name" value="{{ $role->name }}" class="form-control"
                                @disabled($role->is_super_admin) required>

                            <input type="hidden" name="is_active" value="0">
                            <label class="mt-2">
                                <input type="checkbox" name="is_active" value="1"
                                    @checked($role->is_active) @disabled($role->is_super_admin)>
                                Active
                            </label>
                        </div>

                        <div class="col-md-8">
                            @if ($role->is_super_admin)
                                <p class="text-muted">The Super Admin bypasses all gates and policies.</p>
                            @else
                                @foreach ($permissions as $module => $items)
                                    <div class="mb-3">
                                        <strong class="text-capitalize">{{ str($module)->replace('-', ' ') }}</strong>
                                        <div class="d-flex flex-wrap gap-3">
                                            @foreach ($items as $permission)
                                                <label>
                                                    <input type="checkbox" name="permissions[]"
                                                        value="{{ $permission->id }}"
                                                        @checked($role->permissions->contains($permission))>
                                                    {{ ucfirst($permission->action) }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    @can('roles.update')
                        @unless ($role->is_super_admin)
                            <button class="btn btn-success">Save permissions</button>
                        @endunless
                    @endcan
                </form>

                @can('roles.delete')
                    @unless ($role->is_super_admin || $role->users->isNotEmpty())
                        <form method="post" action="{{ route('admin.roles.destroy', $role) }}" class="d-inline"
                            onsubmit="return confirm('Delete this role?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-soft-danger mt-2">Delete role</button>
                        </form>
                    @endunless
                @endcan
            </div>
        </div>
    @endforeach
@endsection
