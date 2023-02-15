<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>WAP Missing Items</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/pikaday.min.css">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/chosen.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/moment.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/pikaday.min.js"></script>
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
	<div class = "container" style="width:95%;">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href="{{ url('/wap/index') }}">WAP</a></li>
			<li class = "active">Missing Items Report</li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		<div class = "col-xs-12">
			{!! Form::open(['method' => 'get']) !!}
				<div class="row">
					<div class = "form-group col-xs-3">
						<label>Department</label>
						{!! Form::select('section_id', $sections, $request->get('section_id'), ['id'=>'section_id', 'class' => 'form-control']) !!}
					</div>
					<div class = "form-group col-xs-2">
						<label for = "start_date">Last Scan Start date</label>
						<div class = 'input-group date' id = 'start_date_picker'>
							{!! Form::text('start_date', $request->get('start_date'), ['id'=>'start_datepicker', 'class' => 'form-control', 'placeholder' => 'Enter start date', 'autocomplete' => 'off']) !!}
							<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
						</div>
					</div>
					<div class = "form-group col-xs-2">
						<label for = "end_date">Last Scan End date</label>
						<div class = 'input-group date' id = 'end_date_picker'>
							{!! Form::text('end_date', $request->get('end_date'), ['id'=>'end_datepicker', 'class' => 'form-control', 'placeholder' => 'Enter end date', 'autocomplete' => 'off']) !!}
							<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
						</div>
					</div>
					<div class = "form-group col-xs-2">
						<label for = "end_date">Printed End date</label>
						<div class = 'input-group date' id = 'print_date_picker'>
							{!! Form::text('print_date', $request->get('print_date'), ['id'=>'print_datepicker', 'class' => 'form-control', 'placeholder' => 'Enter Print date', 'autocomplete' => 'off']) !!}
							<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
						</div>
					</div>
				</div>
				<div class="row">
					<div class = "form-group col-xs-3">
						<label>Station</label>
						{!! Form::select('station', $stationsList, $request->get('station'), ['id'=>'station', 'class' => 'form-control chosen_txt']) !!}
					</div>
					<div class = "form-group col-xs-2">
						<label for = "status">Status</label>
						{!! Form::select('status', $statuses, $request->get('status'), ['id'=>'status', 'class' => 'form-control']) !!}
					</div>
					<div class = "form-group col-xs-2">
						<label for = "store">Store</label>
						{!! Form::select('store_id', $stores, $request->get('store_id'), ['id'=>'store_id', 'class' => 'form-control']) !!}
					</div>			
					<div class = "form-group col-xs-1">
					</div>
					<div class = "form-group col-xs-1">
						<label for = "" class = ""></label>
						{!! Form::submit('Search', ['id'=>'search', 'style' => 'margin-top: 5px;', 'class' => 'btn btn-primary btn-sm form-control']) !!}
					</div>
				</div>
			{!! Form::close() !!}
		</div>
		
		<div class = "col-xs-12">
			<div class="row">
				&nbsp;
			</div>
			<div class="row">
				<div class = "col-xs-6">
					<table class="table table-bordered">
						<tr class="info">
							<th>WAP</th>
							<th>Ready to Ship</th>
							<th>Holds</th>
							<th>Incomplete</th>
							<th>Total</th>
						</tr>
						<tr>
							<th class="info">Bins:</th>
							<td>{{ isset($summary['ready']) ? count($summary['ready']) : 0 }}</td>
							<td>{{ isset($summary['hold']) ? count($summary['hold']) : 0 }}</td>
							<td>{{ isset($summary['incomplete']) ? count($summary['incomplete']) : 0 }}</td>
							<td>{{ count($bins) }}</td>
						</tr>
						<tr>
							<th class="info">Items:</th>
							<td>{{ isset($summary['ready']) ? array_sum($summary['ready']) : 0  }}</td>
							<td>{{ isset($summary['hold']) ? array_sum($summary['hold']) : 0 }}</td>
							<td>{{ isset($summary['incomplete']) ? array_sum($summary['incomplete']) : 0 }}</td>
							<td>{{ $wap_items }}</td>
						</tr>
					</table>
				</div>
			</div>
			
			@if(count($batches))
				<h4 class = "page-header">
					Page  {{ $batches->currentPage() }} / {{ $batches->lastPage() }} 
					( {{ $batches->total() }} Batches Found )  
  
				</h4>
				<table id="summary_table" class="table">
				<thead>
					<tr>
						<th>Batch</th>
						<th style="width:50px;">Lines</th>
						<th>Last Scan</th>
						<th>Summary</th>
						<th style="width:200px;">Current Station</th>
						<th>Image</th>
						<th style="width:250px;">Child SKU</th>
					</tr>
				</thead>
				 <tbody>
					
					@foreach($batchlist as $batch_number)
						
						@setvar($batch = $batches->where('batch_number', $batch_number)->first())
						@setvar($batch_items = $batches->where('batch_number', $batch_number)->count())
						
						<tr>
			
							<td>
								<a href = "{{ url(sprintf('batches/details/%s',$batch->batch_number)) }}">
													{{ $batch->batch_number }}</a>
								@if (0) 
									<br>
									{{ $batch->store->store_name }}
								@endif
								@if ($batch->status != 'active')
									<br>
									{{ $batch->status }}
								@endif
							</td>
							<td>
								@if ($batch->itemsCount->first())
									{{ $batch->itemsCount->first()->count }}
								@endif
							</td>
							<td>
								{{ $batch->change_date }}
							</td>
							<td> 
								@if ($batch->summary_date != NULL)
									{{ $batch->summary_count }} Printed 
									<br>
									{{ $batch->summary_user->username }} {{ $batch->summary_date }}
								@else
									Not Printed
								@endif
							</td>
							<td>
								@if ($batch->station)
									<span data-toggle = "tooltip" data-placement = "top"
								      title = "{{ $batch->station->station_description }}">{{ $batch->station->station_name }}<br>
															{{ $batch->station->station_description }}</span>
								@endif
							</td>						
							
							@if ($batch->first_item)
																	
									<td>
									  <span data-toggle = "tooltip" data-placement = "top"
									          title = "{{ $batch->first_item->child_sku }}">
									  	<img src = "{{ $batch->first_item->item_thumb }}" width = "70" height = "70" />
									  </span>

									</td>
									<td>{{ $batch->first_item->child_sku }}</td>
									
							@else
									
									<td colspan="2"> No Items </td>
								
							@endif
							
						</tr>
					@endforeach
					<tbody>
					<tfoot>
					
					</tfoot>


				</table>
			@else
				<div class = "alert alert-warning">No batches found</div>
			@endif
		</div>
		<div class = "col-xs-12 text-center">
			{!! $batches->appends(request()->all())->render() !!}
		</div>
	</div>

	<script type = "text/javascript">
		var picker = new Pikaday(
		{
				field: document.getElementById('start_datepicker'),
				format : "YYYY-MM-DD",
				minDate: new Date('2016-06-01'),    
		});
		
		var picker = new Pikaday(
		{
				field: document.getElementById('end_datepicker'),
				format : "YYYY-MM-DD",
				minDate: new Date('2016-06-01'),    
		});
	
		var picker = new Pikaday(
		{
				field: document.getElementById('print_datepicker'),
				format : "YYYY-MM-DD",
				minDate: new Date('2016-06-01'),    
		});
		
		$(function ()
		{
			$('[data-toggle="tooltip"]').tooltip();
		});
		
		$(".chosen_txt").chosen();

	</script>
</body>
</html>