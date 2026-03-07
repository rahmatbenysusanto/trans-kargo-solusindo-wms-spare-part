@extends('layout.index')
@section('title', 'Staging Management')

@section('content')
    <div class="row">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="fw-bold mb-0">Staging Management</h4>
                <p class="text-muted small mb-0">Manage items currently in testing or staging lab.</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary" onclick="showSelectModal()">
                    <i class="ti tabler-clipboard-check me-1"></i> Start Staging
                </button>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body border-bottom">
                    <form action="{{ route('staging.index') }}" method="GET">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Search (S/N, Part Name)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti tabler-search"></i></span>
                                    <input type="text" name="search" class="form-control" placeholder="Search..."
                                        value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-secondary w-100">Filter</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0" style="font-size: 0.85rem;">
                        <thead class="table-light">
                            <tr>
                                <th width="50">#</th>
                                <th>Serial Number</th>
                                <th>Part Name/Number</th>
                                <th>Origin Location</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stagingItems as $item)
                                <tr>
                                    <td>{{ ($stagingItems->currentPage() - 1) * $stagingItems->perPage() + $loop->iteration }}
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $item->serial_number }}</div>
                                        <small class="text-muted">Since:
                                            {{ \Carbon\Carbon::parse($item->last_staging_date)->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-medium">{{ $item->part_name }}</div>
                                        <small class="text-muted">{{ $item->part_number }}</small>
                                    </td>
                                    <td>
                                        @if ($item->storageLevel)
                                            <span
                                                class="badge bg-label-secondary small">{{ $item->storageLevel->zone->name }}
                                                - {{ $item->storageLevel->name }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-xs btn-label-success"
                                            onclick="finishStagingModal({{ $item->id }})">
                                            <i class="ti tabler-circle-check fs-6 me-1"></i> Finish
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No items currently in staging.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-flex justify-content-end">
                    {{ $stagingItems->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Start Staging Modal -->
    <div class="modal fade" id="selectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold">Select Inventory for Staging</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Search Available Inventory (S/N, Part Name)</label>
                        <div class="input-group">
                            <input type="text" id="inventorySearchInput" class="form-control" placeholder="Type here...">
                            <button class="btn btn-primary" onclick="searchAvailable()">Search</button>
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height: 400px;">
                        <table class="table table-sm table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th width="30">Pick</th>
                                    <th>Serial Number</th>
                                    <th>Part Name</th>
                                    <th>Location</th>
                                </tr>
                            </thead>
                            <tbody id="inventorySearchResult">
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">Type above to search...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <label class="form-label small fw-bold">Notes / Description (Optional)</label>
                        <textarea id="stagingDescription" class="form-control" rows="2" placeholder="Testing purpose..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top p-2">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="submitToStaging()">Move to Staging</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Finish Staging Modal -->
    <div class="modal fade" id="finishModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold">Complete Staging & Result</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="finishId">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Staging Result (New Condition)</label>
                        <select id="finishCondition" class="form-select">
                            <option value="New">New (Excellent)</option>
                            <option value="Good">Good (Passed)</option>
                            <option value="Faulty">Faulty (Failed)</option>
                            <option value="Write-off Needed">Bad / Write-off</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Checking Note</label>
                        <textarea id="finishDescription" class="form-control" rows="3" placeholder="Checked by Lab..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top p-2">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" onclick="submitFinishStaging()">Confirm Done</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        function showSelectModal() {
            $('#selectModal').modal('show');
        }

        function searchAvailable() {
            const query = $('#inventorySearchInput').val();
            if (!query) return;

            $('#inventorySearchResult').html(
                '<tr><td colspan="4" class="text-center"><div class="spinner-border spinner-border-sm text-primary"></div> Searching...</td></tr>'
                );

            fetch(`{{ route('staging.search-available') }}?search=${query}`)
                .then(res => res.json())
                .then(data => {
                    let html = '';
                    if (data.length === 0) {
                        html =
                            '<tr><td colspan="4" class="text-center text-muted py-3">No matching items found.</td></tr>';
                    } else {
                        data.forEach(item => {
                            html += `
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input staging-pick" value="${item.id}">
                                </td>
                                <td><div class="fw-bold small text-dark">${item.serial_number}</div></td>
                                <td><div class="small">${item.part_name}</div></td>
                                <td><span class="badge bg-label-secondary x-small">${item.storage_level ? item.storage_level.zone.name + ' - ' + item.storage_level.name : 'N/A'}</span></td>
                            </tr>
                        `;
                        });
                    }
                    $('#inventorySearchResult').html(html);
                });
        }

        function submitToStaging() {
            const selectedIds = [];
            $('.staging-pick:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length === 0) {
                Swal.fire('Warning', 'Please select at least one item.', 'warning');
                return;
            }

            fetch(`{{ route('staging.start') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        inventory_ids: selectedIds,
                        description: $('#stagingDescription').val()
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status) {
                        Swal.fire('Success', data.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                });
        }

        function finishStagingModal(id) {
            $('#finishId').val(id);
            $('#finishModal').modal('show');
        }

        function submitFinishStaging() {
            const id = $('#finishId').val();

            fetch(`{{ route('staging.finish') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        inventory_ids: [id],
                        condition: $('#finishCondition').val(),
                        description: $('#finishDescription').val()
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status) {
                        Swal.fire('Success', 'Staging completed.', 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                });
        }
    </script>
@endsection
