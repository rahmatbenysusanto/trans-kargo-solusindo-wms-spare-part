@extends('layout.index')

@section('title', 'Cycle Count (Movement Report)')

@section('css')
    <style>
        .table-cycle thead th {
            font-size: 0.7rem !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
            background-color: #f8fafc !important;
        }

        .table-cycle tbody td {
            font-size: 0.8rem !important;
            white-space: nowrap;
        }
    </style>
@endsection

@section('content')
    <div class="row mb-4">
        <!-- Summary Cards for selected Period -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-label-success">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="badge bg-success p-2 me-3">
                            <i class="ti tabler-download fs-3"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-success small fw-bold">Period Inbound</h6>
                            <h4 class="mb-0 fw-bold">{{ $summary['inbound'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-label-danger">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="badge bg-danger p-2 me-3">
                            <i class="ti tabler-upload fs-3"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-danger small fw-bold">Period Outbound</h6>
                            <h4 class="mb-0 fw-bold">{{ $summary['outbound'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-label-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="badge bg-primary p-2 me-3">
                            <i class="ti tabler-arrows-diff fs-3"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-primary small fw-bold">Period Movement</h6>
                            <h4 class="mb-0 fw-bold">{{ $summary['movement'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center py-2">
                    <h6 class="mb-0 fw-bold"><i class="ti tabler-filter me-2"></i>Filter Period & Type</h6>
                </div>
                <div class="card-body py-3">
                    <form action="{{ url()->current() }}" method="GET" class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Client:</label>
                            <select name="client_id" class="form-select form-select-sm select2">
                                <option value="">All Clients</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}" {{ $clientId == $client->id ? 'selected' : '' }}>
                                        {{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Start Date:</label>
                            <input type="date" name="start_date" class="form-control form-control-sm"
                                value="{{ $startDate }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">End Date:</label>
                            <input type="date" name="end_date" class="form-control form-control-sm"
                                value="{{ $endDate }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Type:</label>
                            <select name="type" class="form-select form-select-sm">
                                <option value="">All Types</option>
                                <option value="Inbound" {{ $type == 'Inbound' ? 'selected' : '' }}>Inbound</option>
                                <option value="Outbound" {{ $type == 'Outbound' ? 'selected' : '' }}>Outbound</option>
                                <option value="Movement" {{ $type == 'Movement' ? 'selected' : '' }}>Movement</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">Search:</label>
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="..."
                                value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="ti tabler-search"></i> Apply
                            </button>
                            <a href="{{ url()->current() }}" class="btn btn-outline-secondary btn-sm">
                                <i class="ti tabler-refresh"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom bg-light d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0 fw-bold">Movement Log & Statistics</h5>
                        <small class="text-muted">Showing data from
                            {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} to
                            {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</small>
                    </div>
                    <button id="btnExportExcel" class="btn btn-success btn-sm">
                        <i class="ti tabler-file-spreadsheet me-1"></i> Export Excel
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 table-cycle" id="cycleCountTable">
                            <thead>
                                <tr>
                                    <th>Date/Time</th>
                                    <th>Activity</th>
                                    <th>Client</th>
                                    <th>Part Name / SKU</th>
                                    <th>Brand / Group</th>
                                    <th>Serial Number (SN)</th>
                                    <th>WH Asset Number</th>
                                    <th>Category</th>
                                    <th>Ref#</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $row)
                                    <tr>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span
                                                    class="fw-bold">{{ \Carbon\Carbon::parse($row->created_at)->format('d/m/Y') }}</span>
                                                <small
                                                    class="text-muted">{{ \Carbon\Carbon::parse($row->created_at)->format('H:i') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($row->type == 'Inbound')
                                                <span class="badge bg-label-success"><i
                                                        class="ti tabler-arrow-down-left fs-6 me-1"></i> IN</span>
                                            @elseif($row->type == 'Outbound')
                                                <span class="badge bg-label-danger"><i
                                                        class="ti tabler-arrow-up-right fs-6 me-1"></i> OUT</span>
                                            @else
                                                <span class="badge bg-label-primary"><i
                                                        class="ti tabler-arrows-diff fs-6 me-1"></i> MOVE</span>
                                            @endif
                                        </td>
                                        <td><small>{{ $row->inventory->client->name ?? '-' }}</small></td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span
                                                    class="fw-bold text-dark">{{ $row->inventory->part_name ?? '-' }}</span>
                                                <small class="text-muted">{{ $row->inventory->part_number ?? '-' }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span>{{ $row->inventory->brand->name ?? '-' }}</span>
                                                <small
                                                    class="text-secondary">{{ $row->inventory->productGroup->name ?? '-' }}</small>
                                            </div>
                                        </td>
                                        <td class="fw-bold text-primary">{{ $row->serial_number }}</td>
                                        <td class="small fw-bold">{{ $row->inventory->unique_id ?? '-' }}</td>
                                        <td><span class="badge bg-label-secondary small"
                                                style="font-size: 0.65rem;">{{ $row->category }}</span></td>
                                        <td><span class="text-dark fw-bold">{{ $row->reference_number }}</span></td>
                                        <td><small class="text-muted">{{ $row->from_location ?: '-' }}</small></td>
                                        <td><span class="badge bg-label-info">{{ $row->to_location ?: '-' }}</span></td>
                                        <td><span class="small">{{ $row->user ?? 'System' }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center py-5">No records in this period.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    {{ $data->appends(request()->input())->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://cdn.rawgit.com/rainabba/jquery-table2excel/1.1.0/dist/jquery.table2excel.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#btnExportExcel').click(function() {
                $("#cycleCountTable").table2excel({
                    exclude: ".noExl",
                    name: "MovementPeriodReport",
                    filename: "CycleCount-" + "{{ $startDate }}" + "-to-" +
                        "{{ $endDate }}" + ".xls",
                    fileext: ".xls",
                    exclude_img: true,
                    exclude_links: true,
                    exclude_inputs: true
                });
            });
        });
    </script>
@endsection
