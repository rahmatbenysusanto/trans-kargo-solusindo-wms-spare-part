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

                    {{-- Paste Serial Numbers Section --}}
                    <div class="mt-3 pt-3 border-top" id="pasteBySnSection">
                        <div class="d-flex align-items-center justify-content-between">
                            <a href="javascript:void(0)" onclick="togglePasteSection()"
                               class="text-primary fw-medium text-decoration-none small d-flex align-items-center gap-1"
                               id="pasteToggle">
                                <i class="ti tabler-chevron-down fs-6" id="pasteChevron"
                                   style="transition: transform 0.2s;"></i>
                                Paste Serial Numbers
                            </a>
                            <span class="text-muted small">
                                <i class="ti tabler-bulb me-1"></i>Select multiple items at once
                            </span>
                        </div>
                        <div id="pasteSection" class="mt-2" style="display: none;">
                            <div class="d-flex gap-2 align-items-start">
                                <textarea class="form-control form-control-sm" id="serialNumbersPaste" rows="3"
                                    placeholder="Paste serial numbers here...&#10;One per line, or separated by comma / semicolon"
                                    style="resize: vertical; font-size: 0.85rem;"></textarea>
                                <button class="btn btn-primary btn-sm px-3 flex-shrink-0 shadow-sm"
                                    onclick="selectBySerialNumbers()" id="selectBySnBtn"
                                    style="border-radius: 8px; min-width: 110px;">
                                    <i class="ti tabler-check me-1"></i> Select All
                                </button>
                            </div>
                            <div class="d-flex align-items-center gap-3 mt-1">
                                <small class="text-muted">
                                    <i class="ti tabler-info-circle me-1"></i>
                                    One serial number per line, or separated by comma / semicolon
                                </small>
                                <small id="pasteSerialCount" class="fw-medium text-muted" style="display: none;"></small>
                            </div>
                            <div id="pasteResult" class="mt-1 small fw-medium" style="display: none;"></div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-label-secondary text-uppercase small ls-1 fw-bold border-top-0">
                            <tr>
                                <th class="ps-4">Warehouse Asset ID</th>
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

        // No longer forcing client selection to allow fetching products with no client

        tbody.innerHTML =
            '<tr><td colspan="6" class="text-center py-5"><div class="spinner-border text-primary shadow-sm mb-3"></div><p class="text-muted fw-medium mb-0">Scanning inventory database...</p></td></tr>';

        // Collect existing IDs to exclude
        const keys = ['outbound_temp_products', 'outbound_products', 'outbound_f_products', 'outbound_rma_products',
            'outbound_products_wo'
        ];
        let excludeIds = [];
        keys.forEach(k => {
            try {
                const items = JSON.parse(localStorage.getItem(k)) ?? [];
                items.forEach(i => {
                    if (i.product_id) excludeIds.push(i.product_id)
                });
            } catch (e) {}
        });

        const category = document.getElementById('category').value;
        const url =
            `{{ route('outbound.get.inventory') }}?client_id=${clientId}&search=${search}&exclude_ids=${excludeIds.join(',')}&category=${category}`;

        fetch(url)
            .then(r => {
                if (!r.ok) throw new Error('Server responded with ' + r.status);
                return r.json();
            })
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
            })
            .catch(error => {
                console.error('Fetch error:', error);
                tbody.innerHTML =
                    `<tr><td colspan="6" class="text-center py-5"><div class="badge bg-label-danger fs-6 rounded-pill px-4 py-2 mb-2"><i class="ti tabler-alert-circle me-1"></i> Error</div><p class="text-muted mb-0">Failed to load inventory: ${error.message}</p></td></tr>`;
            });
    }

    function togglePasteSection() {
        const section = document.getElementById('pasteSection');
        const chevron = document.getElementById('pasteChevron');
        const isHidden = section.style.display === 'none' || !section.style.display;
        section.style.display = isHidden ? 'block' : 'none';
        chevron.style.transform = isHidden ? 'rotate(0deg)' : 'rotate(-90deg)';

        if (isHidden) {
            document.getElementById('serialNumbersPaste').focus();
        }
    }

    function updateSerialCount() {
        const textarea = document.getElementById('serialNumbersPaste');
        const raw = textarea.value.trim();
        const countEl = document.getElementById('pasteSerialCount');
        if (!raw) {
            countEl.style.display = 'none';
            return;
        }
        const sns = raw.split(/[\r\n,;]+/).map(s => s.trim()).filter(s => s.length > 0);
        if (sns.length > 0) {
            countEl.style.display = 'inline';
            countEl.textContent = sns.length + ' serial number(s) detected';
        } else {
            countEl.style.display = 'none';
        }
    }

    // Auto-count serial numbers as user types
    document.addEventListener('DOMContentLoaded', function() {
        const textarea = document.getElementById('serialNumbersPaste');
        if (textarea) {
            textarea.addEventListener('input', updateSerialCount);
        }
    });

    function selectBySerialNumbers() {
        const textarea = document.getElementById('serialNumbersPaste');
        const raw = textarea.value.trim();
        if (!raw) {
            const resultDiv = document.getElementById('pasteResult');
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = '<span class="text-warning"><i class="ti tabler-alert-triangle me-1"></i> Please paste at least one serial number</span>';
            setTimeout(() => { resultDiv.style.display = 'none'; }, 3000);
            return;
        }

        // Split by newline, comma, or semicolon
        const sns = raw.split(/[\r\n,;]+/)
            .map(s => s.trim())
            .filter(s => s.length > 0);

        if (sns.length === 0) return;

        const btn = document.getElementById('selectBySnBtn');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Selecting...';

        const clientId = document.getElementById('client_id').value;
        const category = document.getElementById('category').value;
        const url = `{{ route('outbound.get.inventory') }}?client_id=${clientId}&category=${category}&serial_numbers=${encodeURIComponent(sns.join(','))}`;

        fetch(url)
            .then(r => {
                if (!r.ok) throw new Error('Server responded with ' + r.status);
                return r.json();
            })
            .then(data => {
                let selectedCount = 0;
                let skippedCount = 0;

                data.forEach(item => {
                    // Skip if already in localStorage (check all outbound storage keys)
                    const keys = ['outbound_temp_products', 'outbound_products',
                                  'outbound_f_products', 'outbound_rma_products',
                                  'outbound_products_wo'];
                    let alreadySelected = false;
                    for (const k of keys) {
                        try {
                            const items = JSON.parse(localStorage.getItem(k)) ?? [];
                            if (items.some(i => i.product_id === item.id)) {
                                alreadySelected = true;
                                break;
                            }
                        } catch (e) {}
                    }

                    if (!alreadySelected && typeof window.onReceivePickedItem === 'function') {
                        window.onReceivePickedItem(item);
                        selectedCount++;
                    } else {
                        skippedCount++;
                    }
                });

                const notFound = sns.length - data.length - (sns.length > data.length ? 0 : 0);
                const resultDiv = document.getElementById('pasteResult');
                resultDiv.style.display = 'block';

                let msg = '';
                let cls = '';

                if (selectedCount > 0) {
                    msg += `<span class="text-success"><i class="ti tabler-check-circle me-1"></i> ${selectedCount} item(s) selected successfully</span>`;
                    cls = 'text-success';
                }
                if (skippedCount > 0) {
                    msg += `<span class="text-muted ms-2">(${skippedCount} already selected)</span>`;
                }
                const notFoundCount = sns.length - data.length;
                if (notFoundCount > 0) {
                    msg += `<div class="text-warning mt-1"><i class="ti tabler-alert-triangle me-1"></i> ${notFoundCount} serial number(s) not found in available inventory</div>`;
                }

                if (!msg) {
                    msg = '<span class="text-muted">No new items were selected</span>';
                }

                resultDiv.innerHTML = msg;

                // Reset button
                btn.disabled = false;
                btn.innerHTML = originalText;

                // Refresh the inventory list
                fetchInventory();
            })
            .catch(err => {
                const resultDiv = document.getElementById('pasteResult');
                resultDiv.style.display = 'block';
                resultDiv.innerHTML = `<span class="text-danger"><i class="ti tabler-alert-circle me-1"></i> Error: ${err.message}</span>`;

                btn.disabled = false;
                btn.innerHTML = originalText;
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
