@extends('layout.index')
@section('title', 'SN & Date Movement History')
@section('layout_class', 'layout-menu-collapsed')

@section('content')
    <div class="container-fluid flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12 mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1 text-primary fw-bold"><i class="ti tabler-arrows-left-right me-2"></i>Movement History
                        </h4>
                        <p class="text-muted mb-0 small text-uppercase ls-1 fw-medium mt-n1">Tracks every Inbound & Outbound
                            transaction across the warehouse</p>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="col-12 mb-4">
                <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <form action="{{ route('reporting.movement-history') }}" method="GET">
                            <div class="row align-items-end g-3">
                                <div class="col-md-3">
                                    <label class="form-label small text-muted text-uppercase fw-bold">Serial Number</label>
                                    <input type="text" class="form-control border-0 bg-light-subtle shadow-none"
                                        name="sn" value="{{ request('sn') }}" placeholder="Search SN...">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small text-muted text-uppercase fw-bold">Start Date</label>
                                    <input type="date" class="form-control border-0 bg-light-subtle shadow-none"
                                        name="start_date" value="{{ request('start_date') }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small text-muted text-uppercase fw-bold">End Date</label>
                                    <input type="date" class="form-control border-0 bg-light-subtle shadow-none"
                                        name="end_date" value="{{ request('end_date') }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small text-muted text-uppercase fw-bold">Type</label>
                                    <select class="form-select border-0 bg-light-subtle fw-bold" name="type">
                                        <option value="">All Types</option>
                                        <option value="Inbound" {{ request('type') == 'Inbound' ? 'selected' : '' }}>Inbound
                                        </option>
                                        <option value="Outbound" {{ request('type') == 'Outbound' ? 'selected' : '' }}>
                                            Outbound</option>
                                        <option value="Movement" {{ request('type') == 'Movement' ? 'selected' : '' }}>
                                            Internal Movement</option>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary waves-effect w-100 fw-bold py-2">
                                        <i class="ti tabler-filter me-1"></i> Filter
                                    </button>
                                    <a href="{{ route('reporting.movement-history') }}"
                                        class="btn btn-label-secondary waves-effect py-2">
                                        <i class="ti tabler-rotate"></i>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 12px;">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-uppercase fs-tiny fw-bold border-top-0">
                                    <tr>
                                        <th class="ps-4">Timestamp</th>
                                        <th>Ref#</th>
                                        <th>Activity</th>
                                        <th>Serial Number</th>
                                        <th>Product</th>
                                        <th>Route</th>
                                        <th class="pe-4 text-center">User</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $history)
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex flex-column text-nowrap">
                                                    <span
                                                        class="fw-bold text-dark">{{ $history->created_at->format('Y-m-d') }}</span>
                                                    <small
                                                        class="text-muted fs-tiny">{{ $history->created_at->format('H:i:s') }}</small>
                                                </div>
                                            </td>
                                            <td><span
                                                    class="badge bg-label-secondary border-0">{{ $history->reference_number ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if ($history->type == 'Inbound')
                                                        <span class="badge bg-label-success p-1 rounded-circle me-2"><i
                                                                class="ti tabler-arrow-down-left fs-6"></i></span>
                                                    @elseif($history->type == 'Outbound')
                                                        <span class="badge bg-label-danger p-1 rounded-circle me-2"><i
                                                                class="ti tabler-arrow-up-right fs-6"></i></span>
                                                    @else
                                                        <span class="badge bg-label-info p-1 rounded-circle me-2"><i
                                                                class="ti tabler-arrows-exchange fs-6"></i></span>
                                                    @endif
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-bold text-dark">{{ $history->type }}</span>
                                                        <small
                                                            class="text-muted fs-tiny text-uppercase">{{ $history->category }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="fw-bold text-primary">{{ $history->serial_number }}</span>
                                            </td>
                                            <td>
                                                <div class="small fw-medium text-dark">
                                                    {{ $history->inventory->part_name ?? 'Unknown Product' }}</div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span
                                                        class="badge bg-light text-muted border py-1 px-2 fs-tiny fw-medium">{{ $history->from_location ?: 'START' }}</span>
                                                    <i class="ti tabler-arrow-right text-muted fs-tiny"></i>
                                                    <span
                                                        class="badge bg-label-primary border-0 py-1 px-2 fs-tiny fw-medium">{{ $history->to_location ?: 'END' }}</span>
                                                </div>
                                            </td>
                                            <td class="pe-4 text-center"><span class="small fw-medium text-muted"><i
                                                        class="ti tabler-user me-1"></i>{{ $history->user }}</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <i class="ti tabler-history-off fs-1 text-muted mb-3 d-block"></i>
                                                <h6 class="text-muted mb-0">No movement history matches your criteria.</h6>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top py-3 px-4">
                        {{ $data->appends(request()->input())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .fs-tiny {
            font-size: 0.65rem;
        }

        .ls-1 {
            letter-spacing: 1px;
        }

        .bg-light-subtle {
            background-color: #f8f9fa !important;
        }

        .table> :not(caption)>*>* {
            padding: 1.1rem 1.25rem;
        }

        .text-nowrap {
            white-space: nowrap !important;
        }
    </style>
    @section('js')
        <script>
            // Export functionality can be added here
        </script>
    @endsection
@endsection
