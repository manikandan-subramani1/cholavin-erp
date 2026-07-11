@extends('backend.layouts.app')
@section('title', 'Users & Access | Cholavin ERP')
@section('content')
<div class="page-title-box d-flex justify-content-between align-items-center"><h4>Users & Access</h4></div>
@include('backend.access.partials.alerts')
@can('users.create')
<div class="card"><div class="card-header"><h5 class="mb-0">Create User</h5></div><div class="card-body">
<form method="post" action="{{ route('admin.users.store') }}" class="row g-3">@csrf
    <div class="col-md-3"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
    <div class="col-md-3"><label class="form-label">Username</label><input name="username" class="form-control" required></div>
    <div class="col-md-3"><label class="form-label">Email</label><input name="email" type="email" class="form-control" required></div>
    <div class="col-md-3"><label class="form-label">Mobile</label><input name="mobile" class="form-control"></div>
    <div class="col-md-3"><label class="form-label">Password</label><input name="password" type="password" class="form-control" minlength="8" required></div>
    <div class="col-md-3"><label class="form-label">Role</label><select name="role_id" class="form-select" required><option value="">Select</option>@foreach($roles as $role)<option value="{{ $role->id }}">{{ $role->name }}</option>@endforeach</select></div>
    <div class="col-md-3"><label class="form-label">Shops</label><select name="shops[]" class="form-select" multiple>@foreach($shops as $shop)<option value="{{ $shop->id }}">{{ $shop->name }}</option>@endforeach</select></div>
    <div class="col-md-3"><label class="form-label">Godowns</label><select name="godowns[]" class="form-select" multiple>@foreach($godowns as $godown)<option value="{{ $godown->id }}">{{ $godown->name }}{{ $godown->shop ? ' — '.$godown->shop->name : '' }}</option>@endforeach</select></div>
    <div class="col-12"><input type="hidden" name="is_active" value="0"><label><input type="checkbox" name="is_active" value="1" checked> Active</label><button class="btn btn-primary ms-3">Create User</button></div>
</form></div></div>
@endcan
<div class="card"><div class="card-header"><h5 class="mb-0">User Register</h5></div><div class="card-body table-responsive"><table class="table align-middle"><thead><tr><th>User</th><th>Role</th><th>Assigned locations</th><th>Status</th><th>Actions</th></tr></thead><tbody>
@foreach($users as $user)<tr><td><strong>{{ $user->name }}</strong><br><small>{{ $user->username }} · {{ $user->email }}{{ $user->mobile ? ' · '.$user->mobile : '' }}</small></td><td>{{ $user->role?->name ?? 'Unassigned' }}</td><td><small>Shops: {{ $user->shops->pluck('name')->join(', ') ?: 'None' }}<br>Godowns: {{ $user->godowns->pluck('name')->join(', ') ?: 'None' }}</small></td><td><span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span></td><td>
@can('users.update')<button class="btn btn-sm btn-soft-info" data-bs-toggle="collapse" data-bs-target="#edit-user-{{ $user->id }}">Edit access</button>@endcan
@can('users.update')
    <a href="{{ route('admin.users.permissions', $user) }}" class="btn btn-sm btn-soft-primary">Permissions</a>
@endcan
@can('users.delete')
    @unless(auth()->user()->is($user) || $user->isSuperAdmin())
        <form method="post" action="{{ route('admin.users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Delete this user?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-sm btn-soft-danger">Delete</button>
        </form>
    @endunless
@endcan
</td></tr>
@can('users.update')<tr class="collapse" id="edit-user-{{ $user->id }}"><td colspan="5"><form method="post" action="{{ route('admin.users.update', $user) }}" class="row g-2">@csrf @method('PUT')
<div class="col-md-2"><input name="name" value="{{ $user->name }}" class="form-control" required></div><div class="col-md-2"><input name="username" value="{{ $user->username }}" class="form-control" required></div><div class="col-md-2"><input name="email" type="email" value="{{ $user->email }}" class="form-control" required></div><div class="col-md-2"><input name="mobile" value="{{ $user->mobile }}" class="form-control" placeholder="Mobile"></div><div class="col-md-2"><input name="password" type="password" class="form-control" placeholder="New password (optional)"></div>
<div class="col-md-2"><select name="role_id" class="form-select">@foreach($roles as $role)<option value="{{ $role->id }}" @selected($role->id === $user->role_id)>{{ $role->name }}</option>@endforeach</select></div>
<div class="col-md-4"><label>Shops</label><select name="shops[]" multiple class="form-select">@foreach($shops as $shop)<option value="{{ $shop->id }}" @selected($user->shops->contains($shop))>{{ $shop->name }}</option>@endforeach</select></div><div class="col-md-4"><label>Godowns</label><select name="godowns[]" multiple class="form-select">@foreach($godowns as $godown)<option value="{{ $godown->id }}" @selected($user->godowns->contains($godown))>{{ $godown->name }}</option>@endforeach</select></div>
<div class="col-md-4 align-self-end"><input type="hidden" name="is_active" value="0"><label><input type="checkbox" name="is_active" value="1" @checked($user->is_active)> Active</label><button class="btn btn-success ms-3">Save</button></div></form></td></tr>@endcan
@endforeach</tbody></table></div></div>
@endsection
