@extends('layout.index')
@section('title', 'Inbound vs Outbound Trend')

@section('css')
<style>
    .trend-card { border-radius: 16px; transition: all 0.3s; }
    .trend-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.08) !important; }
    .net-positive { color: #28c76f; }
    .net-negative { color: #ea5455; }
</style>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h4 class="fw-bold mb-0"><i class="ti tabler-trending-up me-2 text-primary"></i> Inbound vs Outbound Trend</h4>
            <p class="text-muted small mb-0">Spare part movement trend over the last 12 months.</p>
        </div>
        @if (Auth::user()->isAdminWMS() || Auth::user()->clients->count() > 1)
            <form action="{{ route('inboundReturn') }}" method="GET" class="d-flex">
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
    $totalInbound = $trendData->sum('inbound');
    $totalOutbound = $trendData->sum('outbound');
    $netTotal = $totalInbound - $totalOutbound;
    $avgInbound = $trendData->count() > 0 ? round($totalInbound / $trendData->count()) : 0;
    $avgOutbound = $trendData->count() > 0 ? round($totalOutbound / $trendData->count()) : 0;
    $lastInbound = $trendData->last()['inbound'] ?? 0;
    $lastOutbound = $trendData->last()['outbound'] ?? 0;
@endphp

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card trend-card border-0 shadow-sm border-start border-success border-4">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div><i class="ti tabler-download fs-1 text-success"></i></div>
                    <div>
                        <h6 class="text-muted mb-0">Total Inbound</h6>
                        <h3 class="fw-bold mb-0 text-success">{{ number_format($totalInbound) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card trend-card border-0 shadow-sm border-start border-warning border-4">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div><i class="ti tabler-upload fs-1 text-warning"></i></div>
                    <div>
                        <h6 class="text-muted mb-0">Total Outbound</h6>
                        <h3 class="fw-bold mb-0 text-warning">{{ number_format($totalOutbound) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card trend-card border-0 shadow-sm border-start border-primary border-4">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div><i class="ti tabler-activity fs-1 text-primary"></i></div>
                    <div>
                        <h6 class="text-muted mb-0">Net Change</h6>
                        <h3 class="fw-bold mb-0 {{ $netTotal >= 0 ? 'text-primary' : 'text-danger' }}">
                            {{ $netTotal >= 0 ? '+' : '' }}{{ number_format($netTotal) }}
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card trend-card border-0 shadow-sm border-start border-info border-4">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div><i class="ti tabler-calculator fs-1 text-info"></i></div>
                    <div>
                        <h6 class="text-muted mb-0">Monthly Avg</h6>
                        <h3 class="fw-bold mb-0 text-info">{{ number_format($avgInbound) }} / {{ number_format($avgOutbound) }}</h3>
                        <small class="text-muted">In / Out</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Big Trend Chart -->
    <div class="col-xl-8">
        <div class="card trend-card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="ti tabler-chart-area me-2 text-primary"></i>Movement Trend
                </h5>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary active" id="btnArea">Area</button>
                    <button class="btn btn-outline-primary" id="btnBar">Bar</button>
                </div>
            </div>
            <div class="card-body">
                <div id="movementTrendDetailedChart" style="height:400px;"></div>
            </div>
        </div>
    </div>

    <!-- Cumulative / Monthly Data -->
    <div class="col-xl-4">
        <div class="card trend-card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="ti tabler-table me-2 text-info"></i>Monthly Breakdown
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:400px;">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Month</th>
                                <th class="text-end text-success">In</th>
                                <th class="text-end text-warning">Out</th>
                                <th class="text-end">Net</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($trendData as $trend)
                                @php $net = $trend['inbound'] - $trend['outbound']; @endphp
                                <tr>
                                    <td class="fw-semibold">{{ $trend['month'] }}</td>
                                    <td class="text-end text-success fw-bold">+{{ number_format($trend['inbound']) }}</td>
                                    <td class="text-end text-warning fw-bold">-{{ number_format($trend['outbound']) }}</td>
                                    <td class="text-end fw-bold {{ $net >= 0 ? 'text-primary' : 'text-danger' }}">
                                        {{ $net >= 0 ? '+' : '' }}{{ number_format($net) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-light py-2 border-0">
                <div class="d-flex justify-content-between small fw-bold">
                    <span>Total</span>
                    <span class="text-success">+{{ number_format($totalInbound) }}</span>
                    <span class="text-warning">-{{ number_format($totalOutbound) }}</span>
                    <span class="{{ $netTotal >= 0 ? 'text-primary' : 'text-danger' }}">
                        {{ $netTotal >= 0 ? '+' : '' }}{{ number_format($netTotal) }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
const trendMonths = @json($trendData->pluck('month'));
const trendInbound = @json($trendData->pluck('inbound'));
const trendOutbound = @json($trendData->pluck('outbound'));

let chartType = 'area';
let chart;

function renderChart(type) {
    const isArea = type === 'area';
    const opts = {
        series: [
            { name: 'Inbound', data: trendInbound },
            { name: 'Outbound', data: trendOutbound }
        ],
        chart: {
            type: isArea ? 'area' : 'bar',
            height: 400,
            toolbar: { show: true },
            zoom: { enabled: true },
            animations: { enabled: true }
        },
        colors: ['#28c76f', '#ff9f43'],
        dataLabels: { enabled: !isArea, style: { fontSize: '10px' } },
        stroke: { curve: 'smooth', width: isArea ? 3 : 2 },
        fill: isArea ? {
            type: 'gradient',
            gradient: { shadeIntensity: 1, opacityFrom: 0.6, opacityTo: 0.1 }
        } : { opacity: 0.85 },
        xaxis: {
            categories: trendMonths,
            labels: { style: { fontSize: '11px' } }
        },
        yaxis: { title: { text: 'Items' }, labels: { style: { fontSize: '11px' } } },
        legend: { position: 'top' },
        markers: isArea ? { size: 4, hover: { size: 6 } } : { size: 0 },
        tooltip: {
            shared: true,
            intersect: false,
            y: { formatter: function(v) { return v + ' items'; } }
        },
        grid: { borderColor: '#f1f1f1' },
        plotOptions: isArea ? {} : {
            bar: {
                horizontal: false,
                columnWidth: '50%',
                borderRadius: 4,
            }
        }
    };

    if (chart) chart.destroy();
    chart = new ApexCharts(document.querySelector("#movementTrendDetailedChart"), opts);
    chart.render();
}

// Toggle buttons
document.getElementById('btnArea').addEventListener('click', function() {
    document.getElementById('btnArea').classList.add('active');
    document.getElementById('btnBar').classList.remove('active');
    renderChart('area');
});
document.getElementById('btnBar').addEventListener('click', function() {
    document.getElementById('btnBar').classList.add('active');
    document.getElementById('btnArea').classList.remove('active');
    renderChart('bar');
});

// Initial render
renderChart('area');
</script>
@endsection
