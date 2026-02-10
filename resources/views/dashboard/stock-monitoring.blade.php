@extends('layout.index')
@section('title', 'Stock Monitoring')

@section('content')
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Stock Quantity Availability by Product</h5>
                    <span class="badge bg-info">{{ count($data) }} Products Monitored</span>
                </div>
                <div class="card-body">
                    <div id="stockAvailabilityDetailChart" style="height: 400px;"></div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">Detailed Stock Inventory</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle datatable">
                            <thead class="bg-light">
                                <tr>
                                    <th>#</th>
                                    <th>Part Name</th>
                                    <th>Part Number</th>
                                    <th>Description</th>
                                    <th>Total Qty</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="fw-bold">{{ $item->part_name }}</td>
                                        <td>{{ $item->part_number }}</td>
                                        <td>{{ $item->part_description }}</td>
                                        <td>
                                            <h5 class="mb-0 {{ $item->total_qty < 5 ? 'text-danger' : 'text-primary' }}">
                                                {{ number_format($item->total_qty) }}
                                            </h5>
                                        </td>
                                        <td>
                                            @if ($item->total_qty == 0)
                                                <span class="badge bg-danger">Out of Stock</span>
                                            @elseif($item->total_qty < 10)
                                                <span class="badge bg-warning">Low Stock</span>
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
@endsection

@section('js')
    <script>
        const top5Data = {!! json_encode($data->take(10)) !!};
        const stockLabels = top5Data.map(i => i.part_name);
        const stockQtys = top5Data.map(i => i.total_qty);

        new ApexCharts(document.querySelector("#stockAvailabilityDetailChart"), {
            series: [{
                name: 'Total Quantity',
                data: stockQtys
            }],
            chart: {
                type: 'bar',
                height: 400
            },
            colors: ['#008FFB'],
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    columnWidth: '40%',
                    distributed: true
                }
            },
            dataLabels: {
                enabled: true
            },
            xaxis: {
                categories: stockLabels,
                labels: {
                    rotate: -45,
                    offsetY: 0
                }
            },
            title: {
                text: 'Top 10 Products by Stock Level',
                align: 'center'
            },
            legend: {
                show: false
            }
        }).render();
    </script>
@endsection
