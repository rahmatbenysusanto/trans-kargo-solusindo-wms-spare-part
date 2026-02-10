<!-- Mass Upload Modal -->
<div class="modal fade" id="massUploadProductModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content overflow-hidden border-0 shadow-lg" style="border-radius: 12px;">
            <div class="modal-header bg-label-secondary border-0 py-3">
                <h5 class="modal-title fw-bold text-dark"><i
                        class="ti tabler-file-upload me-2 text-secondary fs-4"></i>Mass Upload Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-4 text-center border rounded-3 p-4 bg-light-subtle dashed-border">
                    <i class="ti tabler-upload fs-1 text-muted mb-3 d-block"></i>
                    <p class="mb-2 fw-medium">Upload Excel Template</p>
                    <small class="text-muted d-block mb-3">Please use the official template for best results.</small>
                    <input type="file" class="form-control bg-white" id="excelFile" accept=".xlsx, .xls">
                </div>
                <div class="d-grid">
                    <button type="button" class="btn btn-primary waves-effect waves-light shadow-sm"
                        onclick="uploadProduct()">
                        <i class="ti tabler-check me-1"></i> Start Import
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .dashed-border {
        border-style: dashed !important;
        border-width: 2px !important;
    }
</style>

<!-- Select Inventory Modal -->
<div class="modal fade" id="selectInventoryModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px; overflow: hidden;">
            <div class="modal-header bg-label-primary border-bottom py-3 px-4">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-sm me-3 shadow-sm">
                        <span class="avatar-initial rounded-circle bg-primary"><i class="ti tabler-search"></i></span>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-primary mb-0">Browse Inventory</h5>
                        <small class="text-muted text-uppercase fw-medium ls-1 fs-tiny">Select available units from
                            stock</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0 bg-light-subtle">
                <div class="p-4 bg-white border-bottom shadow-xs">
                    <div class="row align-items-center g-3">
                        <div class="col-md-8">
                            <div
                                class="input-group input-group-merge border rounded-pill overflow-hidden bg-light transition-all search-input-group">
                                <span class="input-group-text bg-transparent border-0 pe-1 fs-4"><i
                                        class="ti tabler-search text-primary"></i></span>
                                <input type="text" class="form-control border-0 bg-transparent py-2 shadow-none"
                                    id="inventorySearch" placeholder="Search by Asset#, Serial Number, or Part Name..."
                                    onkeyup="fetchInventory()">
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <span class="text-muted small fw-medium">
                                <i class="ti tabler-info-circle me-1 f-5"></i> Multiple items selection enabled
                            </span>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-label-secondary text-uppercase small ls-1 fw-bold border-top-0">
                            <tr>
                                <th class="ps-4">Asset ID</th>
                                <th>Specification</th>
                                <th>Serial Number</th>
                                <th>Brand</th>
                                <th>Location</th>
                                <th class="text-center pe-4" style="width: 120px;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="inventoryListBody">
                            <!-- Items will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-white border-top p-3 justify-content-between">
                <div>
                    <small class="text-muted" id="inventoryResultCount">Found 0 items</small>
                </div>
                <button type="button" class="btn btn-label-secondary rounded-pill px-4 waves-effect"
                    data-bs-dismiss="modal">Finished Selection</button>
            </div>
        </div>
    </div>
</div>

<style>
    .search-input-group:focus-within {
        background-color: #fff !important;
        border-color: #7367f0 !important;
        box-shadow: 0 4px 12px rgba(115, 103, 240, 0.15);
    }

    .f-5 {
        font-size: 1.1rem;
    }

    .ls-1 {
        letter-spacing: 0.5px;
    }

    .bg-light-subtle {
        background-color: #fbfbfc !important;
    }

    .cursor-pointer {
        cursor: pointer;
    }

    #inventoryListBody tr:hover {
        background-color: #f0f0ff !important;
    }
</style>

<script>
    function fetchInventory() {
        const clientId = document.getElementById('client_id').value;
        const search = document.getElementById('inventorySearch').value;
        const tbody = document.getElementById('inventoryListBody');

        if (!clientId) {
            tbody.innerHTML =
                '<tr><td colspan="6" class="text-center py-5"><div class="badge bg-label-danger fs-6 rounded-pill px-4 py-2 mb-2"><i class="ti tabler-alert-triangle me-1"></i> Selection Required</div><p class="text-muted mb-0">Please choose a Client first to load inventory data.</p></td></tr>';
            return;
        }

        tbody.innerHTML =
            '<tr><td colspan="6" class="text-center py-5"><div class="spinner-border text-primary shadow-sm mb-3"></div><p class="text-muted fw-medium mb-0">Scanning inventory database...</p></td></tr>';

        // Collect existing IDs to exclude
        const keys = ['outbound_products', 'outbound_f_products', 'outbound_rma_products', 'outbound_products_wo'];
        let excludeIds = [];
        keys.forEach(k => {
            const items = JSON.parse(localStorage.getItem(k)) ?? [];
            items.forEach(i => {
                if (i.product_id) excludeIds.push(i.product_id)
            });
        });

        fetch(
                `{{ route('outbound.get.inventory') }}?client_id=${clientId}&search=${search}&exclude_ids=${excludeIds.join(',')}`
                )
            .then(r => r.json())
            .then(data => {
                tbody.innerHTML = '';
                document.getElementById('inventoryResultCount').innerText = `Found ${data.length} available items`;

                if (data.length === 0) {
                    tbody.innerHTML =
                        '<tr><td colspan="6" class="text-center py-5"><i class="ti tabler-search-off fs-1 text-muted mb-3 d-block"></i><h6 class="text-muted mb-0">No available units found matching your search.</h6></td></tr>';
                    return;
                }
                data.forEach(item => {
                    tbody.innerHTML += `
                        <tr class="animate__animated animate__fadeIn">
                            <td class="ps-4 fw-bold text-dark"><span class="badge bg-label-dark rounded-pill shadow-xs">${item.unique_id}</span></td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-dark">${item.part_name}</span>
                                    <small class="text-muted font-small">${item.part_number}</small>
                                </div>
                            </td>
                            <td><span class="fw-medium">${item.serial_number}</span></td>
                            <td><span class="badge bg-label-secondary border-0">${item.brand}</span></td>
                            <td><span class="text-primary fw-medium"><i class="ti tabler-map-pin me-1"></i> ${item.location}</span></td>
                            <td class="text-center pe-4">
                                <button class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm select-unit-btn waves-effect" onclick='pickInventoryItem(${JSON.stringify(item)})'>
                                    <i class="ti tabler-plus me-1"></i> Select
                                </button>
                            </td>
                        </tr>
                    `;
                });
            });
    }

    function pickInventoryItem(item) {
        if (typeof window.onReceivePickedItem === 'function') {
            window.onReceivePickedItem(item);
            // Show a tiny success toast or feedback instead of closing modal
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 800,
                timerProgressBar: false,
                width: '18rem',
                padding: '0.5rem',
                customClass: {
                    title: 'fs-6 fw-medium'
                }
            });
            Toast.fire({
                icon: 'success',
                title: 'Added!'
            });
            // Re-fetch to skip selected item
            fetchInventory();
        }
    }
</script>
