@extends('layout.index')
@section('title', 'Stock Monitoring')

@section('css')
<style>
    .mon-card { border-radius: 16px; transition: all 0.3s; }
    .mon-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.08) !important; }
    .stock-bar {
        height: 6px;
        border-radius: 3px;
        transition: width 0.6s ease;
    }
    .low-stock { color: #ea5455; font-weight: 700; }
    .healthy-stock { color: #28c76f; font-weight: 700; }
    .medium-stock { color: #ff9f43; font-weight: 700; }
</style>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h4 class="fw-bold mb-0"><i class="ti tabler-eye me-2 text-primary"></i> Stock Monitoring</h4>
            <p class="text-muted small mb-0">Real-time stock quantity availability by product.</p>
        </div>
        @if (Auth::user()->isAdminWMS() || Auth::user()->clients->count() > 1)
            <form action="{{ route('stockMonitoring') }}" method="GET" class="d-flex">
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
    $totalProducts = $data->count();
    $totalQty = $data->sum('total_qty');
    $lowStockCount = $data->filter(fn($i) => $i->total_qty > 0 && $i->total_qty < 10)->count();
    $outOfStock = $data->filter(fn($i) => $i->total_qty == 0)->count();
    $healthyCount = $data->filter(fn($i) => $i->total_qty >= 10)->count();
@endphp

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card mon-card border-0 shadow-sm border-start border-info border-4">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div><i class="ti tabler-package fs-1 text-info"></i></div>
                    <div>
                        <h6 class="text-muted mb-0">Products</h6>
                        <h3 class="fw-bold mb-0">{{ number_format($totalProducts) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card mon-card border-0 shadow-sm border-start border-success border-4">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div><i class="ti tabler-box-seam fs-1 text-success"></i></div>
                    <div>
                        <h6 class="text-muted mb-0">Total Units</h6>
                        <h3 class="fw-bold mb-0">{{ number_format($totalQty) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card mon-card border-0 shadow-sm border-start border-success border-4">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div><i class="ti tabler-circle-check fs-1 text-success"></i></div>
                    <div>
                        <h6 class="text-muted mb-0">Healthy (≥10)</h6>
                        <h3 class="fw-bold mb-0 text-success">{{ number_format($healthyCount) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card mon-card border-0 shadow-sm border-start border-danger border-4">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div><i class="ti tabler-alert-triangle fs-1 text-danger"></i></div>
                    <div>
                        <h6 class="text-muted mb-0">Low/Out (<10)</h6>
                        <h3 class="fw-bold mb-0 text-danger">{{ number_format($lowStockCount + $outOfStock) }}</h3>
                        <small class="text-muted">Out: {{ $outOfStock }} · Low: {{ $lowStockCount }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Chart -->
    <div class="col-xl-7">
        <div class="card mon-card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="ti tabler-bar-chart me-2 text-primary"></i>Stock Level by Product
                </h5>
                <span class="badge bg-label-info rounded-pill">Top {{ min(10, count($data)) }}</span>
            </div>
            <div class="card-body">
                <div id="stockAvailabilityDetailChart" style="height:380px;"></div>
            </div>
        </div>
    </div>

    <!-- Alert List -->
    <div class="col-xl-5">
        <div class="card mon-card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="ti tabler-bell me-2 text-warning"></i>Stock Alerts
                    @if ($lowStockCount + $outOfStock > 0)
                        <span class="badge bg-danger ms-2">{{ $lowStockCount + $outOfStock }}</span>
                    @endif
                </h5>
            </div>
            <div class="card-body p-0">
                @php
                    $alerts = $data->filter(fn($i) => $i->total_qty < 10)->sortBy('total_qty');
                @endphp
                @if ($alerts->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach ($alerts as $item)
                            <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div class="min-w-0" style="flex:1;">
                                    <div class="fw-semibold text-truncate">{{ $item->part_name }}</div>
                                    <small class="text-muted text-truncate d-block">{{ $item->part_number ?: '-' }}</small>
                                </div>
                                <div class="text-end ms-3">
                                    @if ($item->total_qty == 0)
                                        <span class="badge bg-danger rounded-pill px-3">Out of Stock</span>
                                    @elseif($item->total_qty < 5)
                                        <span class="badge bg-warning rounded-pill px-3">{{ $item->total_qty }} left</span>
                                    @else
                                        <span class="badge bg-info rounded-pill px-3">{{ $item->total_qty }} left</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="ti tabler-circle-check fs-1 text-success d-block mb-2"></i>
                        <h6 class="fw-bold">All Stock Healthy</h6>
                        <small>No low stock alerts</small>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Full Table -->
<div class="row g-3 mt-2">
    <div class="col-12">
        <div class="card mon-card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="ti tabler-table me-2 text-secondary"></i>All Products
                </h5>
                <div class="d-flex gap-2">
                    <input type="text" class="form-control form-control-sm" id="tableSearch" placeholder="Cari produk..." style="width:200px;" onkeyup="filterTable()">
                    <span class="badge bg-label-secondary rounded-pill align-self-center px-3">{{ $data->count() }} products</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:500px;">
                    <table class="table table-hover align-middle mb-0 small" id="stockTable">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>#</th>
                                <th>Part Number</th>
                                <th>Description</th>
                                <th class="text-center">Total Qty</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $i => $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td><span class="text-muted">{{ $item->part_number ?: '-' }}</span></td>
                                    <td class="text-muted text-truncate" style="max-width:200px;">{{ $item->part_description ?: '-' }}</td>
                                    <td class="text-center">
                                        <span class="fw-bold fs-6 {{ $item->total_qty == 0 ? 'low-stock' : ($item->total_qty < 5 ? 'medium-stock' : 'healthy-stock') }}">
                                            {{ number_format($item->total_qty) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($item->total_qty == 0)
                                            <span class="badge bg-danger">Out of Stock</span>
                                        @elseif($item->total_qty < 5)
                                            <span class="badge bg-warning">Critical</span>
                                        @elseif($item->total_qty < 10)
                                            <span class="badge bg-info">Low Stock</span>
                                        @else
                                            <span class="badge bg-success">Healthy</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function filterTable() {
    const q = document.getElementById('tableSearch').value.toLowerCase();
    document.querySelectorAll('#stockTable tbody tr').forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(q) ? '' : 'none';
    });
}
</script>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
const topData = @json($data->take(10));
const labels = topData.map(i => i.part_name);
const qtys = topData.map(i => i.total_qty);
const colors = qtys.map(v => v == 0 ? '#ea5455' : v < 5 ? '#ff9f43' : v < 10 ? '#00cfe8' : '#28c76f');

new ApexCharts(document.querySelector("#stockAvailabilityDetailChart"), {
    series: [{ name: 'Qty', data: qtys }],
    chart: {
        type: 'bar', height: 380,
        toolbar: { show: true },
        animations: { enabled: true }
    },
    colors: ['#7367f0'],
    plotOptions: {
        bar: {
            borderRadius: 4,
            columnWidth: '50%',
            distributed: true,
            colors: {
                ranges: [{
                    from: 0, to: 0, color: '#ea5455'
                }, {
                    from: 1, to: 4, color: '#ff9f43'
                }, {
                    from: 5, to: 9, color: '#00cfe8'
                }, {
                    from: 10, to: 99999, color: '#28c76f'
                }]
            }
        }
    },
    dataLabels: { enabled: true, style: { fontSize: '11px' } },
    xaxis: {
        categories: labels,
        labels: { rotate: -30, style: { fontSize: '10px' }, trim: true, maxHeight: 80 }
    },
    yaxis: { title: { text: 'Qty' } },
    title: { text: 'Top 10 Products by Stock Level', align: 'left', style: { fontSize: '13px' } },
    legend: { show: false },
    tooltip: { y: { formatter: function(v) { return v + ' units'; } } },
    grid: { borderColor: '#f1f1f1' }
}).render();
</script>
@endsection
