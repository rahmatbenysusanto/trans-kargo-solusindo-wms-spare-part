@extends('layout.index')
@section('title', 'Stock Statement Report')

@section('content')
    <div class="row" id="print-area">
        <div class="col-12 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                <a href="{{ route('inventory.stock.statement') }}" class="btn btn-secondary">
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
                            <h2 class="text-primary fw-bold mb-1">STOCK STATEMENT REPORT</h2>
                            <p class="text-muted mb-0">Total Records: <strong>{{ count($data) }}</strong></p>
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
                                    <th>Serial Number</th>
                                    <th>Part Name</th>
                                    <th>Inbound Ref</th>
                                    <th>Receive Date</th>
                                    <th>Status</th>
                                    <th>Outbound Ref</th>
                                    <th>Outbound Date</th>
                                </tr>
                            </thead>
                            <tbody class="border-dark">
                                @foreach ($data as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->serial_number }}</td>
                                        <td>{{ $item->part_name }}</td>
                                        <td>{{ $item->inbound->number ?? '-' }}</td>
                                        <td>{{ $item->inbound->received_date ?? '-' }}</td>
                                        <td>
                                            @if($item->is_in_stock)
                                                In Inventory
                                            @elseif($item->is_outbound)
                                                Outbound
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $item->outbound_detail->outbound->number ?? '-' }}</td>
                                        <td>{{ $item->outbound_detail->outbound->outbound_date ?? '-' }}</td>
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
            .printable-table th, .printable-table td { font-size: 9px; padding: 4px !important; }
        }
    </style>
@endsection
