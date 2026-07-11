@extends('backend.layouts.app')

@section('title', 'User Permissions | Cholavin ERP')

@section('content')
    <div class="page-title-box d-flex align-items-center justify-content-between">
        <div>
            <h4 class="mb-1">User Permissions</h4>
            <p class="text-muted mb-0">{{ $user->name }} — {{ $user->role?->name ?? 'No role' }}</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="btn btn-light">Back to Users</a>
    </div>

    @include('backend.access.partials.alerts')

    @if ($user->isSuperAdmin())
        <div class="alert alert-info">Super Admin always has every permission and cannot be overridden.</div>
    @else
        <form method="post" action="{{ route('admin.users.permissions.update', $user) }}">
            @csrf
            @method('PUT')

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-1">Direct permission overrides</h5>
                    <p class="text-muted mb-0">Inherit uses the role setting. Allow or Deny overrides the role immediately.</p>
                </div>
                <div class="card-body table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Module</th>
                                <th>Action</th>
                                <th>Role access</th>
                                <th>User override</th>
                                <th>Effective access</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($permissions as $module => $items)
                                @foreach ($items as $permission)
                                    @php
                                        $override = $user->permissions->firstWhere('id', $permission->id);
                                        $roleAllows = $user->role?->permissions->contains($permission) ?? false;
                                        $overrideValue = $override ? ($override->pivot->allowed ? 'allow' : 'deny') : 'inherit';
                                        $effective = $override ? (bool) $override->pivot->allowed : $roleAllows;
                                    @endphp
                                    <tr>
                                        <td class="text-capitalize">{{ str($module)->replace('-', ' ') }}</td>
                                        <td>{{ ucfirst($permission->action) }}</td>
                                        <td><span class="badge bg-{{ $roleAllows ? 'success' : 'secondary' }}">{{ $roleAllows ? 'Allowed' : 'Denied' }}</span></td>
                                        <td>
                                            <select name="overrides[{{ $permission->id }}]" class="form-select form-select-sm">
                                                <option value="inherit" @selected($overrideValue === 'inherit')>Inherit role</option>
                                                <option value="allow" @selected($overrideValue === 'allow')>Allow</option>
                                                <option value="deny" @selected($overrideValue === 'deny')>Deny</option>
                                            </select>
                                        </td>
                                        <td><span class="badge bg-{{ $effective ? 'primary' : 'danger' }}">{{ $effective ? 'Allowed' : 'Denied' }}</span></td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <button class="btn btn-primary">Save user permissions</button>
                </div>
            </div>
        </form>
    @endif
@endsection
