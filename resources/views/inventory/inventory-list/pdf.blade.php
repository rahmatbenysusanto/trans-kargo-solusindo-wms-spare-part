@extends('layout.index')
@section('title', 'Print Inventory List')

@section('content')
    <div class="row" id="print-area">
        <div class="col-12 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                <a href="{{ route('inventory.index') }}" class="btn btn-secondary">
                    <i class="ti tabler-arrow-left me-1"></i> Back to List
                </a>
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="ti tabler-printer me-1"></i> Print Report
                </button>
            </div>

            <div class="card shadow-none">
                <div class="card-body p-5">
                    <!-- Report Header -->
                    <div class="row border-bottom pb-4 mb-4 align-items-center">
                        <div class="col-sm-6">
                            <h2 class="text-primary fw-bold mb-1">INVENTORY LIST REPORT</h2>
                            <p class="text-muted mb-0">Total Items: <strong>{{ count($inventory) }}</strong></p>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <h4 class="mb-1 fw-bold text-dark">TRANS KARGO SOLUSINDO</h4>
                            <p class="text-muted mb-0 small">
                                Warehouse Management System<br>
                                Generated: {{ date('F d, Y h:i A') }}
                            </p>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered border-dark printable-table">
                            <thead class="table-light border-dark">
                                <tr>
                                    <th class="text-center">#</th>
                                    <th>Asset ID</th>
                                    <th>Product Details</th>
                                    <th>Serial Number</th>
                                    <th>Zone</th>
                                    <th>Rack</th>
                                    <th>Bin</th>
                                    <th>Level</th>
                                    <th>Status</th>
                                    <th class="text-center">Condition</th>
                                </tr>
                            </thead>
                            <tbody class="border-dark">
                                @forelse ($inventory as $item)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td><strong>{{ $item->unique_id }}</strong></td>
                                        <td>
                                            <div class="fw-bold">{{ $item->part_name }}</div>
                                            <small class="text-muted">P/N: {{ $item->part_number }}</small>
                                        </td>
                                        <td>{{ $item->serial_number }}</td>
                                        <td>{{ $item->storageLevel ? $item->storageLevel->bin->rak->zone->name : '-' }}</td>
                                        <td>{{ $item->storageLevel ? $item->storageLevel->bin->rak->name : '-' }}</td>
                                        <td>{{ $item->storageLevel ? $item->storageLevel->bin->name : '-' }}</td>
                                        <td>{{ $item->storageLevel ? $item->storageLevel->name : '-' }}</td>
                                        <td>{{ $item->status }}</td>
                                        <td class="text-center">{{ $item->condition }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted">No inventory items found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="row pt-5">
                        <div class="col-sm-6"></div>
                        <div class="col-sm-6 text-center mt-4">
                            <p class="mb-5 text-muted">Warehouse Manager</p>
                            <p class="fw-bold text-dark border-top border-dark d-inline-block pt-1" style="width: 200px;">
                                ( ____________________ )
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {
            @page {
                size: A4 landscape;
                margin: 10mm;
            }

            body {
                background: white !important;
            }

            body * {
                visibility: hidden;
            }

            #print-area,
            #print-area * {
                visibility: visible;
            }

            #print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            .no-print,
            .navbar,
            .menu,
            .layout-navbar,
            .layout-menu,
            footer {
                display: none !important;
            }

            .printable-table th,
            .printable-table td {
                padding: 6px !important;
                font-size: 11px;
                border-color: #000 !important;
            }

            .text-primary {
                color: #000 !important;
            }
        }
    </style>
@endsection
