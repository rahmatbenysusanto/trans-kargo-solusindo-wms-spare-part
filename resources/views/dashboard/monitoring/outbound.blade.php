@extends('layout.index')
@section('title', 'Outbound Monitoring')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--single {
            border: 1px solid #dbdade !important;
            border-radius: 0.375rem !important;
            height: 38px !important;
            display: flex !important;
            align-items: center !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #6f6b7d !important;
            padding-left: 0.9rem !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0">Outbound Monitoring</h4>
            <div>
                <!-- Action buttons removed for monitoring only -->
            </div>
        </div>

        <div class="col-12">
            <!-- Filter Section -->
            <div class="card mb-3 shadow-sm border border-light-subtle">
                <div class="card-header bg-light py-2 px-3 border-bottom">
                    <h6 class="card-title mb-0 text-dark small fw-bold"><i
                            class="ti tabler-filter me-2 text-secondary"></i>Search & Filter</h6>
                </div>
                <div class="card-body py-2 px-3">
                    <form action="{{ url()->current() }}" method="GET">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold mb-1">Client</label>
                                <select name="client_id" class="form-select form-select-sm select2"
                                    onchange="this.form.submit()">
                                    <option value="">All Clients</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}"
                                            {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                            {{ $client->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold mb-1">Stock Category</label>
                                <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">All Categories</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat }}"
                                            {{ request('category') == $cat ? 'selected' : '' }}>
                                            {{ $cat }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold mb-1">Keyword</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="ti tabler-search"></i></span>
                                    <input type="text" class="form-control" name="search"
                                        value="{{ request()->get('search') }}"
                                        placeholder="Search Number, SAP PO, DN, RMA, ITSM ...">
                                    <button class="btn btn-primary" type="submit">Filter</button>
                                    <a href="{{ url()->current() }}" class="btn btn-label-secondary"><i
                                            class="ti tabler-refresh"></i></a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- List Section -->
            <div class="card shadow-sm border border-light-subtle">
                <div class="card-header bg-light py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 text-dark small fw-bold"><i
                            class="ti tabler-list me-2 text-secondary"></i>Outbound Records</h6>
                    <small class="text-muted">{{ $data->total() }} Records Found</small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0 text-nowrap table-sm"
                            style="font-size: 0.82rem;">
                            <thead class="table-light">
                                <tr>
                                    <th width="30" class="py-2 px-3">#</th>
                                    <th class="py-2 px-3">Client</th>
                                    <th class="py-2 px-3">Outbound Number</th>
                                    <th class="py-2 px-3">Category</th>
                                    <th class="py-2 px-3 text-center">Status</th>
                                    <th class="py-2 px-3">Qty</th>
                                    <th class="py-2 px-3">SAP PO#</th>
                                    <th class="py-2 px-3">NTT DN#</th>
                                    <th class="py-2 px-3">RMA# / ITSM#</th>
                                    <th class="py-2 px-3">Date</th>
                                    <th class="py-2 px-3">Processed By</th>
                                    <th class="py-2 px-3 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $item)
                                    <tr>
                                        <td class="py-2 px-3">
                                            {{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}
                                        </td>
                                        <td class="py-2 px-3 small">{{ $item->client->name ?? '-' }}</td>
                                        <td class="py-2 px-3 fw-bold text-dark">{{ $item->number }}</td>
                                        <td class="py-2 px-3">
                                            <span class="badge bg-label-secondary badge-sm"
                                                style="font-size: 0.72rem;">{{ strtoupper($item->category) }}</span>
                                        </td>
                                        <td class="py-2 px-3 text-center">
                                            @php
                                                $statusClass = 'bg-label-secondary';
                                                if ($item->status == 'new') {
                                                    $statusClass = 'bg-label-info';
                                                } elseif ($item->status == 'process') {
                                                    $statusClass = 'bg-label-warning';
                                                } elseif ($item->status == 'cancel') {
                                                    $statusClass = 'bg-label-danger';
                                                } elseif ($item->status == 'close') {
                                                    $statusClass = 'bg-label-success';
                                                }
                                            @endphp
                                            <span class="badge {{ $statusClass }} badge-sm"
                                                style="font-size: 0.72rem;">{{ strtoupper($item->status) }}</span>
                                        </td>
                                        <td class="py-2 px-3 fw-medium">{{ $item->qty }}</td>
                                        <td class="py-2 px-3 small">{{ $item->sap_po_number ?? '-' }}</td>
                                        <td class="py-2 px-3 small text-dark fw-medium">{{ $item->ntt_dn_number ?? '-' }}</td>
                                        <td class="py-2 px-3">
                                            @if ($item->rma_number || $item->itsm_number)
                                                <div class="small">
                                                    @if ($item->rma_number)
                                                        <span class="text-warning fw-medium">R:{{ $item->rma_number }}</span>
                                                    @endif
                                                    @if ($item->itsm_number)
                                                        <span class="text-info fw-medium ms-1">I:{{ $item->itsm_number }}</span>
                                                    @endif
                                                </div>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="py-2 px-3 small">{{ $item->outbound_date }}</td>
                                        <td class="py-2 px-3 small">{{ $item->outbound_by }}</td>
                                        <td class="py-2 px-3 text-center">
                                            <a href="{{ route('outboundMonitoring.show', $item->id) }}" class="btn btn-xs btn-primary p-1" title="Detail">
                                                <i class="ti tabler-eye fs-6"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center py-4">
                                            <i class="ti tabler-box-off text-muted mb-2" style="font-size: 2rem;"></i>
                                            <p class="text-muted small mb-0">No records found.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($data->hasPages())
                        <div class="card-footer py-2 px-3 border-top">
                            {{ $data->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "-- Choose Client --",
                allowClear: true,
                width: '100%'
            });
        });
    </script>
@endsection
