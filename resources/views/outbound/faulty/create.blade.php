@extends('layout.index')
@section('title', 'Create Outbound Faulty')
@section('layout_class', 'layout-menu-collapsed')

@section('js')
    <script>
        localStorage.clear();

        function renderProducts() {
            const products = JSON.parse(localStorage.getItem('outbound_f_products')) ?? [];
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
                            <small class="text-muted text-uppercase fw-medium">Choose faulty items from stock.</small>
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
                                <span class="avatar-initial rounded-circle bg-label-danger font-small"><i class="ti tabler-barcode fs-6"></i></span>
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
                    const products = JSON.parse(localStorage.getItem('outbound_f_products')) ?? [];
                    products.splice(index, 1);
                    localStorage.setItem('outbound_f_products', JSON.stringify(products));
                    renderProducts();
                }
            });
        }

        function submitOutbound() {
            const products = JSON.parse(localStorage.getItem('outbound_f_products')) ?? [];
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
                category: 'Faulty',
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
                title: 'Confirm Outbound Faulty?',
                text: "This will move selected items to Faulty/Outbound status.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="ti tabler-device-floppy me-1"></i> Save Faulty Record',
                customClass: {
                    confirmButton: 'btn btn-danger me-1',
                    cancelButton: 'btn btn-label-secondary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('{{ route('outbound.store.faulty') }}', {
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
                                localStorage.removeItem('outbound_f_products');
                                Swal.fire({
                                    title: 'Success!',
                                    text: 'Outbound Faulty recorded.',
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
            const products = JSON.parse(localStorage.getItem('outbound_f_products')) ?? [];
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

            localStorage.setItem('outbound_f_products', JSON.stringify(products));
            renderProducts();
        };

        renderProducts();
    </script>
@endsection

@section('content')
    <div class="container-fluid flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12 text-end mb-4">
                <a href="{{ route('outbound.index') }}" class="btn btn-label-secondary waves-effect me-2">
                    <i class="ti tabler-arrow-left me-1"></i> Cancel
                </a>
                <button class="btn btn-danger shadow-sm waves-effect waves-light" onclick="submitOutbound()">
                    <i class="ti tabler-device-floppy me-1"></i> Create Outbound Faulty
                </button>
            </div>

            <div class="col-12">
                <div class="card mb-4 border-0 shadow-sm overflow-hidden" style="border-radius: 12px;">
                    <div class="card-header bg-danger p-1"></div>
                    <div class="card-body p-4 bg-white">
                        <div class="row g-4 pt-2">
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-muted small text-uppercase ls-1">Category</label>
                                <input type="text" class="form-control border-0 bg-light fw-bold text-danger mb-0"
                                    value="Faulty" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-muted small text-uppercase ls-1">Client *</label>
                                <select class="form-select border-0 bg-light fw-bold" id="client_id">
                                    <option value="">-- Choose Client --</option>
                                    @foreach ($client as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-muted small text-uppercase ls-1">Outbound Date
                                    *</label>
                                <input type="date" class="form-control border-0 bg-light fw-bold" id="date"
                                    value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-muted small text-uppercase ls-1">Outbound By *</label>
                                <input type="text" class="form-control border-0 bg-light fw-bold text-primary"
                                    id="outbound_by" placeholder="Assignee name">
                            </div>

                            <div class="col-12">
                                <hr class="my-1 border-light">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small text-muted text-uppercase fw-bold">PO# (Optional)</label>
                                <input type="text" class="form-control border-0 bg-light" id="po_number"
                                    placeholder="PO# ...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted text-uppercase fw-bold">NTT DN#</label>
                                <input type="text" class="form-control border-0 bg-light" id="ntt_dn_number"
                                    placeholder="Enter NTT DN">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted text-uppercase fw-bold">TKS DN#</label>
                                <input type="text" class="form-control border-0 bg-light" id="tks_dn_number"
                                    placeholder="Enter TKS DN">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted text-uppercase fw-bold">TKS Invoice#</label>
                                <input type="text" class="form-control border-0 bg-light" id="tks_invoice_number"
                                    placeholder="Enter Invoice">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div
                        class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                        <h5 class="mb-0 fw-bold d-flex align-items-center">
                            <span class="badge bg-label-danger rounded p-2 me-2"><i
                                    class="ti tabler-alert-square-rounded"></i></span>
                            Faulty Unit List
                        </h5>
                        <button class="btn btn-label-primary shadow-none waves-effect btn-sm fw-bold"
                            onclick="$('#selectInventoryModal').modal('show'); fetchInventory();">
                            <i class="ti tabler-zoom-in-filled me-1"></i> Browse Faulty Units
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle custom-table mb-0">
                                <thead class="bg-light text-uppercase fs-tiny fw-bold border-top-0">
                                    <tr>
                                        <th class="text-center" style="width: 50px;">#</th>
                                        <th>Asset Number</th>
                                        <th>Unit Specification</th>
                                        <th>Serial Number</th>
                                        <th>Last Location</th>
                                        <th class="text-center" style="width: 100px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="productTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top py-3 px-4 d-flex justify-content-end align-items-center">
                        <div class="small fw-bold text-muted">Items Ready for Outbound: <span id="totalItemsCount"
                                class="text-danger fs-5 ms-2">0</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .custom-table thead th {
            font-size: 0.65rem;
            letter-spacing: 1px;
            color: #82868b;
            padding: 0.75rem 1.25rem;
        }

        .fs-tiny {
            font-size: 0.7rem;
        }

        .ls-1 {
            letter-spacing: 1px;
        }

        .form-control,
        .form-select {
            padding: 0.55rem 0.9rem;
            transition: all 0.2s ease;
        }

        .form-control:read-only {
            background-color: #fceeee !important;
        }

        .table> :not(caption)>*>* {
            padding: 1rem 1.25rem;
        }
    </style>

    @include('outbound.modals')
@endsection
