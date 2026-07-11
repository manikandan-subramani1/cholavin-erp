@extends('backend.layouts.app')

@section('title', 'Dashboard | Cholavin ERP')

@section('content')
    <div class="erp-welcome mb-4">
        <div>
            <span class="erp-eyebrow">Cholavin ERP workspace</span>
            <h2>Welcome back, {{ auth()->user()->name }}</h2>
            <p>Manage today’s operations for <strong>{{ $headerShops->firstWhere('id', $activeShopId)?->name ?? 'your business' }}</strong> with the shortcuts below.</p>
        </div>
        <div class="erp-welcome-mark"><i class="ri-seedling-line"></i></div>
    </div>

    <div class="row g-3 mb-4">
        @foreach ($metrics as $metric)
            @if (is_array($metric['ability']) ? auth()->user()->can($metric['ability'][0], $metric['ability'][1]) : auth()->user()->can($metric['ability']))
                <div class="col-xl-3 col-md-6">
                    <a href="{{ route($metric['route']) }}" class="erp-stat-card">
                        <span class="erp-stat-icon"><i class="{{ $metric['icon'] }}"></i></span>
                        <span><small>{{ $metric['label'] }}</small><strong>{{ number_format($metric['value']) }}</strong></span>
                        <i class="ri-arrow-right-up-line erp-stat-arrow"></i>
                    </a>
                </div>
            @endif
        @endforeach
    </div>

    <div class="row g-4">
        <div class="col-xl-7">
            <div class="card erp-panel h-100">
                <div class="card-header"><div><span class="erp-eyebrow">Setup health</span><h5 class="mb-0">Master data readiness</h5></div></div>
                <div class="card-body">
                    @foreach ($masterChecks as $check)
                        @php($complete = $check['count'] >= ($check['expected'] ?? 1))
                        <div class="erp-readiness-row">
                            <span class="erp-status-dot {{ $complete ? 'is-ready' : 'is-missing' }}"></span>
                            <div class="flex-grow-1"><strong>{{ $check['label'] }}</strong><p>{{ $complete ? 'Ready for use.' : $check['message'] }}</p></div>
                            <span class="erp-count {{ $complete ? 'is-ready' : 'is-missing' }}">{{ $check['count'] }}/{{ $check['expected'] ?? 1 }}</span>
                            @unless ($complete)
                                @if (is_array($check['ability']) ? auth()->user()->can($check['ability'][0], $check['ability'][1]) : auth()->user()->can($check['ability']))
                                    <a href="{{ route($check['route']) }}" class="btn btn-sm btn-brand">Fix now <i class="ri-arrow-right-line"></i></a>
                                @endif
                            @endunless
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card erp-panel h-100">
                <div class="card-header"><span class="erp-eyebrow">Shortcuts</span><h5 class="mb-0">Quick actions</h5></div>
                <div class="card-body erp-actions">
                    @can('create', App\Models\Product::class)<a href="{{ route('admin.products.create') }}"><i class="ri-add-box-line"></i><span><strong>Add product</strong><small>Create a sellable item</small></span></a>@endcan
                    @can('users.create')<a href="{{ route('admin.users.index') }}"><i class="ri-user-add-line"></i><span><strong>Add staff user</strong><small>Assign role and locations</small></span></a>@endcan
                    @can('shops.create')<a href="{{ route('admin.locations.index') }}"><i class="ri-store-2-line"></i><span><strong>Add location</strong><small>Create shop or godown</small></span></a>@endcan
                    @can('settings.update')<a href="{{ route('admin.settings.index') }}"><i class="ri-settings-4-line"></i><span><strong>Company settings</strong><small>Brand, contacts and mail</small></span></a>@endcan
                </div>
            </div>
        </div>
    </div>
@endsection
