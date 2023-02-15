<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Sales Summary</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/pikaday.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/moment.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/pikaday.min.js"></script>

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
	<div class = "container" style="min-width: 800px;">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li>Sales Summary</li>
		</ol>

		<h3 class = "page-header">Sales Summary</h3>
		
		<div class = "col-xs-12">
			{!! Form::open(['method' => 'get']) !!}
				<div class = "form-group col-xs-3">
					<label>Group By:</label>
					{!! Form::select('grouping', $group_list , $grouping, ['id'=>'grouping', 'class' => 'form-control']) !!}
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
					<label for = "" class = ""></label>
					{!! Form::submit('Search', ['id'=>'search', 'style' => 'margin-top: 5px;', 'class' => 'btn btn-primary btn-sm form-control']) !!}
				</div>
			{!! Form::close() !!}
		</div>		
		
		@if (count($orders) > 0)
		
			<table class="table table-hover table-striped">
				<tr>
					<th></th>
					<th>{{ $headers['col1'] }}</th>
					<th>{{ $headers['col2'] }}</th>
					<th class="data">Order Count</th>
					<th class="data">Order Sum</th>
					<th class="data">Average Order</th>
					<th class="data">Shipping Total</th>
				</tr>
				
				@setvar($count=0)
				
				@foreach ($orders as $order)
					
					@setvar($class = 'row_' . $count++)
					
					<tr>
						<td>
								 <a onclick='TR_toggle("{{ $class }}");'
	 								 data-toggle = "tooltip" data-placement = "top"
	 								 title = "Show SKUs"><i class = 'glyphicon glyphicon-list-alt text-info'></i></a>
						</td>
						<td width="100">{{ $order->col1 }}</td>
						<td width="100">{{ $order->col2 }}</td>
						<td class="data">{{ $order->order_count }}</td>
						<td class="data">${{ number_format($order->orders_total,2) }}</td>
						<td class="data">${{ number_format($order->orders_total / $order->order_count, 2) }}</td>
						<td class="data">${{ number_format($order->shipping_total,2) }}</td>
					</tr>
					
						@if ($grouping == 'coupon')
							@setvar($details = $order_items->where('promotion', $order->col1)->where('coupon', $order->col2)->all())
						@elseif ($grouping == 'store')
							@setvar($details = $order_items->where('store_name', $order->col1)->all())
						@endif
						
						@foreach ($details as $detail)
								<tr class="{{ $class }} collapse out info">
										<td></td>
										<td>{{ number_format($detail->quantity) }}</td>
										<td><img src = "{{ $detail->item_thumb }}" height="50" /></a></td>
										<td>{{ $detail->item_code}}</td>
										<td colspan=3>{{ $detail->product_name}}</td>
								</tr>
						@endforeach
						
				@endforeach
				
				<tr>
					<th></th>
					<th width="100"></th>
					<th width="100">Totals:</th>
					<th class="data">{{ $orders->sum('order_count') }}</th>
					<th class="data">${{ number_format($orders->sum('orders_total'),2) }}</th>
					<th class="data">${{ number_format($orders->sum('orders_total') / $orders->sum('order_count'), 2) }}</th>
					<th class="data">${{ number_format($orders->sum('shipping_total'),2) }}</th>
				</tr>
				
			</table>
		@else
			<div class = "col-xs-12">
				<br>
				<div class = "alert alert-warning text-center">
					No orders found.
				</div>
			</div>
		@endif
	
	</div>
	
<script>
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
	
	function TR_toggle(className) {
		if($("." + className).hasClass("out")) {
        $("." + className).addClass("in");
        $("." + className).removeClass("out");
    } else {
        $("." + className).addClass("out");
        $("." + className).removeClass("in");
    }
	}
</script>

</body>
</html>