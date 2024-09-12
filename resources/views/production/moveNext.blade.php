<!doctype html> 
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Move Batches</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	<link rel="stylesheet" href="/assets/css/chosen.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/chosen.jquery.min.js"></script>	

	<style>
		table {
			table-layout: fixed;
			font-size: 12px;
		}

		td {
			width: auto;
		}
		
	</style>
</head>
<body>
	@include('includes.header_menu')
	<div class = "container">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li class = "active">Move Batches</li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')

		<div class = "col-md-12">
			<div class = "panel panel-default">
				<div class = "panel-heading">Search</div>
				<div class = "panel-body">
					{!! Form::open(['method' => 'post', 'id' => 'barcode_form']) !!}
										
				<div class = "col-xs-12">

					<div class="form-group">
						<div class = "form-group col-xs-4">
							<label>Find Batches</label>
							{!! Form::text('scan_batches', $scan_batches, ['id'=>'barcode', 'class' => 'form-control', 'placeholder' => 'Enter Batches']) !!}
						</div>
						
						<div class = "form-group col-xs-5">
							<label>View Batches in Station</label>
							{!! Form::select('station', $stations_list, (!empty($station)) ? $station->id : '', ['id'=>'station', 'class' => 'form-control chosen_txt']) !!}
						</div>
										
						<div class = "form-group col-xs-2">
							{!! Form::button('Search', ['id'=>'search_button', 'style' => 'margin-top: 20px;', 'class' => 'btn btn-primary btn-sm form-control']) !!}
						</div>
					</div>
				</div>
				
			</div>
		</div>
		
		<div class = "col-xs-12">
			
			@if (isset($station))
				<h4 class = "page-header">
					{{ $station->station_name }} - {{ $station->station_description }}
						
						@if ($route && $route != 'all')
							( Route:  {{ $routes_in_station[$route] }} )
						@endif
						
						- {{ count($batches->where('status', 'active')->all()) }}
						 @if (count($batches->where('status', 'active')->all()) != 1)
							 Active Batches Found 
						 @else
							 Active Batch Found
						 @endif
				</h4>
			@endif
			
			@if(isset($batches) && count($batches) > 0)
									
					
					@if (auth()->user()->accesses->where('page', 'supervisor')->all())
							<div class = "col-xs-12" style="background-color:#E6F3F6;padding: 30px 30px 30px 30px;">
								<div class = "form-group col-xs-12"> 
									<div class = "col-xs-12" style="border-style: solid;border-color:#cde8ed;padding: 20px 5px 10px 5px;">
										<div class="form-group col-xs-1">
										</div>
										
										@if((($route && $route != 'all') || count($batches) == 1)  && isset($stations_in_route))
											{!! Form::close() !!}
											{!! Form::open(['url' => '/supervisor/move_batch', 'method' => 'post', 'id' => 'batch_move_form']) !!}
											{!! Form::hidden('station', !empty($station) ? $station->id : '', ['id' => 'station']) !!}
											<div class="form-group col-xs-8">
												{!! Form::select('station_change', $stations_in_route, 'all', ['id'=>'station_change', 'class' => 'form-control chosen_txt']) !!}
											</div>
											<div class = "form-group col-xs-2">
												{!! Form::button('Move to Station', ['id'=>'move', 'class' => 'btn btn-primary btn-sm form-control']) !!}
											</div>
										@else
											<div class = "form-group col-xs-8">
												<label for = "routes_in_station">Select Route</label> (To move to other stations) 
												{!! Form::select('route', $routes_in_station, (!empty($route)) ? $route : '', ['id'=>'route', 'class' => 'form-control chosen_txt']) !!} 
											</div>
											<div class = "form-group col-xs-2">
												{!! Form::button('Filter', ['id'=>'filter', 'class' => 'btn btn-primary btn-sm form-control', 'style' => 'margin-top: 15px;']) !!}
											</div>
											{!! Form::close() !!}
											{!! Form::open(['method' => 'post', 'id' => 'batch_move_form']) !!}
											{!! Form::hidden('station', !empty($station) ? $station->id : '', ['id' => 'station']) !!}
										@endif
									</div>
								</div>  
							</div>
					@else
						{!! Form::close() !!}
						{!! Form::open(['method' => 'post', 'id' => 'batch_move_form']) !!}
						{!! Form::hidden('station', !empty($station) ? $station->id : '', ['id' => 'station']) !!}
					@endif
							
							<input type="hidden" name="task" id="task" value="">
							{!! Form::hidden('scan_batches', $scan_batches) !!}
							{!! Form::button('Move to Next Station', ['id'=>'next', 'class' => 'btn btn-primary', 'style' => 'margin-top: 20px;']) !!}

			
				<table class="table">
				<thead>
					<tr>
						<th style="width:30px;">
							<input type="checkbox" name="select_all" id="select_all" class="checkbox">	
						</th>
						<th>Select All</th>
						<th>Batch Date</th>
						<th>Min. Order Date</th>
						<th style="width:180px;">Current Station</th>
						<th style="width:180px;">Next Station</th>
						<th>Route</th>
{{--						<th>Lines</th>--}}
						<th>Image</th>
					</tr>
				</thead>
				 <tbody>					
					@foreach($batches as $batch)
						@if(stripos($batch->creation_date, "2021") !== false) @continue @endif
						@if ($batch->status == 'active' || ($batch->status == 'back order' && 
								isset($next_type[$batch->batch_route_id]) && $next_type[$batch->batch_route_id] == 'G'))
						<tr>
							<td>
									<input type="checkbox" name="batch_number[]" class="checkbox" value="{{ $batch->batch_number }}"
										@if (count($batches) == 1) 
											CHECKED
										@endif
										>					
							</td>
							<td>
								<a href = "{{ url(sprintf('batches/details/%s',$batch->batch_number )) }}">
													{{ $batch->batch_number }}</a>
								<br>
								{!! ucfirst($batch->status) !!}
							</td>
							<td>{{ $batch->creation_date }}</td>
							<td>{{ $batch->min_order_date }}</td>
							<td>
								<b>{{ $batch->first_item->vendor }}</b>
								<br>
								{{ $batch->station->station_name }} - {{ $batch->station->station_description }}
							</td>
							<td>
								@if(isset($next_in_route[$batch->batch_route_id]))
									{{ $next_in_route[$batch->batch_route_id] }}
								@endif
							</td>
							<td>
								<span>{{ $batch->route->batch_code }} <strong>{{$batch->route->batch_route_name}}</strong></span>
							</td>
{{--							<td>--}}
{{--								@if (count($batch->itemsCount) > 0)--}}
{{--									{{ $batch->itemsCount->first()->count }}--}}
{{--								@endif--}}
{{--							</td>--}}
							<td>
								@if (count($batch->first_item) > 0)
								  <span data-toggle = "tooltip" data-placement = "top"
								          title = "{{ $batch->first_item->child_sku }}">
								  	<img src = "{{ $batch->first_item->item_thumb }}" width = "70" height = "70" />
								  </span>
								@endif
							</td>
							<td>
								@if (count($batch->first_item) > 0)
									{{ $batch->first_item->child_sku }}
									<br>
									<b>QTY: {{ $batch->first_item->item_quantity }}</b>
								@endif
							</td>
						</tr>
						@endif	
					@endforeach
					
					{!! Form::close() !!}
					<tbody>
				</table>
			@else
				<br>
				<div class = "alert alert-warning">No movable batches found.</div>
			@endif
			
		</div>
	</div>
	
	<script type = "text/javascript">
		
		$(function() {
				// Focus on load
				 $('#barcode').focus();
		});
	
		var state = false;
		
		$("#select_all").on('click', function ()
		{
			state = !state;
			$(".checkbox").prop('checked', state);
		});
		
		$("#station").change( function ()
		{ 
			$("#route").val("all");
		});
		
		$("button#filter").on('click', function ()
		{
			$("#barcode_form").submit();
		});
		
		$("button#next").on('click', function ()
		{
			$("#task").val("next")
			$("#batch_move_form").submit();
		});
		
		$("button#move").on('click', function ()
		{
			$("#task").val("move")
			$("#batch_move_form").submit();
		});
		
		$(".chosen_txt").chosen();
		
		$("#barcode").change(function(event) {
				event.preventDefault();
				$("#barcode").val($("#barcode").val() + ',');
				$("#barcode").focus();
			});
		
		$("#search_button").on('click', function () {
					$("#barcode_form").submit();
			});
			
	</script>
</body>
</html>