<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>WAP</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	<link type = "text/css" rel = "stylesheet" href="/assets/css/pikaday.min.css">
	<link type = "text/css" rel = "stylesheet"
	      href = "https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/moment.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/pikaday.min.js"></script>
	<script type = "text/javascript" src = "https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
	<script type = "text/javascript" src = "https://cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script>

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
			<li class = "active"><a href = "{{url('/wap/index')}}">WAP</a></li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')

		<div class = "col-xs-12">
			<div class = "form-group col-xs-1"></div>
			{!! Form::open(['url' => '/wap/index', 'method' => 'get', 'id' => 'date_form']) !!}
			<div class = "form-group col-xs-3">
				<label for = "route">Older Than:</label>
				<div class = 'input-group date' id = 'start_date_picker'>
					{!! Form::text('end_date', $end_date , ['id'=>'datepicker', 'class' => 'form-control', 'autocomplete' => 'off']) !!}
					<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
				</div>
			</div>
			<div class = "form-group col-xs-2">
				<label for = "" class = ""></label>
				{!! Form::submit('Search by Date', ['id'=>'search', 'style' => 'margin-top: 5px;', 'class' => 'btn btn-primary btn-sm form-control']) !!}
			</div>
			{!! Form::close() !!}
			<div class = "form-group col-xs-2"></div>
			{!! Form::open(['url' => '/wap/details', 'method' => 'get', 'id' => 'barcode_form']) !!}
			<div class = "form-group col-xs-2">
				<label for = "route">Scan Label</label>
				{!! Form::text('order_id', '', ['id'=>'barcode', 'class' => 'form-control']) !!}
			</div>
			<div class = "form-group col-xs-2">
				<label for = "" class = ""></label>
				{!! Form::submit('Open Bin', ['id'=>'search', 'style' => 'margin-top: 5px;', 'class' => 'btn btn-primary btn-sm form-control']) !!}
			</div>
			{!! Form::close() !!}
		</div>
		
		<div class = "col-xs-12">
			
			@if(isset($bins) && count($bins) > 0)
				<br>
				<table class="table" id="wap-table">
				<thead>
					<tr bgcolor="#DFE3EE">
						<th>Bin</th>
						<th style="width:150px;">Status</th>
						<th style="width:150px;">Last Item Added</th>
						<th style="width:200px;">Store Order</th>
						<th>Order Date</th>
						<th>In Order</th>
						<th>In Bin</th>
						<th>Rejected</th>
						<th>Backorder</th>
					</tr>
				</thead>
				 <tbody>
					@foreach($sorted_bins as $bin)
						@if (count($bin->order->shippable_items) == $bin->item_count &&
									($bin->order->order_status == 4 || $bin->order->order_status == 9))
							<tr>
								<td><a href = "{{ url(sprintf('/wap/details?bin=%d',$bin->id )) }}">{{ $bin->name }}</a></td>
								<td style="color: red;">Ready to Ship</td>
								<td>{{ $bin->last }}</td>
								<td>
									<a href = "{{ url(sprintf('/orders/details/%d',$bin->order_id )) }}">{{ $bin->order->short_order }}</a>
									@if($bin->order->store_id != '52053152')
										<br>
										{{ $bin->order->store_name }}
									@endif
								</td>
								<td>{{ substr($bin->order->order_date, 0 ,10) }}</td>
								<td>{{ count($bin->order->shippable_items) }}</td>
								<td>{{ $bin->item_count }}</td>
								<td colspan=2></td>
							</tr>
						@endif
					@endforeach

					@foreach($bins as $bin)
						@setvar( $rejected = $bin->order->items->where('item_status', 'rejected')->count() ?? 0 )
						@setvar( $backordered = $bin->order->items->where('item_status', 'back order')->count() ?? 0)

						@if (count($bin->order->shippable_items) > $bin->item_count ||
										($bin->order->order_status != 4 && $bin->order->order_status != 9))
							<tr>
								<td><a href = "{{ url(sprintf('/wap/details?bin=%d',$bin->id )) }}">{{ $bin->name }}</a></td>
								<td>
										@if ($bin->order->order_status == 4 || $bin->order->order_status == 9)
											Incomplete
										@else
											{{ $statuses[$bin->order->order_status] }}
										@endif
								</td>
								<td>{{ $bin->last }}</td>
								<td>
									<a href = "{{ url(sprintf('/orders/details/%d',$bin->order_id )) }}">{{ $bin->order->short_order }}</a>
									@if($bin->order->store_id != '52053152')
										<br>
										{{ $bin->order->store_name }}
									@endif
								</td>
								<td
								@if (strtotime($bin->order->order_date) < strtotime('-7 days'))
									style="color: red;"
								@endif
								>{{ substr($bin->order->order_date, 0 ,10) }}</td>
								<td>{{ count($bin->order->shippable_items) }}</td>
								<td>{{ $bin->item_count }}</td>
								<td>
									@if ($rejected > 0)
										{{ $rejected }}
									@endif
								</td>
								<td>
									@if ($backordered > 0)
										{{ $backordered }}
									@endif
								</td>
							</tr>
						@elseif (count($bin->order->shippable_items) < $bin->item_count)
							<tr>
								<td><a href = "{{ url(sprintf('/wap/details?bin=%d',$bin->id )) }}">{{ $bin->name }}</a></td>
								<td>ERROR</td>
								<td></td>
								<td>
									<a href = "{{ url(sprintf('/orders/details/%d',$bin->order_id )) }}">{{ $bin->order_id }}</a>
									@if($bin->order->store_id != '52053152')
										<br>
										{{ $bin->order->store_name }}
									@endif
								</td>
								<td
								@if (strtotime($bin->order->order_date) < strtotime('-7 days'))
									style="color: red;"
								@endif
								>{{ substr($bin->order->order_date, 0 ,10) }}</td>
								<td>{{ count($bin->order->shippable_items) }}</td>
								<td>{{ $bin->item_count }}</td>
								<td>
									@if ($rejected > 0)
										{{ $rejected }}
									@endif
								</td>
								<td>
									@if ($backordered > 0)
										{{ $backordered }}
									@endif
								</td>
							</tr>
						@endif
					@endforeach
					<tbody>
				</table>

			@else
				<br>
				<div class = "alert alert-warning">No bins found.</div>
			@endif
			
		</div>
	</div>
	
	<script type = "text/javascript">
	
		var picker = new Pikaday(
		{
				field: document.getElementById('datepicker'),
				format : "YYYY-MM-DD",
				minDate: new Date('2016-06-01'),
				maxDate: new Date(),
				yearRange: [2000,2030]      
		});
	
		$(function() {
				// Focus on load
				 $('#barcode').focus();
				 
		});
	
		$(document).ready(function() {
			
			// $('#wap-table').DataTable( {
			// 		"bPaginate": false,
			// 		"columns": [
			// 		
			// 		{"name": "Status", "orderable": true},
			// 		{"name": "Last Item Added", "orderable": true},
			// 		{"name": "Bin", "orderable": false},
			// 		{"name": "Store Order", "orderable": false},
			// 		{"name": "Order Date", "orderable": false},
			// 		{"name": "Items in Order", "orderable": false},
			// 		{"name": "Items in Bin", "orderable": false}
			// 		]
			// 	});
			
				var pressed = false; 
				var chars = []; 
				$(window).keypress(function(e) {
						if (e.which >= 48 && e.which <= 57) {
								chars.push(String.fromCharCode(e.which));
						}
						console.log(e.which + ":" + chars.join("|"));
						if (pressed == false) {
								setTimeout(function(){
										if (chars.length >= 10) {
												var barcode = chars.join("");
												console.log("Barcode Scanned: " + barcode);
												// assign value to some input (or do whatever you want)
												$("#barcode").val(barcode);
												$("#barcode_form").submit();
										}
										chars = [];
										pressed = false;
								},500);
						}
						pressed = true;
				});
		});

	</script>
</body>
</html>

