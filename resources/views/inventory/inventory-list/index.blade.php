@extends('layout.index')
@section('title', 'Inventory List')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <form action="{{ url()->current() }}" method="GET">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Client</label>
                                <select name="client_id" class="form-select" onchange="this.form.submit()">
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
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" onchange="this.form.submit()">
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
                                <label class="form-label">Condition</label>
                                <select name="condition" class="form-select" onchange="this.form.submit()">
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
                            <div class="col-md-5">
                                <label class="form-label">Global Search</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search"
                                        value="{{ request()->get('search') }}"
                                        placeholder="Search SN, Asset#, Part Name ...">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="ti tabler-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Identifiers</th>
                                    <th>Product Details</th>
                                    <th>Warehouse Location</th>
                                    <th>Status & Dates</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($inventory as $item)
                                    <tr>
                                        <td>{{ $loop->iteration + ($inventory->currentPage() - 1) * $inventory->perPage() }}
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="text-dark fw-bold mb-1"><i
                                                        class="ti tabler-barcode text-muted me-1"></i>{{ $item->unique_id }}</span>
                                                <span class="badge bg-label-info w-px-150 text-start"><i
                                                        class="ti tabler-qrcode me-1"></i>{{ $item->serial_number }}</span>
                                                @if ($item->parent_serial_number)
                                                    <small class="text-muted mt-1">Parent:
                                                        {{ $item->parent_serial_number }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold text-primary">{{ $item->part_name }}</span>
                                                <small class="text-muted mb-1">P/N: {{ $item->part_number }}</small>
                                                <div>
                                                    <span
                                                        class="badge bg-label-secondary me-1">{{ $item->product && $item->product->brand ? $item->product->brand->name : '-' }}</span>
                                                    <span
                                                        class="badge bg-label-secondary">{{ $item->product && $item->product->productGroup ? $item->product->productGroup->name : '-' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($item->storageLevel)
                                                <div class="d-flex align-items-center">
                                                    <div class="badge bg-label-primary p-2 me-2"><i
                                                            class="ti tabler-map-pin"></i></div>
                                                    <div class="d-flex flex-column">
                                                        <span
                                                            class="fw-bold text-dark">{{ $item->storageLevel->bin->rak->zone->name }}
                                                            - {{ $item->storageLevel->bin->rak->name }}</span>
                                                        <small class="text-muted">Bin: {{ $item->storageLevel->bin->name }}
                                                            | Lvl: {{ $item->storageLevel->name }}</small>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted"><i class="ti tabler-map-pin-off me-1"></i>Not
                                                    Set</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column align-items-start">
                                                @php
                                                    $bgClass = 'bg-secondary';
                                                    switch (strtolower($item->status)) {
                                                        case 'available':
                                                        case 'in stock':
                                                            $bgClass = 'bg-success';
                                                            break;
                                                        case 'out for replacement/ support':
                                                        case 'out for loan':
                                                        case 'out for return':
                                                        case 'shipped / outbound':
                                                            $bgClass = 'bg-warning text-dark';
                                                            break;
                                                        case 'write-off':
                                                        case 'faulty':
                                                        case 'broken':
                                                        case 'defective':
                                                            $bgClass = 'bg-danger';
                                                            break;
                                                    }
                                                @endphp
                                                <span class="badge {{ $bgClass }} mb-1">{{ $item->status }}</span>
                                                <small class="text-muted"><i class="ti tabler-clock me-1"></i>Appr:
                                                    {{ $item->last_staging_date ? \Carbon\Carbon::parse($item->last_staging_date)->format('d/m/y') : '-' }}</small>
                                                <small class="text-muted"><i
                                                        class="ti tabler-arrows-right-left me-1"></i>Moved:
                                                    {{ $item->last_movement_date ? \Carbon\Carbon::parse($item->last_movement_date)->format('d/m/y') : '-' }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('inventory.show', $item->id) }}"
                                                    class="btn btn-primary btn-sm d-flex align-items-center justify-content-center">
                                                    <i class="icon-base ti tabler-info-circle me-1"></i> Detail
                                                </a>
                                                <button type="button"
                                                    onclick="printBarcode('{{ $item->unique_id }}', '{{ $item->part_number }}', '{{ $item->serial_number }}')"
                                                    class="btn btn-info btn-sm d-flex align-items-center justify-content-center">
                                                    <i class="icon-base ti tabler-printer me-1"></i> Print
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center justify-content-center">
                                                <i class="ti tabler-box-off text-muted mb-2" style="font-size: 3rem;"></i>
                                                <p class="text-muted mb-0">No inventory records found.</p>
                                            </div>
                                        </td>
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
                                size: 50mm 40mm; /* Adjusted for QR Code */
                            }
                            body {
                                margin: 0;
                                padding: 5px;
                                display: flex;
                                flex-direction: column;
                                align-items: center;
                                justify-content: center;
                                font-family: 'Public Sans', -apple-system, sans-serif;
                                height: 100vh;
                                box-sizing: border-box;
                            }
                            #qrcode {
                                margin-bottom: 5px;
                            }
                            #qrcode img {
                                margin: 0 auto;
                            }
                            .unique-id {
                                font-size: 11px;
                                font-weight: 700;
                                margin-bottom: 3px;
                                color: #000;
                            }
                            .details {
                                font-size: 9px;
                                line-height: 1.2;
                                font-weight: 600;
                                text-align: center;
                                width: 100%;
                            }
                            .label-text {
                                color: #555;
                                font-weight: 400;
                            }
                        </style>
                    </head>
                    <body>
                        <div class="unique-id">${uniqueId}</div>
                        <div id="qrcode"></div>
                        <div class="details">
                            <div><span class="label-text">P/N:</span> ${partNumber}</div>
                            <div><span class="label-text">S/N:</span> ${serialNumber}</div>
                        </div>

                        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"><\/script>
                        <script>
                            window.onload = function() {
                                const scanUrl = "{{ url('/scan') }}/" + "${uniqueId}";
                                new QRCode(document.getElementById("qrcode"), {
                                    text: scanUrl,
                                    width: 100,
                                    height: 100,
                                    colorDark : "#000000",
                                    colorLight : "#ffffff",
                                    correctLevel : QRCode.CorrectLevel.H
                                });
                                setTimeout(() => {
                                    window.print();
                                    window.close();
                                }, 400);
                            };
                        <\/script>
                    </body>
                </html>
            `);
            printWindow.document.close();
        }
    </script>
@endsection
