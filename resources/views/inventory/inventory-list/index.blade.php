@extends('layout.index')
@section('title', 'Inventory List')

@section('css')
    <style>
        .inventory-card {
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .table thead th {
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: #5d596c;
            border-top: none;
        }

        .id-badge {
            font-family: 'Monaco', 'Consolas', monospace;
            font-size: 0.8rem;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            color: #212529;
            border-radius: 6px;
            padding: 4px 8px;
        }

        .sn-text {
            font-size: 0.85rem;
            color: #6f6b7d;
        }

        .part-name {
            font-size: 0.95rem;
            font-weight: 600;
            color: #444050;
            margin-bottom: 2px;
        }

        .part-meta {
            font-size: 0.8rem;
            color: #a19fad;
        }

        .status-badge {
            padding: 0.5em 1em;
            border-radius: 50rem;
            font-weight: 600;
            font-size: 0.75rem;
        }

        .activity-item {
            display: flex;
            align-items: center;
            font-size: 0.8rem;
            color: #6f6b7d;
            margin-bottom: 2px;
        }

        .activity-icon {
            font-size: 1rem;
            margin-right: 6px;
            color: #a19fad;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center border-bottom mb-3">
                    <h5 class="card-title mb-0">Inventory Data</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('inventory.export.pdf', request()->all()) }}" target="_blank"
                            class="btn btn-label-secondary">
                            <i class="ti tabler-file-type-pdf me-1"></i> Export PDF
                        </a>
                        <a href="{{ route('inventory.export.excel', request()->all()) }}" class="btn btn-label-success">
                            <i class="ti tabler-file-spreadsheet me-1"></i> Export Excel
                        </a>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <form action="{{ url()->current() }}" method="GET" class="filter-row">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-10">
                                <div class="row g-2">
                                    <div class="col-md-3">
                                        <div class="input-group input-group-merge">
                                            <span class="input-group-text"><i class="ti tabler-users"></i></span>
                                            <select name="client_id" class="form-select border-start-0"
                                                onchange="this.form.submit()">
                                                <option value="">All Clients</option>
                                                @foreach ($clients as $client)
                                                    <option value="{{ $client->id }}"
                                                        {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                                        {{ $client->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
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
                                        <div class="input-group input-group-merge">
                                            <span class="input-group-text"><i class="ti tabler-search"></i></span>
                                            <input type="text" class="form-control border-start-0" name="search"
                                                value="{{ request()->get('search') }}"
                                                placeholder="Search SN, Asset#, Part Name ...">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 d-grid">
                                <button class="btn btn-primary" type="submit">Apply Filter</button>
                            </div>
                        </div>
                    </form>
                    <hr class="my-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Unit Info</th>
                                    <th>Product & Specs</th>
                                    <th>Storage</th>
                                    <th>Condition</th>
                                    <th>Status</th>
                                    <th>Last Activity</th>
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
                                                <div class="mb-1">
                                                    <span class="id-badge"><i
                                                            class="ti tabler-hash me-1"></i>{{ $item->unique_id }}</span>
                                                </div>
                                                <div class="sn-text"><i
                                                        class="ti tabler-qrcode me-1"></i>{{ $item->serial_number }}</div>
                                                @if ($item->parent_serial_number)
                                                    <small class="text-muted opacity-75 mt-1">P:
                                                        {{ $item->parent_serial_number }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <div class="part-name">{{ $item->part_name }}</div>
                                                <div class="part-meta mb-2">P/N: {{ $item->part_number }}</div>
                                                <div class="d-flex gap-1">
                                                    <span class="badge bg-label-dark p-1 px-2"
                                                        style="font-size: 0.65rem;">{{ $item->product && $item->product->brand ? $item->product->brand->name : '-' }}</span>
                                                    <span class="badge bg-label-secondary p-1 px-2"
                                                        style="font-size: 0.65rem;">{{ $item->product && $item->product->productGroup ? $item->product->productGroup->name : '-' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($item->storageLevel)
                                                <span class="text-muted small">
                                                    {{ $item->storageLevel->bin->rak->zone->name }}-{{ $item->storageLevel->bin->rak->name }}-{{ $item->storageLevel->bin->name }}-{{ $item->storageLevel->name }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-label-info border-0 px-3">{{ $item->condition ?? '-' }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $bgClass = 'bg-label-secondary';
                                                $statusLower = strtolower($item->status);
                                                switch ($statusLower) {
                                                    case 'available':
                                                    case 'in stock':
                                                        $bgClass = 'bg-label-success';
                                                        break;
                                                    case 'out for replacement/ support':
                                                    case 'out for loan':
                                                    case 'out for return':
                                                    case 'shipped / outbound':
                                                        $bgClass = 'bg-label-warning';
                                                        break;
                                                    case 'write-off':
                                                    case 'faulty':
                                                    case 'broken':
                                                    case 'defective':
                                                        $bgClass = 'bg-label-danger';
                                                        break;
                                                }
                                            @endphp
                                            <span
                                                class="badge {{ $bgClass }} status-badge border-0">{{ $item->status }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <div class="activity-item">
                                                    <i class="ti tabler-circle-check activity-icon"></i>
                                                    {{ $item->last_staging_date ? \Carbon\Carbon::parse($item->last_staging_date)->format('d/m/Y') : '-' }}
                                                </div>
                                                <div class="activity-item">
                                                    <i class="ti tabler-replace activity-icon"></i>
                                                    {{ $item->last_movement_date ? \Carbon\Carbon::parse($item->last_movement_date)->format('d/m/Y') : '-' }}
                                                </div>
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
                                        <td colspan="8" class="text-center py-5">
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
