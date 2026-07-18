<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Sign In | {{ $commonSettings['company_name'] ?? 'Cholavin' }} ERP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('frontend/assets/img/logo/favicon.png') }}">
    @include('shared.google-fonts')
    <link href="{{ asset('backend/assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('backend/assets/css/icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('backend/assets/vendor/toastr/toastr.min.css') }}" rel="stylesheet">
    <link href="{{ asset('backend/assets/css/auth.css') }}?v={{ filemtime(public_path('backend/assets/css/auth.css')) }}" rel="stylesheet">
    <link href="{{ asset('shared/assets/css/cholavin-fonts.css') }}?v={{ filemtime(public_path('shared/assets/css/cholavin-fonts.css')) }}" rel="stylesheet">
</head>
<body>
@php
    $supportPhone = $commonSettings['contact_phone'] ?? '+91 99652 52555';
    $supportEmail = $commonSettings['contact_email'] ?? config('mail.from.address');
@endphp
<main class="auth-shell">
    <section class="auth-brand" style="--auth-hero-image: url('{{ asset($commonSettings['auth_brand_image'] ?? 'frontend/assets/img/logo/logo-hm64.png') }}')">
        <div class="auth-brand-content">
            <img src="{{ asset($commonSettings['brand_logo'] ?? 'frontend/assets/img/logo/logo-hm62.png') }}" alt="{{ $commonSettings['company_name'] ?? 'Cholavin' }}">
            <span class="auth-kicker"><i class="ri-shield-check-line"></i> Secure business workspace</span>
            <h1>One system. Every shop. Complete control.</h1>
            <p>Access billing, inventory, customer operations, and reports according to your assigned role, godown, shop, and financial year.</p>
            <ul class="auth-feature-list">
                <li><i class="ri-checkbox-circle-line"></i> Role, module, and action permissions are checked on every request.</li>
                <li><i class="ri-map-pin-2-line"></i> Assigned godown and shop context loads automatically after login.</li>
                <li><i class="ri-flashlight-line"></i> AJAX-powered workflow keeps billing counters fast.</li>
            </ul>
        </div>
    </section>
    <section class="auth-form-panel">
        <div class="auth-form">
            <div class="auth-mobile-logo"><img src="{{ asset($commonSettings['brand_logo'] ?? 'frontend/assets/img/logo/logo-hm62.png') }}" alt="Brand logo"></div>
            <span class="auth-kicker">{{ $commonSettings['company_name'] ?? 'Cholavin' }} ERP</span>
            <h2>Welcome back</h2>
            <p class="lead">Sign in with your username, email address, or mobile number.</p>
            @if (session('status'))<div class="alert alert-success auth-status" role="status">{{ session('status') }}</div>@endif
            @if ($errors->any())<div class="alert alert-danger auth-status">{{ $errors->first() }}</div>@endif
            <div id="login-feedback" class="alert alert-danger auth-status d-none" role="alert" aria-live="polite"></div>
            <form id="login-form" action="{{ route('admin.auth.login') }}" method="post" data-locked-url="{{ route('admin.auth.locked') }}" novalidate>
                @csrf
                <div class="mb-3">
                    <label for="login" class="form-label">Username / Email / Mobile</label>
                    <input type="text" name="login" value="{{ old('login') }}" class="form-control @error('login') is-invalid @enderror" id="login" placeholder="Enter your login ID" required autofocus autocomplete="username">
                </div>
                <div class="mb-2 auth-password-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="password" placeholder="Enter your password" required autocomplete="current-password">
                    <button type="button" class="auth-password-toggle" data-auth-password-toggle="#password" aria-label="Show password"><i class="ri-eye-line"></i></button>
                </div>
                @if(config('erp_auth.captcha.enabled'))
                    <div class="mb-3">
                        <label for="captcha" class="form-label">Security check: {{ session('auth_captcha_question') }} = ?</label>
                        <input type="number" name="captcha" class="form-control @error('captcha') is-invalid @enderror" id="captcha" required inputmode="numeric" autocomplete="off" placeholder="Enter answer">
                    </div>
                @endif
                <div class="auth-meta">
                    <label class="form-check-label"><input class="form-check-input me-1" name="remember" value="1" type="checkbox"> Remember me</label>
                    <a class="small" href="{{ route('admin.password.request') }}">Forgot password?</a>
                </div>
                <button class="btn btn-login" type="submit" data-loading-label="Signing in...">Sign in to ERP <i class="ri-arrow-right-line ms-1"></i></button>
            </form>
            <div class="auth-security-note"><i class="ri-lock-password-line"></i><span>Access is monitored and restricted by role, shop, godown, action permission, and financial-year context.</span></div>
            <div class="auth-support">
                <span><i class="ri-phone-line me-1"></i>{{ $supportPhone }}</span>
                <span><i class="ri-mail-line me-1"></i>{{ $supportEmail }}</span>
            </div>
            <div class="auth-version">Version {{ config('app.version', '1.0.0') }}</div>
        </div>
    </section>
</main>
<script src="{{ asset('backend/assets/vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('backend/assets/vendor/jquery-validation/jquery.validate.min.js') }}"></script>
<script src="{{ asset('backend/assets/vendor/toastr/toastr.min.js') }}"></script>
<script src="{{ asset('backend/assets/js/erp-common.js') }}"></script>
<script src="{{ asset('backend/assets/js/auth-login.js') }}?v={{ filemtime(public_path('backend/assets/js/auth-login.js')) }}"></script>
</body>
</html>
