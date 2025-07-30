@extends('layouts.main')


@section('content')


<div class="card">
	<h5 class="card-header">Oynalar</h5>
	<div class="table-responsive text-nowrap">
		<table class="table">
			<thead>
				<tr>
					<th>Nomi</th>
					<th>Informatsiya</th>
					<th>Status</th>
				</tr>
			</thead>
			<tbody class="table-border-bottom-0">
				<tr>
					<td><i class="fab fa-angular fa-lg text-danger me-3"></i> 
					<a href="/dashboards/system"><strong>Server</strong></a>
					</td>
					<td>Linux hostni monitoring qismi</td>
					<td><span class="badge bg-label-primary me-1">Active</span></td>
				</tr>
				<tr>
					<td><i class="fab fa-angular fa-lg text-danger me-3"></i> 
					<a href="/dashboards/network"><strong>Tarmoq</strong></a>
					</td>
					<td>Linux hostni monitoring qismi</td>
					<td><span class="badge bg-label-primary me-1">Active</span></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>


@endsection