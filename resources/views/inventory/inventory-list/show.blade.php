@extends('layout.index')
@section('title', 'Inventory Detail')

@section('css')
    <style>
        .detail-label {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #a19fad;
            letter-spacing: 0.5px;
            margin-bottom: 0.25rem;
            display: block;
        }

        .detail-value {
            font-size: 0.9rem;
            font-weight: 600;
            color: #444050;
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
    <div class="row">
        <!-- Header & Action -->
        <div class="col-12 mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold mb-0">Inventory Detail</h4>
                    <p class="text-muted small mb-0">Track lifecycle and complete specifications of the unit.</p>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti tabler-box me-1"></i> Asset Group
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <button class="dropdown-item" data-bs-toggle="modal"
                                    data-bs-target="#invCreateGroupModal">
                                    <i class="ti tabler-plus me-1 text-primary"></i> Create New Group with this Asset
                                </button>
                            </li>
                            <li>
                                <button class="dropdown-item" data-bs-toggle="modal"
                                    data-bs-target="#invAddGroupModal">
                                    <i class="ti tabler-folder-plus me-1 text-primary"></i> Add to Existing Group
                                </button>
                            </li>
                            @if (isset($assetGroups) && $assetGroups->isNotEmpty())
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <h6 class="dropdown-header">In these groups</h6>
                                </li>
                                @foreach ($assetGroups as $ag)
                                    <li>
                                        <a class="dropdown-item text-mono small"
                                            href="{{ route('inventory.asset-group.show', $ag->id) }}">
                                            <i class="ti tabler-box me-1 text-muted"></i>{{ $ag->group_number }}
                                        </a>
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                    </div>
                    <button onclick="window.print()" class="btn btn-sm btn-label-secondary">
                        <i class="ti tabler-printer me-1"></i> Print Page
                    </button>
                    <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-primary">
                        <i class="ti tabler-arrow-left me-1"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <!-- Left Column: Unit Info -->
        <div class="col-md-4">
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-primary py-3">
                    <h5 class="card-title mb-0 text-white fw-bold small"><i class="ti tabler-info-square me-2"></i>Core
                        Identity</h5>
                </div>
                <div class="card-body pt-3">
                    <div class="text-center mb-4 p-3 bg-light rounded-3 border border-dashed border-primary">
                        <span class="detail-label">Warehouse Asset ID / Unique ID</span>
                        <div class="h4 fw-bold text-primary mb-0 font-monospace">{{ $inventory->unique_id }}</div>
                        <div class="mt-2">
                            @php
                                $statusClass = 'bg-label-secondary';
                                switch (strtolower($inventory->status)) {
                                    case 'available':
                                        $statusClass = 'bg-label-success';
                                        break;
                                    case 'staging':
                                        $statusClass = 'bg-label-info';
                                        break;
                                    case 'shipped / outbound':
                                        $statusClass = 'bg-label-warning';
                                        break;
                                    case 'write-off':
                                    case 'faulty':
                                        $statusClass = 'bg-label-danger';
                                        break;
                                }
                            @endphp
                            <span class="badge {{ $statusClass }} uppercase fw-bold">{{ $inventory->status }}</span>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <span class="detail-label">Serial Number</span>
                            <span class="detail-value text-dark">{{ $inventory->serial_number }}</span>
                        </div>
                        <div class="col-6">
                            <span class="detail-label">Old / Parent SN</span>
                            @if ($parentInventory)
                                <a href="{{ route('inventory.show', $parentInventory->id) }}" class="detail-value text-danger fw-bold">
                                    <i class="ti tabler-link me-1"></i>{{ $inventory->parent_serial_number }}
                                </a>
                            @else
                                <span class="detail-value text-muted">{{ $inventory->parent_serial_number ?: '-' }}</span>
                            @endif
                        </div>
                        <div class="col-6">
                            <span class="detail-label">Condition</span>
                            <span
                                class="badge {{ $inventory->condition == 'New' ? 'bg-label-info' : 'bg-label-secondary' }}">{{ $inventory->condition ?? '-' }}</span>
                        </div>
                        <div class="col-12 border-top pt-2 mt-2">
                            <span class="detail-label">Client / Owner</span>
                            <span class="detail-value"><i
                                    class="ti tabler-user me-1 text-muted"></i>{{ $inventory->client->name ?? '-' }}</span>
                        </div>
                        <div class="col-12 border-top pt-2 mt-2">
                            <span class="detail-label">Storage Location</span>
                            @if ($inventory->storageLevel)
                                <div class="p-2 bg-label-secondary rounded border border-light">
                                    <div class="small fw-bold text-dark font-monospace">
                                        {{ $inventory->storageLevel->bin->rak->zone->name }}-{{ $inventory->storageLevel->bin->rak->name }}-{{ $inventory->storageLevel->bin->name }}-{{ $inventory->storageLevel->name }}
                                    </div>
                                </div>
                            @else
                                <span class="text-muted small">Not Assigned to Bin</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Specs -->
            <div class="card shadow-sm border-0">
                <div class="card-header border-bottom py-3">
                    <h5 class="card-title mb-0 fw-bold small"><i
                            class="ti tabler-settings me-1 text-primary"></i>Specification</h5>
                </div>
                <div class="card-body py-3">
                    <div class="mb-3">
                        <span class="detail-label">Part Name</span>
                        <span class="detail-value">{{ $inventory->part_name }}</span>
                    </div>
                    <div class="mb-3">
                        <span class="detail-label">Part Number / SKU</span>
                        <span class="detail-value">{{ $inventory->part_number }}</span>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <span class="detail-label">Brand</span>
                            <span class="detail-value small">{{ $inventory->product->brand->name ?? '-' }}</span>
                        </div>
                        <div class="col-6">
                            <span class="detail-label">Group</span>
                            <span class="detail-value small">{{ $inventory->productGroup->name ?? '-' }}</span>
                        </div>
                    </div>
                    @if ($inventory->part_description)
                        <div class="mt-3 text-muted border-top pt-2 small">
                            <span class="detail-label">Product Description</span>
                            {{ $inventory->part_description }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column: Lifecycle & Details -->
        <div class="col-md-8">
            <!-- Summary Stats -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-3 border-start border-primary border-4">
                        <span class="detail-label">Last Staging (Lab)</span>
                        <span
                            class="h6 mb-0 fw-bold">{{ $inventory->last_staging_date ? \Carbon\Carbon::parse($inventory->last_staging_date)->format('d/m/Y') : 'Never' }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-3 border-start border-info border-4">
                        <span class="detail-label">Last Movement</span>
                        <span
                            class="h6 mb-0 fw-bold">{{ $inventory->last_movement_date ? \Carbon\Carbon::parse($inventory->last_movement_date)->format('d/m/Y') : 'Never' }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm p-3 border-start border-success border-4 h-100">
                        <span class="detail-label">Total Stock Qty</span>
                        <span class="h6 mb-0 fw-bold text-success">{{ $inventory->qty }} Unit</span>
                    </div>
                </div>
            </div>

            <!-- Inbound Info Section -->
            @php
                $firstInboundDetail = $inventory->details->first() ? $inventory->details->first()->inboundDetail : null;
                $firstInbound = $firstInboundDetail ? $firstInboundDetail->inbound ?? null : null;
            @endphp
            @if ($firstInbound)
                <div class="card shadow-sm border-0 mb-4 overflow-hidden">
                    <div
                        class="card-header bg-label-success py-2 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0 fw-bold small"><i class="ti tabler-login me-2"></i>ORIGIN INBOUND DATA
                            (COMPLETE)</h6>
                        <span
                            class="badge bg-white text-success border border-success fw-bold">{{ $firstInbound->number }}</span>
                    </div>
                    <div class="card-body pt-3">
                        <!-- Section 1: Transaction Primary -->
                        <div class="row g-2 mb-3">
                            <div class="col-md-2">
                                <span class="detail-label">Stock Category</span>
                                <span
                                    class="detail-value text-dark small fw-bold">{{ strtoupper($firstInbound->category) }}</span>
                            </div>
                            <div class="col-md-2">
                                <span class="detail-label">Request Type</span>
                                <span
                                    class="detail-value text-dark small">{{ strtoupper($firstInbound->request_type ?: '-') }}</span>
                            </div>
                            <div class="col-md-2">
                                <span class="detail-label">Received Date</span>
                                <span
                                    class="detail-value small">{{ $firstInbound->received_date ? \Carbon\Carbon::parse($firstInbound->received_date)->format('d M Y') : '-' }}</span>
                            </div>
                            <div class="col-md-3">
                                <span class="detail-label">Received By</span>
                                <span class="detail-value small">{{ $firstInbound->received_by ?: '-' }}</span>
                            </div>
                            <div class="col-md-3">
                                <span class="detail-label">Inbound Old SN</span>
                                <span
                                    class="detail-value small text-danger fw-bold text-mono">{{ $firstInboundDetail->old_serial_number ?: ($firstInboundDetail->parent_sn ?: '-') }}</span>
                            </div>
                        </div>

                        <!-- Section 2: Requestor & Client Contact -->
                        <div class="p-2 mb-3 bg-light rounded border border-light">
                            <div class="row g-2">
                                <div class="col-md-4 border-end">
                                    <span class="detail-label">Requestor (NTT)</span>
                                    <span
                                        class="detail-value small fw-bold">{{ $firstInbound->ntt_requestor ?? '-' }}</span>
                                    <div class="text-muted" style="font-size: 0.65rem;">Req Date:
                                        {{ $firstInbound->request_date ?? '-' }}</div>
                                </div>
                                <div class="col-md-4 border-end ps-3">
                                    <span class="detail-label">Client Contact</span>
                                    <span class="detail-value small">{{ $firstInbound->client_contact ?? '-' }}</span>
                                </div>
                                <div class="col-md-4 ps-3">
                                    <span class="detail-label">Pickup/Origin Address</span>
                                    <span class="small text-muted d-block"
                                        style="line-height: 1.1;">{{ $firstInbound->pickup_address ?? '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Reference Identifiers Group -->
                        <div class="row g-2 mb-3">
                            <div class="col-md-3">
                                <span class="detail-label">NTT RN#</span>
                                <span
                                    class="detail-value text-primary small fw-bold">{{ $firstInbound->receiving_note ?? '-' }}</span>
                            </div>
                            <div class="col-md-3">
                                <span class="detail-label">SAP PO#</span>
                                <span class="detail-value small">{{ $firstInbound->sap_po_number ?? '-' }}</span>
                            </div>
                            <div class="col-md-3">
                                <span class="detail-label">eCapex#</span>
                                <span class="detail-value small">{{ $firstInbound->ecapex_number ?? '-' }}</span>
                            </div>
                            <div class="col-md-3">
                                <span class="detail-label">ITSM#</span>
                                <span class="detail-value small fw-bold">{{ $firstInbound->itsm_number ?? '-' }}</span>
                            </div>
                        </div>

                        <!-- Section 4: External & Financial Identifiers -->
                        <div class="row g-2 mb-3">
                            <div class="col-md-3">
                                <span class="detail-label">Vendor</span>
                                <span class="detail-value small">{{ $firstInbound->vendor ?? '-' }}</span>
                            </div>
                            <div class="col-md-3">
                                <span class="detail-label">Vendor DN#</span>
                                <span class="detail-value small">{{ $firstInbound->vendor_dn_number ?? '-' }}</span>
                            </div>
                            <div class="col-md-2">
                                <span class="detail-label">TKS DN#</span>
                                <span class="detail-value small">{{ $firstInbound->tks_dn_number ?? '-' }}</span>
                            </div>
                            <div class="col-md-2">
                                <span class="detail-label">TKS Invoice#</span>
                                <span
                                    class="detail-value small text-danger">{{ $firstInbound->tks_invoice_number ?? '-' }}</span>
                            </div>
                            <div class="col-md-2">
                                <span class="detail-label">RMA#</span>
                                <span class="badge bg-label-warning x-small">{{ $firstInbound->rma_number ?? '-' }}</span>
                            </div>
                        </div>

                        <!-- Section 5: Shipment & Delivery -->
                        <div class="row g-2 pt-2 border-top">
                            <div class="col-md-3">
                                <span class="detail-label">Courier DN</span>
                                <span class="detail-value small">{{ $firstInbound->courier_delivery_note ?? '-' }}</span>
                            </div>
                            <div class="col-md-3">
                                <span class="detail-label">Courier Invoice</span>
                                <span class="detail-value small">{{ $firstInbound->courier_invoice ?? '-' }}</span>
                            </div>
                            <div class="col-md-3">
                                <span class="detail-label">STTB / Reference</span>
                                <span
                                    class="detail-value small">{{ $firstInbound->sttb ?? ($firstInbound->reff_number ?? '-') }}</span>
                            </div>
                            <div class="col-md-3">
                                <span class="detail-label">Delivery Date</span>
                                <span class="detail-value small">{{ $firstInbound->delivery_date ?? '-' }}</span>
                            </div>
                        </div>

                        <!-- Section 6: Notes -->
                        @if ($firstInboundDetail->notes)
                        <div class="row g-2 pt-2 mt-2 border-top">
                            <div class="col-12">
                                <span class="detail-label">Notes</span>
                                <div class="p-2 bg-light rounded border small text-dark fw-medium" style="white-space: pre-line;">
                                    {{ $firstInboundDetail->notes }}
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Transaction History -->
            <div class="card shadow-sm border-0">
                <div
                    class="card-header bg-label-primary py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold small text-primary"><i class="ti tabler-history me-1"></i>Movement
                        & Activity
                        History</h5>
                    <span class="badge bg-white text-primary border border-primary">{{ count($history) }} Records</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover history-table mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="150">Date</th>
                                    <th>Activity</th>
                                    <th>Ref Number</th>
                                    <th>Movement Path</th>
                                    <th>Product Description</th>
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
                                            @php
                                                $catColor = 'bg-label-secondary';
                                                $catIcon = 'ti-circle';
                                                switch (strtolower($h['type'])) {
                                                    case 'inbound':
                                                        $catColor = 'bg-label-success';
                                                        $catIcon = 'ti-download';
                                                        break;
                                                    case 'outbound':
                                                        $catColor = 'bg-label-danger';
                                                        $catIcon = 'ti-upload';
                                                        break;
                                                    case 'movement':
                                                        $catColor = 'bg-label-primary';
                                                        $catIcon = 'ti-arrows-left-right';
                                                        break;
                                                    case 'staging_in':
                                                    case 'staging_out':
                                                        $catColor = 'bg-label-info';
                                                        $catIcon = 'ti-test-pipe';
                                                        break;
                                                }
                                            @endphp
                                            <span class="badge {{ $catColor }} d-flex align-items-center w-fit">
                                                <i
                                                    class="ti tabler-{{ str_starts_with(strtolower($h['type']), 'staging') ? 'flask' : (strtolower($h['type']) == 'inbound' ? 'circle-arrow-down' : (strtolower($h['type']) == 'outbound' ? 'circle-arrow-up' : 'refresh')) }} me-1 fs-xs"></i>
                                                {{ strtoupper(str_replace('_', ' ', $h['type'])) }}
                                            </span>
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
                                        <td style="max-width: 200px;">
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
                                        <td colspan="6" class="text-center py-5 text-muted">No activity recorded for
                                            this unit.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Group with this Asset Modal -->
    <div class="modal fade" id="invCreateGroupModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="invCreateGroupForm">
                    @csrf
                    <input type="hidden" name="inventory_ids[]" value="{{ $inventory->id }}">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold"><i class="ti tabler-box me-1 text-primary"></i>New Asset Group</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex align-items-center gap-2 mb-3 p-2 border rounded bg-light">
                            <span class="badge bg-label-primary font-monospace"
                                style="font-size:0.7rem;">{{ $inventory->serial_number }}</span>
                            <span class="small text-muted">{{ $inventory->part_name }}
                                ({{ $inventory->unique_id }})</span>
                            <span class="ms-auto badge bg-label-success">Seed</span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Group Name
                                <span class="text-muted">(optional)</span></label>
                            <input type="text" name="name" class="form-control"
                                placeholder="e.g. Site replacement unit — chain">
                        </div>
                        <div>
                            <label class="form-label small fw-bold">Description
                                <span class="text-muted">(optional)</span></label>
                            <textarea name="description" rows="2" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="invCreateGroupSubmit">Create Group</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add to Existing Group Modal -->
    <div class="modal fade" id="invAddGroupModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="ti tabler-folder-plus me-1 text-primary"></i>Add to
                        Existing Group</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center gap-2 mb-3 p-2 border rounded bg-light">
                        <span class="badge bg-label-primary font-monospace"
                            style="font-size:0.7rem;">{{ $inventory->serial_number }}</span>
                        <span class="small text-muted">{{ $inventory->part_name }}</span>
                    </div>
                    <select class="form-select" id="invGroupSelect" style="width:100%;"></select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="invAddGroupSubmit">Add to Group</button>
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

    const INVENTORY_ID = {{ $inventory->id }};
    const CSRF = '{{ csrf_token() }}';
    const CREATE_GROUP_URL = '{{ route('inventory.asset-group.store') }}';
    const SEARCH_GROUPS_URL = '{{ route('inventory.asset-group.search.groups') }}';
    const ADD_ITEMS_URL = '{{ route('inventory.asset-group.add-items', ':groupId:') }}';

    function ajaxGroupResults(term) {
        const q = new URLSearchParams({ search: term });
        return fetch(SEARCH_GROUPS_URL + '?' + q.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => ({ results: data.results }));
    }

    // Create new group seeded with this asset
    $('#invCreateGroupForm').on('submit', function (e) {
        e.preventDefault();
        const btn = $('#invCreateGroupSubmit');
        btn.prop('disabled', true);

        fetch(CREATE_GROUP_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF },
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

    // Add this asset to an existing group
    $('#invGroupSelect').select2({
        dropdownParent: $('#invAddGroupModal'),
        placeholder: '-- Search group number / name --',
        allowClear: true,
        ajax: {
            url: SEARCH_GROUPS_URL,
            dataType: 'json',
            delay: 250,
            data: p => ({ search: p.term }),
            processResults: ajaxGroupResults
        }
    });

    $('#invAddGroupModal').on('hidden.bs.modal', function () {
        $('#invGroupSelect').val(null).trigger('change');
    });

    $('#invAddGroupSubmit').on('click', function () {
        const groupId = $('#invGroupSelect').val();
        if (!groupId) {
            Swal.fire({ title: 'No selection', text: 'Pick an asset group first.', icon: 'info' });
            return;
        }
        const btn = $(this);
        btn.prop('disabled', true);

        const body = new URLSearchParams();
        body.append('_token', CSRF);
        body.append('inventory_ids[]', INVENTORY_ID);

        fetch(ADD_ITEMS_URL.replace(':groupId:', groupId), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF },
            body
        })
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
</script>
@endsection
