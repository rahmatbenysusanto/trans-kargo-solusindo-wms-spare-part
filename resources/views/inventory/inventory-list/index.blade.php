@extends('layout.index')
@section('title', 'Inventory List')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <form action="{{ url()->current() }}" method="GET">
                        <div class="row">
                            <div class="col-2">
                                <label class="form-label">SKU</label>
                                <input type="text" class="form-control" name="sku" value="{{ request()->get('sku') }}" placeholder="SKU ...">
                            </div>
                        </div>
                    </form>
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
                                    <th>Status</th>
                                    <th>Storage</th>
                                    <th>Last Staging Date</th>
                                    <th>Last Movement Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
