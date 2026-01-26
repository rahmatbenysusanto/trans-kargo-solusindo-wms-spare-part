@extends('layout.index')
@section('title', 'Receiving')

@section('content')
    <div class="row">
        <div class="d-flex justify-content-end mb-3">
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    Create Receiving
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <a class="dropdown-item" href="{{ route('receiving.create.spare') }}">Spare</a>
                    <a class="dropdown-item" href="{{ route('receiving.create.faulty') }}">Faulty</a>
                    <a class="dropdown-item" href="{{ route('receiving.create.rma') }}">RMA</a>
                    <a class="dropdown-item" href="{{ route('receiving.create.new.po') }}">New PO</a>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Receiving List</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>PO / Ref Number</th>
                                    <th>Category</th>
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
                                                    <a class="dropdown-item" href="javascript:void(0);"><i
                                                            class="ti tabler-eye me-1"></i> View</a>
                                                    <a class="dropdown-item" href="javascript:void(0);"><i
                                                            class="ti tabler-trash me-1"></i> Delete</a>
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
