@extends('layout.index')
@section('title', 'Detail Receiving')

@section('content')
    <div class="row">
        <!-- Action Header -->
        <div class="col-12 mb-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="fw-bold mb-1">Receiving Detail: <span class="text-primary">{{ $inbound->number }}</span></h4>
                    <p class="text-muted mb-0">Manage and view detailed information for this inbound shipment.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('receiving') }}" class="btn btn-label-secondary">
                        <i class="ti tabler-arrow-left me-1"></i> Back to List
                    </a>
                    @if ($inbound->status == 'new')
                        <button type="button" class="btn btn-success" onclick="approveReceiving({{ $inbound->id }})">
                            <i class="ti tabler-check me-1"></i> Approve
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Left Column: Primary Data -->
        <div class="col-md-8">
            <!-- Reference Numbers Card -->
            <div class="card mb-3 shadow-sm border border-light-subtle">
                <div class="card-header bg-label-primary py-2 px-3 border-bottom">
                    <h6 class="card-title mb-0 text-primary fw-bold"><i
                            class="ti tabler-file-description me-2"></i>Reference Identifiers</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0 table-sm">
                            <tbody>
                                <tr>
                                    <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">NTT RN#</th>
                                    <td class="fw-bold py-2 px-3 text-primary small">{{ $inbound->receiving_note ?? '-' }}
                                    </td>
                                    <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">SAP PO#</th>
                                    <td class="fw-bold py-2 px-3 text-dark small">{{ $inbound->sap_po_number ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">eCapex#</th>
                                    <td class="fw-bold py-2 px-3 text-dark small">{{ $inbound->ecapex_number ?? '-' }}</td>
                                    <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">Vendor DN#</th>
                                    <td class="fw-bold py-2 px-3 text-dark small">{{ $inbound->vendor_dn_number ?? '-' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">TKS DN#</th>
                                    <td class="fw-bold py-2 px-3 text-dark small">{{ $inbound->tks_dn_number ?? '-' }}</td>
                                    <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">TKS Invoice#</th>
                                    <td class="fw-bold py-2 px-3 text-dark small">{{ $inbound->tks_invoice_number ?? '-' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">ITSM / RMA</th>
                                    <td class="fw-bold py-2 px-3 text-dark small" colspan="3">
                                        <span class="badge bg-label-info badge-sm me-2">ITSM:
                                            {{ $inbound->itsm_number ?? '-' }}</span>
                                        <span class="badge bg-label-warning badge-sm">RMA:
                                            {{ $inbound->rma_number ?? '-' }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Client & Shipping Details -->
            <div class="card mb-3 shadow-sm border border-light-subtle">
                <div class="card-header bg-label-primary py-2 px-3 border-bottom">
                    <h6 class="card-title mb-0 text-primary small fw-bold"><i class="ti tabler-truck me-2"></i>Shipping
                        Information</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0 table-sm">
                            <tbody>
                                <tr>
                                    <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">Client Name</th>
                                    <td class="fw-bold py-2 px-3 text-dark small">{{ $inbound->client->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">Contact / Address
                                    </th>
                                    <td class="py-2 px-3 text-dark small">
                                        <div class="mb-1 fw-medium"><i class="ti tabler-user me-1 text-secondary"></i>
                                            {{ $inbound->client_contact ?? '-' }}</div>
                                        <div class="text-muted"><i class="ti tabler-map-pin me-1 text-danger"></i>
                                            {{ $inbound->pickup_address ?? '-' }}
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Operational Stats -->
        <div class="col-md-4">
            <!-- Inbound Summary Card -->
            <div class="card mb-3 shadow-sm border border-light-subtle">
                <div class="card-header bg-label-primary py-2 px-3 border-bottom text-center">
                    <h6 class="card-title mb-0 text-primary small fw-bold">Inbound Summary</h6>
                </div>
                <div class="card-body py-3 px-3">
                    <div class="mb-3">
                        <span
                            class="badge {{ $inbound->status == 'new' ? 'bg-label-info' : 'bg-label-success' }} py-2 w-100 shadow-sm fw-bold">
                            {{ strtoupper($inbound->status) }}
                        </span>
                    </div>

                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small fw-medium text-uppercase">Stock Category</span>
                        <span class="text-dark small fw-bold">{{ strtoupper($inbound->category) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small fw-medium text-uppercase">Request Type</span>
                        <span class="text-dark small fw-bold">{{ strtoupper($inbound->request_type ?? '-') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                        <span class="text-muted small fw-medium text-uppercase">Total Qty</span>
                        <span class="text-dark fw-bold small">{{ $inbound->qty }} UNITS</span>
                    </div>

                    <div class="bg-light rounded p-2 text-start border-start border-secondary border-3">
                        <div class="mb-1 d-flex justify-content-between">
                            <small class="text-muted fw-medium">REQ. BY</small>
                            <small class="text-dark fw-bold">{{ $inbound->ntt_requestor ?? '-' }}</small>
                        </div>
                        <div class="mb-1 d-flex justify-content-between">
                            <small class="text-muted fw-medium">REQ. DATE</small>
                            <small
                                class="text-dark fw-bold">{{ $inbound->request_date ?? $inbound->created_at->format('d/m/Y') }}</small>
                        </div>
                        <div class="mb-1 d-flex justify-content-between">
                            <small class="text-muted fw-medium">REC. DATE</small>
                            <small class="text-dark fw-bold">{{ $inbound->received_date ?? '-' }}</small>
                        </div>
                        <hr class="my-1">
                        <div class="mb-0 d-flex justify-content-between">
                            <small class="text-muted fw-medium">PROC. BY</small>
                            <small class="text-dark fw-bold">{{ $inbound->received_by ?? '-' }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoices Card -->
            <div class="card shadow-sm border border-light-subtle mb-3">
                <div
                    class="card-header bg-label-primary py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 text-primary small fw-bold"><i
                            class="ti tabler-file-invoice me-2"></i>Invoices</h6>
                    <a href="{{ route('invoice.create', ['ref_type' => 'inbound', 'ref_id' => $inbound->id]) }}"
                        class="btn btn-xs btn-primary">
                        <i class="ti tabler-plus me-1"></i> Add
                    </a>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($inbound->invoices as $invoice)
                            <li class="list-group-item p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold text-dark small">{{ $invoice->invoice_number }}</div>
                                        <small
                                            class="text-muted">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}</small>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold text-primary small">IDR
                                            {{ number_format($invoice->amount, 0, ',', '.') }}</div>
                                        @if ($invoice->file_path)
                                            <a href="{{ asset('storage/' . $invoice->file_path) }}" target="_blank"
                                                class="small">View File</a>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item p-3 text-center">
                                <small class="text-muted">No invoices linked yet.</small>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <!-- Product Table Card -->
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div
                    class="card-header border-bottom bg-label-primary py-2 px-3 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 small fw-bold text-primary">Item Received Details</h6>
                    <span class="badge bg-primary text-white px-2 py-1 small">{{ $inbound->qty }} Items</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0 text-nowrap table-sm"
                            style="font-size: 0.82rem;">
                            <thead class="table-light">
                                <tr>
                                    <th width="30" class="py-2 px-3">#</th>
                                    <th class="py-2 px-3">Brand</th>
                                    <th class="py-2 px-3">SKU</th>
                                    <th class="py-2 px-3">Part Name</th>
                                    <th class="py-2 px-3">Description</th>
                                    <th class="py-2 px-3 text-center">Qty</th>
                                    <th class="py-2 px-3">Serial Number</th>
                                    <th class="py-2 px-3">Parent SN</th>
                                    <th class="py-2 px-3">WH Asset#</th>
                                    <th class="py-2 px-3">Stock Status</th>
                                    <th class="py-2 px-3 text-center">Condition</th>
                                    <th class="py-2 px-3">Location</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($inbound->details as $detail)
                                    <tr>
                                        <td class="py-2 px-3">{{ $loop->iteration }}</td>
                                        <td class="py-2 px-3"><span
                                                class="fw-bold text-dark">{{ $detail->brand->name ?? '-' }}</span></td>
                                        <td class="py-2 px-3"><span class="badge bg-label-secondary small"
                                                style="font-size: 0.75rem;">{{ $detail->part_number }}</span></td>
                                        <td class="py-2 px-3">{{ $detail->part_name }}</td>
                                        <td class="py-2 px-3">
                                            <div
                                                style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                {{ $detail->description ?? '-' }}
                                            </div>
                                        </td>
                                        <td class="py-2 px-3 text-center fw-bold">{{ $detail->qty }}</td>
                                        <td class="py-2 px-3"><span
                                                class="fw-bold text-primary">{{ $detail->serial_number }}</span></td>
                                        <td class="py-2 px-3 small">
                                            {{ $detail->parent_sn ?? ($detail->old_serial_number ?? '-') }}</td>
                                        <td class="py-2 px-3 small text-muted">{{ $detail->wh_asset_number ?? '-' }}</td>
                                        <td class="py-2 px-3">
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
                                            <span class="badge {{ $stockClass }}" style="font-size: 0.7rem;">
                                                {{ strtoupper($stockStatus) }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-3 text-center">
                                            @php
                                                $condClass = 'bg-label-info';
                                                if ($detail->condition == 'New' || $detail->condition == 'Good') {
                                                    $condClass = 'bg-label-success';
                                                } elseif ($detail->condition == 'Broken') {
                                                    $condClass = 'bg-label-danger';
                                                }
                                            @endphp
                                            <span class="badge {{ $condClass }}"
                                                style="font-size: 0.7rem;">{{ strtoupper($detail->condition) }}</span>
                                        </td>
                                        <td class="py-2 px-3">
                                            @if ($detail->storageLevel)
                                                @php
                                                    $location = collect([
                                                        $detail->storageLevel->zone->name ?? null,
                                                        $detail->storageLevel->rak->name ?? null,
                                                        $detail->storageLevel->bin->name ?? null,
                                                        $detail->storageLevel->name ?? null,
                                                    ])
                                                        ->filter()
                                                        ->implode(' - ');
                                                @endphp
                                                <small class="text-dark fw-medium">
                                                    {{ $location }}
                                                </small>
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
                <div class="card-footer bg-light border-top py-1 px-3">
                    <small class="text-muted" style="font-size: 0.75rem;"><i class="ti tabler-info-circle me-1"></i> Use
                        horizontal scroll for more columns.</small>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        function approveReceiving(id) {
            Swal.fire({
                title: 'Approve Receiving?',
                text: "Status will be changed to PROCESS QC",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, approve it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    executeAjax('{{ route('receiving.approve') }}', id);
                }
            });
        }

        function executeAjax(url, id) {
            Swal.fire({
                title: 'Processing...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        id: id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        Swal.fire('Success!', 'Status updated successfully.', 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.message || 'Failed to update status.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'An unexpected error occurred.', 'error');
                });
        }
    </script>
@endsection
