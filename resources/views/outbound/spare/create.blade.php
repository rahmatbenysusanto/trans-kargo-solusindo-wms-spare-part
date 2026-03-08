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
                request_type: document.getElementById('request_type').value,
                ntt_requestor: document.getElementById('ntt_requestor').value,
                request_date: document.getElementById('request_date').value,
                sap_po_number: document.getElementById('sap_po_number').value,
                number: document.getElementById('po_number').value,
                ntt_dn_number: document.getElementById('ntt_dn_number').value,
                tks_dn_number: document.getElementById('tks_dn_number').value,
                tks_invoice_number: document.getElementById('tks_invoice_number').value,
                rma_number: document.getElementById('rma_number').value,
                itsm_number: document.getElementById('itsm_number').value,
                client_id: document.getElementById('client_id').value,
                client_contact: document.getElementById('client_contact').value,
                pickup_address: document.getElementById('pickup_address').value,
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
                    <div class="card-body p-4"
                        style="background: linear-gradient(180deg, rgba(255,255,255,1) 0%, rgba(248,247,255,1) 100%);">
                        <div class="row g-4">
                            <!-- Left Column: Primary Transaction Info -->
                            <div class="col-md-4">
                                <div class="card border-0 shadow-sm bg-white p-3 h-100"
                                    style="border-radius: 12px; border: 1px solid rgba(115, 103, 240, 0.08) !important;">
                                    <h6 class="fw-bold mb-3 d-flex align-items-center text-primary">
                                        <i class="ti tabler-info-square-rounded me-2"></i> Transaction Detail
                                    </h6>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-dark">Stock Category <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select border-light-subtle" name="category" id="category">
                                            <option>Spare to Replacement</option>
                                            <option>Spare from Replacement</option>
                                            <option>Spare to Loan</option>
                                            <option>Spare from Loan</option>
                                            <option>Faulty</option>
                                            <option>RMA</option>
                                            <option>Spare Write-off</option>
                                            <option>Spare Migration</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-dark">Request Type</label>
                                        <select class="form-select border-light-subtle" name="request_type"
                                            id="request_type">
                                            <option value="New PO">New PO</option>
                                            <option value="RMA">RMA</option>
                                            <option value="Loan">Loan</option>
                                            <option value="Spare Write Off">Spare Write Off</option>
                                            <option value="Spare Migration">Spare Migration</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-dark">Client <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select select2" name="client_id" id="client_id">
                                            <option value="">-- Choose Client --</option>
                                            @foreach ($client as $item)
                                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label small fw-bold text-dark">Client Contact</label>
                                        <input type="text" class="form-control border-light-subtle" name="client_contact"
                                            id="client_contact" placeholder="Contact person/Dept">
                                    </div>
                                </div>
                            </div>

                            <!-- Middle Column: Request & PO Info -->
                            <div class="col-md-4">
                                <div class="card border-0 shadow-sm bg-white p-3 h-100"
                                    style="border-radius: 12px; border: 1px solid rgba(115, 103, 240, 0.08) !important;">
                                    <h6 class="fw-bold mb-3 d-flex align-items-center text-primary">
                                        <i class="ti tabler-file-description me-2"></i> Request & PO
                                    </h6>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-dark">NTT Requestor</label>
                                        <input type="text" class="form-control border-light-subtle" name="ntt_requestor"
                                            id="ntt_requestor" placeholder="Name/Dept">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-dark">Request Date</label>
                                        <input type="date" class="form-control border-light-subtle" name="request_date"
                                            id="request_date" value="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-dark">SAP PO#</label>
                                        <input type="text" class="form-control border-light-subtle fw-bold text-primary"
                                            name="sap_po_number" id="sap_po_number" placeholder="Enter SAP PO">
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label small fw-bold text-dark">PO# (System Ref)</label>
                                        <input type="text" class="form-control border-light-subtle" name="po_number"
                                            id="po_number" placeholder="Enter Reference">
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Shipping & Logs -->
                            <div class="col-md-4">
                                <div class="card border-0 shadow-sm bg-white p-3 h-100"
                                    style="border-radius: 12px; border: 1px solid rgba(115, 103, 240, 0.08) !important;">
                                    <h6 class="fw-bold mb-3 d-flex align-items-center text-primary">
                                        <i class="ti tabler-truck-delivery me-2"></i> Dispatch & Shipping
                                    </h6>
                                    <div class="row g-2">
                                        <div class="col-6 mb-2">
                                            <label class="form-label small fw-bold text-dark">NTT DN#</label>
                                            <input type="text" class="form-control form-control-sm border-light-subtle"
                                                name="ntt_dn_number" id="ntt_dn_number" placeholder="NTT DN">
                                        </div>
                                        <div class="col-6 mb-2">
                                            <label class="form-label small fw-bold text-dark">TKS DN#</label>
                                            <input type="text" class="form-control form-control-sm border-light-subtle"
                                                name="tks_dn_number" id="tks_dn_number" placeholder="TKS DN">
                                        </div>
                                        <div class="col-6 mb-2">
                                            <label class="form-label small fw-bold text-dark">RMA#</label>
                                            <input type="text" class="form-control form-control-sm border-light-subtle"
                                                id="rma_number" placeholder="RMA#">
                                        </div>
                                        <div class="col-6 mb-2">
                                            <label class="form-label small fw-bold text-dark">ITSM#</label>
                                            <input type="text" class="form-control form-control-sm border-light-subtle"
                                                id="itsm_number" placeholder="ITSM#">
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small fw-bold text-primary">Delivery Date *</label>
                                        <input type="date"
                                            class="form-control form-control-sm border-primary-subtle fw-bold"
                                            name="date" id="date" value="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label small fw-bold text-primary">Processed By *</label>
                                        <input type="text"
                                            class="form-control form-control-sm border-primary-subtle fw-bold"
                                            name="outbound_by" id="outbound_by" placeholder="Person in charge">
                                    </div>
                                </div>
                            </div>

                            <!-- Full Width Column: Address -->
                            <div class="col-12">
                                <div class="card border-0 shadow-sm p-3"
                                    style="border-radius: 12px; background: rgba(115, 103, 240, 0.03); border: 1px dashed rgba(115, 103, 240, 0.2) !important;">
                                    <label class="form-label small fw-bold text-primary d-flex align-items-center">
                                        <i class="ti tabler-map-pin me-2"></i> Pick up / Shipment Address
                                    </label>
                                    <textarea class="form-control border-0 bg-transparent p-0" name="pickup_address" id="pickup_address" rows="2"
                                        placeholder="Write full delivery address here..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product List Section -->
            <div class="col-12">
                <div class="card border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                    <div class="card-header bg-white py-4 px-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1 fw-bold text-dark">
                                <i class="ti tabler-box-seam me-2 text-primary"></i> Selected Items
                            </h5>
                            <p class="text-muted small mb-0">Total items: <span id="totalItemsCount"
                                    class="fw-bold text-primary">0</span></p>
                        </div>
                        <button class="btn btn-primary shadow-sm fw-bold px-4 py-2" style="border-radius: 10px;"
                            onclick="$('#selectInventoryModal').modal('show'); fetchInventory();">
                            <i class="ti tabler-plus me-2 fs-5"></i> Add Products
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle custom-table mb-0">
                                <thead class="bg-label-primary text-uppercase small fw-bold">
                                    <tr>
                                        <th class="text-center" style="width: 60px;">#</th>
                                        <th>Asset info</th>
                                        <th>Specification</th>
                                        <th>Serial Number</th>
                                        <th>Location</th>
                                        <th class="text-center" style="width: 100px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="productTableBody">
                                    <!-- Initial empty state handled by JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .custom-table thead th {
            font-size: 0.75rem;
            letter-spacing: 0.8px;
            color: #7367f0;
            background: rgba(115, 103, 240, 0.05);
            border-bottom: 2px solid rgba(115, 103, 240, 0.1);
            padding: 1.2rem;
        }

        .avatar-initial {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }

        .form-select,
        .form-control {
            border-radius: 8px;
            padding: 0.6rem 1rem;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .form-select:focus,
        .form-control:focus {
            box-shadow: 0 0 15px rgba(115, 103, 240, 0.15) !important;
            border-color: #7367f0 !important;
            transform: translateY(-1px);
        }

        .card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.05) !important;
        }

        .btn-primary {
            background: linear-gradient(135deg, #7367f0 0%, #4834d4 100%);
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(115, 103, 240, 0.4);
        }

        .btn-label-secondary {
            background: rgba(130, 134, 139, 0.1);
            color: #82868b;
            border: none;
        }

        .table> :not(caption)>*>* {
            padding: 1rem 1.2rem;
        }
    </style>

    @include('outbound.modals')
@endsection
