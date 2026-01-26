@extends('layout.index')
@section('title', 'Storage Rak')

@section('content')
    <div class="row">
        <div class="col-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-end">
                        <a class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addRak">Add
                            Rak</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Zone</th>
                                    <th>Rak</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($storageRak as $rak)
                                    <tr>
                                        <td>{{ $loop->iteration + ($storageRak->currentPage() - 1) * $storageRak->perPage() }}
                                        </td>
                                        <td>{{ $rak->zone->name ?? '-' }}</td>
                                        <td>{{ $rak->name }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-sm btn-secondary"
                                                    onclick="editRak({{ $rak->id }}, {{ $rak->storage_zone_id }}, '{{ $rak->name }}')">Edit</button>
                                                <a href="{{ route('storage.rak.destroy', $rak->id) }}"
                                                    class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Are you sure?')">Delete</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $storageRak->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="addRak" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true"
        style="display: none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">Add Rak</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('storage.rak.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Zone Name</label>
                            <select class="form-control" name="zone" required>
                                <option value="">-- Choose Zone --</option>
                                @foreach ($storageZone as $zone)
                                    <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rak Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Create</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="editRak" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true"
        style="display: none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">Edit Rak</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('storage.rak.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Zone Name</label>
                            <select class="form-control" name="zone" id="edit_zone" required>
                                <option value="">-- Choose Zone --</option>
                                @foreach ($storageZone as $zone)
                                    <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rak Name</label>
                            <input type="text" class="form-control" name="name" id="edit_name" required>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        function editRak(id, zoneId, name) {
            $('#edit_id').val(id);
            $('#edit_zone').val(zoneId);
            $('#edit_name').val(name);
            $('#editRak').modal('show');
        }
    </script>
@endsection
