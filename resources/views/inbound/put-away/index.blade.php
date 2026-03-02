@extends('layout.index')
@section('title', 'Put Away')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Put Away List</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>PO / Ref Number</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Note</th>
                                    <th>RMA</th>
                                    <th>ITSM</th>
                                    <th>Vendor</th>
                                    <th>Received By</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($inbound as $item)
                                    <tr>
                                        <td>{{ $loop->iteration + ($inbound->currentPage() - 1) * $inbound->perPage() }}
                                        </td>
                                        <td>{{ $item->number }}</td>
                                        <td><span class="badge bg-label-primary">{{ $item->category }}</span></td>
                                        <td>
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
                                            <span class="badge {{ $statusClass }}">{{ strtoupper($item->status) }}</span>
                                        </td>
                                        <td>{{ $item->receiving_note ?? '-' }}</td>
                                        <td>{{ $item->rma_number ?? '-' }}</td>
                                        <td>{{ $item->itsm_number ?? '-' }}</td>
                                        <td>{{ $item->vendor }}</td>
                                        <td>{{ $item->received_by }}</td>
                                        <td>{{ $item->received_date }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                    data-bs-toggle="dropdown">
                                                    <i class="ti tabler-dots-vertical"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item"
                                                        href="{{ route('receiving.show', $item->id) }}">
                                                        <i class="ti tabler-eye me-1"></i> Detail
                                                    </a>
                                                    {{-- Put Away specific actions can be added here --}}
                                                    <a class="dropdown-item"
                                                        href="{{ route('receiving.put.away.process', $item->id) }}">
                                                        <i class="ti tabler-package me-1"></i> Process Put Away
                                                    </a>
                                                    <a class="dropdown-item text-danger" href="javascript:void(0);"
                                                        onclick="cancelRemainingPutAway({{ $item->id }}, '{{ $item->number }}')">
                                                        <i class="ti tabler-x me-1"></i> Cancel Remaining
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $inbound->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        function cancelRemainingPutAway(id, number) {
            Swal.fire({
                title: 'Cancel Remaining Items?',
                text: `Are you sure you want to cancel the remaining items for ${number}? Items not yet in shelving will be moved to a cancelled reference.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, cancel them!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.showLoading();
                    fetch('{{ route('receiving.put.away.cancel') }}', {
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
                                Swal.fire('Success!', 'Remaining items have been cancelled.', 'success').then(
                                () => {
                                        location.reload();
                                    });
                            } else {
                                Swal.fire('Error', data.message || 'Failed to cancel items.', 'error');
                            }
                        });
                }
            });
        }
    </script>
@endsection
