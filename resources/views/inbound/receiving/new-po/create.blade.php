@extends('layout.index')
@section('title', 'Create New PO')
@section('layout_class', 'layout-menu-collapsed')

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
                                <select class="form-control" id="productGroup">
                                    <option value="">-- Choose Product Group --</option>
                                    @foreach ($productGroup as $item)
                                        <option value="{{ $item->name }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Condition</label>
                                <select class="form-control" id="condition">
                                    <option>New</option>
                                    <option>Second</option>
                                    <option>Scrap</option>
                                    <option>Broken</option>
                                    <option>Repair</option>
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
                                <select class="form-control" id="brand">
                                    <option value="">-- Choose Brand --</option>
                                    @foreach ($brand as $item)
                                        <option value="{{ $item->name }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">QTY</label>
                                <input type="number" class="form-control" id="qty" placeholder="QTY ...">
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
    <script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
    <script>
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

            if (!sn) {
                Swal.fire('Error', 'Serial Number is required', 'error');
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
                qty: document.getElementById('qty').value,
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
                    Swal.fire(
                        'Created!',
                        'Your PO has been created successfully (Simulation).',
                        'success'
                    );
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
                    <td><span class="badge bg-info">${product.condition}</span></td>
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
            document.getElementById('productGroup').value = product.productGroup;
            document.getElementById('brand').value = product.brand;
            document.getElementById('condition').value = product.condition;
            document.getElementById('qty').value = product.qty;

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

        function resetForm() {
            document.getElementById('partName').value = '';
            document.getElementById('partNumber').value = '';
            document.getElementById('partDescription').value = '';
            document.getElementById('serialNumber').value = '';
            document.getElementById('productGroup').value = '';
            document.getElementById('brand').value = '';
            document.getElementById('condition').value = 'New';
            document.getElementById('qty').value = '';

            document.getElementById('addProductModalLabel').innerText = 'Add Product';
            document.getElementById('saveProductBtn').innerText = 'Add Product';
            editingIndex = null;
        }

        // Initial render
        renderProducts();
    </script>
@endsection
