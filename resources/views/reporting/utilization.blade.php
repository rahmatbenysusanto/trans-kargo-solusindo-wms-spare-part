@extends('layout.index')
@section('title', 'Utilization Report')
@section('layout_class', 'layout-menu-collapsed')

@section('content')
    <div class="row">
        <div class="col-12 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1 fw-bold text-dark">
                        <i class="ti tabler-chart-dots me-2 text-primary"></i> Utilization Analysis
                    </h4>
                    <p class="text-muted mb-0">Analysis of spare parts consumption for client incident support</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-label-success fw-bold px-3">
                        <i class="ti tabler-file-spreadsheet me-1"></i> Export Excel
                    </button>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body p-4 bg-white" style="border-radius: 16px;">
                    <form action="{{ route('reporting.utilization') }}" method="GET">
                        <div class="row align-items-end g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-dark">SELECT CLIENT</label>
                                <select class="form-select border-light-subtle select2" name="client_id">
                                    <option value="">All Clients</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}"
                                            {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                            {{ $client->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">CONSUMPTION PERIOD</label>
                                <div class="d-flex gap-2 text-nowrap">
                                    <input type="date" class="form-control border-light-subtle" name="start_date"
                                        value="{{ request('start_date') }}">
                                    <span class="align-self-center text-muted fw-bold">to</span>
                                    <input type="date" class="form-control border-light-subtle" name="end_date"
                                        value="{{ request('end_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3 offset-md-2 d-flex gap-2">
                                <button type="submit" class="btn btn-primary fw-bold w-100 py-2 shadow-sm">
                                    <i class="ti tabler-search me-2"></i> Run Analysis
                                </button>
                                <a href="{{ route('reporting.utilization') }}"
                                    class="btn btn-label-secondary fw-bold px-3 py-2">
                                    <i class="ti tabler-rotate"></i>
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
                        <table class="table table-hover align-middle mb-0 custom-table">
                            <thead class="bg-label-primary text-uppercase small fw-bold">
                                <tr>
                                    <th class="ps-4">Outbound Date</th>
                                    <th>Client Target</th>
                                    <th>TKS Delivery Note</th>
                                    <th>Product Details</th>
                                    <th>Serial Number</th>
                                    <th>Ticket / ITSM Ref</th>
                                    <th class="pe-4 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $row)
                                    <tr>
                                        <td class="ps-4">
                                            <span
                                                class="fw-bold text-dark">{{ \Carbon\Carbon::parse($row->outbound->outbound_date)->format('d M Y') }}</span>
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-label-info rounded-pill px-3 fw-bold">{{ $row->outbound->client->name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-muted border-light-subtle py-1 px-2 fw-bold"
                                                style="font-size: 0.7rem;">{{ $row->outbound->tks_dn_number ?? '-' }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold text-dark small">{{ $row->part_name }}</span>
                                                <small class="text-muted" style="font-size: 0.65rem;">P/N:
                                                    {{ $row->part_number }}</small>
                                            </div>
                                        </td>
                                        <td><span
                                                class="fw-bold text-primary font-monospace">{{ $row->serial_number }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="ti tabler-ticket me-2 text-warning fs-5"></i>
                                                <span
                                                    class="fw-bold text-secondary">{{ $row->outbound->itsm_number ?? $row->outbound->number }}</span>
                                            </div>
                                        </td>
                                        <td class="pe-4 text-center">
                                            <span class="badge bg-label-success rounded-pill px-3 py-2 fw-bold">
                                                <i class="ti tabler-check me-1"></i> UTILIZED
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="py-5 opacity-50">
                                                <i class="ti tabler-chart-bar-off fs-1 mb-3"></i>
                                                <h5 class="fw-bold">No Records Found</h5>
                                                <p class="small">Try expanding your filter parameters.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top py-4 px-4">
                    {{ $data->appends(request()->input())->links('pagination::bootstrap-4') }}
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

        .form-select,
        .form-control {
            border-radius: 8px;
            padding: 0.6rem 1rem;
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
        }
    </style>
@endsection
