@extends('layout.index')
@section('title', 'RMA')

@section('content')
    <div class="row">
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
                                    <th>Original Asset SN</th>
                                    <th>Replacement Serial Number</th>
                                    <th>Part Number</th>
                                    <th>Part Name</th>
                                    <th>RMA</th>
                                    <th>ITSM</th>
                                    <th>RMA Date</th>
                                    <th>Processed By</th>
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
