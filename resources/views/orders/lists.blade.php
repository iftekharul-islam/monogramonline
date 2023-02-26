<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Orders list</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap-multiselect.css">
	<link type = "text/css" rel = "stylesheet" href="/assets/css/pikaday.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap-multiselect.js"></script>	
	<script type = "text/javascript" src = "/assets/js/moment.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/pikaday.min.js"></script>
</head>
<body>
	@include('includes.header_menu')
	<div class = "container" style="min-width: 1400px;">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "{{url('orders/list')}}">Orders</a></li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		
		<div class = "col-xs-12">
			<div class = "panel panel-default">
				<div class = "panel-heading">Search</div>
				<div class = "panel-body">
					{!! Form::open(['method' => 'get', 'url' => url('orders/list'), 'id' => 'search-order']) !!}
					<div class="row">
						<div class = "form-group col-xs-2">
							{!! Form::text('search_for_first', $request->get('search_for_first'), ['id'=>'search_for_first', 'class' => 'form-control', 'placeholder' => 'Search For 1']) !!}
						</div>
						<div class = "form-group col-xs-1">
							{!! Form::select('operator_first', $operators, $request->get('operator_first'), ['id'=>'operator_first', 'class' => 'form-control']) !!}
						</div>
						<div class = "form-group col-xs-2">
							{!! Form::select('search_in_first', $search_in, $request->get('search_in_first'), ['id'=>'search_in_first', 'class' => 'form-control']) !!}
						</div>
						<div class = "form-group col-xs-2">
							{!! Form::text('search_for_second', $request->get('search_for_second'), ['id'=>'search_for_second', 'class' => 'form-control', 'placeholder' => 'Search For 2']) !!}
						</div>
						<div class = "form-group col-xs-1">
							{!! Form::select('operator_second', $operators, $request->get('operator_second'), ['id'=>'operator_second', 'class' => 'form-control']) !!}
						</div>
						<div class = "form-group col-xs-2">
							{!! Form::select('search_in_second', $search_in, $request->get('search_in_second'), ['id'=>'search_in_second', 'class' => 'form-control']) !!}
						</div>
						
						<div class = "form-group col-xs-2">
							{!! Form::submit('Search', ['id'=>'search', 'class' => 'btn btn-primary form-control']) !!}
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
						<div class = "form-group col-xs-3">
							{!! Form::select('status[]', $statuses, $request->get('status'), ['id'=>'status', 'multiple' => 'multiple', 'class' => 'form-control']) !!}
						</div>
						<div class = "form-group col-xs-3">
							{!! Form::select('store[]', $stores, $request->get('store'), ['id'=>'store', 'class' => 'form-control', 'multiple' => 'multiple']) !!}
						</div>
					</div>
					{!! Form::close() !!}
				</div>
			</div>
		</div>
		
		@if(count($orders) > 0)
			<div class = "col-xs-12">
				<div class="col-xs-3">
					<h3>
					Orders <small>({{$orders->currentPage()}} of {{$orders->lastPage()}} pages)</small>
					</h3>					
				</div>
				<div class="col-xs-6">
					<table class="table table-bordered table-condensed">
						<tr class="small info">
							<th>Total Orders</th>
							<th>Total Amount</th>
							<th>Average Amount</th>
							<th>Tax Total</th>
							<th>Shipping Total</th>
						</tr>
						<tr class="small">
							<td align="right">{{ number_format($orders->total()) }} </td>
							<td align="right">${{ number_format($total->money, 2) }}</td>
							<td align="right">
							@if ($orders->total() > 0)
								${{ number_format($total->money / $orders->total(), 2) }}
							@else
								${{ number_format($total->money, 2) }}
							@endif
							</td>
							<td align="right">${{ number_format($total->tax, 2) }}</td>
							<td align="right">${{ number_format($total->shipping, 2) }}</td>
						</tr>
					</table>
				</div>
				<div class="col-xs-2">
					@if(!empty($orders) && count($orders) > 0 && $orders->total() < 400000)
						{!! Form::open(['url' => url('/exports/orders'), 'method' => 'post']) !!}
							@setvar($data1 = serialize($request->get('store')))
							{!! Form::hidden('store', $data1) !!}
							@setvar($data = serialize($request->get('status')))
							{!! Form::hidden('status', $data) !!}
							{!! Form::hidden('search_in_first', $request->get('search_in_first')) !!}
							{!! Form::hidden('operator_first', $request->get('operator_first')) !!}
							{!! Form::hidden('search_for_first', $request->get('search_for_first')) !!}
							{!! Form::hidden('search_in_second', $request->get('search_in_second')) !!}
							{!! Form::hidden('operator_second', $request->get('operator_second')) !!}
							{!! Form::hidden('search_for_second', $request->get('search_for_second')) !!}
							{!! Form::hidden('shipping_method', $request->get('shipping_method')) !!}
							{!! Form::hidden('start_date', $request->get('start_date')) !!}
							{!! Form::hidden('end_date', $request->get('end_date')) !!}
							{!! Form::hidden('count', $orders->total()) !!}
							{!! Form::submit('Create CSV Export##', ['class' => 'btn btn-success', 'style' => 'margin-bottom:0px;']) !!}
						{!! Form::close() !!}
					@endif
				</div>
			</div>
			
			<div class = "col-xs-12">
				
			<table class = "table table-bordered table-condensed">
				<tr>
					<th>Order/PO</th>
					<th>Date</th>
					<th>Customer</th>
					<th>Items</th>
					<th>Subtotal</th>
					<th>Discount</th>
					<th>Shipping</th>
					<th>Tax</th>
					<th>Total</th>
					<th>Status</th>
					<th>Coupon</th>
				</tr>
				@foreach($orders as $order)
					<tr data-id = "{{$order->id}}">
						<td>
								<a href = "{{ url("orders/details/" . $order->id) }}" target = "_blank">{{ $order->short_order }}</a>
								<br>
								<a href = "{{ url("orders/details/" . $order->id) }}" target = "_blank">#{{ $order->purchase_order }}</a>
								<div class="pull-right">
									{!! \App\Task::widget('App\Order', $order->id, null, 10); !!}
								</div>
						</td>
						<td>
								@if ($order->store)
									@if ($order->store->company > 0)
										{{ $companies[$order->store->company] }} :
									@endif
									{{ $order->store->store_name }}
								@else
									STORE NOT FOUND
								@endif
								<br>
								{{ \Carbon\Carbon::parse($order->order_date)->format("Y-m-d h:i:s a")}}
						</td>
						<td>
							<a href = "{{ url("/customer_service/customers/" . ($order->customer ? $order->customer->id : "#")) }}"
										 target = "_blank">{{$order->customer ? $order->customer->ship_full_name : "#"}}</a>
							<br>
							{{$order->customer ? $order->customer->ship_state: "#"}}, {{$order->customer ? $order->customer->ship_country : "#"}}
						</td>
						<td>{{$order->item_count}}</td>
						<td align="right" >${!! sprintf("%01.2f", $order->items->sum('item_total_price')) !!}</td>
						<td align="right">(${!! sprintf("%01.2f", $order->promotion_value + $order->coupon_value) !!})</td>
						<td align="right">${!! sprintf("%01.2f",$order->shipping_charge) !!}</td>
						<td align="right">${!! sprintf("%01.2f",$order->tax_charge) !!}</td>
						
						@setvar($diff = sprintf("%01.2f",$order->items->sum('item_total_price') - ( $order->promotion_value + $order->coupon_value) +  $order->shipping_charge + $order->tax_charge) - $order->total)
						
						<td align="right">
							<strong @if ($diff != 0) style="color:red;"@endif>
								${!! sprintf("%01.2f", $order->total) !!}
							</strong>
						</td>
						<td>{!! $statuses[$order->order_status] !!} <br> {{ $order->carrier }} {{ $order->method }}</td>
						<td>{{ $order->promotion_id }} {{ $order->coupon_id }}</td>
					</tr>
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
					No order found.
				</div>
			</div>
		@endif
	</div>

	<script type = "text/javascript">
		$(document).ready(function() {

			$('#status').multiselect({includeSelectAllOption:true,
																nonSelectedText:'Filter By Status',
																numberDisplayed: 1,});
																
			$('#store').multiselect({includeSelectAllOption:true,
																nonSelectedText:'Filter By Store',
																numberDisplayed: 1,});
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