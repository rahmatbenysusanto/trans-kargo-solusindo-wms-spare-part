@extends('layout.pdf')
@section('title', 'Product Summary Report')

@section('content')
    <div class="row" id="print-area">
        <div class="col-12 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                <a href="{{ route('inventory.product.summary') }}" class="btn btn-secondary">
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
                            <h2 class="text-primary fw-bold mb-1">PRODUCT SUMMARY REPORT</h2>
                            <p class="text-muted mb-0">Total Products: <strong>{{ count($data) }}</strong></p>
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
                                    <th>Part Number</th>
                                    <th class="text-center">Total Received</th>
                                    <th class="text-center">In Inventory</th>
                                    <th class="text-center">Total Outbound</th>
                                </tr>
                            </thead>
                            <tbody class="border-dark">
                                @foreach ($data as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->part_number }}</td>
                                        <td class="text-center">{{ $item->total_in }}</td>
                                        <td class="text-center">{{ $item->in_inventory }}</td>
                                        <td class="text-center">{{ $item->total_out }}</td>
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
            @page { size: A4 portrait; margin: 15mm; }
            .no-print { display: none !important; }
        }
    </style>
@endsection
