@extends('layout.index')
@section('title', 'Receiving')

@section('content')
    <div class="row">
        <div class="d-flex justify-content-end mb-3">
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton"
                        data-bs-toggle="dropdown" aria-expanded="false">
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
                <div class="card-header">

                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>PO</th>
                                <th>Category</th>
                                <th>Data Receiving</th>
                                <th>RMA</th>
                                <th>ITSM</th>
                                <th>Vendor</th>
                                <th>Received By</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
