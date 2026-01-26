@extends('layout.index')
@section('title', 'Stock Movement')

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
                                    <th>SKU</th>
                                    <th>Part Name</th>
                                    <th>Part Number</th>
                                    <th>Serial Number</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Date</th>
                                    <th>Process By</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
