<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Security Check' }} | Cholavin ERP</title>
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
            <span class="auth-kicker"><i class="{{ $icon ?? 'ri-shield-keyhole-line' }}"></i> {{ $eyebrow ?? 'Secure access' }}</span>
            <h3 class="mt-2">{{ $title ?? 'Security check required' }}</h3>
            <p class="text-muted">{{ $message ?? 'Follow the instructions from your administrator to continue.' }}</p>
            @isset($enabled)
                <div class="auth-policy-status {{ $enabled ? 'is-enabled' : '' }}"><i class="{{ $enabled ? 'ri-shield-check-line' : 'ri-information-line' }}"></i>{{ $enabled ? 'Security policy enabled' : 'Optional policy not enabled' }}</div>
            @endisset
            @if(in_array($challengeType ?? null, ['otp', 'two-factor'], true))
                <div class="auth-code-preview" aria-label="Six digit verification code">
                    @for($digit = 0; $digit < 6; $digit++)<span aria-hidden="true">•</span>@endfor
                </div>
                <p class="auth-policy-help">Verification becomes interactive only after a secure OTP or authenticator provider is configured.</p>
            @elseif(($challengeType ?? null) === 'device')
                <div class="auth-device-summary"><i class="ri-computer-line"></i><span><strong>Current request</strong><small>{{ request()->ip() }} · {{ str(request()->userAgent() ?: 'Unknown browser')->limit(72) }}</small></span></div>
            @endif
            @if(session('retry_after'))<div class="alert alert-warning auth-status">Try again in approximately {{ session('retry_after') }} seconds.</div>@endif
            @if(!empty($steps))
                <div class="auth-state-grid">
                    @foreach($steps as $step)
                        <div class="auth-state-item">
                            <i class="{{ $step['icon'] ?? 'ri-checkbox-circle-line' }}"></i>
                            <span><strong>{{ $step['title'] }}</strong><small>{{ $step['description'] }}</small></span>
                        </div>
                    @endforeach
                </div>
            @endif
            <a class="btn btn-auth mt-4" href="{{ route('admin.auth.index') }}">Back to sign in</a>
            <div class="auth-security-note"><i class="ri-customer-service-2-line"></i><span>Need help? Contact your ERP administrator or Cholavin support before retrying.</span></div>
        </div>
    </div>
</main>
</body>
</html>
