@extends('layout.index')
@section('title', 'User Management')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0">User List</h5>
                    <div class="d-flex align-items-center gap-2">
                        <form action="{{ route('user.index') }}" method="GET" class="d-flex gap-2">
                            <input type="text" name="search" class="form-control form-control-sm"
                                placeholder="Search user..." value="{{ $search ?? '' }}">
                            <button type="submit" class="btn btn-primary btn-sm text-white">Search</button>
                        </form>
                        <a href="{{ route('menu.index') }}" class="btn btn-info btn-sm text-white">
                            <i class="bi bi-list-task me-1"></i> Manage Menus
                        </a>
                        <button class="btn btn-primary btn-sm text-white" data-bs-toggle="modal"
                            data-bs-target="#addUserModal">
                            <i class="bi bi-plus-circle me-1"></i> Add User
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Assigned Clients</th>
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
                                        <td>
                                            <span class="badge {{ $user->role == 'Admin WMS' ? 'bg-primary' : 'bg-info' }}">
                                                {{ $user->role }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($user->role == 'Admin WMS')
                                                <span class="text-muted small">All Clients Access</span>
                                            @else
                                                @forelse($user->clients as $client)
                                                    <span class="badge bg-secondary mb-1">{{ $client->name }}</span>
                                                @empty
                                                    <span class="text-danger small">No Clients Assigned</span>
                                                @endforelse
                                            @endif
                                        </td>
                                        <td>
                                            @if ($user->status == 'active')
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-info btn-sm text-white"
                                                    onclick="manageMenu('{{ $user->id }}', '{{ $user->name }}')">
                                                    <i class="bi bi-shield-lock"></i> Menu
                                                </button>
                                                <button class="btn btn-secondary btn-sm text-white"
                                                    onclick="editUser('{{ $user->id }}', '{{ addslashes($user->name) }}', '{{ $user->username }}', '{{ $user->email }}', '{{ $user->no_hp }}', '{{ $user->status }}', '{{ $user->role }}', {{ json_encode($user->clients->pluck('id')) }})">
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
                            <label class="form-label">Role</label>
                            <select class="form-select" name="role"
                                onchange="toggleClientFields(this, '#addClientFields')">
                                <option value="Admin WMS">Admin WMS (Full Access)</option>
                                <option value="Client User">Client User (Restricted)</option>
                            </select>
                        </div>
                        <div class="mb-3 d-none" id="addClientFields">
                            <label class="form-label">Assign Clients</label>
                            <select class="form-select select2" name="client_ids[]" multiple style="width: 100%;">
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>
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
                            <input type="password" class="form-control" name="password" placeholder="Password ..."
                                required>
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
                            <label class="form-label">Role</label>
                            <select class="form-select" name="role" id="editRole"
                                onchange="toggleClientFields(this, '#editClientFields')">
                                <option value="Admin WMS">Admin WMS (Full Access)</option>
                                <option value="Client User">Client User (Restricted)</option>
                            </select>
                        </div>
                        <div class="mb-3 d-none" id="editClientFields">
                            <label class="form-label">Assign Clients</label>
                            <select class="form-select select2" name="client_ids[]" id="editClientIds" multiple
                                style="width: 100%;">
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>
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

    <!-- User Menu Modal -->
    <div class="modal fade" id="userMenuModal" tabindex="-1" aria-labelledby="userMenuModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userMenuModalLabel">Manage User Menu Access: <span
                            id="menuUserName"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="menuListContainer">
                        <!-- Menus will be loaded here via JS -->
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            $('.select2').each(function() {
                $(this).select2({
                    dropdownParent: $(this).closest('.modal-content')
                });
            });
        });

        function toggleClientFields(select, targetId) {
            if (select.value === 'Client User') {
                $(targetId).removeClass('d-none');
            } else {
                $(targetId).addClass('d-none');
            }
        }

        function editUser(id, name, username, email, phone, status, role, clientIds) {
            $('#editId').val(id);
            $('#editName').val(name);
            $('#editUsername').val(username);
            $('#editEmail').val(email);
            $('#editPhone').val(phone === 'null' ? '' : phone);
            $('#editStatus').val(status);
            $('#editRole').val(role).trigger('change');

            // Set multiple clients if it's Client User
            if (role === 'Client User') {
                $('#editClientIds').val(clientIds).trigger('change');
            } else {
                $('#editClientIds').val([]).trigger('change');
            }

            $('#editUserModal').modal('show');
        }

        function manageMenu(userId, userName) {
            $('#menuUserName').text(userName);
            $('#menuListContainer').html(
                '<div class="text-center"><div class="spinner-border text-primary" role="status"></div></div>');
            $('#userMenuModal').modal('show');

            $.get('{{ url('user/menu/user') }}/' + userId, function(data) {
                let html = '<ul class="list-group">';
                data.menus.forEach(function(menu) {
                    html += `
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            ${menu.name}
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" 
                                    ${menu.has_access ? 'checked' : ''} 
                                    onchange="toggleMenu(${userId}, ${menu.id}, this)">
                            </div>
                        </li>
                    `;
                });
                html += '</ul>';
                if (data.menus.length === 0) {
                    html = '<div class="alert alert-warning">No menus found. Please add menus first.</div>';
                }
                $('#menuListContainer').html(html);
            });
        }

        function toggleMenu(userId, menuId, checkbox) {
            $.post('{{ route('menu.user.toggle') }}', {
                _token: '{{ csrf_token() }}',
                user_id: userId,
                menu_id: menuId
            }, function(response) {
                if (response.success) {
                    // Success feedback if needed
                } else {
                    alert('Error updating menu access');
                    checkbox.checked = !checkbox.checked;
                }
            }).fail(function() {
                alert('Error updating menu access');
                checkbox.checked = !checkbox.checked;
            });
        }
    </script>
@endsection
