@extends('backend.layouts.app')
@section('title', 'Shops & Godowns | Cholavin ERP')
@section('content')
<div class="page-title-box"><h4>Shops & Godowns</h4></div>@include('backend.access.partials.alerts')
<div class="row"><div class="col-lg-6">
@can('shops.create')<div class="card"><div class="card-header"><h5>Create Shop</h5></div><div class="card-body"><form method="post" action="{{ route('admin.shops.store') }}" class="row g-2">@csrf<div class="col-6"><input name="name" class="form-control" placeholder="Shop name" required></div><div class="col-6"><input name="code" class="form-control" placeholder="Code" required></div><div class="col-12"><textarea name="address" class="form-control" placeholder="Address"></textarea></div><div><button class="btn btn-primary">Add shop</button></div></form></div></div>@endcan
<div class="card"><div class="card-header"><h5>Shops</h5></div><div class="card-body">@foreach($shops as $shop)<form method="post" action="{{ route('admin.shops.update', $shop) }}" class="border rounded p-3 mb-2">@csrf @method('PUT')<div class="row g-2"><div class="col-6"><input name="name" value="{{ $shop->name }}" class="form-control" required></div><div class="col-6"><input name="code" value="{{ $shop->code }}" class="form-control" required></div><div class="col-12"><textarea name="address" class="form-control">{{ $shop->address }}</textarea></div></div><small>{{ $shop->users_count }} users · {{ $shop->godowns_count }} godowns</small><input type="hidden" name="is_active" value="0"><label class="ms-2"><input type="checkbox" name="is_active" value="1" @checked($shop->is_active)> Active</label>@can('shops.update')<button class="btn btn-sm btn-success ms-2">Save</button>@endcan</form>
@can('shops.delete')
    @if(!$shop->users_count && !$shop->godowns_count)
        <form method="post" action="{{ route('admin.shops.destroy', $shop) }}" class="mb-3">@csrf @method('DELETE')<button class="btn btn-sm btn-soft-danger">Delete {{ $shop->name }}</button></form>
    @endif
@endcan
@endforeach</div></div>
</div><div class="col-lg-6">
@can('godowns.create')<div class="card"><div class="card-header"><h5>Create Godown</h5></div><div class="card-body"><form method="post" action="{{ route('admin.godowns.store') }}" class="row g-2">@csrf<div class="col-6"><input name="name" class="form-control" placeholder="Godown name" required></div><div class="col-6"><input name="code" class="form-control" placeholder="Code" required></div><div class="col-6"><select name="shop_id" class="form-select"><option value="">Independent</option>@foreach($shops as $shop)<option value="{{ $shop->id }}">{{ $shop->name }}</option>@endforeach</select></div><div class="col-6"><input name="address" class="form-control" placeholder="Address"></div><div><button class="btn btn-primary">Add godown</button></div></form></div></div>@endcan
<div class="card"><div class="card-header"><h5>Godowns</h5></div><div class="card-body">@foreach($godowns as $godown)<form method="post" action="{{ route('admin.godowns.update', $godown) }}" class="border rounded p-3 mb-2">@csrf @method('PUT')<div class="row g-2"><div class="col-6"><input name="name" value="{{ $godown->name }}" class="form-control" required></div><div class="col-6"><input name="code" value="{{ $godown->code }}" class="form-control" required></div><div class="col-6"><select name="shop_id" class="form-select"><option value="">Independent</option>@foreach($shops as $shop)<option value="{{ $shop->id }}" @selected($godown->shop_id === $shop->id)>{{ $shop->name }}</option>@endforeach</select></div><div class="col-6"><input name="address" value="{{ $godown->address }}" class="form-control"></div></div><small>{{ $godown->users_count }} users</small><input type="hidden" name="is_active" value="0"><label class="ms-2"><input type="checkbox" name="is_active" value="1" @checked($godown->is_active)> Active</label>@can('godowns.update')<button class="btn btn-sm btn-success ms-2">Save</button>@endcan</form>
@can('godowns.delete')
    @if(!$godown->users_count)
        <form method="post" action="{{ route('admin.godowns.destroy', $godown) }}" class="mb-3">@csrf @method('DELETE')<button class="btn btn-sm btn-soft-danger">Delete {{ $godown->name }}</button></form>
    @endif
@endcan
@endforeach</div></div>
</div></div>
@endsection
