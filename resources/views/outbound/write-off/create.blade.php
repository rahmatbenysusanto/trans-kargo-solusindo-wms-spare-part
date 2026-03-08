@extends('layout.index')
@section('title', 'Create Outbound Write-off')
@section('layout_class', 'layout-menu-collapsed')

@section('js')
    <script>
        localStorage.clear();

        function renderProducts() {
            const products = JSON.parse(localStorage.getItem('outbound_products_wo')) ?? [];
            const tbody = document.getElementById('productTableBody');
            const totalCount = document.getElementById('totalItemsCount');
            if (totalCount) totalCount.innerText = products.length;
            tbody.innerHTML = '';

            if (products.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="mb-2"><i class="ti tabler-trash-off text-light shadow-sm bg-label-secondary rounded p-3 fs-1"></i></div>
                            <h5 class="text-muted mb-0">Write-off list is empty.</h5>
                            <small class="text-muted text-uppercase fw-medium ls-sm">Select items for permanent disposal.</small>
                        </td>
                    </tr>`;
                return;
            }

            products.forEach((product, index) => {
                const row = `
                <tr class="animate__animated animate__fadeIn">
                    <td class="text-center">${index + 1}</td>
                    <td><span class="badge bg-label-dark fw-bold border-0 px-3 py-2 rounded-3 shadow-xs">${product.unique_id}</span></td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-dark">${product.partName}</span>
                            <small class="text-muted font-small">${product.partNumber}</small>
                        </div>
                    </td>
                    <td><span class="fw-medium">${product.serialNumber}</span></td>
                    <td><span class="badge bg-label-info border-0"><i class="ti tabler-map-pin me-1 fs-tiny"></i> ${product.location}</span></td>
                    <td><span class="badge bg-label-danger border-0 animate__animated animate__pulse animate__infinite">Write-off</span></td>
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
                title: 'Keep item?',
                text: "Don't write-off this item yet?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, remove',
                customClass: {
                    confirmButton: 'btn btn-danger me-1',
                    cancelButton: 'btn btn-label-secondary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    const products = JSON.parse(localStorage.getItem('outbound_products_wo')) ?? [];
                    products.splice(index, 1);
                    localStorage.setItem('outbound_products_wo', JSON.stringify(products));
                    renderProducts();
                }
            });
        }

        function submitOutbound() {
            const products = JSON.parse(localStorage.getItem('outbound_products_wo')) ?? [];
            if (products.length === 0) {
                Swal.fire({
                    title: 'Error',
                    text: 'Select at least one product.',
                    icon: 'error',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                });
                return;
            }

            const client_id = document.getElementById('client_id').value;
            if (!client_id) {
                Swal.fire({
                    title: 'Missing Client',
                    text: 'Please select a Client.',
                    icon: 'warning',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                });
                return;
            }

            const data = {
                category: 'Write-off',
                request_type: 'Spare Write Off',
                ntt_requestor: document.getElementById('ntt_requestor') ? document.getElementById('ntt_requestor')
                    .value : '',
                request_date: document.getElementById('request_date') ? document.getElementById('request_date').value :
                    '',
                sap_po_number: document.getElementById('sap_po_number') ? document.getElementById('sap_po_number')
                    .value : '',
                client_id: client_id,
                client_contact: document.getElementById('client_contact') ? document.getElementById('client_contact')
                    .value : '',
                pickup_address: document.getElementById('pickup_address') ? document.getElementById('pickup_address')
                    .value : '',
                outbound_date: document.getElementById('date').value,
                outbound_by: document.getElementById('outbound_by').value,
                products: products.map(p => ({
                    ...p,
                    condition: 'Scrap'
                }))
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
                title: 'Permanent Write-off?',
                text: "This action will permanently remove these items from active inventory.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Confirm Disposal',
                customClass: {
                    confirmButton: 'btn btn-dark me-1',
                    cancelButton: 'btn btn-label-secondary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('{{ route('outbound.store.write-off') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(data)
                        })
                        .then(r => r.json())
                        .then(res => {
                            if (res.status) {
                                localStorage.removeItem('outbound_products_wo');
                                Swal.fire({
                                    title: 'Success!',
                                    text: 'Write-off recorded.',
                                    icon: 'success',
                                    customClass: {
                                        confirmButton: 'btn btn-primary'
                                    },
                                    buttonsStyling: false
                                }).then(() => window.location.href = '{{ route('outbound.index') }}');
                            } else Swal.fire('Error', res.message, 'error');
                        });
                }
            });
        }

        window.onReceivePickedItem = function(item) {
            const products = JSON.parse(localStorage.getItem('outbound_products_wo')) ?? [];
            if (products.some(p => p.product_id === item.id)) return;

            products.push({
                product_id: item.id,
                unique_id: item.unique_id,
                partName: item.part_name,
                partNumber: item.part_number,
                partDescription: item.part_description,
                serialNumber: item.serial_number,
                brand: item.brand,
                productGroup: item.product_group,
                location: item.location
            });

            localStorage.setItem('outbound_products_wo', JSON.stringify(products));
            renderProducts();
        };

        renderProducts();
    </script>
