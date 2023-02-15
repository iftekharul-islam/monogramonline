<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Order Items</title>
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
	<div class = "container" style="min-width: 1250px;">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "{{url('/report/items')}}">Order Items</a></li>
		</ol>

		<h3 class = "page-header">Order Items
			<div class="pull-right"><small><a href="{{ url('report/order_date')}}" target="_blank">old version</a></small></div>
		</h3>
		
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
						<div class = "form-group col-xs-2">
							<label for = "start_date">Start Order date</label>
							<div class = 'input-group date' id = 'start_date_picker'>
								{!! Form::text('start_date', $start_date, ['id'=>'start_datepicker', 'class' => 'form-control', 'placeholder' => 'Enter start date', 'autocomplete' => 'off']) !!}
								<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
							</div>
						</div>
						<div class = "form-group col-xs-2">
							<label for = "end_date">End Order date</label>
							<div class = 'input-group date' id = 'end_date_picker'>
								{!! Form::text('end_date', $end_date, ['id'=>'end_datepicker', 'class' => 'form-control', 'placeholder' => 'Enter end date', 'autocomplete' => 'off']) !!}
								<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
							</div>
						</div>
						<div class = "form-group col-xs-2">
							<label for = "end_date">Results</label>
							{!! Form::select('limit', [10=>10, 25=>25, 50=>50, 100=>100, 250=>250], $limit ?? 25, ['id'=>'limit', 'class' => 'form-control']) !!}
						</div>
						<div class = "form-group col-xs-2">
							<label for = "Search"></label>
							{!! Form::submit('Search', ['id'=>'search', 'style' => 'margin-top: 5px;', 'class' => 'btn btn-primary form-control']) !!}
						</div>
					</div>
					{!! Form::close() !!}
				</div>
			</div>
		
		@if (count($items) > 0)
			<div class = "col-xs-12">
			<table class="table">
				<tr bgcolor="#f7eaea">
					<th colspan=4>Product</th>
					<th class="data">Items</th>
					<th class="data">Shipped</th>
					<th class="data">% Shipped</th>
					<th class="data">Avg Days to Ship</th>
					<th class="data">Max Days to Ship</th>
					<th class="data">Rejects</th>
				</tr>
				
				@foreach ($items as $item)
					
					@setvar($item_rejects = $rejects->where('item_code', $item->item_code)->first())
					
					<tr>
						<td width=50 height=40>
							<img src="{{ $item->product_thumb }}" height=40>
						</td>
						<td>
							{{ $item->item_code }}
						</td>
						<td>
							{!! \App\Task::widget('App\Product', $item->product_id, null, 12); !!}
						</td>
						<td>
							{{ $item->product_name }}
						</td>
						<td class="data">
							<a href="{{ url(sprintf('/items?search_for_first=%s&search_in_first=item_code&store=%s&start_date=%s&end_date=%s', $item->item_code, $store_str , $start_date, $end_date)) }}"
									target="_blank">{{ number_format($item->item_qty) }}</a>
						</td>
						<td class="data">
							<a href="{{ url(sprintf('/items?search_for_first=%s&search_in_first=item_code&store=%s&start_date=%s&end_date=%s&status=2', $item->item_code, $store_str, $start_date, $end_date)) }}"
									target="_blank">{{ number_format($item->shipped) }}</a>
						</td>
						<td class="data">
							@if ($item->item_qty > 0 && $item->shipped > 0)
								{{ number_format(($item->shipped / $item->item_qty) * 100, 1) }}%
							@else
								-
							@endif
						</td>
						<td class="data">
							@if ($item->shipped > 0)
								{{ number_format($item->ship_days / $item->shipped, 1) }}
							@else
								-
							@endif
						</td>
						<td class="data">
							{{ number_format($item->maxdays, 1) }}
						</td>
						<td class="data">
							@if ($item_rejects && $item_rejects->count > 0)
							<a href="{{ url(sprintf('/report/rejects?item_code=%s&start_date=%s&end_date=%s&store=%s', $item->item_code, $start_date, $end_date, $store_str)) }}"
									target="_blank">{{ number_format($item_rejects->count) }}</a>
							@else
								-
							@endif
						</td>
					</tr>
					
				@endforeach
				
				<tr bgcolor="#f7eaea">
					<th colspan=10></th>
					<!-- <th colspan=2></th>
					<th width="100">Totals:</th>
					<th class="data">{{ number_format($items->sum('item_qty')) }}</th>
					<th class="data">{{ number_format($items->sum('shipped')) }}</th>
					<th class="data">
						@if ($items->sum('item_qty') > 0)
							{{ number_format(($items->sum('shipped') / $items->sum('item_qty')) * 100, 1) }}%
						@else
							-
						@endif
					</th>
					<th class="data">-</th> -->
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