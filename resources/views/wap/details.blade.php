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
									@if($item->item_status == 'wap' && isset($thumbs[$item->id][0]))
											<img src = "{{ $thumbs[$item->id][0] }}" width="{{ $thumbs[$item->id][1] }}" height="{{ $thumbs[$item->id][2] }}">
									@elseif($item->item_status == 'production' && $item->batch_number != '0')
											<a href = "/batches/details/{{ $item->batch_number }}" target = "_blank">
											Batch {{ $item->batch_number }}</a>
											<br>
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
										<a href = "/batches/details/{{ $item->batch_number }}" target = "_blank">
										Batch {{ $item->batch_number }}</a>
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
									@elseif($item->item_status == 'back order')
										<a href = "/batches/details/{{ $item->batch_number }}" target = "_blank">
										Batch {{ $item->batch_number }}</a>
										<br>
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