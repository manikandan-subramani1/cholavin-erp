<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Forgot Password | Cholavin ERP</title>
    <link rel="icon" type="image/png" href="{{ asset('frontend/assets/img/logo/favicon.png') }}">
    @include('shared.google-fonts')
    <link href="{{ asset('backend/assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('backend/assets/css/cholavin-erp.css') }}" rel="stylesheet">
    <link href="{{ asset('shared/assets/css/cholavin-fonts.css') }}?v={{ filemtime(public_path('shared/assets/css/cholavin-fonts.css')) }}" rel="stylesheet">
    <style>
        body{background:#f8f4f1}.auth-card{max-width:460px;margin:8vh auto;border:0;border-radius:16px;box-shadow:0 18px 45px #3b001018}.auth-logo{max-width:230px;max-height:76px}.btn-auth{background:#8f0028;color:#fff}.btn-auth:hover{background:#3b0010;color:#f4c430}
    </style>
</head>
<body>
<main class="container">
    <div class="card auth-card">
        <div class="card-body p-5">
            <img class="auth-logo mb-4" src="{{ asset($commonSettings['brand_logo'] ?? 'frontend/assets/img/logo/logo-hm62.png') }}" alt="Cholavin">
            <h3>Reset your password</h3>
            <p class="text-muted">Enter the email registered by your administrator. A secure, time-limited link will be sent to you.</p>
            @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('admin.password.email') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Email address</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
                </div>
                <button class="btn btn-auth w-100">Send reset link</button>
            </form>
            <a class="d-block text-center mt-3" href="{{ route('admin.auth.index') }}">Back to sign in</a>
        </div>
    </div>
</main>
</body>
</html>
