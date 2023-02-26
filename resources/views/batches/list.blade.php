<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Batch list</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap-multiselect.css">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/pikaday.min.css">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/chosen.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap-multiselect.js"></script>
	<script type = "text/javascript" src = "/assets/js/moment.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/pikaday.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/chosen.jquery.min.js"></script>	
</head>
<body>
	@include('includes.header_menu')
	<div class = "container" style="min-width: 1400px;">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li class = "active"><a href = "{{url('/batches/list')}}">Batch list</a></li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		<div class = "col-xs-12">
			{!! Form::open(['method' => 'get']) !!}
			<div class="row">
				<div class = "form-group col-xs-3">
					<label for = "route">Route</label>
					{!! Form::select('route', $routes, $request->get('route'), ['id'=>'route', 'class' => 'form-control chosen_txt']) !!}
				</div>
				<div class = "form-group col-xs-3">
					<label for = "route">Station</label>
					{!! Form::select('station', $stationsList, $request->get('station'), ['id'=>'station', 'class' => 'form-control chosen_txt']) !!}
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
			</div>
			<div class="row">
				<div class = "form-group col-xs-2">
					<label for = "filter_username">User</label>
					{!! Form::text('filter_username', $request->get('filter_username', ''), ['id'=>'filter_username', 'class' => 'form-control', 'placeholder' => 'Search by user']) !!}
				</div>

				<div class = "form-group col-xs-2">
					<label for = "batch">Batch#</label>
					{!! Form::text('batch', $request->get('batch'), ['id'=>'batch', 'class' => 'form-control', 'placeholder' => 'Search in batch']) !!}
				</div>
				<div class = "form-group col-xs-2">
					<label for = "status">Status</label>
					{!! Form::select('status', $statuses, $request->get('status'), ['id'=>'status', 'class' => 'form-control']) !!}
				</div>
				<div class = "form-group col-xs-2">
					<label class="col-xs-12" for="store">Store</label>
					{!! Form::select('store[]', $stores, $request->get('store'), ['id'=>'store', 'class' => 'form-control', 'multiple' => 'multiple']) !!}
				</div>
			</div>
			<div class="row">
				<div class = "form-group col-xs-2">
					<label for = "order_start_date">Order Start date</label>
					<div class = 'input-group date' id = 'order_start_date_picker'>
						{!! Form::text('order_start_date', $request->get('order_start_date'), ['id'=>'order_start_datepicker', 'class' => 'form-control', 'placeholder' => 'Order start date', 'autocomplete' => 'off']) !!}
						<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
					</div>
				</div>
				<div class = "form-group col-xs-2">
					<label for = "order_end_date">Order End date</label>
					<div class = 'input-group date' id = 'order_end_date_picker'>
						{!! Form::text('order_end_date', $request->get('order_end_date'), ['id'=>'order_end_datepicker', 'class' => 'form-control', 'placeholder' => 'Order end date', 'autocomplete' => 'off']) !!}
						<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
					</div>
				</div>
				<div class = "form-group col-xs-2">
					<label for = "" class = ""></label>
					{!! Form::submit('Search', ['id'=>'search', 'style' => 'margin-top: 5px;', 'class' => 'btn btn-primary btn-sm form-control']) !!}
				</div>
			</div>
			{!! Form::close() !!}
		</div>
		<div class = "col-xs-12">
			@if(count($batches))
				<h4 class = "page-header">
					Page  {{ $batches->currentPage() }} / {{ $batches->lastPage() }} 
					( {{ $batches->total() }} Batches Found ) Total lines : {{ $total->count }}   quantity : {{ $total->quantity }} 
  
				</h4>
				<table class="table">
				<thead>
					<tr>
						<th style="width:50px;"></th>
						<th style="width:100px;">Batch</th>
						<th style="width:50px;"></th>
						<th style="width:100px;">First Order</th>
						<th style="width:50px;">Lines</th>
						<th style="width:200px;">Current Station</th>
						<th style="width:150px;">Last Scan</th>
						<th style="width:75px;">Image</th>
						<th style="width:250px;">Child SKU</th>
						<th style="width:250px;">User</th>
					</tr>
				</thead>
				 <tbody>
					{!! Form::open(['url' => url('prints/batches'), 'method' => 'get', 'id' => 'batch_list_form']) !!}
					<input type="hidden" name="_token" value="{{ csrf_token() }}">
					@foreach($batches as $batch)
						<tr>
							<td>
								<input type = "checkbox" name = "batch_number[]" class = "checkbox"
								       value = "{{ $batch->batch_number }}" />
							</td>
							<td>
								<a href = "{{ url(sprintf('batches/details/%s',$batch->batch_number)) }}">
													{{ $batch->batch_number }}</a>
								<small>
								@if ($batch->status != 'active')
									<br>
									({{ ucFirst($batch->status) }})
								@endif
								@if ($batch->store)
									<br>
									{{ $batch->store->store_name }}
								@endif
								</small>
							</td>
							<td>
								{!! \App\Task::widget('App\Batch', $batch->id, null, 10); !!}
							</td>
							<td>{{ substr($batch->min_order_date, 0, 10) }}</td>
							<td>
								@if ($batch->itemsCount->first())
									{{ $batch->itemsCount->first()->count }}
								@endif
							</td>
							<td>
								@if ($batch->station)
									<span data-toggle = "tooltip" data-placement = "top"
								      title = "{{ $batch->station->station_description }}">{{ $batch->station->station_name }}<br>
															{{ $batch->station->station_description }}</span>
								@endif
							</td>
							<td>
								{{ $batch->change_date }}
							</td>
						
							
							@if ($batch->first_item)
																	
									<td>
									  <span data-toggle = "tooltip" data-placement = "top"
									          title = "{{ $batch->first_item->child_sku }}">
									  	<img src = "{{ $batch->first_item->item_thumb }}" 
													@if($batch->first_item->product)
														onerror="{{ $batch->first_item->product->product_thumb }}"
													@endif
													height = "70" />
									  </span>

									</td>
									<td>{{ $batch->first_item->child_sku }}</td>
									
							@else
									
									<td colspan="2"> No Items </td>
								
							@endif

							<td>{{ $scans[$batch->batch_number] }}</td>
						</tr>
					@endforeach
					<tbody>
					<tfoot>
					<tr>
						<td colspan = "12">
							{!! Form::button('Select / Deselect all', ['id' => 'select_deselect', 'class' => 'btn btn-link']) !!}
							{!! Form::button('Export Orders', ['id' => 'export_orders', 'class' => 'btn btn-link']) !!}
							{!! Form::button('Export Batch CSV', ['id' => 'export_batch', 'class' => 'btn btn-link']) !!}
							{!! Form::button('Get Graphic From Archive', ['id' => 'reprint_batch', 'class' => 'btn btn-link']) !!}
							{!! Form::button('Send bulk E-mail', ['id' => 'bulk_email', 'class' => 'btn btn-link']) !!}
							{!! Form::button('Print Summaries', ['id' => 'bulk_summaries', 'class' => 'btn btn-link']) !!}
							{!! Form::button('Release Batches', ['id' => 'bulk_release', 'class' => 'btn btn-link']) !!}
							{!! Form::button('Update Graphic From Link', ['id' => 'update_graphics', 'class' => 'btn btn-link']) !!}
						</td>
					</tr>
					</tfoot>
					{!! Form::close() !!}

				</table>
				
				<div class = "col-xs-12 text-center">
					{!! $batches->appends(request()->all())->render() !!}
				</div>
				
			@elseif ($request->all() != [])
				<div class = "alert alert-warning">No Batches Found</div>
			@endif
		</div>
	</div>

	<script type = "text/javascript">
		$(function() {
				// Focus on load
			 	$('#batch').focus();

				$('#store').multiselect({includeSelectAllOption:true,
				nonSelectedText:'Filter By Store',
				numberDisplayed: 1,});
		});
		
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
				field: document.getElementById('order_start_datepicker'),
				format : "YYYY-MM-DD",
				minDate: new Date('2016-06-01'),    
		});
		
		var picker = new Pikaday(
		{
				field: document.getElementById('order_end_datepicker'),
				format : "YYYY-MM-DD",
				minDate: new Date('2016-06-01'),    
		});

		$(function ()
		{
			$('[data-toggle="tooltip"]').tooltip();
		});

		$("button#export_orders").on('click', function (event)
		{
			var url = "{{ url('/exports/export_orders') }}";
			setFormUrlAndSubmit(url);
		});		
		
		$("button#export_batch").on('click', function (event)
		{
			var url = "{{ url('/graphics/export_batchbulk') }}";
			setFormUrlAndSubmit(url);
		});	
		
		$("button#reprint_batch").on('click', function (event)
		{
			var url = "{{ url('/graphics/reprint_bulk') }}";
			setFormUrlAndSubmit(url);
		});	
		
		$("button#bulk_email").on('click', function (event)
		{
			var url = "{{ url('/customer_service/bulk_email') }}";
			setFormUrlAndSubmit(url);
		});	

		$("button#bulk_summaries").on('click', function (event)
		{
			var url = "{{ url('/supervisor/print_summaries') }}";
			setFormUrlAndSubmit(url);
		});	
		
		$("button#bulk_release").on('click', function (event)
		{
			var url = "{{ url('/orders_admin/bulk_release') }}";
			setFormUrlAndSubmit(url);
		});

		$("button#update_graphics").on('click', function (event)
		{
			event.preventDefault();

			var url = "http://order.monogramonline.com/lazy/mass";
			setFormUrlAndSubmit(url);
		});


		function setFormUrlAndSubmit (url)
		{
			var form = $("form#batch_list_form");
			$(form).attr('action', url);
			$(form).submit();
		}
		var state = false;

		$("button#select_deselect").on('click', function ()
		{
			state = !state;
			$(".checkbox").prop('checked', state);
		});

		$(".chosen_txt").chosen();

	</script>
</body>
</html>