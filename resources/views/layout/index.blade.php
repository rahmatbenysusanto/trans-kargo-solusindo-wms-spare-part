<!doctype html>
<html lang="en" class=" layout-navbar-fixed layout-menu-fixed layout-wide @yield('layout_class')" dir="ltr"
    data-skin="default" data-assets-path="../../assets/" data-template="vertical-menu-template" data-bs-theme="light">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>@yield('title') - WMS Spare</title>
    <link rel="canonical"
        href="https://themeforest.net/item/vuexy-vuejs-html-laravel-admin-dashboard-template/23328599" />

    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-5J3LMKC');
    </script>

    <link rel="icon" type="image/x-icon"
        href="https://demos.pixinvent.com/vuexy-html-admin-template/assets/img/favicon/favicon.ico" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com/" />
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&amp;ampdisplay=swap"
        rel="stylesheet" />

    <link rel="stylesheet"
        href="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/fonts/iconify-icons.css" />

    <!-- Core CSS -->
    <!-- build:css assets/vendor/css/theme.css  -->

    <link rel="stylesheet"
        href="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/libs/node-waves/node-waves.css" />


    <link rel="stylesheet"
        href="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/libs/pickr/pickr-themes.css" />

    <link rel="stylesheet" href="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/css/core.css" />
    <link rel="stylesheet" href="https://demos.pixinvent.com/vuexy-html-admin-template/assets/css/demo.css" />


    <!-- Vendors CSS -->

    <link rel="stylesheet"
        href="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <!-- endbuild -->

    <link rel="stylesheet"
        href="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/libs/apex-charts/apex-charts.css" />
    <link rel="stylesheet"
        href="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css" />
    <link rel="stylesheet"
        href="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css" />

    <!-- Page CSS -->

    <link rel="stylesheet"
        href="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/css/pages/app-logistics-dashboard.css" />

    <!-- Helpers -->
    <script src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/js/helpers.js"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->

    <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
    <script src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/js/template-customizer.js"></script>

    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->

    <script src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/js/config.js"></script>

    <link rel="stylesheet"
        href="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/libs/animate-css/animate.css" />
    <link rel="stylesheet"
        href="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/libs/sweetalert2/sweetalert2.css" />

    <style>
        .pagination {
            display: flex;
            gap: 5px;
            margin-top: 20px;
        }

        .pagination .page-item .page-link {
            border-radius: 8px;
            border: none;
            color: #6366f1;
            /* Primary color */
            font-weight: 500;
            padding: 8px 16px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            background: #fff;
        }

        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: #fff;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .pagination .page-item .page-link:hover:not(.active) {
            background-color: #f8fafc;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .pagination .page-item.disabled .page-link {
            background-color: #f1f5f9;
            color: #94a3b8;
            cursor: not-allowed;
        }
    </style>
    @yield('css')
</head>

