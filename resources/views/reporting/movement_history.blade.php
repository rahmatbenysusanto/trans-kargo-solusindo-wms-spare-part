@extends('layout.index')
@section('title', 'Movement History Report')
@section('layout_class', 'layout-menu-collapsed')

@section('content')
    <div class="row">
        <div class="col-12 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1 fw-bold text-dark">
                        <i class="ti tabler-arrows-left-right me-2 text-primary"></i> Movement History
                    </h4>
                    <p class="text-muted mb-0">Tracks every single Inbound, Outbound, & Internal Movement transaction</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-label-success fw-bold px-3">
                        <i class="ti tabler-file-spreadsheet me-2"></i> Export CSV
                    </button>
                    <button class="btn btn-label-primary fw-bold px-3" onclick="window.print()">
                        <i class="ti tabler-printer me-2"></i> Print history
                    </button>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body p-4 bg-white" style="border-radius: 16px;">
                    <form action="{{ route('reporting.movement-history') }}" method="GET">
                        <div class="row align-items-end g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-dark">SERIAL NUMBER</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text bg-white border-light-subtle"><i
                                            class="ti tabler-barcode text-primary"></i></span>
                                    <input type="text" class="form-control border-light-subtle" name="sn"
                                        value="{{ request('sn') }}" placeholder="Search SN...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-dark">START DATE</label>
                                <input type="date" class="form-control border-light-subtle" name="start_date"
                                    value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-dark">END DATE</label>
                                <input type="date" class="form-control border-light-subtle" name="end_date"
                                    value="{{ request('end_date') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-dark">MOVEMENT TYPE</label>
                                <select class="form-select border-light-subtle fw-bold" name="type">
                                    <option value="">All Types</option>
                                    <option value="Inbound" {{ request('type') == 'Inbound' ? 'selected' : '' }}>Inbound
                                    </option>
                                    <option value="Outbound" {{ request('type') == 'Outbound' ? 'selected' : '' }}>Outbound
                                    </option>
                                    <option value="Movement" {{ request('type') == 'Movement' ? 'selected' : '' }}>Internal
                                        Move</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex gap-2">
                                <button type="submit" class="btn btn-primary fw-bold w-100 py-2 shadow-sm">
                                    <i class="ti tabler-search me-2"></i> Search
                                </button>
                                <a href="{{ route('reporting.movement-history') }}"
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
                                    <th class="ps-4">Timestamp</th>
                                    <th>Activity & Reference</th>
                                    <th>Serial Number</th>
                                    <th>Product Details</th>
                                    <th>Movement Path</th>
                                    <th class="pe-4 text-center">User</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $history)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold text-dark"
                                                    style="font-size: 0.9rem;">{{ $history->created_at->format('d M Y') }}</span>
                                                <small class="text-muted fw-bold"
                                                    style="font-size: 0.7rem;">{{ $history->created_at->format('H:i:s') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center mb-1">
                                                @php
                                                    $typeClass = 'bg-label-success';
                                                    $typeIcon = 'arrow-down-left';
                                                    if ($history->type == 'Outbound') {
                                                        $typeClass = 'bg-label-danger';
                                                        $typeIcon = 'arrow-up-right';
                                                    } elseif ($history->type == 'Movement') {
                                                        $typeClass = 'bg-label-info';
                                                        $typeIcon = 'arrows-exchange';
                                                    }
                                                @endphp
                                                <span class="badge {{ $typeClass }} p-1 rounded-circle me-2"><i
                                                        class="ti tabler-{{ $typeIcon }} fs-6"></i></span>
                                                <span class="fw-bold text-dark">{{ $history->type }}</span>
                                            </div>
                                            <span class="badge bg-light text-muted border-light-subtle py-1 px-2 fw-bold"
                                                style="font-size: 0.65rem;">REF:
                                                {{ $history->reference_number ?? 'SYSTEM' }}</span>
                                        </td>
                                        <td><span
                                                class="fw-bold text-primary font-monospace">{{ $history->serial_number }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span
                                                    class="fw-bold text-dark small">{{ $history->inventory->part_name ?? 'Unknown' }}</span>
                                                <small class="text-muted" style="font-size: 0.65rem;">CAT:
                                                    {{ $history->category }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="text-center">
                                                    <small class="text-muted d-block fw-bold text-uppercase"
                                                        style="font-size: 0.55rem;">FROM</small>
                                                    <span
                                                        class="badge bg-light text-dark border-light-subtle py-1 px-2 fs-tiny fw-bold">{{ $history->from_location ?: 'SUPPLIER' }}</span>
                                                </div>
                                                <i class="ti tabler-chevron-right text-primary opacity-50"></i>
                                                <div class="text-center">
                                                    <small class="text-muted d-block fw-bold text-uppercase"
                                                        style="font-size: 0.55rem;">TO</small>
                                                    <span
                                                        class="badge bg-label-primary border-0 py-1 px-2 fs-tiny fw-bold">{{ $history->to_location ?: 'CLIENT' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="pe-4 text-center">
                                            <div class="d-flex flex-column align-items-center">
                                                <span class="badge bg-label-dark p-2 rounded-circle mb-1"><i
                                                        class="ti tabler-user fs-6"></i></span>
                                                <small class="fw-bold text-muted"
                                                    style="font-size: 0.7rem;">{{ $history->user }}</small>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="py-5 opacity-50">
                                                <i class="ti tabler-history-off fs-1 mb-3"></i>
                                                <h5 class="fw-bold">No History Found</h5>
                                                <p class="small">Try adjusting your date range or filter criteria.</p>
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

        .fs-tiny {
            font-size: 0.65rem;
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
