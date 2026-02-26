@extends('layout.index')
@section('title', 'Outbound List')

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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "-- Choose Client --",
                allowClear: true,
                width: '100%'
            });
        });
    </script>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-end mb-3">
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle shadow-sm" type="button" id="dropdownMenuButton"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti tabler-plus me-1"></i> Create Outbound
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="dropdownMenuButton">
                        <a class="dropdown-item d-flex align-items-center" href="{{ route('outbound.create.spare') }}">
                            <i class="ti tabler-settings me-2"></i> Spare
                        </a>
                        <a class="dropdown-item d-flex align-items-center" href="{{ route('outbound.create.faulty') }}">
                            <i class="ti tabler-tool me-2"></i> Faulty
                        </a>
                        <a class="dropdown-item d-flex align-items-center" href="{{ route('outbound.create.rma') }}">
                            <i class="ti tabler-refresh me-2"></i> RMA
                        </a>
                        <a class="dropdown-item d-flex align-items-center" href="{{ route('outbound.create.write-off') }}">
                            <i class="ti tabler-trash-x me-2"></i> Write-off
                        </a>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header border-bottom">
                    <form action="{{ url()->current() }}" method="GET">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Client</label>
                                <select name="client_id" class="form-select select2" onchange="this.form.submit()">
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
                                <label class="form-label fw-bold">Category</label>
                                <select name="category" class="form-select" onchange="this.form.submit()">
                                    <option value="">All Categories</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat }}"
                                            {{ request('category') == $cat ? 'selected' : '' }}>
                                            {{ $cat }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-7">
                                <label class="form-label fw-bold">Global Search</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti tabler-search"></i></span>
                                    <input type="text" class="form-control" name="search"
                                        value="{{ request()->get('search') }}"
                                        placeholder="Search PO#, DN#, RMA#, ITSM# ...">
                                    <button class="btn btn-primary" type="submit">Filter</button>
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
                                    <th width="30">#</th>
                                    <th>Client & Category</th>
                                    <th>Reference Numbers</th>
                                    <th>Summary</th>
                                    <th>Status & Dates</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $item)
                                    <tr>
                                        <td>{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span
                                                    class="text-dark fw-bold mb-1">{{ $item->client->name ?? '-' }}</span>
                                                <span class="badge bg-label-info w-px-100">{{ $item->category }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column gap-1">
                                                @if ($item->number)
                                                    <small class="text-muted">PO#: <span
                                                            class="text-dark fw-bold">{{ $item->number }}</span></small>
                                                @endif
                                                @if ($item->ntt_dn_number)
                                                    <small class="text-muted">NTT DN: <span
                                                            class="text-dark fw-bold">{{ $item->ntt_dn_number }}</span></small>
                                                @endif
                                                @if ($item->tks_dn_number)
                                                    <small class="text-muted">TKS DN: <span
                                                            class="text-dark fw-bold">{{ $item->tks_dn_number }}</span></small>
                                                @endif
                                                @if ($item->tks_invoice_number)
                                                    <small class="text-muted">INV: <span
                                                            class="text-dark fw-bold">{{ $item->tks_invoice_number }}</span></small>
                                                @endif
                                                @if ($item->rma_number)
                                                    <small class="text-muted">RMA: <span
                                                            class="text-dark fw-bold">{{ $item->rma_number }}</span></small>
                                                @endif
                                                @if ($item->itsm_number)
                                                    <small class="text-muted">ITSM: <span
                                                            class="text-dark fw-bold">{{ $item->itsm_number }}</span></small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2">
                                                    <span class="avatar-initial rounded bg-label-secondary">
                                                        <i class="ti tabler-box"></i>
                                                    </span>
                                                </div>
                                                <span class="fw-bold">{{ $item->qty }} Item(s)</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column align-items-start">
                                                <span class="badge bg-label-success mb-1">{{ $item->status }}</span>
                                                <small class="text-muted"><i class="ti tabler-calendar me-1"></i>
                                                    {{ $item->outbound_date ? \Carbon\Carbon::parse($item->outbound_date)->format('d/m/Y') : '-' }}</small>
                                                <small class="text-muted"><i class="ti tabler-user me-1"></i>
                                                    {{ $item->outbound_by }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2 justify-content-center">
                                                <a href="{{ route('outbound.show', $item->id) }}"
                                                    class="btn btn-primary btn-sm d-flex align-items-center"
                                                    title="View Detail">
                                                    <i class="ti tabler-eye me-1"></i> Detail
                                                </a>
                                                <a href="{{ route('outbound.print', $item->id) }}" target="_blank"
                                                    class="btn btn-label-secondary btn-sm d-flex align-items-center"
                                                    title="Print PDF">
                                                    <i class="ti tabler-printer me-1"></i> Print
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center justify-content-center">
                                                <i class="ti tabler-box-off text-muted mb-2" style="font-size: 3rem;"></i>
                                                <p class="text-muted mb-0">No outbound records found.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $data->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
