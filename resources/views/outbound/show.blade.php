@extends('layout.index')
@section('title', 'Detail Outbound')

@section('content')
    <div class="row">
        <!-- Action Header -->
        <div class="col-12 mb-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="fw-bold mb-1">Outbound Detail: <span
                            class="text-primary">{{ $outbound->number ?? $outbound->tks_dn_number }}</span></h4>
                    <p class="text-muted mb-0">View detailed information and product shipment details.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('outbound.index') }}" class="btn btn-label-secondary">
                        <i class="ti tabler-arrow-left me-1"></i> Back to List
                    </a>
                    @if ($outbound->status !== 'cancel')
                        <button type="button" class="btn btn-danger"
                            onclick="cancelOutbound({{ $outbound->id }}, '{{ $outbound->number ?? $outbound->tks_dn_number }}')">
                            <i class="ti tabler-x me-1"></i> Cancel Outbound
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card mb-4 shadow-sm border border-light-subtle">
                <div class="card-header bg-light py-2 px-3 border-bottom">
                    <h6 class="card-title mb-0 text-dark fw-bold"><i
                            class="ti tabler-file-description me-2 text-secondary"></i>General Information</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0 table-sm">
                            <tbody>
                                <tr>
                                    <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">Client Name</th>
                                    <td class="fw-bold py-2 px-3 text-dark small" colspan="3">
                                        {{ $outbound->client->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">PO/SO Number</th>
                                    <td class="fw-bold py-2 px-3 text-primary small">{{ $outbound->number ?? '-' }}</td>
                                    <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">Category</th>
                                    <td class="py-2 px-3 small"><span
                                            class="badge bg-label-primary">{{ $outbound->category }}</span></td>
                                </tr>
                                <tr>
                                    <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">NTT DN#</th>
                                    <td class="fw-bold py-2 px-3 text-dark small">{{ $outbound->ntt_dn_number ?? '-' }}</td>
                                    <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">TKS DN#</th>
                                    <td class="fw-bold py-2 px-3 text-dark small">{{ $outbound->tks_dn_number ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">RMA#</th>
                                    <td class="fw-bold py-2 px-3 text-dark small">{{ $outbound->rma_number ?? '-' }}</td>
                                    <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">ITSM#</th>
                                    <td class="fw-bold py-2 px-3 text-dark small">{{ $outbound->itsm_number ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">Outbound Date</th>
                                    <td class="fw-bold py-2 px-3 text-dark small">{{ $outbound->outbound_date }}</td>
                                    <th class="bg-light-subtle text-muted w-25 py-2 px-3 small fw-medium">Outbound By</th>
                                    <td class="fw-bold py-2 px-3 text-dark small">{{ $outbound->outbound_by ?? '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header border-bottom bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 small fw-bold">Product Details</h6>
                    <span class="badge bg-label-primary px-2 py-1 small">{{ $outbound->qty }} Items</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0 text-nowrap table-sm"
                            style="font-size: 0.82rem;">
                            <thead class="table-light">
                                <tr>
                                    <th width="30" class="py-2 px-3">#</th>
                                    <th class="py-2 px-3">Part Name</th>
                                    <th class="py-2 px-3">Part Number</th>
                                    <th class="py-2 px-3">Serial Number</th>
                                    <th class="py-2 px-3">Condition</th>
                                    <th class="py-2 px-3">Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($outbound->details as $detail)
                                    <tr>
                                        <td class="py-2 px-3">{{ $loop->iteration }}</td>
                                        <td class="py-2 px-3 fw-medium text-dark">{{ $detail->part_name }}</td>
                                        <td class="py-2 px-3">{{ $detail->part_number }}</td>
                                        <td class="py-2 px-3 fw-bold text-primary">{{ $detail->serial_number }}</td>
                                        <td class="py-2 px-3">
                                            @php
                                                $badgeClass = 'bg-label-info';
                                                if ($detail->condition == 'New' || $detail->condition == 'Good') {
                                                    $badgeClass = 'bg-label-success';
                                                } elseif (
                                                    $detail->condition == 'Faulty' ||
                                                    $detail->condition == 'Write-off Needed'
                                                ) {
                                                    $badgeClass = 'bg-label-danger';
                                                }
                                            @endphp
                                            <span class="badge {{ $badgeClass }} small"
                                                style="font-size: 0.7rem;">{{ strtoupper($detail->condition) }}</span>
                                        </td>
                                        <td class="py-2 px-3 small text-muted">{{ $detail->description ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4 shadow-sm border border-light-subtle text-center">
                <div class="card-header bg-light py-2 px-3 border-bottom">
                    <h6 class="card-title mb-0 text-dark small fw-bold">Status Detail</h6>
                </div>
                <div class="card-body py-3 px-3">
                    <span
                        class="badge {{ $outbound->status == 'cancel' ? 'bg-label-danger' : 'bg-label-success' }} py-2 w-100 shadow-sm fw-bold mb-3">
                        {{ strtoupper($outbound->status) }}
                    </span>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">CREATED AT</span>
                        <span class="text-dark small fw-bold">{{ $outbound->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <!-- Invoices Card -->
            <div class="card shadow-sm border border-light-subtle">
                <div class="card-header bg-light py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 text-dark small fw-bold"><i
                            class="ti tabler-file-invoice me-2 text-secondary"></i>Connected Invoices</h6>
                    <a href="{{ route('invoice.create', ['ref_type' => 'outbound', 'ref_id' => $outbound->id]) }}"
                        class="btn btn-xs btn-primary">
                        <i class="ti tabler-plus me-1"></i> Add
                    </a>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($outbound->invoices as $invoice)
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
    </div>
@endsection

@section('js')
    <script>
        function cancelOutbound(id, number) {
            Swal.fire({
                title: 'Cancel Outbound?',
                text: `Are you sure you want to cancel Outbound ${number}? All items will be returned to inventory.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, cancel it!',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return fetch('{{ route('outbound.cancel') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                id: id
                            })
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(response.statusText)
                            }
                            return response.json()
                        })
                        .catch(error => {
                            Swal.showValidationMessage(`Request failed: ${error}`)
                        })
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    if (result.value.status) {
                        Swal.fire('Cancelled!', 'Status updated successfully.', 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', result.value.message || 'Failed to cancel outbound.', 'error');
                    }
                }
            })
        }
    </script>
@endsection
