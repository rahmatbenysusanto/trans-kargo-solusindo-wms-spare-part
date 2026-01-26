@extends('layout.index')
@section('title', 'Brand')

@section('content')
    <div class="row">
        <div class="col-6">
            <div class="card">
                <div class="card-header d-flex justify-content-end">
                    <a class="btn btn-primary text-white btn-sm" data-bs-toggle="modal" data-bs-target="#exampleModal">Add
                        Brand</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($brand as $index => $item)
                                    <tr>
                                        <td>{{ $brand->firstItem() + $index }}</td>
                                        <td>{{ $item->name }}</td>
                                        <td>
                                            <a class="btn btn-secondary btn-sm text-white"
                                                onclick="editBrand('{{ $item->id }}', '{{ $item->name }}')">Edit</a>
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

    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Add Brand</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('brand.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" placeholder="Brand Name ..." required>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary text-white">Add Brand</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="editModalLabel">Edit Brand</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('brand.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" id="editId">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" id="editName"
                                placeholder="Brand Name ..." required>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary text-white">Update Brand</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        function editBrand(id, name) {
            $('#editId').val(id);
            $('#editName').val(name);
            $('#editModal').modal('show');
        }
    </script>
@endsection
