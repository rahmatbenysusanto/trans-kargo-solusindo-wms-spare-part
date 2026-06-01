@extends('layout.index')
@section('title', 'Stock Overview')

@section('css')
<style>
    /* === Card Styles === */
    .stat-card {
        border-radius: 16px;
        border: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        overflow: hidden;
        position: relative;
    }
    .stat-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 32px rgba(0,0,0,0.12) !important;
    }
    .stat-card .stat-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        flex-shrink: 0;
    }
    .stat-card .stat-value {
        font-size: 1.8rem;
        font-weight: 800;
        line-height: 1.2;
    }
    .stat-card .stat-label {
        font-size: 0.78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        opacity: 0.75;
    }
    .stat-card .stat-trend {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 3px;
    }

    /* === Chart Card === */
    .chart-card {
        border-radius: 16px;
        border: none;
        transition: all 0.3s ease;
    }
    .chart-card:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.08) !important;
    }
    .chart-card .card-header {
        border-bottom: 1px solid rgba(0,0,0,0.04);
        padding: 1rem 1.25rem;
    }
    .chart-card .card-title {
        font-size: 0.95rem;
        font-weight: 700;
    }

    /* === Activity Timeline === */
    .activity-timeline {
        position: relative;
        padding-left: 2rem;
    }
    .activity-timeline::before {
        content: '';
        position: absolute;
        left: 8px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(180deg, #7367f0 0%, rgba(115,103,240,0.1) 100%);
        border-radius: 4px;
    }
    .activity-item {
        position: relative;
        padding-bottom: 1.2rem;
    }
    .activity-item:last-child {
        padding-bottom: 0;
    }
    .activity-item .activity-dot {
        position: absolute;
        left: -2rem;
        top: 4px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: 3px solid #fff;
        box-shadow: 0 2px 6px rgba(0,0,0,0.12);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1;
    }

    /* === Table === */
    .dash-table th {
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
        color: #6c757d;
        border-bottom-width: 1px;
    }
    .dash-table td {
        font-size: 0.8rem;
        vertical-align: middle;
        padding: 0.6rem 0.75rem;
    }

    /* === Mini Sparkline === */
    .mini-chart {
        width: 80px;
        height: 32px;
        display: inline-block;
    }

    /* Status colors */
    .bg-soft-primary { background-color: rgba(115,103,240,0.12); }
    .bg-soft-success { background-color: rgba(40,199,111,0.12); }
    .bg-soft-warning { background-color: rgba(255,171,0,0.12); }
    .bg-soft-danger  { background-color: rgba(234,84,85,0.12); }
    .bg-soft-info    { background-color: rgba(0,207,232,0.12); }
    .bg-soft-dark    { background-color: rgba(75,75,75,0.08); }

    .text-gradient-primary {
        background: linear-gradient(135deg, #7367f0, #4834d4);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* Pulse animation for live indicator */
    @keyframes pulse-dot {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }
    .pulse-dot {
        animation: pulse-dot 2s ease-in-out infinite;
    }

    /* Loading skeleton */
    .chart-loading {
        min-height: 250px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h4 class="fw-bold mb-0">
                <i class="ti tabler-layout-dashboard me-2 text-primary"></i>
                Stock Overview
            </h4>
            <p class="text-muted small mb-0">
                <i class="ti tabler-calendar me-1"></i> {{ now()->format('l, d F Y') }}
                <span class="mx-2">|</span>
                <span class="pulse-dot text-success"><i class="ti tabler-circle-filled fs-9"></i></span> Live
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @if (Auth::user()->isAdminWMS() || Auth::user()->clients->count() > 1)
                <form action="{{ route('dashboard') }}" method="GET" class="d-flex">
                    <select name="client_id" class="form-select form-select-sm me-2"
                            onchange="this.form.submit()" style="min-width:160px;">
                        <option value="">All Clients</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                {{ $client->name }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary px-3">
                        <i class="ti tabler-filter me-1"></i> Filter
                    </button>
                </form>
            @endif
            <a href="{{ route('reporting.movement-history') }}" class="btn btn-sm btn-outline-secondary">
                <i class="ti tabler-history me-1"></i> Full History
            </a>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Card 1: In Stock -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card card bg-white shadow-sm h-100" onclick="openModal('in-stock', 'In Stock Items')">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div class="stat-icon bg-soft-success">
                    <i class="ti tabler-box-seam text-success"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="stat-value text-success">{{ number_format($inStockCount) }}</div>
                    <div class="stat-label text-muted">In Stock</div>
                    <div class="mt-1">
                        <span class="stat-trend bg-soft-success text-success">
                            <i class="ti tabler-circle-filled fs-10 me-1"></i> Available Units
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2: Outbounded -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card card bg-white shadow-sm h-100" onclick="openModal('outbounded', 'Outbounded Items')">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div class="stat-icon bg-soft-warning">
                    <i class="ti tabler-truck-return text-warning"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="stat-value text-warning">{{ number_format($outboundedCount) }}</div>
                    <div class="stat-label text-muted">Outbounded</div>
                    <div class="mt-1">
                        <span class="stat-trend bg-soft-warning text-warning">
                            <i class="ti tabler-arrow-up me-1"></i> Shipped Out
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 3: Total Stock Items -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card card bg-white shadow-sm h-100" onclick="openModal('in-stock', 'All Stock Items')">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div class="stat-icon bg-soft-primary">
                    <i class="ti tabler-database text-primary"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="stat-value text-primary">{{ number_format($totalStockCount) }}</div>
                    <div class="stat-label text-muted">Total Qty</div>
                    <div class="mt-1">
                        <span class="stat-trend bg-soft-primary text-primary">
                            <i class="ti tabler-list me-1"></i> All Items
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 4: RMA Processed -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card card bg-white shadow-sm h-100" onclick="openModal('rma', 'RMA Swaps')">
            <div class="card-body d-flex align-items-center gap-3 p-3">
                <div class="stat-icon bg-soft-danger">
                    <i class="ti tabler-arrows-exchange text-danger"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="stat-value text-danger">{{ $rmaStats->count ?? 0 }}</div>
                    <div class="stat-label text-muted">RMA Swaps</div>
                    <div class="mt-1">
                        <span class="stat-trend bg-soft-danger text-danger">
                            <i class="ti tabler-calendar me-1"></i> {{ $rmaThisMonth }} this month
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm bg-gradient-primary text-white" style="border-radius:14px; background: linear-gradient(135deg, #7367f0, #9e95f5);">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-1" style="font-size:0.7rem;letter-spacing:0.5px;text-transform:uppercase;">Inbound This Month</h6>
                        <h3 class="fw-bold mb-0 text-white">{{ number_format($inboundMonthCount) }}</h3>
                    </div>
                    <div class="text-end">
                        <div class="fs-1 opacity-50"><i class="ti tabler-download"></i></div>
                    </div>
                </div>
                <div class="mt-2 small text-white-50">
                    <i class="ti tabler-{{ $inboundChange >= 0 ? 'trending-up' : 'trending-down' }} me-1"></i>
                    {{ $inboundChange >= 0 ? '+' : '' }}{{ $inboundChange }}% vs last month
                    <a href="{{ route('receiving') }}" class="float-end text-white fw-bold small">
                        View <i class="ti tabler-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm" style="border-radius:14px;background: linear-gradient(135deg, #ff9f43, #feca57);">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1" style="font-size:0.7rem;letter-spacing:0.5px;text-transform:uppercase;color:rgba(0,0,0,0.5);">Outbound This Month</h6>
                        <h3 class="fw-bold mb-0">{{ number_format($outboundMonthCount) }}</h3>
                    </div>
                    <div class="text-end">
                        <div class="fs-1 opacity-50"><i class="ti tabler-upload"></i></div>
                    </div>
                </div>
                <div class="mt-2 small" style="color:rgba(0,0,0,0.5);">
                    <i class="ti tabler-{{ $outboundChange >= 0 ? 'trending-up' : 'trending-down' }} me-1"></i>
                    {{ $outboundChange >= 0 ? '+' : '' }}{{ $outboundChange }}% vs last month
                    <a href="{{ route('outbound.index') }}" class="float-end fw-bold small text-dark">
                        View <i class="ti tabler-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm" style="border-radius:14px;background: linear-gradient(135deg, #28c76f, #81e4a0);">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1" style="font-size:0.7rem;letter-spacing:0.5px;text-transform:uppercase;color:rgba(255,255,255,0.6);">Net Change</h6>
                        <h3 class="fw-bold mb-0 text-white">
                            {{ number_format($inboundMonthCount - $outboundMonthCount) >= 0 ? '+' : '' }}{{ number_format($inboundMonthCount - $outboundMonthCount) }}
                        </h3>
                    </div>
                    <div class="text-end">
                        <div class="fs-1 opacity-50 text-white"><i class="ti tabler-activity"></i></div>
                    </div>
                </div>
                <div class="mt-2 small text-white-50">
                    Inbound - Outbound this month
                    <a href="{{ route('inboundReturn') }}" class="float-end text-white fw-bold small">
                        Trend <i class="ti tabler-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm" style="border-radius:14px;background: linear-gradient(135deg, #ea5455, #f09090);">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1" style="font-size:0.7rem;letter-spacing:0.5px;text-transform:uppercase;color:rgba(255,255,255,0.6);">RMA This Month</h6>
                        <h3 class="fw-bold mb-0 text-white">{{ number_format($rmaThisMonth) }}</h3>
                    </div>
                    <div class="text-end">
                        <div class="fs-1 opacity-50 text-white"><i class="ti tabler-arrows-exchange"></i></div>
                    </div>
                </div>
                <div class="mt-2 small text-white-50">
                    Total all-time: {{ $rmaStats->count ?? 0 }}
                    <a href="{{ route('rmaMonitoring') }}" class="float-end text-white fw-bold small">
                        Detail <i class="ti tabler-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 1 -->
<div class="row g-3 mb-4">
    <!-- Stock by Status (Donut) -->
    <div class="col-xl-4">
        <div class="card chart-card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="ti tabler-chart-pie me-2 text-primary"></i>Stock by Status</h5>
                <span class="badge bg-label-primary rounded-pill">{{ $stockByStatus->sum('count') }} items</span>
            </div>
            <div class="card-body d-flex align-items-center">
                <div id="stockStatusChart" style="width:100%;height:300px;"></div>
            </div>
        </div>
    </div>

    <!-- Stock by Condition (Polar) -->
    <div class="col-xl-4">
        <div class="card chart-card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="ti tabler-shield-check me-2 text-success"></i>Stock by Condition</h5>
                <span class="badge bg-label-success rounded-pill">{{ $stockByCondition->sum('count') }} items</span>
            </div>
            <div class="card-body d-flex align-items-center">
                <div id="conditionChart" style="width:100%;height:300px;"></div>
            </div>
        </div>
    </div>

    <!-- Top 5 Stock -->
    <div class="col-xl-4">
        <div class="card chart-card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="ti tabler-bar-chart me-2 text-info"></i>Top Products</h5>
                <a href="{{ route('stockMonitoring') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body d-flex align-items-center">
                <div id="topStockChart" style="width:100%;height:300px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 2 -->
<div class="row g-3 mb-4">
    <!-- Trend Inbound vs Outbound -->
    <div class="col-xl-8">
        <div class="card chart-card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0"><i class="ti tabler-trending-up me-2 text-primary"></i>Inbound vs Outbound</h5>
                    <small class="text-muted">Last 6 months movement trend</small>
                </div>
                <a href="{{ route('inboundReturn') }}" class="btn btn-sm btn-outline-primary">Full Report</a>
            </div>
            <div class="card-body">
                <div id="trendChart" style="height:320px;"></div>
            </div>
        </div>
    </div>

    <!-- Client Utilization -->
    <div class="col-xl-4">
        <div class="card chart-card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="ti tabler-users me-2 text-warning"></i>Utilization</h5>
                <a href="{{ route('utilizationByClient') }}" class="btn btn-sm btn-outline-primary">Detail</a>
            </div>
            <div class="card-body d-flex align-items-center">
                <div id="clientUtilizationChart" style="width:100%;height:320px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity + RMA Table -->
<div class="row g-3 mb-4">
    <!-- Recent Activity -->
    <div class="col-xl-5">
        <div class="card chart-card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="ti tabler-bell-ringing me-2 text-primary"></i>Recent Activity</h5>
                <a href="{{ route('reporting.movement-history') }}" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="card-body py-3">
                @if ($recentActivity->count() > 0)
                    <div class="activity-timeline">
                        @foreach ($recentActivity as $act)
                            @php
                                $dotColor = match($act->type) {
                                    'Inbound' => 'bg-success',
                                    'Outbound' => 'bg-danger',
                                    'Movement' => 'bg-info',
                                    'Receiving' => 'bg-primary',
                                    default => 'bg-secondary'
                                };
                                $icon = match($act->type) {
                                    'Inbound', 'Receiving' => 'tabler-arrow-down-left',
                                    'Outbound' => 'tabler-arrow-up-right',
                                    'Movement' => 'tabler-arrows-exchange',
                                    default => 'tabler-circle'
                                };
                            @endphp
                            <div class="activity-item">
                                <div class="activity-dot {{ $dotColor }}">
                                    <i class="ti {{ $icon }} text-white" style="font-size:0.55rem;"></i>
                                </div>
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="min-w-0">
                                        <strong class="small">{{ $act->type }}</strong>
                                        <span class="text-muted small mx-1">·</span>
                                        <span class="small text-muted">{{ $act->category }}</span>
                                        @if ($act->serial_number)
                                            <div class="text-truncate" style="max-width:280px;">
                                                <span class="badge bg-label-secondary" style="font-size:0.65rem;">{{ $act->serial_number }}</span>
                                            </div>
                                        @endif
                                        <div class="small text-muted text-truncate" style="max-width:280px;">{{ $act->description }}</div>
                                    </div>
                                    <small class="text-muted text-nowrap ms-2" style="font-size:0.65rem;">
                                        {{ $act->created_at ? $act->created_at->diffForHumans() : '' }}
                                    </small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="ti tabler-history-off fs-1 mb-2 d-block"></i>
                        <small>No recent activity</small>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- RMA Monitoring -->
    <div class="col-xl-7">
        <div class="card chart-card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="ti tabler-arrows-exchange me-2 text-danger"></i>RMA Monitoring</h5>
                <a href="{{ route('rmaMonitoring') }}" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table dash-table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Old SN (Original)</th>
                                <th>New SN (Replacement)</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rmaHistory as $rma)
                                <tr>
                                    <td class="fw-semibold">{{ $rma->part_name }}</td>
                                    <td><span class="badge bg-label-secondary" style="font-size:0.7rem;">{{ $rma->old_serial_number }}</span></td>
                                    <td><span class="badge bg-label-primary" style="font-size:0.7rem;">{{ $rma->serial_number }}</span></td>
                                    <td><span class="small text-muted">{{ $rma->created_at->format('d/m/y H:i') }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="ti tabler-arrows-exchange me-2"></i>No RMA swaps yet
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-light py-2 px-3 border-0 text-end">
                <small class="text-muted">Total <strong>{{ $rmaStats->count ?? 0 }}</strong> swaps · <strong>{{ $rmaThisMonth }}</strong> this month</small>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
// ===== COLOR PALETTE =====
const C = {
    primary: '#7367f0', success: '#28c76f', warning: '#ff9f43',
    danger: '#ea5455', info: '#00cfe8', dark: '#4b4b4b',
    purple: '#775DD0', teal: '#1abc9c', pink: '#e83e8c'
};
const STATUS_COLORS = ['#7367f0','#28c76f','#ff9f43','#ea5455','#00cfe8','#4b4b4b','#1abc9c','#e83e8c'];
const CONDITION_COLORS = ['#28c76f','#00cfe8','#ff9f43','#ea5455','#4b4b4b'];

// ===== SAFE CHART RENDER HELPER =====
function renderChart(id, config) {
    try {
        var el = document.querySelector(id);
        if (el && typeof ApexCharts !== 'undefined') {
            new ApexCharts(el, config).render();
        } else if (!el) {
            console.warn('Chart container ' + id + ' not found');
        }
    } catch(e) {
        console.error('Chart error ' + id + ':', e);
    }
}

// ===== CHART 1: STOCK BY STATUS (DONUT) =====
renderChart('#stockStatusChart', {
    series: @json($stockByStatus->pluck('count')),
    chart: { type: 'donut', height: 300 },
    labels: @json($stockByStatus->pluck('status')),
    colors: STATUS_COLORS.slice(0, @json($stockByStatus->count())),
    legend: { position: 'bottom', fontSize: '12px' },
    dataLabels: { enabled: true, formatter: function(v) { return Math.round(v) + '%'; } },
    plotOptions: {
        pie: { donut: { size: '55%', labels: { show: true, total: { show: true, label: 'Total',
            formatter: function(w) { return w.globals.seriesTotals.reduce(function(a,b){return a+b},0); }
        }}}}
    },
    tooltip: { y: { formatter: function(v) { return v + ' items'; } } }
});

// ===== CHART 2: STOCK BY CONDITION (POLAR) =====
(function() {
    var d = @json($stockByCondition->pluck('count'));
    var l = @json($stockByCondition->pluck('condition'));
    if (d.length > 0) {
        renderChart('#conditionChart', {
            series: d, chart: { type: 'polarArea', height: 300 },
            labels: l, colors: CONDITION_COLORS.slice(0, l.length),
            stroke: { colors: ['#fff'] }, fill: { opacity: 0.85 },
            legend: { position: 'bottom', fontSize: '12px' },
            dataLabels: { enabled: false },
            tooltip: { y: { formatter: function(v) { return v + ' items'; } } }
        });
    } else {
        document.getElementById('conditionChart').innerHTML = '<div class="text-center py-5 text-muted"><i class="ti tabler-alert-circle"></i> No data</div>';
    }
})();

// ===== CHART 3: TOP PRODUCTS (BAR) =====
renderChart('#topStockChart', {
    series: [{ name: 'Qty', data: @json($topStock->pluck('total_qty')) }],
    chart: { type: 'bar', height: 300, toolbar: { show: false } },
    colors: [C.info],
    plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } },
    dataLabels: { enabled: true, style: { fontSize: '10px' } },
    xaxis: { categories: @json($topStock->pluck('part_name')), labels: { style: { fontSize: '10px' }, trim: true } },
    legend: { show: false },
    grid: { borderColor: '#f1f1f1' }
});

// ===== CHART 4: INBOUND VS OUTBOUND (AREA) =====
renderChart('#trendChart', {
    series: [
        { name: 'Inbound', data: @json($trendData->pluck('inbound')) },
        { name: 'Outbound', data: @json($trendData->pluck('outbound')) }
    ],
    chart: { type: 'area', height: 320, toolbar: { show: true }, zoom: { enabled: true } },
    colors: [C.success, C.warning],
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 3 },
    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.6, opacityTo: 0.1 } },
    xaxis: { categories: @json($trendData->pluck('month')), labels: { style: { fontSize: '11px' } } },
    yaxis: { title: { text: 'Items' } },
    legend: { position: 'top', horizontalAlign: 'right' },
    markers: { size: 4, hover: { size: 6 } },
    tooltip: { shared: true, intersect: false, y: { formatter: function(v) { return v + ' items'; } } },
    grid: { borderColor: '#f1f1f1' }
});

// ===== CHART 5: CLIENT UTILIZATION (HORIZONTAL BAR) =====
renderChart('#clientUtilizationChart', {
    series: [{ data: @json($utilizationByClient->pluck('count')) }],
    chart: { type: 'bar', height: 320, toolbar: { show: false } },
    colors: [C.purple],
    plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '65%' } },
    dataLabels: { enabled: true, style: { fontSize: '11px' }, formatter: function(v) { return v + ' orders'; } },
    xaxis: { categories: @json($utilizationByClient->pluck('client_name')), labels: { style: { fontSize: '10px' } } },
    legend: { show: false },
    grid: { borderColor: '#f1f1f1' }
});

