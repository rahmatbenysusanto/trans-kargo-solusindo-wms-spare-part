@extends('layout.index')
@section('title', 'Outbound Detail Monitoring')

@section('content')
    <div class="row">
        <!-- Header -->
        <div class="col-12 mb-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="fw-bold mb-1">Monitoring Outbound: <span class="text-primary">{{ $outbound->number ?? $outbound->tks_dn_number }}</span></h4>
                    <p class="text-muted mb-0">Detailed view of outbound shipment (Read-Only).</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('outboundMonitoring') }}" class="btn btn-label-secondary">
                        <i class="ti tabler-arrow-left me-1"></i> Back to Monitoring
                    </a>
                </div>
            </div>
        </div>

        <!-- Left Column -->
        <div class="col-md-8">
            <div class="card mb-3 shadow-sm border border-light-subtle">
                <div class="card-header bg-label-info py-2 px-3 border-bottom">
                    <h6 class="card-title mb-0 text-info fw-bold"><i class="ti tabler-file-description me-2"></i>Shipment Information</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0 table-sm">
                        <tbody>
                            <tr>
                                <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">Client Name</th>
                                <td class="fw-bold py-2 px-3 text-dark small" colspan="3">{{ $outbound->client->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">Category</th>
                                <td class="py-2 px-3 small">
                                    <span class="badge bg-label-primary">{{ $outbound->category }}</span>
                                    @if ($outbound->request_type)
                                        <span class="badge bg-label-info">{{ $outbound->request_type }}</span>
                                    @endif
                                </td>
                                <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">SAP PO#</th>
                                <td class="fw-bold py-2 px-3 text-primary small">{{ $outbound->sap_po_number ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">NTT DN#</th>
                                <td class="fw-bold py-2 px-3 text-dark small">{{ $outbound->ntt_dn_number ?? '-' }}</td>
                                <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">TKS DN#</th>
                                <td class="fw-bold py-2 px-3 text-dark small">{{ $outbound->tks_dn_number ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">ITSM / RMA</th>
                                <td class="fw-bold py-2 px-3 text-dark small" colspan="3">
                                    <span class="badge bg-label-info badge-sm me-2">ITSM: {{ $outbound->itsm_number ?? '-' }}</span>
                                    <span class="badge bg-label-warning badge-sm">RMA: {{ $outbound->rma_number ?? '-' }}</span>
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">Shipment Address</th>
                                <td class="py-2 px-3 small" colspan="3">{{ $outbound->pickup_address ?? '-' }}</td>
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
                    <h6 class="card-title mb-0 text-info small fw-bold">Operational Status</h6>
                </div>
                <div class="card-body py-3 px-3">
                    <div class="mb-3">
                        <span class="badge {{ $outbound->status == 'cancel' ? 'bg-label-danger' : 'bg-label-success' }} py-2 w-100 shadow-sm fw-bold">
                            {{ strtoupper($outbound->status) }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small">TOTAL QTY</span>
                        <span class="text-dark fw-bold small">{{ $outbound->qty }} UNITS</span>
                    </div>
                    <hr class="my-2">
                    <div class="mb-1 d-flex justify-content-between">
                        <small class="text-muted fw-medium text-uppercase">Outbound Date</small>
                        <small class="text-dark fw-bold">{{ $outbound->outbound_date }}</small>
                    </div>
                    <div class="mb-0 d-flex justify-content-between">
                        <small class="text-muted fw-medium text-uppercase">Processed By</small>
                        <small class="text-dark fw-bold">{{ $outbound->outbound_by ?? '-' }}</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Item Table -->
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header border-bottom bg-label-info py-2 px-3">
                    <h6 class="card-title mb-0 small fw-bold text-info">Shipped Item Details</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0 text-nowrap table-sm" style="font-size: 0.82rem;">
                            <thead class="table-light">
                                <tr>
                                    <th width="30" class="py-2 px-3">#</th>
                                    <th class="py-2 px-3">Part Number</th>
                                    <th class="py-2 px-3">Part Name</th>
                                    <th class="py-2 px-3">Serial Number</th>
                                    <th class="py-2 px-3 text-center">Condition</th>
                                    <th class="py-2 px-3">Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($outbound->details as $detail)
                                    <tr>
                                        <td class="py-2 px-3">{{ $loop->iteration }}</td>
                                        <td class="py-2 px-3"><span class="badge bg-label-secondary small">{{ $detail->part_number }}</span></td>
                                        <td class="py-2 px-3 fw-medium text-dark">{{ $detail->part_name }}</td>
                                        <td class="py-2 px-3 fw-bold text-primary">{{ $detail->serial_number }}</td>
                                        <td class="py-2 px-3 text-center">
                                            @php
                                                $condClass = 'bg-label-success';
                                                if (in_array($detail->condition, ['Faulty', 'Broken'])) $condClass = 'bg-label-danger';
                                            @endphp
                                            <span class="badge {{ $condClass }}" style="font-size: 0.7rem;">{{ strtoupper($detail->condition) }}</span>
                                        </td>
                                        <td class="py-2 px-3 small text-muted text-wrap" style="max-width: 300px;">{{ $detail->description ?? '-' }}</td>
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
