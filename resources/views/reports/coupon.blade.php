<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Coupon Report</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/pikaday.min.css">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap-multiselect.css">
		
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/moment.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/pikaday.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap-multiselect.js"></script>	

	<style>
		tr {
			font-size: 12px;
		}
		.data {
			/*border-left: 1px solid #ddd;*/
			text-align: right;
		}
	</style>

</head>
<body>
	@include('includes.header_menu')
	<div class = "container">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "{{url('/report/coupon')}}">Coupon Report</a></li>
		</ol>

		<h3 class = "page-header">Coupon Report</h3>
		
		<div class = "col-xs-12">
			<div class = "panel panel-default">
				<div class = "panel-heading">Search</div>
				<div class = "panel-body">
					{!! Form::open(['method' => 'get']) !!}
						<div class = "form-group col-xs-4">
							<label for = "store_ids">Store</label>
							<br>
							{!! Form::select('store_ids[]', $stores, $store_ids ?? [], ['id'=>'store_ids', 'multiple' => 'multiple', 'class' => 'form-control']) !!}
						</div>
						<div class = "form-group col-xs-3">
							<label for = "start_date">Start Order date</label>
							<div class = 'input-group date' id = 'start_date_picker'>
								{!! Form::text('start_date', $start_date, ['id'=>'start_datepicker', 'class' => 'form-control', 'placeholder' => 'Enter start date', 'autocomplete' => 'off']) !!}
								<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
							</div>
						</div>
						<div class = "form-group col-xs-3">
							<label for = "end_date">End Order date</label>
							<div class = 'input-group date' id = 'end_date_picker'>
								{!! Form::text('end_date', $end_date, ['id'=>'end_datepicker', 'class' => 'form-control', 'placeholder' => 'Enter end date', 'autocomplete' => 'off']) !!}
								<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
							</div>
						</div>
						<div class = "form-group col-xs-2">
							<label for = "Search"></label>
							{!! Form::submit('Search', ['id'=>'search', 'style' => 'margin-top: 5px;', 'class' => 'btn btn-primary form-control']) !!}
						</div>
					</div>
					{!! Form::close() !!}
				</div>
			</div>
		
		@if (count($coupons) > 0 && !(count($coupons) == 1 && $coupons->first()->order_total == null))
			<div class = "col-xs-12">
			<table class="table table-striped">
				<tr>
					<th>Coupon</th>
					<th class="data">Total</th>
					<th class="data">Orders</th>
					<th class="data">Avg Order</th>
					<th class="data">Shipping</th>
					<th class="data">Total - Shipping</th>
					<th class="data">Items</th>
					<th class="data">Shipped</th>
					<th class="data">% Shipped</th>
					<th class="data">Avg Days to Ship</th>
				</tr>
				
				@setvar($count=0)
				
				@foreach ($coupons as $coupon)
					
					@setvar($class = 'row_' . $count++)
					
					@setvar($item_sum = $coupon_items->where('coupon', $coupon->coupon)->first())
					
					<tr>
						<td width="100">{{ $coupon->coupon }}</td>
						<td class="data">${{ number_format($coupon->order_total,2) }}</td>
						<td class="data">
							@if ($coupon->coupon != 'No Coupon')
								<a href="{{ url(sprintf('/orders/list?search_for_first=%s&operator_first=starts_with&search_in_first=coupon&start_date=%s&end_date=%s', $coupon->coupon, $start_date, $end_date)) }}"
									target="_blank">{{ $coupon->order_count }}</a>
							@else
								{{ $coupon->order_count }}
							@endif
						</td>
						<td class="data">${{ number_format($coupon->order_total / $coupon->order_count, 2) }}</td>
						<td class="data">${{ number_format($coupon->shipping_total,2) }}</td>
						<td class="data">${{ number_format($coupon->order_total - $coupon->shipping_total,2) }}</td>
						<td class="data">
							@if ($coupon->coupon != 'No Coupon')
								<a href="{{ url(sprintf('/items?search_for_first=%s&search_in_first=coupon_id&start_date=%s&end_date=%s', $coupon->coupon, $start_date, $end_date)) }}"
									target="_blank">{{ number_format($item_sum->item_qty) }}</a>
							@else
								{{ number_format($item_sum->item_qty) }}
							@endif
						</td>
						<td class="data">
							@if ($coupon->coupon != 'No Coupon')
								<a href="{{ url(sprintf('/items?search_for_first=%s&search_in_first=coupon_id&start_date=%s&end_date=%s&status=2', $coupon->coupon, $start_date, $end_date)) }}"
									target="_blank">{{ number_format($item_sum->shipped) }}</a>
							@else
								{{ number_format($item_sum->shipped) }}
							@endif
						</td>
						<td class="data">
							@if ($item_sum->item_qty > 0 && $item_sum->shipped > 0)
								{{ number_format(($item_sum->shipped / $item_sum->item_qty) * 100, 1) }}%
							@else
								-
							@endif
						</td>
						<td class="data">
							@if ($item_sum->shipped > 0)
								{{ number_format($item_sum->ship_days / $item_sum->shipped, 1) }}
							@else
								-
							@endif
						</td>
					</tr>
					
				@endforeach
				
				<tr>
					<th width="100">Totals:</th>
					<th class="data">${{ number_format($coupons->sum('order_total'),2) }}</th>
					<th class="data">{{ $coupons->sum('order_count') }}</th>
					<th class="data">${{ number_format($coupons->sum('order_total') / $coupons->sum('order_count'), 2) }}</th>
					<th class="data">${{ number_format($coupons->sum('shipping_total'),2) }}</th>
					<th class="data">${{ number_format($coupons->sum('order_total') - $coupons->sum('shipping_total'),2) }}</th>
					<th class="data">{{ number_format($coupon_items->sum('item_qty')) }}</th>
					<th class="data">{{ number_format($coupon_items->sum('shipped')) }}</th>
					<th class="data">
						@if ($coupon_items->sum('item_qty') > 0)
							{{ number_format(($coupon_items->sum('shipped') / $coupon_items->sum('item_qty')) * 100, 1) }}%
						@else
							-
						@endif
					</th>
					<th class="data">-</th>
				</tr>
				
			</table>
			</div>
			
		@else
			<div class = "col-xs-12">
				<br>
				<div class = "alert alert-warning">
					No orders found.
				</div>
			</div>
		@endif
	
	</div>
	
<script>

	$('#store_ids').multiselect({
											includeSelectAllOption:true,
											numberDisplayed: 1,
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
</script>

</body>
</html>