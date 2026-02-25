@extends('layout.index')
@section('title', 'Outbound')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-end mb-3">
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        Create Outbound
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <a class="dropdown-item" href="{{ route('outbound.create.spare') }}">Spare</a>
                        <a class="dropdown-item" href="{{ route('outbound.create.faulty') }}">Faulty</a>
                        <a class="dropdown-item" href="{{ route('outbound.create.rma') }}">RMA</a>
                        <a class="dropdown-item" href="{{ route('outbound.create.write-off') }}">Write-off</a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">

                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Category</th>
                                    <th>PO#</th>
                                    <th>NTT DN#</th>
                                    <th>TKS DN#</th>
                                    <th>TKS Invoice#</th>
                                    <th>RMA#</th>
                                    <th>ITSM#</th>
                                    <th>Qty</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->category }}</td>
                                        <td>{{ $item->number ?? '-' }}</td>
                                        <td>{{ $item->ntt_dn_number ?? '-' }}</td>
                                        <td>{{ $item->tks_dn_number ?? '-' }}</td>
                                        <td>{{ $item->tks_invoice_number ?? '-' }}</td>
                                        <td>{{ $item->rma_number ?? '-' }}</td>
                                        <td>{{ $item->itsm_number ?? '-' }}</td>
                                        <td>{{ $item->qty }}</td>
                                        <td><span class="badge bg-secondary">{{ $item->status }}</span></td>
                                        <td>
                                            <a href="{{ route('outbound.show', $item->id) }}" class="btn btn-info btn-sm"
                                                title="View Detail">
                                                <i class="ti tabler-eye"></i>
                                            </a>
                                            <a href="{{ route('outbound.print', $item->id) }}" target="_blank"
                                                class="btn btn-primary btn-sm" title="Print PDF">
                                                <i class="ti tabler-printer"></i>
                                            </a>
                                        </td>
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
