@extends('layout.index')
@section('title', 'Put Away Management')

@section('css')
    <style>
        .table-compact td,
        .table-compact th {
            padding: 0.5rem 0.75rem !important;
            font-size: 0.85rem;
        }

        .status-badge {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            padding: 0.35em 0.65em;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12 mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold mb-0">Put Away Management</h4>
                    <p class="text-muted small mb-0">Manage and move received items to their designated storage locations.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-12 py-3">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3">
                    <form action="" method="GET">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Search</label>
                                <input type="text" name="search" class="form-control form-control-sm"
                                    placeholder="Ref Number, NTT RN, Vendor..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Stock Category</label>
                                <select name="category" class="form-select form-select-sm">
                                    <option value="">-- All --</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat }}"
                                            {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Request Type</label>
                                <select name="request_type" class="form-select form-select-sm">
                                    <option value="">-- All --</option>
                                    @foreach ($requestTypes as $req)
                                        <option value="{{ $req }}"
                                            {{ request('request_type') == $req ? 'selected' : '' }}>{{ $req }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Client</label>
                                <select name="client_id" class="form-select form-select-sm">
                                    <option value="">-- All Clients --</option>
                                    @foreach ($clients as $c)
                                        <option value="{{ $c->id }}"
                                            {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end gap-1">
                                <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i
                                        class="ti tabler-search"></i> Filter</button>
                                <a href="{{ route('receiving.put.away') }}" class="btn btn-label-secondary btn-sm"><i
                                        class="ti tabler-refresh"></i></a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header border-bottom bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold small text-uppercase text-muted">Waiting for Shelving</h5>
                        <div class="d-flex gap-2">
                            <div class="badge bg-label-info">{{ $inbound->total() }} Total Inbounds</div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle table-compact mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Ref / Inbound #</th>
                                    <th>Category / Request Type</th>
                                    <th class="text-center">Status</th>
                                    <th>Client / Vendor</th>
                                    <th>Shipment Identifiers</th>
                                    <th>Received Info</th>
                                    <th width="100" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($inbound as $item)
                                    <tr>
                                        <td class="text-muted small">
                                            {{ $loop->iteration + ($inbound->currentPage() - 1) * $inbound->perPage() }}
                                        </td>
                                        <td>
                                            <div class="fw-bold text-primary">{{ $item->number }}</div>
                                            @if ($item->receiving_note)
                                                <div class="text-muted" style="font-size: 0.7rem;"><i
                                                        class="ti tabler-note me-1"></i>{{ $item->receiving_note }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-label-info status-badge">{{ $item->category }}</span>
                                            <div class="mt-1 small text-muted">{{ $item->request_type }}</div>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $statusClass = 'bg-label-secondary';
                                                switch ($item->status) {
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
                                            <span
                                                class="badge {{ $statusClass }} rounded-pill status-badge">{{ strtoupper($item->status) }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-medium small"><i
                                                    class="ti tabler-user me-1 text-muted"></i>{{ $item->client->name ?? '-' }}
                                            </div>
                                            <div class="small text-muted"><i
                                                    class="ti tabler-truck me-1"></i>{{ $item->vendor }}</div>
                                        </td>
                                        <td>
                                            @if ($item->rma_number)
                                                <div class="text-danger small" style="font-size: 0.75rem;">RMA:
                                                    {{ $item->rma_number }}</div>
                                            @endif
                                            @if ($item->itsm_number)
                                                <div class="text-info small" style="font-size: 0.75rem;">ITSM:
                                                    {{ $item->itsm_number }}</div>
                                            @endif
                                            @if (!$item->rma_number && !$item->itsm_number)
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-bold small">{{ $item->received_by }}</div>
                                            <div class="text-muted" style="font-size: 0.7rem;">
                                                {{ \Carbon\Carbon::parse($item->received_date)->format('d M Y') }}</div>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="{{ route('receiving.put.away.process', $item->id) }}"
                                                    class="btn btn-sm btn-label-primary p-1" title="Process Put Away">
                                                    <i class="ti tabler-package-export fs-5"></i>
                                                </a>
                                                <a href="{{ route('receiving.put.away.show', $item->id) }}"
                                                    class="btn btn-sm btn-label-info p-1" title="View Detail Inbound">
                                                    <i class="ti tabler-eye fs-5"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-label-danger p-1"
                                                    onclick="cancelRemainingPutAway({{ $item->id }}, '{{ $item->number }}')"
                                                    title="Cancel Remaining">
                                                    <i class="ti tabler-circle-x fs-5"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <img src="https://cdni.iconscout.com/illustration/premium/thumb/empty-box-4860341-4043997.png"
                                                alt="No data" style="width: 150px; opacity: 0.5;">
                                            <p class="text-muted mt-2">No pending items found for Put Away.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="px-3 py-2 border-top">
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
                confirmButtonColor: '#ea5455',
                cancelButtonColor: '#a8aaae',
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
                                Swal.fire('Cancelled!', 'Remaining items have been moved to cancelled status.',
                                    'success').then(
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