<body>

    <!-- ?PROD Only: Google Tag Manager (noscript) (Default ThemeSelection: GTM-5DDHKGP, PixInvent: GTM-5J3LMKC) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5J3LMKC" height="0" width="0"
            style="display: none; visibility: hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar  ">
        <div class="layout-container">

            <!-- Menu -->
            <aside id="layout-menu" class="layout-menu menu-vertical menu">

                <div class="app-brand demo justify-content-center position-relative">
                    <a href="index.html" class="app-brand-link">
                        <span class="app-brand-logo demo text-center">
                            <img src="{{ asset('assets/image/logo.png') }}" alt="logo" height="30">
                        </span>
                    </a>

                    <a href="javascript:void(0);"
                        class="layout-menu-toggle menu-link text-large position-absolute end-0 me-4">
                        <i class="icon-base ti menu-toggle-icon d-none d-xl-block"></i>
                        <i class="icon-base ti tabler-x d-block d-xl-none"></i>
                    </a>
                </div>

                <div class="menu-inner-shadow"></div>

                <ul class="menu-inner py-1">
                    <li
                        class="menu-item {{ in_array($title, ['Stock Overview', 'utilizationByClient', 'rmaMonitoring', 'inboundReturn', 'stockMonitoring']) ? 'show open' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon icon-base ti tabler-chart-pie"></i>
                            <div data-i18n="Dashboards">Dashboards</div>
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item {{ $title == 'Stock Overview' ? 'active' : '' }}">
                                <a href="{{ route('dashboard') }}" class="menu-link">
                                    <div data-i18n="Stock Overview">Stock Overview</div>
                                </a>
                            </li>
                            <li class="menu-item {{ $title == 'utilizationByClient' ? 'active' : '' }}">
                                <a href="{{ route('utilizationByClient') }}" class="menu-link">
                                    <div data-i18n="Utilization By Client">Utilization By Client</div>
                                </a>
                            </li>
                            <li class="menu-item {{ $title == 'rmaMonitoring' ? 'active' : '' }}">
                                <a href="{{ route('rmaMonitoring') }}" class="menu-link">
                                    <div data-i18n="RMA Monitoring">RMA Monitoring</div>
                                </a>
                            </li>
                            <li class="menu-item {{ $title == 'inboundReturn' ? 'active' : '' }}">
                                <a href="{{ route('inboundReturn') }}" class="menu-link">
                                    <div data-i18n="Inbound vs Return Trend">Inbound vs Return Trend</div>
                                </a>
                            </li>
                            <li class="menu-item {{ $title == 'stockMonitoring' ? 'active' : '' }}">
                                <a href="{{ route('stockMonitoring') }}" class="menu-link">
                                    <div data-i18n="Stock Monitoring">Stock Monitoring</div>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="menu-header small">
                        <span class="menu-header-text" data-i18n="Main Menu">Main Menu</span>
                    </li>

                    <li class="menu-item {{ in_array($title, ['Receiving', 'Put Away']) ? 'show open' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon icon-base ti tabler-archive"></i>
                            <div data-i18n="Inbound">Inbound</div>
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item {{ $title == 'Receiving' ? 'active' : '' }}">
                                <a href="{{ route('receiving') }}" class="menu-link">
                                    <div data-i18n="Receiving">Receiving</div>
                                </a>
                            </li>
                            <li class="menu-item {{ $title == 'Put Away' ? 'active' : '' }}">
                                <a href="{{ route('receiving.put.away') }}" class="menu-link">
                                    <div data-i18n="Put Away">Put Away</div>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li
                        class="menu-item {{ in_array($title, ['Inventory List', 'Stock Movement', 'Product Movement', 'Write Off', 'Cycle Count']) ? 'show open' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon icon-base ti tabler-truck-loading"></i>
                            <div data-i18n="Inventory">Inventory</div>
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item {{ $title == 'Inventory List' ? 'active' : '' }}">
                                <a href="{{ route('inventory.index') }}" class="menu-link">
                                    <div data-i18n="Inventory List">Inventory List</div>
                                </a>
                            </li>
                            <li class="menu-item {{ $title == 'Stock Movement' ? 'active' : '' }}">
                                <a href="{{ route('inventory.stock.movement') }}" class="menu-link">
                                    <div data-i18n="Stock Movement">Stock Movement</div>
                                </a>
                            </li>
                            <li class="menu-item {{ $title == 'Product Movement' ? 'active' : '' }}">
                                <a href="{{ route('inventory.product.movement') }}" class="menu-link">
                                    <div data-i18n="Product Movement">Product Movement</div>
                                </a>
                            </li>
                            <li class="menu-item {{ $title == 'Write Off' ? 'active' : '' }}">
                                <a href="{{ route('write-off.index') }}" class="menu-link">
                                    <div data-i18n="Write-off">Write-off</div>
                                </a>
                            </li>
                            <li class="menu-item {{ $title == 'Cycle Count' ? 'active' : '' }}">
                                <a href="{{ route('inventory.cycle-count') }}" class="menu-link text-warning">
                                    <div data-i18n="Cycle Count">Cycle Count</div>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="menu-item {{ $title == 'Outbound' ? 'active' : '' }}">
                        <a href="{{ route('outbound.index') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-truck-delivery"></i>
                            <div data-i18n="Outbound">Outbound</div>
                        </a>
                    </li>


                    <li
                        class="menu-item {{ in_array($title, ['Stock on Hand', 'Movement History', 'Utilization Report']) ? 'show open' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon icon-base ti tabler-report"></i>
                            <div data-i18n="Audit & Reporting">Audit & Reporting</div>
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item {{ $title == 'Stock on Hand' ? 'active' : '' }}">
                                <a href="{{ route('reporting.stock-on-hand') }}" class="menu-link">
                                    <div data-i18n="Stock on Hand">Stock on Hand</div>
                                </a>
                            </li>
                            <li class="menu-item {{ $title == 'Movement History' ? 'active' : '' }}">
                                <a href="{{ route('reporting.movement-history') }}" class="menu-link">
                                    <div data-i18n="Movement History">Movement History</div>
                                </a>
                            </li>
                            <li class="menu-item {{ $title == 'Utilization Report' ? 'active' : '' }}">
                                <a href="{{ route('reporting.utilization') }}" class="menu-link">
                                    <div data-i18n="Utilization Report">Utilization Report</div>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="menu-header small">
                        <span class="menu-header-text" data-i18n="Warehouse">Warehouse</span>
                    </li>

                    <li class="menu-item {{ in_array($title, ['Zone', 'Rak', 'Bin', 'Level']) ? 'show open' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon icon-base ti tabler-server"></i>
                            <div data-i18n="Storage">Storage</div>
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item {{ $title == 'Zone' ? 'active' : '' }}">
                                <a href="{{ route('storage.zone') }}" class="menu-link">
                                    <div data-i18n="Zone">Zone</div>
                                </a>
                            </li>
                            <li class="menu-item {{ $title == 'Rak' ? 'active' : '' }}">
                                <a href="{{ route('storage.rak') }}" class="menu-link">
                                    <div data-i18n="Rak">Rak</div>
                                </a>
                            </li>
                            <li class="menu-item {{ $title == 'Bin' ? 'active' : '' }}">
                                <a href="{{ route('storage.bin') }}" class="menu-link">
                                    <div data-i18n="Bin">Bin</div>
                                </a>
                            </li>
                            <li class="menu-item {{ $title == 'Level' ? 'active' : '' }}">
                                <a href="{{ route('storage.level') }}" class="menu-link">
                                    <div data-i18n="Level">Level</div>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="menu-item {{ $title == 'Brand' ? 'active' : '' }}">
                        <a href="{{ route('brand.index') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-brand-flutter"></i>
                            <div data-i18n="Brand">Brand</div>
                        </a>
                    </li>

                    <li class="menu-item {{ $title == 'Product Group' ? 'active' : '' }}">
                        <a href="{{ route('product.group.index') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-barcode"></i>
                            <div data-i18n="Product Group">Product Group</div>
                        </a>
                    </li>

                    <li class="menu-item {{ $title == 'Client' ? 'active' : '' }}">
                        <a href="{{ route('client.index') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-user-check"></i>
                            <div data-i18n="Client">Client</div>
                        </a>
                    </li>

                    <li class="menu-item {{ $title == 'User' ? 'active' : '' }}">
                        <a href="{{ route('user.index') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-user"></i>
                            <div data-i18n="User">User</div>
                        </a>
                    </li>
                </ul>


            </aside>

            <div class="menu-mobile-toggler d-xl-none rounded-1">
                <a href="javascript:void(0);"
                    class="layout-menu-toggle menu-link text-large text-bg-secondary p-2 rounded-1">
                    <i class="ti tabler-menu icon-base"></i>
                    <i class="ti tabler-chevron-right icon-base"></i>
                </a>
            </div>

            <div class="layout-page">
                <nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme"
                    id="layout-navbar">
                    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0   d-xl-none ">
                        <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
                            <i class="icon-base ti tabler-menu-2 icon-md"></i>
                        </a>
                    </div>

                    <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">

                        <!-- Search -->
                        <div class="navbar-nav align-items-center">
                            <div class="nav-item navbar-search-wrapper px-md-0 px-2 mb-0">
                                <a class="nav-item nav-link search-toggler d-flex align-items-center px-0"
                                    href="javascript:void(0);">
                                    <span class="d-inline-block text-body-secondary fw-normal"
                                        id="autocomplete"></span>
                                </a>
                            </div>
                        </div>

                        <!-- /Search -->





                        <ul class="navbar-nav flex-row align-items-center ms-md-auto">
                            <!-- Style Switcher -->
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill"
                                    id="nav-theme" href="javascript:void(0);" data-bs-toggle="dropdown">
                                    <i class="icon-base ti tabler-sun icon-22px theme-icon-active text-heading"></i>
                                    <span class="d-none ms-2" id="nav-theme-text">Toggle theme</span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="nav-theme-text">
                                    <li>
                                        <button type="button" class="dropdown-item align-items-center active"
                                            data-bs-theme-value="light" aria-pressed="false">
                                            <span><i class="icon-base ti tabler-sun icon-22px me-3"
                                                    data-icon="sun"></i>Light</span>
                                        </button>
                                    </li>
                                    <li>
                                        <button type="button" class="dropdown-item align-items-center"
                                            data-bs-theme-value="dark" aria-pressed="true">
                                            <span><i class="icon-base ti tabler-moon-stars icon-22px me-3"
                                                    data-icon="moon-stars"></i>Dark</span>
                                        </button>
                                    </li>
                                    <li>
                                        <button type="button" class="dropdown-item align-items-center"
                                            data-bs-theme-value="system" aria-pressed="false">
                                            <span><i class="icon-base ti tabler-device-desktop-analytics icon-22px me-3"
                                                    data-icon="device-desktop-analytics"></i>System</span>
                                        </button>
                                    </li>
                                </ul>
                            </li>
                            <!-- / Style Switcher-->

                            <!-- User -->
                            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                                <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);"
                                    data-bs-toggle="dropdown">
                                    <div class="avatar avatar-online">
                                        <img src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/img/avatars/1.png"
                                            alt class="rounded-circle" />
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item mt-0" href="pages-account-settings-account.html">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 me-2">
                                                    <div class="avatar avatar-online">
                                                        <img src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/img/avatars/1.png"
                                                            alt class="rounded-circle" />
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-0">{{ Auth::user()->name }}</h6>
                                                    <small class="text-body-secondary"></small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider my-1 mx-n2"></div>
                                    </li>
                                    <li>
                                        <div class="d-grid px-2 pt-2 pb-1">
                                            <a class="btn btn-sm btn-danger d-flex" href="{{ route('logout') }}">
                                                <small class="align-middle">Logout</small>
                                                <i class="icon-base ti tabler-logout ms-2 icon-14px"></i>
                                            </a>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                            <!--/ User -->

                        </ul>
                    </div>
                </nav>

                <div class="content-wrapper">
                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">

                        @yield('content')

                    </div>

                    <footer class="content-footer footer bg-footer-theme">
                        <div class="container-xxl">
                            <div
                                class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
                                <div class="text-body">
                                    ©
                                    <script>
                                        document.write(new Date().getFullYear());
                                    </script>
                                    , made with ❤️ by <a href="https://pixinvent.com/" target="_blank"
                                        class="footer-link">Maximaz</a>
                                </div>
                            </div>
                        </div>
                    </footer>

                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>

        <div class="layout-overlay layout-menu-toggle"></div>
        <div class="drag-target"></div>

    </div>

    <script src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/libs/popper/popper.js"></script>
    <script src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/js/bootstrap.js"></script>
    <script src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/libs/node-waves/node-waves.js">
    </script>
    <script src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/libs/@algolia/autocomplete-js.js">
    </script>
    <script src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/libs/pickr/pickr.js"></script>
    <script
        src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js">
    </script>
    <script src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/libs/hammer/hammer.js"></script>
    <script src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/libs/i18n/i18n.js"></script>
    <script src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/js/menu.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script
        src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js">
    </script>
    <script src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/js/main.js"></script>
    <script src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/js/app-logistics-dashboard.js"></script>

    <script src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/vendor/libs/sweetalert2/sweetalert2.js">
    </script>
    <script src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/js/extended-ui-sweetalert2.js"></script>

    @yield('js')

    @if ($message = Session::get('success'))
        <script>
            Swal.fire({
                title: 'Success!',
                title: '{{ $message }}',
                icon: 'success'
            });
        </script>
    @endif

    @if ($message = Session::get('error'))
        <script>
            Swal.fire({
                title: 'Error!',
                title: '{{ $message }}',
                icon: 'error'
            });
        </script>
    @endif

</body>

</html>
