@extends('layout.index')
@section('title', 'Dashboard Overview')

@section('content')
    <div class="row">
        <!-- Summary Cards -->
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <h6 class="text-white-50">Total RMA Processed</h6>
                    <h2 class="mb-0">{{ $rmaStats->count ?? 0 }}</h2>
                    <small>Items swapped with replacement SN</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <h6 class="text-white-50">Monthly Inbound</h6>
                    <h2 class="mb-0">{{ optional(collect($trendData)->last())['inbound'] ?? 0 }}</h2>
                    <small>Current month items received</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body">
                    <h6 class="text-dark-50">Monthly Outbound</h6>
                    <h2 class="mb-0">{{ optional(collect($trendData)->last())['outbound'] ?? 0 }}</h2>
                    <small>Current month items shipped</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm bg-info text-white h-100">
                <div class="card-body">
                    <h6 class="text-white-50">Total Stock Items</h6>
                    <h2 class="mb-0">{{ number_format($totalStockCount) }}</h2>
                    <small>Total inventory available</small>
                </div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="col-xl-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Stock Overview by Status</h5>
                </div>
                <div class="card-body">
                    <div id="stockStatusChart" style="height: 350px;"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Inbound vs Outbound Trend</h5>
                    <a href="{{ route('inboundReturn') }}" class="btn btn-sm btn-outline-primary">View Detail</a>
                </div>
                <div class="card-body">
                    <div id="trendChart" style="height: 350px;"></div>
                </div>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="col-xl-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Utilization by Client</h5>
                    <a href="{{ route('utilizationByClient') }}" class="btn btn-sm btn-outline-primary">View Detail</a>
                </div>
                <div class="card-body">
                    <div id="clientUtilizationChart" style="height: 350px;"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Stock Monitoring (Top 5)</h5>
                    <a href="{{ route('stockMonitoring') }}" class="btn btn-sm btn-outline-primary">View Detail</a>
                </div>
                <div class="card-body">
                    <div id="topStockChart" style="height: 350px;"></div>
                </div>
            </div>
        </div>
        <!-- RMA Monitoring Table -->
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">RMA Monitoring (Original vs Replacement SN)</h5>
                    <a href="{{ route('rmaMonitoring') }}" class="btn btn-primary btn-sm">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>Product Name</th>
                                    <th>Original Serial Number (Old)</th>
                                    <th>Replacement Serial Number (New)</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rmaHistory as $rma)
                                    <tr>
                                        <td>{{ $rma->part_name }}</td>
                                        <td><span class="badge bg-secondary">{{ $rma->old_serial_number }}</span></td>
                                        <td><span class="badge bg-primary">{{ $rma->serial_number }}</span></td>
                                        <td><span class="badge bg-success">Swapped</span></td>
                                        <td>{{ $rma->created_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @endforeach
                                @if ($rmaHistory->isEmpty())
                                    <tr>
                                        <td colspan="5" class="text-center">No RMA history found</td>
                                    </tr>
                                @endif
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
        // 1. Stock Status (Donut)
        const stockStatusLabels = {!! json_encode($stockByStatus->pluck('status')) !!};
        const stockStatusData = {!! json_encode($stockByStatus->pluck('count')) !!};

        new ApexCharts(document.querySelector("#stockStatusChart"), {
            series: stockStatusData,
            chart: {
                type: 'donut',
                height: 350
            },
            labels: stockStatusLabels,
            colors: ['#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0'],
            legend: {
                position: 'bottom'
            },
            dataLabels: {
                enabled: true
            }
        }).render();

        // 2. Trend (Area)
        const trendMonths = {!! json_encode($trendData->pluck('month')) !!};
        const trendInbound = {!! json_encode($trendData->pluck('inbound')) !!};
        const trendOutbound = {!! json_encode($trendData->pluck('outbound')) !!};

        new ApexCharts(document.querySelector("#trendChart"), {
            series: [{
                name: 'Inbound',
                data: trendInbound
            }, {
                name: 'Outbound',
                data: trendOutbound
            }],
            chart: {
                type: 'area',
                height: 350,
                toolbar: {
                    show: false
                }
            },
            dataLabels: {
                enabled: false
            },
            colors: ['#00E396', '#FEB019'],
            stroke: {
                curve: 'smooth'
            },
            xaxis: {
                categories: trendMonths
            },
            yaxis: {
                title: {
                    text: 'Quantity'
                }
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.7,
                    opacityTo: 0.3
                }
            }
        }).render();

        // 3. Client Utilization (Horizontal Bar)
        const clientLabels = {!! json_encode($utilizationByClient->pluck('client_name')) !!};
        const clientData = {!! json_encode($utilizationByClient->pluck('count')) !!};

        new ApexCharts(document.querySelector("#clientUtilizationChart"), {
            series: [{
                data: clientData
            }],
            chart: {
                type: 'bar',
                height: 350
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 4
                }
            },
            colors: ['#775DD0'],
            xaxis: {
                categories: clientLabels
            },
            title: {
                text: 'Orders per Client',
                align: 'left'
            }
        }).render();

        // 4. Top Stock (Vertical Bar)
        const stockLabels = {!! json_encode($topStock->pluck('part_name')) !!};
        const stockQty = {!! json_encode($topStock->pluck('total_qty')) !!};

        new ApexCharts(document.querySelector("#topStockChart"), {
            series: [{
                name: 'Quantity',
                data: stockQty
            }],
            chart: {
                type: 'bar',
                height: 350
            },
            colors: ['#008FFB'],
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    columnWidth: '45%'
                }
            },
            xaxis: {
                categories: stockLabels
            },
            yaxis: {
                title: {
                    text: 'Total Qty'
                }
            }
        }).render();
    </script>

    <style>
        .card {
            transition: transform .2s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .text-white-50 {
            color: rgba(255, 255, 255, 0.7) !important;
        }

        .text-dark-50 {
            color: rgba(0, 0, 0, 0.5) !important;
        }
    </style>
@endsection
