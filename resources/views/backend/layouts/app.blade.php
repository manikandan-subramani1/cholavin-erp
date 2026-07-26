<!doctype html>
<html lang="en"
      data-layout="vertical"
      data-topbar="light"
      data-sidebar="dark"
      data-sidebar-size="lg"
      data-sidebar-image="none"
      data-preloader="disable"
      data-theme="default"
      data-theme-colors="default">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Cholavin ERP')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Cholavin ERP rice business workspace">
    @include('shared.google-fonts')
    <link rel="stylesheet" href="{{ asset('backend/assets/css/app.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/assets/css/custom.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/assets/css/icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/datatables/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/assets/vendor/datatables/responsive.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/assets/libs/sweetalert2/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/assets/css/cholavin-erp.css') }}">
    <link rel="stylesheet" href="{{ asset('backend/assets/css/cholavin-dashboard.css') }}?v={{ filemtime(public_path('backend/assets/css/cholavin-dashboard.css')) }}">
    <script>
        (function () {
            try {
                var savedLayout = sessionStorage.getItem('data-layout');
                var savedDefaults = JSON.parse(sessionStorage.getItem('defaultAttribute') || 'null');
                var storedLayout = savedLayout || (savedDefaults && savedDefaults['data-layout']);
                if (storedLayout === 'twocolumn') {
                    sessionStorage.setItem('data-layout', 'vertical');
                    sessionStorage.setItem('data-sidebar-size', 'lg');
                    sessionStorage.removeItem('defaultAttribute');
                }
            } catch (error) {
                // A restricted session store must not prevent the dashboard from rendering.
            }
        }());
    </script>
    <script src="{{ asset('backend/assets/js/layout.js') }}"></script>
    @stack('styles')
</head>
<body class="cholavin-app">
    <div id="layout-wrapper">
        @include('backend.layouts.header')
        @include('backend.layouts.menu')
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>
            @include('backend.layouts.footer')
        </div>
    </div>

    @include('backend.layouts.theme-settings')
    <div class="modal fade" id="removeNotificationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <i class="ri-delete-bin-5-line text-danger fs-32"></i>
                    <h5 class="mt-3">Remove notifications?</h5>
                    <p class="text-muted mb-3">Selected notifications will be removed from this view.</p>
                    <button type="button" class="btn btn-light me-2" id="NotificationModalbtn-close" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="delete-notification">Remove</button>
                </div>
            </div>
        </div>
    </div>
    <button type="button" class="btn btn-danger btn-icon rounded-circle shadow-lg" id="back-to-top" style="display:none;position:fixed;right:24px;bottom:24px;z-index:1040;" aria-label="Back to top">
        <i class="ri-arrow-up-line"></i>
    </button>
    <div class="vertical-overlay" data-sidebar-toggle></div>
    <aside class="cholavin-drawer" id="workspace-drawer" aria-hidden="true">
        <div class="cholavin-drawer-head">
            <div><span class="cholavin-eyebrow">Right drawer</span><h3 class="mb-0" data-drawer-title>Details</h3></div>
            <button class="cholavin-icon-btn" type="button" data-drawer-close aria-label="Close drawer"><i class="ri-close-line"></i></button>
        </div>
        <div class="cholavin-drawer-body" data-drawer-body>
            <div class="cholavin-empty-state"><i class="ri-layout-right-line"></i><h4>Select a record</h4><p>View, edit, create, or inspect details without leaving this workspace.</p></div>
        </div>
    </aside>
    <div class="cholavin-drawer-backdrop" data-drawer-close></div>

    <script src="{{ asset('backend/assets/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('backend/assets/vendor/jquery-validation/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('backend/assets/vendor/jquery-validation/additional-methods.min.js') }}"></script>
    <script src="{{ asset('backend/assets/vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('backend/assets/vendor/datatables/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('backend/assets/vendor/datatables/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('backend/assets/vendor/datatables/responsive.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/feather-icons/feather.min.js') }}"></script>
    <script src="{{ asset('backend/assets/js/plugins.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script>
        window.Toastify = window.Toastify || function (options) {
            return { showToast: function () {
                if (window.cholavinToast) window.cholavinToast(options.text || 'Action completed', 'success');
            }};
        };
    </script>
    <script src="{{ asset('backend/assets/js/app.js') }}"></script>
    <script src="{{ asset('backend/assets/js/erp-common.js') }}?v={{ filemtime(public_path('backend/assets/js/erp-common.js')) }}"></script>
    <script src="{{ asset('backend/assets/js/cholavin-dashboard.js') }}?v={{ filemtime(public_path('backend/assets/js/cholavin-dashboard.js')) }}"></script>
    @stack('scripts')
</body>
</html>
