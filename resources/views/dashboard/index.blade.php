@extends('layout.index')
@section('title', 'Dashboard')

@section('content')
    <div class="row">
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Stock Overview</h5>
                </div>
                <div class="card-body d-flex justify-content-center">
                    <div id="stockOverviewPieChart" style="width: 100%; max-width: 520px;"></div>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Stock Overview</h5>
                </div>
                <div class="card-body">
                    <div id="chart" style="height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        let pieOptions = {
            series: [44, 55, 13, 43],
            chart: {
                type: 'pie',
                width: '100%',
                height: '365'
            },
            labels: ['Available', 'Loan', 'RMA', 'Defective'],
            legend: {
                position: 'bottom',
                horizontalAlign: 'center'
            }
        };

        let pieChart = new ApexCharts(
            document.querySelector("#stockOverviewPieChart"),
            pieOptions
        );
        pieChart.render();
    </script>

    <script>
        let barOptions = {
            series: [{
                data: [44, 55, 13, 43]
            }],
            chart: {
                type: 'bar',
                height: 350
            },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    borderRadiusApplication: 'end',
                    horizontal: true
                }
            },
            dataLabels: {
                enabled: false
            },
            xaxis: {
                categories: ['Available', 'Loan', 'RMA', 'Defective']
            }
        };

        let barChart = new ApexCharts(
            document.querySelector("#chart"),
            barOptions
        );
        barChart.render();
    </script>
@endsection


