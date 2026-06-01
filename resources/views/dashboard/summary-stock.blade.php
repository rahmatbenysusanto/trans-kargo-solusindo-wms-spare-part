@extends('layout.index')
@section('title', 'Summary Stock')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card bg-gradient-primary text-white mb-4 shadow-sm overflow-hidden" style="background: linear-gradient(135deg, #7367f0, #9e95f5); border-radius:16px;">
            <div class="card-body p-4 position-relative">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="fw-bold mb-1 text-white">📊 Summary Stock Dashboard</h3>
                        <p class="mb-0 opacity-75">Akses semua laporan inventory dan stock dari hub ini.</p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <span class="badge bg-white text-primary fw-bold px-3 py-2 rounded-pill">
                            <i class="ti tabler-calendar me-1"></i> {{ now()->format('d M Y') }}
                        </span>
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
            class="card h-100 hover-elevate transition-all border-0 shadow-sm overflow-hidden text-decoration-none"
            style="border-radius:16px;">
            <div class="card-body text-center p-4">
                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center"
                     style="width:64px;height:64px;border-radius:16px;background:rgba(0,207,232,0.12);">
                    <i class="ti tabler-list-details fs-1 text-info"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">Inventory List</h5>
                <p class="text-muted small mb-0">Browse all items with detail locations and status.</p>
            </div>
            <div class="card-footer bg-light border-0 py-3 text-center">
                <span class="text-info fw-medium small">View List <i class="ti tabler-chevron-right ms-1"></i></span>
            </div>
        </a>
    </div>

    <!-- Product Summary -->
    <div class="col-md-6 col-lg-3">
        <a href="{{ route('dashboard.product.summary') }}"
            class="card h-100 hover-elevate transition-all border-0 shadow-sm overflow-hidden text-decoration-none"
            style="border-radius:16px;">
            <div class="card-body text-center p-4">
                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center"
                     style="width:64px;height:64px;border-radius:16px;background:rgba(40,199,111,0.12);">
                    <i class="ti tabler-package fs-1 text-success"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">Product Summary</h5>
                <p class="text-muted small mb-0">Stock quantity grouped by product name and number.</p>
            </div>
            <div class="card-footer bg-light border-0 py-3 text-center">
                <span class="text-success fw-medium small">View Product <i class="ti tabler-chevron-right ms-1"></i></span>
            </div>
        </a>
    </div>

    <!-- Stock Statement -->
    <div class="col-md-6 col-lg-3">
        <a href="{{ route('dashboard.stock.statement') }}"
            class="card h-100 hover-elevate transition-all border-0 shadow-sm overflow-hidden text-decoration-none"
            style="border-radius:16px;">
            <div class="card-body text-center p-4">
                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center"
                     style="width:64px;height:64px;border-radius:16px;background:rgba(255,171,0,0.12);">
                    <i class="ti tabler-file-report fs-1 text-warning"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">Stock Statement</h5>
                <p class="text-muted small mb-0">Master data history of all inbound items and current status.</p>
            </div>
            <div class="card-footer bg-light border-0 py-3 text-center">
                <span class="text-warning fw-medium small">View Statement <i class="ti tabler-chevron-right ms-1"></i></span>
            </div>
        </a>
    </div>

    <!-- Cycle Count -->
    <div class="col-md-6 col-lg-3">
        <a href="{{ route('dashboard.cycle-count') }}"
            class="card h-100 hover-elevate transition-all border-0 shadow-sm overflow-hidden text-decoration-none"
            style="border-radius:16px;">
            <div class="card-body text-center p-4">
                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center"
                     style="width:64px;height:64px;border-radius:16px;background:rgba(234,84,85,0.12);">
                    <i class="ti tabler-scan fs-1 text-danger"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">Cycle Count</h5>
                <p class="text-muted small mb-0">Verify physical stock levels against system data.</p>
            </div>
            <div class="card-footer bg-light border-0 py-3 text-center">
                <span class="text-danger fw-medium small">Start Counting <i class="ti tabler-chevron-right ms-1"></i></span>
            </div>
        </a>
    </div>

    <!-- Quick Links -->
    <div class="col-12 mt-2">
        <div class="card border-0 shadow-sm" style="border-radius:16px;">
            <div class="card-body p-3">
                <h6 class="fw-bold mb-3"><i class="ti tabler-link me-1"></i> Quick Actions</h6>
                <div class="row g-2">
                    <div class="col-md-3">
                        <a href="{{ route('receiving') }}" class="btn btn-outline-primary w-100">
                            <i class="ti tabler-download me-1"></i> Inbound
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('outbound.index') }}" class="btn btn-outline-warning w-100">
                            <i class="ti tabler-upload me-1"></i> Outbound
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('inventory.index') }}" class="btn btn-outline-info w-100">
                            <i class="ti tabler-search me-1"></i> Inventory List
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('receiving.quick-return') }}" class="btn btn-outline-success w-100">
                            <i class="ti tabler-arrow-back-up me-1"></i> Back to WH
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-elevate:hover {
        transform: translateY(-8px);
        box-shadow: 0 1rem 3rem rgba(100,100,100,0.175) !important;
    }
    .transition-all {
        transition: all 0.3s ease-in-out;
    }
</style>
@endsection
