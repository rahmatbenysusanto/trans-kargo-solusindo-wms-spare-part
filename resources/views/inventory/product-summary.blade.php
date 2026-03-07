@extends('layout.index')
@section('title', 'Inventory Product')

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
                    <h5 class="card-title mb-0 fw-bold"><i class="ti tabler-packages me-2 text-primary"></i>Product Stock
                        Summary</h5>
                    <form action="{{ url()->current() }}" method="GET" class="d-flex gap-2">
                        <div class="input-group input-group-sm" style="width: 300px;">
                            <input type="text" class="form-control" name="search" value="{{ request()->get('search') }}"
                                placeholder="Search Name or Number ...">
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
                                    <th>Part Name</th>
                                    <th>Part Number</th>
                                    <th class="text-center">Arrival (In)</th>
                                    <th class="text-center">Stock (Current)</th>
                                    <th class="text-center">Released (Out)</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $item)
                                    <tr>
                                        <td>{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                                        <td><span class="fw-bold text-dark">{{ $item->part_name }}</span></td>
                                        <td><span class="text-muted">{{ $item->part_number }}</span></td>
                                        <td class="text-center">
                                            <span class="badge bg-label-secondary fw-bold"
                                                style="min-width: 40px;">{{ number_format($item->total_in) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-label-success fw-bold"
                                                style="min-width: 40px;">{{ number_format($item->in_inventory) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-label-danger fw-bold"
                                                style="min-width: 40px;">{{ number_format($item->total_out) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-primary btn-xs btn-icon"
                                                onclick="showDetail('{{ addslashes($item->part_name) }}', '{{ addslashes($item->part_number) }}')"
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

    <!-- Modal Detail SN -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom bg-light py-3">
                    <h5 class="modal-title fw-bold" id="modalPartName">Loading...</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive" style="max-height: 60vh;">
                        <table class="table table-hover table-striped table-compact table-sm align-middle mb-0"
                            id="detailTable">
                            <thead class="bg-primary text-white sticky-top shadow-sm">
                                <tr>
                                    <th class="text-white">Asset ID</th>
                                    <th class="text-white">Serial Number</th>
                                    <th class="text-white">Client / Owner</th>
                                    <th class="text-white">Storage Location</th>
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
        function showDetail(partName, partNumber) {
            $('#modalPartName').html(
                `<i class="ti tabler-list me-1"></i> SN Details: <span class="text-primary">${partName}</span> (${partNumber})`
                );
            const tbody = $('#detailTable tbody');
            tbody.html(
                '<tr><td colspan="6" class="text-center py-5"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><p class="mt-2 text-muted small">Loading units...</p></td></tr>'
            );
            $('#detailModal').modal('show');

            $.ajax({
                url: '{{ route('inventory.product.summary.detail') }}',
                method: 'GET',
                data: {
                    part_name: partName,
                    part_number: partNumber
                },
                success: function(res) {
                    tbody.empty();
                    if (res.length === 0) {
                        tbody.append(
                            '<tr><td colspan="6" class="text-center py-4">No matching units found.</td></tr>'
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
                                <td><span class="text-mono fw-bold text-dark">${item.serial_number}</span></td>
                                <td><span class="small fw-medium">${item.client}</span></td>
                                <td><span class="text-muted" style="font-size: 0.75rem;">${item.storage}</span></td>
                                <td><span class="badge bg-label-info x-small" style="font-size: 0.65rem;">${item.condition}</span></td>
                                <td><span class="badge ${statusBadge} x-small" style="font-size: 0.65rem;">${item.status.toUpperCase()}</span></td>
                            </tr>
                        `);
                    });
                },
                error: function() {
                    tbody.html(
                        '<tr><td colspan="6" class="text-center py-4 text-danger">Failed to load data.</td></tr>'
                        );
                }
            });
        }
    </script>
@endsection
