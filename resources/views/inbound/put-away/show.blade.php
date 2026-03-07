@extends('layout.index')
@section('title', 'Detail Put Away')

@section('css')
    <style>
        .header-label {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #a19fad;
            letter-spacing: 1px;
            margin-bottom: 0.2rem;
            display: block;
        }

        .header-value {
            font-size: 0.95rem;
            font-weight: 600;
            color: #444050;
            margin-bottom: 0;
        }

        .info-card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(165, 163, 174, 0.3);
            border-radius: 0.5rem;
        }

        .table-compact td,
        .table-compact th {
            padding: 0.5rem 0.75rem !important;
            font-size: 0.82rem;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <!-- Action Header -->
        <div class="col-12 mb-4">
            <div class="card info-card border-start border-primary border-5">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="me-3 bg-label-primary p-2 rounded">
                                <i class="ti tabler-package fs-2"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-0">Record: <span class="text-primary">{{ $inbound->number }}</span></h4>
                                <div class="d-flex align-items-center mt-1">
                                    @php
                                        $statusClass = 'bg-label-secondary';
                                        switch ($inbound->status) {
                                            case 'new':
                                                $statusClass = 'bg-label-info';
                                                break;
                                            case 'process qc':
                                                $statusClass = 'bg-label-warning';
                                                break;
                                            case 'cancel':
                                                $statusClass = 'bg-label-danger';
                                                break;
                                            case 'close':
                                                $statusClass = 'bg-label-success';
                                                break;
                                        }
                                    @endphp
                                    <span class="badge {{ $statusClass }} rounded-pill me-2" style="font-size: 0.65rem;">
                                        {{ strtoupper($inbound->status) }}
                                    </span>
                                    <small class="text-muted"><i
                                            class="ti tabler-calendar me-1"></i>{{ \Carbon\Carbon::parse($inbound->received_date)->format('d M Y') }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('receiving.put.away') }}" class="btn btn-label-secondary btn-sm">
                                <i class="ti tabler-arrow-left me-1"></i> Back
                            </a>
                            @if ($inbound->status == 'process qc')
                                <a href="{{ route('receiving.put.away.process', $inbound->id) }}"
                                    class="btn btn-primary btn-sm">
                                    <i class="ti tabler-package-export me-1"></i> Process Put Away
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Left Column: Primary Data -->
        <div class="col-md-8">
            <!-- Reference Details -->
            <div class="card info-card mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
                    <i class="ti tabler-file-analytics me-2 text-primary fs-4"></i>
                    <h5 class="card-title mb-0 fw-bold small text-uppercase">Shipment & Reference Identifiers</h5>
                </div>
                <div class="card-body p-0">
                    <div class="row g-0">
                        <div class="col-md-6 border-end border-bottom p-3">
                            <span class="header-label">NTT RN Number</span>
                            <div class="fw-bold text-dark font-monospace">{{ $inbound->receiving_note ?: '-' }}</div>
                        </div>
                        <div class="col-md-6 border-bottom p-3">
                            <span class="header-label">SAP PO Number</span>
                            <div class="fw-bold text-dark">{{ $inbound->sap_po_number ?: '-' }}</div>
                        </div>
                        <div class="col-md-6 border-end border-bottom p-3">
                            <span class="header-label">Category / Request</span>
                            <div class="d-flex gap-2 align-items-center">
                                <span class="badge bg-label-info">{{ $inbound->category }}</span>
                                <span class="small fw-bold text-muted">{{ $inbound->request_type ?: '-' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 border-bottom p-3">
                            <span class="header-label">RMA / ITSM Reference</span>
                            <div class="d-flex gap-2">
                                <span class="badge bg-label-danger">{{ $inbound->rma_number ?: 'N/A' }}</span>
                                <span class="badge bg-label-secondary">{{ $inbound->itsm_number ?: 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-md-4 border-end p-3">
                            <span class="header-label">Vendor DN</span>
                            <div class="small fw-medium">{{ $inbound->vendor_dn_number ?: '-' }}</div>
                        </div>
                        <div class="col-md-4 border-end p-3">
                            <span class="header-label">TKS DN</span>
                            <div class="small fw-medium">{{ $inbound->tks_dn_number ?: '-' }}</div>
                        </div>
                        <div class="col-md-4 p-3">
                            <span class="header-label">TKS Invoice</span>
                            <div class="small fw-medium">{{ $inbound->tks_invoice_number ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipping Info -->
            <div class="card info-card mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
                    <i class="ti tabler-truck me-2 text-primary fs-4"></i>
                    <h5 class="card-title mb-0 fw-bold small text-uppercase">Logistics & Client Information</h5>
                </div>
                <div class="card-body p-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="bg-light p-3 rounded h-100">
                                <span class="header-label">Owner / Client</span>
                                <h6 class="mb-2 fw-bold text-primary">{{ $inbound->client->name ?? '-' }}</h6>
                                <div class="small text-muted mb-1"><i class="ti tabler-user me-1"></i>
                                    {{ $inbound->client_contact ?: '-' }}</div>
                                <div class="small text-muted"><i class="ti tabler-map-pin me-1"></i>
                                    {{ $inbound->pickup_address ?: '-' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light p-3 rounded h-100">
                                <span class="header-label">Transportation</span>
                                <h6 class="mb-2 fw-bold text-dark">{{ $inbound->vendor ?: 'Direct' }}</h6>
                                <div class="small text-muted mb-1"><i class="ti tabler-clipboard-list me-1"></i> STTB:
                                    {{ $inbound->sttb ?: '-' }}</div>
                                <div class="small text-muted"><i class="ti tabler-package me-1"></i> Courier DN:
                                    {{ $inbound->courier_delivery_note ?: '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Operational Stats -->
        <div class="col-md-4">
            <!-- Summary Stats -->
            <div class="card info-card mb-4 overflow-hidden">
                <div class="bg-primary p-4 text-white">
                    <span class="opacity-75 small text-uppercase fw-bold">Transaction Summary</span>
                    <h2 class="text-white mb-0 fw-bold">{{ $inbound->qty }} <small class="fs-6 opacity-75">Units</small>
                    </h2>
                </div>
                <div class="card-body py-4 border-bottom">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-label bg-label-info p-1 me-2 rounded-circle">
                            <i class="ti tabler-user-check"></i>
                        </div>
                        <div>
                            <div class="header-label mb-0">Processed By</div>
                            <div class="small fw-bold">{{ $inbound->received_by }}</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="avatar-label bg-label-warning p-1 me-2 rounded-circle">
                            <i class="ti tabler-user-edit"></i>
                        </div>
                        <div>
                            <div class="header-label mb-0">Original Requestor</div>
                            <div class="small fw-bold">{{ $inbound->ntt_requestor ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoices -->
            <div class="card info-card mb-4 overflow-hidden">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold small text-uppercase">Linked Invoices</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($inbound->invoices as $invoice)
                            <li class="list-group-item px-3 py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="fw-bold small">{{ $invoice->invoice_number }}</div>
                                    <div class="text-primary fw-bold small text-end">
                                        IDR {{ number_format($invoice->amount, 0, ',', '.') }}
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-center py-4 text-muted small">No invoices found.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <!-- Product Table -->
        <div class="col-12">
            <div class="card info-card">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="card-title mb-0 fw-bold small text-uppercase text-muted">Item Level Inventory Records</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle table-compact mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Part Specification</th>
                                    <th>SKU / Part Number</th>
                                    <th>Serial Number</th>
                                    <th>Old / Parent SN</th>
                                    <th class="text-center">Condition</th>
                                    <th class="text-center">Status</th>
                                    <th>Storage Location</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($inbound->details as $detail)
                                    <tr>
                                        <td class="text-muted small">{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $detail->part_name }}</div>
                                            <div class="text-muted" style="font-size: 0.72rem;">
                                                {{ $detail->brand->name ?? '-' }}</div>
                                        </td>
                                        <td><span class="badge bg-label-secondary small"
                                                style="font-size: 0.75rem;">{{ $detail->part_number }}</span></td>
                                        <td class="font-monospace fw-bold text-primary">{{ $detail->serial_number }}</td>
                                        <td class="small text-danger">
                                            {{ $detail->parent_sn ?? ($detail->old_serial_number ?? '-') }}</td>
                                        <td class="text-center">
                                            @php
                                                $condClass = 'bg-label-info';
                                                if (in_array($detail->condition, ['New', 'Good'])) {
                                                    $condClass = 'bg-label-success';
                                                } elseif (in_array($detail->condition, ['Broken', 'Faulty'])) {
                                                    $condClass = 'bg-label-danger';
                                                }
                                            @endphp
                                            <span class="badge {{ $condClass }}"
                                                style="font-size: 0.7rem;">{{ strtoupper($detail->condition) }}</span>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $stockStatus = $detail->stock_status ?? 'Available';
                                                $stockClass = 'bg-label-dark';
                                                if ($stockStatus == 'Available') {
                                                    $stockClass = 'bg-label-success';
                                                } elseif ($stockStatus == 'Faulty') {
                                                    $stockClass = 'bg-label-danger';
                                                } elseif ($stockStatus == 'Write-off') {
                                                    $stockClass = 'bg-label-secondary';
                                                }
                                            @endphp
                                            <span class="badge {{ $stockClass }}"
                                                style="font-size: 0.7rem;">{{ strtoupper($stockStatus) }}</span>
                                        </td>
                                        <td>
                                            @if ($detail->storageLevel)
                                                <div class="d-flex align-items-center">
                                                    <i class="ti tabler-map-pin me-1 text-danger small"></i>
                                                    <span class="small fw-medium text-dark">
                                                        {{ $detail->storageLevel->zone->name ?? '' }} -
                                                        {{ $detail->storageLevel->rak->name ?? '' }} -
                                                        {{ $detail->storageLevel->bin->name ?? '' }} -
                                                        {{ $detail->storageLevel->name ?? '' }}
                                                    </span>
                                                </div>
                                            @else
                                                <span class="badge bg-label-warning" style="font-size: 0.65rem;">WAITING
                                                    FOR SHELVING</span>
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
