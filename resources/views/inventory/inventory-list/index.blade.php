@extends('layout.index')
@section('title', 'Inventory List')

@section('css')
    <style>
        .table thead th {
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: #5d596c;
            white-space: nowrap;
        }

        .table-compact td {
            font-size: 0.8rem;
            padding: 0.5rem 0.6rem !important;
        }

        .badge-status {
            font-size: 0.65rem;
            padding: 0.4em 0.8em;
            border-radius: 4px;
        }

        .text-mono {
            font-family: 'Monaco', 'Consolas', monospace;
            font-size: 0.75rem;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex justify-content-between align-items-center border-bottom py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="ti tabler-box me-2 text-primary"></i>Inventory Data</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('inventory.export.pdf', request()->all()) }}" target="_blank"
                            class="btn btn-sm btn-label-secondary">
                            <i class="ti tabler-file-type-pdf me-1"></i> PDF Export
                        </a>
                        <a href="{{ route('inventory.export.excel', request()->all()) }}"
                            class="btn btn-sm btn-label-success">
                            <i class="ti tabler-file-spreadsheet me-1"></i> Excel Export
                        </a>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <form action="{{ url()->current() }}" method="GET">
                        <div class="row g-2">
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Client</label>
                                <select name="client_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">All Clients</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}"
                                            {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                            {{ $client->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Status</label>
                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">All Statuses</option>
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status }}"
                                            {{ request('status') == $status ? 'selected' : '' }}>
                                            {{ $status }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Condition</label>
                                <select name="condition" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">All Conditions</option>
                                    @foreach ($conditions as $condition)
                                        @if ($condition)
                                            <option value="{{ $condition }}"
                                                {{ request('condition') == $condition ? 'selected' : '' }}>
                                                {{ $condition }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Search (SN, Asset#, Part Name)</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" name="search"
                                        value="{{ request('search') }}" placeholder="Enter keyword...">
                                    <button class="btn btn-primary" type="submit">Filter</button>
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <a href="{{ url()->current() }}" class="btn btn-sm btn-label-secondary w-100">Reset</a>
                            </div>
                        </div>
                    </form>

                    <hr class="my-3">

                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-compact table-sm text-nowrap align-middle">
                            <thead class="table-light border-top">
                                <tr>
                                    <th width="30">#</th>
                                    <th>Client / Owner</th>
                                    <th>Asset ID</th>
                                    <th>Serial Number</th>
                                    <th>Part Name</th>
                                    <th>Part Number</th>
                                    <th>Brand</th>
                                    <th>Group</th>
                                    <th>Location</th>
                                    <th>Condition</th>
                                    <th>Status</th>
                                    <th>Check Date</th>
                                    <th>Activity</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($inventory as $item)
                                    <tr>
                                        <td>{{ $loop->iteration + ($inventory->currentPage() - 1) * $inventory->perPage() }}
                                        </td>
                                        <td><span class="small fw-medium text-dark">{{ $item->client->name ?? '-' }}</span>
                                        </td>
                                        <td><span class="text-mono fw-bold text-primary">{{ $item->unique_id }}</span></td>
                                        <td><span class="text-mono fw-bold text-dark">{{ $item->serial_number }}</span>
                                        </td>
                                        <td style="max-width: 200px; white-space: normal;"><span
                                                class="fw-medium">{{ $item->part_name }}</span></td>
                                        <td>{{ $item->part_number }}</td>
                                        <td><span class="badge bg-label-dark"
                                                style="font-size: 0.65rem;">{{ $item->brand->name ?? '-' }}</span></td>
                                        <td><span class="badge bg-label-secondary"
                                                style="font-size: 0.65rem;">{{ $item->productGroup->name ?? '-' }}</span>
                                        </td>
                                        <td>
                                            @if ($item->storageLevel)
                                                <span class="text-muted" style="font-size: 0.72rem;">
                                                    {{ $item->storageLevel->bin->rak->zone->name }}-{{ $item->storageLevel->bin->rak->name }}-{{ $item->storageLevel->bin->name }}-{{ $item->storageLevel->name }}
                                                </span>
                                            @else
                                                <span class="text-muted small">Not Assigned</span>
                                            @endif
                                        </td>
                                        <td><span
                                                class="badge {{ $item->condition == 'New' ? 'bg-label-info' : 'bg-label-secondary' }} badge-status">{{ $item->condition ?? '-' }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $bgClass = 'bg-label-secondary';
                                                switch (strtolower($item->status)) {
                                                    case 'available':
                                                        $bgClass = 'bg-label-success';
                                                        break;
                                                    case 'staging':
                                                        $bgClass = 'bg-label-info';
                                                        break;
                                                    case 'shipped / outbound':
                                                        $bgClass = 'bg-label-warning';
                                                        break;
                                                    case 'write-off':
                                                    case 'faulty':
                                                        $bgClass = 'bg-label-danger';
                                                        break;
                                                }
                                            @endphp
                                            <span
                                                class="badge {{ $bgClass }} badge-status">{{ strtoupper($item->status) }}</span>
                                        </td>
                                        <td><small
                                                class="text-muted">{{ $item->last_staging_date ? \Carbon\Carbon::parse($item->last_staging_date)->format('d/m/Y') : '-' }}</small>
                                        </td>
                                        <td><small
                                                class="text-muted">{{ $item->last_movement_date ? \Carbon\Carbon::parse($item->last_movement_date)->format('d/m/Y') : '-' }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1 justify-content-center">
                                                <a href="{{ route('inventory.show', $item->id) }}"
                                                    class="btn btn-icon btn-sm btn-label-primary">
                                                    <i class="ti tabler-info-circle fs-6"></i>
                                                </a>
                                                <button
                                                    onclick="printBarcode('{{ $item->unique_id }}', '{{ $item->part_number }}', '{{ $item->serial_number }}')"
                                                    class="btn btn-icon btn-sm btn-label-info">
                                                    <i class="ti tabler-printer fs-6"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="14" class="text-center py-5">No records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $inventory->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        function printBarcode(uniqueId, partNumber, serialNumber) {
            const printWindow = window.open('', '_blank', 'width=600,height=400');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Print QR Code - ${uniqueId}</title>
                        <style>
                            @page { 
                                margin: 0; 
                                size: 50mm 40mm; 
                            }
                            body { 
                                margin: 0; 
                                padding: 5px; 
                                display: flex; 
                                flex-direction: column; 
                                align-items: center; 
                                justify-content: center; 
                                font-family: sans-serif; 
                                height: 40mm; 
                                width: 50mm;
                                background-color: white;
                                overflow: hidden;
                            }
                            .unique-id { 
                                font-size: 13px; 
                                font-weight: bold; 
                                margin-bottom: 2px; 
                                letter-spacing: 0.5px;
                            }
                            #qrcode { 
                                margin-bottom: 2px; 
                            }
                            .details { 
                                font-size: 9px; 
                                text-align: center; 
                                line-height: 1.2;
                            }
                        </style>
                    </head>
                    <body>
                        <div class="unique-id">${uniqueId}</div>
                        <div id="qrcode"></div>
                        <div class="details">
                            <div>P/N: ${partNumber}</div>
                            <div>S/N: ${serialNumber}</div>
                        </div>
                        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"><\/script>
                        <script>
                            window.onload = function() {
                                new QRCode(document.getElementById("qrcode"), {
                                    text: "{{ url('/scan') }}/" + "${uniqueId}",
                                    width: 80, 
                                    height: 80
                                });
                                setTimeout(() => { window.print(); window.close(); }, 500);
                            };
                        <\/script>
                    </body>
                </html>
            `);
            printWindow.document.close();
        }
    </script>
@endsection
