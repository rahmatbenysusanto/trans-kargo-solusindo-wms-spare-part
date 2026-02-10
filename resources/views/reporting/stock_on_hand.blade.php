@extends('layout.index')
@section('title', 'Stock on Hand Report')
@section('layout_class', 'layout-menu-collapsed')

@section('content')
    <div class="container-fluid flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12 mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1 text-primary fw-bold"><i class="ti tabler-box me-2"></i>Stock on Hand</h4>
                        <p class="text-muted mb-0 small text-uppercase ls-1 fw-medium mt-n1">Real-time inventory levels by
                            Serial Number & Part Specification</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-label-success waves-effect btn-sm py-2">
                            <i class="ti tabler-file-spreadsheet me-1"></i> Export Excel
                        </button>
                        <button class="btn btn-label-primary waves-effect btn-sm py-2" onclick="window.print()">
                            <i class="ti tabler-printer me-1"></i> Print
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="col-12 mb-4">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <form action="{{ route('reporting.stock-on-hand') }}" method="GET">
                            <div class="row align-items-end g-3">
                                <div class="col-md-3">
                                    <label class="form-label small text-muted text-uppercase fw-bold">Filter Client</label>
                                    <select class="form-select border-0 bg-light-subtle fw-bold" name="client_id">
                                        <option value="">All Clients</option>
                                        @foreach ($clients as $client)
                                            <option value="{{ $client->id }}"
                                                {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                                {{ $client->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small text-muted text-uppercase fw-bold">Search Part /
                                        SN</label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text bg-light-subtle border-0"><i
                                                class="ti tabler-search text-primary"></i></span>
                                        <input type="text" class="form-control border-0 bg-light-subtle fw-medium"
                                            name="search" value="{{ request('search') }}"
                                            placeholder="Search by SN, Part Name, or SKU...">
                                    </div>
                                </div>
                                <div class="col-md-4 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary waves-effect px-4 py-2 w-100 fw-bold">
                                        <i class="ti tabler-filter me-1"></i> Apply Filters
                                    </button>
                                    <a href="{{ route('reporting.stock-on-hand') }}"
                                        class="btn btn-label-secondary waves-effect px-4 py-2 w-100 fw-bold">
                                        Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 12px;">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="stockTable">
                                <thead class="bg-light text-uppercase fs-tiny fw-bold border-top-0">
                                    <tr>
                                        <th class="ps-4">Asset ID</th>
                                        <th>Product Details</th>
                                        <th>Serial Number</th>
                                        <th>Client</th>
                                        <th>Status</th>
                                        <th>Location</th>
                                        <th class="pe-4 text-center">Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $item)
                                        <tr>
                                            <td class="ps-4 fw-bold text-dark"><span
                                                    class="badge bg-label-dark rounded-pill shadow-xs">{{ $item->unique_id }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-bold text-dark">{{ $item->part_name }}</span>
                                                    <small class="text-muted font-small">{{ $item->part_number }}</small>
                                                </div>
                                            </td>
                                            <td><span class="fw-medium text-primary">{{ $item->serial_number }}</span></td>
                                            <td><span
                                                    class="badge bg-label-info border-0 rounded-pill px-3">{{ $item->client->name ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                @php
                                                    $statusClass = 'bg-label-success';
                                                    if ($item->status == 'Reserved') {
                                                        $statusClass = 'bg-label-warning';
                                                    }
                                                    if ($item->status == 'Faulty') {
                                                        $statusClass = 'bg-label-danger';
                                                    }
                                                @endphp
                                                <span
                                                    class="badge {{ $statusClass }} border-0">{{ $item->status ?? 'Available' }}</span>
                                            </td>
                                            <td>
                                                <span class="text-muted small fw-medium">
                                                    <i class="ti tabler-map-pin me-1 text-primary fs-tiny"></i>
                                                    {{ $item->storageLevel ? $item->storageLevel->bin->rak->zone->name . ' - ' . $item->storageLevel->name : 'N/A' }}
                                                </span>
                                            </td>
                                            <td class="pe-4 text-center"><span
                                                    class="fw-bold fs-5">{{ $item->qty }}</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <i class="ti tabler-search-off fs-1 text-muted mb-3 d-block"></i>
                                                <h6 class="text-muted mb-0">No matching stock items found.</h6>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top py-3 px-4">
                        {{ $data->appends(request()->input())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .fs-tiny {
            font-size: 0.65rem;
        }

        .ls-1 {
            letter-spacing: 1px;
        }

        .bg-light-subtle {
            background-color: #f8f9fa !important;
        }

        .shadow-xs {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .table> :not(caption)>*>* {
            padding: 1rem 1.25rem;
        }

        @media print {

            .btn,
            .sidebar,
            .card-footer,
            form {
                display: none !important;
            }

            .card {
                box-shadow: none !important;
                border: 1px solid #eee !important;
            }
        }
    </style>
@endsection
