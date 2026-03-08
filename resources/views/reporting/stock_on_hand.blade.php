@extends('layout.index')
@section('title', 'Stock on Hand Report')
@section('layout_class', 'layout-menu-collapsed')

@section('content')
    <div class="row">
        <div class="col-12 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1 fw-bold text-dark">
                        <i class="ti tabler-box-seam me-2 text-primary"></i> Stock on Hand
                    </h4>
                    <p class="text-muted mb-0">Real-time inventory levels by Serial Number & Part Specification</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-label-success fw-bold px-3">
                        <i class="ti tabler-file-spreadsheet me-2"></i> Export Excel
                    </button>
                    <button class="btn btn-label-primary fw-bold px-3" onclick="window.print()">
                        <i class="ti tabler-printer me-2"></i> Print Report
                    </button>
                </div>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="col-12 mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm bg-white"
                        style="border-radius: 12px; border-left: 4px solid #7367f0 !important;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="badge bg-label-primary p-2 me-3 rounded-3">
                                    <i class="ti tabler-package fs-3"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-muted small fw-bold text-uppercase ls-1">Total Items</h6>
                                    <h4 class="mb-0 fw-bold">{{ $data->total() }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm bg-white"
                        style="border-radius: 12px; border-left: 4px solid #28c76f !important;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="badge bg-label-success p-2 me-3 rounded-3">
                                    <i class="ti tabler-circle-check fs-3"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-muted small fw-bold text-uppercase ls-1">Available</h6>
                                    <h4 class="mb-0 fw-bold">{{ $data->where('status', 'Available')->count() }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm bg-white"
                        style="border-radius: 12px; border-left: 4px solid #ea5455 !important;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="badge bg-label-danger p-2 me-3 rounded-3">
                                    <i class="ti tabler-alert-circle fs-3"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-muted small fw-bold text-uppercase ls-1">Faulty / Scrap</h6>
                                    <h4 class="mb-0 fw-bold">{{ $data->where('status', 'Faulty')->count() }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm bg-white"
                        style="border-radius: 12px; border-left: 4px solid #ff9f43 !important;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="badge bg-label-warning p-2 me-3 rounded-3">
                                    <i class="ti tabler-clock-pause fs-3"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-muted small fw-bold text-uppercase ls-1">Reserved / Pending</h6>
                                    <h4 class="mb-0 fw-bold">{{ $data->where('status', 'Reserved')->count() }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body p-4 bg-white" style="border-radius: 16px;">
                    <form action="{{ route('reporting.stock-on-hand') }}" method="GET">
                        <div class="row align-items-end g-4">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-dark">FILTER BY CLIENT</label>
                                <select class="form-select border-light-subtle select2" name="client_id">
                                    <option value="">All Clients</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}"
                                            {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                            {{ $client->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small fw-bold text-dark">SEARCH PART NAME / SERIAL NUMBER</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text bg-white border-light-subtle"><i
                                            class="ti tabler-search text-primary"></i></span>
                                    <input type="text" class="form-control border-light-subtle" name="search"
                                        value="{{ request('search') }}" placeholder="Search by SN, Part Name, or SKU...">
                                </div>
                            </div>
                            <div class="col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary fw-bold w-100 py-2 shadow-sm">
                                    <i class="ti tabler-filter me-2"></i> Apply Filters
                                </button>
                                <a href="{{ route('reporting.stock-on-hand') }}"
                                    class="btn btn-label-secondary fw-bold w-50 py-2">
                                    <i class="ti tabler-rotate me-1"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <div class="card-body p-0 bg-white">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 custom-table" id="stockTable">
                            <thead class="bg-label-primary text-uppercase small fw-bold">
                                <tr>
                                    <th class="ps-4">Asset Info</th>
                                    <th>Product Identity</th>
                                    <th>Serial Number</th>
                                    <th>Client</th>
                                    <th>Status</th>
                                    <th>Location</th>
                                    <th class="pe-4 text-center">Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $item)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-3 ">
                                                    <span
                                                        class="avatar-initial rounded bg-label-dark font-small fw-bold">{{ substr($item->unique_id, -3) }}</span>
                                                </div>
                                                <span class="fw-bold text-dark">{{ $item->unique_id }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold text-dark"
                                                    style="font-size: 0.95rem;">{{ $item->part_name }}</span>
                                                <small
                                                    class="text-muted font-small fw-medium">{{ $item->part_number }}</small>
                                            </div>
                                        </td>
                                        <td><span class="fw-bold text-primary font-monospace"
                                                style="letter-spacing: -0.5px;">{{ $item->serial_number }}</span></td>
                                        <td><span
                                                class="badge bg-label-info rounded-pill px-3 fw-bold">{{ $item->client->name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $statusClass = 'bg-label-success';
                                                $statusIcon = 'circle-check';
                                                if ($item->status == 'Reserved' || $item->status == 'Pending') {
                                                    $statusClass = 'bg-label-warning';
                                                    $statusIcon = 'clock-pause';
                                                }
                                                if (
                                                    $item->status == 'Faulty' ||
                                                    $item->status == 'RMA' ||
                                                    $item->status == 'Scrap'
                                                ) {
                                                    $statusClass = 'bg-label-danger';
                                                    $statusIcon = 'alert-circle';
                                                }
                                            @endphp
                                            <span class="badge {{ $statusClass }} py-2 px-3">
                                                <i class="ti tabler-{{ $statusIcon }} me-1 fs-6"></i>
                                                {{ strtoupper($item->status ?? 'Available') }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-light text-dark border-light-subtle py-2 px-3">
                                                    <i class="ti tabler-map-pin me-2 text-primary"></i>
                                                    {{ $item->storageLevel ? $item->storageLevel->bin->rak->zone->name . '-' . $item->storageLevel->bin->rak->name . '-' . $item->storageLevel->bin->name . '-' . $item->storageLevel->name : 'No Loc' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="pe-4 text-center">
                                            <div class="fw-bold fs-4 text-dark">{{ $item->qty }}</div>
                                            <small class="text-muted fs-tiny text-uppercase fw-bold">Unit</small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="py-5">
                                                <i
                                                    class="ti tabler-database-x fs-1 text-muted mb-3 d-block opacity-25"></i>
                                                <h5 class="text-muted fw-bold">Stock Database Empty</h5>
                                                <p class="text-muted small">No items matches your filter criteria.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top py-4 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <p class="mb-0 text-muted small">Showing <b>{{ $data->firstItem() }}</b> to
                            <b>{{ $data->lastItem() }}</b> of <b>{{ $data->total() }}</b> records</p>
                        <div>
                            {{ $data->appends(request()->input())->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .custom-table thead th {
            font-size: 0.75rem;
            letter-spacing: 0.8px;
            color: #7367f0;
            background: rgba(115, 103, 240, 0.05);
            border-bottom: 2px solid rgba(115, 103, 240, 0.1);
            padding: 1.2rem;
        }

        .custom-table tbody td {
            padding: 1.2rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.03);
        }

        .avatar-initial {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }

        .form-select,
        .form-control {
            border-radius: 8px;
            padding: 0.6rem 1rem;
            transition: all 0.3s ease;
        }

        .ls-1 {
            letter-spacing: 1px;
        }

        @media print {

            .btn,
            .sidebar,
            .card-footer,
            form,
            .layout-navbar {
                display: none !important;
            }

            .card {
                box-shadow: none !important;
                border: 1px solid #eee !important;
            }

            .content-wrapper {
                padding: 0 !important;
            }
        }
    </style>
@endsection
