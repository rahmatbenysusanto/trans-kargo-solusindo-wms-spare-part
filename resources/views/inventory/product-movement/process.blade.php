@extends('layout.index')
@section('title', 'Product Movement')
@section('layout_class', 'layout-menu-collapsed')

@section('content')
    <div class="row g-4">
        <!-- Left Card: Available Products in Inventory -->
        <div class="col-md-7">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header border-bottom bg-transparent pt-4 pb-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="card-title mb-1">Available Inventory</h5>
                            <p class="card-subtitle text-muted mb-0">Select products to move</p>
                        </div>
                        <button class="btn btn-outline-primary btn-sm rounded-pill" onclick="moveAllRight()">
                            <i class="ti tabler-chevron-right me-1"></i> Move All
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="p-3 border-bottom bg-lighter">
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="ti tabler-search"></i></span>
                            <input type="text" class="form-control" id="searchProduct"
                                placeholder="Search by SN or Part Name..." onkeyup="filterProducts()">
                        </div>
                    </div>
                    <div class="table-responsive" style="height: calc(100vh - 350px); min-height: 400px; overflow-y: auto;">
                        <table class="table table-hover align-middle" id="availableTable">
                            <thead class="sticky-top bg-white shadow-sm z-index-1">
                                <tr>
                                    <th class="ps-4">Product Information</th>
                                    <th>Current Location</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0" id="availableTableBody">
                                <tr id="loading-row">
                                    <td colspan="3" class="text-center py-5">
                                        <div class="empty bg-transparent">
                                            <div class="empty-icon text-muted mb-3">
                                                <div class="spinner-border text-primary" role="span">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                            </div>
                                            <p class="empty-title h5 mb-1">Loading inventory...</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3 border-top d-flex justify-content-between align-items-center" id="paginationContainer">
                        <small class="text-muted" id="paginationInfo">Loading...</small>
                        <nav>
                            <ul class="pagination pagination-sm mb-0" id="paginationLinks"></ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Card: Selected for Movement -->
        <div class="col-md-5">
            <div class="card h-100 shadow-sm border-0 border-primary border-top border-3">
                <div class="card-header border-bottom bg-transparent pt-4 pb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1 text-primary">Items to Move</h5>
                            <p class="card-subtitle text-primary opacity-75 mb-0"><span id="selectedCount"
                                    class="fw-bold">0</span> items selected</p>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0 d-flex flex-column">
                    <div class="table-responsive flex-grow-1"
                        style="height: calc(100vh - 650px); min-height: 250px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0" id="selectedTable">
                            <thead class="sticky-top bg-white z-1 shadow-sm">
                                <tr>
                                    <th class="ps-4">Product Details</th>
                                    <th>Condition</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0" id="selectedTableBody">
                                <!-- Selected items will appear here -->
                                <tr id="empty-state-row">
                                    <td colspan="3" class="text-center py-5">
                                        <div class="empty bg-transparent">
                                            <div class="empty-icon text-muted mb-3">
                                                <div
                                                    class="avatar avatar-lg bg-label-secondary rounded-circle mx-auto d-flex align-items-center justify-content-center">
                                                    <i class="ti tabler-arrows-right-left fs-3 text-secondary"></i>
                                                </div>
                                            </div>
                                            <p class="empty-title h6 mb-1">No items selected</p>
                                            <p class="empty-subtitle text-muted small">Select items from the left panel to
                                                move</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-3 border-top bg-lighter d-flex justify-content-between align-items-center gap-2">
                        <small class="fw-bold text-muted">Bulk Update Condition:</small>
                        <select class="form-select form-select-sm w-auto" id="bulkCondition" onchange="bulkUpdateCondition(this.value)">
                            <option value="">-- Select Condition --</option>
                            <option value="Good">Good</option>
                            <option value="Faulty">Faulty</option>
                            <option value="New">New</option>
                            <option value="Refurbished">Refurbished</option>
                            <option value="Write-off Needed">Write-off Needed</option>
                        </select>
                    </div>

                    <div class="p-4 bg-white border-top mt-auto rounded-bottom">
                        <h6 class="fw-semibold mb-3 d-flex align-items-center text-primary">
                            <div
                                class="avatar avatar-xs bg-label-primary rounded me-2 d-flex align-items-center justify-content-center">
                                <i class="ti tabler-map-pin fs-6"></i>
                            </div>
                            Target Destination
                        </h6>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label fs-7 fw-medium text-muted text-uppercase mb-1">Zone</label>
                                <select class="form-select select2" id="zone_id" onchange="changeZone(this.value)">
                                    <option value="">-- Choose Zone --</option>
                                    @foreach ($storageZone as $zone)
                                        <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label fs-7 fw-medium text-muted text-uppercase mb-1">Rak</label>
                                <select class="form-select select2" id="rak_id" onchange="changeRak(this.value)">
                                    <option value="">-- Choose Rak --</option>
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label fs-7 fw-medium text-muted text-uppercase mb-1">Bin</label>
                                <select class="form-select select2" id="bin_id" onchange="changeBin(this.value)">
                                    <option value="">-- Choose Bin --</option>
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label fs-7 fw-medium text-muted text-uppercase mb-1">Level</label>
                                <select class="form-select select2" id="storage_level_id">
                                    <option value="">-- Choose Level --</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button class="btn btn-primary w-100 py-2 shadow-sm fs-6" onclick="submitMovement()"
                                id="submitBtn">
                                <i class="ti tabler-send me-2"></i> Execute Movement
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
        let currentPage = 1;
        let searchTimeout = null;

        function loadProducts(page = 1) {
            const search = document.getElementById('searchProduct').value;
            currentPage = page;

            document.getElementById('availableTableBody').innerHTML = `
                <tr id="loading-row">
                    <td colspan="3" class="text-center py-5">
                        <div class="empty bg-transparent">
                            <div class="empty-icon text-muted mb-3">
                                <div class="spinner-border text-primary" role="span">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <p class="empty-title h5 mb-1">Searching inventory...</p>
                        </div>
                    </td>
                </tr>`;

            fetch(`{{ route('inventory.product.movement.search') }}?search=${encodeURIComponent(search)}&page=${page}`)
                .then(res => {
                    if (!res.ok) throw new Error('Network error');
                    return res.json();
                })
                .then(data => {
                    renderProducts(data);
                })
                .catch(err => {
                    console.error('Search error:', err);
                    document.getElementById('availableTableBody').innerHTML = `
                        <tr>
                            <td colspan="3" class="text-center py-5">
                                <div class="empty bg-transparent">
                                    <div class="empty-icon text-muted mb-3">
                                        <i class="ti tabler-alert-circle fs-1 text-danger opacity-50"></i>
                                    </div>
                                    <p class="empty-title h5 mb-1">Failed to load data</p>
                                    <p class="empty-subtitle text-muted small">Check your connection and try again</p>
                                </div>
                            </td>
                        </tr>`;
                    document.getElementById('paginationInfo').textContent = 'Error loading data';
                    document.getElementById('paginationLinks').innerHTML = '';
                });
        }

        function renderProducts(data) {
            const tbody = document.getElementById('availableTableBody');

            if (data.data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="3" class="text-center py-5">
                            <div class="empty bg-transparent">
                                <div class="empty-icon text-muted mb-3">
                                    <i class="ti tabler-box fs-1 opacity-50"></i>
                                </div>
                                <p class="empty-title h5 mb-1">No inventory items found</p>
                                <p class="empty-subtitle text-muted small">Try adjusting your search criteria</p>
                            </div>
                        </td>
                    </tr>`;
            } else {
                let html = '';
                data.data.forEach(item => {
                    const isSelected = selectedProducts.includes(item.id);
                    html += `
                        <tr id="row-${item.id}" class="product-row${isSelected ? ' d-none' : ''}">
                            <td class="ps-4">
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold text-heading">${escapeHtml(item.part_name)}</span>
                                    <small class="text-muted">${escapeHtml(item.part_number)}</small>
                                    <small class="text-primary mt-1">${escapeHtml(item.unique_id)} | ${escapeHtml(item.client_name)}</small>
                                    <div class="mt-1">
                                        <span class="badge bg-label-secondary border"><i class="ti tabler-scan me-1"></i> ${escapeHtml(item.serial_number)}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    <span class="badge bg-label-info text-start d-inline-flex align-items-center" style="white-space: normal;">
                                        <i class="ti tabler-map-pin me-1 opacity-75"></i>
                                        ${escapeHtml(item.location)}
                                    </span>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-icon btn-primary rounded-circle shadow-sm"
                                    onclick="moveRight(${item.id}, '${escapeHtml(item.part_name)}', '${escapeHtml(item.serial_number)}', '${escapeHtml(item.unique_id)}', '${escapeHtml(item.client_name)}', '${escapeHtml(item.condition)}')"
                                    data-id="${item.id}"
                                    data-name="${escapeHtml(item.part_name)}"
                                    data-sn="${escapeHtml(item.serial_number)}"
                                    data-unique="${escapeHtml(item.unique_id)}"
                                    data-client="${escapeHtml(item.client_name)}"
                                    data-condition="${escapeHtml(item.condition)}"
                                    data-bs-toggle="tooltip" title="Move Item">
                                    <i class="ti tabler-chevron-right"></i>
                                </button>
                            </td>
                        </tr>`;
                });
                tbody.innerHTML = html;
            }

            renderPagination(data);
        }

        function renderPagination(data) {
            const info = document.getElementById('paginationInfo');
            const links = document.getElementById('paginationLinks');

            info.textContent = `Showing ${data.from || 0} to ${data.to || 0} of ${data.total} items`;

            if (data.last_page <= 1) {
                links.innerHTML = '';
                return;
            }

            let html = '';
            const current = data.current_page;
            const last = data.last_page;

            html += `<li class="page-item ${current <= 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="loadProducts(${current - 1}); return false;">&laquo;</a>
            </li>`;

            const start = Math.max(1, current - 2);
            const end = Math.min(last, current + 2);
            for (let i = start; i <= end; i++) {
                html += `<li class="page-item ${i === current ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="loadProducts(${i}); return false;">${i}</a>
                </li>`;
            }

            html += `<li class="page-item ${current >= last ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="loadProducts(${current + 1}); return false;">&raquo;</a>
            </li>`;

            links.innerHTML = html;
        }

        function escapeHtml(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.textContent = String(str);
            return div.innerHTML;
        }

        function filterProducts() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                loadProducts(1);
            }, 300);
        }

        function updateSelectedCount() {
            const count = selectedProducts.length;
            document.getElementById('selectedCount').innerText = count;

            const emptyState = document.getElementById('empty-state-row');
            if (count > 0 && emptyState) {
                emptyState.style.display = 'none';
            } else if (count === 0 && emptyState) {
                emptyState.style.display = '';
            }
        }

        function moveRight(id, name, sn, unique_id, client, condition) {
            if (selectedProducts.includes(id)) return;

            selectedProducts.push(id);
            document.getElementById(`row-${id}`).classList.add('d-none');

            const tbody = document.getElementById('selectedTableBody');
            const row = document.createElement('tr');
            row.id = `selected-row-${id}`;
            row.innerHTML = `
            <td class="ps-4">
                <div class="d-flex flex-column">
                    <span class="fw-semibold text-heading">${name}</span>
                    <small class="text-primary mt-1">${unique_id} | ${client}</small>
                    <div class="mt-1">
                        <span class="badge bg-label-info border"><i class="ti tabler-scan me-1"></i>${sn}</span>
                    </div>
                </div>
            </td>
            <td>
                <select class="form-select form-select-sm product-condition" data-id="${id}">
                    <option value="Good" ${condition === 'Good' ? 'selected' : ''}>Good</option>
                    <option value="Faulty" ${condition === 'Faulty' ? 'selected' : ''}>Faulty</option>
                    <option value="New" ${condition === 'New' ? 'selected' : ''}>New</option>
                    <option value="Refurbished" ${condition === 'Refurbished' ? 'selected' : ''}>Refurbished</option>
                    <option value="Write-off Needed" ${condition === 'Write-off Needed' ? 'selected' : ''}>Write-off Needed</option>
                </select>
            </td>
            <td class="text-end pe-4">
                <button class="btn btn-sm btn-icon btn-danger rounded-circle shadow-sm" onclick="moveLeft(${id})">
                    <i class="ti tabler-chevron-left"></i>
                </button>
            </td>
        `;
            tbody.appendChild(row);

            updateSelectedCount();
        }

        function moveLeft(id) {
            selectedProducts = selectedProducts.filter(item => item !== id);

            // Ensure to remove the d-none only if it still match search filter
            const input = document.getElementById('searchProduct');
            const filter = input.value.toUpperCase();
            const originalRow = document.getElementById(`row-${id}`);

            if (originalRow.innerText.toUpperCase().indexOf(filter) > -1 || filter === '') {
                originalRow.classList.remove('d-none');
            }

            document.getElementById(`selected-row-${id}`).remove();

            updateSelectedCount();
        }

        function moveAllRight() {
            // Only move currently visible rows
            const rows = document.querySelectorAll('#availableTable tbody tr.product-row:not(.d-none)');
            rows.forEach(row => {
                const btn = row.querySelector('button[title="Move Item"]');
                if (btn) {
                    moveRight(
                        parseInt(btn.getAttribute('data-id')),
                        btn.getAttribute('data-name'),
                        btn.getAttribute('data-sn'),
                        btn.getAttribute('data-unique'),
                        btn.getAttribute('data-client'),
                        btn.getAttribute('data-condition')
                    );
                }
            });
        }

        function bulkUpdateCondition(condition) {
            if (!condition) return;
            document.querySelectorAll('.product-condition').forEach(select => {
                select.value = condition;
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
                    $('#rak_id').html(html).trigger('change');
                    $('#bin_id').html('<option value="">-- Choose Bin --</option>').trigger('change');
                    $('#storage_level_id').html('<option value="">-- Choose Level --</option>').trigger(
                        'change');
                }
            });
        }

        function changeRak(rakId) {
            if (!rakId) return;
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
                    $('#storage_level_id').html('<option value="">-- Choose Level --</option>').trigger(
                        'change');
                }
            });
        }

        function changeBin(binId) {
            if (!binId) return;
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

        function submitMovement() {
            if (selectedProducts.length === 0) {
                Swal.fire({
                    title: 'No Products Selected',
                    text: 'Please select at least one product to move.',
                    icon: 'warning',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    }
                });
                return;
            }

            const storageLevelId = document.getElementById('storage_level_id').value;
            if (!storageLevelId) {
                Swal.fire({
                    title: 'Missing Destination',
                    text: 'Please select a New Storage Level to move the products to.',
                    icon: 'warning',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    }
                });
                return;
            }

            Swal.fire({
                title: 'Confirm Movement',
                text: `You are about to move ${selectedProducts.length} items to a new location. This action cannot be undone immediately.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3b5998',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, move them!',
                customClass: {
                    confirmButton: 'btn btn-primary me-2',
                    cancelButton: 'btn btn-label-secondary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Processing Movement',
                        html: 'Please wait while we transfer the items...',
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
                                conditions: Array.from(document.querySelectorAll('.product-condition')).reduce((acc, el) => {
                                    acc[el.dataset.id] = el.value;
                                    return acc;
                                }, {}),
                                storage_level_id: storageLevelId
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status) {
                                Swal.fire({
                                    title: 'Success!',
                                    text: 'Products have been moved successfully.',
                                    icon: 'success',
                                    customClass: {
                                        confirmButton: 'btn btn-success'
                                    }
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error',
                                    text: data.message || 'Failed to move products.',
                                    icon: 'error',
                                    customClass: {
                                        confirmButton: 'btn btn-primary'
                                    }
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                title: 'Error',
                                text: 'An unexpected error occurred.',
                                icon: 'error',
                                customClass: {
                                    confirmButton: 'btn btn-primary'
                                }
                            });
                        });
                }
            });
        }

        // Load initial data on page load
        document.addEventListener('DOMContentLoaded', function () {
            loadProducts(1);
        });
    </script>
@endsection
