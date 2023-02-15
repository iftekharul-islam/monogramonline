<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Marketplace Exports</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">

	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
</head>
<body>
	@include('includes.header_menu')
	<div class = "container" style="min-width: 1400px;">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "{{url('/transfer/export')}}">Export Shipments</a></li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		
		@if (isset($details) && count($details) > 0 && !$isDropship)
		
			<h3 class="page-header">
				{{ $store->store_name }} {{ $title }} Exports 
				<small class="pull-right">{{ count($details) }} Shipments Ready to Export</small>
			</h3>
			
			{!! Form::open(['method' => 'post', 'url' => url('transfer/export/create')]) !!}
			{!! Form::hidden('store_id', $store->store_id) !!}
			{!! Form::hidden('type', $type) !!}
			{!! Form::hidden('dropship', "yes") !!}

			<div class="col-xs-2">
			{!! Form::submit('Export', ['id'=>'export_button', 'class' => 'btn btn-primary btn-sm form-control']) !!}
			<br><br>
			</div>
			
			<table class="table table-bordered">
				
				<tr>
					<th width="50">
						<input type="checkbox" name="select_all" id="select_all" class="checkbox">	
					</th>
					<th>Select All</th>
					<th>Order Date</th>
					@if ($type == 'csv')
						<th>Ship Method</th>
						<th>Tracking</th>
					@elseif ($type == 'qb')
						<th>Invoice</th>
						<th>Invoice Date</th>
					@endif
				</tr>
				
				@foreach ($details as $row)
					<tr>
						<td>
							<input type = "checkbox" name = "ship_ids[]" class = "checkbox"
										 value = "{{ $row->id }}" />
						</td>
						<td>
							<a href = "{{url(sprintf('/orders/details/%s', $row->order_number))}}"
								 target = "_blank">{{ $row->order->short_order }}</a>
						</td>
						<td>
							{{ $row->order->order_date }}
						</td>
						@if ($type == 'csv')
							<td>
								{{ $row->mail_class }}
							</td>
							<td>
								{{ $row->shipping_id }}
							</td>
						@elseif ($type == 'qb')
							<td>
								{{ $row->unique_order_id }}
							</td>
							<td>
								{{ $row->transaction_datetime }}
							</td>
						@endif
					</tr>
				@endforeach
			</table>
			
			<div class="col-xs-2">
			{!! Form::submit('Export', ['id'=>'export_button', 'class' => 'btn btn-primary btn-sm form-control']) !!}
			</div>
			
			{!! Form::close('type', $type) !!}
			
		@endif
		@if (isset($dropship) && count($dropship) > 0)

			<h3 class="page-header">
				{{ $store->store_name }} {{ $title }} Exports
				<small class="pull-right">{{ count($dropship) }} Shipments Ready to Export</small>
			</h3>

			{!! Form::open(['method' => 'post', 'url' => url('transfer/export/create')]) !!}
			{!! Form::hidden('store_id', $store->store_id) !!}
			{!! Form::hidden('type', $type) !!}
			{!! Form::hidden('$dropship', "yes") !!}

			<div class="col-xs-2">
				{!! Form::submit('Export', ['id'=>'export_button', 'class' => 'btn btn-primary btn-sm form-control']) !!}
				<br><br>
			</div>

			<table class="table table-bordered">

				<tr>
					<th width="50">
						<input type="checkbox" name="select_all" id="select_all" class="checkbox">
					</th>
					<th>Select All</th>
					<th>Order Date</th>
					@if ($type == 'csv')
						<th>Ship Method</th>
						<th>Tracking</th>
					@elseif ($type == 'qb')
						<th>Invoice</th>
						<th>Invoice Date</th>
					@endif
				</tr>

				@foreach ($dropship as $row)
					<tr>
						<td>
							<input type = "checkbox" name = "ship_ids[]" class = "checkbox"
								   value = "{{ $row->id }}" />
						</td>
						<td>
							<a href = "{{url(sprintf('/orders/details/%s', $row->id))}}"
							   target = "_blank">{{ $row->short_order }}</a>
						</td>
						<td>
							{{ $row->order_date }}
						</td>
							<td>
								{{ $row->method }}
							</td>
							<td>

							</td>
					</tr>
				@endforeach
			</table>

			<div class="col-xs-2">
				{!! Form::submit('Export', ['id'=>'export_button', 'class' => 'btn btn-primary btn-sm form-control']) !!}
			</div>

			{!! Form::close('type', $type) !!}

		@endif
	</div>
	
	<script type = "text/javascript">
	
	var state = false;
	
	$("#select_all").on('click', function ()
	{
		state = !state;
		$(".checkbox").prop('checked', state);
	});
	
	</script>
	
</body>
</html>