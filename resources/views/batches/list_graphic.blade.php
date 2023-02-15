<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Batch list</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/pikaday.min.css">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/chosen.min.css">

	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/moment.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/pikaday.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/chosen.jquery.min.js"></script>
</head>
<body>
@include('includes.header_menu')
<div class = "container" style="min-width: 1400px;">
	<ol class = "breadcrumb">
		<li><a href = "{{url('/')}}">Home</a></li>
		<li class = "active"><a href = "{{url('/batches/list')}}">Batch list Graphic</a></li>
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
				<label for = "batch">Batch#</label>
				{!! Form::text('batch', $request->get('batch'), ['id'=>'batch', 'class' => 'form-control', 'placeholder' => 'Search in batch']) !!}
			</div>
			<div class = "form-group col-xs-2">
				<label for = "status">Status</label>
				{!! Form::select('status', $statuses, $request->get('status'), ['id'=>'status', 'class' => 'form-control']) !!}
			</div>
			<div class = "form-group col-xs-2">
				<label for = "store">Store</label>
				{!! Form::select('store_id', $stores, $request->get('store_id'), ['id'=>'store_id', 'class' => 'form-control']) !!}
			</div>
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
		</div>
		<div class="row">
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


					<th>Image</th>
				</tr>
				</thead>
				<tbody>
				{!! Form::open(['url' => url('prints/batches'), 'method' => 'get', 'id' => 'batch_list_form']) !!}
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				@setvar( $totalJobs = 0)
				@foreach($batches as $batch)

					@if ($totalJobs == 6)
						@setvar( $totalJobs = 0)

						<tr>

							@if ($batch->first_item)
								<td>
								  <span data-toggle = "tooltip" data-placement = "top"
										title = "{{ $batch->first_item->child_sku }}">
									<img src = "{{ $batch->first_item->item_thumb }}"
										 @if($batch->first_item->product)
										 onerror="{{ $batch->first_item->product->product_thumb }}"
										 @endif
										 height = "200" />
								  </span>
									<input type = "checkbox" name = "batch_number[]" class = "checkbox"
										   value = "{{ $batch->batch_number }}" />
									<br><a href = "{{ url(sprintf('batches/details/%s',$batch->batch_number)) }}">{{ $batch->batch_number }}</a>
								</td>

							@else

								<td colspan="2"> No Items </td>

							@endif

						</tr>
					@else



						@if ($batch->first_item)
							<td>
								  <span data-toggle = "tooltip" data-placement = "top"
										title = "{{ $batch->first_item->child_sku }}">
									<img src = "{{ $batch->first_item->item_thumb }}"
										 @if($batch->first_item->product)
										 onerror="{{ $batch->first_item->product->product_thumb }}"
										 @endif
										 height = "200" />
								  </span>
								<input type = "checkbox" name = "batch_number[]" class = "checkbox"
									   value = "{{ $batch->batch_number }}" />
								<br><a href = "{{ url(sprintf('batches/details/%s',$batch->batch_number)) }}">{{ $batch->batch_number }}</a>

							</td>

						@else

							<td colspan="2"> No Items </td>

						@endif


					@endif
					@setvar( $totalJobs ++)
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