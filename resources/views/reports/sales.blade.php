<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Sales Summary</title>
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
			<li><a href = "{{url('/report/sales')}}">Sales Summary</a></li>
		</ol>

		<h3 class = "page-header">Sales Summary</h3>
		
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
		
		@if (count($sales) > 0)
			<div class = "col-xs-12">
			<table class="table">
				<tr bgcolor="#E2F1F0">
					<th></th>
					<th width=275>Store</th>
					<th class="data">Total</th>
					<th class="data"></th>
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
				
				@foreach ($sales as $sale)
					
					@setvar($class = 'row_' . $count++)
					
					@setvar($store_items = $sale_items->where('store_id', $sale->store_id))
					
					<tr>
						<td width=25>
							<a onclick='TR_toggle("{{ $class }}");'
							 data-toggle = "tooltip" data-placement = "top"
							 title = "Show SKUs"><i class = 'glyphicon glyphicon-chevron-down'></i></a>
						</td>
						<td width="100">
							@if (isset($stores[$sale->store_id]))
								{{ $stores[$sale->store_id] }}
							@else 
								Store not found
							@endif
						</td>
						<td class="data">${{ number_format($sale->order_total,2) }}</td>
						<td class="data">
							@if ($total_amount > 0)
								( {{ number_format(($sale->order_total / $total_amount) * 100, 1) }}% )
							@else
								-
							@endif
						</td>
						<td class="data">
							<a href="{{ url(sprintf('/orders/list?store=%s&start_date=%s&end_date=%s', $sale->store_id, $start_date, $end_date)) }}"
									target="_blank">{{ number_format($sale->order_count) }}</a>
						</td>
						<td class="data">
							@if ($sale->order_count > 0)
								${{ number_format($sale->order_total / $sale->order_count, 2) }}
							@endif
						</td>
						<td class="data">${{ number_format($sale->shipping_total,2) }}</td>
						<td class="data">${{ number_format($sale->order_total - $sale->shipping_total,2) }}</td>
						<td class="data">
							<a href="{{ url(sprintf('/items?store=%s&start_date=%s&end_date=%s', $sale->store_id, $start_date, $end_date)) }}"
									target="_blank">{{ number_format($store_items->sum('item_qty')) }}</a>
						</td>
						<td class="data">
							<a href="{{ url(sprintf('/items?store=%s&start_date=%s&end_date=%s&status=2', $sale->store_id, $start_date, $end_date)) }}"
									target="_blank">{{ number_format($store_items->sum('shipped')) }}</a>
						</td>
						<td class="data">
							@if ($store_items->sum('item_qty') > 0 && $store_items->sum('shipped') > 0)
								{{ number_format(($store_items->sum('shipped') / $store_items->sum('item_qty')) * 100, 1) }}%
							@else
								-
							@endif
						</td>
						<td class="data">
							@if ($store_items->sum('shipped') > 0)
								{{ number_format($store_items->sum('ship_days') / $store_items->sum('shipped'), 1) }}
							@else
								-
							@endif
						</td>
					</tr>
					
					@foreach ($store_items as $section) 
						@if ($section->header == null)
							{{!! dd($store_items)}}
						@endif
						<tr class="{{ $class }} secondary collapse out" bgcolor="WhiteSmoke">
							<td colspan=5>
							</td>
							<td colspan=2>
								<strong>{{ $section->header }}</strong>
							</td>
							<td>
								@if ($store_items->sum('item_qty') > 0)
									( {{ number_format(($section->item_qty / $store_items->sum('item_qty')) * 100, 1) }}% )
								@else
									-
								@endif
							</td>
							<td class="data">
								@if ($section->item_qty < 1)
									{{ number_format($section->item_qty) }}
								@elseif ($section->header == 'Unbatched')
									<a href="{{ url(sprintf('/items?search_for_first=zero&search_in_first=batch&store=%s&start_date=%s&end_date=%s&section=%s', 	
																					$sale->store_id, $start_date, $end_date, $section->section_id)) }}"
										target="_blank">{{ number_format($section->item_qty) }}</a>
								@else
									<a href="{{ url(sprintf('/items?store=%s&start_date=%s&end_date=%s&section=%s', $sale->store_id, $start_date, $end_date, $section->section_id)) }}"
											target="_blank">{{ number_format($section->item_qty) }}</a>
								@endif
							</td>
							<td class="data">
								@if ($section->shipped < 1)
									{{ number_format($section->shipped) }}
								@elseif ($section->header == 'Unbatched')
									<a href="{{ url(sprintf('/items?search_for_first=zero&search_in_first=batch&store=%s&start_date=%s&end_date=%s&section=%s&status=2', 	
																					$sale->store_id, $start_date, $end_date, $section->section_id)) }}"
										target="_blank">{{ number_format($section->shipped) }}</a>
								@else
									<a href="{{ url(sprintf('/items?store=%s&start_date=%s&end_date=%s&section=%s&status=2', 
																					$sale->store_id, $start_date, $end_date, $section->section_id)) }}"
											target="_blank">{{ number_format($section->shipped) }}</a>
								@endif
							</td>
							<td class="data">
								@if ($section->item_qty > 0 && $section->shipped > 0)
									{{ number_format(($section->shipped / $section->item_qty) * 100, 1) }}%
								@else
									-
								@endif
							</td>
							<td class="data">
								@if ($section->shipped > 0)
									{{ number_format($section->ship_days / $section->shipped, 1) }}
								@else
									-
								@endif
							</td>
						</tr>
					@endforeach
					
				@endforeach
				
				<tr bgcolor="#E2F1F0">
					<th></th>
					<th width="100">Totals:</th>
					<th class="data">${{ number_format($sales->sum('order_total'),2) }}</th>
					<th class="data"></th>
					<th class="data">{{ number_format($sales->sum('order_count')) }}</th>
					<th class="data">
						@if ($sales->sum('order_count') > 0)
							${{ number_format($sales->sum('order_total') / $sales->sum('order_count'), 2) }}
						@else
							-
						@endif
					</th>
					<th class="data">${{ number_format($sales->sum('shipping_total'),2) }}</th>
					<th class="data">${{ number_format($sales->sum('order_total') - $sales->sum('shipping_total'),2) }}</th>
					<th class="data">{{ number_format($sale_items->sum('item_qty')) }}</th>
					<th class="data">{{ number_format($sale_items->sum('shipped')) }}</th>
					<th class="data">
						@if ($sale_items->sum('item_qty') > 0)
							{{ number_format(($sale_items->sum('shipped') / $sale_items->sum('item_qty')) * 100, 1) }}%
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