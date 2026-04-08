@extends('layout.index')
@section('title', 'Stock Movement Report')

@section('content')
    <div class="row" id="print-area">
        <div class="col-12 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                <a href="{{ route('inventory.stock.movement') }}" class="btn btn-secondary">
                    <i class="ti tabler-arrow-left me-1"></i> Back
                </a>
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="ti tabler-printer me-1"></i> Print Report
                </button>
            </div>

            <div class="card shadow-none">
                <div class="card-body p-5">
                    <div class="row border-bottom pb-4 mb-4 align-items-center">
                        <div class="col-sm-6">
                            <h2 class="text-primary fw-bold mb-1">STOCK MOVEMENT REPORT</h2>
                            <p class="text-muted mb-0">Total Movements: <strong>{{ count($movements) }}</strong></p>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <h4 class="mb-1 fw-bold text-dark">TRANS KARGO SOLUSINDO</h4>
                            <p class="text-muted mb-0 small">Generated: {{ date('F d, Y h:i A') }}</p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered border-dark printable-table">
                            <thead class="table-light border-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Item Details</th>
                                    <th>From Location</th>
                                    <th>To Location</th>
                                    <th>User</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody class="border-dark">
                                @foreach ($movements as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <div class="fw-bold">{{ $item->inventory->part_name ?? '-' }}</div>
                                            <small class="text-muted">SN: {{ $item->inventory->serial_number ?? '-' }}</small>
                                        </td>
                                        <td>{{ $item->fromStorageLevel ? "{$item->fromStorageLevel->bin->rak->zone->name}-{$item->fromStorageLevel->bin->rak->name}-{$item->fromStorageLevel->bin->name}-{$item->fromStorageLevel->name}" : '-' }}</td>
                                        <td>{{ $item->toStorageLevel ? "{$item->toStorageLevel->bin->rak->zone->name}-{$item->toStorageLevel->bin->rak->name}-{$item->toStorageLevel->bin->name}-{$item->toStorageLevel->name}" : '-' }}</td>
                                        <td>{{ $item->user->name ?? '-' }}</td>
                                        <td><span class="badge bg-label-info">{{ $item->type }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {
            @page { size: A4 landscape; margin: 10mm; }
            .no-print { display: none !important; }
            .printable-table th, .printable-table td { font-size: 10px; padding: 4px !important; }
        }
    </style>
@endsection
