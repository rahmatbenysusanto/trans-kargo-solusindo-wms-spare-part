<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="@yield('sizeBarSize', 'lg')" data-sidebar-image="none" data-preloader="disable">
<head>
    <meta charset="utf-8" />
    <title>@yield('title') | Trans Kargo Solusindo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">
    <script src="{{ asset('assets/js/layout.js') }}"></script>
    <link href="{{ asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/libs/dropzone/dropzone.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ asset('assets/libs/filepond/filepond.min.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ asset('assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.css') }}">

    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">

    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/custom.min.css') }}" rel="stylesheet" type="text/css" />

</head>

<body>
<div id="layout-wrapper">

    <header id="page-topbar">
        <div class="layout-width">
            <div class="navbar-header">
                <div class="d-flex">
                    <!-- LOGO -->
                    <div class="navbar-brand-box horizontal-logo">
                        <a href="#" class="logo logo-dark">
                            <span class="logo-sm">
                                <img src="{{ asset('assets/images/maximaz-logo.png') }}" alt="" height="22">
                            </span>
                            <span class="logo-lg">
                                <img src="{{ asset('assets/images/maximaz-logo.png') }}" alt="" height="17">
                            </span>
                        </a>

                        <a href="#" class="logo logo-light">
                            <span class="logo-sm">
                                <img src="{{ asset('assets/images/maximaz-logo.png') }}" alt="" height="22">
                            </span>
                            <span class="logo-lg">
                                <img src="{{ asset('assets/images/maximaz-logo.png') }}" alt="" height="17">
                            </span>
                        </a>
                    </div>

                    <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger shadow-none" id="topnav-hamburger-icon">
                        <span class="hamburger-icon">
                            <span></span>
                            <span></span>
                            <span></span>
                        </span>
                    </button>
                </div>

                <div class="d-flex align-items-center">

                    <div class="dropdown d-md-none topbar-head-dropdown header-item">
                        <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle shadow-none" id="page-header-search-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-search fs-22"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" aria-labelledby="page-header-search-dropdown">
                            <form class="p-3">
                                <div class="form-group m-0">
                                    <div class="input-group">
                                        <input type="text" class="form-control" placeholder="Search ..." aria-label="Recipient's username">
                                        <button class="btn btn-primary" type="submit"><i class="mdi mdi-magnify"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="ms-1 header-item d-none d-sm-flex">
                        <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle shadow-none" data-toggle="fullscreen">
                            <i class='bx bx-fullscreen fs-22'></i>
                        </button>
                    </div>

                    <div class="ms-1 header-item d-none d-sm-flex">
                        <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle light-dark-mode shadow-none">
                            <i class='bx bx-moon fs-22'></i>
                        </button>
                    </div>

                    <div class="dropdown topbar-head-dropdown ms-1 header-item" id="notificationDropdown">
                        <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle shadow-none" id="page-header-notifications-dropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false">
                            <i class='bx bx-bell fs-22'></i>
                            <span class="position-absolute topbar-badge fs-10 translate-middle badge rounded-pill bg-danger">3<span class="visually-hidden">unread messages</span></span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" aria-labelledby="page-header-notifications-dropdown">

                            <div class="dropdown-head bg-primary bg-pattern rounded-top">
                                <div class="p-3">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h6 class="m-0 fs-16 fw-semibold text-white"> Notifications </h6>
                                        </div>
                                        <div class="col-auto dropdown-tabs">
                                            <span class="badge bg-light-subtle text-body fs-13"> 4 New</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="px-2 pt-2">
                                    <ul class="nav nav-tabs dropdown-tabs nav-tabs-custom" data-dropdown-tabs="true" id="notificationItemsTab" role="tablist">
                                        <li class="nav-item waves-effect waves-light">
                                            <a class="nav-link active" data-bs-toggle="tab" href="#all-noti-tab" role="tab" aria-selected="true">
                                                All (4)
                                            </a>
                                        </li>
                                        <li class="nav-item waves-effect waves-light">
                                            <a class="nav-link" data-bs-toggle="tab" href="#messages-tab" role="tab" aria-selected="false">
                                                Messages
                                            </a>
                                        </li>
                                        <li class="nav-item waves-effect waves-light">
                                            <a class="nav-link" data-bs-toggle="tab" href="#alerts-tab" role="tab" aria-selected="false">
                                                Alerts
                                            </a>
                                        </li>
                                    </ul>
                                </div>

                            </div>

                            <div class="tab-content position-relative" id="notificationItemsTabContent">
                                <div class="tab-pane fade show active py-2 ps-2" id="all-noti-tab" role="tabpanel">
                                    <div data-simplebar style="max-height: 300px;" class="pe-2">
                                        <div class="text-reset notification-item d-block dropdown-item position-relative">
                                            <div class="d-flex">
                                                <div class="avatar-xs me-3 flex-shrink-0">
                                                    <span class="avatar-title bg-info-subtle text-info rounded-circle fs-16">
                                                        <i class="bx bx-badge-check"></i>
                                                    </span>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <a href="#!" class="stretched-link">
                                                        <h6 class="mt-0 mb-2 lh-base">Your <b>Elite</b> author Graphic
                                                            Optimization <span class="text-secondary">reward</span> is
                                                            ready!
                                                        </h6>
                                                    </a>
                                                    <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                                                        <span><i class="mdi mdi-clock-outline"></i> Just 30 sec ago</span>
                                                    </p>
                                                </div>
                                                <div class="px-2 fs-15">
                                                    <div class="form-check notification-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="all-notification-check01">
                                                        <label class="form-check-label" for="all-notification-check01"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-reset notification-item d-block dropdown-item position-relative">
                                            <div class="d-flex">
                                                <img src="assets/images/users/avatar-2.jpg" class="me-3 rounded-circle avatar-xs flex-shrink-0" alt="user-pic">
                                                <div class="flex-grow-1">
                                                    <a href="#!" class="stretched-link">
                                                        <h6 class="mt-0 mb-1 fs-13 fw-semibold">Angela Bernier</h6>
                                                    </a>
                                                    <div class="fs-13 text-muted">
                                                        <p class="mb-1">Answered to your comment on the cash flow forecast's
                                                            graph 🔔.</p>
                                                    </div>
                                                    <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                                                        <span><i class="mdi mdi-clock-outline"></i> 48 min ago</span>
                                                    </p>
                                                </div>
                                                <div class="px-2 fs-15">
                                                    <div class="form-check notification-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="all-notification-check02">
                                                        <label class="form-check-label" for="all-notification-check02"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-reset notification-item d-block dropdown-item position-relative">
                                            <div class="d-flex">
                                                <div class="avatar-xs me-3 flex-shrink-0">
                                                    <span class="avatar-title bg-danger-subtle text-danger rounded-circle fs-16">
                                                        <i class='bx bx-message-square-dots'></i>
                                                    </span>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <a href="#!" class="stretched-link">
                                                        <h6 class="mt-0 mb-2 fs-13 lh-base">You have received <b class="text-success">20</b> new messages in the conversation
                                                        </h6>
                                                    </a>
                                                    <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                                                        <span><i class="mdi mdi-clock-outline"></i> 2 hrs ago</span>
                                                    </p>
                                                </div>
                                                <div class="px-2 fs-15">
                                                    <div class="form-check notification-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="all-notification-check03">
                                                        <label class="form-check-label" for="all-notification-check03"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-reset notification-item d-block dropdown-item position-relative">
                                            <div class="d-flex">
                                                <img src="assets/images/users/avatar-8.jpg" class="me-3 rounded-circle avatar-xs flex-shrink-0" alt="user-pic">
                                                <div class="flex-grow-1">
                                                    <a href="#!" class="stretched-link">
                                                        <h6 class="mt-0 mb-1 fs-13 fw-semibold">Maureen Gibson</h6>
                                                    </a>
                                                    <div class="fs-13 text-muted">
                                                        <p class="mb-1">We talked about a project on linkedin.</p>
                                                    </div>
                                                    <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                                                        <span><i class="mdi mdi-clock-outline"></i> 4 hrs ago</span>
                                                    </p>
                                                </div>
                                                <div class="px-2 fs-15">
                                                    <div class="form-check notification-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="all-notification-check04">
                                                        <label class="form-check-label" for="all-notification-check04"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="my-3 text-center view-all">
                                            <button type="button" class="btn btn-soft-success waves-effect waves-light">View
                                                All Notifications <i class="ri-arrow-right-line align-middle"></i></button>
                                        </div>
                                    </div>

                                </div>

                                <div class="tab-pane fade py-2 ps-2" id="messages-tab" role="tabpanel" aria-labelledby="messages-tab">
                                    <div data-simplebar style="max-height: 300px;" class="pe-2">
                                        <div class="text-reset notification-item d-block dropdown-item">
                                            <div class="d-flex">
                                                <img src="assets/images/users/avatar-3.jpg" class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                                <div class="flex-grow-1">
                                                    <a href="#!" class="stretched-link">
                                                        <h6 class="mt-0 mb-1 fs-13 fw-semibold">James Lemire</h6>
                                                    </a>
                                                    <div class="fs-13 text-muted">
                                                        <p class="mb-1">We talked about a project on linkedin.</p>
                                                    </div>
                                                    <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                                                        <span><i class="mdi mdi-clock-outline"></i> 30 min ago</span>
                                                    </p>
                                                </div>
                                                <div class="px-2 fs-15">
                                                    <div class="form-check notification-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="messages-notification-check01">
                                                        <label class="form-check-label" for="messages-notification-check01"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-reset notification-item d-block dropdown-item">
                                            <div class="d-flex">
                                                <img src="assets/images/users/avatar-2.jpg" class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                                <div class="flex-grow-1">
                                                    <a href="#!" class="stretched-link">
                                                        <h6 class="mt-0 mb-1 fs-13 fw-semibold">Angela Bernier</h6>
                                                    </a>
                                                    <div class="fs-13 text-muted">
                                                        <p class="mb-1">Answered to your comment on the cash flow forecast's
                                                            graph 🔔.</p>
                                                    </div>
                                                    <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                                                        <span><i class="mdi mdi-clock-outline"></i> 2 hrs ago</span>
                                                    </p>
                                                </div>
                                                <div class="px-2 fs-15">
                                                    <div class="form-check notification-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="messages-notification-check02">
                                                        <label class="form-check-label" for="messages-notification-check02"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-reset notification-item d-block dropdown-item">
                                            <div class="d-flex">
                                                <img src="assets/images/users/avatar-6.jpg" class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                                <div class="flex-grow-1">
                                                    <a href="#!" class="stretched-link">
                                                        <h6 class="mt-0 mb-1 fs-13 fw-semibold">Kenneth Brown</h6>
                                                    </a>
                                                    <div class="fs-13 text-muted">
                                                        <p class="mb-1">Mentionned you in his comment on 📃 invoice #12501.
                                                        </p>
                                                    </div>
                                                    <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                                                        <span><i class="mdi mdi-clock-outline"></i> 10 hrs ago</span>
                                                    </p>
                                                </div>
                                                <div class="px-2 fs-15">
                                                    <div class="form-check notification-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="messages-notification-check03">
                                                        <label class="form-check-label" for="messages-notification-check03"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-reset notification-item d-block dropdown-item">
                                            <div class="d-flex">
                                                <img src="assets/images/users/avatar-8.jpg" class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                                <div class="flex-grow-1">
                                                    <a href="#!" class="stretched-link">
                                                        <h6 class="mt-0 mb-1 fs-13 fw-semibold">Maureen Gibson</h6>
                                                    </a>
                                                    <div class="fs-13 text-muted">
                                                        <p class="mb-1">We talked about a project on linkedin.</p>
                                                    </div>
                                                    <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                                                        <span><i class="mdi mdi-clock-outline"></i> 3 days ago</span>
                                                    </p>
                                                </div>
                                                <div class="px-2 fs-15">
                                                    <div class="form-check notification-check">
                                                        <input class="form-check-input" type="checkbox" value="" id="messages-notification-check04">
                                                        <label class="form-check-label" for="messages-notification-check04"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="my-3 text-center view-all">
                                            <button type="button" class="btn btn-soft-success waves-effect waves-light">View
                                                All Messages <i class="ri-arrow-right-line align-middle"></i></button>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade p-4" id="alerts-tab" role="tabpanel" aria-labelledby="alerts-tab"></div>

                                <div class="notification-actions" id="notification-actions">
                                    <div class="d-flex text-muted justify-content-center">
                                        Select <div id="select-content" class="text-body fw-semibold px-1">0</div> Result <button type="button" class="btn btn-link link-danger p-0 ms-3" data-bs-toggle="modal" data-bs-target="#removeNotificationModal">Remove</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="dropdown ms-sm-3 header-item topbar-user">
                        <button type="button" class="btn shadow-none" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="d-flex align-items-center">
                                <img class="rounded-circle header-profile-user" src="{{ asset('assets/images/users/avatar-1.jpg') }}" alt="Header Avatar">
                                <span class="text-start ms-xl-2">
                                    <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text"></span>
                                </span>
                            </span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <!-- item-->
                            <h6 class="dropdown-header">Welcome </h6>
                            <a class="dropdown-item" href="{{ route('logout') }}"><i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i> <span class="align-middle" data-key="t-logout">Logout</span></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- removeNotificationModal -->
    <div id="removeNotificationModal" class="modal fade zoomIn" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="NotificationModalbtn-close"></button>
                </div>
                <div class="modal-body">
                    <div class="mt-2 text-center">
                        <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop" colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon>
                        <div class="mt-4 pt-2 fs-15 mx-4 mx-sm-5">
                            <h4>Are you sure ?</h4>
                            <p class="text-muted mx-4 mb-0">Are you sure you want to remove this Notification ?</p>
                        </div>
                    </div>
                    <div class="d-flex gap-2 justify-content-center mt-4 mb-2">
                        <button type="button" class="btn w-sm btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn w-sm btn-danger" id="delete-notification">Yes, Delete It!</button>
                    </div>
                </div>

            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <!-- ========== App Menu ========== -->
    <div class="app-menu navbar-menu">
        <!-- LOGO -->
        <div class="navbar-brand-box">
            <!-- Dark Logo-->
            <a href="#" class="logo logo-dark">
                        <span class="logo-sm">
                            <img src="{{ asset('assets/images/maximaz-logo.png') }}" alt="" height="22">
                        </span>
                <span class="logo-lg">
                            <img src="{{ asset('assets/images/maximaz-logo.png') }}" alt="" height="17">
                        </span>
            </a>
            <!-- Light Logo-->
            <a href="#" class="logo logo-light">
                        <span class="logo-sm">
                            <img src="{{ asset('assets/images/maximaz-logo.png') }}" alt="" height="22">
                        </span>
                <span class="logo-lg">
                            <img src="{{ asset('assets/images/maximaz-logo.png') }}" alt="" height="35">
                        </span>
            </a>
            <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
                <i class="ri-record-circle-line"></i>
            </button>
        </div>

        <div id="scrollbar">
            <div class="container-fluid">

                <div id="two-column-menu"></div>
                <ul class="navbar-nav" id="navbar-nav">

                    <li class="menu-title"><span data-key="t-menu">Dashboard</span></li>
                    <li class="nav-item {{ in_array($title, ['Dashboard', 'Dashboard stockAvailability', 'Dashboard Inbound vs Return Trend', 'Dashboard Top Devices by Client', 'Lifecycle Status Distributor']) ? 'active' : '' }}">
                        <a class="nav-link menu-link"
                           href="#sidebarDashboard" data-bs-toggle="collapse" role="button"
                           aria-expanded="false" aria-controls="sidebarDashboard">
                            <i class="mdi mdi-view-dashboard-outline"></i>
                            <span data-key="t-widgets">Dashboard</span>
                        </a>

                        <div class="collapse menu-dropdown {{ in_array($title, ['Stock Overview', 'Utilization By Client', 'Dashboard Inbound vs Return Trend', 'Dashboard Top Devices by Client', 'Lifecycle Status Distributor']) ? 'show' : '' }}" id="sidebarDashboard">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a href="{{ route('dashboard') }}" class="nav-link {{ $title == 'Stock Overview' ? 'active' : '' }}" data-key="t-analytics"> Stock Overview </a>
                                </li>
                                <li class="nav-item">
                                    <a href="" class="nav-link {{ $title == 'Utilization By Client' ? 'active' : '' }}" data-key="t-analytics"> Utilization By Client </a>
                                </li>
                                <li class="nav-item">
                                    <a href="" class="nav-link {{ $title == 'RMA Monitoring' ? 'active' : '' }}" data-key="t-analytics"> RMA Monitoring </a>
                                </li>
                                <li class="nav-item">
                                    <a href="" class="nav-link {{ $title == 'Inbound vs Return Trend' ? 'active' : '' }}" data-key="t-analytics"> Inbound vs Return Trend </a>
                                </li>
                                <li class="nav-item">
                                    <a href="" class="nav-link {{ $title == 'Stock Monitoring' ? 'active' : '' }}" data-key="t-analytics"> Stock Monitoring </a>
                                </li>
                            </ul>
                        </div>
                    </li>


                    <li class="menu-title"><span data-key="t-menu">Main Menu</span></li>

                    <!-- Inbound -->
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ in_array($title, ['Receiving', 'Put Away']) ? 'active' : '' }}" href="#inbound" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="inbound">
                            <i class="mdi mdi-truck-cargo-container"></i>
                            <span data-key="t-widgets">Inbound</span>
                        </a>
                        <div class="collapse menu-dropdown {{ in_array($title, ['Receiving', 'Put Away']) ? 'show' : '' }}" id="inbound">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a href="{{ route('receiving') }}" class="nav-link {{ $title == 'Receiving' ? 'active' : '' }}" data-key="t-analytics">
                                        <i class="mdi mdi-file-document-edit-outline me-2"></i> Receiving
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('receiving.put.away') }}" class="nav-link {{ $title == 'Put Away' ? 'active' : '' }}" data-key="t-analytics">
                                        <i class="mdi mdi-arrow-down-bold-box-outline me-2"></i> Put Away
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <!-- Inventory -->
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ in_array($title, ['Inventory List', 'Stock Movement']) ? 'active' : '' }}" href="#inventory" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="inventory">
                            <i class="mdi mdi-warehouse"></i>
                            <span data-key="t-widgets">Inventory</span>
                        </a>
                        <div class="collapse menu-dropdown {{ in_array($title, ['Inventory List', 'Stock Movement']) ? 'show' : '' }}" id="inventory">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a href="" class="nav-link {{ $title == 'Inventory List' ? 'active' : '' }}" data-key="t-analytics">
                                        <i class="mdi mdi-format-list-bulleted me-2"></i> Inventory List
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="" class="nav-link {{ $title == 'Stock Movement' ? 'active' : '' }}" data-key="t-analytics">
                                        <i class="mdi mdi-arrow-decision-auto me-2"></i> Stock Movement
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <!-- Outbound -->
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ $title == 'Outbound' ? 'active' : '' }}" href="">
                            <i class="mdi mdi-truck-fast-outline"></i>
                            <span data-key="t-user"> Outbound </span>
                        </a>
                    </li>

                    <!-- RMA / Replacement -->
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ $title == 'RMA / Replacement' ? 'active' : '' }}" href="">
                            <i class="mdi mdi-data-matrix-edit"></i>
                            <span data-key="t-user"> RMA / Replacement </span>
                        </a>
                    </li>

                    <!-- Write-off -->
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ $title == 'Return' ? 'active' : '' }}" href="">
                            <i class="mdi mdi-undo-variant"></i>
                            <span data-key="t-user"> Write-off </span>
                        </a>
                    </li>

                    <li class="menu-title"><span data-key="t-menu">Warehouse</span></li>

                    <!-- Storage -->
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ in_array($title, ['Area', 'Rak', 'Lantai', 'Bin']) ? 'active' : '' }}" href="#storage" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="storage">
                            <i class="mdi mdi-store"></i>
                            <span data-key="t-widgets">Storage</span>
                        </a>
                        <div class="collapse menu-dropdown {{ in_array($title, ['Area', 'Rak', 'Lantai', 'Bin']) ? 'show' : '' }}" id="storage">
                            <ul class="nav nav-sm flex-column">
                                <li class="nav-item">
                                    <a href="{{ route('storage.area') }}" class="nav-link {{ $title == 'Area' ? 'active' : '' }}" data-key="t-analytics">
                                        <i class="mdi mdi-map-marker-radius-outline me-2"></i> Area
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('storage.rak') }}" class="nav-link {{ $title == 'Rak' ? 'active' : '' }}" data-key="t-analytics">
                                        <i class="mdi mdi-view-column me-2"></i> Rak
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('storage.lantai') }}" class="nav-link {{ $title == 'Lantai' ? 'active' : '' }}" data-key="t-analytics">
                                        <i class="mdi mdi-floor-plan me-2"></i> Lantai
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('storage.bin') }}" class="nav-link {{ $title == 'Bin' ? 'active' : '' }}" data-key="t-analytics">
                                        <i class="mdi mdi-package-variant me-2"></i> Bin
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <!-- Client -->
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ $title == 'Client' ? 'active' : '' }}" href="{{ route('client.index') }}">
                            <i class="mdi mdi-account"></i>
                            <span data-key="t-user"> Client </span>
                        </a>
                    </li>

                    <!-- User -->
                    <li class="nav-item">
                        <a class="nav-link menu-link {{ $title == 'User' ? 'active' : '' }}" href="{{ route('user.index') }}">
                            <i class="mdi mdi-account-circle"></i>
                            <span data-key="t-user"> User </span>
                        </a>
                    </li>

                </ul>

            </div>
        </div>

        <div class="sidebar-background"></div>
    </div>

    <div class="vertical-overlay"></div>

    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                @yield('content')

            </div>
        </div>

        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <script>document.write(new Date().getFullYear())</script> © Maximaz.
                    </div>
                    <div class="col-sm-6">
                        <div class="text-sm-end d-none d-sm-block">
                            Design & Develop by Trans Kargo Solusindo
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