// ===== MODAL FUNCTIONS =====
var modalState = { type: '', title: '', page: 1, items: [], total: 0, lastPage: 1 };

function openModal(type, title) {
    modalState = { type: type, title: title, page: 1, items: [], total: 0, lastPage: 1 };
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalSubtitle').textContent = 'Loading...';
    var m = document.getElementById('detailModal');
    m.classList.add('show');
    m.style.display = 'block';
    m.setAttribute('aria-modal', 'true');
    m.removeAttribute('aria-hidden');
    loadModalData();
}

function closeModal() {
    var m = document.getElementById('detailModal');
    m.classList.remove('show');
    m.style.display = 'none';
    m.setAttribute('aria-hidden', 'true');
}

document.getElementById('detailModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

function loadModalData() {
    var tbody = document.getElementById('modalTableBody');
    tbody.innerHTML = '<tr><td colspan="10" class="text-center py-5"><div class="spinner-border text-primary mb-2"></div><div class="text-muted small">Memuat data...</div></td></tr>';

    var params = new URLSearchParams(window.location.search);
    var clientId = params.get('client_id') || '';
    var url = '{{ route("dashboard.data") }}?type=' + modalState.type + '&page=' + modalState.page + (clientId ? '&client_id=' + clientId : '');

    fetch(url).then(function(r) { return r.json(); }).then(function(data) {
        modalState.items = data.items;
        modalState.total = data.total;
        modalState.lastPage = data.lastPage;
        document.getElementById('modalSubtitle').textContent = data.total + ' items ditemukan';
        document.getElementById('modalFooterInfo').textContent = data.total + ' items (hal. ' + data.page + '/' + data.lastPage + ')';
        renderModalTable();
        renderModalPagination(data.page, data.lastPage);
    }).catch(function() {
        tbody.innerHTML = '<tr><td colspan="10" class="text-center py-5 text-danger">Error loading data</td></tr>';
    });
}

function renderModalTable() {
    var thead = document.getElementById('modalTableHead');
    var tbody = document.getElementById('modalTableBody');
    var items = modalState.items;

    if (modalState.type === 'rma') {
        thead.innerHTML = '<tr><th>#</th><th>Client</th><th>Product</th><th>Part Number</th><th>Old SN</th><th>New SN</th><th>Condition</th><th>Date</th></tr>';
        if (items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">No data</td></tr>';
            return;
        }
        var html = '';
        for (var i = 0; i < items.length; i++) {
            var it = items[i];
            var num = (modalState.page - 1) * 10 + i + 1;
            html += '<tr><td>' + num + '</td><td>' + esc(it.client_name) + '</td><td class="fw-semibold">' + esc(it.part_name) + '</td>'
                + '<td class="text-muted small">' + esc(it.part_number || '-') + '</td>'
                + '<td><span class="badge bg-label-secondary font-monospace" style="font-size:0.7rem;">' + esc(it.old_serial_number) + '</span></td>'
                + '<td><span class="badge bg-label-primary font-monospace" style="font-size:0.7rem;">' + esc(it.serial_number) + '</span></td>'
                + '<td>' + esc(it.condition || '-') + '</td><td class="small text-muted">' + (it.date || '-') + '</td></tr>';
        }
        tbody.innerHTML = html;
    } else {
        thead.innerHTML = '<tr><th>#</th><th>WH Asset#</th><th>SN</th><th>Part Name</th><th>Brand</th><th>Group</th><th>Condition</th><th>Status</th><th>Client</th><th>Location</th></tr>';
        if (items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-muted">No data</td></tr>';
            return;
        }
        var html2 = '';
        for (var j = 0; j < items.length; j++) {
            var it2 = items[j];
            var num2 = (modalState.page - 1) * 10 + j + 1;
            var statusBadge = 'bg-label-secondary';
            if (it2.status === 'available') statusBadge = 'bg-label-success';
            else if (it2.status.indexOf('Out') >= 0) statusBadge = 'bg-label-warning';
            html2 += '<tr><td>' + num2 + '</td>'
                + '<td class="fw-bold small font-monospace">' + esc(it2.unique_id) + '</td>'
                + '<td class="font-monospace small">' + esc(it2.serial_number) + '</td>'
                + '<td class="fw-semibold">' + esc(it2.part_name) + '</td>'
                + '<td>' + esc(it2.brand) + '</td><td>' + esc(it2.group) + '</td>'
                + '<td>' + esc(it2.condition) + '</td>'
                + '<td><span class="badge ' + statusBadge + '" style="font-size:0.65rem;">' + esc(it2.status) + '</span></td>'
                + '<td class="small">' + esc(it2.client_name) + '</td>'
                + '<td class="small text-muted">' + esc(it2.location) + '</td></tr>';
        }
        tbody.innerHTML = html2;
    }
}

function renderModalPagination(page, lastPage) {
    var ul = document.getElementById('modalPagination');
    if (lastPage <= 1) { ul.innerHTML = ''; return; }
    var h = '';
    h += '<li class="page-item ' + (page <= 1 ? 'disabled' : '') + '"><a class="page-link" href="javascript:void(0)" onclick="goModalPage(' + (page - 1) + ')">«</a></li>';
    var start = Math.max(1, page - 2);
    var end = Math.min(lastPage, start + 4);
    if (end - start < 4) start = Math.max(1, end - 4);
    for (var i2 = start; i2 <= end; i2++) {
        h += '<li class="page-item ' + (i2 === page ? 'active' : '') + '"><a class="page-link" href="javascript:void(0)" onclick="goModalPage(' + i2 + ')">' + i2 + '</a></li>';
    }
    h += '<li class="page-item ' + (page >= lastPage ? 'disabled' : '') + '"><a class="page-link" href="javascript:void(0)" onclick="goModalPage(' + (page + 1) + ')">»</a></li>';
    ul.innerHTML = h;
}

function goModalPage(page) {
    if (page < 1 || page > modalState.lastPage) return;
    modalState.page = page;
    loadModalData();
}

function esc(str) {
    if (!str) return '-';
    var d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

function downloadModalData() {
    if (modalState.items.length === 0) return;
    var params = new URLSearchParams(window.location.search);
    var clientId = params.get('client_id') || '';
    fetch('{{ route("dashboard.data") }}?type=' + modalState.type + '&page=1&client_id=' + clientId)
    .then(function(r) { return r.json(); }).then(function(data) {
        var csv = '';
        if (modalState.type === 'rma') {
            csv = 'Client,Product,Part Number,Old SN,New SN,Condition,Date\n';
            for (var i = 0; i < data.items.length; i++) {
                var d = data.items[i];
                csv += (d.client_name||'-') + ',' + (d.part_name||'-') + ',' + (d.part_number||'-') + ','
                    + (d.old_serial_number||'-') + ',' + (d.serial_number||'-') + ',' + (d.condition||'-') + ',' + (d.date||'-') + '\n';
            }
        } else {
            csv = 'WH Asset#,SN,Part Name,Part Number,Brand,Group,Condition,Status,Client,Location\n';
            for (var j = 0; j < data.items.length; j++) {
                var d = data.items[j];
                csv += (d.unique_id||'-') + ',' + (d.serial_number||'-') + ',' + (d.part_name||'-') + ','
                    + (d.part_number||'-') + ',' + (d.brand||'-') + ',' + (d.group||'-') + ','
                    + (d.condition||'-') + ',' + (d.status||'-') + ',' + (d.client_name||'-') + ',' + (d.location||'-') + '\n';
            }
        }
        var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        var link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = modalState.type + '-' + new Date().toISOString().slice(0,10) + '.csv';
        link.click();
    });
}
</script>
@endsection@endsection