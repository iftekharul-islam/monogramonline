<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Profit Report</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap-multiselect.css">
	<link type = "text/css" rel = "stylesheet" href="/assets/css/pikaday.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap-multiselect.js"></script>	
	<script type = "text/javascript" src = "/assets/js/moment.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/pikaday.min.js"></script>
	
	<style>
	th {
		font-size: 12px;
	}
	</style>
</head>
<body>
	@include('includes.header_menu')
	<div class = "container">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "{{url('report/profit')}}">Profit Report</a></li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		
		<div class = "col-xs-12">
			<div class = "panel panel-default">
				<div class = "panel-heading">Search</div>
				<div class = "panel-body">
					{!! Form::open(['method' => 'get', 'url' => url('report/profit'), 'id' => 'search-order']) !!}
					<div class="row">
						<div class = "form-group col-xs-2">
							{!! Form::text('search_for_first', $request->get('search_for_first'), ['id'=>'search_for_first', 'class' => 'form-control', 'placeholder' => 'Search For 1']) !!}
						</div>
						<div class = "form-group col-xs-2">
							{!! Form::select('operator_first', $operators, $request->get('operator_first'), ['id'=>'operator_first', 'class' => 'form-control']) !!}
						</div>
						<div class = "form-group col-xs-2">
							{!! Form::select('search_in_first', $search_in, $request->get('search_in_first'), ['id'=>'search_in_first', 'class' => 'form-control']) !!}
						</div>
						<div class = "form-group col-xs-2">
							{!! Form::text('search_for_second', $request->get('search_for_second'), ['id'=>'search_for_second', 'class' => 'form-control', 'placeholder' => 'Search For 2']) !!}
						</div>
						<div class = "form-group col-xs-2">
							{!! Form::select('operator_second', $operators, $request->get('operator_second'), ['id'=>'operator_second', 'class' => 'form-control']) !!}
						</div>
						<div class = "form-group col-xs-2">
							{!! Form::select('search_in_second', $search_in, $request->get('search_in_second'), ['id'=>'search_in_second', 'class' => 'form-control']) !!}
						</div>
					</div>
					
					<div class="row">
						<div class = "form-group col-xs-2">
							<div class = 'input-group date'>
								{!! Form::text('start_date', $request->get('start_date'), ['id'=>'start_date_picker', 'class' => 'form-control', 'placeholder' => 'Start date', 'autocomplete' => 'off']) !!}
								<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
							</div>
						</div>
						<div class = "form-group col-xs-2">
							<div class = 'input-group date'>
								{!! Form::text('end_date', $request->get('end_date'), ['id'=>'end_date_picker', 'class' => 'form-control', 'placeholder' => 'End date', 'autocomplete' => 'off']) !!}
								<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
							</div>
						</div>
						<div class = "form-group col-xs-2">
							{!! Form::select('store', $stores, $request->get('store'), ['id'=>'store', 'class' => 'form-control', 'placeholder' => 'Select Store']) !!}
						</div>
						<div class = "form-group col-xs-4">
							{!! Form::select('status[]', $statuses, $request->get('status'), ['id'=>'status', 'multiple' => 'multiple', 'class' => 'form-control']) !!}
						</div>
						<div class = "form-group col-xs-2">
							{!! Form::submit('Search', ['id'=>'search', 'class' => 'btn btn-primary form-control']) !!}
						</div>
					</div>
					{!! Form::close() !!}
				</div>
			</div>
		</div>
		
		@if(count($orders) > 0)
			<div class = "col-xs-12">
				<div class="col-xs-6">
					<h3>
					Profit Report <small>({{$orders->currentPage()}} of {{$orders->lastPage()}} pages)</small>
					</h3>					
				</div>
			</div>
			
			<div class = "col-xs-12">
				
			<table class = "table table-bordered">
				
				<tr>
					<th>Order</th>
					<th colspan=3>Coupon</th>
					<th align="center">QTY</th>
					<th align="center">Item<br>Price</th>
					<th align="center">Item<br>Cost</th>
					<th align="center">Labor</th>
					<th align="center">Shipping<br>Cost</th>
					<th align="center">Shipping<br>Paid</th>
					<th align="center">Total<br>Paid</th>
					<th align="center">Profit</th>
					<th align="center">Margin</th>
				</tr>
				
				@foreach($orders as $order)
					<tr  class="warning" data-id = "{{$order->id}}">
						<td>
								<a href = "{{ url("orders/details/" . $order->id) }}" target = "_blank">{{ $order->short_order }}</a>
						</td>
						<td>{{ $order->coupon_id }} {{ $order->promotion_id}}</td>
						<td>{{ $order->coupon_description }}</td>
						<td align="right">(${!! sprintf("%01.2f", $order->promotion_value + $order->coupon_value) !!})</td>
						<td align="right">{{ $order->items->sum('item_quantity') }}</td>
						<td align="right">${!! sprintf("%01.2f", $order->items->sum('item_total_price')) !!}</td>
						<td align="right">${{ number_format($order_info[$order->id]['total_cost'], 2) }}</td>
						<td align="right">${{ number_format($order_info[$order->id]['total_labor'], 2) }}</td>
						<td align="right">${{ number_format($order_info[$order->id]['shipping_cost'], 2) }}</td>
						<td align="right">${!! sprintf("%01.2f",$order->shipping_charge) !!}</td>
						@setvar($diff = sprintf("%01.2f",$order->items->sum('item_total_price') - ( $order->promotion_value + $order->coupon_value) +  $order->shipping_charge + $order->tax_charge) - $order->total)
						
						<td align="right">
							<strong @if ($diff != 0) style="color:red;"@endif>
								${!! sprintf("%01.2f", $order->total) !!}
							</strong>
						</td>
						<td align="right">
							{{$order_info[$order->id]['profit'] }}
						</td>
						<td>{{ $order_info[$order->id]['margin'] }}</td>
					</tr>
					
					@foreach ($order_info[$order->id]['items'] as $item)
						<tr>
							<td>{{ $item['item_code'] }}</td>
							<td colspan=3>{{ $item['item_description'] }}</td>
							<td align="right">{{ $item['item_quantity'] }}</td>
							<td align="right">${{ $item['item_unit_price'] }}</td>
							<td align="right"@if ($item['cost'] == 0) bgcolor = "#F9EBEA" @endif>
								${{ number_format($item['cost'] * $item['item_quantity'], 2) }}
							</td>
							<td align="right">${{ number_format($item['labor'], 2) }}</td>
							<td colspan=5></td>
						</tr>
					@endforeach
				@endforeach
			</table>
		</div>
		
			<div class = "col-xs-12 text-center">
				{!! $orders->appends($request->all())->render() !!}
			</div>
		@else
			<br>
			<div class = "col-xs-12">
				<div class = "alert alert-warning text-center">
					No orders found.
				</div>
			</div>
		@endif
	</div>


	<script type = "text/javascript">
		$(document).ready(function() {

			$('#status').multiselect();
		});

		var picker = new Pikaday(
		{
				field: document.getElementById('start_date_picker'),
				format : "YYYY-MM-DD",
				minDate: new Date('2016-06-01'),
				maxDate: new Date(),
				yearRange: [2000,2030]      
		});
		
		var picker = new Pikaday(
		{
				field: document.getElementById('end_date_picker'),
				format : "YYYY-MM-DD",
				minDate: new Date('2016-06-01'),
				maxDate: new Date(),
				yearRange: [2000,2030]      
		});
		
	</script>
</body>
</html>