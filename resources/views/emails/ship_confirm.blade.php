<html>
<head>
	<meta http-equiv = "Content-Type" content = "text/html; charset=utf-8" />
	<title>Shipping Confirmation</title>
	<style type = "text/css">
		@page {
			width: 210mm;
			height: 297mm;
			margin-top: 10mm;
		}
		body,td,th {
			font-family: Verdana, Arial, Helvetica, sans-serif;
			font-size: 10px;
			margin-top: 0px;
		}
		h2 {
		  font-family: Verdana, Arial, Helvetica, sans-serif;
			font-size: 18px;
			font-weight:bold;
		}
	</style>
</head>
<body>
	<div class = "current-batch">
		<table cellpadding = "0" cellspacing = "0" width = "810" border = "0">
			<tr valign = "top">
				<td style = "width:800px;">
					<table cellpadding = "0" cellspacing = "0" width = "100%" border = "0">
						<tr valign = "top">
							<td style = " width:15%; height:1px;"></td>
							<td style = " width:30%; height:1px;"></td>
							<td style = " width:5%; height:1px;"></td>
							<td style = " width:7%; height:1px;"></td>
							<td style = " width:40%; height:1px;"></td>
						</tr>
						<tr valign = "top"></tr>
						<tr valign = "top">
							<td colspan = "5" align="center">
								@if($store->ship_banner_image != '')
									<a href="{{ $store->ship_banner_url }}"><img src = "{{ $store->ship_banner_image }}" width="800" border = "0"></a>
								@endif
								<br>
							</td>
						</tr>
						<tr valign = "top">
							<td colspan = "5" style = "padding-top:10px;">
								<img src = "http://order.monogramonline.com/{{ sprintf('/assets/images/%s.jpg', $order->store_id) }}"
								     border = "0">
							</td>
						</tr>
						<tr valign = "top">
							<td colspan = "5"><strong>
									Shipping Confirmation for order#
									{{$order->short_order}} </strong>
								<hr size = "1">
							</td>
						</tr>
						<tr valign = "top">
							<td><strong>Order Date:</strong></td>
							<td>{{date("m/d/y", strtotime($order->order_date) )}}</td>
							<td>

							</td>
							<td><strong>Order #</strong></td>
							<td>
								<table width = "100%" cellpadding = "0" cellspacing = "0" border = "0">
									<tr valign = "top">
										<td align = "left"><strong>{{$order->short_order}}</strong></td>
										<td align = "right">
											
										</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr valign = "top">
							<td><strong>Shipping Address:</strong></td>
							<td>
								{{$order->customer->ship_full_name}}<br>
								{{$order->customer->ship_address_1}}<br>
								{{$order->customer->ship_address_2}}<br>
								{{$order->customer->ship_city}} {{$order->customer->ship_state}}  {{$order->customer->ship_zip}}
								<br>
								{{$order->customer->ship_country}}<br>
								{{$order->customer->ship_phone}}
							</td>
							<td colspan=3></td>
						</tr>

						<tr valign = "top">
							<td colspan = "5">
								<table width = "100%" cellpadding = "2" cellspacing = "0" border = "0">
									<tr height = "10" valign = "top">
										<td colspan = "7" height=20>
										</td>
									</tr>
									<tr valign = "top">
										<td></td>
										<td><strong>Product</strong></td>
										<td align = "center"></td>
										<td align = "center"><strong></strong></td>
										<td align = "center"><strong>Qty</strong></td>
										<td colspan=2 align = "center"><strong>Shipping Information</strong></td>
									</tr>
									<tr height = "10" valign = "top">
										<td colspan = "7">
											<hr size = "1">
										</td>
									</tr>
									@foreach($order->items as $item)
										<tr valign = "top">
											<td>
												
											</td>
											<td>
												<img height = "80" width = "80"
												     src = "{{$item->item_thumb}}"
												     border = "0" />
											</td>
											<td align = "left">
												{{$item->item_description}}
												<br>
												{!! \Monogram\Helper::optionTransformer($item->item_option, 1, 1, 0, 1, 0, "<br/>") !!}
											</td>
											{{-- SKU --}}
											<td align = "left">{{$item->item_code}}</td>
											{{-- QTY --}}
											<td align = "right" style = "font-size:18px;">
												<strong>{{$item->item_quantity}}</strong>
											</td>
											{{-- tracking --}}
											<td colspan=2 align = "right">
												@if($item->shipInfo)
													Shipped on {{substr($item->shipInfo->transaction_datetime, 0, 10)}} 
													<br>
													{{$item->shipInfo->mail_class}}
													<br />
													Trk# <a href = "{{ \Monogram\Helper::getTrackingUrl($item->shipInfo->shipping_id) }}" target = "_blank">{{$item->shipInfo->shipping_id}}</a>
												@elseif ($item->item_status == 'rejected')
													In Production
												@else
													{{ ucFirst($item->item_status) }}
												@endif
											</td>
										</tr>
										<tr>
											<td colspan = "7">
												<hr size = "1">
											</td>
										</tr>
									@endforeach

										<tr valign = "top">
											<td colspan = "7">
												<hr size = "1">
											</td>
										</tr>


									<tr valign = "top">
										<td align = "center" colspan = "7">
											<table width = "100%" cellpadding = "5" cellspacing = "5" border = "1">
												<tr valign = "top">
													<td align = "center"><p style = "text-align: center;">
															<strong>IMPORTANT INFORMATION:</strong></p>
														<p style = "text-align: center;">
															We encourage you to confirm the contents of this order.
														</p>
														<p style = "text-align: center;">
															If you have placed an order that consists of multiple line items,
														</p>
														<p style = "text-align: center;">
															you may receive each item in a separate package.
														</p>
														<p style = "text-align: center;">
															We thank you for your business and we hope you love your new {{ $store->store_name }} product. 
														</p>
															@if($store->email)
																<p style = "text-align: center;">
																If for any reason you are not satisfied with your order please contact us at {{ $store->email }}.
																</p>
															@endif
													</td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</div>
</body>
</html>
