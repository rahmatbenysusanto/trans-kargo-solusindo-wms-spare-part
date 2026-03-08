@extends('layout.index')
@section('title', 'Product Movement History')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header border-bottom bg-transparent pt-4 pb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">Product Movement History</h5>
                            <p class="card-subtitle text-muted mb-0">Track and monitor all inventory item movements</p>
                        </div>
                        <a href="{{ route('inventory.product.movement.process') }}"
                            class="btn btn-primary shadow-sm rounded-pill">
                            <i class="ti tabler-arrows-transfer me-1"></i> New Movement
                        </a>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive h-100">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Product Information</th>
                                    <th>From Location</th>
                                    <th>To Location</th>
                                    <th>Process Date</th>
                                    <th class="pe-4">Processed By</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @forelse ($movements as $index => $item)
                                    <tr>
                                        <td class="ps-4 text-muted">{{ $movements->firstItem() + $index }}</td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span
                                                    class="fw-semibold text-heading">{{ $item->inventory->part_name ?? '-' }}</span>
                                                <small class="text-muted mt-1">
                                                    <i
                                                        class="ti tabler-barcode me-1"></i>{{ $item->inventory->serial_number ?? '-' }}
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($item->fromStorageLevel)
                                                <div class="d-flex align-items-center">
                                                    <span
                                                        class="badge bg-label-secondary py-2 border d-inline-flex align-items-center gap-1">
                                                        <i class="ti tabler-map-pin"></i>
                                                        {{ $item->fromStorageLevel->bin->rak->zone->name ?? '-' }} /
                                                        {{ $item->fromStorageLevel->bin->rak->name ?? '-' }} /
                                                        {{ $item->fromStorageLevel->bin->name ?? '-' }} /
                                                        {{ $item->fromStorageLevel->name ?? '-' }}
                                                    </span>
                                                </div>
                                            @else
                                                <span
                                                    class="badge bg-label-warning py-2 border border-warning d-inline-flex align-items-center gap-1">
                                                    <i class="ti tabler-box"></i> Initial / Staging
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="ti tabler-arrow-right text-primary me-3 opacity-50"></i>
                                                <span
                                                    class="badge bg-label-primary py-2 border border-primary d-inline-flex align-items-center gap-1">
                                                    <i class="ti tabler-map-pin"></i>
                                                    {{ $item->toStorageLevel->bin->rak->zone->name ?? '-' }} /
                                                    {{ $item->toStorageLevel->bin->rak->name ?? '-' }} /
                                                    {{ $item->toStorageLevel->bin->name ?? '-' }} /
                                                    {{ $item->toStorageLevel->name ?? '-' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-medium">{{ $item->created_at->format('d M Y') }}</span>
                                                <small class="text-muted">{{ $item->created_at->format('H:i') }}</small>
                                            </div>
                                        </td>
                                        <td class="pe-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2">
                                                    <span
                                                        class="avatar-initial rounded-circle bg-label-info">{{ substr($item->user->name ?? 'U', 0, 1) }}</span>
                                                </div>
                                                <span class="fw-medium text-heading">{{ $item->user->name ?? '-' }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="empty bg-transparent">
                                                <div class="empty-icon text-muted mb-3">
                                                    <i class="ti tabler-folder-off fs-1 opacity-50"></i>
                                                </div>
                                                <p class="empty-title h5 mb-1">No movement history found</p>
                                                <p class="empty-subtitle text-muted">There are no inventory movements
                                                    recorded yet.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($movements->hasPages())
                    <div class="card-footer border-top bg-transparent py-3">
                        <div class="m-0">
                            {{ $movements->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
