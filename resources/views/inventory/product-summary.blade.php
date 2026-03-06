@extends('layout.index')
@section('title', 'Inventory Product')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header border-bottom">
                    <form action="{{ url()->current() }}" method="GET">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Search Product</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti tabler-search"></i></span>
                                    <input type="text" class="form-control" name="search"
                                        value="{{ request()->get('search') }}"
                                        placeholder="Search Part Name or Part Number ...">
                                    <button class="btn btn-primary" type="submit">Filter</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-body mt-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="30">#</th>
                                    <th>Part Name</th>
                                    <th>Part Number</th>
                                    <th class="text-center">Total In</th>
                                    <th class="text-center">In Inventory</th>
                                    <th class="text-center">Total Out</th>
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
                                            <span
                                                class="badge bg-label-secondary">{{ number_format($item->total_in) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="badge bg-label-success">{{ number_format($item->in_inventory) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-label-danger">{{ number_format($item->total_out) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-primary btn-sm btn-icon"
                                                onclick="showDetail('{{ addslashes($item->part_name) }}', '{{ addslashes($item->part_number) }}')"
                                                title="View SN Details">
                                                <i class="ti tabler-list-search"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center justify-content-center">
                                                <i class="ti tabler-box-off text-muted mb-2" style="font-size: 3rem;"></i>
                                                <p class="text-muted mb-0">No product records found.</p>
                                            </div>
                                        </td>
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
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title" id="modalPartName">Product SN Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive" style="max-height: 60vh;">
                        <table class="table table-hover align-middle mb-0" id="detailTable">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th>Asset ID</th>
                                    <th>Serial Number</th>
                                    <th>Client</th>
                                    <th>Storage</th>
                                    <th>Status</th>
                                    <th>Condition</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        function showDetail(partName, partNumber) {
            $('#modalPartName').text(`SN Details: ${partName} (${partNumber})`);
            const tbody = $('#detailTable tbody');
            tbody.html(
                '<tr><td colspan="6" class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Loading serial numbers...</p></td></tr>'
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
                            '<tr><td colspan="6" class="text-center py-4">No serial numbers found.</td></tr>'
                            );
                        return;
                    }

                    res.forEach(item => {
                        let statusBadge = 'bg-label-secondary';
                        if (item.status.toLowerCase() === 'available' || item.status.toLowerCase() ===
                            'in stock') {
                            statusBadge = 'bg-label-success';
                        } else if (item.status.toLowerCase().includes('outbound') || item.status
                            .toLowerCase().includes('shipped')) {
                            statusBadge = 'bg-label-danger';
                        }

                        tbody.append(`
                            <tr>
                                <td><span class="fw-bold">${item.unique_id}</span></td>
                                <td><span class="text-primary fw-medium">${item.serial_number}</span></td>
                                <td>${item.client}</td>
                                <td><small>${item.storage}</small></td>
                                <td><span class="badge ${statusBadge}">${item.status}</span></td>
                                <td>${item.condition}</td>
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
