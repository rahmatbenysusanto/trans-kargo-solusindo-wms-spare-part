@extends('layout.index')
@section('title', 'Summary Stock')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card bg-primary text-white mb-4 shadow-sm overflow-hidden">
                <div class="card-body p-4 position-relative">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="fw-bold mb-1 text-white">Summary Stock Dashboard</h3>
                            <p class="mb-0 opacity-75">Welcome! Access all inventory and stock reports from this central hub.
                            </p>
                        </div>
                    </div>
                    <div class="position-absolute end-0 top-0 p-3 opacity-25">
                        <i class="ti tabler-dashboard fs-1" style="font-size: 5rem !important;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Inventory List -->
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('dashboard.inventory.list') }}"
                class="card h-100 hover-elevate transition-all border-0 shadow-sm overflow-hidden text-decoration-none">
                <div class="card-body text-center p-4">
                    <div class="avatar avatar-lg mx-auto mb-3 bg-label-info rounded-3">
                        <i class="ti tabler-list-details fs-2"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Inventory List</h5>
                    <p class="text-muted small mb-0 font-small">Browse all items in stock with detail locations and status.
                    </p>
                </div>
                <div class="card-footer bg-light border-0 py-2 text-center">
                    <span class="text-info fw-medium small">View List <i class="ti tabler-chevron-right ms-1"></i></span>
                </div>
            </a>
        </div>

        <!-- Inventory Product -->
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('dashboard.product.summary') }}"
                class="card h-100 hover-elevate transition-all border-0 shadow-sm overflow-hidden text-decoration-none">
                <div class="card-body text-center p-4">
                    <div class="avatar avatar-lg mx-auto mb-3 bg-label-success rounded-3">
                        <i class="ti tabler-package fs-2"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Inventory Product</h5>
                    <p class="text-muted small mb-0 font-small">Summary of stock quantity grouped by product name and
                        number.</p>
                </div>
                <div class="card-footer bg-light border-0 py-2 text-center">
                    <span class="text-success fw-medium small">View Product <i
                            class="ti tabler-chevron-right ms-1"></i></span>
                </div>
            </a>
        </div>

        <!-- Stock Statement -->
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('dashboard.stock.statement') }}"
                class="card h-100 hover-elevate transition-all border-0 shadow-sm overflow-hidden text-decoration-none">
                <div class="card-body text-center p-4">
                    <div class="avatar avatar-lg mx-auto mb-3 bg-label-warning rounded-3">
                        <i class="ti tabler-file-report fs-2"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Stock Statement</h5>
                    <p class="text-muted small mb-0 font-small">Master data history of all inbound items and their current
                        status.</p>
                </div>
                <div class="card-footer bg-light border-0 py-2 text-center">
                    <span class="text-warning fw-medium small">View Statement <i
                            class="ti tabler-chevron-right ms-1"></i></span>
                </div>
            </a>
        </div>

        <!-- Cycle Count -->
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('dashboard.cycle-count') }}"
                class="card h-100 hover-elevate transition-all border-0 shadow-sm overflow-hidden text-decoration-none">
                <div class="card-body text-center p-4">
                    <div class="avatar avatar-lg mx-auto mb-3 bg-label-danger rounded-3">
                        <i class="ti tabler-scan fs-2"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Cycle Count</h5>
                    <p class="text-muted small mb-0 font-small">Verify physical stock levels against system data for
                        accuracy.</p>
                </div>
                <div class="card-footer bg-light border-0 py-2 text-center">
                    <span class="text-danger fw-medium small">Start Counting <i
                            class="ti tabler-chevron-right ms-1"></i></span>
                </div>
            </a>
        </div>
    </div>

    <style>
        .hover-elevate:hover {
            transform: translateY(-8px);
            box-shadow: 0 1rem 3rem rgba(100, 100, 100, 0.175) !important;
        }

        .transition-all {
            transition: all 0.3s ease-in-out;
        }

        .font-small {
            font-size: 0.85rem;
        }
    </style>
@endsection
