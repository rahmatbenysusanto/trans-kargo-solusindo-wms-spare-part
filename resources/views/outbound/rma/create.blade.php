@extends('layout.index')
@section('title', 'Create Outbound RMA')
@section('layout_class', 'layout-menu-collapsed')

@section('js')
    <script>
        localStorage.clear();

        function renderProducts() {
            const products = JSON.parse(localStorage.getItem('outbound_rma_products')) ?? [];
            const tbody = document.getElementById('productTableBody');
            const totalCount = document.getElementById('totalItemsCount');
            if (totalCount) totalCount.innerText = products.length;
            tbody.innerHTML = '';

            if (products.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="mb-2"><i class="ti tabler-refresh-dot text-light shadow-sm bg-label-warning rounded p-3 fs-1"></i></div>
                            <h5 class="text-muted mb-0">RMA list is currently empty.</h5>
                            <small class="text-muted text-uppercase fw-medium ls-sm">Select items for replacement or return.</small>
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
                    <td>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0"><i class="ti tabler-id text-warning"></i></span>
                            <input type="text" class="form-control border-start-0 ps-0 bg-light-subtle" placeholder="New Serial..." 
                                value="${product.oldSerialNumber || ''}" onchange="updateOldSn(${index}, this.value)">
                        </div>
                    </td>
                    <td><span class="badge bg-label-warning text-dark border-0"><i class="ti tabler-map-pin me-1 fs-tiny"></i> ${product.location}</span></td>
                    <td class="text-center">
                        <button class="btn btn-label-danger btn-icon btn-sm rounded-circle shadow-none waves-effect" onclick="deleteProduct(${index})">
                            <i class="ti tabler-trash-x fs-5"></i>
                        </button>
                    </td>
                </tr>`;
                tbody.innerHTML += row;
            });
        }

        function updateOldSn(index, value) {
            const products = JSON.parse(localStorage.getItem('outbound_rma_products')) ?? [];
            if (products[index]) {
                products[index].oldSerialNumber = value;
                localStorage.setItem('outbound_rma_products', JSON.stringify(products));
            }
        }

        function deleteProduct(index) {
            Swal.fire({
                title: 'Remove from RMA?',
                text: "Item will be removed from this return batch",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, remove',
                customClass: {
                    confirmButton: 'btn btn-danger me-1',
                    cancelButton: 'btn btn-label-secondary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    const products = JSON.parse(localStorage.getItem('outbound_rma_products')) ?? [];
                    products.splice(index, 1);
                    localStorage.setItem('outbound_rma_products', JSON.stringify(products));
                    renderProducts();
                }
            });
        }

        function submitOutbound() {
            const products = JSON.parse(localStorage.getItem('outbound_rma_products')) ?? [];
            if (products.length === 0) {
                Swal.fire({
                    title: 'RMA Empty',
                    text: 'Select at least one unit.',
                    icon: 'error',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                });
                return;
            }

            const rmaNo = document.getElementById('rma_number').value;
            const itsmNo = document.getElementById('itsm_number').value;
            const clientId = document.getElementById('client_id').value;

            if (!rmaNo || !itsmNo || !clientId) {
                Swal.fire({
                    title: 'Validation Failed',
                    text: 'RMA#, ITSM#, and Client are essential.',
                    icon: 'warning',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                });
                return;
            }

            const data = {
                category: 'RMA',
                rma_number: rmaNo,
                itsm_number: itsmNo,
                number: rmaNo,
                client_id: clientId,
                outbound_date: document.getElementById('date').value,
                outbound_by: document.getElementById('outbound_by').value,
                products
            };

            Swal.fire({
                title: 'Process RMA Outbound?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Proceed',
                customClass: {
                    confirmButton: 'btn btn-warning me-1',
                    cancelButton: 'btn btn-label-secondary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('{{ route('outbound.store.rma') }}', {
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
                                localStorage.removeItem('outbound_rma_products');
                                Swal.fire({
                                    title: 'Success!',
                                    text: 'RMA recorded.',
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
            const products = JSON.parse(localStorage.getItem('outbound_rma_products')) ?? [];
            if (products.some(p => p.product_id === item.id)) return;

            products.push({
                product_id: item.id,
                unique_id: item.unique_id,
                partName: item.part_name,
                partNumber: item.part_number,
                partDescription: item.part_description,
                serialNumber: item.serial_number,
                oldSerialNumber: '',
                location: item.location
            });

            localStorage.setItem('outbound_rma_products', JSON.stringify(products));
            renderProducts();
        };

        renderProducts();
    </script>
@endsection

@section('content')
    <div class="container-fluid flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12 mb-4 d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1 text-warning fw-bold d-flex align-items-center"><i
                            class="ti tabler-refresh me-2 fs-2"></i> RMA Outbound Process</h4>
                    <p class="text-muted mb-0 small text-uppercase ls-1 fw-medium mt-n1">Returning or replacing defective
                        units</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('outbound.index') }}" class="btn btn-label-secondary waves-effect btn-sm py-2">
                        <i class="ti tabler-arrow-left me-1"></i> Cancel
                    </a>
                    <button class="btn btn-warning shadow-sm waves-effect btn-sm py-2 px-3 fw-bold"
                        onclick="submitOutbound()">
                        <i class="ti tabler-circle-check me-1"></i> Finalize RMA
                    </button>
                </div>
            </div>

            <div class="col-12">
                <div class="card mb-4 border-0 shadow-sm"
                    style="border-radius: 12px; border-left: 5px solid #ff9f43 !important;">
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small text-muted text-uppercase fw-bold">Client *</label>
                                <select class="form-select border-0 bg-light-subtle fw-bold" id="client_id">
                                    <option value="">-- Select Client --</option>
                                    @foreach ($client as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted text-uppercase fw-bold">RMA Case Number *</label>
                                <input type="text"
                                    class="form-control border-0 bg-light-subtle fw-bold placeholder-light" id="rma_number"
                                    placeholder="Enter RMA#">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted text-uppercase fw-bold">ITSM Ticket ID *</label>
                                <input type="text"
                                    class="form-control border-0 bg-light-subtle fw-bold placeholder-light" id="itsm_number"
                                    placeholder="Enter ITSM#">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted text-uppercase fw-bold">Outbound Date</label>
                                <input type="date" class="form-control border-0 bg-light-subtle fw-bold" id="date"
                                    value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-3 mt-3">
                                <label class="form-label small text-muted text-uppercase fw-bold">Outbound By</label>
                                <input type="text" class="form-control border-0 bg-light-subtle" id="outbound_by"
                                    placeholder="Assignee name">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 12px;">
                    <div
                        class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                        <h5 class="mb-0 fw-bold d-flex align-items-center">
                            <span class="badge bg-label-warning rounded p-2 me-2 shadow-xs"><i
                                    class="ti tabler-box-margin text-warning"></i></span>
                            Units to Return
                        </h5>
                        <button class="btn btn-label-primary shadow-none btn-sm fw-bold px-3 py-2 waves-effect"
                            onclick="$('#selectInventoryModal').modal('show'); fetchInventory();">
                            <i class="ti tabler-barcode me-1 fs-5"></i> Scan Inventory
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle custom-table mb-0">
                                <thead class="bg-light text-uppercase fs-tiny fw-bold border-top-0">
                                    <tr>
                                        <th class="text-center" style="width: 50px;">#</th>
                                        <th>Asset ID</th>
                                        <th>Unit Details</th>
                                        <th>Old Serial</th>
                                        <th>ITSM Replace SN</th>
                                        <th>From Location</th>
                                        <th class="text-center" style="width: 100px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="productTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top py-3 px-4 d-flex justify-content-end align-items-center">
                        <div class="small fw-bold text-muted d-flex align-items-center">
                            Total Units for RMA: <span id="totalItemsCount"
                                class="text-warning fs-5 ms-2 animate__animated animate__pulse animate__infinite">0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .custom-table thead th {
            font-size: 0.65rem;
            letter-spacing: 1px;
            color: #5d596c;
            padding: 1rem 1.25rem;
        }

        .bg-light-subtle {
            background-color: #f7f8f9 !important;
        }

        .placeholder-light::placeholder {
            color: #d0d2d5;
            font-weight: 300;
        }

        .table> :not(caption)>*>* {
            padding: 1rem 1.25rem;
        }

        .ls-sm {
            letter-spacing: 0.5px;
        }

        .avatar-initial {
            background-color: rgba(255, 159, 67, 0.1) !important;
            color: #ff9f43 !important;
        }
    </style>

    @include('outbound.modals')
@endsection
