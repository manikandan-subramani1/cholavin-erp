<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable" data-theme="default" data-theme-colors="default">
<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'Billing Dashboard | Cholavin ERP')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta content="Cholavin ERP billing software interface" name="description" />
    <meta content="Cholavin" name="author" />
    <link rel="shortcut icon" href="{{ asset('backend/assets/images/favicon.ico') }}">
    @stack('styles')
    <script src="{{ asset('backend/assets/js/layout.js') }}"></script>
    <link href="{{ asset('backend/assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/assets/css/custom.min.css') }}" rel="stylesheet" type="text/css" />
</head>
<body>
    <div id="layout-wrapper">
        <header id="page-topbar">
            <div class="layout-width">
                <div class="navbar-header">
                    <div class="d-flex">
                        <div class="navbar-brand-box horizontal-logo">
                            <a href="{{ route('admin.dashboard') }}" class="logo logo-dark"><span class="logo-sm"><img src="{{ asset('backend/assets/images/logo-sm.png') }}" alt="" height="22"></span><span class="logo-lg"><img src="{{ asset('backend/assets/images/logo-dark.png') }}" alt="" height="17"></span></a>
                            <a href="{{ route('admin.dashboard') }}" class="logo logo-light"><span class="logo-sm"><img src="{{ asset('backend/assets/images/logo-sm.png') }}" alt="" height="22"></span><span class="logo-lg"><img src="{{ asset('backend/assets/images/logo-light.png') }}" alt="" height="17"></span></a>
                        </div>
                        <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger material-shadow-none" id="topnav-hamburger-icon"><span class="hamburger-icon"><span></span><span></span><span></span></span></button>
                        <form class="app-search d-none d-md-block"><div class="position-relative"><input type="text" class="form-control" placeholder="Search invoice, customer, product..." autocomplete="off"><span class="mdi mdi-magnify search-widget-icon"></span></div></form>
                    </div>
                    <div class="d-flex align-items-center">
                        <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle light-dark-mode"><i class="bx bx-moon fs-22"></i></button>
                        <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle" data-bs-toggle="offcanvas" data-bs-target="#theme-settings-offcanvas"><i class="ri-settings-3-line fs-22"></i></button>
                        <div class="dropdown ms-sm-3 header-item topbar-user">
                            <button type="button" class="btn material-shadow-none" data-bs-toggle="dropdown"><span class="d-flex align-items-center"><img class="rounded-circle header-profile-user" src="{{ asset('backend/assets/images/users/avatar-1.jpg') }}" alt="Header Avatar"><span class="text-start ms-xl-2"><span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">Admin</span><span class="d-none d-xl-block ms-1 fs-12 user-name-sub-text">Billing Manager</span></span></span></button>
                            <div class="dropdown-menu dropdown-menu-end"><h6 class="dropdown-header">Welcome Admin</h6><a class="dropdown-item" href="{{ route('admin.login') }}"><i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i> <span>Sign out UI</span></a></div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <div class="app-menu navbar-menu">
            <div class="navbar-brand-box">
                <a href="{{ route('admin.dashboard') }}" class="logo logo-dark"><span class="logo-sm"><img src="{{ asset('backend/assets/images/logo-sm.png') }}" alt="" height="22"></span><span class="logo-lg"><img src="{{ asset('backend/assets/images/logo-dark.png') }}" alt="" height="17"></span></a>
                <a href="{{ route('admin.dashboard') }}" class="logo logo-light"><span class="logo-sm"><img src="{{ asset('backend/assets/images/logo-sm.png') }}" alt="" height="22"></span><span class="logo-lg"><img src="{{ asset('backend/assets/images/logo-light.png') }}" alt="" height="17"></span></a>
                <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover"><i class="ri-record-circle-line"></i></button>
            </div>
            <div id="scrollbar"><div class="container-fluid"><ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span>Billing Software</span></li>
                <li class="nav-item"><a class="nav-link menu-link active" href="{{ route('admin.dashboard') }}"><i class="ri-dashboard-2-line"></i> <span>Dashboard</span></a></li>
                <li class="nav-item"><a class="nav-link menu-link" href="#"><i class="ri-file-list-3-line"></i> <span>Invoices</span></a></li>
                <li class="nav-item"><a class="nav-link menu-link" href="{{ route('admin.products.index') }}"><i class="ri-shopping-bag-3-line"></i> <span>Products</span></a></li>
                <li class="nav-item"><a class="nav-link menu-link" href="{{ route('admin.enquiries.index') }}"><i class="ri-message-3-line"></i> <span>Enquiries</span></a></li>
                <li class="nav-item"><a class="nav-link menu-link" href="#"><i class="ri-line-chart-line"></i> <span>Reports</span></a></li>
                <li class="nav-item"><a class="nav-link menu-link" href="#"><i class="ri-settings-4-line"></i> <span>Settings</span></a></li>
            </ul></div></div>
            <div class="sidebar-background"></div>
        </div>
        <div class="vertical-overlay"></div>
        <div class="main-content"><div class="page-content"><div class="container-fluid">@yield('content')</div></div><footer class="footer"><div class="container-fluid"><div class="row"><div class="col-sm-6"><script>document.write(new Date().getFullYear())</script> Cholavin ERP</div><div class="col-sm-6"><div class="text-sm-end d-none d-sm-block">Billing software UI</div></div></div></div></footer></div>
    </div>
    <div class="offcanvas offcanvas-end border-0" tabindex="-1" id="theme-settings-offcanvas"><div class="d-flex align-items-center bg-primary bg-gradient p-3 offcanvas-header"><h5 class="m-0 me-2 text-white">Layout Settings</h5><button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="offcanvas"></button></div><div class="offcanvas-body p-0"><div data-simplebar class="h-100"><div class="p-4"><h6 class="mb-3 fw-semibold text-uppercase">Layout</h6><div class="row gy-3"><div class="col-6"><button class="btn btn-light w-100 border" type="button">Vertical</button></div><div class="col-6"><button class="btn btn-light w-100 border" type="button">Horizontal</button></div></div><h6 class="mt-4 mb-3 fw-semibold text-uppercase">Color Scheme</h6><div class="form-check form-switch mb-2"><input class="form-check-input" type="checkbox" id="layout-mode-dark"><label class="form-check-label" for="layout-mode-dark">Dark mode preview</label></div><h6 class="mt-4 mb-3 fw-semibold text-uppercase">Sidebar Size</h6><select class="form-select"><option>Default</option><option>Compact</option><option>Small icon view</option></select></div></div></div></div>
    <script src="{{ asset('backend/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/feather-icons/feather.min.js') }}"></script>
    <script src="{{ asset('backend/assets/js/pages/plugins/lord-icon-2.1.0.js') }}"></script>
    <script src="{{ asset('backend/assets/js/plugins.js') }}"></script>
    <script src="{{ asset('backend/assets/js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
