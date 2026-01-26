@extends('layout.index')
@section('title', 'Put Away')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Put Away List</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>PO / Ref Number</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Note</th>
                                    <th>RMA</th>
                                    <th>ITSM</th>
                                    <th>Vendor</th>
                                    <th>Received By</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($inbound as $item)
                                    <tr>
                                        <td>{{ $loop->iteration + ($inbound->currentPage() - 1) * $inbound->perPage() }}
                                        </td>
                                        <td>{{ $item->number }}</td>
                                        <td><span class="badge bg-label-primary">{{ $item->category }}</span></td>
                                        <td>
                                            @php
                                                $statusClass = 'bg-label-secondary';
                                                if ($item->status == 'new') {
                                                    $statusClass = 'bg-label-info';
                                                } elseif ($item->status == 'process qc') {
                                                    $statusClass = 'bg-label-warning';
                                                } elseif ($item->status == 'cancel') {
                                                    $statusClass = 'bg-label-danger';
                                                } elseif ($item->status == 'close') {
                                                    $statusClass = 'bg-label-success';
                                                }
                                            @endphp
                                            <span class="badge {{ $statusClass }}">{{ strtoupper($item->status) }}</span>
                                        </td>
                                        <td>{{ $item->receiving_note ?? '-' }}</td>
                                        <td>{{ $item->rma_number ?? '-' }}</td>
                                        <td>{{ $item->itsm_number ?? '-' }}</td>
                                        <td>{{ $item->vendor }}</td>
                                        <td>{{ $item->received_by }}</td>
                                        <td>{{ $item->received_date }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                    data-bs-toggle="dropdown">
                                                    <i class="ti tabler-dots-vertical"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item"
                                                        href="{{ route('receiving.show', $item->id) }}">
                                                        <i class="ti tabler-eye me-1"></i> Detail
                                                    </a>
                                                    {{-- Put Away specific actions can be added here --}}
                                                    <a class="dropdown-item"
                                                        href="{{ route('receiving.put.away.process', $item->id) }}">
                                                        <i class="ti tabler-package me-1"></i> Process Put Away
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $inbound->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
