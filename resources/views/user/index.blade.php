@extends('layout.index')
@section('title', 'User Management')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">User List</h5>
                    <button class="btn btn-primary btn-sm text-white" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="bi bi-plus-circle me-1"></i> Add User
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $index => $user)
                                    <tr>
                                        <td>{{ $users->firstItem() + $index }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->username }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->no_hp ?? '-' }}</td>
                                        <td>
                                            @if ($user->status == 'active')
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-secondary btn-sm text-white"
                                                    onclick="editUser('{{ $user->id }}', '{{ $user->name }}', '{{ $user->username }}', '{{ $user->email }}', '{{ $user->no_hp }}', '{{ $user->status }}')">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>
                                                <a href="{{ route('user.destroy', $user->id) }}"
                                                    class="btn btn-danger btn-sm text-white"
                                                    onclick="return confirm('Are you sure you want to delete this user?')">
                                                    <i class="bi bi-trash"></i> Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('user.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" placeholder="Full Name ..." required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" placeholder="Username ..." required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" placeholder="Email Address ..."
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number (Optional)</label>
                            <input type="text" class="form-control" name="no_hp" placeholder="Phone Number ...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" placeholder="Password ..." required>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary text-white">Save User</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('user.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" id="editId">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" id="editName"
                                placeholder="Full Name ..." required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" id="editUsername"
                                placeholder="Username ..." required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="editEmail"
                                placeholder="Email Address ..." required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number (Optional)</label>
                            <input type="text" class="form-control" name="no_hp" id="editPhone"
                                placeholder="Phone Number ...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status" id="editStatus">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password (Leave blank to keep current)</label>
                            <input type="password" class="form-control" name="password" placeholder="New Password ...">
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary text-white">Update User</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        function editUser(id, name, username, email, phone, status) {
            $('#editId').val(id);
            $('#editName').val(name);
            $('#editUsername').val(username);
            $('#editEmail').val(email);
            $('#editPhone').val(phone === 'null' ? '' : phone);
            $('#editStatus').val(status);
            $('#editUserModal').modal('show');
        }
    </script>
@endsection
