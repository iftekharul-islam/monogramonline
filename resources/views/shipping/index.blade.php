@setvar($shipped = intval($request->get('shipped', 0)))
<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Shipment list</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap-multiselect.css">
	<link type = "text/css" rel = "stylesheet" href="/assets/css/pikaday.min.css">
	<link rel="stylesheet" href="/assets/css/chosen.min.css">
	
	<script type = "text/javascript" src = "https://cdn.jsdelivr.net/npm/jquery@3.2.1/dist/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap-multiselect.js"></script>
	<script type = "text/javascript" src = "/assets/js/moment.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/pikaday.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/chosen.jquery.min.js"></script>
</head>

<body>
	@include('includes.header_menu')
	<div class = "container"  style="min-width: 1400px;">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li>Shipment list</li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')

		<div class = "col-xs-12">
			{!! Form::open(['method' => 'get', 'url' => url('shipping'), 'id' => 'search-order']) !!}
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
			<br />

			<div class = "form-group col-xs-3">
				<label for = "start_date">Start date</label>
				<div class = 'input-group date' id = 'start_date_picker'>
					{!! Form::text('start_date', $request->get('start_date'), ['id'=>'start_datepicker', 'class' => 'form-control', 'placeholder' => 'Enter start date', 'autocomplete' => 'off']) !!}
					<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
				</div>
			</div>
			<div class = "form-group col-xs-3">
				<label for = "end_date">End date</label>
				<div class = 'input-group date' id = 'end_date_picker'>
					{!! Form::text('end_date', $request->get('end_date'), ['id'=>'end_datepicker', 'class' => 'form-control', 'placeholder' => 'Enter end date', 'autocomplete' => 'off']) !!}
					<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
				</div>
			</div>
			<div class = "form-group col-xs-2">
				<label for = "status">Store</label>
				<br>
				{!! Form::select('store_id[]', $stores, $request->get('store_id'), ['id'=>'store_id', 'class' => 'form-control', 'multiple' => 'multiple']) !!}
			</div>
				{!! Form::hidden('shipped', $request->get('shipped','0'), ['id'=>'shipped']) !!}
			<div class = "form-group col-xs-2">
				<label for = "" class = ""></label>
				{!! Form::submit('Search', ['id'=>'search', 'style' => 'margin-top: 2px;', 'class' => 'btn btn-primary form-control']) !!}
			</div>

			{!! Form::close() !!}
		</div>

		@if (!empty($label))
			@include('prints.includes.label')
		@endif

		@if(count($ships) > 0)
			<div class = "col-xs-12">
				<h4>
					Shipment list @if(count($ships) > 0 ) ({{ $ships->total() }} Shipments found / {{$ships->currentPage()}} of {{$ships->lastPage()}} pages) @endif
				</h4>
			</div>

			<table class="table">
				<tr>
					<th></th>
					<th>Order</th>
					<th>Shipment</th>
					<th>Ship Address</th>
					<th>Item</th>
					<th colspan="2">Product Information</th>
					<th> Raw</th>

				</tr>
				@foreach($ships as $ship) 
					@if (count($ship->items) > 0)
							@setvar($count = count($ship->items))
							@setvar($order = $ship->items->first()->order)
							<tr>
								<td rowspan = "{{ $count }}" style = "vertical-align: middle">
									<div class="btn-group col-xs-4">
										<button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" 
											aria-haspopup="true" aria-expanded="false">Action <span class="caret"></span>
										</button>
										<ul class="dropdown-menu">
											<li>
												{{-- @if ($ship->transaction_datetime > $yesterday) --}}
													{!! Form::open(['name' => 'reprint', 'method' => 'post', 'id' => 'reprint']) !!}
													{!! Form::hidden('unique_order_id', $ship->unique_order_id, ['id' => 'unique_order_id']) !!}
													{!! Form::hidden('search_for_first', $ship->shipping_id) !!}
													{!! Form::hidden('search_in_first', 'tracking_number') !!}
													{!! Form::submit('Reprint Label', ['id' => 'reprint_label', 'class' => 'btn btn-sm btn-default', 
																															'style'=>'border:none;margin-left:10px;']) !!}
													{!! Form::close() !!}
												{{-- @endif --}}
											</li>
											<li>
												{!! Form::open(['name' => 'returned', 'url' => '/ship_order/returned', 'method' => 'post', 'id' => 'returned',
																				 'onsubmit' => "return confirm('Are you sure you want to mark this package returned by carrier?');"]) !!}
												{!! Form::hidden('tracking_number', $ship->tracking_number) !!}
												{!! Form::submit('Package Returned', ['id' => 'returned', 'class' => 'btn btn-default btn-sm',
																															'style'=>'border:none;margin-left:10px;']) !!}
												{!! Form::close() !!}
											</li>
											<li>
												<a href = "{{ url('ship_order/void/' . $ship->id . '/' . $ship->order_number) }}" ,
														onclick="return confirm('Are you sure you want to void this shipment with the carrier?');"
														style="font-size:90%;">Void Shipment</a>
											</li>
										</ul>
									</div>
									<br>
									@if($ship->tracking_type == "DHL")
										@if(empty($ship->manifestStatus) && ($ship->tracking_type == "DHL"))
											No_Mainfest
										@else
											Yes_Mainfest
										@endif
									@endif
								</td>
								<td rowspan = "{{ $count }}" style = "vertical-align: middle">
									<a href = "{{url(sprintf("orders/details/%s", $order->id))}}"
									   target = "_blank">{{ $order->short_order }} </a> 
									<br>
									{{ $ship->unique_order_id }}
			 						<br>
									{{ $ship->transaction_datetime }}
								</td>
								<td rowspan = "{{ $count }}" style = "vertical-align: middle">
									<a href = "{{ \Monogram\Helper::getTrackingUrl($ship->shipping_id) }}" target="_blank">
										{{ $ship->shipping_id }}</a>
									<br>
									{{ $ship->mail_class }}
									@if ($ship->tracking_type != null)
										<br>
										Tracking Type: {{ $ship->tracking_type }}
									@endif
									@if ($ship->user)
										<br>
										Shipped By: {{$ship->user->username}}
									@endif
								</td>
								<td rowspan = "{{ $count }}" style = "vertical-align: middle">
									{{$order->customer->ship_full_name}}<br>
									@if (!empty($order->customer->ship_company_name))
										{{$order->customer->ship_company_name}}<br>
									@endif
									{{$order->customer->ship_address_1}}<br>
									@if (!empty($order->customer->ship_address_2))
										{{$order->customer->ship_address_2}}<br>
									@endif
									{{$order->customer->ship_city}}, {{$order->customer->ship_state}} 
									{{$order->customer->ship_zip}}<br>
									@if (substr($order->customer->ship_country, 0 ,2) != 'US')
										{{$order->customer->ship_country}}
									@endif
								</td>

							@setvar($row_count = 0)
							
							@foreach ($ship->items as $item)
							
								@setvar($row_count++)
								<td style = "vertical-align: middle">
									Item: {{ $item->id }}
									<br>
									QTY: {{ $item->item_quantity }}
									<br> 
									@if (isset($item->batch->batch_number))
										Batch: <a href = "{{ url(sprintf("/batches/details/%s", $item->batch_number)) }}"
																		   target = "_blank">{{ $item->batch_number }}</a>
									@endif
								</td>
								<td style = "vertical-align: middle"><img width="70" height="70"  src = "{{ $item->item_thumb }}" /></td>
								<td style = "vertical-align: middle" width="250">
									{{ $item->item_description }}
									<br>
									SKU: <a href = "{{ url(sprintf("/products?search_for=%s&search_in=product_model", $item->item_code)) }}"
									   target = "_blank">{{ $item->item_code }}
									</a>
									<br>
									@if (isset($item->batch->batch_number))
										{!! Form::open(['name' => 'reject-' . $item->id, 'url' => '/reject_item', 'method' => 'get', 'id' => 'reject-' . $item->id]) !!}
										{!! Form::hidden('item_id', $item->id, ['id' => 'item_id']) !!}
										{!! Form::hidden('origin', 'SL', ['id' => 'origin']) !!}
										{!! Form::button('Reject' , ['id'=>'reject-' . $item->item_quantity, 'class' => 'btn btn-xs btn-danger']) !!}
										{!! Form::close() !!}
									@endif
								</td>
									<td>
{{--										<pre>{{$label}}</pre>--}}
									</td>
								@if ($row_count < $count)
									</tr> <tr>
								@endif
						@endforeach
						</tr>	
					@endif
				@endforeach
			</table>

			<div class = "col-xs-12 text-center">
				{!! $ships->appends($request->all())->render() !!}
			</div>
		@elseif (count($request) > 0)
			<div class = "col-xs-12"> 
				<div class = "alert alert-warning">
					No Shipments Found.
				</div>
			</div>
		@endif
	</div>

		@include('/rejections/rejection_modal')
		
	<script type = "text/javascript">
		$(function() {

			$('#store_id').multiselect({includeSelectAllOption:true,
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
					field: document.getElementById('dhlManifest_datepicker'),
					format : "YYYY-MM-DD",
					minDate: new Date('2016-06-01'),
				});

		var picker = new Pikaday(
				{
					field: document.getElementById('dhlInternationalManifest_datepicker'),
					format : "YYYY-MM-DD",
					minDate: new Date('2016-06-01'),
				});

		@if (!empty($label))
			window.onload = function(){
				document.getElementById("sendLabelBtn").click();
			};
		@endif

	</script>
</body>
</html>