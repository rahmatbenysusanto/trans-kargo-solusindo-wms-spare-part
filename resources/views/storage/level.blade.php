@extends('layout.index')
@section('title', 'Storage Level')

@section('content')
    <div class="row">
        <div class="col-10">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-end">
                        <a class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addLevel">Add
                            Level</a>
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
                                    <th>Level</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($storageLevel as $level)
                                    <tr>
                                        <td>{{ $loop->iteration + ($storageLevel->currentPage() - 1) * $storageLevel->perPage() }}
                                        </td>
                                        <td>{{ $level->zone->name ?? '-' }}</td>
                                        <td>{{ $level->rak->name ?? '-' }}</td>
                                        <td>{{ $level->bin->name ?? '-' }}</td>
                                        <td>{{ $level->name }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-sm btn-secondary"
                                                    onclick="editLevel({{ $level->id }}, {{ $level->storage_zone_id }}, {{ $level->storage_rak_id }}, {{ $level->storage_bin_id }}, '{{ $level->name }}')">Edit</button>
                                                <a href="{{ route('storage.level.destroy', $level->id) }}"
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
                        {{ $storageLevel->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="addLevel" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true"
        style="display: none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">Add Level</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('storage.level.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Zone Name</label>
                            <select class="form-control" name="zone" onchange="changeZone(this.value, 'rak', 'bin')"
                                required>
                                <option value="">-- Choose Zone --</option>
                                @foreach ($storageZone as $zone)
                                    <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rak Name</label>
                            <select class="form-control" name="rak" id="rak"
                                onchange="changeRak(this.value, 'bin')" required>
                                <option value="">-- Choose Rak --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bin Name</label>
                            <select class="form-control" name="bin" id="bin" required>
                                <option value="">-- Choose Bin --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Level Name</label>
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

    <div id="editLevel" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true"
        style="display: none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">Edit Level</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('storage.level.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Zone Name</label>
                            <select class="form-control" name="zone" id="edit_zone"
                                onchange="changeZone(this.value, 'edit_rak', 'edit_bin')" required>
                                <option value="">-- Choose Zone --</option>
                                @foreach ($storageZone as $zone)
                                    <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rak Name</label>
                            <select class="form-control" name="rak" id="edit_rak"
                                onchange="changeRak(this.value, 'edit_bin')" required>
                                <option value="">-- Choose Rak --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bin Name</label>
                            <select class="form-control" name="bin" id="edit_bin" required>
                                <option value="">-- Choose Bin --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Level Name</label>
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
        function changeZone(zoneId, targetRak, targetBin) {
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

                    document.getElementById(targetRak).innerHTML = html;
                    if (targetBin) {
                        document.getElementById(targetBin).innerHTML =
                            '<option value="">-- Choose Bin --</option>';
                    }
                }
            });
        }

        function changeRak(rakId, targetBin) {
            $.ajax({
                url: '{{ route('storage.bin.find') }}',
                method: 'GET',
                data: {
                    rakId: rakId
                },
                success: (res) => {
                    const data = res.data;
                    let html = '<option value="">-- Choose Bin --</option>';

                    data.forEach((item) => {
                        html += `<option value="${item.id}">${item.name}</option>`;
                    });

                    document.getElementById(targetBin).innerHTML = html;
                }
            });
        }

        function editLevel(id, zoneId, rakId, binId, name) {
            $('#edit_id').val(id);
            $('#edit_zone').val(zoneId);
            $('#edit_name').val(name);

            // Fetch Raks
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

                    // Fetch Bins
                    $.ajax({
                        url: '{{ route('storage.bin.find') }}',
                        method: 'GET',
                        data: {
                            rakId: rakId
                        },
                        success: (resBin) => {
                            const dataBin = resBin.data;
                            let htmlBin = '<option value="">-- Choose Bin --</option>';
                            dataBin.forEach((item) => {
                                htmlBin +=
                                    `<option value="${item.id}" ${item.id == binId ? 'selected' : ''}>${item.name}</option>`;
                            });
                            $('#edit_bin').html(htmlBin);
                            $('#editLevel').modal('show');
                        }
                    });
                }
            });
        }
    </script>
@endsection
