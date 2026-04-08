@extends('layout.index')
@section('title', 'Stock Movement')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Stock Movement History</h5>
                    <div class="btn-group">
                        <a href="{{ route('inventory.stock.movement.excel') }}" class="btn btn-success">
                            <i class="ti tabler-file-spreadsheet me-1"></i> Excel
                        </a>
                        <a href="{{ route('inventory.stock.movement.pdf') }}" class="btn btn-primary" target="_blank">
                            <i class="ti tabler-file-description me-1"></i> PDF
                        </a>
                    </div>
                </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product Information</th>
                                    <th>Type</th>
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
                                            <span
                                                class="badge {{ $item->type == 'Movement' ? 'bg-label-warning' : 'bg-label-primary' }}">
                                                {{ $item->type }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($item->fromStorageLevel)
                                                <small>{{ $item->fromStorageLevel->bin->rak->zone->name }}-{{ $item->fromStorageLevel->bin->rak->name }}-{{ $item->fromStorageLevel->bin->name }}-{{ $item->fromStorageLevel->name }}</small>
                                            @else
                                                <span class="badge bg-label-secondary">Initial / Staging</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ $item->toStorageLevel->bin->rak->zone->name }}-{{ $item->toStorageLevel->bin->rak->name }}-{{ $item->toStorageLevel->bin->name }}-{{ $item->toStorageLevel->name }}</small>
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
