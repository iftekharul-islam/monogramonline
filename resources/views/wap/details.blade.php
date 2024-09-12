<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>WAP Bin {{ $bin->name }}</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	
	@if ($label != null)
		@include('prints.includes.label')
	@endif
	
	<style>
		.panel-default {
			font-size: 16px;
		}
		
		div.finished {
			background-color:lightgrey;
		}
	</style>
	
</head>
<body>
	@include('includes.header_menu')
	<div class = "container">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "{{url('/wap/index')}}">WAP</a></li>
			<li class = "active">Bin {{ $bin->name }}</li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')

		<div class = "col-xs-12">
			{!! Form::open(['url' => '/wap/details', 'method' => 'get', 'id' => 'barcode_form']) !!}
			<div class = "form-group col-xs-8">
			</div>
			<div class = "form-group col-xs-2">
				{!! Form::text('order_id', '', ['id'=>'barcode', 'class' => 'form-control', 'placeholder' => 'Scan Label']) !!}
			</div>
			<div class = "form-group col-xs-2">
				{!! Form::submit('Open Bin', ['id'=>'search', 'class' => 'btn btn-primary btn-sm form-control']) !!}
			</div>
			{!! Form::close() !!}
		</div>

		<form>
			<input type="hidden" value="" id="allItems" class="allItems">
		</form>
	@if ($order)
		<h4 class="page-header">
			BIN <strong>{{ $bin->name }}</strong>
			&nbsp;&nbsp; - &nbsp;&nbsp;
			Order <a href = "{{ url(sprintf('/orders/details/%d',$order->id )) }}">{{ $order->short_order }}</a>
			<small class="pull-right"> Order Date: {{ substr($order->order_date, 0 , 10) }}</small>
		</h4>
		<div class = "col-xs-12">
			<div class="col-xs-1"></div>
			<div class="col-xs-10">
				@if($order->order_status == 4 || $order->order_status == 11)
					@if ($label == null || $show_ship == '1')
						@setvar($origin = 'WAP')
						<div class="col-xs-12 col-sm-12 col-md-12 panel panel-success">

							<div class="panel-body">
								<div class="col-xs-12 col-sm-12 col-md-8">
									@if(count($rates))
										{!! Form::open(['url' => "/shipping/ship_final", 'method' => 'get']) !!}
										<div style="max-height: 200px; overflow: auto; padding: 5px" class="table-bordered">
										<table class="table table-responsive table-bordered">
											<thead>
											<tr class="bg-success">
												<th></th>
												<th>Service</th>
												<th>Carrier</th>
												<th>Rate</th>
												<th>Days</th>
											</tr>
											</thead>
											<tbody>
											@foreach ($rates as $rate)
												<tr>
													<td>
														{!! Form::radio('selected_rate', $rate['carrier_account_id'], false, ['id' => 'selected_rate_' . $rate['carrier_account_id']]) !!}
													</td>
													<td>{{ $rate['service'] }}</td>
													<td>{{ $rate['carrier'] }}</td>
													<td>$ {{ $rate['rate'] }}</td>
													<td>{{ $rate['delivery_days'] }}</td>
												</tr>
											@endforeach
											</tbody>
										</table>
										</div>
										@if(count($rates))
											{!! Form::button('Select', ['class' => 'btn btn-primary', 'id' => 'focus-btn', 'style' => 'margin-top:5px;', 'onclick' => 'this.form.submit();']) !!}
										@endif
										{!! Form::close() !!}
									@else
									<strong>
										<u>Shipping address </u>
									</strong>
									<br>
									{{ $order->customer->ship_full_name}}<br>
									@if (!empty($order->customer->ship_company_name))
										{{ $order->customer->ship_company_name}}<br>
									@endif
									{{ $order->customer->ship_address_1}}<br>
									@if (!empty($order->customer->ship_address_2))
										{{ $order->customer->ship_address_2}}<br>
									@endif
									{{ $order->customer->ship_city}}, {{ $order->customer->ship_state}} {{ $order->customer->ship_zip}}<br>
									@if (substr($order->customer->ship_country, 0, 2) != 'US')
										{{ $order->customer->ship_country}}
									@endif
									@endif

								</div>
								<div class="col-xs-12 col-sm-12 col-md-4" style="text-align:right;">
									{!! Form::open(['url' => "/wap/details", 'method' => 'get']) !!}
									{!! Form::hidden('action', 'find_rate') !!}
									{!! Form::hidden('bin', $bin->id) !!}
									{!! Form::hidden('name', $order->customer->ship_full_name) !!}
									{!! Form::hidden('street1', $order->customer->ship_address_1) !!}
									{!! Form::hidden('city', $order->customer->ship_city) !!}
									{!! Form::hidden('state', $order->customer->ship_state) !!}
									{!! Form::hidden('zip', $order->customer->ship_zip) !!}
									{!! Form::hidden('country', $order->customer->ship_country) !!}
									{!! Form::hidden('phone', $order->customer->ship_phone) !!}
									{!! Form::hidden('email', $order->customer->ship_email ?  $order->customer->ship_email : $order->customer->bill_email) !!}
									<table class="table table-condensed borderless" id="packages">
										<tr>
											<td>{!! Form::label('*Weight:', '', ['style' => 'color:red;']) !!}</td>
											<td>{!! Form::number('pounds', request()->input('pounds') ? request()->input('pounds') : '0', ['id' => 'pounds', 'style' => 'width:50px', 'min' => '0', 'required' => 'required']) !!}</td>
											<td>lbs</td>
											<td>{!! Form::number('ounces', request()->input('ounces') ? request()->input('ounces') : '0', ['id' => 'ounces', 'style' => 'width:50px', 'min' => '0', 'required' => 'required']) !!}</td>
											<td>ozs</td>
										</tr>
										<tr>
											<td>{!! Form::label('Others:', '') !!}</td>
											<td>{!! Form::number('length', request()->input('length') ? request()->input('length') : '', ['id' => 'length', 'style' => 'width:50px', 'min' => '0']) !!}</td>
											<td>Length</td>
											<td>{!! Form::number('width', request()->input('width') ? request()->input('width') : '', ['id' => 'width', 'style' => 'width:50px', 'min' => '0']) !!}</td>
											<td>Width</td>
										</tr>
										<tr>
											<td></td>
											<td>{!! Form::number('height', request()->input('height') ? request()->input('height') : '', ['id' => 'length', 'style' => 'width:50px', 'min' => '0']) !!}</td>
											<td>Height</td>
										</tr>
									</table>
									<br>
									{!! Form::submit('Compare price from GA', ['class' => 'btn btn-success', 'name' => 'submit', 'style' => 'margin-bottom:2px']) !!}
									{!! Form::submit('Compare price from NY', ['class' => 'btn btn-warning', 'name' => 'submit']) !!}
									{!! Form::close() !!}
								</div>
							</div>
						</div>
					@endif
				@elseif($label != null)
					<input type="button" value="Reprint Shipping Label" class="btn btn-lg" onclick="sendLabel();">
					<br><br>
				@endif
			</div>
		</div>
		<h1>OR</h1>
		<div class = "col-xs-12">
				<div class="col-xs-1"></div>
				<div class="col-xs-9"> 
					@if($order->order_status == 4 || $order->order_status == 11)
						@if ($label == null || $show_ship == '1')
							@setvar($origin = 'WAP')
							@include('shipping.ship_panel')
						@endif
					@elseif($label != null)
						<input type="button" value="Reprint Shipping Label" class="btn btn-lg" onclick="sendLabel();">
						<br><br>
					@endif
				</div>					
		</div>
			
			@if(isset($order->items) && count($order->items) > 0)

				<script type="application/javascript">

					setInterval(function() {
						var items = []
						$('input[type=checkbox]:checked').each(function () {

							if(this.checked){
								items.push($(this).val())
							}

						});

						updateItems(items);
					}, 800);


					function updateItems(data) {
						// document.getElementById("selected-items-json").value = JSON.stringify(data)

						document.getElementById("allItems").value = JSON.stringify(data)

						if(data.length > 0) {
							console.log("Updated selected items to " + JSON.stringify(data))
						}
					}

				</script>

				@foreach($order->items->sortBy('item_status') as $item)
					
						<div class="col-xs-12 panel panel-default {{$item->id}}" item-id='{{$item->id}}'>
							<div class="panel-body">
								<div class="col-xs-12 col-sm-12 col-md-8">
									<h4>
										<a href="{{ $item->item_url }}" 
										target = "_blank">{{ $item->item_description }}</a>
										<span>{{ $item->item_status }}</span>
									</h4>
								</div>
								
								<div class="col-xs-12 col-sm-6 col-md-2" align="right">
										@if ($item->item_status == 'wap')
											<a href="{{url(sprintf('/wap/reprint?bin_id=%d&item_id=%d', $bin->id, $item->id))}}"
													class="btn btn-default btn-sm">Reprint WAP Label</a>
										@endif
								</div>
								
								<div class="col-xs-12 col-sm-6 col-md-2" align="right">
									@if ($item->item_status == 'wap' && $item->batch)
										{!! Form::open(['name' => 'reject-' . $item->id, 'url' => '/reject_item', 'method' => 'get', 'id' => 'reject-' . $item->id]) !!}
										{!! Form::hidden('item_id', $item->id, ['id' => 'item_id']) !!}
										{!! Form::hidden('bin_id', $bin->id, ['id' => 'bin_id']) !!}
										{!! Form::hidden('origin', 'WP', ['id' => 'origin']) !!}
										{!! Form::button('Reject from WAP' , ['id'=>'reject-' . $item->item_quantity, 'class' => 'btn btn-sm btn-danger']) !!}
										{!! Form::checkbox('s', $item->id, true) !!}
										{!! Form::close() !!}
									@elseif($item->item_status == 'rejected')
										<strong>REJECTED
										@if (count($item->rejections) > 1)
											 {{ count($item->rejections) }} TIMES
										@endif
										</strong>
									@else 
										<strong>{{ strtoupper($item->item_status) }}</strong>
									@endif
								</div>
								
								<div class="col-xs-12">
									@if ($item->wap_item && $item->item_status == 'wap')
										<small>
									 	Added to Bin {{ $item->wap_item->created_at }} 
								 		</small>
									@elseif ($item->item_status == 'wap')
										WAP ITEM NOT FOUND
									@endif
									<hr style="margin-top:0;">
								</div>
								
								<div class="col-xs-12 col-sm-12 col-md-3">
									<a href = "{{ $item->item_url }}" target = "_blank">
									<img src = "{{ $item->item_thumb }}" height="200"></a>
								</div>
								
								<div class="col-xs-12 col-sm-12 col-md-6">
									{{ $item->child_sku }}
									<br>
									Item: {{ $item->id }}
									
									<br><br>
									
									@if ($item->item_quantity > 1)
										<strong style="font-size: 125%;">QTY: {{ $item->item_quantity }}</strong>
										<br><br>
									@endif
									
									<ul>
										{!! $item_options[$item->id] !!}
									</ul>
									
								</div>

								<div class="col-xs-12 col-sm-12 col-md-3">
									@if(!empty($item->batch_number))
										<a href = "/batches/details/{{ $item->batch_number }}" target = "_blank">
											Batch {{ $item->batch_number }}</a>
										<br>
									@endif
									@if($item->item_status == 'wap' && isset($thumbs[$item->id][0]))
										<img src = "{{ $thumbs[$item->id][0] }}" width="{{ $thumbs[$item->id][1] }}" height="{{ $thumbs[$item->id][2] }}">
									@elseif($item->item_status == 'production' && $item->batch_number != '0')
										@if ($item->batch && $item->batch->station)
											{{ $item->batch->station->station_description }}
											<br>
										@endif
										@if ($item->batch)
											Last Scan: {{ $item->batch->change_date }}
										@endif
									@elseif($item->item_status == 'production' && $item->batch_number == '0')
										Unbatched
									@elseif($item->item_status == 'rejected')
										@foreach ($item->rejections as $rejection)
											<br><br>
											<small>
												Rejected {{ $rejection->created_at }}
												<br>
												@if ($rejection->rejection_reason_info)
													{{ $rejection->rejection_reason_info->rejection_message }}
												@endif
											</small>
										@endforeach
									@elseif($item->item_status == 'shipped')
										@if ($item->shipInfo)
											{{ $item->shipInfo->mail_class }}
											<br>
											{{ $item->shipInfo->shipping_id }}
										@else
											SHIPMENT NOT FOUND
										@endif
									@endif
								</div>
							</div>
						</div>

				@endforeach
					
			@else
				<br>
				<div class = "alert alert-warning">No Items in Bin.</div>
			@endif
			
		</div>
	@else 
		<div class = "alert alert-warning">Bin Empty</div>
	@endif
	</div>

	@include('/rejections/rejection_modal')
	
	@include('/shipping/shipval_modal')

	<script type = "text/javascript">
	
		$(function() {
				// Focus on load
				@if($label != null)
				 	$('#barcode').focus();
				@else
					$('#single_batch').focus();
				@endif
		});
		
		$(document).ready(function () {
			$('div').click(function () {
					var item = '.' + $(this).attr('item-id'); 
					if (item != '.') {
						if($(item).hasClass('finished')) {
								$(item).removeClass('finished');
						} else {
								$(item).addClass('finished');
						}
					}
			});
		});
		
	</script>
</body>
</html>