@extends('layout.index')
@section('title', 'Inbound vs Outbound Trend')

@section('content')
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">Spare Part Movement Trend (Last 12 Months)</h5>
                </div>
                <div class="card-body">
                    <div id="movementTrendDetailedChart" style="height: 450px;"></div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">Monthly Movement Data</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>Month</th>
                                    <th>Inbound (Items)</th>
                                    <th>Outbound (Items)</th>
                                    <th>Net Change</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($trendData as $trend)
                                    <tr>
                                        <td>{{ $trend['month'] }}</td>
                                        <td><span class="text-success">+{{ number_format($trend['inbound']) }}</span></td>
                                        <td><span class="text-warning">-{{ number_format($trend['outbound']) }}</span></td>
                                        <td>
                                            @php $net = $trend['inbound'] - $trend['outbound']; @endphp
                                            <span class="{{ $net >= 0 ? 'text-primary' : 'text-danger' }}">
                                                {{ $net >= 0 ? '+' : '' }}{{ number_format($net) }}
                                            </span>
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
    <script>
        const trendMonths = {!! json_encode($trendData->pluck('month')) !!};
        const trendInbound = {!! json_encode($trendData->pluck('inbound')) !!};
        const trendOutbound = {!! json_encode($trendData->pluck('outbound')) !!};

        new ApexCharts(document.querySelector("#movementTrendDetailedChart"), {
            series: [{
                name: 'Inbound',
                data: trendInbound
            }, {
                name: 'Outbound',
                data: trendOutbound
            }],
            chart: {
                type: 'area',
                height: 450,
                zoom: {
                    enabled: true
                },
                toolbar: {
                    show: true
                }
            },
            colors: ['#00E396', '#FEB019'],
            dataLabels: {
                enabled: true
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.6,
                    opacityTo: 0.1,
                }
            },
            xaxis: {
                categories: trendMonths,
                title: {
                    text: 'Period'
                }
            },
            yaxis: {
                title: {
                    text: 'Total Items'
                }
            },
            legend: {
                position: 'top'
            },
            tooltip: {
                shared: true,
                intersect: false
            }
        }).render();
    </script>
@endsection
