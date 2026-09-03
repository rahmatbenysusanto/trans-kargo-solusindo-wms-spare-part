@extends('layout.index')
@section('title', 'Asset Group')

@section('css')
    <style>
        .text-mono {
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
        }

        .history-table td {
            font-size: 0.8rem;
            padding: 0.6rem 0.8rem !important;
        }

        .history-table thead th {
            font-size: 0.7rem;
            text-transform: uppercase;
            font-weight: 700;
        }

        .avatar-label {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 0.7rem;
            font-weight: 700;
            background: #e9ecef;
            color: #6c757d;
        }
    </style>
@endsection

@section('content')
    <!-- Header & Actions -->
    <div class="row g-3 mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h4 class="fw-bold mb-0 font-monospace text-primary">{{ $group->group_number }}</h4>
                        <span class="badge bg-label-primary">{{ count($items) }} member(s)</span>
                    </div>
                    @if ($group->name)
                        <h6 class="mb-0 mt-1">{{ $group->name }}</h6>
                    @endif
                    @if ($group->description)
                        <p class="text-muted small mb-0 mt-1">{{ $group->description }}</p>
                    @endif
                    <p class="text-muted small mb-0 mt-1">
                        Created {{ $group->created_at->format('d/m/Y H:i') }}
                        @if ($group->creator)
                            by {{ $group->creator->name }}
                        @endif
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                        <i class="ti tabler-plus me-1"></i> Add Inventory
                    </button>
                    @if (Auth::user()->isAdminWMS())
                        <form action="{{ route('inventory.asset-group.destroy', $group->id) }}" method="POST"
                            class="delete-group-form">
                            @csrf
                            <button type="button" class="btn btn-sm btn-outline-danger delete-group-btn">
                                <i class="ti tabler-trash me-1"></i> Delete Group
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('inventory.asset-group.index') }}" class="btn btn-sm btn-label-secondary">
                        <i class="ti tabler-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Members -->
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold small"><i class="ti tabler-box me-1 text-primary"></i>Members
                    </h5>
                    <span class="badge bg-label-primary">{{ count($items) }} unit(s)</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th>WH Asset #</th>
                                    <th>Serial Number</th>
                                    <th>Part</th>
                                    <th>Condition</th>
                                    <th>Status</th>
                                    <th>Location</th>
                                    <th>Client</th>
                                    <th>Added</th>
                                    <th class="text-center" style="width: 100px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($items as $m)
                                    @php
                                        $inv = $m->inventory;
                                    @endphp
                                    @if ($inv)
                                        <tr>
                                            <td>
                                                <span class="text-mono fw-bold text-dark">{{ $inv->unique_id }}</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('inventory.show', $inv->id) }}"
                                                    class="text-mono fw-bold text-primary">{{ $inv->serial_number }}</a>
                                                @if ($inv->parent_serial_number)
                                                    <div class="small text-muted">
                                                        <span class="text-muted">parent:</span> {{ $inv->parent_serial_number }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td style="max-width: 200px;">
                                                <span class="fw-semibold small cell-truncate">{{ $inv->part_name }}</span>
                                                <div class="small text-muted text-mono">{{ $inv->part_number ?: '-' }}</div>
                                            </td>
                                            <td>
                                                @php
                                                    $condClass = match ($inv->condition) {
                                                        'New', 'Refurbished', 'Good' => 'bg-label-success',
                                                        'Faulty', 'Write-off Needed' => 'bg-label-danger',
                                                        default => 'bg-label-info'
                                                    };
                                                @endphp
                                                <span class="badge {{ $condClass }}" style="font-size:0.7rem;">{{ $inv->condition ?? '-' }}</span>
                                            </td>
                                            <td>
                                                @php
                                                    $bgClass = 'bg-label-secondary';
                                                    switch (strtolower($inv->status)) {
                                                        case 'available':
                                                            $bgClass = 'bg-label-success';
                                                            break;
                                                        case 'staging':
                                                            $bgClass = 'bg-label-info';
                                                            break;
                                                        case 'out for replacement/ support':
                                                        case 'on relocation':
                                                            $bgClass = 'bg-label-warning';
                                                            break;
                                                        case 'out for loan':
                                                            $bgClass = 'bg-label-primary';
                                                            break;
                                                        case 'shipped / outbound':
                                                        case 'out for return':
                                                            $bgClass = 'bg-label-secondary';
                                                            break;
                                                        case 'write-off':
                                                            $bgClass = 'bg-label-danger';
                                                            break;
                                                        case 'unavailable':
                                                            $bgClass = 'bg-label-dark';
                                                            break;
                                                    }
                                                @endphp
                                                <span class="badge {{ $bgClass }}" style="font-size:0.7rem;">{{ strtoupper($inv->status) }}</span>
                                            </td>
                                            <td>
                                                @if ($inv->storageLevel)
                                                    <span class="small text-muted" style="white-space: nowrap;">
                                                        {{ $inv->storageLevel->bin->rak->zone->name }}-{{ $inv->storageLevel->bin->rak->name }}-{{ $inv->storageLevel->bin->name }}-{{ $inv->storageLevel->name }}
                                                    </span>
                                                @else
                                                    <span class="small text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="small">{{ $inv->client->name ?? '-' }}</span>
                                            </td>
                                            <td>
                                                <span class="small text-muted">{{ $m->created_at->format('d/m/Y H:i') }}</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex gap-1 justify-content-center">
                                                    <a href="{{ route('inventory.show', $inv->id) }}"
                                                        class="btn btn-icon btn-sm btn-label-primary" title="Open unit">
                                                        <i class="ti tabler-external-link fs-6"></i>
                                                    </a>
                                                    <button type="button"
                                                        class="btn btn-icon btn-sm btn-label-danger remove-member-btn"
                                                        title="Remove from group"
                                                        data-item-id="{{ $m->id }}"
                                                        data-sn="{{ $inv->serial_number }}">
                                                        <i class="ti tabler-x fs-6"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-5 text-muted">
                                            <i class="ti tabler-box fs-1 d-block mb-2 opacity-50"></i>
                                            No members yet. Add inventory rows to this group.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Suggested Members -->
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold small"><i class="ti tabler-bulb me-1 text-warning"></i>Suggested
                        Related Units</h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-label-secondary" id="refreshSuggestBtn">
                            <i class="ti tabler-refresh me-1"></i> Refresh
                        </button>
                        <button type="button" class="btn btn-sm btn-primary" id="addSelectedBtn">
                            <i class="ti tabler-plus me-1"></i> Add Selected
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th style="width: 40px;"></th>
                                    <th>Serial Number</th>
                                    <th>Part</th>
                                    <th>Condition</th>
                                    <th>Reason (related via)</th>
                                </tr>
                            </thead>
                            <tbody id="suggestionBody">
                                @include('inventory.asset-group._suggestion_rows', ['suggestions' => $suggestions])
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Combined Activity Timeline -->
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-label-primary py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold small text-primary"><i class="ti tabler-history me-1"></i>Combined
                        Activity History</h5>
                    <span class="badge bg-white text-primary border border-primary">{{ count($history) }} Records</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover history-table mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="130">Date</th>
                                    <th width="150">Serial</th>
                                    <th>Activity</th>
                                    <th>Ref Number</th>
                                    <th>Movement Path</th>
                                    <th>Description</th>
                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($history as $h)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ \Carbon\Carbon::parse($h['date'])->format('d M Y') }}
                                            </div>
                                            <div class="small text-muted">
                                                {{ \Carbon\Carbon::parse($h['date'])->format('H:i') }}</div>
                                        </td>
                                        <td>
                                            @if ($h['sn'])
                                                <span class="badge bg-label-secondary font-monospace"
                                                    style="font-size:0.65rem;">{{ $h['sn'] }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $catColor = 'bg-label-secondary';
                                                switch (strtolower($h['type'])) {
                                                    case 'inbound':
                                                        $catColor = 'bg-label-success';
                                                        break;
                                                    case 'outbound':
                                                        $catColor = 'bg-label-danger';
                                                        break;
                                                    case 'movement':
                                                        $catColor = 'bg-label-primary';
                                                        break;
                                                    case 'staging_in':
                                                    case 'staging_out':
                                                        $catColor = 'bg-label-info';
                                                        break;
                                                }
                                            @endphp
                                            <span class="badge {{ $catColor }}">
                                                {{ strtoupper(str_replace('_', ' ', $h['type'])) }}
                                            </span>
                                            @if ($h['category'])
                                                <div class="small text-muted mt-1">{{ $h['category'] }}</div>
                                            @endif
                                        </td>
                                        <td><span class="fw-bold text-dark small">{{ $h['reference'] ?: '-' }}</span></td>
                                        <td>
                                            <div class="d-flex flex-column gap-1">
                                                <div class="d-flex align-items-center small text-muted">
                                                    <i class="ti tabler-point text-danger me-1"></i>
                                                    {{ $h['from_location'] ?: 'Unknown' }}
                                                </div>
                                                <div class="d-flex align-items-center small text-success fw-medium">
                                                    <i class="ti tabler-arrow-narrow-right me-1"></i>
                                                    {{ $h['to_location'] ?: 'Unknown' }}
                                                </div>
                                            </div>
                                        </td>
                                        <td style="max-width: 220px;">
                                            <span class="small text-wrap">{{ $h['description'] }}</span>
                                            @if (isset($h['parent_sn']) && $h['parent_sn'])
                                                <div class="mt-1 small text-danger font-monospace">Old SN:
                                                    {{ $h['parent_sn'] }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center" title="{{ $h['user'] ?? 'System' }}">
                                                <div class="avatar-label me-1">
                                                    {{ substr($h['user'] ?? 'S', 0, 1) }}
                                                </div>
                                                <span
                                                    class="small truncate">{{ explode(' ', $h['user'] ?? 'System')[0] }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">No activity recorded for
                                            this asset group yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Member Modal -->
    <div class="modal fade" id="addMemberModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="ti tabler-box me-1 text-primary"></i>Add Inventory to Group
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <select class="form-select" id="addMemberSelect" multiple style="width:100%;"></select>
                    <div class="form-text mt-1">Search by SN / asset number / part number. Already-added members are
                        excluded.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="addMemberSubmit">
                        <i class="ti tabler-plus me-1"></i> Add to Group
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script>
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 1500,
        width: '18rem',
        padding: '0.5rem'
    });

    const GROUP_ID = {{ $group->id }};
    const MEMBER_IDS = @json($items->pluck('inventory_id')->all());
    const SEARCH_INV_URL = '{{ route('inventory.asset-group.search') }}';
    const SUGGEST_URL = '{{ route('inventory.asset-group.suggest', $group->id) }}';
    const ADD_ITEMS_URL = '{{ route('inventory.asset-group.add-items', $group->id) }}';
    const REMOVE_ITEM_URL = '{{ route('inventory.asset-group.remove-item', $group->id) }}';
    const CSRF = '{{ csrf_token() }}';

    // ---- Add by search (select2 multiple) ----
    $('#addMemberSelect').select2({
        dropdownParent: $('#addMemberModal'),
        placeholder: '-- Search SN / Asset # / Part --',
        allowClear: true,
        ajax: {
            url: SEARCH_INV_URL,
            dataType: 'json',
            delay: 250,
            data: p => ({ search: p.term, exclude_ids: MEMBER_IDS.join(',') }),
            processResults: data => ({ results: data.results })
        }
    });

    $('#addMemberModal').on('hidden.bs.modal', function () {
        $('#addMemberSelect').val(null).trigger('change');
    });

    $('#addMemberSubmit').on('click', function () {
        const ids = $('#addMemberSelect').val();
        if (!ids || !ids.length) {
            Swal.fire({ title: 'No selection', text: 'Search and pick at least one unit first.', icon: 'info' });
            return;
        }
        const btn = $(this);
        btn.prop('disabled', true);

        const body = new URLSearchParams();
        body.append('_token', CSRF);
        ids.forEach(id => body.append('inventory_ids[]', id));

        fetch(ADD_ITEMS_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF }, body })
            .then(r => r.json())
            .then(data => {
                if (data.status) {
                    Toast.fire({ icon: 'success', title: data.message || 'Added' });
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    btn.prop('disabled', false);
                    Swal.fire({ title: 'Failed', text: data.message || 'Something went wrong.', icon: 'error' });
                }
            })
            .catch(() => {
                btn.prop('disabled', false);
                Swal.fire({ title: 'Failed', text: 'Network error.', icon: 'error' });
            });
    });

    // ---- Suggested members: refresh & add selected ----
    $('#refreshSuggestBtn').on('click', function () {
        const btn = $(this);
        btn.prop('disabled', true);
        fetch(SUGGEST_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                if (data.status) {
                    $('#suggestionBody').html(data.html);
                    Toast.fire({ icon: 'success', title: 'Suggestions refreshed' });
                }
            })
            .catch(() => Swal.fire({ title: 'Failed', text: 'Could not refresh suggestions.', icon: 'error' }))
            .finally(() => btn.prop('disabled', false));
    });

    $('#addSelectedBtn').on('click', function () {
        const ids = $('.suggestion-check:checked').map(function () { return $(this).val(); }).get();
        if (!ids.length) {
            Swal.fire({ title: 'No selection', text: 'Tick the suggested units you want to add.', icon: 'info' });
            return;
        }
        const btn = $(this);
        btn.prop('disabled', true);

        const body = new URLSearchParams();
        body.append('_token', CSRF);
        ids.forEach(id => body.append('inventory_ids[]', id));

        fetch(ADD_ITEMS_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF }, body })
            .then(r => r.json())
            .then(data => {
                if (data.status) {
                    Toast.fire({ icon: 'success', title: data.message || 'Added' });
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    btn.prop('disabled', false);
                    Swal.fire({ title: 'Failed', text: data.message || 'Something went wrong.', icon: 'error' });
                }
            })
            .catch(() => {
                btn.prop('disabled', false);
                Swal.fire({ title: 'Failed', text: 'Network error.', icon: 'error' });
            });
    });

    // ---- Remove member ----
    document.querySelectorAll('.remove-member-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const itemId = this.dataset.itemId;
            const sn = this.dataset.sn;
            Swal.fire({
                title: 'Remove from group?',
                html: `<strong>${sn}</strong> will be removed from this asset group.<br>
                       <span class="text-muted small">The inventory row itself is not affected.</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, remove',
                cancelButtonText: 'Cancel'
            }).then(result => {
                if (!result.isConfirmed) return;
                const body = new URLSearchParams();
                body.append('_token', CSRF);
                body.append('item_id', itemId);
                fetch(REMOVE_ITEM_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF }, body })
                    .then(r => r.json())
                    .then(data => {
                        if (data.status) {
                            Toast.fire({ icon: 'success', title: data.message || 'Removed' });
                            setTimeout(() => window.location.reload(), 500);
                        } else {
                            Swal.fire({ title: 'Failed', text: data.message || 'Something went wrong.', icon: 'error' });
                        }
                    })
                    .catch(() => Swal.fire({ title: 'Failed', text: 'Network error.', icon: 'error' }));
            });
        });
    });

    // ---- Delete group (admin only) ----
    document.querySelectorAll('.delete-group-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const form = this.closest('.delete-group-form');
            Swal.fire({
                title: 'Delete Asset Group?',
                html: `Group <strong>{{ $group->group_number }}</strong> will be deleted.<br>
                       <span class="text-muted small">Inventory rows and their history are NOT deleted.</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then(result => {
                if (result.isConfirmed) form.submit();
            });
        });
    });
</script>
@endsection
