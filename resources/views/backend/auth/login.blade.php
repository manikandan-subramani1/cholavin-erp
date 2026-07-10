<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable" data-theme="default" data-theme-colors="default">
<head>
    <meta charset="utf-8" />
    <title>Sign In | Cholavin ERP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Cholavin ERP login UI" name="description" />
    <link rel="shortcut icon" href="{{ asset('backend/assets/images/favicon.ico') }}">
    <script src="{{ asset('backend/assets/js/layout.js') }}"></script>
    <link href="{{ asset('backend/assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/assets/css/custom.min.css') }}" rel="stylesheet" type="text/css" />
</head>
<body>
    <div class="auth-page-wrapper pt-5">
        <div class="auth-one-bg-position auth-one-bg" id="auth-particles"><div class="bg-overlay"></div><div class="shape"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 120"><path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path></svg></div></div>
        <div class="auth-page-content"><div class="container"><div class="row"><div class="col-lg-12"><div class="text-center mt-sm-5 mb-4 text-white-50"><a href="{{ route('admin.dashboard') }}" class="d-inline-block auth-logo"><img src="{{ asset('backend/assets/images/logo-light.png') }}" alt="" height="20"></a><p class="mt-3 fs-15 fw-medium">Cholavin Billing Software</p></div></div></div><div class="row justify-content-center"><div class="col-md-8 col-lg-6 col-xl-5"><div class="card mt-4 card-bg-fill"><div class="card-body p-4"><div class="text-center mt-2"><h5 class="text-primary">Welcome Back</h5><p class="text-muted">Sign in to continue to the billing dashboard.</p></div><div class="p-2 mt-4"><form action="{{ route('admin.dashboard') }}" method="get"><div class="mb-3"><label for="username" class="form-label">Username</label><input type="text" class="form-control" id="username" placeholder="Enter username"></div><div class="mb-3"><div class="float-end"><a href="#" class="text-muted">Forgot password?</a></div><label class="form-label" for="password-input">Password</label><div class="position-relative auth-pass-inputgroup mb-3"><input type="password" class="form-control pe-5 password-input" placeholder="Enter password" id="password-input"><button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon material-shadow-none" type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i></button></div></div><div class="form-check"><input class="form-check-input" type="checkbox" id="auth-remember-check"><label class="form-check-label" for="auth-remember-check">Remember me</label></div><div class="mt-4"><button class="btn btn-success w-100" type="submit">Sign In</button></div></form></div></div></div></div></div></div></div>
        <footer class="footer"><div class="container"><div class="row"><div class="col-lg-12"><div class="text-center"><p class="mb-0 text-muted">&copy; <script>document.write(new Date().getFullYear())</script> Cholavin ERP.</p></div></div></div></div></footer>
    </div>
    <script src="{{ asset('backend/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/feather-icons/feather.min.js') }}"></script>
    <script src="{{ asset('backend/assets/js/pages/plugins/lord-icon-2.1.0.js') }}"></script>
    <script src="{{ asset('backend/assets/js/plugins.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/particles.js/particles.js') }}"></script>
    <script src="{{ asset('backend/assets/js/pages/particles.app.js') }}"></script>
    <script src="{{ asset('backend/assets/js/pages/password-addon.init.js') }}"></script>
</body>
</html>
