@extends('layout.index')
@section('title', 'Inventory History')

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

        .text-mono {
            font-family: 'Monaco', 'Consolas', monospace;
            font-size: 0.75rem;
        }

        .highlight-column {
            background-color: rgba(115, 103, 240, 0.08) !important;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex justify-content-between align-items-center border-bottom py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="ti tabler-history me-2 text-primary"></i>Inventory History (Outbound)</h5>
                    <div class="btn-group">
                        <a href="{{ route('inventory.history.excel', request()->all()) }}" class="btn btn-sm btn-success">
                            <i class="ti tabler-file-spreadsheet me-1"></i> Excel
                        </a>
                        <a href="{{ route('inventory.history.pdf', request()->all()) }}" class="btn btn-sm btn-primary" target="_blank">
                            <i class="ti tabler-file-description me-1"></i> PDF
                        </a>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <form action="{{ url()->current() }}" method="GET">
                        <div class="row g-2">
                            @if (Auth::user()->isAdminWMS() || Auth::user()->clients->count() > 1)
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Client</label>
                                    <select name="client_id" class="form-select form-select-sm"
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
                            @endif
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Serial Number</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" name="serial_number"
                                        value="{{ request('serial_number') }}" placeholder="Search Serial Number...">
                                    <button class="btn btn-primary" type="submit">Filter</button>
                                </div>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
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
                                    <th>Outbound No</th>
                                    <th>Outbound Date</th>
                                    <th>Client</th>
                                    <th>Part Name</th>
                                    <th>Part Number</th>
                                    <th class="highlight-column">Serial Number</th>
                                    <th>Storage Location</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($history as $item)
                                    <tr>
                                        <td>{{ $loop->iteration + ($history->currentPage() - 1) * $history->perPage() }}</td>
                                        <td>
                                            <a href="{{ route('outbound.show', $item->outbound->id) }}" class="fw-bold text-primary">
                                                {{ $item->outbound->number ?? $item->outbound->tks_dn_number }}
                                            </a>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($item->outbound->outbound_date)->format('d/m/Y') }}</td>
                                        <td><span class="small fw-medium text-dark">{{ $item->outbound->client->name ?? '-' }}</span></td>
                                        <td style="max-width: 250px; white-space: normal;">{{ $item->part_name }}</td>
                                        <td>{{ $item->part_number }}</td>
                                        <td class="highlight-column"><span class="text-mono fw-bold text-dark">{{ $item->serial_number }}</span></td>
                                        <td>
                                            @if ($item->inventory && $item->inventory->storageLevel)
                                                <span class="text-muted" style="font-size: 0.72rem;">
                                                    {{ $item->inventory->storageLevel->bin->rak->zone->name }}-{{ $item->inventory->storageLevel->bin->rak->name }}-{{ $item->inventory->storageLevel->bin->name }}-{{ $item->inventory->storageLevel->name }}
                                                </span>
                                            @else
                                                <span class="text-muted small">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">No records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $history->appends(request()->all())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
