<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Unbatchable Items</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">

	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>

	<style>
	
		td {
			word-wrap:break-word;
		}
		
		.divline {
			border-left:1px solid lightgray;
			border-right:1px solid lightgray;
			white-space: pre-wrap;
		}
		
	</style>
</head>
<body>
	@include('includes.header_menu')
	<div class = "container" style="min-width: 1550px; margin-left: 10px;">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "{{url('prod_report/unbatchable')}}">Unbatchable Items</a></li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		
		<h3 class = "page-header">Unbatchable Items</h3>
		
		@if(!empty($items) && count($items) > 0)
			
			<h4>{{ count($items) }} Items found </h4>
			<table class="table" id="items_table">
				@foreach($items as $item_array)
					@setvar($item = $item_array['item'])
					<tr>
						<td>
							@if (isset($item_array['hold']))
								{{ $item_array['hold'] }}
								<br>
							@endif
							@if (isset($item_array['parameter_option']))
								Child SKU doen not exist in 5P
								<br>
									{!! Form::open(['url' => url('/logistics/add_child_sku'), 'method' => 'get', 'target' => '_blank']) !!}
									{!! Form::hidden('id_catalog', $item->item_id) !!}
									{!! Form::hidden('parent_sku', $item->item_code) !!}
									{!! Form::hidden('child_sku', $item->child_sku) !!}
									{!! Form::submit('Add Child SKU', ['class' => 'btn btn-link']) !!}
									{!! Form::close() !!}
							@endif
							@if (isset($item_array['route']))
								<a href="{{ $item_array['route'] }}" target="_blank">Needs Route</a>
								<br>
							@endif
							@if (isset($item_array['stock_no']))
								<a href="{{ $item_array['stock_no'] }}" target="_blank">Needs Stock Number</a>
								<br>
							@endif
							@if (isset($item_array['qty_av']))
								<a href="{{ $item_array['qty_av'] }}" target="_blank">Insufficient Inventory</a>
								<br>
							@endif
						</td>
						
						<td width="150">
							<strong><u>
							{{ $item->order->customer->ship_full_name ? $item->order->customer->ship_full_name : $item->order->customer->bill_full_name }}
							</u></strong>
							<br>
							Item# {{($item->item_table_id)}}
							<br>
							<span data-toggle = "tooltip" data-placement = "top" 
										title = "5p# {{ $item->order_5p }} ">
										<a href = "{{ url("orders/details/".$item->order_5p) }}" target = "_blank">
												{{ $item->order->short_order }}</a>
							</span>
							@if ($item->store_id != '52053152')
								<br>
								@if ($item->store)
									{{ $item->store->store_name }}
								@else 
								 STORE: {{ $item->store_id }} NOT FOUND
								@endif
							@endif
							<br>
							Date: {{ substr($item->order->order_date, 0, 10) }}
						</td>						
						<td width="70">
								<img src = "{{$item->item_thumb}}" width = "70" height = "70" />
						</td>
						
						<td width="200">
							{{$item->item_description}}
						<br>
						SKU: {{$item->child_sku}}
						<br>
						QTY: {{$item->item_quantity}}
						</td>
						
						<td width="40%" class="divline">{{ \Monogram\Helper::jsonTransformer($item->item_option) }}</td>
						
						<td>
							Order Status: {!! $order_statuses[$item->order->order_status]  !!}<br>
							Item Status: {{ $item->item_status }}
							@if ($item->order->coupon_id != NULL)
								<br>
								{{ $item->order->coupon_id }}
							@endif
							@if ($item->order->promotion_id != NULL)
								<br>
								{{ $item->order->promotion_id }}
							@endif
						</td>
					</tr>
				@endforeach
			</table>
			
		@else
			<div class = "col-xs-12">
				<br>
				<div class = "alert alert-warning text-center">
					No items found.
				</div>
			</div>
		@endif
	</div>

</body>
</html>