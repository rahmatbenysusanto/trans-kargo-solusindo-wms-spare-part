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
                client_id: client_id,
                outbound_date: document.getElementById('date').value,
                outbound_by: document.getElementById('outbound_by').value,
                products: products.map(p => ({
                    ...p,
                    condition: 'Scrap'
                }))
            };

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
                    <div class="card-body p-4">
                        <div class="row g-4 pt-1 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small text-muted text-uppercase fw-bold">Target Client *</label>
                                <select class="form-select border-0 bg-white shadow-xs fw-bold py-2" id="client_id">
                                    <option value="">-- Choose Client --</option>
                                    @foreach ($client as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted text-uppercase fw-bold">Disposal Date</label>
                                <input type="date" class="form-control border-0 bg-white shadow-xs fw-bold py-2"
                                    id="date" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted text-uppercase fw-bold">Authorized By</label>
                                <input type="text" class="form-control border-0 bg-white shadow-xs py-2 fw-medium"
                                    id="outbound_by" placeholder="Name of authorize person">
                            </div>
                            <div class="col-md-3 text-end">
                                <img src="https://img.icons8.com/isometric/100/trash.png" alt="trash"
                                    style="width: 60px; opacity: 0.6;">
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
                            <span class="badge bg-label-secondary rounded p-2 me-2 shadow-xs"><i
                                    class="ti tabler-list-check text-dark"></i></span>
                            Disposal Queue
                        </h5>
                        <button class="btn btn-label-dark shadow-none btn-sm fw-bold px-4 py-2 waves-effect border"
                            onclick="$('#selectInventoryModal').modal('show'); fetchInventory();">
                            <i class="ti tabler-package-export me-1 fs-5"></i> Select Units
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle custom-table mb-0">
                                <thead class="bg-light text-uppercase fs-tiny fw-bold border-top-0">
                                    <tr>
                                        <th class="text-center" style="width: 50px;">#</th>
                                        <th>Asset ID</th>
                                        <th>Unit Specification</th>
                                        <th>Serial Number</th>
                                        <th>Last Location</th>
                                        <th>Action Category</th>
                                        <th class="text-center" style="width: 100px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="productTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top py-3 px-4 d-flex justify-content-end align-items-center">
                        <div class="small fw-bold text-muted d-flex align-items-center">
                            Total Units for Disposal: <span id="totalItemsCount" class="text-danger fs-4 ms-2">0</span>
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
            color: #82868b;
            padding: 1rem 1.25rem;
        }

        .ls-1 {
            letter-spacing: 1px;
        }

        .shadow-xs {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .placeholder-light::placeholder {
            color: #d0d2d5;
            font-weight: 300;
        }

        .table> :not(caption)>*>* {
            padding: 1rem 1.25rem;
        }

        .fs-tiny {
            font-size: 0.7rem;
        }
    </style>

    @include('outbound.modals')
@endsection
