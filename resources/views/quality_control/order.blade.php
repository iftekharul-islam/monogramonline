<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>QC {{ $batch->batch_number }} - {{ $order->short_order }}</title>
	
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	
	@if ($label != null)
		@include('prints.includes.label')
	@endif
	
	<style>
		.panel-default {
			font-size: 17px;
		}
		
		div.finished {
			background-color:lightgrey;
		}
	</style>
	<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
	
	@include('includes.header_menu')
	<div class = "container">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "{{url('/shipping/qc_station')}}">Quality Control</a></li>
			<li class = "active">
				<a href = "{{ url(sprintf('/shipping/qc_batch?id=%s&batch_number=%s', $id, $batch_number)) }}">Batch {{ $batch_number }}</a>
			</li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		
		<h3>
		<div class="col-xs-12 col-sm-6 col-md-6">
				Order: <a href="{{ url('/orders/details/' . $order->id) }}" target="_blank">{{ $order->short_order }}</a>
		</div>
		<div class="col-xs-12 col-sm-6 col-md-6" style="text-align:right;">
				Batch: <a href="{{ url('/batches/details/' . $batch_number) }}" target="_blank">{{ $batch_number }}</a>
		</div>
		</h3>
		
		<br><br><br>
		
			@foreach($items as $item)
				
				<div class="col-xs-12 panel panel-default {{$item->id}}" item-id="{{$item->id}}">
					<div class="panel-body">
						<div class="col-xs-12 col-sm-6 col-md-11">
							<h4>
								<strong>
								<a href="{{ $item->item_url }}" 
								target = "_blank">{{ $item->item_description }}</a>
								</strong>
							</h4>
						</div>


						<div class="col-xs-12 col-sm-3 col-md-1">
							{!! Form::open(['name' => 'reject-' . $item->id, 'url' => '/reject_item', 'method' => 'get', 'id' => 'reject-' . $item->id]) !!}
							{!! Form::hidden('item_id', $item->id, ['id' => 'item_id']) !!}
							{!! Form::hidden('origin', 'QC', ['id' => 'origin']) !!}
							{!! Form::hidden('id', $id, ['id' => 'id']) !!}
							{!! Form::button('Reject Item' , ['id'=>'reject-' . $item->item_quantity, 'class' => 'btn btn-sm btn-danger']) !!}
							{!! Form::close() !!}
							<button class="btn btn-sm btn-danger" id="shipping_update" order_id="{{$order->id}}" style="margin-top: 10%">Set first-class</button>
						</div>
						<div class="col-xs-12">
							<hr style="margin-top:0;">
						</div>
						
						<div class="col-xs-12 col-sm-12 col-md-3">
							<a href = "{{ $item->item_url }}" target = "_blank">
							<img src = "{{ $item->item_thumb }}" height="250" width="250"></a>
						</div>
						
						<div
						@if(isset($thumbs[$item->id][0]))
							 class="col-xs-12 col-sm-12 col-md-6"
						@else
								class="col-xs-12 col-sm-12 col-md-9"
						@endif
						>
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
						
						@if(isset($thumbs[$item->id][0]))
							<div class="col-xs-12 col-sm-12 col-md-3">
								<img src = "{{ $thumbs[$item->id][0] }}" width="250" height="250">
							</div>
						@endif
					
					</div>
				</div>
			@endforeach
			
			@if($dest == 'ship')
				<div class="col-xs-0 col-sm-0 col-md-4">
				</div>
				<div class="col-xs-12 col-sm-12 col-md-8">
					@setvar($origin = 'QC')
					@include('shipping.ship_panel')
				</div>
			@else 
				<div align="center">
					{!! Form::open(['url' => 'shipping/add_wap', 'method' => 'post']) !!}
					{!! Form::hidden('batch_number', $batch->batch_number, ['id' => 'batch_number']) !!} 
					{!! Form::hidden('id', $id, ['id' => 'id']) !!} 
					{!! Form::hidden('order_id', $order->id, ['id' => 'order_id']) !!}
					{!! Form::hidden('origin', 'QC', ['id' => 'origin']) !!}
					{!! Form::hidden('count', count($items), ['id' => 'count']) !!} 
					@if (count($items) > 1)
						@setvar($btn_text = count($items) . ' Lines Approved by ' . auth()->user()->username)
					@else
						@setvar($btn_text = 'Item Approved by ' . auth()->user()->username)
					@endif
					{!! Form::button($btn_text, ['class' => 'pull-right btn btn-lg btn-warning', 'id' => 'focus-btn', 'style' => 'margin-top:5px;', 'onclick' => 'this.disabled=true;this.form.submit();']) !!}
					{!! Form::close() !!}
				</div>
			@endif
			
	@include('/rejections/rejection_modal')

	<script type = "text/javascript">

			$("#shipping_update").click(function () {

			$.ajax({
				url: "https://order.monogramonline.com/order/shipping_update?order_id=" + document.getElementById("shipping_update").getAttribute("order_id"),
				type: 'GET',
				success: function(res) {
					window.location.reload();
				},
			});
		})


		@if(is_object($order) && !is_null($order->ship_message) && strlen($order->ship_message) >= 1)
		setTimeout(function () {
			Swal.fire(
					'Ship Message',
					'{{ $order->ship_message }}',
					'question'
			)
		}, 1000)
		@endif
		// $(function() {
		// 			$('#focus-btn').focus();
		// });
		
		$(document).ready(function () {
			$('div').click(function () {
					var item = '.' + $(this).attr('item-id'); 
					if (item != '') {
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