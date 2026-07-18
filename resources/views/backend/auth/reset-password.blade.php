<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Set Password | Cholavin ERP</title>
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
            <span class="auth-kicker"><i class="ri-lock-password-line"></i> New secure password</span>
            <h3 class="mt-2">Set a new password</h3>
            <p class="text-muted">Use at least 8 characters with letters and numbers. Your old password will stop working immediately.</p>
            @if($errors->any())<div class="alert alert-danger auth-status">{{ $errors->first() }}</div>@endif
            <div id="auth-form-feedback" class="alert alert-danger auth-status d-none" role="alert" aria-live="polite"></div>
            <form id="reset-password-form" data-auth-ajax method="POST" action="{{ route('admin.password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email',$email) }}" class="form-control" required autocomplete="email">
                </div>
                <div class="mb-3 auth-password-group">
                    <label class="form-label" for="password">New password</label>
                    <input id="password" type="password" name="password" class="form-control" required autocomplete="new-password">
                    <button type="button" class="auth-password-toggle" data-auth-password-toggle="#password" aria-label="Show password"><i class="ri-eye-line"></i></button>
                </div>
                <div class="mb-3 auth-password-group">
                    <label class="form-label" for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
                    <button type="button" class="auth-password-toggle" data-auth-password-toggle="#password_confirmation" aria-label="Show password"><i class="ri-eye-line"></i></button>
                </div>
                <button class="btn btn-auth" data-loading-label="Updating password...">Update password</button>
            </form>
        </div>
    </div>
</main>
<script src="{{ asset('backend/assets/vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('backend/assets/js/erp-common.js') }}"></script>
<script src="{{ asset('backend/assets/js/auth-login.js') }}?v={{ filemtime(public_path('backend/assets/js/auth-login.js')) }}"></script>
</body>
</html>
