@extends('layout.index')
@section('title', 'Movement Log & Statistics')
@section('layout_class', 'layout-menu-collapsed')

@section('content')
    <div class="row">
        <div class="col-12 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1 fw-bold text-dark">
                        <i class="ti tabler-arrows-diff me-2 text-primary"></i> Movement Log
                    </h4>
                    <p class="text-muted mb-0">Detailed log of all inventory changes and location shifts</p>
                </div>
                <div class="d-flex gap-2">
                    <button id="btnExportExcel" class="btn btn-label-success fw-bold px-3">
                        <i class="ti tabler-file-spreadsheet me-2"></i> Export Excel
                    </button>
                </div>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="col-12 mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm bg-white"
                        style="border-radius: 12px; border-left: 4px solid #28c76f !important;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="badge bg-label-success p-2 me-3 rounded-3">
                                    <i class="ti tabler-download fs-3"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-muted small fw-bold text-uppercase ls-1">Inbound Activity</h6>
                                    <h4 class="mb-0 fw-bold">{{ $summary['inbound'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm bg-white"
                        style="border-radius: 12px; border-left: 4px solid #ea5455 !important;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="badge bg-label-danger p-2 me-3 rounded-3">
                                    <i class="ti tabler-upload fs-3"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-muted small fw-bold text-uppercase ls-1">Outbound Activity</h6>
                                    <h4 class="mb-0 fw-bold">{{ $summary['outbound'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm bg-white"
                        style="border-radius: 12px; border-left: 4px solid #7367f0 !important;">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="badge bg-label-primary p-2 me-3 rounded-3">
                                    <i class="ti tabler-arrows-exchange fs-3"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-muted small fw-bold text-uppercase ls-1">Internal Movements</h6>
                                    <h4 class="mb-0 fw-bold">{{ $summary['movement'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body p-4 bg-white" style="border-radius: 16px;">
                    <form action="{{ url()->current() }}" method="GET" class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-dark">CLIENT</label>
                            <select name="client_id" class="form-select border-light-subtle select2">
                                <option value="">All Clients</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}" {{ $clientId == $client->id ? 'selected' : '' }}>
                                        {{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-dark">PERIOD</label>
                            <div class="input-group input-group-merge">
                                <input type="date" name="start_date" class="form-control border-light-subtle"
                                    value="{{ $startDate }}">
                                <span class="input-group-text bg-light border-light-subtle">-</span>
                                <input type="date" name="end_date" class="form-control border-light-subtle"
                                    value="{{ $endDate }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-dark">TYPE</label>
                            <select name="type" class="form-select border-light-subtle">
                                <option value="">All Types</option>
                                <option value="Inbound" {{ $type == 'Inbound' ? 'selected' : '' }}>Inbound</option>
                                <option value="Outbound" {{ $type == 'Outbound' ? 'selected' : '' }}>Outbound</option>
                                <option value="Movement" {{ $type == 'Movement' ? 'selected' : '' }}>Movement</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold text-dark">SEARCH</label>
                            <input type="text" name="search" class="form-control border-light-subtle"
                                placeholder="Part, SN, Ref..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary fw-bold w-100 py-2 shadow-sm">
                                <i class="ti tabler-search"></i> Apply
                            </button>
                            <a href="{{ url()->current() }}" class="btn btn-label-secondary fw-bold px-3 py-2">
                                <i class="ti tabler-rotate"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <div class="card-body p-0 bg-white">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 custom-table" id="cycleCountTable">
                            <thead class="bg-label-primary text-uppercase small fw-bold">
                                <tr>
                                    <th class="ps-4">Timestamp</th>
                                    <th>Activity</th>
                                    <th>Product Identity</th>
                                    <th>Serial Number</th>
                                    <th>Ref Number</th>
                                    <th>Path</th>
                                    <th class="pe-4 text-center">User</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $row)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold text-dark"
                                                    style="font-size: 0.9rem;">{{ \Carbon\Carbon::parse($row->created_at)->format('d M Y') }}</span>
                                                <small class="text-muted fw-bold"
                                                    style="font-size: 0.7rem;">{{ \Carbon\Carbon::parse($row->created_at)->format('H:i') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $badgeClass = 'bg-label-success';
                                                $icon = 'arrow-down-left';
                                                if ($row->type == 'Outbound') {
                                                    $badgeClass = 'bg-label-danger';
                                                    $icon = 'arrow-up-right';
                                                } elseif ($row->type == 'Movement') {
                                                    $badgeClass = 'bg-label-info';
                                                    $icon = 'arrows-exchange';
                                                }
                                            @endphp
                                            <div class="d-flex align-items-center">
                                                <span class="badge {{ $badgeClass }} p-1 rounded-circle me-2"><i
                                                        class="ti tabler-{{ $icon }} fs-6"></i></span>
                                                <span
                                                    class="fw-bold text-dark small text-uppercase">{{ $row->type }}</span>
                                            </div>
                                            <small class="text-muted fw-medium d-block mt-1"
                                                style="font-size: 0.65rem;">CAT: {{ $row->category }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span
                                                    class="fw-bold text-dark small">{{ $row->inventory->part_name ?? '-' }}</span>
                                                <small class="text-muted"
                                                    style="font-size: 0.65rem;">{{ $row->inventory->part_number ?? '-' }}</small>
                                            </div>
                                        </td>
                                        <td><span
                                                class="fw-bold text-primary font-monospace">{{ $row->serial_number }}</span>
                                        </td>
                                        <td><span class="badge bg-light text-secondary border-light-subtle fw-bold"
                                                style="font-size: 0.7rem;">{{ $row->reference_number }}</span></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span
                                                    class="badge bg-light text-muted border-light-subtle py-1 px-2 fs-tiny fw-bold">{{ $row->from_location ?: 'SUPPLIER' }}</span>
                                                <i class="ti tabler-chevron-right text-primary opacity-50"></i>
                                                <span
                                                    class="badge bg-label-primary border-0 py-1 px-2 fs-tiny fw-bold">{{ $row->to_location ?: 'CLIENT' }}</span>
                                            </div>
                                        </td>
                                        <td class="pe-4 text-center">
                                            <div class="d-flex flex-column align-items-center">
                                                <span class="badge bg-label-dark p-2 rounded-circle mb-1"><i
                                                        class="ti tabler-user fs-6"></i></span>
                                                <small class="fw-bold text-muted"
                                                    style="font-size: 0.7rem;">{{ $row->user ?? 'System' }}</small>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="py-5 opacity-50 text-center">
                                                <i class="ti tabler-database-x fs-1 mb-3"></i>
                                                <h5 class="fw-bold">No Records Found</h5>
                                                <p class="small">Try adjusting your filters for the selected period.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top py-4 px-4">
                    {{ $data->appends(request()->input())->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>

    <style>
        .custom-table thead th {
            font-size: 0.75rem;
            letter-spacing: 0.8px;
            color: #7367f0;
            background: rgba(115, 103, 240, 0.05);
            border-bottom: 2px solid rgba(115, 103, 240, 0.1);
            padding: 1.2rem;
        }

        .custom-table tbody td {
            padding: 1.2rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.03);
        }

        .form-select,
        .form-control {
            border-radius: 8px;
            padding: 0.6rem 1rem;
        }

        .fs-tiny {
            font-size: 0.65rem;
        }

        .ls-1 {
            letter-spacing: 1px;
        }

        @media print {

            .btn,
            .sidebar,
            .card-footer,
            form,
            .layout-navbar {
                display: none !important;
            }

            .card {
                box-shadow: none !important;
                border: 1px solid #eee !important;
            }
        }
    </style>
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
