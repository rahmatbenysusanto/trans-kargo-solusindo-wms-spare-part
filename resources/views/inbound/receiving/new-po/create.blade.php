@extends('layout.index')
@section('title', 'Create New PO')
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

@section('content')
    <div class="row">
        <div class="d-flex justify-content-end mb-3">
            <a class="btn btn-primary btn-sm text-white" onclick="submitPO()">Create New PO</a>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-4 mb-3">
                            <label class="form-label">Category</label>
                            <input type="text" class="form-control" value="New PO" readonly>
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
                            <label class="form-label">PO Number</label>
                            <input type="text" class="form-control" name="po_number" id="po_number"
                                placeholder="PO Number ...">
                        </div>
                        <div class="col-4 mb-3">
                            <label class="form-label">Return/Receiving Note</label>
                            <input type="text" class="form-control" name="ntt_no" id="ntt_no"
                                placeholder="Return/Receiving Note ...">
                        </div>
                        <div class="col-4 mb-3">
                            <label class="form-label">Vendor/Supplier</label>
                            <input type="text" class="form-control" name="vendor" id="vendor"
                                placeholder="Vendor/Supplier ...">
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
                                    <th>QTY</th>
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

@section('js')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
    <script>
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
                ["Part Name", "Part Number", "Part Description", "Serial Number", "Product Group", "Brand", "Condition",
                    "QTY"
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
                const firstSheetName = workbook.SheetNames[0];
                const worksheet = workbook.Sheets[firstSheetName];
                const jsonData = XLSX.utils.sheet_to_json(worksheet);

                const products = JSON.parse(localStorage.getItem('products')) ?? [];

                let duplicates = [];
                jsonData.forEach(row => {
                    const sn = String(row["Serial Number"] || "").trim();
                    if (!sn) return;

                    // Check if already in localStorage
                    const isDuplicateInStorage = products.some(p => p.serialNumber === sn);
                    // Check if already in current upload list
                    const isDuplicateInCurrent = duplicates.includes(sn);

                    if (isDuplicateInStorage || isDuplicateInCurrent) {
                        duplicates.push(sn);
                    } else {
                        products.push({
                            partName: row["Part Name"] || "",
                            partNumber: row["Part Number"] || "",
                            partDescription: row["Part Description"] || "",
                            serialNumber: sn,
                            productGroup: row["Product Group"] || "",
                            brand: row["Brand"] || "",
                            condition: row["Condition"] || "New",
                            qty: row["QTY"] || 0,
                        });
                    }
                });

                localStorage.setItem('products', JSON.stringify(products));
                renderProducts();
                $('#massUploadProductModal').modal('hide');
                fileInput.value = ''; // Reset input

                if (duplicates.length > 0) {
                    Swal.fire({
                        title: 'Upload Finished',
                        text: `Products uploaded with ${duplicates.length} duplicate Serial Numbers skipped: ${duplicates.join(', ')}`,
                        icon: 'warning'
                    });
                } else {
                    Swal.fire({
                        title: 'Success!',
                        text: 'All products uploaded successfully!',
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
                Swal.fire('Error', 'Please add at least one product before creating a PO.', 'error');
                return;
            }

            const poNumber = document.getElementById('po_number').value;
            const clientId = document.getElementById('client_id').value;
            const vendor = document.getElementById('vendor').value;
            const receivedDate = document.getElementById('date').value;
            const receivedBy = document.getElementById('received_by').value;
            const receivingNote = document.getElementById('ntt_no').value;
            const category = "New PO";

            if (!poNumber || !clientId || !vendor || !receivedDate || !receivedBy) {
                Swal.fire('Error', 'Please fill in all required fields (Client, PO Number, Vendor, Date, Received By).',
                    'error');
                return;
            }

            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to create this New PO?",
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

                    fetch('{{ route('receiving.store.new-po') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                category,
                                client_id: clientId,
                                number: poNumber,
                                vendor,
                                receivedDate,
                                receivedBy,
                                receivingNote,
                                products
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status) {
                                localStorage.removeItem('products');
                                Swal.fire({
                                    title: 'Success!',
                                    text: 'Your PO has been created successfully.',
                                    icon: 'success'
                                }).then(() => {
                                    window.location.href = '{{ route('receiving') }}';
                                });
                            } else {
                                Swal.fire('Error', data.message || 'Failed to create PO.', 'error');
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
            tbody.innerHTML = '';

            products.forEach((product, index) => {
                const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${product.partName}</td>
                    <td>${product.partNumber}</td>
                    <td>${product.brand}</td>
                    <td>${product.productGroup}</td>
                    <td>${product.partDescription}</td>
                    <td>${product.qty}</td>
                    <td>${product.serialNumber}</td>
                    <td>
                        <select class="form-control form-control-sm" onchange="updateProductCondition(${index}, this.value)">
                            <option value="New" ${product.condition === 'New' ? 'selected' : ''}>New</option>
                            <option value="Second" ${product.condition === 'Second' ? 'selected' : ''}>Second</option>
                            <option value="Refurbished" ${product.condition === 'Refurbished' ? 'selected' : ''}>Refurbished</option>
                            <option value="Scrap" ${product.condition === 'Scrap' ? 'selected' : ''}>Scrap</option>
                            <option value="Broken" ${product.condition === 'Broken' ? 'selected' : ''}>Broken</option>
                            <option value="Repair/Disposal Needed" ${product.condition === 'Repair/Disposal Needed' ? 'selected' : ''}>Repair/Disposal Needed</option>
                        </select>
                    </td>
                    <td>
                        <button class="btn btn-warning btn-sm" onclick="editProduct(${index})">Edit</button>
                        <button class="btn btn-danger btn-sm" onclick="deleteProduct(${index})">Delete</button>
                    </td>
                </tr>
            `;
                tbody.innerHTML += row;
            });
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
