@extends('layout.index')
@section('title', 'Asset Group')

@section('css')
    <style>
        .text-mono {
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
        }
    </style>
@endsection

@section('content')
    <div class="row g-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="card-title mb-0 fw-bold"><i class="ti tabler-box me-2 text-primary"></i>Asset Group</h5>
                        <p class="text-muted small mb-0">Group several serial numbers that belong to one asset unit.</p>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <form method="GET" class="d-flex gap-2">
                            <input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm"
                                placeholder="Search group # / SN / Asset #" style="min-width: 240px;">
                            <button type="submit" class="btn btn-sm btn-label-secondary"><i class="ti tabler-search"></i></button>
                        </form>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createGroupModal">
                            <i class="ti tabler-plus me-1"></i> New Asset Group
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th>Group Number</th>
                                    <th>Name</th>
                                    <th>Members</th>
                                    <th>Serial Numbers</th>
                                    <th>Created</th>
                                    <th class="text-center" style="width: 110px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($groups as $group)
                                    <tr>
                                        <td>
                                            <a href="{{ route('inventory.asset-group.show', $group->id) }}"
                                                class="text-mono fw-bold text-primary">{{ $group->group_number }}</a>
                                        </td>
                                        <td><span class="small">{{ $group->name ?: '-' }}</span></td>
                                        <td>
                                            <span class="badge bg-label-primary">{{ $group->items_count }} member(s)</span>
                                        </td>
                                        <td>
                                            @forelse ($group->items->take(3) as $item)
                                                @if ($item->inventory)
                                                    <span class="badge bg-label-secondary font-monospace me-1"
                                                        style="font-size:0.7rem;">{{ $item->inventory->serial_number }}</span>
                                                @endif
                                            @empty
                                                <span class="text-muted small">-</span>
                                            @endforelse
                                        </td>
                                        <td>
                                            <span class="small">{{ $group->created_at->format('d/m/Y H:i') }}</span>
                                            <div class="small text-muted">{{ $group->creator->name ?? 'System' }}</div>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex gap-1 justify-content-center">
                                                <a href="{{ route('inventory.asset-group.show', $group->id) }}"
                                                    class="btn btn-icon btn-sm btn-label-primary" title="View group">
                                                    <i class="ti tabler-eye fs-6"></i>
                                                </a>
                                                @if (Auth::user()->isAdminWMS())
                                                    <form action="{{ route('inventory.asset-group.destroy', $group->id) }}"
                                                        method="POST" class="d-inline delete-group-form">
                                                        @csrf
                                                        <button type="button" class="btn btn-icon btn-sm btn-label-danger delete-group-btn"
                                                            title="Delete group" data-group="{{ $group->group_number }}">
                                                            <i class="ti tabler-trash fs-6"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="ti tabler-box fs-1 d-block mb-2 opacity-50"></i>
                                            No asset groups yet. Create one to tie serial numbers into a single asset unit.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($groups->hasPages())
                    <div class="card-footer bg-white py-3 px-3 border-0">
                        {{ $groups->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Create Group Modal -->
    <div class="modal fade" id="createGroupModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form id="createGroupForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold"><i class="ti tabler-box me-1 text-primary"></i>New Asset Group</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Group Name <span class="text-muted">(optional)</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Site replacement unit — chain">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Description <span class="text-muted">(optional)</span></label>
                            <textarea name="description" rows="2" class="form-control"
                                placeholder="Short note about this asset unit"></textarea>
                        </div>
                        <div class="mb-1">
                            <label class="form-label small fw-bold">Initial Members <span class="text-muted">(optional)</span></label>
                            @if ($seedInventory)
                                <div class="d-flex align-items-center gap-2 mb-2 p-2 border rounded bg-light">
                                    <span class="badge bg-label-primary font-monospace" style="font-size:0.7rem;">
                                        {{ $seedInventory->serial_number }}
                                    </span>
                                    <span class="small text-muted">{{ $seedInventory->part_name }}
                                        ({{ $seedInventory->unique_id }})</span>
                                    <input type="hidden" name="inventory_ids[]" value="{{ $seedInventory->id }}">
                                    <span class="ms-auto badge bg-label-success">Seed</span>
                                </div>
                            @endif
                            <select class="form-select" id="createGroupInvSelect" multiple
                                style="width:100%;"></select>
                            <div class="form-text">Search by SN / asset number / part number.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="createGroupSubmit">
                            <i class="ti tabler-plus me-1"></i> Create Group
                        </button>
                    </div>
                </form>
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

    const CREATE_GROUP_URL = '{{ route('inventory.asset-group.store') }}';
    const SEARCH_INV_URL = '{{ route('inventory.asset-group.search') }}';

    function ajaxSelect2(url, term, params) {
        const query = new URLSearchParams({ search: term });
        Object.entries(params).forEach(([k, v]) => {
            if (v) query.set(k, v);
        });
        return fetch(url + '?' + query.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => ({ results: data.results }));
    }

    // Initial-member picker inside create modal
    $('#createGroupInvSelect').select2({
        dropdownParent: $('#createGroupModal'),
        placeholder: '-- Search SN / Asset # / Part --',
        allowClear: true,
        ajax: {
            url: SEARCH_INV_URL,
            dataType: 'json',
            delay: 250,
            data: p => ({ search: p.term }),
            processResults: data => ({ results: data.results })
        }
    });

    $('#createGroupModal').on('hidden.bs.modal', function () {
        $('#createGroupInvSelect').val(null).trigger('change');
        $('#createGroupForm')[0].reset();
    });

    $('#createGroupForm').on('submit', function (e) {
        e.preventDefault();
        const btn = $('#createGroupSubmit');
        btn.prop('disabled', true);

        fetch(CREATE_GROUP_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: new FormData(this)
        })
            .then(r => r.json())
            .then(data => {
                if (data.status) {
                    Toast.fire({ icon: 'success', title: data.message || 'Created' });
                    setTimeout(() => { window.location.href = data.redirect; }, 600);
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

    // Delete group (admin only)
    document.querySelectorAll('.delete-group-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const form = this.closest('.delete-group-form');
            Swal.fire({
                title: 'Delete Asset Group?',
                html: `Group <strong>${this.dataset.group}</strong> will be deleted.<br>
                       <span class="text-muted small">Membership links are removed — inventory rows and their history are NOT deleted.</span>`,
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
