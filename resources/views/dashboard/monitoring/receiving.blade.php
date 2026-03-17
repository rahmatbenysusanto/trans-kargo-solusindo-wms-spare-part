@extends('layout.index')
@section('title', 'Receiving Monitoring')

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
            <h4 class="fw-bold mb-0">Receiving Monitoring</h4>
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
                            <div class="col-md-2">
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
                            <div class="col-md-2">
                                <label class="form-label small fw-bold mb-1">Request Type</label>
                                <select name="request_type" class="form-select form-select-sm"
                                    onchange="this.form.submit()">
                                    <option value="">All Types</option>
                                    @foreach ($requestTypes as $rt)
                                        <option value="{{ $rt }}"
                                            {{ request('request_type') == $rt ? 'selected' : '' }}>
                                            {{ $rt }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small fw-bold mb-1">Keyword</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="ti tabler-search"></i></span>
                                    <input type="text" class="form-control" name="search"
                                        value="{{ request()->get('search') }}"
                                        placeholder="Search Number, RN#, SAP PO#, RMA#, Vendor ...">
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
                            class="ti tabler-list me-2 text-secondary"></i>Receiving Records</h6>
                    <small class="text-muted">{{ $inbound->total() }} Records Found</small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0 text-nowrap table-sm"
                            style="font-size: 0.82rem;">
                            <thead class="table-light">
                                <tr>
                                    <th width="30" class="py-2 px-3">#</th>
                                    <th class="py-2 px-3">Client</th>
                                    <th class="py-2 px-3">Transaction Number</th>
                                    <th class="py-2 px-3">Stock Category</th>
                                    <th class="py-2 px-3">Request Type</th>
                                    <th class="py-2 px-3 text-center">Status</th>
                                    <th class="py-2 px-3">NTT RN#</th>
                                    <th class="py-2 px-3">RMA# / ITSM#</th>
                                    <th class="py-2 px-3">Vendor / Supplier</th>
                                    <th class="py-2 px-3">Received By</th>
                                    <th class="py-2 px-3">Received Date</th>
                                    <th class="py-2 px-3 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($inbound as $item)
                                    <tr>
                                        <td class="py-2 px-3">
                                            {{ $loop->iteration + ($inbound->currentPage() - 1) * $inbound->perPage() }}
                                        </td>
                                        <td class="py-2 px-3 small">{{ $item->client->name ?? '-' }}</td>
                                        <td class="py-2 px-3">
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold text-dark">{{ $item->number }}</span>
                                                @if ($item->sap_po_number)
                                                    <small class="text-primary fw-bold" style="font-size: 0.7rem;">SAP:
                                                        {{ $item->sap_po_number }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-2 px-3">
                                            <span class="badge bg-label-info badge-sm"
                                                style="font-size: 0.72rem;">{{ strtoupper($item->category) }}</span>
                                        </td>
                                        <td class="py-2 px-3">
                                            <span class="badge bg-label-secondary badge-sm"
                                                style="font-size: 0.72rem;">{{ strtoupper($item->request_type ?? '-') }}</span>
                                        </td>
                                        <td class="py-2 px-3 text-center">
                                            @php
                                                $statusClass = 'bg-label-secondary';
                                                if ($item->status == 'new') {
                                                    $statusClass = 'bg-label-info';
                                                } elseif ($item->status == 'process qc') {
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
                                        <td class="py-2 px-3 text-dark fw-medium small">{{ $item->receiving_note ?? '-' }}
                                        </td>
                                        <td class="py-2 px-3">
                                            @if ($item->rma_number || $item->itsm_number)
                                                <div class="small">
                                                    @if ($item->rma_number)
                                                        <span
                                                            class="text-warning fw-medium">R:{{ $item->rma_number }}</span>
                                                    @endif
                                                    @if ($item->itsm_number)
                                                        <span
                                                            class="text-info fw-medium ms-1">I:{{ $item->itsm_number }}</span>
                                                    @endif
                                                </div>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="py-2 px-3 small">{{ $item->vendor ?? '-' }}</td>
                                        <td class="py-2 px-3 small fw-medium">{{ $item->received_by }}</td>
                                        <td class="py-2 px-3 small">{{ $item->received_date }}</td>
                                        <td class="py-2 px-3 text-center">
                                            <a href="{{ route('receivingMonitoring.show', $item->id) }}" class="btn btn-xs btn-primary p-1" title="Detail">
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
                    @if ($inbound->hasPages())
                        <div class="card-footer py-2 px-3 border-top">
                            {{ $inbound->links() }}
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
