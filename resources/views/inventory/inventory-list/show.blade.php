@extends('layout.index')
@section('title', 'Inventory Detail')

@section('content')
    <div class="row">
        <!-- Info Card -->
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Product Information</h5>
                    <a href="{{ route('inventory.index') }}" class="btn btn-secondary btn-sm">
                        <i class="ti tabler-arrow-left me-1"></i> Back to List
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold text-muted small uppercase">Asset# / Unique ID</label>
                            <p class="mb-0 fw-bold text-primary">{{ $inventory->unique_id }}</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold text-muted small uppercase">Part Name</label>
                            <p class="mb-0 font-medium">{{ $inventory->part_name }}</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold text-muted small uppercase">Brand</label>
                            <p class="mb-0 font-medium">
                                {{ $inventory->product && $inventory->product->brand ? $inventory->product->brand->name : '-' }}
                            </p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold text-muted small uppercase">Brand Group</label>
                            <p class="mb-0 font-medium">
                                {{ $inventory->product && $inventory->product->productGroup ? $inventory->product->productGroup->name : '-' }}
                            </p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold text-muted small uppercase">Part Number / SKU</label>
                            <p class="mb-0 fw-medium text-dark">{{ $inventory->part_number }}</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold text-muted small uppercase">Serial Number (SN)</label>
                            <p class="mb-0"><span class="badge bg-label-info">{{ $inventory->serial_number }}</span></p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold text-muted small uppercase">Current Status</label>
                            <p class="mb-0">
                                @if ($inventory->status == 'In Stock' || $inventory->status == 'Available')
                                    <span class="badge bg-success">{{ $inventory->status }}</span>
                                @elseif(in_array($inventory->status, ['Defective', 'Broken', 'Faulty']))
                                    <span class="badge bg-danger">{{ $inventory->status }}</span>
                                @else
                                    <span class="badge bg-warning">{{ $inventory->status }}</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold text-muted small uppercase">Warehouse Location</label>
                            <p class="mb-0 text-primary fw-bold">
                                @if ($inventory->storageLevel)
                                    {{ $inventory->storageLevel->bin->rak->zone->name }} -
                                    {{ $inventory->storageLevel->name }}
                                @else
                                    <span class="text-muted">Not Set</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold text-muted small uppercase">Client Owner</label>
                            <p class="mb-0">{{ $inventory->client->name ?? '-' }}</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold text-muted small uppercase">Quantity</label>
                            <p class="mb-0 h5 text-primary">{{ $inventory->qty }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline / Lifecycle History -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 border-bottom">
                    <h5 class="card-title mb-0">Item Lifecycle History (Tracking SN)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 180px;">Date & Time</th>
                                    <th>Activity Type</th>
                                    <th>Category</th>
                                    <th>Ref / Doc#</th>
                                    <th>Description / Transaction Detail</th>
                                    <th>Handled By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($history as $h)
                                    <tr>
                                        <td class="small text-muted">
                                            {{ \Carbon\Carbon::parse($h['date'])->format('d M Y') }}<br>
                                            <span
                                                class="fw-bold text-dark">{{ \Carbon\Carbon::parse($h['date'])->format('H:i') }}</span>
                                        </td>
                                        <td>
                                            @if ($h['type'] == 'Inbound')
                                                <span class="badge bg-label-success text-success"><i
                                                        class="ti tabler-download me-1"></i> INBOUND</span>
                                            @elseif($h['type'] == 'Outbound')
                                                <span class="badge bg-label-danger text-danger"><i
                                                        class="ti tabler-upload me-1"></i> OUTBOUND</span>
                                            @else
                                                <span class="badge bg-label-primary text-primary"><i
                                                        class="ti tabler-arrows-diff me-1"></i> MOVEMENT</span>
                                            @endif
                                        </td>
                                        <td><span class="badge bg-label-secondary">{{ $h['category'] }}</span></td>
                                        <td><span class="fw-medium text-dark">{{ $h['reference'] }}</span></td>
                                        <td>
                                            <p class="mb-0 small">{{ $h['description'] }}</p>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-xs me-2">
                                                    <span
                                                        class="avatar-initial rounded-circle bg-label-secondary">{{ substr($h['user'] ?? 'S', 0, 1) }}</span>
                                                </div>
                                                <span class="small">{{ $h['user'] ?? 'System' }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <i class="ti tabler-history text-muted d-block mb-2"
                                                style="font-size: 3rem;"></i>
                                            <p class="text-muted">No history recorded for this item.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
