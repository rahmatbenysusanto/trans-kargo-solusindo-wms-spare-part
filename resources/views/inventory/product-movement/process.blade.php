@extends('layout.index')
@section('title', 'Product Movement')
@section('layout_class', 'layout-menu-collapsed')

@section('content')
    <div class="row">
        <!-- Left Card: Available Products in Inventory -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Available Inventory</h5>
                    <button class="btn btn-primary btn-sm" onclick="moveAllRight()">Move All <i
                            class="ti tabler-arrow-right ms-1"></i></button>
                </div>
                <div class="card-body p-0">
                    <div class="p-3">
                        <input type="text" class="form-control" id="searchProduct"
                            placeholder="Search by SN or Part Name..." onkeyup="filterProducts()">
                    </div>
                    <div class="table-responsive" style="max-height: 600px;">
                        <table class="table table-hover align-middle" id="availableTable">
                            <thead class="sticky-top bg-white">
                                <tr>
                                    <th>Product Information</th>
                                    <th>Location</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($inventory as $item)
                                    <tr id="row-{{ $item->id }}" class="product-row">
                                        <td>
                                            <div class="fw-bold">{{ $item->part_name }}</div>
                                            <small class="text-muted d-block">{{ $item->part_number }}</small>
                                            <span class="badge bg-label-secondary small mt-1">SN:
                                                {{ $item->serial_number }}</span>
                                        </td>
                                        <td>
                                            <small>{{ $item->storageLevel->bin->rak->zone->name ?? '-' }} /
                                                {{ $item->storageLevel->bin->rak->name ?? '-' }} /
                                                {{ $item->storageLevel->bin->name ?? '-' }} /
                                                {{ $item->storageLevel->name ?? '-' }}</small>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-icon btn-label-primary"
                                                onclick="moveRight({{ $item->id }}, '{{ addslashes($item->part_name) }}', '{{ $item->serial_number }}')">
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

        <!-- Right Card: Selected for Movement -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Products to Move</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive mb-4" style="max-height: 350px;">
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
                        <h6 class="fw-bold mb-3">Select New Storage Destination</h6>
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
                            <button class="btn btn-warning" onclick="submitMovement()">
                                <i class="ti tabler-arrows-transfer me-1"></i> Confirm Movement
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

        function filterProducts() {
            const input = document.getElementById('searchProduct');
            const filter = input.value.toUpperCase();
            const rows = document.querySelectorAll('.product-row');

            rows.forEach(row => {
                const text = row.innerText.toUpperCase();
                if (text.indexOf(filter) > -1) {
                    if (!selectedProducts.includes(parseInt(row.id.replace('row-', '')))) {
                        row.classList.remove('d-none');
                    }
                } else {
                    row.classList.add('d-none');
                }
            });
        }

        function moveRight(id, name, sn) {
            if (selectedProducts.includes(id)) return;

            selectedProducts.push(id);
            document.getElementById(`row-${id}`).classList.add('d-none');

            const tbody = document.querySelector('#selectedTable tbody');
            const row = `
                <tr id="selected-row-${id}">
                    <td>${name}</td>
                    <td><span class="badge bg-label-info">${sn}</span></td>
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
            filterProducts(); // Ensure search filter is still applied
        }

        function moveAllRight() {
            const rows = document.querySelectorAll('#availableTable tbody tr:not(.d-none)');
            rows.forEach(row => {
                const id = row.id.replace('row-', '');
                const infoCell = row.cells[0];
                const name = infoCell.querySelector('.fw-bold').innerText;
                const sn = infoCell.querySelector('.badge').innerText.replace('SN: ', '');
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

        function submitMovement() {
            if (selectedProducts.length === 0) {
                Swal.fire('Error', 'Please select at least one product to move.', 'error');
                return;
            }

            const storageLevelId = document.getElementById('storage_level_id').value;
            if (!storageLevelId) {
                Swal.fire('Error', 'Please select a New Storage Level.', 'error');
                return;
            }

            Swal.fire({
                title: 'Confirm Movement?',
                text: `You are about to move ${selectedProducts.length} items to a new location.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff9f43',
                confirmButtonText: 'Yes, move them!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Processing...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    });

                    fetch('{{ route('inventory.product.movement.update') }}', {
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
                                Swal.fire('Success!', 'Products have been moved successfully.', 'success').then(
                                    () => {
                                        location.reload();
                                    });
                            } else {
                                Swal.fire('Error', data.message || 'Failed to move products.', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('Error', 'An unexpected error occurred.', 'error');
                        });
                }
            });
        }
    </script>
@endsection
