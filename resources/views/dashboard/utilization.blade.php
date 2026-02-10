@extends('layout.index')
@section('title', 'Utilization by Client')

@section('content')
    <div class="row">
        <div class="col-xl-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">Distribution of Spare Part Usage by Client</h5>
                </div>
                <div class="card-body">
                    <div id="clientUtilizationDetailChart" style="height: 400px;"></div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">Client Usage Data</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Client Name</th>
                                    <th>Total Outbound Orders</th>
                                    <th>Total Items Shipped</th>
                                    <th>Utilization Share (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalItems = $data->sum('total_items'); @endphp
                                @foreach ($data as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->client->name ?? 'Unknown' }}</td>
                                        <td>{{ number_format($item->total_orders) }}</td>
                                        <td>{{ number_format($item->total_items) }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span
                                                    class="me-2">{{ $totalItems > 0 ? round(($item->total_items / $totalItems) * 100, 1) : 0 }}%</span>
                                                <div class="progress w-100" style="height: 6px;">
                                                    <div class="progress-bar bg-primary" role="progressbar"
                                                        style="width: {{ $totalItems > 0 ? ($item->total_items / $totalItems) * 100 : 0 }}%">
                                                    </div>
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
    <script>
        const clientNames = {!! json_encode($data->map(fn($i) => $i->client->name ?? 'Unknown')) !!};
        const orderCounts = {!! json_encode($data->pluck('total_orders')) !!};
        const itemCounts = {!! json_encode($data->pluck('total_items')) !!};

        new ApexCharts(document.querySelector("#clientUtilizationDetailChart"), {
            series: [{
                name: 'Total Orders',
                data: orderCounts
            }, {
                name: 'Total Items Shipped',
                data: itemCounts
            }],
            chart: {
                type: 'bar',
                height: 400,
                toolbar: {
                    show: true
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    borderRadius: 4,
                },
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: clientNames
            },
            yaxis: {
                title: {
                    text: 'Count'
                }
            },
            fill: {
                opacity: 1
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val + " units"
                    }
                }
            },
            colors: ['#775DD0', '#00E396']
        }).render();
    </script>
@endsection
