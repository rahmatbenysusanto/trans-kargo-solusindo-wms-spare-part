@extends('layout.index')
@section('title', 'Detail Outbound')

@section('content')
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">General Information</h5>
                    <div class="d-flex gap-2">
                        @if ($outbound->status !== 'cancel')
                            <button type="button" class="btn btn-danger btn-sm"
                                onclick="cancelOutbound({{ $outbound->id }}, '{{ $outbound->number ?? $outbound->tks_dn_number }}')">
                                <i class="ti tabler-x me-1"></i> Cancel Outbound
                            </button>
                        @endif
                        <a href="{{ route('outbound.index') }}" class="btn btn-secondary btn-sm">
                            <i class="ti tabler-arrow-left me-1"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Client</label>
                            <p class="form-control-plaintext">{{ $outbound->client->name ?? '-' }}</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">PO Number</label>
                            <p class="form-control-plaintext">{{ $outbound->number ?? '-' }}</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Category</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-label-primary">{{ $outbound->category }}</span>
                            </p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">NTT DN Number</label>
                            <p class="form-control-plaintext">{{ $outbound->ntt_dn_number ?? '-' }}</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">TKS DN Number</label>
                            <p class="form-control-plaintext">{{ $outbound->tks_dn_number ?? '-' }}</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">TKS Invoice</label>
                            <p class="form-control-plaintext">{{ $outbound->tks_invoice_number ?? '-' }}</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">RMA Number</label>
                            <p class="form-control-plaintext">{{ $outbound->rma_number ?? '-' }}</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">ITSM Number</label>
                            <p class="form-control-plaintext">{{ $outbound->itsm_number ?? '-' }}</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Total Qty</label>
                            <p class="form-control-plaintext">{{ $outbound->qty }}</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Outbound Date</label>
                            <p class="form-control-plaintext">{{ $outbound->outbound_date }}</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Outbound By</label>
                            <p class="form-control-plaintext">{{ $outbound->outbound_by ?? '-' }}</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <p class="form-control-plaintext">
                                <span
                                    class="badge {{ $outbound->status == 'cancel' ? 'bg-label-danger' : 'bg-label-secondary' }}">{{ $outbound->status }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Product Details</h5>
                </div>
                <div class="card-body mt-3">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Part Name</th>
                                    <th>Part Number</th>
                                    <th>Serial Number</th>
                                    <th>Condition</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($outbound->details as $detail)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $detail->part_name }}</td>
                                        <td>{{ $detail->part_number }}</td>
                                        <td>{{ $detail->serial_number }}</td>
                                        <td>
                                            @php
                                                $badgeClass = 'bg-label-info';
                                                if ($detail->condition == 'New') {
                                                    $badgeClass = 'bg-label-success';
                                                } elseif (
                                                    $detail->condition == 'Faulty' ||
                                                    $detail->condition == 'Write-off Needed'
                                                ) {
                                                    $badgeClass = 'bg-label-danger';
                                                }
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">{{ $detail->condition }}</span>
                                        </td>
                                        <td>{{ $detail->description ?? '-' }}</td>
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
                        Swal.fire('Cancelled!', 'Outbound has been cancelled.', 'success').then(() => {
                            window.location.href = '{{ route('outbound.index') }}';
                        });
                    } else {
                        Swal.fire('Error', result.value.message || 'Failed to cancel outbound.', 'error');
                    }
                }
            })
        }
    </script>
@endsection
