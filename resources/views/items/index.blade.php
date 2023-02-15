<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Items list</title>
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
	<div class = "container" style="min-width: 1400px;">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "{{url('items')}}">Items list</a></li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		<div class = "col-xs-12">
			{!! Form::open(['method' => 'get', 'url' => url('items'), 'id' => 'search-order']) !!}
			<div class="row">
				<div class = "form-group col-xs-3">
					<label for = "search_for_first">Search for 1</label>
					{!! Form::text('search_for_first', $request->get('search_for_first'), ['id'=>'search_for_first', 'class' => 'form-control', 'placeholder' => 'Comma delimited']) !!}
				</div>
				<div class = "form-group col-xs-3">
					<label for = "search_in_first">Search in 1</label>
					{!! Form::select('search_in_first', $search_in, $request->get('search_in_first'), ['id'=>'search_in_first', 'class' => 'form-control']) !!}
				</div>
				<div class = "form-group col-xs-3">
					<label for = "search_for_second">Search for 2</label>
					{!! Form::text('search_for_second', $request->get('search_for_second'), ['id'=>'search_for_second', 'class' => 'form-control', 'placeholder' => 'Comma delimited']) !!}
				</div>
				<div class = "form-group col-xs-3">
					<label for = "search_in_first">Search in 2</label>
					{!! Form::select('search_in_second', $search_in, $request->get('search_in_second'), ['id'=>'search_in_second', 'class' => 'form-control']) !!}
				</div>
			</div>
			<div class="row">
				<div class = "form-group col-xs-2">
					<label for = "start_date">Start date</label>
					<div class = 'input-group date'>
						{!! Form::text('start_date', $request->get('start_date'), ['id'=>'start_datepicker', 'class' => 'form-control', 'placeholder' => 'Enter start date', 'autocomplete' => 'off']) !!}
						<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
					</div>
				</div>
				<div class = "form-group col-xs-2">
					<label for = "end_date">End date</label>
					<div class = 'input-group date'>
						{!! Form::text('end_date', $request->get('end_date'), ['id'=>'end_datepicker', 'class' => 'form-control', 'placeholder' => 'Enter end date', 'autocomplete' => 'off']) !!}
						<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
					</div>
				</div>
				<div class = "form-group col-xs-2">
					<label for = "tracking_date">Shipping date</label>
					<div class = 'input-group date'>
						{!! Form::text('tracking_date', $request->get('tracking_date'), ['id'=>'tracking_datepicker', 'class' => 'form-control', 'placeholder' => 'Enter shipping date', 'autocomplete' => 'off']) !!}
						<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
					</div>
				</div>
				<div class = "form-group col-xs-2">
					<label for = "status">Item Status</label>
					<br>
					{!! Form::select('status[]', $statuses, $request->get('status'), ['id'=>'status', 'multiple' => 'multiple', 'class' => 'form-control']) !!}
				</div>
				<div class = "form-group col-xs-2">
					<label for = "status">Store</label>
					<br>
					{!! Form::select('store[]', $stores, $store, ['id'=>'store', 'multiple' => 'multiple', 'class' => 'form-control']) !!}
				</div>
			</div>
			<div class="row">
				<div class = "form-group col-xs-2">
					{!! Form::submit('Search', ['id'=>'search', 'class' => 'btn btn-primary form-control']) !!}
				</div>
				<div class = "form-group col-xs-4">
				</div>
				<div class = "form-group col-xs-4">
					<a href = "{{url('/logistics/sku_list?sku_status=RT&active=2')}}">
						 	{{$unassignedProductCount}} products need routes /
							{{ $unassignedOrderCount }} unbatched items need routes</a>
					<a href = "{{url('/prod_config/batch_routes?unassigned=1')}}">{{$emptyStationsCount}}</a>
				</div>
			{!! Form::close() !!}
			
				<div class = "form-group col-xs-2">
					@if(!empty($items) && count($items) > 0)
						{!! Form::open(['url' => url('/exports/items'), 'method' => 'post']) !!}
							@setvar($data1 = serialize($request->get('store')))
							{!! Form::hidden('store', $data1) !!}
							@setvar($data = serialize($request->get('status')))
							{!! Form::hidden('status', $data) !!}
							{!! Form::hidden('search_in_first', $request->get('search_in_first')) !!}
							{!! Form::hidden('search_for_first', $request->get('search_for_first')) !!}
							{!! Form::hidden('search_in_second', $request->get('search_in_second')) !!}
							{!! Form::hidden('search_for_second', $request->get('search_for_second')) !!}
							{!! Form::hidden('tracking_date', $request->get('tracking_date')) !!}
							{!! Form::hidden('start_date', $request->get('start_date')) !!}
							{!! Form::hidden('end_date', $request->get('end_date')) !!}
							{!! Form::hidden('scan_start_date', $request->get('scan_start_date')) !!}
							{!! Form::hidden('scan_end_date', $request->get('scan_end_date')) !!}
							{!! Form::hidden('unbatched', $request->get('unbatched')) !!}
							{!! Form::hidden('count', $items->total()) !!}
							{!! Form::submit('Create CSV Export', ['class' => 'btn btn-success form-control']) !!}
						{!! Form::close() !!}
					@endif
				</div>
				
			</div>
		</div>
		
		@if(!empty($items) && count($items) > 0)
			
			<h4>{{ $item_sum->count }} Items found  <small>(Total Quantity: {{ $item_sum->sum }})</small></h4>
			<table class="table" id="items_table">
				@foreach($items as $item)
					<tr data-id = "{{$item->id}}">
						<td width="150">
							<strong><u>
							@if ($item->order && $item->order->customer)
								{{ !empty($item->order->customer->ship_full_name) ? $item->order->customer->ship_full_name : $item->order->customer->bill_full_name }}
							@else
								CUSTOMER NOT FOUND
							@endif
							</u></strong>
							<br>
							Item# {{($item->id)}}
							<br>
							<span data-toggle = "tooltip" data-placement = "top" 
										title = "5p# {{ $item->order_5p }} ">
										<a href = "{{ url("orders/details/".$item->order_5p) }}" target = "_blank">
											@if ($item->order)
												{{ $item->order->short_order }}
											@else
												ORDER NOT FOUND
											@endif
										</a>
							</span>
							<br>
							@if ($item->order)
								Date: {{ substr($item->order->order_date, 0, 10) }}
							@endif
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
						
						<td width=30% class="divline">{{ \Monogram\Helper::optionTransformer($item->item_option,1,0,0,1,0) }}</td>
						
						<td>
							@if ($item->order)
								Order Status: {!! $order_statuses[$item->order->order_status]  !!}<br>
							@endif
							Item Status: {{ $item->item_status }}
							@if ($item->order && $item->order->coupon_id != NULL)
								<br>
								{{ $item->order->coupon_id }}
							@endif
							@if ($item->order && $item->order->promotion_id != NULL)
								<br>
								{{ $item->order->promotion_id }}
							@endif
						</td>
						
						@if ($item->item_status == 'shipped')
							<td>
								@if($item->shipInfo)
									<a href = "{{ \Monogram\Helper::getTrackingUrl($item->shipInfo->shipping_id) }}" target = "_blank">{{ $item->shipInfo->shipping_id }}</a>
									<br>
									<a href = "shipping?search_for_first={{ $item->shipInfo->shipping_id }}&search_in_first=tracking_number" target = "_blank">{{ $item->shipInfo->transaction_datetime }}</a>
								@elseif ($item->tracking_number)
									<a href = "{{ \Monogram\Helper::getTrackingUrl($item->tracking_number) }}" target = "_blank">{{ $item->tracking_number }}</a>
								@endif
								<br>
								@if ($item->order && $item->order->customer)
									Ship State: {{$item->order->customer->ship_state}}
								@endif
							</td>
						
						@elseif ($item->item_status == 'wap')
							<td>
								@if($item->wap_item)
									WAP Bin <a href = "wap/details?bin={{ $item->wap_item->bin->id }}" target = "_blank">{{ $item->wap_item->bin->name }}</a>
									<br>
									Added:
									<br>
									{{ $item->wap_item->created_at }}
								@else
									WAP - Bin not found
								@endif
							</td>
							
						@elseif ($item->batch_number != '0')
							<td>
								@if (!empty($item->batch))
									<span data-toggle = "tooltip" data-placement = "top" 
											title = "Batch: {{$item->batch_number}}
											Status: {{$item->batch->status ?: 'N/A'}}
											Last Scan: {{$item->batch->change_date ?: 'N/A'}}">
								@endif
								
								@if($item->batch_number)
									Batch # 	 									
									<a href = "{{ url(sprintf('/batches/details/%s', $item->batch_number)) }}"
									   target = "_blank">{{$item->batch_number}}</a>
									
								@endif
							
							<br>
							
							 @if (!empty($item->batch->station))
								Station: 
								{{$item->batch->station->station_name ?: 'N/A'}} {{$item->batch->station->station_description ?: 'N/A'}}
							 @endif
							 
							 
									<br> ***Last Scan: <br>
									{{$item->batch->change_date ?: 'N/A'}} 
						
								
							 
							 @if (!empty($item->batch))
								 </span>
							 @endif
							</td>
						@else
							<td>
								Unbatched
							</td>
						@endif
							<!-- <td>
								{!! \App\Task::widget('App\Item', $item->id); !!}
							</td> -->
					</tr>
				@endforeach
			</table>
			
			<div class = "col-xs-12 text-center">
				{!! $items->appends($request->all())->render() !!}
			</div>
			
		@else
			<div class = "col-xs-12">
				<br>
				<div class = "alert alert-warning text-center">
					No items found.
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
			field: document.getElementById('tracking_datepicker'),
			format : "YYYY-MM-DD",
			minDate: new Date('2016-06-01'),    
	});

	</script>

</body>
</html>