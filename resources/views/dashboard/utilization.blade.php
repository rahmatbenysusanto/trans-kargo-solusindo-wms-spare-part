@extends('layout.index')
@section('title', 'Utilization by Client')

@section('css')
<style>
    .util-card {
        border-radius: 16px;
        transition: all 0.3s ease;
    }
    .progress-thin {
        height: 8px;
        border-radius: 4px;
    }
    .rank-badge {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.75rem;
    }
</style>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h4 class="fw-bold mb-0"><i class="ti tabler-users me-2 text-primary"></i> Utilization by Client</h4>
            <p class="text-muted small mb-0">Distribution of spare part usage across clients.</p>
        </div>
        <div class="d-flex gap-2">
            @if (Auth::user()->isAdminWMS() || Auth::user()->clients->count() > 1)
                <form action="{{ route('utilizationByClient') }}" method="GET" class="d-flex">
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
            <button class="btn btn-sm btn-outline-success" onclick="window.print()">
                <i class="ti tabler-printer me-1"></i> Print
            </button>
        </div>
    </div>
</div>

@php
    $totalOrders = $data->sum('total_orders');
    $totalItems = $data->sum('total_items');
    $topClient = $data->sortByDesc('total_items')->first();
@endphp

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card util-card border-0 shadow-sm bg-primary text-white h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="fs-1 opacity-50"><i class="ti tabler-building"></i></div>
                    <div>
                        <h6 class="text-white-50 mb-0">Active Clients</h6>
                        <h3 class="fw-bold mb-0 text-white">{{ $data->count() }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card util-card border-0 shadow-sm bg-success text-white h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="fs-1 opacity-50"><i class="ti tabler-box-seam"></i></div>
                    <div>
                        <h6 class="text-white-50 mb-0">Total Items</h6>
                        <h3 class="fw-bold mb-0 text-white">{{ number_format($totalItems) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card util-card border-0 shadow-sm bg-warning h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="fs-1 opacity-50"><i class="ti tabler-shopping-cart"></i></div>
                    <div>
                        <h6 class="mb-0" style="color:rgba(0,0,0,0.5);">Total Orders</h6>
                        <h3 class="fw-bold mb-0">{{ number_format($totalOrders) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card util-card border-0 shadow-sm bg-info text-white h-100">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="fs-1 opacity-50"><i class="ti tabler-trophy"></i></div>
                    <div>
                        <h6 class="text-white-50 mb-0">Top Client</h6>
                        <h3 class="fw-bold mb-0 text-white text-truncate" style="max-width:160px;">{{ $topClient?->client?->name ?? '-' }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Chart -->
    <div class="col-xl-7">
        <div class="card util-card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="ti tabler-chart-bar me-2 text-primary"></i>Orders & Items per Client
                </h5>
            </div>
            <div class="card-body">
                <div id="clientUtilizationDetailChart" style="height:380px;"></div>
            </div>
        </div>
    </div>

    <!-- Ranking Table -->
    <div class="col-xl-5">
        <div class="card util-card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="ti tabler-ranking me-2 text-warning"></i>Client Ranking
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th style="width:50px;">Rank</th>
                                <th>Client</th>
                                <th class="text-center">Orders</th>
                                <th class="text-center">Items</th>
                                <th class="text-end">Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $i => $item)
                                <tr>
                                    <td>
                                        <span class="rank-badge bg-{{ $i == 0 ? 'warning' : ($i == 1 ? 'secondary' : ($i == 2 ? 'danger' : 'light')) }} text-{{ $i < 3 ? 'white' : 'dark' }}">
                                            {{ $i + 1 }}
                                        </span>
                                    </td>
                                    <td class="fw-semibold">{{ $item->client->name ?? 'Unknown' }}</td>
                                    <td class="text-center">{{ number_format($item->total_orders) }}</td>
                                    <td class="text-center">
                                        <span class="fw-bold">{{ number_format($item->total_items) }}</span>
                                    </td>
                                    <td class="text-end">
                                        @php $share = $totalItems > 0 ? round(($item->total_items / $totalItems) * 100, 1) : 0; @endphp
                                        <div class="d-flex align-items-center justify-content-end gap-2">
                                            <span class="fw-bold small">{{ $share }}%</span>
                                            <div class="progress progress-thin" style="width:80px;">
                                                <div class="progress-bar bg-primary" style="width: {{ $share }}%"></div>
                                            </div>
                                        </div>
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
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
const clientNames = @json($data->map(fn($i) => $i->client->name ?? 'Unknown'));
const orderCounts = @json($data->pluck('total_orders'));
const itemCounts = @json($data->pluck('total_items'));

new ApexCharts(document.querySelector("#clientUtilizationDetailChart"), {
    series: [
        { name: 'Orders', data: orderCounts },
        { name: 'Items Shipped', data: itemCounts }
    ],
    chart: {
        type: 'bar', height: 380,
        toolbar: { show: true },
        animations: { enabled: true }
    },
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '50%',
            borderRadius: 6,
        }
    },
    colors: ['#7367f0', '#28c76f'],
    dataLabels: { enabled: false },
    stroke: { show: true, width: 2, colors: ['transparent'] },
    xaxis: { categories: clientNames, labels: { style: { fontSize: '11px' } } },
    yaxis: { title: { text: 'Count' }, labels: { style: { fontSize: '11px' } } },
    fill: { opacity: 1 },
    tooltip: { y: { formatter: function(v) { return v + ' units'; } } },
    legend: { position: 'top' },
    grid: { borderColor: '#f1f1f1' }
}).render();
</script>
@endsection