@endsection

@section('content')
    <div class="container-fluid flex-grow-1 container-p-y animate__animated animate__fadeIn">
        <div class="row">
            <div class="col-12 mb-4 d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1 text-dark fw-bold d-flex align-items-center"><i
                            class="ti tabler-trash-x me-2 fs-2 text-danger"></i> Inventory Write-off</h4>
                    <p class="text-muted mb-0 small text-uppercase ls-1 fw-medium mt-n1">Disposal of damaged or obsolete
                        units</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('outbound.index') }}" class="btn btn-label-secondary waves-effect btn-sm py-2">
                        <i class="ti tabler-arrow-left me-1"></i> Back
                    </a>
                    <button class="btn btn-dark shadow-sm waves-effect btn-sm py-2 px-3 fw-bold" onclick="submitOutbound()">
                        <i class="ti tabler-archive me-1 text-danger"></i> Process Disposal
                    </button>
                </div>
            </div>

            <div class="col-12">
                <div class="card mb-4 border-0 shadow-sm"
                    style="border-radius: 12px; background: linear-gradient(135deg, #fff 0%, #fafafa 100%);">
                    <div class="card-body p-4 bg-white">
                        <div class="row g-4 pt-2">
                            <!-- Left Column: Primary Transaction Info -->
                            <div class="col-md-4">
                                <div class="card border-0 shadow-sm bg-white p-3 h-100"
                                    style="border-radius: 12px; border: 1px solid rgba(130, 134, 139, 0.1) !important;">
                                    <h6 class="fw-bold mb-3 d-flex align-items-center text-secondary">
                                        <i class="ti tabler-trash me-2"></i> Transaction Detail
                                    </h6>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-dark">Outbound Category</label>
                                        <input type="text"
                                            class="form-control fw-bold text-dark border-light-subtle bg-light-subtle"
                                            value="Spare Write-off" readonly id="category">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-dark">Request Type</label>
                                        <select class="form-select border-light-subtle" name="request_type"
                                            id="request_type">
                                            <option value="Spare Write Off" selected>Spare Write Off</option>
                                            <option value="New PO">New PO</option>
                                            <option value="RMA">RMA</option>
                                            <option value="Loan">Loan</option>
                                            <option value="Spare Migration">Spare Migration</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-dark">Client <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select border-light-subtle select2" id="client_id">
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
                                        <i class="ti tabler-file-description me-2"></i> Request Info
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
                                        <label class="form-label small fw-bold text-dark">SAP PO# (Optional)</label>
                                        <input type="text" class="form-control border-light-subtle" name="sap_po_number"
                                            id="sap_po_number" placeholder="Enter SAP PO">
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
                                        <i class="ti tabler-calendar-event me-2"></i> Processing Info
                                    </h6>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-primary">Write-off Date *</label>
                                        <input type="date" class="form-control border-primary-subtle fw-bold"
                                            name="date" id="date" value="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-primary">Processed By *</label>
                                        <input type="text" class="form-control border-primary-subtle fw-bold"
                                            name="outbound_by" id="outbound_by" placeholder="PIC Name">
                                    </div>
                                    <div class="mb-0">
                                        <p class="small text-muted mb-0">Note: Items in write-off will be removed from
                                            active inventory permanently.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <div class="card border-0 shadow-sm p-3"
                                    style="border-radius: 12px; background: rgba(130, 134, 139, 0.03); border: 1px dashed rgba(130, 134, 139, 0.2) !important;">
                                    <label class="form-label small fw-bold text-secondary d-flex align-items-center">
                                        <i class="ti tabler-message-dots me-2"></i> Write-off Remarks / Reason
                                    </label>
                                    <textarea class="form-control border-0 bg-transparent p-1" name="pickup_address" id="pickup_address" rows="2"
                                        placeholder="Explain the reason for disposal/write-off..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Unit List Section -->
            <div class="col-12">
                <div class="card border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                    <div
                        class="card-header bg-white py-4 px-4 d-flex justify-content-between align-items-center border-bottom">
                        <div>
                            <h5 class="mb-1 fw-bold text-dark">
                                <i class="ti tabler-package-off me-2 text-secondary"></i> Items to Write-off
                            </h5>
                            <p class="text-muted small mb-0">Total items: <span id="totalItemsCount"
                                    class="fw-bold text-primary">0</span></p>
                        </div>
                        <button class="btn btn-primary shadow-sm fw-bold px-4 py-2"
                            onclick="$('#selectInventoryModal').modal('show'); fetchInventory();">
                            <i class="ti tabler-search me-2"></i> Find Items
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle custom-table mb-0">
                                <thead class="bg-label-secondary text-uppercase small fw-bold">
                                    <tr>
                                        <th class="text-center" style="width: 60px;">#</th>
                                        <th>Asset Info</th>
                                        <th>Specification/Condition</th>
                                        <th>Serial Number</th>
                                        <th>Location</th>
                                        <th class="text-center" style="width: 100px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="productTableBody"></tbody>
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
            color: #82868b;

            .fs-tiny {
                font-size: 0.7rem;
            }
    </style>

    @include('outbound.modals')
@endsection
