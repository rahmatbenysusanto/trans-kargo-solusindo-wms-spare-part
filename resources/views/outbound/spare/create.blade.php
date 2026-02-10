@extends('layout.index')
@section('title', 'Create Outbound Spare')
@section('layout_class', 'layout-menu-collapsed')

@section('js')
    <script>
        localStorage.clear();

        function renderProducts() {
            const products = JSON.parse(localStorage.getItem('outbound_products')) ?? [];
            const tbody = document.getElementById('productTableBody');
            const totalCount = document.getElementById('totalItemsCount');
            if (totalCount) totalCount.innerText = products.length;
            tbody.innerHTML = '';

            if (products.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="mb-2"><i class="ti tabler-package-off text-light shadow-sm bg-label-secondary rounded p-3 fs-1"></i></div>
                            <h5 class="text-muted mb-0">No items selected yet.</h5>
                            <small class="text-muted text-uppercase fw-medium">Pick items from the inventory to start.</small>
                        </td>
                    </tr>`;
                return;
            }

            products.forEach((product, index) => {
                const row = `
                <tr class="animate__animated animate__fadeIn">
                    <td class="text-center">${index + 1}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded-circle bg-label-primary font-small"><i class="ti tabler-barcode fs-6"></i></span>
                            </div>
                            <span class="fw-bold text-dark">${product.unique_id}</span>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-dark">${product.partName}</span>
                            <small class="text-muted badge bg-label-secondary border-0 text-start px-0" style="width: fit-content;">${product.partNumber}</small>
                        </div>
                    </td>
                    <td><span class="fw-medium">${product.serialNumber}</span></td>
                    <td><span class="badge bg-label-info border-info-subtle"><i class="ti tabler-map-pin me-1 fs-tiny"></i> ${product.location}</span></td>
                    <td class="text-center">
                        <button class="btn btn-label-danger btn-icon btn-sm rounded-circle shadow-none waves-effect" onclick="deleteProduct(${index})">
                            <i class="ti tabler-trash-x fs-5"></i>
                        </button>
                    </td>
                </tr>`;
                tbody.innerHTML += row;
            });
        }

        function deleteProduct(index) {
            Swal.fire({
                title: 'Remove item?',
                text: "Removing this item from the list",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ea5455',
                cancelButtonColor: '#82868b',
                confirmButtonText: 'Yes, remove',
                customClass: {
                    confirmButton: 'btn btn-danger me-1',
                    cancelButton: 'btn btn-label-secondary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    const products = JSON.parse(localStorage.getItem('outbound_products')) ?? [];
                    products.splice(index, 1);
                    localStorage.setItem('outbound_products', JSON.stringify(products));
                    renderProducts();
                }
            });
        }

        function submitOutbound() {
            const products = JSON.parse(localStorage.getItem('outbound_products')) ?? [];
            if (products.length === 0) {
                Swal.fire({
                    title: 'List Empty',
                    text: 'Please select at least one product before submitting.',
                    icon: 'error',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                });
                return;
            }

            const data = {
                category: document.getElementById('category').value,
                number: document.getElementById('po_number').value,
                ntt_dn_number: document.getElementById('ntt_dn_number').value,
                tks_dn_number: document.getElementById('tks_dn_number').value,
                tks_invoice_number: document.getElementById('tks_invoice_number').value,
                client_id: document.getElementById('client_id').value,
                outbound_date: document.getElementById('date').value,
                outbound_by: document.getElementById('outbound_by').value,
                products
            };

            if (!data.client_id || !data.outbound_date || !data.outbound_by) {
                Swal.fire({
                    title: 'Incomplete Form',
                    text: 'Please fill in all required fields marked with *',
                    icon: 'warning',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                });
                return;
            }

            Swal.fire({
                title: 'Confirm Outbound?',
                text: "This will record the transaction and update inventory.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="ti tabler-device-floppy me-1"></i> Save Transaction',
                customClass: {
                    confirmButton: 'btn btn-success me-1',
                    cancelButton: 'btn btn-label-secondary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('{{ route('outbound.store.spare') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(data)
                        })
                        .then(response => response.json())
                        .then(res => {
                            if (res.status) {
                                localStorage.removeItem('outbound_products');
                                Swal.fire({
                                    title: 'Success!',
                                    text: 'Outbound Spare recorded successfully.',
                                    icon: 'success',
                                    customClass: {
                                        confirmButton: 'btn btn-primary'
                                    },
                                    buttonsStyling: false
                                }).then(() => {
                                    window.location.href = '{{ route('outbound.index') }}';
                                });
                            } else {
                                Swal.fire('Error', res.message, 'error');
                            }
                        });
                }
            });
        }

        window.onReceivePickedItem = function(item) {
            const products = JSON.parse(localStorage.getItem('outbound_products')) ?? [];
            if (products.some(p => p.product_id === item.id)) {
                // Already handled in fetchInventory, but double check
                return;
            }

            products.push({
                product_id: item.id,
                unique_id: item.unique_id,
                partName: item.part_name,
                partNumber: item.part_number,
                partDescription: item.part_description,
                serialNumber: item.serial_number,
                brand: item.brand,
                productGroup: item.product_group,
                condition: item.condition,
                location: item.location
            });

            localStorage.setItem('outbound_products', JSON.stringify(products));
            renderProducts();
        };

        renderProducts();
    </script>
@endsection

@section('content')
    <div class="container-fluid flex-grow-1 container-p-y">
        <div class="row">
            <!-- Main Form Section -->
            <div class="col-12">
                <div class="card mb-4 border-0 shadow-sm overflow-hidden" style="border-radius: 12px;">
                    <div
                        class="card-header d-flex flex-wrap justify-content-between align-items-center bg-white border-bottom py-3 px-4">
                        <div class="me-3">
                            <h4 class="mb-1 text-primary fw-bold"><i class="ti tabler-arrow-up-right me-2"></i>Create Outbound
                                Spare</h4>
                            <p class="text-muted mb-0 small text-uppercase fw-medium ls-1">Record items leaving the warehouse
                            </p>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
                            <a href="{{ route('outbound.index') }}" class="btn btn-label-secondary waves-effect btn-sm">
                                <i class="ti tabler-arrow-left me-1"></i> Cancel
                            </a>
                            <button class="btn btn-primary shadow-sm btn-sm waves-effect waves-light"
                                onclick="submitOutbound()">
                                <i class="ti tabler-device-floppy me-1"></i> Save Transaction
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-4 bg-light-subtle">
                        <div class="row row-bordered g-0 rounded-3 bg-white border">
                            <div class="col-md-4 p-4 border-bottom">
                                <div class="mb-4">
                                    <label class="form-label text-dark fw-bold mb-1">Outbound Category <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select border-0 bg-light-subtle" name="category" id="category">
                                        <option>Spare from Replacement</option>
                                        <option>Spare from Loan</option>
                                    </select>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label text-dark fw-bold mb-1">Client <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select border-0 bg-light-subtle" name="client_id" id="client_id">
                                        <option value="">-- Choose Client --</option>
                                        @foreach ($client as $item)
                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-8 p-4">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">PO# (Optional)</label>
                                        <input type="text" class="form-control border-0 bg-light" name="po_number"
                                            id="po_number" placeholder="Enter PO number">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">NTT DN#</label>
                                        <input type="text" class="form-control border-0 bg-light" name="ntt_dn_number"
                                            id="ntt_dn_number" placeholder="Enter NTT DN">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">TKS DN#</label>
                                        <input type="text" class="form-control border-0 bg-light" name="tks_dn_number"
                                            id="tks_dn_number" placeholder="Enter TKS DN">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">TKS Invoice#</label>
                                        <input type="text" class="form-control border-0 bg-light"
                                            name="tks_invoice_number" id="tks_invoice_number" placeholder="Enter Invoice">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-primary">Outbound Date *</label>
                                        <input type="date" class="form-control border-0 bg-light fw-bold" name="date"
                                            id="date" value="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-primary">Outbound By *</label>
                                        <input type="text" class="form-control border-0 bg-light fw-bold"
                                            name="outbound_by" id="outbound_by" placeholder="Person in charge">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product List Section -->
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div
                        class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                        <h5 class="mb-0 fw-bold d-flex align-items-center">
                            <span class="badge bg-label-primary rounded p-2 me-2"><i
                                    class="ti tabler-list-details"></i></span>
                            List Product
                        </h5>
                        <button class="btn btn-label-primary shadow-none waves-effect btn-sm fw-bold px-3 py-2"
                            style="border-radius: 8px;"
                            onclick="$('#selectInventoryModal').modal('show'); fetchInventory();">
                            <i class="ti tabler-layers-plus me-1 fs-5"></i> Select from Inventory
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle custom-table mb-0">
                                <thead class="bg-light-subtle text-uppercase small ls-1 fw-bold">
                                    <tr>
                                        <th class="text-center" style="width: 50px;">#</th>
                                        <th>Asset Number</th>
                                        <th>Part Specification</th>
                                        <th>Serial Number</th>
                                        <th>Bin Location</th>
                                        <th class="text-center" style="width: 100px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="productTableBody">
                                    <!-- Initial empty state handled by JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top py-3 px-4 d-flex justify-content-end">
                        <div class="text-muted small">Total items: <span id="totalItemsCount"
                                class="fw-bold text-primary">0</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .custom-table thead th {
            font-size: 0.7rem;
            letter-spacing: 0.5px;
            color: #5d596c;
        }

        .bg-light-subtle {
            background-color: #f8f9fa !important;
        }

        .avatar-initial {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }

        .ls-1 {
            letter-spacing: 1px;
        }

        .form-select,
        .form-control {
            transition: all 0.2s;
        }

        .form-select:hover,
        .form-control:hover {
            background-color: #eeeef3 !important;
        }

        .form-select:focus,
        .form-control:focus {
            background-color: #fff !important;
            box-shadow: 0 0 0 0.2rem rgba(115, 103, 240, 0.1) !important;
            border-color: #7367f0 !important;
        }

        .btn-label-primary:hover {
            background-color: #e9e7fd !important;
        }

        .table> :not(caption)>*>* {
            padding: 0.85rem 1.25rem;
        }
    </style>

    @include('outbound.modals')
@endsection
