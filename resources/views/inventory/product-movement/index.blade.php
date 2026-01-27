@extends('layout.index')
@section('title', 'Product Movement History')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Product Movement History</h5>
                    <a href="{{ route('inventory.product.movement.process') }}" class="btn btn-primary btn-sm text-white">
                        <i class="ti tabler-arrows-transfer me-1"></i> Movement Product
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product Information</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Date</th>
                                    <th>Process By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($movements as $index => $item)
                                    <tr>
                                        <td>{{ $movements->firstItem() + $index }}</td>
                                        <td>
                                            <div class="fw-bold">{{ $item->inventory->part_name }}</div>
                                            <small class="text-muted">SN: {{ $item->inventory->serial_number }}</small>
                                        </td>
                                        <td>
                                            @if ($item->fromStorageLevel)
                                                <small>{{ $item->fromStorageLevel->bin->rak->zone->name }} /
                                                    {{ $item->fromStorageLevel->bin->rak->name }} /
                                                    {{ $item->fromStorageLevel->bin->name }} /
                                                    {{ $item->fromStorageLevel->name }}</small>
                                            @else
                                                <span class="badge bg-label-secondary">Initial / Staging</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ $item->toStorageLevel->bin->rak->zone->name }} /
                                                {{ $item->toStorageLevel->bin->rak->name }} /
                                                {{ $item->toStorageLevel->bin->name }} /
                                                {{ $item->toStorageLevel->name }}</small>
                                        </td>
                                        <td>{{ $item->created_at->format('d M Y H:i') }}</td>
                                        <td>{{ $item->user->name }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $movements->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
