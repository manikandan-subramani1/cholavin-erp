<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg"
    data-sidebar-image="none" data-preloader="disable" data-theme="default" data-theme-colors="default">

<head>

    <meta charset="utf-8" />
    <title>@yield('title', 'Billing Dashboard | Cholavin ERP')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta content="Cholavin ERP billing software interface" name="description" />
    <meta content="Cholavin" name="author" />
    @include('shared.google-fonts')
    <link rel="shortcut icon" href="{{ asset('backend/assets/images/favicon.ico') }}">
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('backend/assets/images/favicon.ico') }}">

    <!-- jsvectormap css -->
    <link href="{{ asset('backend/assets/libs/jsvectormap/css/jsvectormap.min.css') }}" rel="stylesheet"
        type="text/css" />

    <!--Swiper slider css-->
    <link href="{{ asset('backend/assets/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- Layout config Js -->
    <script src="{{ asset('backend/assets/js/layout.js') }}"></script>
    <!-- Bootstrap Css -->
    <link href="{{ asset('backend/assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Shared offline ERP dependencies -->
    <link href="{{ asset('backend/assets/vendor/datatables/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/assets/vendor/datatables/responsive.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/assets/vendor/toastr/toastr.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="{{ asset('backend/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="{{ asset('backend/assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- custom Css-->
    <link href="{{ asset('backend/assets/css/custom.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/assets/css/cholavin-erp.css') }}?v={{ filemtime(public_path('backend/assets/css/cholavin-erp.css')) }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('shared/assets/css/cholavin-fonts.css') }}?v={{ filemtime(public_path('shared/assets/css/cholavin-fonts.css')) }}" rel="stylesheet" type="text/css" />
    @stack('styles')
    <style>
        :root { --cholavin-maroon:#5e001b; --cholavin-deep:#3b0010; --cholavin-red:#8f0028; --cholavin-gold:#f4c430; --cholavin-ivory:#fff8e6; }
        body { background:#f8f5f2; color:#30272a; }
        .app-menu.navbar-menu { background:linear-gradient(180deg,var(--cholavin-deep),#25000b)!important; border-right:0; }
        .navbar-menu .navbar-nav .nav-link { color:#eadde1; border-radius:9px; margin:2px 10px; }
        .navbar-menu .navbar-nav .nav-link:hover,.navbar-menu .navbar-nav .nav-link.active { color:var(--cholavin-gold); background:rgba(244,196,48,.11); }
        .navbar-menu .navbar-nav .nav-link i { color:#d9a925; }.menu-title span{color:#a98d94!important}.navbar-brand-box{background:var(--cholavin-deep)!important}
        .erp-brand-logo{max-height:47px;max-width:175px;object-fit:contain}.brand-glyph{font-size:26px;color:var(--cholavin-gold)}
        #page-topbar{border-bottom:1px solid #eadfe2;box-shadow:0 4px 18px rgba(59,0,16,.06)}.page-content{background:#f8f5f2}
        .btn-brand{background:var(--cholavin-red);border-color:var(--cholavin-red);color:#fff}.btn-brand:hover{background:var(--cholavin-deep);color:var(--cholavin-gold)}.btn-outline-brand{border-color:var(--cholavin-red);color:var(--cholavin-red)}
        .erp-eyebrow{display:block;color:#a77400;font-size:11px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;margin-bottom:5px}
        .erp-welcome{display:flex;justify-content:space-between;align-items:center;padding:28px 32px;border-radius:16px;color:#fff;background:linear-gradient(115deg,var(--cholavin-deep),var(--cholavin-red));box-shadow:0 16px 36px rgba(94,0,27,.18)}.erp-welcome h2{color:#fff;margin:4px 0 8px}.erp-welcome p{margin:0;color:#ead9de}.erp-welcome .erp-eyebrow{color:var(--cholavin-gold)}.erp-welcome-mark{font-size:56px;color:var(--cholavin-gold);opacity:.85}
        .erp-stat-card{position:relative;display:flex;align-items:center;gap:15px;padding:22px;background:#fff;border:1px solid #eee5e6;border-radius:14px;color:#33272a;box-shadow:0 8px 24px rgba(59,0,16,.05);transition:.2s}.erp-stat-card:hover{transform:translateY(-3px);border-color:#d9b44a;color:var(--cholavin-maroon)}.erp-stat-icon{width:46px;height:46px;display:grid;place-items:center;border-radius:12px;background:var(--cholavin-ivory);color:var(--cholavin-red);font-size:22px}.erp-stat-card small,.erp-stat-card strong{display:block}.erp-stat-card strong{font-size:25px}.erp-stat-arrow{margin-left:auto}
        .erp-panel{border:1px solid #eee5e6;border-radius:14px;box-shadow:0 8px 24px rgba(59,0,16,.04)}.erp-panel .card-header{background:#fff;border-bottom:1px solid #f0e7e8;padding:20px 22px}
        .erp-readiness-row{display:flex;align-items:center;gap:13px;padding:15px 0;border-bottom:1px solid #f2eaeb}.erp-readiness-row:last-child{border:0}.erp-readiness-row p{margin:2px 0 0;color:#817579;font-size:13px}.erp-status-dot{width:10px;height:10px;border-radius:50%}.is-ready{color:#198754}.erp-status-dot.is-ready{background:#198754}.is-missing{color:#b42318}.erp-status-dot.is-missing{background:#dc3545}.erp-count{font-weight:800}
        .erp-actions{display:grid;gap:11px}.erp-actions>a{display:flex;align-items:center;gap:14px;padding:15px;border:1px solid #eee5e6;border-radius:11px;color:#392b2f}.erp-actions>a:hover{border-color:#d4af37;background:#fffaf0}.erp-actions i{font-size:22px;color:var(--cholavin-red)}.erp-actions strong,.erp-actions small{display:block}.erp-actions small{color:#8b7e82}
        .brand-preview{height:130px;margin-top:10px;border:1px solid #e8dddf;border-radius:10px;display:grid;place-items:center;overflow:hidden;background:#faf7f7}.brand-preview-dark{background:var(--cholavin-deep)}.brand-preview img{max-width:90%;max-height:110px;object-fit:contain}.branch-mini{padding:12px 0;border-bottom:1px solid #eee}.branch-mini small{float:right;color:#9a7d00}.branch-mini p{margin:4px 0 0;color:#82767a;font-size:13px}.erp-empty{text-align:center;padding:25px;color:#887c80}.erp-empty i{font-size:30px}
    </style>

</head>

<body>

    <!-- Begin page -->
    <div id="layout-wrapper">

        @include('backend.layouts.header')

        <!-- ========== App Menu ========== -->
        @include('backend.layouts.menu')

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content"> 
            <div class="page-content">
                <div class="container-fluid">
                    @yield('content') 
                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->
            @include('backend.layouts.footer')
        </div>
        <!-- end main content-->

    </div>
    <!-- END layout-wrapper --> 
    @include('backend.layouts.theme-settings') 
    <!-- JAVASCRIPT -->
    <script src="{{ asset('backend/assets/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('backend/assets/vendor/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('backend/assets/vendor/jquery-validation/additional-methods.min.js') }}"></script>
    <script src="{{ asset('backend/assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('backend/assets/vendor/datatables/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('backend/assets/vendor/datatables/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('backend/assets/vendor/datatables/responsive.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('backend/assets/vendor/toastr/toastr.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/feather-icons/feather.min.js') }}"></script>
    <script src="{{ asset('backend/assets/js/pages/plugins/lord-icon-2.1.0.js') }}"></script>
    <script src="{{ asset('backend/assets/js/plugins.js') }}"></script>

    <!-- apexcharts -->
    <script src="{{ asset('backend/assets/libs/apexcharts/apexcharts.min.js') }}"></script>

    <!-- Vector map-->
    <script src="{{ asset('backend/assets/libs/jsvectormap/js/jsvectormap.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/jsvectormap/maps/world-merc.js') }}"></script>

    <!--Swiper slider js-->
    <script src="{{ asset('backend/assets/libs/swiper/swiper-bundle.min.js') }}"></script>

    <!-- Dashboard init -->
    <script src="{{ asset('backend/assets/js/pages/dashboard-ecommerce.init.js') }}"></script>

    <!-- App js -->
    <script src="{{ asset('backend/assets/js/app.js') }}"></script>
    <script src="{{ asset('backend/assets/js/erp-common.js') }}?v={{ filemtime(public_path('backend/assets/js/erp-common.js')) }}"></script>
    <script src="{{ asset('backend/assets/js/module-workspace.js') }}?v={{ filemtime(public_path('backend/assets/js/module-workspace.js')) }}"></script>
    <script src="{{ asset('backend/assets/js/header-context.js') }}?v={{ filemtime(public_path('backend/assets/js/header-context.js')) }}"></script>
    @stack('scripts')
</body>

</html>
