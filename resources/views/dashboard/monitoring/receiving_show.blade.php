@extends('layout.index')
@section('title', 'Receiving Detail Monitoring')

@section('content')
    <div class="row">
        <!-- Header -->
        <div class="col-12 mb-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="fw-bold mb-1">Monitoring Receiving: <span class="text-primary">{{ $inbound->number }}</span></h4>
                    <p class="text-muted mb-0">Detailed view of inbound transaction (Read-Only).</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('receivingMonitoring') }}" class="btn btn-label-secondary">
                        <i class="ti tabler-arrow-left me-1"></i> Back to Monitoring
                    </a>
                </div>
            </div>
        </div>

        <!-- Left Column -->
        <div class="col-md-8">
            <!-- Reference Numbers -->
            <div class="card mb-3 shadow-sm border border-light-subtle">
                <div class="card-header bg-label-info py-2 px-3 border-bottom">
                    <h6 class="card-title mb-0 text-info fw-bold"><i class="ti tabler-file-description me-2"></i>Reference Numbers</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0 table-sm">
                            <tbody>
                                <tr>
                                    <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">NTT RN#</th>
                                    <td class="fw-bold py-2 px-3 text-primary small">{{ $inbound->receiving_note ?? '-' }}</td>
                                    <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">SAP PO#</th>
                                    <td class="fw-bold py-2 px-3 text-dark small">{{ $inbound->sap_po_number ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">eCapex#</th>
                                    <td class="fw-bold py-2 px-3 text-dark small">{{ $inbound->ecapex_number ?? '-' }}</td>
                                    <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">Vendor DN#</th>
                                    <td class="fw-bold py-2 px-3 text-dark small">{{ $inbound->vendor_dn_number ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">ITSM / RMA</th>
                                    <td class="fw-bold py-2 px-3 text-dark small" colspan="3">
                                        <span class="badge bg-label-info badge-sm me-2">ITSM: {{ $inbound->itsm_number ?? '-' }}</span>
                                        <span class="badge bg-label-warning badge-sm">RMA: {{ $inbound->rma_number ?? '-' }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Client Info -->
            <div class="card mb-3 shadow-sm border border-light-subtle">
                <div class="card-header bg-label-info py-2 px-3 border-bottom">
                    <h6 class="card-title mb-0 text-info small fw-bold"><i class="ti tabler-user me-2"></i>Client Information</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0 table-sm">
                        <tbody>
                            <tr>
                                <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">Client Name</th>
                                <td class="fw-bold py-2 px-3 text-dark small">{{ $inbound->client->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">Contact</th>
                                <td class="py-2 px-3 text-dark small">{{ $inbound->client_contact ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">Address</th>
                                <td class="py-2 px-3 text-dark small">{{ $inbound->pickup_address ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-md-4">
            <div class="card mb-3 shadow-sm border border-light-subtle">
                <div class="card-header bg-label-info py-2 px-3 border-bottom text-center">
                    <h6 class="card-title mb-0 text-info small fw-bold">Status & Dates</h6>
                </div>
                <div class="card-body py-3 px-3">
                    <div class="mb-3">
                        <span class="badge {{ $inbound->status == 'new' ? 'bg-label-info' : 'bg-label-success' }} py-2 w-100 shadow-sm fw-bold">
                            {{ strtoupper($inbound->status) }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small fw-medium text-uppercase">Total Qty</span>
                        <span class="text-dark fw-bold small">{{ $inbound->qty }} UNITS</span>
                    </div>
                    <hr class="my-2">
                    <div class="mb-1 d-flex justify-content-between">
                        <small class="text-muted fw-medium text-uppercase">Rec. Date</small>
                        <small class="text-dark fw-bold">{{ $inbound->received_date ?? '-' }}</small>
                    </div>
                    <div class="mb-0 d-flex justify-content-between">
                        <small class="text-muted fw-medium text-uppercase">Proc. By</small>
                        <small class="text-dark fw-bold">{{ $inbound->received_by ?? '-' }}</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Table -->
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header border-bottom bg-label-info py-2 px-3 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 small fw-bold text-info">Product List</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0 text-nowrap table-sm" style="font-size: 0.82rem;">
                            <thead class="table-light">
                                <tr>
                                    <th width="30" class="py-2 px-3">#</th>
                                    <th class="py-2 px-3">Brand</th>
                                    <th class="py-2 px-3">Part Number</th>
                                    <th class="py-2 px-3">Part Name</th>
                                    <th class="py-2 px-3">Serial Number</th>
                                    <th class="py-2 px-3 text-center">Condition</th>
                                    <th class="py-2 px-3">Location</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($inbound->details as $detail)
                                    <tr>
                                        <td class="py-2 px-3">{{ $loop->iteration }}</td>
                                        <td class="py-2 px-3">{{ $detail->brand->name ?? '-' }}</td>
                                        <td class="py-2 px-3"><span class="badge bg-label-secondary small">{{ $detail->part_number }}</span></td>
                                        <td class="py-2 px-3">{{ $detail->part_name }}</td>
                                        <td class="py-2 px-3 fw-bold text-primary">{{ $detail->serial_number }}</td>
                                        <td class="py-2 px-3 text-center">
                                            @php
                                                $condClass = 'bg-label-success';
                                                if ($detail->condition == 'Broken') $condClass = 'bg-label-danger';
                                            @endphp
                                            <span class="badge {{ $condClass }}" style="font-size: 0.7rem;">{{ strtoupper($detail->condition) }}</span>
                                        </td>
                                        <td class="py-2 px-3 small">
                                            @if ($detail->storageLevel)
                                                {{ $detail->storageLevel->zone->name ?? '-' }}-{{ $detail->storageLevel->rak->name ?? '-' }}-{{ $detail->storageLevel->bin->name ?? '-' }}-{{ $detail->storageLevel->name ?? '-' }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
