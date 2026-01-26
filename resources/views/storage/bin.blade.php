@extends('layout.index')
@section('title', 'Storage Bin')

@section('content')
    <div class="row">
        <div class="col-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-end">
                        <a class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addBin">Add
                            Bin</a>
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
                                    <th>Bin</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($storageBin as $bin)
                                    <tr>
                                        <td>{{ $loop->iteration + ($storageBin->currentPage() - 1) * $storageBin->perPage() }}
                                        </td>
                                        <td>{{ $bin->zone->name ?? '-' }}</td>
                                        <td>{{ $bin->rak->name ?? '-' }}</td>
                                        <td>{{ $bin->name }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-sm btn-secondary"
                                                    onclick="editBin({{ $bin->id }}, {{ $bin->storage_zone_id }}, {{ $bin->storage_rak_id }}, '{{ $bin->name }}')">Edit</button>
                                                <a href="{{ route('storage.bin.destroy', $bin->id) }}"
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
                        {{ $storageBin->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="addBin" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true"
        style="display: none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">Add Bin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('storage.bin.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Zone Name</label>
                            <select class="form-control" name="zone" onchange="changeZone(this.value, 'rak')" required>
                                <option value="">-- Choose Zone --</option>
                                @foreach ($storageZone as $zone)
                                    <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rak Name</label>
                            <select class="form-control" name="rak" id="rak" required>
                                <option value="">-- Choose Rak --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bin Name</label>
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

    <div id="editBin" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true"
        style="display: none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">Edit Bin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('storage.bin.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Zone Name</label>
                            <select class="form-control" name="zone" id="edit_zone"
                                onchange="changeZone(this.value, 'edit_rak')" required>
                                <option value="">-- Choose Zone --</option>
                                @foreach ($storageZone as $zone)
                                    <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rak Name</label>
                            <select class="form-control" name="rak" id="edit_rak" required>
                                <option value="">-- Choose Rak --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bin Name</label>
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
        function changeZone(zoneId, target) {
            $.ajax({
                url: '{{ route('storage.rak.find') }}',
                method: 'GET',
                data: {
                    zoneId: zoneId
                },
                success: (res) => {
                    const data = res.data;
                    let html = '<option value="">-- Choose Rak --</option>';

                    data.forEach((item) => {
                        html += `<option value="${item.id}">${item.name}</option>`;
                    });

                    document.getElementById(target).innerHTML = html;
                }
            });
        }

        function editBin(id, zoneId, rakId, name) {
            $('#edit_id').val(id);
            $('#edit_zone').val(zoneId);
            $('#edit_name').val(name);

            $.ajax({
                url: '{{ route('storage.rak.find') }}',
                method: 'GET',
                data: {
                    zoneId: zoneId
                },
                success: (res) => {
                    const data = res.data;
                    let html = '<option value="">-- Choose Rak --</option>';

                    data.forEach((item) => {
                        html +=
                            `<option value="${item.id}" ${item.id == rakId ? 'selected' : ''}>${item.name}</option>`;
                    });

                    $('#edit_rak').html(html);
                    $('#editBin').modal('show');
                }
            });
        }
    </script>
@endsection
