@extends('layout.index')
@section('title', 'Edit Receiving')
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
    <script>
        $(document).ready(function() {
            $('#client_id').select2({
                placeholder: "-- Choose Client --",
                allowClear: true,
                width: '100%'
            });
        });

        function toggleFields() {
            const category = document.getElementById('category').value;

            // Hide all dynamic fields first
            $('.field-ntt-requestor, .field-request-date, .field-sttb, .field-ntt-rn, .field-sap-po, .field-ecapex, .field-rma, .field-itsm, .field-tks-dn, .field-tks-inv, .field-vendor-dn, .field-po-ref, .field-client, .field-client-contact, .field-pickup-address, .field-vendor, .field-courier-dn, .field-courier-inv, .field-received-date, .field-received-by, .field-remarks')
                .hide();

            if (category === 'New PO') {
                $('.field-ecapex, .field-sap-po, .field-vendor-dn, .field-received-date, .field-received-by').show();
            } else if (category === 'Spare Migration') {
                $('.field-received-date, .field-received-by, .field-remarks').show();
            } else if (category === 'Faulty') {
                $('.field-ntt-requestor, .field-request-date, .field-ntt-rn, .field-received-date, .field-tks-dn, .field-tks-inv, .field-itsm, .field-received-by, .field-client, .field-client-contact, .field-pickup-address, .field-remarks')
                    .show();
            } else if (category === 'RMA') {
                $('.field-ntt-requestor, .field-request-date, .field-vendor-dn, .field-received-date, .field-itsm, .field-rma, .field-received-by, .field-client, .field-remarks')
                    .show();
            } else if (category === 'Spare from/to Replacement') {
                $('.field-ntt-requestor, .field-request-date, .field-ntt-rn, .field-received-date, .field-tks-dn, .field-tks-inv, .field-itsm, .field-received-by, .field-client, .field-client-contact, .field-pickup-address, .field-remarks')
                    .show();
            } else if (category === 'Spare from/to Loan') {
                $('.field-ntt-requestor, .field-request-date, .field-vendor-dn, .field-ntt-rn, .field-tks-dn, .field-tks-inv, .field-itsm, .field-received-by, .field-client, .field-client-contact, .field-pickup-address, .field-remarks')
                    .show();
            } else if (category === 'Spare Write-off') {
                $('.field-received-date, .field-remarks').show();
            }
        }

        async function submitUpdate() {
            const data = {
                category: document.getElementById('category').value,
                request_type: document.getElementById('request_type').value,
                ntt_requestor: document.getElementById('ntt_requestor').value,
                request_date: document.getElementById('request_date').value,
                client_id: document.getElementById('client_id').value,
                client_contact: document.getElementById('client_contact').value,
                pickup_address: document.getElementById('pickup_address').value,
                number: document.getElementById('number').value,
                po_number: document.getElementById('po_number').value,
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
                receivedDate: document.getElementById('date').value,
                receivedBy: document.getElementById('received_by').value,
                remarks: document.getElementById('remarks').value,
            };

            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to update this Receiving transaction?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, update it!'
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
                        const response = await fetch('{{ route('receiving.update', $inbound->id) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(data)
                        });

                        const resultData = await response.json();

                        if (resultData.status) {
                            Swal.fire({
                                title: 'Success!',
                                text: 'Receiving updated successfully.',
                                icon: 'success'
                            }).then(() => {
                                window.location.href = '{{ route('receiving') }}';
                            });
                        } else {
                            Swal.fire('Error', resultData.message || 'Failed to update receiving.',
                                'error');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        Swal.fire('Error', 'An unexpected error occurred.', 'error');
                    }
                }
            });
        }

        $(document).ready(function() {
            toggleFields();
        });
    </script>
@endsection

