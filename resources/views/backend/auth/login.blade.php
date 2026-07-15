<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Sign In | {{ $commonSettings['company_name'] ?? 'Cholavin' }} ERP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('shared.google-fonts')
    <link href="{{ asset('backend/assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('backend/assets/css/icons.min.css') }}" rel="stylesheet">
    <style>
        :root{--maroon:#5e001b;--deep:#3b0010;--red:#8f0028;--gold:#f4c430;--ivory:#fff8e6}*{box-sizing:border-box}body{margin:0;background:#f8f4f1;color:#35292d;font-family:Inter,Arial,sans-serif}.auth-shell{min-height:100vh;display:grid;grid-template-columns:minmax(420px,1.05fr) minmax(420px,.95fr)}.auth-brand{position:relative;background:var(--deep);overflow:hidden;display:flex;align-items:flex-end;padding:56px}.auth-brand:before{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(59,0,16,.06),rgba(59,0,16,.78)),url('{{ asset($commonSettings['auth_brand_image'] ?? 'frontend/assets/img/logo/logo-hm64.png') }}') center/cover no-repeat}.auth-brand-content{position:relative;z-index:1;color:#fff;max-width:580px}.auth-brand-content img{width:250px;max-height:90px;object-fit:contain;object-position:left;margin-bottom:32px}.auth-kicker{color:var(--gold);font-weight:800;letter-spacing:.14em;text-transform:uppercase;font-size:12px}.auth-brand h1{font-size:42px;color:#fff;margin:12px 0}.auth-brand p{font-size:17px;color:#eadcdf;line-height:1.7}.auth-form-panel{display:grid;place-items:center;padding:45px;background:linear-gradient(145deg,#fff,#fff8ed)}.auth-form{width:100%;max-width:430px}.auth-form h2{color:var(--deep);font-size:30px;margin-bottom:7px}.auth-form .lead{color:#84777b;font-size:15px;margin-bottom:32px}.form-label{font-weight:700;color:#57484d}.form-control{height:50px;border:1px solid #ded2d5;border-radius:9px}.form-control:focus{border-color:var(--red);box-shadow:0 0 0 .2rem rgba(143,0,40,.1)}.btn-login{height:52px;border:0;border-radius:9px;background:var(--red);color:#fff;font-weight:800;width:100%;box-shadow:0 10px 24px rgba(143,0,40,.18)}.btn-login:hover{background:var(--deep);color:var(--gold)}.auth-meta{display:flex;align-items:center;justify-content:space-between;margin:18px 0 28px}.security-note{margin-top:28px;padding-top:20px;border-top:1px solid #ebdfe1;color:#93878a;font-size:12px}.mobile-logo{display:none;background:var(--deep);border-radius:12px;padding:18px;margin-bottom:25px}.mobile-logo img{width:220px;max-height:65px;object-fit:contain}@media(max-width:900px){.auth-shell{display:block}.auth-brand{display:none}.auth-form-panel{min-height:100vh;padding:28px}.mobile-logo{display:block}}
    </style>
    <link href="{{ asset('shared/assets/css/cholavin-fonts.css') }}?v={{ filemtime(public_path('shared/assets/css/cholavin-fonts.css')) }}" rel="stylesheet">
</head>
<body>
<main class="auth-shell">
    <section class="auth-brand">
        <div class="auth-brand-content">
            <img src="{{ asset($commonSettings['brand_logo'] ?? 'frontend/assets/img/logo/logo-hm62.png') }}" alt="{{ $commonSettings['company_name'] ?? 'Cholavin' }}">
            <span class="auth-kicker">Secure business workspace</span>
            <h1>One system. Every shop. Complete control.</h1>
            <p>Access billing, inventory, customer operations, and reports according to your assigned role and working location.</p>
        </div>
    </section>
    <section class="auth-form-panel">
        <div class="auth-form">
            <div class="mobile-logo"><img src="{{ asset($commonSettings['brand_logo'] ?? 'frontend/assets/img/logo/logo-hm62.png') }}" alt="Brand logo"></div>
            <span class="auth-kicker">{{ $commonSettings['company_name'] ?? 'Cholavin' }} ERP</span>
            <h2>Welcome back</h2>
            <p class="lead">Sign in with your username, email address, or mobile number.</p>
            @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            <form action="{{ route('admin.auth.login') }}" method="post">
                @csrf
                <div class="mb-3"><label for="login" class="form-label">Username / Email / Mobile</label><input type="text" name="login" value="{{ old('login') }}" class="form-control @error('login') is-invalid @enderror" id="login" placeholder="Enter your login ID" required autofocus autocomplete="username"></div>
                <div class="mb-2"><label for="password" class="form-label">Password</label><input type="password" name="password" class="form-control @error('password') is-invalid @enderror" id="password" placeholder="Enter your password" required autocomplete="current-password"></div>
                <div class="auth-meta"><label class="form-check-label"><input class="form-check-input me-1" name="remember" value="1" type="checkbox"> Remember me</label><a class="small" href="{{ route('admin.password.request') }}">Forgot password?</a></div>
                <button class="btn btn-login" type="submit">Sign in to ERP <i class="ri-arrow-right-line ms-1"></i></button>
            </form>
            <div class="security-note">Access is monitored and restricted by role, shop, and godown assignment.</div>
        </div>
    </section>
</main>
</body>
</html>
