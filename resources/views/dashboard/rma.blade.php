@extends('layout.index')
@section('title', 'RMA Monitoring')

@section('css')
<style>
    .swap-timeline { position: relative; }
    .swap-timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0; bottom: 0;
        width: 2px;
        background: #e9ecef;
    }
    .swap-item { position: relative; padding-left: 3rem; padding-bottom: 1rem; }
    .swap-item:last-child { padding-bottom: 0; }
    .swap-dot {
        position: absolute;
        left: 8px; top: 4px;
        width: 16px; height: 16px;
        border-radius: 50%;
        border: 3px solid #fff;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        z-index: 1;
    }
    .rma-card { border-radius: 16px; transition: all 0.3s; }
    .rma-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.08) !important; }
</style>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h4 class="fw-bold mb-0"><i class="ti tabler-arrows-exchange me-2 text-danger"></i> RMA Monitoring</h4>
            <p class="text-muted small mb-0">SN Swap Tracking — Original vs Replacement serial numbers.</p>
        </div>
        @if (Auth::user()->isAdminWMS() || Auth::user()->clients->count() > 1)
            <form action="{{ route('rmaMonitoring') }}" method="GET" class="d-flex">
                <select name="client_id" class="form-select form-select-sm me-2" onchange="this.form.submit()">
                    <option value="">All Clients</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                            {{ $client->name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-primary">Filter</button>
            </form>
        @endif
    </div>
</div>

@php
    $totalRma = $data->total();
    $monthRma = $data->filter(fn($i) => $i->created_at && $i->created_at->month === now()->month && $i->created_at->year === now()->year)->count();
    $faultyCount = $data->filter(fn($i) => $i->condition === 'Faulty')->count();
    $goodCount = $data->filter(fn($i) => in_array($i->condition, ['New','Refurbished','Good']))->count();
@endphp

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card rma-card border-0 shadow-sm border-start border-danger border-4">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div><i class="ti tabler-arrows-exchange fs-1 text-danger"></i></div>
                    <div>
                        <h6 class="text-muted mb-0">Total Swaps</h6>
                        <h3 class="fw-bold mb-0">{{ number_format($totalRma) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card rma-card border-0 shadow-sm border-start border-warning border-4">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div><i class="ti tabler-calendar fs-1 text-warning"></i></div>
                    <div>
                        <h6 class="text-muted mb-0">This Month</h6>
                        <h3 class="fw-bold mb-0">{{ number_format($monthRma) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card rma-card border-0 shadow-sm border-start border-success border-4">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div><i class="ti tabler-circle-check fs-1 text-success"></i></div>
                    <div>
                        <h6 class="text-muted mb-0">Good/Refurb</h6>
                        <h3 class="fw-bold mb-0">{{ number_format($goodCount) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card rma-card border-0 shadow-sm border-start border-danger border-4">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div><i class="ti tabler-alert-triangle fs-1 text-danger"></i></div>
                    <div>
                        <h6 class="text-muted mb-0">Faulty</h6>
                        <h3 class="fw-bold mb-0">{{ number_format($faultyCount) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Table -->
    <div class="col-12">
        <div class="card rma-card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="ti tabler-table me-2 text-primary"></i>Swap History
                </h5>
                <span class="badge bg-label-primary rounded-pill px-3">{{ $data->total() }} Records</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th style="width:50px;">#</th>
                                <th>Product Name</th>
                                <th>Part Number</th>
                                <th>Old SN (Original)</th>
                                <th>New SN (Replacement)</th>
                                <th>Condition</th>
                                <th>Date Swapped</th>
                                @if (Auth::user()->isAdminWMS())
                                    <th style="width:80px;">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $item)
                                <tr>
                                    <td>{{ ($data->currentPage() - 1) * $data->perPage() + $loop->iteration }}</td>
                                    <td class="fw-semibold">{{ $item->part_name }}</td>
                                    <td><span class="small text-muted">{{ $item->part_number ?: '-' }}</span></td>
                                    <td>
                                        <span class="badge bg-label-secondary font-monospace" style="font-size:0.7rem;">
                                            {{ $item->old_serial_number }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-primary font-monospace" style="font-size:0.7rem;">
                                            {{ $item->serial_number }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $condClass = match($item->condition) {
                                                'New', 'Refurbished', 'Good' => 'bg-label-success',
                                                'Faulty', 'Write-off Needed' => 'bg-label-danger',
                                                default => 'bg-label-info'
                                            };
                                        @endphp
                                        <span class="badge {{ $condClass }}" style="font-size:0.7rem;">{{ $item->condition }}</span>
                                    </td>
                                    <td><span class="small text-muted">{{ $item->created_at->format('d/m/Y H:i') }}</span></td>
                                    @if (Auth::user()->isAdminWMS())
                                        <td>
                                            <form action="{{ route('rmaMonitoring.delete', $item->id) }}" method="POST" class="delete-rma-form">
                                                @csrf
                                                <button type="button" class="btn btn-sm btn-outline-danger border-0 delete-rma-btn"
                                                        title="Revert RMA record"
                                                        data-sn="{{ $item->serial_number }}"
                                                        data-old-sn="{{ $item->old_serial_number }}">
                                                    <i class="ti tabler-rotate-2"></i>
                                                </button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                            @if ($data->isEmpty())
                                <tr>
                                    <td colspan="{{ Auth::user()->isAdminWMS() ? 8 : 7 }}" class="text-center py-5 text-muted">
                                        <i class="ti tabler-arrows-exchange fs-1 d-block mb-2"></i>
                                        No RMA swaps recorded yet.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($data->hasPages())
                <div class="card-footer bg-white py-3 px-3 border-0">
                    {{ $data->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('js')
@if (Auth::user()->isAdminWMS())
<script>
    document.querySelectorAll('.delete-rma-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const form = this.closest('.delete-rma-form');
            const sn = this.dataset.sn;
            const oldSn = this.dataset.oldSn;

            Swal.fire({
                title: 'Revert RMA Record?',
                html: `This will revert the RMA swap record:<br>
                       <strong>Old SN:</strong> ${oldSn}<br>
                       <strong>New SN:</strong> ${sn}<br><br>
                       <span class="text-muted small">The InboundDetail will be soft-deleted (data kept).<br>
                       If already in inventory, stock will be set to unavailable (qty=0).</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, revert it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endif
@endsection