@section('content')
    <div class="row">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0">
                Edit Receiving - <span class="text-primary">{{ $inbound->number }}</span>
                <span class="badge bg-label-info badge-sm ms-2" style="font-size: 0.72rem;">{{ strtoupper($inbound->status) }}</span>
            </h4>
            <div>
                <a href="{{ route('receiving.show', $inbound->id) }}" class="btn btn-sm btn-label-secondary me-1">
                    <i class="ti tabler-arrow-left me-1"></i> Back
                </a>
                <button class="btn btn-primary" onclick="submitUpdate()">
                    <i class="ti tabler-device-floppy me-1"></i> Update Receiving
                </button>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Stock Category</label>
                            <select class="form-control" name="category" id="category" disabled>
                                <option value="New PO" {{ $inbound->category == 'New PO' ? 'selected' : '' }}>New PO</option>
                                <option value="Spare from/to Replacement" {{ $inbound->category == 'Spare from/to Replacement' ? 'selected' : '' }}>Spare from/to Replacement</option>
                                <option value="Spare from/to Loan" {{ $inbound->category == 'Spare from/to Loan' ? 'selected' : '' }}>Spare from/to Loan</option>
                                <option value="Faulty" {{ $inbound->category == 'Faulty' ? 'selected' : '' }}>Faulty</option>
                                <option value="RMA" {{ $inbound->category == 'RMA' ? 'selected' : '' }}>RMA</option>
                                <option value="Spare Write-off" {{ $inbound->category == 'Spare Write-off' ? 'selected' : '' }}>Spare Write-off</option>
                                <option value="Spare Migration" {{ $inbound->category == 'Spare Migration' ? 'selected' : '' }}>Spare Migration</option>
                            </select>
                            <input type="hidden" name="category" value="{{ $inbound->category }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Request Type</label>
                            <select class="form-control" name="request_type" id="request_type">
                                <option value="New PO" {{ $inbound->request_type == 'New PO' ? 'selected' : '' }}>New PO</option>
                                <option value="RMA" {{ $inbound->request_type == 'RMA' ? 'selected' : '' }}>RMA</option>
                                <option value="Loan" {{ $inbound->request_type == 'Loan' ? 'selected' : '' }}>Loan</option>
                                <option value="Spare Write Off" {{ $inbound->request_type == 'Spare Write Off' ? 'selected' : '' }}>Spare Write Off</option>
                                <option value="Spare Migration" {{ $inbound->request_type == 'Spare Migration' ? 'selected' : '' }}>Spare Migration</option>
                                <option value="Return" {{ $inbound->request_type == 'Return' ? 'selected' : '' }}>Return</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3 field-ntt-requestor" style="display:none;">
                            <label class="form-label">NTT Requestor</label>
                            <input type="text" class="form-control" id="ntt_requestor" placeholder="Requestor Name ..." value="{{ $inbound->ntt_requestor }}">
                        </div>
                        <div class="col-md-3 mb-3 field-request-date" style="display:none;">
                            <label class="form-label">Request Date</label>
                            <input type="date" class="form-control" id="request_date" value="{{ $inbound->request_date ?? date('Y-m-d') }}">
                        </div>

                        <div class="col-md-3 mb-3 field-sttb">
                            <label class="form-label">STTB / Ref Number</label>
                            <input type="text" class="form-control" name="sttb" id="sttb" placeholder="STTB ..." value="{{ $inbound->sttb }}">
                        </div>
                        <div class="col-md-3 mb-3 field-ntt-rn">
                            <label class="form-label">NTT RN#</label>
                            <input type="text" class="form-control" name="number" id="number" placeholder="NTT RN# ..." value="{{ $inbound->receiving_note }}">
                        </div>
                        <div class="col-md-3 mb-3 field-sap-po" style="display:none;">
                            <label class="form-label">SAP PO#</label>
                            <input type="text" class="form-control" id="sap_po_number" placeholder="SAP PO# ..." value="{{ $inbound->sap_po_number }}">
                        </div>
                        <div class="col-md-3 mb-3 field-ecapex" style="display:none;">
                            <label class="form-label">eCapex#</label>
                            <input type="text" class="form-control" id="ecapex_number" placeholder="eCapex# ..." value="{{ $inbound->ecapex_number }}">
                        </div>
                        <div class="col-md-3 mb-3 field-rma" style="display:none;">
                            <label class="form-label">RMA#</label>
                            <input type="text" class="form-control" id="rma_number" placeholder="RMA# ..." value="{{ $inbound->rma_number }}">
                        </div>
                        <div class="col-md-3 mb-3 field-itsm" style="display:none;">
                            <label class="form-label">ITSM#</label>
                            <input type="text" class="form-control" id="itsm_number" placeholder="ITSM# ..." value="{{ $inbound->itsm_number }}">
                        </div>

                        <div class="col-md-3 mb-3 field-tks-dn">
                            <label class="form-label">TKS DN# (Optional)</label>
                            <input type="text" class="form-control" id="tks_dn_number" placeholder="TKS DN# ..." value="{{ $inbound->tks_dn_number }}">
                        </div>
                        <div class="col-md-3 mb-3 field-tks-inv">
                            <label class="form-label">TKS Invoice# (Optional)</label>
                            <input type="text" class="form-control" id="tks_invoice_number" placeholder="TKS Invoice# ..." value="{{ $inbound->tks_invoice_number }}">
                        </div>
                        <div class="col-md-3 mb-3 field-vendor-dn" style="display:none;">
                            <label class="form-label">Vendor Supplier DN#</label>
                            <input type="text" class="form-control" id="vendor_dn_number" placeholder="Vendor DN# ..." value="{{ $inbound->vendor_dn_number }}">
                        </div>

                        <div class="col-md-3 mb-3 field-po-ref">
                            <label class="form-label">PO# (Transkargo Ref)</label>
                            <input type="text" class="form-control" name="po_number" id="po_number" placeholder="PO# ..." value="{{ $inbound->number }}">
                        </div>

                        <div class="col-12 border-bottom my-3"></div>

                        <div class="col-md-3 mb-3 field-client">
                            <label class="form-label">Client</label>
                            <select class="form-control select2" name="client_id" id="client_id">
                                <option value="">-- Choose Client --</option>
                                @foreach ($client as $item)
                                    <option value="{{ $item->id }}" {{ $inbound->client_id == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3 field-client-contact">
                            <label class="form-label">Client Contact</label>
                            <input type="text" class="form-control" id="client_contact" placeholder="Contact Name/Dept ..." value="{{ $inbound->client_contact }}">
                        </div>
                        <div class="col-md-6 mb-3 field-pickup-address">
                            <label class="form-label">Pickup/Shipment Address</label>
                            <input type="text" class="form-control" id="pickup_address" placeholder="Address detail ..." value="{{ $inbound->pickup_address }}">
                        </div>

                        <div class="col-md-3 mb-3 field-vendor">
                            <label class="form-label">Vendor / Supplier</label>
                            <input type="text" class="form-control" name="vendor" id="vendor" placeholder="Vendor / Supplier ..." value="{{ $inbound->vendor }}">
                        </div>
                        <div class="col-md-3 mb-3 field-courier-dn">
                            <label class="form-label">Courier DN</label>
                            <input type="text" class="form-control" name="delivery_note" id="delivery_note" placeholder="Courier DN ..." value="{{ $inbound->courier_delivery_note }}">
                        </div>
                        <div class="col-md-2 mb-3 field-courier-inv">
                            <label class="form-label">Courier Invoice</label>
                            <input type="text" class="form-control" name="courier_invoice" id="courier_invoice" placeholder="Courier Invoice ..." value="{{ $inbound->courier_invoice }}">
                        </div>
                        <div class="col-md-2 mb-3 field-received-date">
                            <label class="form-label">Received Date</label>
                            <input type="date" class="form-control" name="date" id="date" value="{{ $inbound->received_date ?? date('Y-m-d') }}">
                        </div>
                        <div class="col-md-2 mb-3 field-received-by">
                            <label class="form-label">Received By</label>
                            <input type="text" class="form-control" name="received_by" id="received_by" placeholder="Received By ..." value="{{ $inbound->received_by }}">
                        </div>
                        <div class="col-12 mb-3 field-remarks">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" name="remarks" id="remarks" rows="3" placeholder="Free text for notes/remarks...">{{ $inbound->remarks }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">List Product (Read-only)</h4>
                    <span class="badge bg-label-primary">{{ $inbound->details->count() }} item(s)</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm align-middle text-nowrap mb-0" style="font-size: 0.85rem;">
                            <thead class="bg-primary">
                                <tr>
                                    <th class="px-3 text-white py-2">#</th>
                                    <th class="text-white">Product Number/SKU</th>
                                    <th class="text-white">Brand</th>
                                    <th class="text-white">Group</th>
                                    <th class="text-white">Product Description</th>
                                    <th class="text-white">Serial Number</th>
                                    <th class="text-white">WH Asset#</th>
                                    <th class="text-white">Condition</th>
                                    <th class="text-white">Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($inbound->details as $detail)
                                    <tr>
                                        <td class="px-3 py-1">{{ $loop->iteration }}</td>
                                        <td class="py-1">{{ $detail->part_number ?? '-' }}</td>
                                        <td class="py-1">{{ $detail->brand->name ?? '-' }}</td>
                                        <td class="py-1">{{ $detail->productGroup->name ?? '-' }}</td>
                                        <td class="py-1">{{ $detail->description ?? $detail->part_name ?? '-' }}</td>
                                        <td class="py-1"><span class="fw-bold text-dark">{{ $detail->serial_number }}</span></td>
                                        <td class="py-1"><span class="badge bg-label-info">{{ $detail->wh_asset_number ?? '-' }}</span></td>
                                        <td class="py-1">{{ $detail->condition }}</td>
                                        <td class="py-1 text-center">{{ $detail->qty }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <p class="text-muted small mb-0">No products found.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center py-3 bg-light">
                    <div class="small text-muted">Products cannot be added/removed here. To change products, cancel and recreate.</div>
                    <button class="btn btn-primary px-4" onclick="submitUpdate()">
                        <i class="ti tabler-device-floppy me-1"></i> Update Receiving
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
