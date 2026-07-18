<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Select Workspace | Cholavin ERP</title>
    <link rel="icon" type="image/png" href="{{ asset('frontend/assets/img/logo/favicon.png') }}">
    @include('shared.google-fonts')
    <link href="{{ asset('backend/assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('backend/assets/css/icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('backend/assets/css/auth.css') }}?v={{ filemtime(public_path('backend/assets/css/auth.css')) }}" rel="stylesheet">
    <link href="{{ asset('shared/assets/css/cholavin-fonts.css') }}?v={{ filemtime(public_path('shared/assets/css/cholavin-fonts.css')) }}" rel="stylesheet">
</head>
<body>
<main class="container">
    <div class="auth-card auth-context-card">
        <div class="auth-card-body">
            <img class="auth-logo mb-4" src="{{ asset($commonSettings['brand_logo'] ?? 'frontend/assets/img/logo/logo-hm62.png') }}" alt="Cholavin">
            <span class="auth-kicker"><i class="ri-map-pin-user-line"></i> Working context</span>
            <h3 class="mt-2">Choose where you are working</h3>
            <p class="text-muted">Your permissions stay the same. Dashboard, billing, stock, and reports will use this location and financial year.</p>
            <div id="auth-form-feedback" class="alert alert-danger auth-status d-none" role="alert" aria-live="polite"></div>
            @if($shops->isEmpty() || $godowns->isEmpty() || $financialYears->isEmpty())
                <div class="auth-assignment-warning"><i class="ri-error-warning-line"></i><span><strong>Workspace setup is incomplete</strong><small>Ask your administrator to assign an active shop, godown, and financial year.</small></span></div>
            @else
                <form id="auth-context-form" data-auth-ajax data-shops-url="{{ route('admin.auth.context.shops') }}" action="{{ route('admin.auth.context.store') }}" method="post" novalidate>
                    @csrf
                    <div class="auth-context-grid">
                        <div><label class="form-label" for="godown_id">Godown</label><select class="form-select" id="godown_id" name="godown_id" required><option value="">Select godown</option>@foreach($godowns as $godown)<option value="{{ $godown->id }}">{{ $godown->name }} ({{ $godown->code }})</option>@endforeach</select></div>
                        <div><label class="form-label" for="shop_id">Shop</label><select class="form-select" id="shop_id" name="shop_id" required><option value="">Select shop</option>@foreach($shops as $shop)<option value="{{ $shop->id }}">{{ $shop->name }} ({{ $shop->code }})</option>@endforeach</select></div>
                        <div><label class="form-label" for="financial_year_id">Financial year</label><select class="form-select" id="financial_year_id" name="financial_year_id" required><option value="">Select financial year</option>@foreach($financialYears as $year)<option value="{{ $year->id }}">{{ $year->name }} ({{ $year->code }})</option>@endforeach</select></div>
                    </div>
                    <button class="btn btn-auth mt-4" type="submit" data-loading-label="Opening workspace...">Continue to workspace <i class="ri-arrow-right-line ms-1"></i></button>
                </form>
            @endif
            <form class="mt-3 text-center" method="post" action="{{ route('admin.logout') }}">@csrf<button class="btn btn-link" type="submit">Sign in with another account</button></form>
        </div>
    </div>
</main>
<script src="{{ asset('backend/assets/vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('backend/assets/js/erp-common.js') }}"></script>
<script src="{{ asset('backend/assets/js/auth-login.js') }}?v={{ filemtime(public_path('backend/assets/js/auth-login.js')) }}"></script>
</body>
</html>
