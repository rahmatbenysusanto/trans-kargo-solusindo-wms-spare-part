@extends('layout.index')
@section('title', 'Process Put Away')
@section('layout_class', 'layout-menu-collapsed')

@section('css')
    <style>
        .table-compact td,
        .table-compact th {
            padding: 0.45rem 0.6rem !important;
            font-size: 0.82rem;
        }

        .header-label {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #a19fad;
            letter-spacing: 1px;
            margin-bottom: 0.2rem;
            display: block;
        }

        .header-value {
            font-size: 0.95rem;
            font-weight: 600;
            color: #444050;
            margin-bottom: 0;
        }

        .sticky-card-header {
            position: sticky;
            top: 0;
            z-index: 10;
            background: white;
            border-bottom: 1px solid #dbdade !important;
        }

        .item-list-container {
            max-height: 550px;
            overflow-y: auto;
        }

        .move-btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
        }

        .selected-count-badge {
            background: #7367f0;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .empty-placeholder {
            text-align: center;
            padding: 2rem;
            color: #a19fad;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <!-- Inbound Record Info -->
        <div class="col-12 mb-4">
            <div class="card shadow-sm border-0 border-start border-primary border-5">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-md-3 border-end">
                            <span class="header-label">Transaction Number</span>
                            <h5 class="header-value text-primary fw-bold">{{ $inbound->number }}</h5>
                        </div>
                        <div class="col-md-3 border-end ps-3">
                            <span class="header-label">Partner / Vendor</span>
                            <h5 class="header-value small text-dark"><i
                                    class="ti tabler-truck me-2"></i>{{ $inbound->vendor }}</h5>
                        </div>
                        <div class="col-md-2 border-end ps-3">
                            <span class="header-label">Category / Request</span>
                            <span class="badge bg-label-info mt-1">{{ $inbound->category }}</span>
                            <div class="small text-muted mt-1" style="font-size: 0.7rem; font-weight: 600;">
                                {{ $inbound->request_type ?: '-' }}</div>
                        </div>
                        <div class="col-md-2 border-end ps-3">
                            <span class="header-label">Received Date</span>
                            <h5 class="header-value small fw-medium">
                                {{ \Carbon\Carbon::parse($inbound->received_date)->format('d M Y') }}</h5>
                        </div>
                        <div class="col-md-2 text-end">
                            <a href="{{ route('receiving.put.away') }}" class="btn btn-sm btn-label-secondary">
                                <i class="ti tabler-arrow-left me-1"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Left Column: Source Items -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100 overflow-hidden">
                <div class="card-header sticky-card-header py-3 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0 fw-bold small text-uppercase text-muted">Items Ready for Shelving
                            </h5>
                        </div>
                        <button class="btn btn-sm btn-label-primary fw-bold" onclick="moveAllRight()">
                            MOVE ALL <i class="ti tabler-arrow-narrow-right ms-1"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="p-3 border-bottom bg-light">
                        <div class="input-group input-group-merge input-group-sm">
                            <span class="input-group-text"><i class="ti tabler-search text-muted"></i></span>
                            <input type="text" id="globalSearch" class="form-control"
                                placeholder="Search Serial Number or Part Name..." onkeyup="debouncedFilter()">
                            <span class="input-group-text d-none" id="loading-spinner">
                                <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                            </span>
                        </div>
                    </div>
                    <div class="item-list-container">
                        <table class="table table-hover table-striped align-middle table-compact mb-0" id="availableTable">
                            <thead class="bg-label-secondary sticky-top">
                                <tr>
                                    <th>Part / Specifications</th>
                                    <th>Serial Number</th>
                                    <th width="50">PA</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($inbound->details as $detail)
                                    <tr id="row-{{ $detail->id }}">
                                        <td>
                                            <div class="fw-bold text-dark">{{ $detail->part_name }}</div>
                                            <div class="text-muted" style="font-size: 0.72rem;">{{ $detail->part_number }}
                                            </div>
                                        </td>
                                        <td class="font-monospace fw-medium small">{{ $detail->serial_number }}</td>
                                        <td>
                                            <button class="btn btn-label-primary move-btn"
                                                onclick="moveRight({{ $detail->id }}, '{{ addslashes($detail->part_name) }}', '{{ $detail->serial_number }}')">
                                                <i class="ti tabler-plus"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-muted">All items have been put away.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Destination Selective Selection -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 mb-4 overflow-hidden">
                <div class="card-header border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold small text-uppercase text-muted">Shelving Preparation List</h5>
                    <span id="selectedCountBadge" class="selected-count-badge">0 Items Selected</span>
                </div>
                <div class="card-body p-0">
                    <div class="item-list-container" style="max-height: 250px;">
                        <table class="table table-hover align-middle table-compact mb-0" id="selectedTable">
                            <thead class="bg-label-warning sticky-top">
                                <tr>
                                    <th>Part Name</th>
                                    <th>Serial Number</th>
                                    <th width="50" class="text-center">Remove</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr id="emptyPlaceholder">
                                    <td colspan="3" class="empty-placeholder">
                                        <i class="ti tabler-package-import d-block mb-1 fs-3"></i>
                                        Select items from the left to start shelving process
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Destination Selection Form -->
                    <div class="p-4 bg-light border-top">
                        <h6 class="fw-bold mb-3 d-flex align-items-center"><i
                                class="ti tabler-map-pin me-2 text-primary"></i>Assign Target Storage Level</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="header-label">Zone</label>
                                <select class="form-select select2" id="zone_id" data-placeholder="Choose Zone"
                                    onchange="changeZone(this.value)">
                                    <option value="">-- Choose Zone --</option>
                                    @foreach ($storageZone as $zone)
                                        <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="header-label">Rak</label>
                                <select class="form-select select2" id="rak_id" data-placeholder="Choose Rak"
                                    onchange="changeRak(this.value)">
                                    <option value="">-- Choose Rak --</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="header-label">Bin</label>
                                <select class="form-select select2" id="bin_id" data-placeholder="Choose Bin"
                                    onchange="changeBin(this.value)">
                                    <option value="">-- Choose Bin --</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="header-label">Level / Position</label>
                                <select class="form-select select2" id="storage_level_id"
                                    data-placeholder="Choose Level">
                                    <option value="">-- Choose Level --</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4 pt-2">
                            <button class="btn btn-primary w-100 fw-bold py-2 shadow-sm" id="submitBtn"
                                onclick="submitPutAway()">
                                <i class="ti tabler-check me-2"></i> CONFIRM PUT AWAY INTO SHELF
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
        let filterTimeout = null;

        $(document).ready(function() {
            $('.select2').each(function() {
                $(this).select2({
                    placeholder: $(this).data('placeholder'),
                    width: '100%',
                    dropdownParent: $(this).parent()
                });
            });
        });

        function debouncedFilter() {
            document.getElementById('loading-spinner').classList.remove('d-none');
            if (filterTimeout) clearTimeout(filterTimeout);
            filterTimeout = setTimeout(() => {
                filterTable();
                document.getElementById('loading-spinner').classList.add('d-none');
            }, 300);
        }

        function filterTable() {
            const searchValue = document.getElementById('globalSearch').value.toLowerCase().trim();
            const rows = document.querySelectorAll('#availableTable tbody tr');

            rows.forEach(row => {
                const id = parseInt(row.id.replace('row-', ''));
                if (selectedProducts.includes(id)) {
                    row.classList.add('d-none');
                    return;
                }

                if (!searchValue) {
                    row.classList.remove('d-none');
                    return;
                }

                const rowContent = row.innerText.toLowerCase();
                if (rowContent.includes(searchValue)) {
                    row.classList.remove('d-none');
                } else {
                    row.classList.add('d-none');
                }
            });
        }

        function moveRight(id, name, sn) {
            if (selectedProducts.includes(id)) return;

            selectedProducts.push(id);
            document.getElementById(`row-${id}`).classList.add('d-none');
            document.getElementById('emptyPlaceholder').classList.add('d-none');

            const tbody = document.querySelector('#selectedTable tbody');
            const row = `
                <tr id="selected-row-${id}">
                    <td class="small fw-medium text-dark">${name}</td>
                    <td class="font-monospace small font-bold">${sn}</td>
                    <td class="text-center">
                        <button class="btn btn-xs btn-label-danger move-btn mx-auto" onclick="moveLeft(${id})">
                            <i class="ti tabler-circle-x fs-6"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.innerHTML += row;
            updateUI();
        }

        function moveLeft(id) {
            selectedProducts = selectedProducts.filter(item => item !== id);
            document.getElementById(`selected-row-${id}`).remove();

            if (selectedProducts.length === 0) {
                document.getElementById('emptyPlaceholder').classList.remove('d-none');
            }

            document.getElementById(`row-${id}`).classList.remove('d-none');
            updateUI();
        }

        function moveAllRight() {
            const rows = document.querySelectorAll('#availableTable tbody tr:not(.d-none)');
            rows.forEach(row => {
                const idText = row.id.replace('row-', '');
                if (idText) {
                    const id = parseInt(idText);
                    const name = row.cells[0].querySelector('.fw-bold').innerText;
                    const sn = row.cells[1].innerText;
                    moveRight(id, name, sn);
                }
            });
        }

        function updateUI() {
            const badge = document.getElementById('selectedCountBadge');
            badge.innerText = `${selectedProducts.length} Items Selected`;

            const submitBtn = document.getElementById('submitBtn');
            if (selectedProducts.length > 0) {
                submitBtn.classList.remove('disabled');
            } else {
                submitBtn.classList.add('disabled');
            }
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
                    $('#rak_id').html(html).trigger('change');
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
                    $('#bin_id').html(html).trigger('change');
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
                    $('#storage_level_id').html(html).trigger('change');
                }
            });
        }

        function submitPutAway() {
            if (selectedProducts.length === 0) {
                Swal.fire('Error', 'Please select at least one product.', 'error');
                return;
            }

            const storageLevelId = document.getElementById('storage_level_id').value;
            if (!storageLevelId) {
                Swal.fire('Error', 'Please select a Target Storage Level.', 'error');
                return;
            }

            Swal.fire({
                title: 'Confirm Operation',
                text: `Process put away for ${selectedProducts.length} items to the selected shelving?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, start shelving!',
                confirmButtonColor: '#7367f0'
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
                                Swal.fire('Success!', 'Items successfully moved to storage.', 'success').then(
                                    () => {
                                        window.location.reload();
                                    });
                            } else {
                                Swal.fire('Error', data.message || 'Processing failed.', 'error');
                            }
                        });
                }
            });
        }

        updateUI();
    </script>
@endsection
