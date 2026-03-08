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

        .w-fit {
            width: fit-content;
        }

        .x-small {
            font-size: 0.65rem;
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

        function cancelOutbound(id, number) {
            Swal.fire({
                title: 'Cancel Outbound?',
                text: `Are you sure you want to cancel Outbound ${number}? All items will be returned to inventory.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, cancel it!',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return fetch('{{ route('outbound.cancel') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                id: id
                            })
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(response.statusText)
                            }
                            return response.json()
                        })
                        .catch(error => {
                            Swal.showValidationMessage(`Request failed: ${error}`)
                        })
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    if (result.value.status) {
                        Swal.fire('Cancelled!', 'Outbound has been cancelled.', 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', result.value.message || 'Failed to cancel outbound.', 'error');
                    }
                }
            })
        }
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
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-sm text-nowrap">
                        <thead class="table-light">
                            <tr>
                                <th width="30">#</th>
                                <th>Date</th>
                                <th>Client</th>
                                <th>Category & Request Type</th>
                                <th>SAP PO#</th>
                                <th>TKS DN / Ref#</th>
                                <th class="text-center">Qty</th>
                                <th>Status</th>
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
                                                class="fw-bold text-dark">{{ $item->outbound_date ? \Carbon\Carbon::parse($item->outbound_date)->format('d/m/Y') : '-' }}</span>
                                            <small class="text-muted" style="font-size: 0.65rem;">By:
                                                {{ $item->outbound_by }}</small>
                                        </div>
                                    </td>
                                    <td><span class="fw-bold text-dark">{{ $item->client->name ?? '-' }}</span></td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <span class="badge bg-label-info w-fit"
                                                style="font-size: 0.7rem;">{{ $item->category }}</span>
                                            @if ($item->request_type)
                                                <span class="text-muted x-small" style="font-size: 0.7rem;"><i
                                                        class="ti tabler-point me-1"></i>{{ $item->request_type }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-primary fw-bold">{{ $item->sap_po_number ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold text-dark">{{ $item->tks_dn_number ?? '-' }}</span>
                                            @if ($item->number)
                                                <small class="text-muted" style="font-size: 0.65rem;">PO:
                                                    {{ $item->number }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-label-secondary fw-bold px-2">{{ $item->qty }}</span>
                                    </td>
                                    <td>
                                        <span
                                            class="badge {{ $item->status == 'cancel' ? 'bg-label-danger' : 'bg-label-success' }}">{{ strtoupper($item->status) }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 justify-content-center">
                                            <a href="{{ route('outbound.show', $item->id) }}"
                                                class="btn btn-icon btn-sm btn-label-primary" title="View Detail">
                                                <i class="ti tabler-eye fs-5"></i>
                                            </a>
                                            <a href="{{ route('outbound.print', $item->id) }}" target="_blank"
                                                class="btn btn-icon btn-sm btn-label-secondary" title="Print PDF">
                                                <i class="ti tabler-printer fs-5"></i>
                                            </a>
                                            @if ($item->status !== 'cancel')
                                                <button type="button" class="btn btn-icon btn-sm btn-label-danger"
                                                    onclick="cancelOutbound({{ $item->id }}, '{{ $item->number ?? $item->tks_dn_number }}')"
                                                    title="Cancel Outbound">
                                                    <i class="ti tabler-x fs-5"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
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
