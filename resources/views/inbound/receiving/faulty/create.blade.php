@extends('layout.index')
@section('title', 'Create Receiving Faulty')
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
                width: '100%'
            });

            $('#brand').select2({
                dropdownParent: $('#addProductModal'),
                placeholder: "-- Choose Brand --",
                allowClear: true,
                width: '100%'
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
                ["Part Name", "Part Number", "Part Desc", "Brand", "Brand Group", "Serial Number",
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
                    const sn = String(row["Serial Number"] || "").trim();
                    if (!sn) return;

                    if (existingSn.has(sn)) {
                        duplicates.push(sn);
                    } else {
                        const excelGroup = (row["Brand Group"] || row["Product Group"] || row["Group"] || "")
                            .trim();
                        const excelBrand = (row["Brand"] || row["Manufacturer"] || "").trim();

                        const groupKey = excelGroup.toLowerCase();
                        const brandKey = excelBrand.toLowerCase();

                        // Case-insensitive Validation
                        const masterGroupName = masterProductGroups.get(groupKey);
                        const masterBrandName = masterBrands.get(brandKey);

                        if (!masterGroupName && excelGroup) invalidGroups.add(excelGroup);
                        if (!masterBrandName && excelBrand) invalidBrands.add(excelBrand);

                        existingSn.add(sn);
                        products.push({
                            partName: row["Part Name"] || row["Material Description"] || row[
                                "Material"] || "",
                            partNumber: row["Part Number"] || row["Part Number/SKU"] || row[
                                "Material"] || "",
                            partDescription: row["Part Desc"] || row["Part Description"] || row[
                                "Material Description"] || "",
                            serialNumber: sn,
                            productGroup: masterGroupName || "",
                            brand: masterBrandName || "",
                            condition: row["Condition"] || "New",
                            qty: row["QTY"] || 1,
                        });
                    }
                });

                localStorage.setItem('products', JSON.stringify(products));
                currentPage = 1; // Reset to first page after upload
                renderProducts();
                $('#massUploadProductModal').modal('hide');
                fileInput.value = ''; // Reset input

                let message = "";
                if (duplicates.length > 0) {
                    message += `Skipped ${duplicates.length} duplicate Serial Numbers.<br>`;
                }
                if (invalidGroups.size > 0) {
                    message +=
                        `Detected invalid Product Groups: <b>${Array.from(invalidGroups).join(', ')}</b> (Values cleared).<br>`;
                }
                if (invalidBrands.size > 0) {
                    message +=
                        `Detected invalid Brands: <b>${Array.from(invalidBrands).join(', ')}</b> (Values cleared).<br>`;
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

            if (!sn || !brand || !productGroup) {
                Swal.fire({
                    title: 'Error',
                    text: 'Serial Number, Brand, and Product Group are required',
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
                productGroup: document.getElementById('productGroup').value,
                brand: document.getElementById('brand').value,
                condition: document.getElementById('condition').value,
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

        function submitPO() {
            const products = JSON.parse(localStorage.getItem('products')) ?? [];
            if (products.length === 0) {
                Swal.fire('Error', 'Please add at least one product before creating.', 'error');
                return;
            }

            const number = document.getElementById('number').value; // NTT RN#
            const poNumber = document.getElementById('po_number').value; // PO#
            const sttb = document.getElementById('sttb').value;
            const clientId = document.getElementById('client_id').value;
            const deliveryNote = document.getElementById('delivery_note').value;
            const courierInvoice = document.getElementById('courier_invoice').value;
            const picNtt = document.getElementById('pic_ntt').value;
            const receivedDate = document.getElementById('date').value;
            const receivedBy = document.getElementById('received_by').value;
            const category = "Faulty";

            if (!number || !clientId || !receivedDate || !receivedBy || !sttb) {
                Swal.fire('Error', 'Please fill in all required fields (STTB, NTT RN#, Client, Date, Received By).',
                    'error');
                return;
            }

            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to create this Receiving Faulty?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, create it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Processing...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    });

                    fetch('{{ route('receiving.store.faulty') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                category,
                                client_id: clientId,
                                number: number, // NTT RN#
                                po_number: poNumber, // PO#
                                vendor: 'Internal',
                                sttb: sttb,
                                delivery_note: deliveryNote,
                                courier_invoice: courierInvoice,
                                receivingNote: 'PIC NTT: ' + picNtt,
                                receivedDate,
                                receivedBy,
                                products
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status) {
                                localStorage.removeItem('products');
                                Swal.fire({
                                    title: 'Success!',
                                    text: 'Your Receiving Faulty has been created successfully.',
                                    icon: 'success'
                                }).then(() => {
                                    window.location.href = '{{ route('receiving') }}';
                                });
                            } else {
                                Swal.fire('Error', data.message || 'Failed to create Receiving Faulty.',
                                    'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('Error', 'An unexpected error occurred.', 'error');
                        });
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
            pageItems.forEach((product, i) => {
                const index = start + i;
                html += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${product.partName}</td>
                    <td>${product.partNumber}</td>
                    <td>${product.brand}</td>
                    <td>${product.productGroup}</td>
                    <td>${product.partDescription}</td>
                    <td>${product.serialNumber}</td>
                    <td>
                        <select class="form-control form-control-sm" onchange="updateProductCondition(${index}, this.value)">
                            <option value="New" ${product.condition === 'New' ? 'selected' : ''}>New</option>
                            <option value="Refurbished" ${product.condition === 'Refurbished' ? 'selected' : ''}>Refurbished</option>
                            <option value="Faulty" ${product.condition === 'Faulty' ? 'selected' : ''}>Faulty</option>
                            <option value="Write-off Needed" ${product.condition === 'Write-off Needed' ? 'selected' : ''}>Write-off Needed</option>
                        </select>
                    </td>
                    <td>
                        <button class="btn btn-warning btn-sm" onclick="editProduct(${index})">Edit</button>
                        <button class="btn btn-danger btn-sm" onclick="deleteProduct(${index})">Delete</button>
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

        function resetForm() {
            document.getElementById('partName').value = '';
            document.getElementById('partNumber').value = '';
            document.getElementById('partDescription').value = '';
            document.getElementById('serialNumber').value = '';

            $('#productGroup').val('').trigger('change');
            $('#brand').val('').trigger('change');
            $('#condition').val('New').trigger('change');

            document.getElementById('addProductModalLabel').innerText = 'Add Product';
            document.getElementById('saveProductBtn').innerText = 'Add Product';
            editingIndex = null;
        }

        // Initial render
        renderProducts();
    </script>
@endsection

@section('content')
    <div class="row">
        <div class="d-flex justify-content-end mb-3">
            <a class="btn btn-primary btn-sm text-white" onclick="submitPO()">Create Faulty</a>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-4 mb-3">
                            <label class="form-label">Category</label>
                            <input type="text" class="form-control" value="Faulty" readonly>
                        </div>
                        <div class="col-4 mb-3">
                            <label class="form-label">STTB</label>
                            <input type="text" class="form-control" name="sttb" id="sttb" placeholder="STTB ...">
                        </div>
                        <div class="col-4 mb-3">
                            <label class="form-label">NTT RN#</label>
                            <input type="text" class="form-control" name="number" id="number"
                                placeholder="NTT RN# ...">
                        </div>
                        <div class="col-4 mb-3">
                            <label class="form-label">PO# (Optional Reference)</label>
                            <input type="text" class="form-control" name="po_number" id="po_number"
                                placeholder="PO# ...">
                        </div>
                        <div class="col-4 mb-3">
                            <label class="form-label">Courier DN</label>
                            <input type="text" class="form-control" name="delivery_note" id="delivery_note"
                                placeholder="Courier DN ...">
                        </div>
                        <div class="col-4 mb-3">
                            <label class="form-label">Courier Invoice</label>
                            <input type="text" class="form-control" name="courier_invoice" id="courier_invoice"
                                placeholder="Courier Invoice ...">
                        </div>
                        <div class="col-4 mb-3">
                            <label class="form-label">Client</label>
                            <select class="form-control select2" name="client_id" id="client_id">
                                <option value="">-- Choose Client --</option>
                                @foreach ($client as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-4 mb-3">
                            <label class="form-label">PIC NTT</label>
                            <input type="text" class="form-control" name="pic_ntt" id="pic_ntt"
                                placeholder="PIC NTT ...">
                        </div>
                        <div class="col-4 mb-3">
                            <label class="form-label">Received Date</label>
                            <input type="date" class="form-control" name="date" id="date"
                                value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-4 mb-3">
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
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Part Name</th>
                                    <th>Part Number/SKU</th>
                                    <th>Brand</th>
                                    <th>Group</th>
                                    <th>Part Description</th>

                                    <th>Serial Number</th>
                                    <th>Condition</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="productTableBody">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <div id="paginationInfo">Showing 0 to 0 of 0 products</div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0" id="paginationControls">
                        </ul>
                    </nav>
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
                            <div class="mb-3">
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
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label">Part Number</label>
                                <input type="text" class="form-control" id="partNumber"
                                    placeholder="Part Number ...">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Serial Number</label>
                                <input type="text" class="form-control" id="serialNumber"
                                    placeholder="Serial Number ...">
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
