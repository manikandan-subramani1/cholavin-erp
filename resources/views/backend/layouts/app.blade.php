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
    <link rel="icon" type="image/png" href="{{ asset('frontend/assets/img/logo/favicon.png') }}">

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
    <link href="{{ asset('backend/assets/vendor/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
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
    <link href="{{ asset('backend/assets/css/erp-prototype.css') }}?v={{ filemtime(public_path('backend/assets/css/erp-prototype.css')) }}" rel="stylesheet" type="text/css" />
    <template id="erp-page-styles">@stack('styles')</template>
</head>

<body class="{{ (($headerGodowns ?? collect())->isNotEmpty() || ($headerShops ?? collect())->isNotEmpty() || ($headerFinancialYears ?? collect())->isNotEmpty()) ? 'erp-has-context' : '' }}">

    @include('backend.layouts.page-loader')

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
                <main class="container-fluid" id="erp-main-content" tabindex="-1">
                    @yield('content') 
                </main>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->
            @include('backend.layouts.footer')
        </div>
        <!-- end main content-->

    </div>
    <!-- END layout-wrapper --> 
    @include('backend.layouts.mobile-navigation')
    @include('backend.layouts.theme-settings')
    <!-- JAVASCRIPT -->
    <script src="{{ asset('backend/assets/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('backend/assets/vendor/select2/js/select2.full.min.js') }}"></script>
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
    <script src="{{ asset('backend/assets/js/page-loader.js') }}?v={{ filemtime(public_path('backend/assets/js/page-loader.js')) }}"></script>
    <script src="{{ asset('backend/assets/js/persistent-fullscreen.js') }}?v={{ filemtime(public_path('backend/assets/js/persistent-fullscreen.js')) }}"></script>
    <script src="{{ asset('backend/assets/js/erp-common.js') }}?v={{ filemtime(public_path('backend/assets/js/erp-common.js')) }}"></script>
    <script src="{{ asset('backend/assets/js/erp-navigation.js') }}?v={{ filemtime(public_path('backend/assets/js/erp-navigation.js')) }}"></script>
    <script src="{{ asset('backend/assets/js/module-workspace.js') }}?v={{ filemtime(public_path('backend/assets/js/module-workspace.js')) }}"></script>
    <script src="{{ asset('backend/assets/js/header-context.js') }}?v={{ filemtime(public_path('backend/assets/js/header-context.js')) }}"></script>
    <script src="{{ asset('backend/assets/js/header-search.js') }}?v={{ filemtime(public_path('backend/assets/js/header-search.js')) }}"></script>
    <script src="{{ asset('backend/assets/js/erp-ui.js') }}?v={{ filemtime(public_path('backend/assets/js/erp-ui.js')) }}"></script>
    <script src="{{ asset('backend/assets/js/auth-session.js') }}?v={{ filemtime(public_path('backend/assets/js/auth-session.js')) }}"></script>
    <template id="erp-page-scripts">@stack('scripts')</template>
</body>

</html>
