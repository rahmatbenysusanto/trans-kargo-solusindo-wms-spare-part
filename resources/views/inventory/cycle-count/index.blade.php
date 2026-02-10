@extends('layout.index')

@section('title', 'Cycle Count')

@section('content')
    <div class="row mb-4">
        <!-- Summary Cards -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-label-success">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="badge bg-success p-2 me-3">
                            <i class="ti tabler-download fs-3"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 text-success">Total Inbound</h6>
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
                            <h6 class="mb-0 text-danger">Total Outbound</h6>
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
                            <h6 class="mb-0 text-primary">Total Movement</h6>
                            <h4 class="mb-0 fw-bold">{{ $summary['movement'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">Daily Movement Report (Cycle Count)</h5>
                        <small class="text-muted">Tracking all warehouse activities for the selected date</small>
                    </div>
                    <form action="{{ url()->current() }}" method="GET" class="d-flex gap-2 align-items-center">
                        <label class="form-label mb-0">Select Date:</label>
                        <input type="date" name="date" class="form-control form-control-sm"
                            value="{{ $date }}" onchange="this.form.submit()">
                    </form>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Time</th>
                                    <th>Activity</th>
                                    <th>Asset# / SN</th>
                                    <th>Category</th>
                                    <th>Ref#</th>
                                    <th>Description</th>
                                    <th>Location (To)</th>
                                    <th>Handled By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $row)
                                    <tr>
                                        <td class="small fw-bold">
                                            {{ \Carbon\Carbon::parse($row->created_at)->format('H:i:s') }}</td>
                                        <td>
                                            @if ($row->type == 'Inbound')
                                                <span class="badge bg-label-success text-success"><i
                                                        class="ti tabler-circle-plus me-1"></i> IN</span>
                                            @elseif($row->type == 'Outbound')
                                                <span class="badge bg-label-danger text-danger"><i
                                                        class="ti tabler-circle-minus me-1"></i> OUT</span>
                                            @else
                                                <span class="badge bg-label-primary text-primary"><i
                                                        class="ti tabler-arrows-left-right me-1"></i> MOVE</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold">{{ $row->inventory->unique_id ?? 'N/A' }}</span>
                                                <small class="text-muted">{{ $row->serial_number }}</small>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-label-secondary small">{{ $row->category }}</span></td>
                                        <td><span class="text-dark">{{ $row->reference_number }}</span></td>
                                        <td class="small">{{ $row->description }}</td>
                                        <td>
                                            @if ($row->to_location)
                                                <span
                                                    class="badge bg-info bg-opacity-10 text-info fw-bold">{{ $row->to_location }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <span class="small">{{ $row->user ?? 'System' }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="ti tabler-list-search d-block mb-3" style="font-size: 3rem;"></i>
                                                <p>No transactions recorded on this date
                                                    ({{ \Carbon\Carbon::parse($date)->format('d M Y') }}).</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    {{ $data->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
