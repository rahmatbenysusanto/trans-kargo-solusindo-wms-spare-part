@extends('layout.index')
@section('title', 'Receiving')

@section('content')
    <div class="row">
        <div class="d-flex justify-content-end mb-3">
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    Create Receiving
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <a class="dropdown-item" href="{{ route('receiving.create.spare') }}">Spare</a>
                    <a class="dropdown-item" href="{{ route('receiving.create.faulty') }}">Faulty</a>
                    <a class="dropdown-item" href="{{ route('receiving.create.rma') }}">RMA</a>
                    <a class="dropdown-item" href="{{ route('receiving.create.new.po') }}">New PO</a>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Receiving List</h5>
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
                                                    @if ($item->status == 'new')
                                                        <a class="dropdown-item" href="javascript:void(0);"
                                                            onclick="approveReceiving({{ $item->id }})">
                                                            <i class="ti tabler-check me-1"></i> Approve
                                                        </a>
                                                        <a class="dropdown-item text-danger" href="javascript:void(0);"
                                                            onclick="cancelReceiving({{ $item->id }})">
                                                            <i class="ti tabler-x me-1"></i> Cancel
                                                        </a>
                                                    @endif
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

        function cancelReceiving(id) {
            Swal.fire({
                title: 'Cancel Receiving?',
                text: "Status will be changed to CANCEL",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, cancel it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    executeAjax('{{ route('receiving.cancel') }}', id);
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
