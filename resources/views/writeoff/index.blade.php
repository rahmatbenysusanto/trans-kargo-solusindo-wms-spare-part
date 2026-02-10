@extends('layout.index')
@section('title', 'Write-off')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Write-off List</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Client</th>
                                    <th>Ref#</th>
                                    <th>NTT DN#</th>
                                    <th>TKS DN#</th>
                                    <th>Qty</th>
                                    <th>Outbound Date</th>
                                    <th>Outbound By</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->client->name ?? '-' }}</td>
                                        <td>{{ $item->number ?? '-' }}</td>
                                        <td>{{ $item->ntt_dn_number ?? '-' }}</td>
                                        <td>{{ $item->tks_dn_number ?? '-' }}</td>
                                        <td>{{ $item->qty }}</td>
                                        <td>{{ $item->outbound_date }}</td>
                                        <td>{{ $item->outbound_by }}</td>
                                        <td>
                                            <a href="#" class="btn btn-info btn-sm">View</a>
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
