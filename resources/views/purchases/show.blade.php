<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>View {{ $purchase->po_number }}</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	
</head>
<body>
	@include('includes.header_menu')
	<div class = "container" style="min-width: 1200px;">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "{{url('purchases')}}">Purchase Orders</a></li>
			<li class = "active"><a href = "{{url('purchases/' . $purchase->po_number )}}">View {{ $purchase->po_number }}</a></li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		
		<div class = "">
			<h3 class = "page-header">
				Purchase Order {{ $purchase->po_number }}
				<div class="pull-right">
						{!! \App\Task::widget('App\Purchase', $purchase->id); !!}
				</div>
			</h3>
			<table class="table table-bordered">
				<tr>
					<th class = "success" width="10%">PO Date:</th>
					<td>{{ date("m-d-Y", strtotime($purchase->po_date)) }}</td>
					<th class = "success"  width="10%">E-Mail</th>
					<td>{{$purchase->vendor_details->email}}</td>
				</tr>
				<tr>
					<th class = "success">Vendor name</th>
					<td>{{$purchase->vendor_details->vendor_name}}</td>
					<th class = "success">Phone number</th>
					<td>{{ $purchase->vendor_details->phone_number}}</td>
				</tr>
				<tr>
					<th class = "success">Notes</th>
					<td colspan=3>{{$purchase->notes}}</td>
				</tr>
			</table>
			
			@if(strlen($purchase->tracking) > 0)
				<table class = "table table-bordered">
					<tr>
						<th class = "success" width="20%">Tracking Number</th>
						<td>
								<a href="{!! Monogram\Helper::getTrackingUrl($purchase->tracking) !!}" target="_blank">{{ $purchase->tracking }}</a>
						</td>
					</tr>
				</table>
			@endif
			
			@if($purchase->products)
				<table class = "table table-bordered">
					<tr class = "success">
						<th>Vendor SKU</th>
						<th>Stock #</th>
						<th>Product</th>
						<th>Quantity</th>
						<th>Price</th>
						<th>Subtotal</th>
						<th>ETA</th>
						<th>Date Received</th>
						<th>Received</th>
						<th>Balance</th>
					</tr>
					@foreach($purchase->products as $product)
						<tr >
							<td>
								<a href="{{ url('/purchases/purchasedinvproducts?search_in=stock_no&search_for=' . $product->stock_no) }}"
								 target="_blank">{{ $product->vendor_sku }}</a>
							</td>
							<td>
								<a href="{{ url('inventories?search_in_first=stock_no_unique&operator_first=equals&search_for_first=' . $product->stock_no) }}"
								 target="_blank">{{ $product->stock_no }}</a>
							</td>
							<td>{{ $product->vendor_sku_name }}</td>
							<td align="right">{{ $product->quantity }}</td>
							<td align="right">{{ $product->price }}</td>
							<td align="right">{{ number_format($product->sub_total, 2, '.', '') }}</td>
							<td>{{ date("m-d", strtotime($product->eta)) }}</td>
							<td>{{ $product->receive_date }}</td>
							<td align="right">{{ $product->receive_quantity }}</td>
							<td align="right">{{ $product->balance_quantity }}</td>
						</tr>
					@endforeach
						<tr>
							<td colspan="5" align="right" class = "success"><b>Total:</b></td>
							<td align="right"><b>{{ number_format($purchase->grand_total, 2, '.', '') }}</b></td>
							<td colspan="4"></td>
						</tr>
				</table>
			@endif

		</div>
	</div>
	
</body>
</html>