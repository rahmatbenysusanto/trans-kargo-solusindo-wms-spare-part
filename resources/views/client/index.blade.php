@extends('layout.index')
@section('title', 'Client')

@section('content')
    <div class="row">
        <div class="col-6">
            <div class="card">
                <div class="card-header d-flex justify-content-end">
                    <a class="btn btn-primary text-white btn-sm" data-bs-toggle="modal" data-bs-target="#addClientModal">Add
                        Client</a>
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
                                @foreach ($client as $index => $item)
                                    <tr>
                                        <td>{{ $client->firstItem() + $index }}</td>
                                        <td>{{ $item->name }}</td>
                                        <td>
                                            <a class="btn btn-secondary btn-sm text-white"
                                                onclick="editClient('{{ $item->id }}', '{{ $item->name }}')">Edit</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $client->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Client Modal -->
    <div id="addClientModal" class="modal fade" tabindex="-1" aria-labelledby="addClientModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addClientModalLabel">Add Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('client.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" placeholder="Name ..." required>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary text-white">Add Client</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Client Modal -->
    <div id="editClientModal" class="modal fade" tabindex="-1" aria-labelledby="editClientModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editClientModalLabel">Edit Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('client.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" id="editId">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" id="editName" placeholder="Name ..."
                                required>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary text-white">Update Client</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        function editClient(id, name) {
            $('#editId').val(id);
            $('#editName').val(name);
            $('#editClientModal').modal('show');
        }
    </script>
@endsection
