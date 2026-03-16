@extends('layout.index')
@section('title', 'Storage Inventory')

@section('css')
    <style>
        .table thead th {
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: #5d596c;
            white-space: nowrap;
        }

        .table-compact td {
            font-size: 0.8rem;
            padding: 0.5rem 0.6rem !important;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 shadow-sm">
                <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold"><i class="ti tabler-server me-2 text-primary"></i>Storage Inventory</h5>
                    <form action="{{ url()->current() }}" method="GET" class="d-flex gap-2">
                        @if (Auth::user()->isAdminWMS() || Auth::user()->clients->count() > 1)
                            <select name="client_id" class="form-select form-select-sm" onchange="this.form.submit()"
                                style="width: 200px;">
                                <option value="">All Clients</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}"
                                        {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                        {{ $client->name }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                        <div class="input-group input-group-sm" style="width: 300px;">
                            <input type="text" class="form-control" name="search" value="{{ request()->get('search') }}"
                                placeholder="Search Zone, Rak, Bin, Level ...">
                            <button class="btn btn-primary" type="submit">Filter</button>
                        </div>
                    </form>
                </div>
                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-compact table-sm text-nowrap align-middle">
                            <thead class="table-light border-top">
                                <tr>
                                    <th width="30">#</th>
                                    <th>Zone</th>
                                    <th>Rak</th>
                                    <th>Bin</th>
                                    <th>Level</th>
                                    <th class="text-center">Total Items</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $item)
                                    <tr>
                                        <td>{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                                        <td><span class="fw-bold text-dark">{{ $item->zone_name }}</span></td>
                                        <td>{{ $item->rak_name }}</td>
                                        <td>{{ $item->bin_name }}</td>
                                        <td>{{ $item->level_name }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-label-success fw-bold"
                                                style="min-width: 40px;">{{ number_format($item->total_items) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-primary btn-xs btn-icon"
                                                onclick="showDetail('{{ $item->storage_level_id }}', '{{ $item->zone_name }}-{{ $item->rak_name }}-{{ $item->bin_name }}-{{ $item->level_name }}')"
                                                title="View Breakdown">
                                                <i class="ti tabler-search"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">No records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $data->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail Storage -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom bg-light py-3">
                    <h5 class="modal-title fw-bold" id="modalStorageName">Loading...</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive" style="max-height: 60vh;">
                        <table class="table table-hover table-striped table-compact table-sm align-middle mb-0"
                            id="detailTable">
                            <thead class="bg-primary text-white sticky-top shadow-sm">
                                <tr>
                                    <th class="text-white">Asset ID</th>
                                    <th class="text-white">Part Name</th>
                                    <th class="text-white">Part Number</th>
                                    <th class="text-white">Serial Number</th>
                                    <th class="text-white">Client / Owner</th>
                                    <th class="text-white">Condition</th>
                                    <th class="text-white">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-top p-2">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        function showDetail(storageLevelId, storageName) {
            $('#modalStorageName').html(
                `<i class="ti tabler-list me-1"></i> Items in Storage: <span class="text-primary">${storageName}</span>`
            );
            const tbody = $('#detailTable tbody');
            tbody.html(
                '<tr><td colspan="7" class="text-center py-5"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><p class="mt-2 text-muted small">Loading units...</p></td></tr>'
            );
            $('#detailModal').modal('show');

            $.ajax({
                url: '{{ route('inventory.storage.detail') }}',
                method: 'GET',
                data: {
                    storage_level_id: storageLevelId,
                    client_id: '{{ request('client_id') }}'
                },
                success: function(res) {
                    tbody.empty();
                    if (res.length === 0) {
                        tbody.append(
                            '<tr><td colspan="7" class="text-center py-4">No matching units found.</td></tr>'
                        );
                        return;
                    }

                    res.forEach(item => {
                        let statusBadge = 'bg-label-secondary';
                        let sLower = item.status.toLowerCase();
                        if (sLower === 'available' || sLower === 'in stock') statusBadge =
                            'bg-label-success';
                        else if (sLower === 'staging') statusBadge = 'bg-label-info';
                        else if (sLower.includes('outbound') || sLower.includes('shipped'))
                            statusBadge = 'bg-label-warning';
                        else if (sLower === 'write-off' || sLower === 'faulty') statusBadge =
                            'bg-label-danger';

                        tbody.append(`
                            <tr>
                                <td><span class="text-mono fw-bold text-primary">${item.unique_id}</span></td>
                                <td><span class="fw-bold text-dark">${item.part_name}</span></td>
                                <td><span class="text-muted">${item.part_number}</span></td>
                                <td><span class="text-mono fw-bold text-dark">${item.serial_number}</span></td>
                                <td><span class="small fw-medium">${item.client}</span></td>
                                <td><span class="badge bg-label-info x-small" style="font-size: 0.65rem;">${item.condition}</span></td>
                                <td><span class="badge ${statusBadge} x-small" style="font-size: 0.65rem;">${item.status.toUpperCase()}</span></td>
                            </tr>
                        `);
                    });
                },
                error: function() {
                    tbody.html(
                        '<tr><td colspan="7" class="text-center py-4 text-danger">Failed to load data.</td></tr>'
                    );
                }
            });
        }
    </script>
@endsection
