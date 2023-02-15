<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Marketplace Imports</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">

	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<body>
	@include('includes.header_menu')
	<div class = "container">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "{{url('/transfer/import')}}">Import Orders</a></li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		
		<h3 class="page-header">Import Orders</h3>
		
		@if (count($downloads) > 0)
			<div class = "panel panel-success">
						<div class = "panel-heading">
							<h4 class="panel-title">Download Shipping Information</h4>
						</div>
						<div class = "panel-body">
							<div class = "col-xs-12">
								@foreach ($downloads as $file)
									
									<div class = "row" style = "margin-bottom:20px;">
										{!! Form::open(['method' => 'post', 'url' => url('transfer/import'), 'class' => 'download-form']) !!}
										{!! Form::hidden('download_file', $file) !!}
										<div class = "col-xs-2">
											{!! Form::submit('Download', ['class' => 'form-control btn btn-sm btn-success']) !!}
										</div>
										<div class = "col-xs-4">
											{!! Form::label($file, '', ['style' => 'margin-top:5px;']) !!}
										</div>
										{!! Form::close() !!}
									</div>
								@endforeach
							</div>
						</div>
			</div>
		@endif
		
		<div class = "panel panel-info">
					<div class = "panel-heading">
						<h4 class="panel-title">Upload Order File</h4>
					</div>
					{!! Form::open(['method' => 'post', 'url' => url('transfer/import'), 'files'=>'true']) !!}
					<div class = "panel-body">
						<div class = "col-xs-12">
							<div class = "col-xs-1">
								{!! Form::label('Store:', '', ['style' => 'margin-top:5px;']) !!}
							</div>
							<div class = "col-xs-3">
								{!! Form::select('store_id', $import_stores , null, ['id' => 'store_id', 'class' => 'form-control']) !!}
							</div>
							<div class = "col-xs-6">
								{!! Form::file('import', ['accept' => '.csv', 'class' => 'form-control']) !!}
							</div>
							<div class = "col-xs-2">
								{!! Form::submit('Import', ['class' => 'form-control btn btn-info']) !!}
							</div>
						</div>
					</div>
					{!! Form::close() !!}
		</div>


		<div class = "panel panel-info">
			<div class = "panel-heading">
				<h4 class="panel-title">Import Tracking</h4>
			</div>
			{!! Form::open(['method' => 'post', 'url' => url('transfer/import'), 'files'=>'true']) !!}
			<div class = "panel-body">
				<div class = "col-xs-12">
					<div class = "col-xs-1">
						{!! Form::label('Store:', '', ['style' => 'margin-top:5px;']) !!}
					</div>
					<div class = "col-xs-3">
						{!! Form::select('store_id', $import_storesTracking , null, ['id' => 'store_id', 'class' => 'form-control']) !!}
						{!! Form::hidden('dropship', "yes") !!}
					</div>
					<div class = "col-xs-6">
						{!! Form::file('import', ['accept' => '.csv', 'class' => 'form-control']) !!}
					</div>
					<div class = "col-xs-2">
						{!! Form::submit('Import', ['class' => 'form-control btn btn-info']) !!}
					</div>
				</div>
			</div>
			{!! Form::close() !!}
		</div>



		<div class = "panel panel-info">
			<div class = "panel-heading">
				<h4 class="panel-title">Import Zakeke Orders</h4>
			</div>
			{!! Form::open(['method' => 'post', 'url' => url('transfer/importZakeke'), 'files'=>'true']) !!}
			<div class = "panel-body">
				<div class = "col-xs-12">
					<div class = "col-xs-6">
						{!! Form::file('import', ['accept' => '.csv', 'class' => 'form-control']) !!}
					</div>
					<div class = "col-xs-2">
						{!! Form::submit('Import', ['class' => 'form-control btn btn-info']) !!}
					</div>
				</div>
			</div>
			{!! Form::close() !!}
		</div>


		@if(count($orders) > 0)
			
			<div class = "col-xs-12">
			
			<h4>{{ count($orders) }} Orders Imported</h4>

			<button onclick="changeStatus()">Set all to OTHER HOLD</button>
			<table class = "table table-bordered">
				<tr>
					<th>Order</th>
					<th>Date</th>
					<th>Customer</th>
					<th>Items</th>
					<th>Subtotal</th>
					<th>Discount</th>
					<th>Shipping</th>
					<th>Tax</th>
					<th>Total</th>
					<th>Order Status</th>
				</tr>
				@foreach($orders as $order)
					<tr data-id = "{{$order->id}}">
						<td>
								<a href = "{{ url("orders/details/" . $order->id) }}" target = "_blank">{{ $order->short_order }}</a>
						</td>
						<td>
								{{ $order->store->store_name }}
								<br>
								{{ $order->order_date }}
						</td>
						<td>
							<a href = "{{ url("/customer_service/customers/" . ($order->customer ? $order->customer->id : "#")) }}"
										 target = "_blank">{{$order->customer ? $order->customer->ship_full_name : "#"}}</a>
							<br>
							{{$order->customer ? $order->customer->ship_state: "#"}}, {{$order->customer ? $order->customer->ship_country : "#"}}
						</td>
						<td>{{$order->item_count}}</td>
						<td align="right">${!! sprintf("%01.2f", $order->items->sum('item_total_price')) !!}</td>
						<td align="right">(${!! sprintf("%01.2f", $order->promotion_value + $order->coupon_value) !!})</td>
						<td align="right">${!! sprintf("%01.2f",$order->shipping_charge) !!}</td>
						<td align="right">${!! sprintf("%01.2f",$order->tax_charge) !!}</td>
						
						@setvar($diff = sprintf("%01.2f",$order->items->sum('item_total_price') - ( $order->promotion_value + $order->coupon_value) +  $order->shipping_charge + $order->tax_charge) - $order->total)
						
						<td align="right">
							<strong @if ($diff != 0) style="color:red;"@endif>
								${!! sprintf("%01.2f", $order->total) !!}
							</strong>
						</td>

						<td align="right">
							<a class="order-status">To Be Processing</a>
						</td>
					</tr>
				@endforeach
				<script>
					function httpGet(theUrl)
					{
						var xmlHttp = new XMLHttpRequest();
						xmlHttp.open( "GET", theUrl, false ); // false for synchronous request
						xmlHttp.send( null );
						return xmlHttp.responseText;
					}

					function changeStatus() {

						    var ids = '{{$orderIds}}'
							httpGet("https://order.monogramonline.com/tool/order_status/hold/" + ids)

						document.querySelectorAll('.order-status').forEach(function(shit) {
							shit.innerHTML = "OTHER HOLD"
						});


							Swal.fire({
								icon: 'success',
								title: 'All set',
								text: 'Status has been changed to order hold!'
							})
					}
				</script>
			</table>
		</div>
		@endif
	</div>

	<script type = "text/javascript">
	
		$('.download-form').submit( function () {
				$(this).find(':input[type=submit]').prop('disabled', true);
		});
		
	</script>
</body>
</html>