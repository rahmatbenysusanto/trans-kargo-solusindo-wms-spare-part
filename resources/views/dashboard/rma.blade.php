@extends('layout.index')
@section('title', 'RMA Monitoring')

@section('content')
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">RMA Monitoring (SN Swap Tracking)</h5>
                    <p class="text-muted small mb-0">Comparison of original serial numbers with replacement units.</p>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>#</th>
                                    <th>Product Name</th>
                                    <th>Part Number</th>
                                    <th>Original SN (Old)</th>
                                    <th>Replacement SN (New)</th>
                                    <th>Condition</th>
                                    <th>Date Swapped</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $item)
                                    <tr>
                                        <td>{{ ($data->currentPage() - 1) * $data->perPage() + $loop->iteration }}</td>
                                        <td>{{ $item->part_name }}</td>
                                        <td>{{ $item->part_number }}</td>
                                        <td><span class="badge bg-secondary">{{ $item->old_serial_number }}</span></td>
                                        <td><span class="badge bg-primary">{{ $item->serial_number }}</span></td>
                                        <td>{{ $item->condition }}</td>
                                        <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @endforeach
                                @if ($data->isEmpty())
                                    <tr>
                                        <td colspan="7" class="text-center">No RMA swaps recorded yet.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $data->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
