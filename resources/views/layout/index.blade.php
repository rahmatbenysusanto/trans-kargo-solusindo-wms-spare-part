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

                <div class="app-brand demo ">
                    <a href="index.html" class="app-brand-link">
                        <span class="app-brand-logo demo">
                            <span class="text-primary">
                                <svg width="32" height="22" viewBox="0 0 32 22" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M0.00172773 0V6.85398C0.00172773 6.85398 -0.133178 9.01207 1.98092 10.8388L13.6912 21.9964L19.7809 21.9181L18.8042 9.88248L16.4951 7.17289L9.23799 0H0.00172773Z"
                                        fill="currentColor" />
                                    <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd"
                                        d="M7.69824 16.4364L12.5199 3.23696L16.5541 7.25596L7.69824 16.4364Z"
                                        fill="#161616" />
                                    <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd"
                                        d="M8.07751 15.9175L13.9419 4.63989L16.5849 7.28475L8.07751 15.9175Z"
                                        fill="#161616" />
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M7.77295 16.3566L23.6563 0H32V6.88383C32 6.88383 31.8262 9.17836 30.6591 10.4057L19.7824 22H13.6938L7.77295 16.3566Z"
                                        fill="currentColor" />
                                </svg>
                            </span>
                        </span>
                        <span class="app-brand-text demo menu-text fw-bold ms-3">Vuexy</span>
                    </a>

                    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
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
                        class="menu-item {{ in_array($title, ['Inventory List', 'Stock Movement']) ? 'show open' : '' }}">
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
                        </ul>
                    </li>

                    <li class="menu-item {{ $title == 'Outbound' ? 'active' : '' }}">
                        <a href="{{ route('outbound.index') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-truck-delivery"></i>
                            <div data-i18n="Outbound">Outbound</div>
                        </a>
                    </li>

                    <li class="menu-item {{ $title == 'RMA' ? 'active' : '' }}">
                        <a href="{{ route('rma.index') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-replace"></i>
                            <div data-i18n="RMA / Replacement">RMA / Replacement</div>
                        </a>
                    </li>

                    <li class="menu-item {{ $title == 'Write Off' ? 'active' : '' }}">
                        <a href="{{ route('write-off.index') }}" class="menu-link">
                            <i class="menu-icon icon-base ti tabler-file-pencil"></i>
                            <div data-i18n="Write-off">Write-off</div>
                        </a>
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



                            <li class="nav-item dropdown-language dropdown">
                                <a class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill"
                                    href="javascript:void(0);" data-bs-toggle="dropdown">
                                    <i class="icon-base ti tabler-language icon-22px text-heading"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0);" data-language="en"
                                            data-text-direction="ltr">
                                            <span>English</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0);" data-language="fr"
                                            data-text-direction="ltr">
                                            <span>French</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0);" data-language="ar"
                                            data-text-direction="rtl">
                                            <span>Arabic</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0);" data-language="de"
                                            data-text-direction="ltr">
                                            <span>German</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <!--/ Language -->


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


                            <!-- Quick links  -->
                            <li class="nav-item dropdown-shortcuts navbar-dropdown dropdown">
                                <a class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill"
                                    href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                    aria-expanded="false">
                                    <i class="icon-base ti tabler-layout-grid-add icon-22px text-heading"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end p-0">
                                    <div class="dropdown-menu-header border-bottom">
                                        <div class="dropdown-header d-flex align-items-center py-3">
                                            <h6 class="mb-0 me-auto">Shortcuts</h6>
                                            <a href="javascript:void(0)"
                                                class="dropdown-shortcuts-add py-2 btn btn-text-secondary rounded-pill btn-icon"
                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="Add shortcuts"><i
                                                    class="icon-base ti tabler-plus icon-20px text-heading"></i></a>
                                        </div>
                                    </div>
                                    <div class="dropdown-shortcuts-list scrollable-container">
                                        <div class="row row-bordered overflow-visible g-0">
                                            <div class="dropdown-shortcuts-item col">
                                                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                                                    <i class="icon-base ti tabler-calendar icon-26px text-heading"></i>
                                                </span>
                                                <a href="app-calendar.html" class="stretched-link">Calendar</a>
                                                <small>Appointments</small>
                                            </div>
                                            <div class="dropdown-shortcuts-item col">
                                                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                                                    <i
                                                        class="icon-base ti tabler-file-dollar icon-26px text-heading"></i>
                                                </span>
                                                <a href="app-invoice-list.html" class="stretched-link">Invoice App</a>
                                                <small>Manage Accounts</small>
                                            </div>
                                        </div>
                                        <div class="row row-bordered overflow-visible g-0">
                                            <div class="dropdown-shortcuts-item col">
                                                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                                                    <i class="icon-base ti tabler-user icon-26px text-heading"></i>
                                                </span>
                                                <a href="app-user-list.html" class="stretched-link">User App</a>
                                                <small>Manage Users</small>
                                            </div>
                                            <div class="dropdown-shortcuts-item col">
                                                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                                                    <i class="icon-base ti tabler-users icon-26px text-heading"></i>
                                                </span>
                                                <a href="app-access-roles.html" class="stretched-link">Role
                                                    Management</a>
                                                <small>Permission</small>
                                            </div>
                                        </div>
                                        <div class="row row-bordered overflow-visible g-0">
                                            <div class="dropdown-shortcuts-item col">
                                                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                                                    <i
                                                        class="icon-base ti tabler-device-desktop-analytics icon-26px text-heading"></i>
                                                </span>
                                                <a href="index.html" class="stretched-link">Dashboard</a>
                                                <small>User Dashboard</small>
                                            </div>
                                            <div class="dropdown-shortcuts-item col">
                                                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                                                    <i class="icon-base ti tabler-settings icon-26px text-heading"></i>
                                                </span>
                                                <a href="pages-account-settings-account.html"
                                                    class="stretched-link">Setting</a>
                                                <small>Account Settings</small>
                                            </div>
                                        </div>
                                        <div class="row row-bordered overflow-visible g-0">
                                            <div class="dropdown-shortcuts-item col">
                                                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                                                    <i
                                                        class="icon-base ti tabler-help-circle icon-26px text-heading"></i>
                                                </span>
                                                <a href="pages-faq.html" class="stretched-link">FAQs</a>
                                                <small>FAQs & Articles</small>
                                            </div>
                                            <div class="dropdown-shortcuts-item col">
                                                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                                                    <i class="icon-base ti tabler-square icon-26px text-heading"></i>
                                                </span>
                                                <a href="modal-examples.html" class="stretched-link">Modals</a>
                                                <small>Useful Popups</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            <!-- Quick links -->

                            <!-- Notification -->
                            <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-2">
                                <a class="nav-link dropdown-toggle hide-arrow btn btn-icon btn-text-secondary rounded-pill"
                                    href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                    aria-expanded="false">
                                    <span class="position-relative">
                                        <i class="icon-base ti tabler-bell icon-22px text-heading"></i>
                                        <span
                                            class="badge rounded-pill bg-danger badge-dot badge-notifications border"></span>
                                    </span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end p-0">
                                    <li class="dropdown-menu-header border-bottom">
                                        <div class="dropdown-header d-flex align-items-center py-3">
                                            <h6 class="mb-0 me-auto">Notification</h6>
                                            <div class="d-flex align-items-center h6 mb-0">
                                                <span class="badge bg-label-primary me-2">8 New</span>
                                                <a href="javascript:void(0)"
                                                    class="dropdown-notifications-all p-2 btn btn-icon"
                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="Mark all as read"><i
                                                        class="icon-base ti tabler-mail-opened text-heading"></i></a>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="dropdown-notifications-list scrollable-container">
                                        <ul class="list-group list-group-flush">
                                            <li
                                                class="list-group-item list-group-item-action dropdown-notifications-item">
                                                <div class="d-flex">
                                                    <div class="flex-shrink-0 me-3">
                                                        <div class="avatar">
                                                            <img src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/img/avatars/1.png"
                                                                alt class="rounded-circle" />
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="small mb-1">Congratulation Lettie 🎉</h6>
                                                        <small class="mb-1 d-block text-body">Won the monthly best
                                                            seller gold badge</small>
                                                        <small class="text-body-secondary">1h ago</small>
                                                    </div>
                                                    <div class="flex-shrink-0 dropdown-notifications-actions">
                                                        <a href="javascript:void(0)"
                                                            class="dropdown-notifications-read"><span
                                                                class="badge badge-dot"></span></a>
                                                        <a href="javascript:void(0)"
                                                            class="dropdown-notifications-archive"><span
                                                                class="icon-base ti tabler-x"></span></a>
                                                    </div>
                                                </div>
                                            </li>
                                            <li
                                                class="list-group-item list-group-item-action dropdown-notifications-item">
                                                <div class="d-flex">
                                                    <div class="flex-shrink-0 me-3">
                                                        <div class="avatar">
                                                            <span
                                                                class="avatar-initial rounded-circle bg-label-danger">CF</span>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1 small">Charles Franklin</h6>
                                                        <small class="mb-1 d-block text-body">Accepted your
                                                            connection</small>
                                                        <small class="text-body-secondary">12hr ago</small>
                                                    </div>
                                                    <div class="flex-shrink-0 dropdown-notifications-actions">
                                                        <a href="javascript:void(0)"
                                                            class="dropdown-notifications-read"><span
                                                                class="badge badge-dot"></span></a>
                                                        <a href="javascript:void(0)"
                                                            class="dropdown-notifications-archive"><span
                                                                class="icon-base ti tabler-x"></span></a>
                                                    </div>
                                                </div>
                                            </li>
                                            <li
                                                class="list-group-item list-group-item-action dropdown-notifications-item marked-as-read">
                                                <div class="d-flex">
                                                    <div class="flex-shrink-0 me-3">
                                                        <div class="avatar">
                                                            <img src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/img/avatars/2.png"
                                                                alt class="rounded-circle" />
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1 small">New Message ✉️</h6>
                                                        <small class="mb-1 d-block text-body">You have new message from
                                                            Natalie</small>
                                                        <small class="text-body-secondary">1h ago</small>
                                                    </div>
                                                    <div class="flex-shrink-0 dropdown-notifications-actions">
                                                        <a href="javascript:void(0)"
                                                            class="dropdown-notifications-read"><span
                                                                class="badge badge-dot"></span></a>
                                                        <a href="javascript:void(0)"
                                                            class="dropdown-notifications-archive"><span
                                                                class="icon-base ti tabler-x"></span></a>
                                                    </div>
                                                </div>
                                            </li>
                                            <li
                                                class="list-group-item list-group-item-action dropdown-notifications-item">
                                                <div class="d-flex">
                                                    <div class="flex-shrink-0 me-3">
                                                        <div class="avatar">
                                                            <span
                                                                class="avatar-initial rounded-circle bg-label-success"><i
                                                                    class="icon-base ti tabler-shopping-cart"></i></span>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1 small">Whoo! You have new order 🛒</h6>
                                                        <small class="mb-1 d-block text-body">ACME Inc. made new order
                                                            $1,154</small>
                                                        <small class="text-body-secondary">1 day ago</small>
                                                    </div>
                                                    <div class="flex-shrink-0 dropdown-notifications-actions">
                                                        <a href="javascript:void(0)"
                                                            class="dropdown-notifications-read"><span
                                                                class="badge badge-dot"></span></a>
                                                        <a href="javascript:void(0)"
                                                            class="dropdown-notifications-archive"><span
                                                                class="icon-base ti tabler-x"></span></a>
                                                    </div>
                                                </div>
                                            </li>
                                            <li
                                                class="list-group-item list-group-item-action dropdown-notifications-item marked-as-read">
                                                <div class="d-flex">
                                                    <div class="flex-shrink-0 me-3">
                                                        <div class="avatar">
                                                            <img src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/img/avatars/9.png"
                                                                alt class="rounded-circle" />
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1 small">Application has been approved 🚀</h6>
                                                        <small class="mb-1 d-block text-body">Your ABC project
                                                            application has been approved.</small>
                                                        <small class="text-body-secondary">2 days ago</small>
                                                    </div>
                                                    <div class="flex-shrink-0 dropdown-notifications-actions">
                                                        <a href="javascript:void(0)"
                                                            class="dropdown-notifications-read"><span
                                                                class="badge badge-dot"></span></a>
                                                        <a href="javascript:void(0)"
                                                            class="dropdown-notifications-archive"><span
                                                                class="icon-base ti tabler-x"></span></a>
                                                    </div>
                                                </div>
                                            </li>
                                            <li
                                                class="list-group-item list-group-item-action dropdown-notifications-item marked-as-read">
                                                <div class="d-flex">
                                                    <div class="flex-shrink-0 me-3">
                                                        <div class="avatar">
                                                            <span
                                                                class="avatar-initial rounded-circle bg-label-success"><i
                                                                    class="icon-base ti tabler-chart-pie"></i></span>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1 small">Monthly report is generated</h6>
                                                        <small class="mb-1 d-block text-body">July monthly financial
                                                            report is generated </small>
                                                        <small class="text-body-secondary">3 days ago</small>
                                                    </div>
                                                    <div class="flex-shrink-0 dropdown-notifications-actions">
                                                        <a href="javascript:void(0)"
                                                            class="dropdown-notifications-read"><span
                                                                class="badge badge-dot"></span></a>
                                                        <a href="javascript:void(0)"
                                                            class="dropdown-notifications-archive"><span
                                                                class="icon-base ti tabler-x"></span></a>
                                                    </div>
                                                </div>
                                            </li>
                                            <li
                                                class="list-group-item list-group-item-action dropdown-notifications-item marked-as-read">
                                                <div class="d-flex">
                                                    <div class="flex-shrink-0 me-3">
                                                        <div class="avatar">
                                                            <img src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/img/avatars/5.png"
                                                                alt class="rounded-circle" />
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1 small">Send connection request</h6>
                                                        <small class="mb-1 d-block text-body">Peter sent you connection
                                                            request</small>
                                                        <small class="text-body-secondary">4 days ago</small>
                                                    </div>
                                                    <div class="flex-shrink-0 dropdown-notifications-actions">
                                                        <a href="javascript:void(0)"
                                                            class="dropdown-notifications-read"><span
                                                                class="badge badge-dot"></span></a>
                                                        <a href="javascript:void(0)"
                                                            class="dropdown-notifications-archive"><span
                                                                class="icon-base ti tabler-x"></span></a>
                                                    </div>
                                                </div>
                                            </li>
                                            <li
                                                class="list-group-item list-group-item-action dropdown-notifications-item">
                                                <div class="d-flex">
                                                    <div class="flex-shrink-0 me-3">
                                                        <div class="avatar">
                                                            <img src="https://demos.pixinvent.com/vuexy-html-admin-template/assets/img/avatars/6.png"
                                                                alt class="rounded-circle" />
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1 small">New message from Jane</h6>
                                                        <small class="mb-1 d-block text-body">Your have new message
                                                            from Jane</small>
                                                        <small class="text-body-secondary">5 days ago</small>
                                                    </div>
                                                    <div class="flex-shrink-0 dropdown-notifications-actions">
                                                        <a href="javascript:void(0)"
                                                            class="dropdown-notifications-read"><span
                                                                class="badge badge-dot"></span></a>
                                                        <a href="javascript:void(0)"
                                                            class="dropdown-notifications-archive"><span
                                                                class="icon-base ti tabler-x"></span></a>
                                                    </div>
                                                </div>
                                            </li>
                                            <li
                                                class="list-group-item list-group-item-action dropdown-notifications-item marked-as-read">
                                                <div class="d-flex">
                                                    <div class="flex-shrink-0 me-3">
                                                        <div class="avatar">
                                                            <span
                                                                class="avatar-initial rounded-circle bg-label-warning"><i
                                                                    class="icon-base ti tabler-alert-triangle"></i></span>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1 small">CPU is running high</h6>
                                                        <small class="mb-1 d-block text-body">CPU Utilization Percent
                                                            is currently at 88.63%,</small>
                                                        <small class="text-body-secondary">5 days ago</small>
                                                    </div>
                                                    <div class="flex-shrink-0 dropdown-notifications-actions">
                                                        <a href="javascript:void(0)"
                                                            class="dropdown-notifications-read"><span
                                                                class="badge badge-dot"></span></a>
                                                        <a href="javascript:void(0)"
                                                            class="dropdown-notifications-archive"><span
                                                                class="icon-base ti tabler-x"></span></a>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="border-top">
                                        <div class="d-grid p-4">
                                            <a class="btn btn-primary btn-sm d-flex" href="javascript:void(0);">
                                                <small class="align-middle">View all notifications</small>
                                            </a>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                            <!--/ Notification -->

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
                                                    <h6 class="mb-0">John Doe</h6>
                                                    <small class="text-body-secondary">Admin</small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider my-1 mx-n2"></div>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="pages-profile-user.html"> <i
                                                class="icon-base ti tabler-user me-3 icon-md"></i><span
                                                class="align-middle">My Profile</span> </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="pages-account-settings-account.html"> <i
                                                class="icon-base ti tabler-settings me-3 icon-md"></i><span
                                                class="align-middle">Settings</span> </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="pages-account-settings-billing.html">
                                            <span class="d-flex align-items-center align-middle">
                                                <i
                                                    class="flex-shrink-0 icon-base ti tabler-file-dollar me-3 icon-md"></i><span
                                                    class="flex-grow-1 align-middle">Billing</span>
                                                <span
                                                    class="flex-shrink-0 badge bg-danger d-flex align-items-center justify-content-center">4</span>
                                            </span>
                                        </a>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider my-1 mx-n2"></div>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="pages-pricing.html"> <i
                                                class="icon-base ti tabler-currency-dollar me-3 icon-md"></i><span
                                                class="align-middle">Pricing</span> </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="pages-faq.html"> <i
                                                class="icon-base ti tabler-question-mark me-3 icon-md"></i><span
                                                class="align-middle">FAQ</span> </a>
                                    </li>
                                    <li>
                                        <div class="d-grid px-2 pt-2 pb-1">
                                            <a class="btn btn-sm btn-danger d-flex" href="auth-login-cover.html"
                                                target="_blank">
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
