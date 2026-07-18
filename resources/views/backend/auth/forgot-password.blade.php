<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Forgot Password | Cholavin ERP</title>
    <link rel="icon" type="image/png" href="{{ asset('frontend/assets/img/logo/favicon.png') }}">
    @include('shared.google-fonts')
    <link href="{{ asset('backend/assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('backend/assets/css/icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('backend/assets/css/auth.css') }}?v={{ filemtime(public_path('backend/assets/css/auth.css')) }}" rel="stylesheet">
    <link href="{{ asset('shared/assets/css/cholavin-fonts.css') }}?v={{ filemtime(public_path('shared/assets/css/cholavin-fonts.css')) }}" rel="stylesheet">
</head>
<body>
<main class="container">
    <div class="auth-card">
        <div class="auth-card-body">
            <img class="auth-logo mb-4" src="{{ asset($commonSettings['brand_logo'] ?? 'frontend/assets/img/logo/logo-hm62.png') }}" alt="Cholavin">
            <span class="auth-kicker"><i class="ri-key-2-line"></i> Secure recovery</span>
            <h3 class="mt-2">Reset your password</h3>
            <p class="text-muted">Enter the email registered by your administrator. A secure, time-limited link will be sent without revealing whether an account exists.</p>
            @if(session('status'))<div class="alert alert-success auth-status" role="status">{{ session('status') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger auth-status">{{ $errors->first() }}</div>@endif
            <div id="auth-form-feedback" class="alert alert-danger auth-status d-none" role="alert" aria-live="polite"></div>
            <form id="forgot-password-form" data-auth-ajax method="POST" action="{{ route('admin.password.email') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="email">Email address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus autocomplete="email">
                </div>
                <button class="btn btn-auth" data-loading-label="Sending reset link...">Send reset link</button>
            </form>
            <a class="d-block text-center mt-3" href="{{ route('admin.auth.index') }}">Back to sign in</a>
            <div class="auth-security-note"><i class="ri-time-line"></i><span>Password reset links are single-use and expire automatically.</span></div>
        </div>
    </div>
</main>
<script src="{{ asset('backend/assets/vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('backend/assets/js/erp-common.js') }}"></script>
<script src="{{ asset('backend/assets/js/auth-login.js') }}?v={{ filemtime(public_path('backend/assets/js/auth-login.js')) }}"></script>
</body>
</html>