</div>

<button onclick="topFunction()" class="btn btn-danger btn-icon" id="back-to-top">
    <i class="ri-arrow-up-line"></i>
</button>

<script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
<script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
<script src="{{ asset('assets/libs/feather-icons/feather.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/plugins/lord-icon-2.1.0.js') }}"></script>
<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/dashboard-projects.init.js') }}"></script>
<script src="{{ asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/sweetalerts.init.js') }}"></script>
<script src="{{ asset('assets/js/app.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="{{ asset('assets/js/pages/datatables.init.js') }}"></script>

<script src="{{ asset('assets/libs/dropzone/dropzone-min.js') }}"></script>
<script src="{{ asset('assets/libs/filepond/filepond.min.js') }}"></script>
<script src="{{ asset('assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.js') }}"></script>
<script src="{{ asset('assets/libs/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js') }}"></script>
<script src="{{ asset('assets/libs/filepond-plugin-image-exif-orientation/filepond-plugin-image-exif-orientation.min.js') }}"></script>
<script src="{{ asset('assets/libs/filepond-plugin-file-encode/filepond-plugin-file-encode.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/form-file-upload.init.js') }}"></script>

@yield('js')

@if($msg = Session::get('success'))
    <script>
        Swal.fire({
            title: 'Success',
            text: '{{ $msg }}',
            icon: 'success'
        });
    </script>
@endif

@if($msg = Session::get('error'))
    <script>
        Swal.fire({
            title: 'Failed',
            text: '{{ $msg }}',
            icon: 'error'
        });
    </script>
@endif
</body>

