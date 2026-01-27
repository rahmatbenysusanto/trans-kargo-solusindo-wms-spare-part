@extends('layout.index')
@section('title', 'Menu Management')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Menu List</h5>
                    <button class="btn btn-primary btn-sm text-white" data-bs-toggle="modal" data-bs-target="#addMenuModal">
                        <i class="bi bi-plus-circle me-1"></i> Add Menu
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Menu Name</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($menus as $index => $menu)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $menu->name }}</td>
                                        <td>{{ $menu->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-secondary btn-sm text-white"
                                                    onclick="editMenu('{{ $menu->id }}', '{{ $menu->name }}')">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>
                                                <a href="{{ route('menu.destroy', $menu->id) }}"
                                                    class="btn btn-danger btn-sm text-white"
                                                    onclick="return confirm('Are you sure you want to delete this menu?')">
                                                    <i class="bi bi-trash"></i> Delete
                                                </a>
                                            </div>
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

    <!-- Add Menu Modal -->
    <div class="modal fade" id="addMenuModal" tabindex="-1" aria-labelledby="addMenuModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addMenuModalLabel">Add Menu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('menu.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Menu Name</label>
                            <input type="text" class="form-control" name="name" placeholder="Menu Name ..." required>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary text-white">Save Menu</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Menu Modal -->
    <div class="modal fade" id="editMenuModal" tabindex="-1" aria-labelledby="editMenuModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editMenuModalLabel">Edit Menu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('menu.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" id="editId">
                        <div class="mb-3">
                            <label class="form-label">Menu Name</label>
                            <input type="text" class="form-control" name="name" id="editName"
                                placeholder="Menu Name ..." required>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary text-white">Update Menu</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        function editMenu(id, name) {
            $('#editId').val(id);
            $('#editName').val(name);
            $('#editMenuModal').modal('show');
        }
    </script>
@endsection
