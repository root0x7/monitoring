@extends('layouts.main')



@section('style')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
@endsection

@section('content')


<div class="row">
    <div class="col-3">
        <label for="cpu_busy" class="text-center">RAM</label>
        <div id="cpu_busy"></div>
    </div>
</div>

<br>

<div class="row">
     <div class="col-12">
        <label for="cpu_busy" class="text-center">CPU load</label>
        <div id="cpuLoadChart"></div>
    </div>
</div>

@endsection

@section('script')

<script>
    axios.get('/api/metrics')
    .then(function (response) {
        let metrics = response.data;

        // RAM donut chart
        var options = {
            series: [metrics.ram_used.used_gb, metrics.ram_used.free_gb],
            chart: {
                type: 'donut',
            },
            labels: ['Band RAM (GB)', 'Bo‘sh RAM (GB)'],
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: { width: 200 },
                    legend: { position: 'bottom' }
                }
            }]
        };

        var chart = new ApexCharts(document.querySelector("#cpu_busy"), options);
        chart.render();


        // CPU Load line chart
        var load_options = {
            series: [{
                name: "Load Average",
                data: [
                    metrics.sysload.load_1min,
                    metrics.sysload.load_5min,
                    metrics.sysload.load_15min
                ]
            }],
            chart: { type: 'line', height: 300 },
            xaxis: { categories: ['1 min', '5 min', '15 min'] }
        };

        var load = new ApexCharts(document.querySelector("#cpuLoadChart"), load_options);
        load.render();
    })
    .catch(function (error) {
        console.error("API error:", error);
    });


</script>

@endsection