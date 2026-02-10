@extends('layout.index')
@section('title', 'Utilization Report')
@section('layout_class', 'layout-menu-collapsed')

@section('content')
    <div class="container-fluid flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12 mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1 text-primary fw-bold"><i class="ti tabler-chart-dots me-2"></i>Utilization Report</h4>
                        <p class="text-muted mb-0 small text-uppercase ls-1 fw-medium mt-n1">Spare part usage analysis for
                            client incident support</p>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="col-12 mb-4">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <form action="{{ route('reporting.utilization') }}" method="GET">
                            <div class="row align-items-end g-3">
                                <div class="col-md-3">
                                    <label class="form-label small text-muted text-uppercase fw-bold">Client</label>
                                    <select class="form-select border-0 bg-light-subtle fw-bold" name="client_id">
                                        <option value="">All Clients</option>
                                        @foreach ($clients as $client)
                                            <option value="{{ $client->id }}"
                                                {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                                {{ $client->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small text-muted text-uppercase fw-bold">Date Range</label>
                                    <div class="d-flex gap-2">
                                        <input type="date" class="form-control border-0 bg-light-subtle shadow-none"
                                            name="start_date" value="{{ request('start_date') }}">
                                        <input type="date" class="form-control border-0 bg-light-subtle shadow-none"
                                            name="end_date" value="{{ request('end_date') }}">
                                    </div>
                                </div>
                                <div class="col-md-3 offset-md-3 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary waves-effect w-100 fw-bold py-2">
                                        <i class="ti tabler-search me-1"></i> Analysis Data
                                    </button>
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
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-uppercase fs-tiny fw-bold border-top-0">
                                    <tr>
                                        <th class="ps-4">Outbound Date</th>
                                        <th>Client</th>
                                        <th>DN Number (TKS)</th>
                                        <th>Product Description</th>
                                        <th>Serial Number</th>
                                        <th>Ticket / ITSM</th>
                                        <th class="pe-4 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $row)
                                        <tr>
                                            <td class="ps-4 fw-bold text-dark">{{ $row->outbound->outbound_date ?? 'N/A' }}
                                            </td>
                                            <td><span
                                                    class="badge bg-label-info border-0">{{ $row->outbound->client->name ?? 'N/A' }}</span>
                                            </td>
                                            <td><span
                                                    class="fw-medium text-muted small">{{ $row->outbound->tks_dn_number ?? '-' }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-bold text-dark small">{{ $row->part_name }}</span>
                                                    <small class="text-muted fs-tiny">{{ $row->part_number }}</small>
                                                </div>
                                            </td>
                                            <td><span class="fw-bold text-primary">{{ $row->serial_number }}</span></td>
                                            <td><span
                                                    class="badge bg-label-secondary border-0">{{ $row->outbound->itsm_number ?? $row->outbound->number }}</span>
                                            </td>
                                            <td class="pe-4 text-center">
                                                <span class="badge bg-label-success border-0 px-3">Utilized</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <i class="ti tabler-chart-bar-off fs-1 text-muted mb-3 d-block"></i>
                                                <h6 class="text-muted mb-0">No utilization data recorded for this period.
                                                </h6>
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

        .table> :not(caption)>*>* {
            padding: 1.1rem 1.25rem;
        }
    </style>
@endsection
