@extends('layout.index')

@section('title', $title)

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
    <style>
        .table-statement thead th {
            font-size: 0.75rem !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
            background-color: #f8fafc !important;
        }

        .table-statement tbody td {
            font-size: 0.82rem !important;
            white-space: nowrap;
        }

        .sticky-col {
            position: sticky;
            left: 0;
            background-color: white !important;
            z-index: 10;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
        }

        .card-header-premium {
            background: linear-gradient(45deg, #4e54c8, #8f94fb);
            color: white;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Inventory /</span> Stock Statement
        </h4>

        <!-- Filter Card -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center py-2">
                <h6 class="mb-0 fw-bold"><i class="ti tabler-filter me-2"></i>Filter Options</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('inventory.stock.statement') }}" method="GET">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Client</label>
                            <select name="client_id" class="form-select form-select-sm select2">
                                <option value="">All Clients</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}"
                                        {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                        {{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Stock Category</label>
                            <select name="category" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                                        {{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Request Type</label>
                            <select name="request_type" class="form-select form-select-sm">
                                <option value="">All Types</option>
                                @foreach ($requestTypes as $type)
                                    <option value="{{ $type }}"
                                        {{ request('request_type') == $type ? 'selected' : '' }}>
                                        {{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Search (SN, Part #, Reff)</label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" class="form-control" placeholder="Search..."
                                    value="{{ request('search') }}">
                                <button class="btn btn-primary" type="submit"><i class="ti tabler-search"></i></button>
                                <a href="{{ route('inventory.stock.statement') }}" class="btn btn-outline-secondary"><i
                                        class="ti tabler-refresh"></i></a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Master Data Card -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-label-primary d-flex justify-content-between align-items-center py-3">
                <h5 class="card-title mb-0 fw-bold">Inventory Master Report (Stock Statement)</h5>
                <div class="d-flex gap-2">
                    <button id="btnExportExcel" class="btn btn-success btn-sm">
                        <i class="ti tabler-file-spreadsheet me-1"></i> Export Excel
                    </button>
                    <span class="badge bg-primary rounded-pill">{{ $inboundData->total() }} Records</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-statement mb-0" id="statementTable">
                        <thead>
                            <tr>
                                <th class="sticky-col">Movement Category</th>
                                <th>Stock Category</th>
                                <th>Request Type</th>
                                <th>NTT Requestor</th>
                                <th>Request Date</th>
                                <th>Product Group</th>
                                <th>Brand</th>
                                <th>Product Number (SKU)</th>
                                <th>Product Description</th>
                                <th>Serial Number (SN)</th>
                                <th>Parent SN</th>
                                <th>Qty</th>
                                <th>WH Asset Number</th>
                                <th>Stock Status</th>
                                <th>Stock Condition</th>
                                <th>Stock Location (Rack/Bin/Level)</th>
                                <th>eCapex #</th>
                                <th>SAP PO #</th>
                                <th>Vendor DN #</th>
                                <th>NTT RN #</th>
                                <th>Received Date</th>
                                <th>NTT DN #</th>
                                <th>Delivery Date</th>
                                <th>Trans Kargo DN #</th>
                                <th>Trans Kargo Invoice #</th>
                                <th>Staging Date</th>
                                <th>ITSM #</th>
                                <th>RMA #</th>
                                <th>Processed By</th>
                                <th>Client Name</th>
                                <th>Client Contact</th>
                                <th>Pickup Address</th>
                                <th>Shipment Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($inboundData as $item)
                                @php
                                    $isOutbound = (bool) $item->outbound_detail;
                                    $statusBadge = $isOutbound ? 'bg-label-danger' : 'bg-label-info';
                                    $categoryText = $isOutbound ? 'Outbound' : 'Inbound';
                                @endphp
                                <tr>
                                    <td class="sticky-col">
                                        <span class="badge {{ $statusBadge }}">{{ $categoryText }}</span>
                                    </td>
                                    <td>{{ $item->inbound->category ?? '-' }}</td>
                                    <td>{{ $item->inbound->request_type ?? '-' }}</td>
                                    <td>{{ $item->inbound->ntt_requestor ?? '-' }}</td>
                                    <td>{{ $item->inbound->request_date ? \Carbon\Carbon::parse($item->inbound->request_date)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td>{{ $item->productGroup->name ?? '-' }}</td>
                                    <td>{{ $item->brand->name ?? '-' }}</td>
                                    <td>{{ $item->part_number }}</td>
                                    <td>{{ $item->part_name }}</td>
                                    <td class="fw-bold text-primary">{{ $item->serial_number }}</td>
                                    <td>{{ $item->parent_sn ?? ($item->old_serial_number ?? '-') }}</td>
                                    <td>{{ $item->qty }}</td>
                                    <td>{{ $item->current_inventory->unique_id ?? ($item->wh_asset_number ?? '-') }}</td>
                                    <td>
                                        @php
                                            $badgeClass = match ($item->stock_status) {
                                                'Available' => 'bg-label-success',
                                                'Faulty' => 'bg-label-danger',
                                                'Write-off' => 'bg-label-secondary',
                                                default => 'bg-label-info',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $item->stock_status }}</span>
                                    </td>
                                    <td>{{ $item->condition }}</td>
                                    <td>
                                        @if ($item->storageLevel)
                                            {{ $item->storageLevel->bin->rak->zone->name }}-{{ $item->storageLevel->bin->rak->name }}-{{ $item->storageLevel->bin->name }}-{{ $item->storageLevel->name }}
                                        @else
                                            <span class="text-muted small">Not Put Away</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->inbound->ecapex_number ?? '-' }}</td>
                                    <td>{{ $item->inbound->sap_po_number ?? '-' }}</td>
                                    <td>{{ $item->inbound->vendor_dn_number ?? '-' }}</td>
                                    <td>{{ $item->inbound->ntt_rn_number ?? ($item->inbound->number ?? '-') }}</td>
                                    <td>{{ $item->inbound->received_date ? \Carbon\Carbon::parse($item->inbound->received_date)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td>{{ $item->inbound->ntt_dn_number ?? '-' }}</td>
                                    <td>{{ $item->inbound->delivery_date ? \Carbon\Carbon::parse($item->inbound->delivery_date)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td>{{ $item->inbound->tks_dn_number ?? '-' }}</td>
                                    <td>{{ $item->inbound->tks_invoice_number ?? '-' }}</td>
                                    <td>{{ $item->staging_date ? \Carbon\Carbon::parse($item->staging_date)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td>{{ $item->inbound->itsm_number ?? '-' }}</td>
                                    <td>{{ $item->inbound->rma_number ?? '-' }}</td>
                                    <td>{{ $item->inbound->received_by ?? '-' }}</td>
                                    <td>{{ $item->inbound->client->name ?? '-' }}</td>
                                    <td>{{ $item->inbound->client_contact ?? '-' }}</td>
                                    <td>{{ $item->inbound->pickup_address ?? '-' }}</td>
                                    <td>
                                        <span
                                            class="badge bg-label-primary px-2">{{ $item->inbound->shipment_status ?? 'N/A' }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="33" class="text-center py-5 text-muted">No records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer py-2">
                {{ $inboundData->links() }}
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="https://cdn.rawgit.com/rainabba/jquery-table2excel/1.1.0/dist/jquery.table2excel.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap-5'
            });

            $('#btnExportExcel').click(function() {
                $("#statementTable").table2excel({
                    exclude: ".noExl",
                    name: "InventoryStockStatement",
                    filename: "InventoryStockStatement-" + new Date().toISOString().replace(
                        /[\-\:\.]/g, "") + ".xls",
                    fileext: ".xls",
                    exclude_img: true,
                    exclude_links: true,
                    exclude_inputs: true
                });
            });
        });
    </script>
@endsection
