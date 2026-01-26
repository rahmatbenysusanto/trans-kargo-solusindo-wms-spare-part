@extends('layout.index')
@section('title', 'Process Put Away')
@section('layout_class', 'layout-menu-collapsed')

@section('content')
    <div class="row">
        <div class="col-12 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">PO Number</label>
                            <p class="form-control-plaintext">{{ $inbound->number }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Vendor</label>
                            <p class="form-control-plaintext">{{ $inbound->vendor }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Category</label>
                            <p class="form-control-plaintext">{{ $inbound->category }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Received Date</label>
                            <p class="form-control-plaintext">{{ $inbound->received_date }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Left Card: Available Products -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Available Products</h5>
                    <button class="btn btn-primary btn-sm" onclick="moveAllRight()">Move All <i
                            class="ti tabler-arrow-right ms-1"></i></button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 500px;">
                        <table class="table table-hover align-middle" id="availableTable">
                            <thead class="sticky-top bg-white">
                                <tr>
                                    <th>Part Name</th>
                                    <th>SN</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($inbound->details as $detail)
                                    <tr id="row-{{ $detail->id }}">
                                        <td>
                                            <div class="fw-bold">{{ $detail->part_name }}</div>
                                            <small class="text-muted">{{ $detail->part_number }}</small>
                                        </td>
                                        <td>{{ $detail->serial_number }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-icon btn-label-primary"
                                                onclick="moveRight({{ $detail->id }}, '{{ addslashes($detail->part_name) }}', '{{ $detail->serial_number }}')">
                                                <i class="ti tabler-arrow-right"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Card: Selected for PA -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Products to Put Away</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive mb-4" style="max-height: 300px;">
                        <table class="table table-hover align-middle" id="selectedTable">
                            <thead>
                                <tr>
                                    <th>Part Name</th>
                                    <th>SN</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Selected items will appear here -->
                            </tbody>
                        </table>
                    </div>

                    <div class="border-top pt-4">
                        <h6 class="fw-bold mb-3">Select Storage Destination</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Zone</label>
                                <select class="form-select select2" id="zone_id" onchange="changeZone(this.value)">
                                    <option value="">-- Choose Zone --</option>
                                    @foreach ($storageZone as $zone)
                                        <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Rak</label>
                                <select class="form-select select2" id="rak_id" onchange="changeRak(this.value)">
                                    <option value="">-- Choose Rak --</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Bin</label>
                                <select class="form-select select2" id="bin_id" onchange="changeBin(this.value)">
                                    <option value="">-- Choose Bin --</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Level</label>
                                <select class="form-select select2" id="storage_level_id">
                                    <option value="">-- Choose Level --</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-grid mt-3">
                            <button class="btn btn-success" onclick="submitPutAway()">
                                <i class="ti tabler-check me-1"></i> Confirm Put Away
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        let selectedProducts = [];

        function moveRight(id, name, sn) {
            if (selectedProducts.includes(id)) return;

            selectedProducts.push(id);
            document.getElementById(`row-${id}`).classList.add('d-none');

            const tbody = document.querySelector('#selectedTable tbody');
            const row = `
                <tr id="selected-row-${id}">
                    <td>${name}</td>
                    <td>${sn}</td>
                    <td>
                        <button class="btn btn-sm btn-icon btn-label-danger" onclick="moveLeft(${id})">
                            <i class="ti tabler-arrow-left"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.innerHTML += row;
        }

        function moveLeft(id) {
            selectedProducts = selectedProducts.filter(item => item !== id);
            document.getElementById(`row-${id}`).classList.remove('d-none');
            document.getElementById(`selected-row-${id}`).remove();
        }

        function moveAllRight() {
            const rows = document.querySelectorAll('#availableTable tbody tr:not(.d-none)');
            rows.forEach(row => {
                const id = row.id.replace('row-', '');
                const name = row.cells[0].innerText.split('\n')[0];
                const sn = row.cells[1].innerText;
                moveRight(parseInt(id), name, sn);
            });
        }

        function changeZone(zoneId) {
            $.ajax({
                url: '{{ route('storage.rak.find') }}',
                method: 'GET',
                data: {
                    zoneId: zoneId
                },
                success: (res) => {
                    let html = '<option value="">-- Choose Rak --</option>';
                    res.data.forEach((item) => {
                        html += `<option value="${item.id}">${item.name}</option>`;
                    });
                    $('#rak_id').html(html);
                    $('#bin_id').html('<option value="">-- Choose Bin --</option>');
                    $('#storage_level_id').html('<option value="">-- Choose Level --</option>');
                }
            });
        }

        function changeRak(rakId) {
            $.ajax({
                url: '{{ route('storage.bin.find') }}',
                method: 'GET',
                data: {
                    rakId: rakId
                },
                success: (res) => {
                    let html = '<option value="">-- Choose Bin --</option>';
                    res.data.forEach((item) => {
                        html += `<option value="${item.id}">${item.name}</option>`;
                    });
                    $('#bin_id').html(html);
                    $('#storage_level_id').html('<option value="">-- Choose Level --</option>');
                }
            });
        }

        function changeBin(binId) {
            $.ajax({
                url: '{{ route('storage.level.find') }}',
                method: 'GET',
                data: {
                    binId: binId
                },
                success: (res) => {
                    let html = '<option value="">-- Choose Level --</option>';
                    res.data.forEach((item) => {
                        html += `<option value="${item.id}">${item.name}</option>`;
                    });
                    $('#storage_level_id').html(html);
                }
            });
        }

        function submitPutAway() {
            if (selectedProducts.length === 0) {
                Swal.fire('Error', 'Please select at least one product to Put Away.', 'error');
                return;
            }

            const storageLevelId = document.getElementById('storage_level_id').value;
            if (!storageLevelId) {
                Swal.fire('Error', 'Please select a Storage Level.', 'error');
                return;
            }

            Swal.fire({
                title: 'Confirm Put Away?',
                text: `You are about to Put Away ${selectedProducts.length} items.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, confirm!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.showLoading();
                    fetch('{{ route('receiving.put.away.update') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                products: selectedProducts,
                                storage_level_id: storageLevelId
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status) {
                                Swal.fire('Success!', 'Products have been Put Away.', 'success').then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error', data.message || 'Failed to Put Away products.', 'error');
                            }
                        });
                }
            });
        }
    </script>
@endsection
