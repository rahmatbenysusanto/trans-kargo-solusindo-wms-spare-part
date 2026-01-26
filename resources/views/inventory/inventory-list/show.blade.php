@extends('layout.index')
@section('title', 'Inventory Detail')

@section('content')
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Inventory Information</h5>
                    <a href="{{ route('inventory.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Back to List
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Unique ID / SKU</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-primary">{{ $inventory->unique_id }}</span>
                            </p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Part Name</label>
                            <p class="form-control-plaintext">{{ $inventory->part_name }}</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Part Number</label>
                            <p class="form-control-plaintext">{{ $inventory->part_number }}</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Serial Number</label>
                            <p class="form-control-plaintext">{{ $inventory->serial_number }}</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-{{ $inventory->status == 'In Stock' ? 'success' : 'secondary' }}">
                                    {{ $inventory->status }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Quantity</label>
                            <p class="form-control-plaintext cursor-default">{{ $inventory->qty }}</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Storage Location</label>
                            <p class="form-control-plaintext">
                                @if ($inventory->storageLevel)
                                    {{ $inventory->storageLevel->bin->rak->zone->name }} -
                                    {{ $inventory->storageLevel->bin->rak->name }} -
                                    {{ $inventory->storageLevel->bin->name }} -
                                    {{ $inventory->storageLevel->name }}
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Client</label>
                            <p class="form-control-plaintext">{{ $inventory->client->name ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">History / Movement</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Transaction Type</th>
                                    <th>Reference Number</th>
                                    <th>Date</th>
                                    <th>Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($inventory->details as $detail)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            @if ($detail->inboundDetail)
                                                <span class="badge bg-label-info text-info">Inbound
                                                    ({{ $detail->inboundDetail->inbound->category }})</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if ($detail->inboundDetail)
                                                {{ $detail->inboundDetail->inbound->number }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $detail->created_at->format('d/m/Y H:i') }}</td>
                                        <td>1</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No movement history found</td>
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
