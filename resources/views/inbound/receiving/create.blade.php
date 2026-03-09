@extends('layout.index')
@section('title', 'Create Receiving Spare')
@section('layout_class', 'layout-menu-collapsed')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--single {
            border: 1px solid #dbdade !important;
            border-radius: 0.375rem !important;
            height: 38px !important;
            display: flex !important;
            align-items: center !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #6f6b7d !important;
            padding-left: 0.9rem !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
        }
    </style>
@endsection

@section('js')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
    <script>
        let currentPage = 1;
        const pageSize = 50;

        // Load master data and create case-insensitive maps
        const groupList = @json($productGroup->pluck('name'));
        const brandList = @json($brand->pluck('name'));

        const masterProductGroups = new Map(groupList.map(name => [name.toLowerCase(), name]));
        const masterBrands = new Map(brandList.map(name => [name.toLowerCase(), name]));

        $(document).ready(function() {
            $('#client_id').select2({
                placeholder: "-- Choose Client --",
                allowClear: true,
                width: '100%'
            });

            $('#productGroup').select2({
                dropdownParent: $('#addProductModal'),
                placeholder: "-- Choose Product Group --",
                allowClear: true,
                width: '100%',
                tags: true
            });

            $('#brand').select2({
                dropdownParent: $('#addProductModal'),
                placeholder: "-- Choose Brand --",
                allowClear: true,
                width: '100%',
                tags: true
            });

            $('#condition').select2({
                dropdownParent: $('#addProductModal'),
                placeholder: "-- Choose Condition --",
                allowClear: true,
                width: '100%'
            });
        });

        localStorage.clear();
        let editingIndex = null;

        function downloadTemplate() {
            const headers = [
                ["Part Name", "Product Number", "Part Desc", "Brand", "Brand Group", "Serial Number", "Parent SN",
                    "Condition"
                ]
            ];
            const worksheet = XLSX.utils.aoa_to_sheet(headers);
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, "Template");
            XLSX.writeFile(workbook, "Template_Upload_Product.xlsx");
        }

        function uploadProduct() {
            const fileInput = document.getElementById('excelFile');
            const file = fileInput.files[0];
            if (!file) {
                alert("Please select a file first!");
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, {
                    type: 'array'
                });
                const firstSheetName = workbook.SheetNames[1];
                const worksheet = workbook.Sheets[firstSheetName];
                const jsonData = XLSX.utils.sheet_to_json(worksheet);

                const products = JSON.parse(localStorage.getItem('products')) ?? [];

                // Optimized duplicate check using Set
                const existingSn = new Set(products.map(p => p.serialNumber));
                let duplicates = [];
                let invalidGroups = new Set();
                let invalidBrands = new Set();

                jsonData.forEach(row => {
                    const sn = String(row["Serial Number"] || row["Serial Number (SN)"] || "").trim();
                    if (!sn) return;

                    if (existingSn.has(sn)) {
                        duplicates.push(sn);
                    }

                    const excelGroup = (row["Brand Group"] || row["Product Group"] || row["Group"] || row[
                            "Product Group"] || "")
                        .trim();
                    const excelBrand = (row["Brand"] || row["Manufacturer"] || row["Brand (Brand)"] || "")
                        .trim();

                    const groupKey = excelGroup.toLowerCase();
                    const brandKey = excelBrand.toLowerCase();

                    // Case-insensitive Validation
                    const groupName = masterProductGroups.get(groupKey) || excelGroup;
                    const brandName = masterBrands.get(brandKey) || excelBrand;

                    existingSn.add(sn);
                    products.push({
                        partName: row["Part Name"] || row["Material Description"] || row[
                            "Material"] || row["Product Number (SKU)"] || "",
                        partNumber: row["Product Number"] || row["Part Number"] || row[
                            "Part Number/SKU"] || row[
                            "Material"] || row["Product Number (SKU)"] || "",
                        partDescription: row["Part Desc"] || row["Part Description"] || row[
                            "Material Description"] || row["Product Description"] || "",
                        serialNumber: sn,
                        parentSn: row["Parent SN"] || row["Parent Serial Number"] || "",
                        whAssetNumber: row["Warehouse Asset#"] || row["WH Asset#"] || row[
                            "Asset#"] || "",
                        productGroup: groupName,
                        brand: brandName,
                        condition: row["Condition"] || "New",
                        stockStatus: row["Stock Status"] || row["Status"] || "Available",
                        stagingDate: row["Staging Date"] || "",
                        qty: row["QTY"] || 1,
                    });
                });

                localStorage.setItem('products', JSON.stringify(products));
                currentPage = 1; // Reset to first page after upload
                renderProducts();
                $('#massUploadProductModal').modal('hide');
                fileInput.value = ''; // Reset input

                let message = "";
                if (duplicates.length > 0) {
                    message += `Found ${duplicates.length} duplicate Serial Numbers. Rows are highlighted in red.<br>`;
                }

                if (message) {
                    Swal.fire({
                        title: 'Upload Summary',
                        html: `<div class="text-start">${message}</div>`,
                        icon: 'warning'
                    });
                } else {
                    Swal.fire({
                        title: 'Success!',
                        text: 'All products uploaded and validated successfully!',
                        icon: 'success'
                    });
                }
            };
            reader.readAsArrayBuffer(file);
        }

        function massUploadModal() {
            $('#massUploadProductModal').modal('show');
        }

        function addProductModal() {
            editingIndex = null;
            resetForm();
            $('#addProductModal').modal('show');
        }

        function addProduct() {
            const products = JSON.parse(localStorage.getItem('products')) ?? [];
            const sn = document.getElementById('serialNumber').value.trim();
            const brand = document.getElementById('brand').value.trim();
            const productGroup = document.getElementById('productGroup').value.trim();
            const parentSn = document.getElementById('parentSn').value.trim();

            if (!sn || !brand || !productGroup) {
                Swal.fire({
                    title: 'Error',
                    text: 'Serial Number, Brand, and Product Group are required!',
                    icon: 'error'
                });
                return;
            }

            // Validation for unique serial number
            const isDuplicate = products.some((p, index) => p.serialNumber === sn && index !== editingIndex);
            if (isDuplicate) {
                Swal.fire({
                    title: 'Duplicate Serial Number',
                    text: 'This Serial Number already exists in the list.',
                    icon: 'error'
                });
                return;
            }

            const product = {
                partName: document.getElementById('partName').value,
                partNumber: document.getElementById('partNumber').value,
                partDescription: document.getElementById('partDescription').value,
                serialNumber: sn,
                parentSn: parentSn,
                whAssetNumber: document.getElementById('whAssetNumber').value,
                productGroup: productGroup,
                brand: brand,
                condition: document.getElementById('condition').value,
                stockStatus: document.getElementById('stockStatus').value,
                stagingDate: document.getElementById('stagingDate').value,
                qty: 1,
            };

            if (editingIndex !== null) {
                products[editingIndex] = product;
            } else {
                products.push(product);
            }

            localStorage.setItem('products', JSON.stringify(products));
            renderProducts();
            $('#addProductModal').modal('hide');
            resetForm();
        }

        async function submitPO() {
            const products = JSON.parse(localStorage.getItem('products')) ?? [];
            if (products.length === 0) {
                Swal.fire('Error', 'Please add at least one product before creating.', 'error');
                return;
            }

            const data = {
                category: document.getElementById('category').value,
                request_type: document.getElementById('request_type').value,
                ntt_requestor: document.getElementById('ntt_requestor').value,
                request_date: document.getElementById('request_date').value,
                client_id: document.getElementById('client_id').value,
                client_contact: document.getElementById('client_contact').value,
                pickup_address: document.getElementById('pickup_address').value,
                number: document.getElementById('number').value, // NTT RN#
                po_number: document.getElementById('po_number').value, // Transkargo SN / PO
                sap_po_number: document.getElementById('sap_po_number').value,
                ecapex_number: document.getElementById('ecapex_number').value,
                vendor_dn_number: document.getElementById('vendor_dn_number').value,
                tks_dn_number: document.getElementById('tks_dn_number').value,
                tks_invoice_number: document.getElementById('tks_invoice_number').value,
                rma_number: document.getElementById('rma_number').value,
                itsm_number: document.getElementById('itsm_number').value,
                sttb: document.getElementById('sttb').value,
                delivery_note: document.getElementById('delivery_note').value,
                courier_invoice: document.getElementById('courier_invoice').value,
                vendor: document.getElementById('vendor').value,
                date: document.getElementById('date').value,
                received_by: document.getElementById('received_by').value,
            };

            if (!data.category || !data.client_id || !data.date || !data.received_by || !data.sttb || !data.number) {
                Swal.fire('Error',
                    'Please fill in all required fields (Category, STTB, NTT RN#, Client, Date, Received By).',
                    'error');
                return;
            }

            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to create this Receiving transaction?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, create it!'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Processing...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    });

                    try {
                        const response = await fetch('{{ route('receiving.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                ...data,
                                receivedDate: data.date,
                                receivedBy: data.received_by,
                                products
                            })
                        });

                        const resultData = await response.json();

                        if (resultData.status) {
                            localStorage.removeItem('products');
                            Swal.fire({
                                title: 'Success!',
                                text: 'The Receiving transaction has been created successfully.',
                                icon: 'success'
                            }).then(() => {
                                window.location.href = '{{ route('receiving') }}';
                            });
                        } else {
                            Swal.fire('Error', resultData.message || 'Failed to create receiving.',
                                'error');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        Swal.fire('Error', 'An unexpected error occurred.', 'error');
                    }
                }
            });
        }

        function renderProducts() {
            const products = JSON.parse(localStorage.getItem('products')) ?? [];
            const tbody = document.getElementById('productTableBody');
            const totalProducts = products.length;
            const totalPages = Math.ceil(totalProducts / pageSize);

            // Adjust current page if out of bounds
            if (currentPage > totalPages && totalPages > 0) currentPage = totalPages;
            if (currentPage < 1) currentPage = 1;

            const start = (currentPage - 1) * pageSize;
            const end = Math.min(start + pageSize, totalProducts);
            const pageItems = products.slice(start, end);

            tbody.innerHTML = '';

            let html = '';
            // Count occurrences of each SN for highlighting duplicates
            const snCounts = {};
            products.forEach(p => {
                const sn = p.serialNumber;
                snCounts[sn] = (snCounts[sn] || 0) + 1;
            });

            pageItems.forEach((product, i) => {
                const index = start + i;
                const isDuplicate = snCounts[product.serialNumber] > 1;
                html += `
                <tr class="${isDuplicate ? 'table-danger' : ''}">
                    <td class="py-1">${index + 1}</td>
                    <td class="py-1">${product.partNumber}</td>
                    <td class="py-1">${product.brand}</td>
                    <td class="py-1">${product.productGroup}</td>
                    <td class="py-1">${product.partDescription}</td>
                    <td class="py-1"><span class="fw-bold text-dark">${product.serialNumber}</span></td>
                    <td class="py-1 text-muted small">${product.parentSn || '-'}</td>
                    <td class="py-1 text-center">1</td>
                    <td class="py-1">
                        <select class="form-select form-select-sm py-0" style="font-size: 0.75rem;" onchange="updateProductCondition(${index}, this.value)">
                            <option value="New" ${product.condition === 'New' ? 'selected' : ''}>New</option>
                            <option value="Refurbished" ${product.condition === 'Refurbished' ? 'selected' : ''}>Refurbished</option>
                            <option value="Faulty" ${product.condition === 'Faulty' ? 'selected' : ''}>Faulty</option>
                            <option value="Write-off Needed" ${product.condition === 'Write-off Needed' ? 'selected' : ''}>Write-off Needed</option>
                            <option value="Spare Migration" ${product.condition === 'Spare Migration' ? 'selected' : ''}>Spare Migration</option>
                        </select>
                    </td>
                    <td class="py-1">
                        <div class="d-flex gap-1">
                            <button class="btn btn-xs btn-label-warning p-1" onclick="editProduct(${index})" title="Edit">
                                <i class="ti tabler-edit fs-6"></i>
                            </button>
                            <button class="btn btn-xs btn-label-danger p-1" onclick="deleteProduct(${index})" title="Delete">
                                <i class="ti tabler-trash fs-6"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
            });
            tbody.innerHTML = html;

            // Update Pagination Info
            document.getElementById('paginationInfo').innerText = totalProducts > 0 ?
                `Showing ${start + 1} to ${end} of ${totalProducts} products` :
                'Showing 0 to 0 of 0 products';

            // Update Pagination Controls
            renderPaginationControls(totalPages);
        }

        function renderPaginationControls(totalPages) {
            const controls = document.getElementById('paginationControls');
            controls.innerHTML = '';

            if (totalPages <= 1) return;

            // Prev Button
            controls.innerHTML += `
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0)" onclick="changePage(${currentPage - 1})">Previous</a>
                </li>
            `;

            // Page Numbers (limited)
            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, startPage + 4);
            if (endPage - startPage < 4) startPage = Math.max(1, endPage - 4);

            for (let i = startPage; i <= endPage; i++) {
                controls.innerHTML += `
                    <li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="javascript:void(0)" onclick="changePage(${i})">${i}</a>
                    </li>
                `;
            }

            // Next Button
            controls.innerHTML += `
                <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="javascript:void(0)" onclick="changePage(${currentPage + 1})">Next</a>
                </li>
            `;
        }

        function changePage(page) {
            currentPage = page;
            renderProducts();
        }

        function editProduct(index) {
            const products = JSON.parse(localStorage.getItem('products')) ?? [];
            const product = products[index];

            document.getElementById('partName').value = product.partName;
            document.getElementById('partNumber').value = product.partNumber;
            document.getElementById('partDescription').value = product.partDescription;
            document.getElementById('serialNumber').value = product.serialNumber;

            $('#productGroup').val(product.productGroup).trigger('change');
            $('#brand').val(product.brand).trigger('change');
            $('#condition').val(product.condition).trigger('change');
            document.getElementById('parentSn').value = product.parentSn ?? '';
            document.getElementById('whAssetNumber').value = product.whAssetNumber ?? '';
            document.getElementById('stockStatus').value = product.stockStatus ?? 'Available';
            document.getElementById('stagingDate').value = product.stagingDate ?? '';

            editingIndex = index;
            document.getElementById('addProductModalLabel').innerText = 'Edit Product';
            document.getElementById('saveProductBtn').innerText = 'Update Product';
            $('#addProductModal').modal('show');
        }

        function deleteProduct(index) {
            if (confirm('Are you sure you want to delete this product?')) {
                const products = JSON.parse(localStorage.getItem('products')) ?? [];
                products.splice(index, 1);
                localStorage.setItem('products', JSON.stringify(products));
                renderProducts();
            }
        }

        function updateProductCondition(index, newCondition) {
            const products = JSON.parse(localStorage.getItem('products')) ?? [];
            if (products[index]) {
                products[index].condition = newCondition;
                localStorage.setItem('products', JSON.stringify(products));
            }
        }

        function bulkUpdateCondition(newCondition) {
            if (!newCondition) return;

            const products = JSON.parse(localStorage.getItem('products')) ?? [];
            if (products.length === 0) return;

            products.forEach(product => {
                product.condition = newCondition;
            });

            localStorage.setItem('products', JSON.stringify(products));
            renderProducts();
        }

        function resetForm() {
            document.getElementById('partName').value = '';
            document.getElementById('partNumber').value = '';
            document.getElementById('partDescription').value = '';
            document.getElementById('serialNumber').value = '';

            $('#productGroup').val('').trigger('change');
            $('#brand').val('').trigger('change');
            $('#condition').val('New').trigger('change');
            document.getElementById('parentSn').value = '';
            document.getElementById('whAssetNumber').value = '';
            document.getElementById('stockStatus').value = 'Available';
            document.getElementById('stagingDate').value = '';

            document.getElementById('addProductModalLabel').innerText = 'Add Product';
            document.getElementById('saveProductBtn').innerText = 'Add Product';
            editingIndex = null;
        }

        // Initial render
        function updateRequestType() {
            const category = document.getElementById('category').value;
            const reqTypeSelect = document.getElementById('request_type');

            // Set default Request Type based on Category
            if (category === 'New PO') reqTypeSelect.value = 'New PO';
            else if (category === 'Spare Migration') reqTypeSelect.value = 'Spare Migration';
            else if (category === 'Spare from/to Loan') reqTypeSelect.value = 'Loan';
            else if (category === 'Faulty') reqTypeSelect.value = 'RMA';
            else if (category === 'Spare from/to Replacement') reqTypeSelect.value = 'RMA';
            else if (category === 'Spare Write-off') reqTypeSelect.value = 'Spare Write Off';

            toggleFields();
        }

        function toggleFields() {
            const reqType = document.getElementById('request_type').value;

            // Reset all dynamic fields
            $('.field-ntt-requestor, .field-request-date, .field-sap-po, .field-ecapex, .field-rma, .field-itsm, .field-vendor-dn')
                .hide();

            if (reqType === 'New PO') {
                $('.field-sap-po, .field-ecapex').show();
            } else if (reqType === 'RMA') {
                $('.field-ntt-requestor, .field-request-date, .field-rma, .field-itsm').show();
            } else if (reqType === 'Loan') {
                $('.field-ntt-requestor, .field-request-date').show();
            } else if (reqType === 'Spare Migration') {
                // No extra fields needed usually
            }
        }

        // Initial call
        $(document).ready(function() {
            updateRequestType();
        });

        renderProducts();
    </script>
@endsection

@section('content')
    <div class="row">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0">Create Receiving</h4>
            <a class="btn btn-primary text-white" onclick="submitPO()">
                <i class="ti tabler-device-floppy me-1"></i> Create Receiving
            </a>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Stock Category</label>
                            <select class="form-control" name="category" id="category" onchange="updateRequestType()">
                                <option value="New PO">New PO</option>
                                <option value="Spare from/to Replacement">Spare from/to Replacement</option>
                                <option value="Spare from/to Loan">Spare from/to Loan</option>
                                <option value="Faulty">Faulty</option>
                                <option value="RMA">RMA</option>
                                <option value="Spare Write-off">Spare Write-off</option>
                                <option value="Spare Migration">Spare Migration</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Request Type</label>
                            <select class="form-control" name="request_type" id="request_type" onchange="toggleFields()">
                                <option value="New PO">New PO</option>
                                <option value="RMA">RMA</option>
                                <option value="Loan">Loan</option>
                                <option value="Spare Write Off">Spare Write Off</option>
                                <option value="Spare Migration">Spare Migration</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3 field-ntt-requestor" style="display:none;">
                            <label class="form-label">NTT Requestor</label>
                            <input type="text" class="form-control" id="ntt_requestor" placeholder="Requestor Name ...">
                        </div>
                        <div class="col-md-3 mb-3 field-request-date" style="display:none;">
                            <label class="form-label">Request Date</label>
                            <input type="date" class="form-control" id="request_date" value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">STTB / Ref Number</label>
                            <input type="text" class="form-control" name="sttb" id="sttb" placeholder="STTB ...">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">NTT RN#</label>
                            <input type="text" class="form-control" name="number" id="number"
                                placeholder="NTT RN# ...">
                        </div>
                        <div class="col-md-3 mb-3 field-sap-po" style="display:none;">
                            <label class="form-label">SAP PO#</label>
                            <input type="text" class="form-control" id="sap_po_number" placeholder="SAP PO# ...">
                        </div>
                        <div class="col-md-3 mb-3 field-ecapex" style="display:none;">
                            <label class="form-label">eCapex#</label>
                            <input type="text" class="form-control" id="ecapex_number" placeholder="eCapex# ...">
                        </div>
                        <div class="col-md-3 mb-3 field-rma" style="display:none;">
                            <label class="form-label">RMA#</label>
                            <input type="text" class="form-control" id="rma_number" placeholder="RMA# ...">
                        </div>
                        <div class="col-md-3 mb-3 field-itsm" style="display:none;">
                            <label class="form-label">ITSM#</label>
                            <input type="text" class="form-control" id="itsm_number" placeholder="ITSM# ...">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">TKS DN# (Optional)</label>
                            <input type="text" class="form-control" id="tks_dn_number" placeholder="TKS DN# ...">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">TKS Invoice# (Optional)</label>
                            <input type="text" class="form-control" id="tks_invoice_number"
                                placeholder="TKS Invoice# ...">
                        </div>
                        <div class="col-md-3 mb-3 field-vendor-dn" style="display:none;">
                            <label class="form-label">Vendor Supplier DN#</label>
                            <input type="text" class="form-control" id="vendor_dn_number"
                                placeholder="Vendor DN# ...">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">PO# (Transkargo Ref)</label>
                            <input type="text" class="form-control" name="po_number" id="po_number"
                                placeholder="PO# ...">
                        </div>

                        <div class="col-12 border-bottom my-3"></div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Client</label>
                            <select class="form-control select2" name="client_id" id="client_id">
                                <option value="">-- Choose Client --</option>
                                @foreach ($client as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Client Contact</label>
                            <input type="text" class="form-control" id="client_contact"
                                placeholder="Contact Name/Dept ...">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pickup/Shipment Address</label>
                            <input type="text" class="form-control" id="pickup_address"
                                placeholder="Address detail ...">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Vendor / Supplier</label>
                            <input type="text" class="form-control" name="vendor" id="vendor"
                                placeholder="Vendor / Supplier ...">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Courier DN</label>
                            <input type="text" class="form-control" name="delivery_note" id="delivery_note"
                                placeholder="Courier DN ...">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Courier Invoice</label>
                            <input type="text" class="form-control" name="courier_invoice" id="courier_invoice"
                                placeholder="Courier Invoice ...">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Received Date</label>
                            <input type="date" class="form-control" name="date" id="date"
                                value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Received By</label>
                            <input type="text" class="form-control" name="received_by" id="received_by"
                                placeholder="Received By ...">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">List Product</h4>
                        <div class="d-flex gap-2">
                            <a class="btn btn-dark btn-sm text-white" onclick="downloadTemplate()">Download Template
                                Upload</a>
                            <a class="btn btn-secondary btn-sm text-white" onclick="massUploadModal()">Mass Upload
                                Product</a>
                            <a class="btn btn-info btn-sm text-white" onclick="addProductModal()">Add Product</a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="p-3 border-bottom bg-light d-flex justify-content-end align-items-center gap-2">
                        <small class="fw-bold text-muted">Bulk Update Condition:</small>
                        <select class="form-select form-select-sm w-auto" style="min-width: 150px;"
                            onchange="bulkUpdateCondition(this.value)">
                            <option value="">-- Select Condition --</option>
                            <option value="New">New</option>
                            <option value="Refurbished">Refurbished</option>
                            <option value="Faulty">Faulty</option>
                            <option value="Write-off Needed">Write-off Needed</option>
                            <option value="Spare Migration">Spare Migration</option>
                        </select>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm align-middle text-nowrap mb-0"
                            style="font-size: 0.85rem;">
                            <thead class="bg-primary">
                                <tr>
                                    <th class="px-3 text-white py-2">#</th>
                                    <th class="text-white">Product Number/SKU</th>
                                    <th class="text-white">Brand</th>
                                    <th class="text-white">Group</th>
                                    <th class="text-white">Part Description</th>
                                    <th class="text-white">Serial Number</th>
                                    <th class="text-white">Parent SN</th>
                                    <th class="text-white text-center">Qty</th>
                                    <th class="text-white" width="130">Condition</th>
                                    <th class="text-white text-center" width="80">Action</th>
                                </tr>
                            </thead>
                            <tbody id="productTableBody">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center py-3 bg-light">
                    <div class="d-flex align-items-center gap-3">
                        <div id="paginationInfo" class="text-muted small">Showing 0 to 0 of 0 products</div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0" id="paginationControls">
                            </ul>
                        </nav>
                    </div>
                    <div>
                        <a class="btn btn-primary px-4" onclick="submitPO()">
                            <i class="ti tabler-device-floppy me-1"></i> Create Receiving
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="massUploadProductModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Mass Upload Product</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="massUploadForm">
                        <label class="form-label">File Excel</label>
                        <input type="file" class="form-control" id="excelFile" accept=".xlsx, .xls">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="uploadProduct()">Upload Product</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="addProductModalLabel">Add Product</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3" style="display: none;">
                                <label class="form-label">Part Name</label>
                                <input type="text" class="form-control" id="partName" placeholder="Part Name ...">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Part Description</label>
                                <input type="text" class="form-control" id="partDescription"
                                    placeholder="Part Description ...">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Product Group</label>
                                <select class="form-control select2" id="productGroup">
                                    <option value="">-- Choose Product Group --</option>
                                    @foreach ($productGroup as $item)
                                        <option value="{{ $item->name }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Condition</label>
                                <select class="form-control select2" id="condition">
                                    <option value="New">New</option>
                                    <option value="Refurbished">Refurbished</option>
                                    <option value="Faulty">Faulty</option>
                                    <option value="Write-off Needed">Write-off Needed</option>
                                    <option value="Spare Migration">Spare Migration</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label">Product Number</label>
                                <input type="text" class="form-control" id="partNumber"
                                    placeholder="Product Number ...">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Serial Number</label>
                                <input type="text" class="form-control" id="serialNumber"
                                    placeholder="Serial Number ...">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Parent SN (Optional)</label>
                                <input type="text" class="form-control" id="parentSn" placeholder="Parent SN ...">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">WH Asset#</label>
                                <input type="text" class="form-control" id="whAssetNumber"
                                    placeholder="Asset Number ...">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Brand</label>
                                <select class="form-control select2" id="brand">
                                    <option value="">-- Choose Brand --</option>
                                    @foreach ($brand as $item)
                                        <option value="{{ $item->name }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Stock Status</label>
                                <select class="form-control" id="stockStatus">
                                    <option value="Available">Available</option>
                                    <option value="Faulty">Faulty</option>
                                    <option value="Write-off">Write-off</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Staging Date (Optional)</label>
                                <input type="date" class="form-control" id="stagingDate">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveProductBtn" onclick="addProduct()">Add
                        Product</button>
                </div>
            </div>
        </div>
    </div>
@endsection
