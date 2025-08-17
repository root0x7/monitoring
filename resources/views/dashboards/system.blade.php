@extends('layouts.main')



@section('content')


<div style="width: 350px;">
    {!! $cpubusy->container() !!}
</div>

@endsection



@section('script')


<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
{!! $cpubusy->script() !!}

@endsection