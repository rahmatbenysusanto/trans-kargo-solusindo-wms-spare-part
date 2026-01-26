@extends('layout.index')
@section('title', 'Detail Receiving')

@section('content')
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">General Information</h5>
                    <a href="{{ route('receiving') }}" class="btn btn-secondary btn-sm">
                        <i class="ti tabler-arrow-left me-1"></i> Back to List
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">PO / Ref Number</label>
                            <p class="form-control-plaintext">{{ $inbound->number }}</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Category</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-label-primary">{{ $inbound->category }}</span>
                            </p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Vendor</label>
                            <p class="form-control-plaintext">{{ $inbound->vendor }}</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Received Date</label>
                            <p class="form-control-plaintext">{{ $inbound->received_date }}</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Received By</label>
                            <p class="form-control-plaintext">{{ $inbound->received_by }}</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Total Qty</label>
                            <p class="form-control-plaintext">{{ $inbound->qty }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Receiving Note</label>
                            <p class="form-control-plaintext">{{ $inbound->receiving_note ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Product Details</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Part Name</th>
                                    <th>Part Number</th>
                                    <th>Serial Number</th>
                                    <th>Condition</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($inbound->details as $detail)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $detail->part_name }}</td>
                                        <td>{{ $detail->part_number }}</td>
                                        <td>{{ $detail->serial_number }}</td>
                                        <td>
                                            @php
                                                $badgeClass = 'bg-label-info';
                                                if ($detail->condition == 'New') {
                                                    $badgeClass = 'bg-label-success';
                                                } elseif ($detail->condition == 'Broken') {
                                                    $badgeClass = 'bg-label-danger';
                                                }
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">{{ $detail->condition }}</span>
                                        </td>
                                        <td>{{ $detail->description ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
