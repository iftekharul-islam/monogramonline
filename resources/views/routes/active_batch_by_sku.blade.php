<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Active batch by SKU group</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet"
	      href = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
	<link type = "text/css" rel = "stylesheet"
	      href = "//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
	<link type = "text/css" rel = "stylesheet"
	      href = "https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css">
	<link type = "text/css" rel = "stylesheet"
	      href = "//cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker.min.css">
    <link rel="stylesheet" href="/assets/css/chosen.min.css">
</head>
<body>
	@include('includes.header_menu')
	<div class = "container" style="min-width: 1550px; margin-left: 10px;">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li class = "active">Active batch by SKU group</li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		
		<div class = "col-xs-12">
			{!! Form::open(['method' => 'get']) !!}
			<div class = "form-group col-xs-3">
				<label for = "route">Current Station</label>
				{!! Form::select('station', $stations, $request->get('station', ''), ['id'=>'station', 'class' => 'form-control']) !!}
			</div>
			
			<div class = "form-group col-xs-3">
				<label for = "routes_in_station">Station Routes</label>
				{!! Form::select('routes_in_station', $routes_in_station, $request->get('routes_in_station', 'all'), ['id'=>'routes_in_station', 'class' => 'form-control']) !!}
			</div>
			
				<div class = "form-group col-xs-1">
				<label for = "" class = ""></label>
				{!! Form::submit('Search', ['id'=>'search', 'style' => 'margin-top: 2px;', 'class' => 'btn btn-primary form-control']) !!}
			</div>			
				{!! Form::hidden('start_date', $request->get('start_date', '2016-06-01')) !!}
				{!! Form::hidden('end_date', $request->get('end_date', '2020-12-31')) !!}
				{!! Form::close() !!}
		</div>
		<div class = "col-xs-12">
			@if(count($rows))
			{!! Form::open(['method' => 'post', 'url' => url('items/active_batch_group'), 'id' => 'active_batch_group']) !!}
			
				<div class = "form-group col-xs-3">
					<label for = "to_station">To Station</label>
					@if($request->get('routes_in_station') != null)
						{!! Form::hidden('station_route', $request->get('routes_in_station', '')) !!}
					@else
						@foreach($routes_in_station as $key => $value)
							{!! Form::hidden('station_route', $key) !!}
							@break;
						@endforeach
					@endif
					
					{!! Form::hidden('from_station', $request->get('station', '')) !!}
					{!! Form::select('to_station', $to_station, $request->get('to_station', ''), ['id'=>'to_station', 'class' => 'form-control chosen']) !!}
				</div>
				
				<div class = "form-group col-xs-4">
					<label for = "update_all_sku">Make Sure you selece correct Routes and To Station</label>
					{!! Form::submit('Update All', ['id' => 'update_all_sku', 'class' => 'btn btn-success']) !!}
				</div>
					
				<table class = "table" id="active_batch_table" name="active_batch_table" >
					<thead>
					<tr>
						<th>Image</th>
						<th>SKU</th>
						<th>Name</th>
						<th>Quantity</th>
						<th>Min-Order date</th>
						<th>Route</th>
					</tr>
					</thead>
					 <tbody>
					@foreach($rows as $row)
						<tr data-sku = "{{ $row['sku'] }}" id = "{{ $row['current_station_anchor'] }}">
							<td>
								<img src = "{{ $row['item_thumb'] }}"  height="42" width="42" />
							</td>
							<td>
								{{ $row['sku'] }}
								{!! Form::hidden('sku[]', $row['sku']) !!}
							</td>
							<td class = "description">
								{{ $row['item_name'] }}
							</td>
							<td>
								{{ $row['item_count'] }}
								{!! Form::hidden('item_count[]', $row['item_count']) !!}
							</td>
							<td>
								{{ $row['min_order_date'] }}
							</td>
							<td style = "min-width: 250px;">
								{{ $row['route'] }}
							</td>
						</tr>
					@endforeach	
					</tbody>
					<tfoot>					
					<tr>
						<td></td>
						<td></td>
						<td></td>
						<td>{{ $total_count }}</td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
					</tr>
					</tfoot>
				</table>
				<div class="col-md-12 text-center">
					{!! $pagination !!}
				</div>
				{!! Form::close() !!}
			@else
				<div class = "alert alert-warning">No data is available.</div>
			@endif
		</div>
		<div class = "col-xs-12 text-center">
			{{--{!! $items->render() !!}--}}
		</div>
	</div>
	<script type = "text/javascript" src = "//code.jquery.com/jquery-1.12.3.js"></script>
	<script type = "text/javascript" src = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "//cdnjs.cloudflare.com/ajax/libs/moment.js/2.11.2/moment.min.js"></script>
	<script type = "text/javascript" src = "//cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js"></script>
	<script type = "text/javascript" src = "https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
	<script type = "text/javascript" src = "https://cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/chosen.jquery.min.js"></script>
		
	<script type = "text/javascript">

		$('#active_batch_table').DataTable({
			"paging":   false,
		});

		$(".chosen").chosen();
	</script>
</body>
</html>
