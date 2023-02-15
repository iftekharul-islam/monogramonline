<html>
<head>
	<meta http-equiv = "Content-Type" content = "text/html; charset=utf-8" />
	<title>Order Confirmation</title>
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
	<div>
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
							</td>
						</tr>
						<tr valign = "top">
							<td colspan = "5" style = "padding-top:10px;">
								<img src = "http://order.monogramonline.com/{{ sprintf('/assets/images/%s.jpg', $order->store_id) }}" border = "0">
							</td>
						</tr>
						<tr valign = "top">
							<td colspan = "5"><strong>
									Order Confirmation for order#
									{{$order->short_order}} </strong>
								<hr size = "1">
							</td>
						</tr>
						<tr valign = "top">
							<td colspan = "5" align = "left">
								<strong> {{$order->customer->bill_full_name}} </strong><br/>
								Thank you for the order you have placed with {{ $store->store_name }}.<br/>
								We would like to keep you informed and up to date with your order status.<br/>
								<a href="https://www.monogramonline.com/order-status">Check your order status</a><br/>
								<hr size = "1">
							</td>
						</tr>

						<tr valign = "top">
							<td colspan = "2" align = "left"> <strong>Your order was placed on :</strong></td>
							<td colspan = "3" align = "left">{{date("m/d/y", strtotime($order->order_date) )}}</td>
						</tr>
						<tr valign = "top">
							<td colspan = "2" align = "left">Order Status:</td>
							<td colspan = "3" align = "left">In Production</td>
						</tr>
						<tr>
							<td colspan = "2" align = "left">Expected shipping time :</td>
							<td colspan = "3" align = "left">5 to 7 Business Days from order date </td>
						</tr>
						<tr>
							<td colspan = "2"></td>
							<td colspan = "3" align = "left">(Please allow an additional 3-5 days for the delivery time.)</td>
						<tr>
							<td colspan = "5">
								<br>
							We would like to assure you that we are working diligently to fulfill your orders.<br/>
							If you have any questions <a href="https://www.monogramonline.com/contact-us">please visit our website</a> 
							or send us an email <a href="mailto:{{ $store->email }}">{{ $store->email }}</a>.<br/>
							<br/>
							We appreciate your business<br/>
							<hr size = "1">
							</td>
						</tr>
						<tr valign = "top">
							<td><strong>Shipping Address:</strong></td>
							<td>
								{{$order->customer->ship_full_name}}<br>
								{{$order->customer->ship_address_1}}<br>
								@if ($order->customer->ship_address_2)
									{{$order->customer->ship_address_2}}<br>
								@endif
								{{$order->customer->ship_city}} {{$order->customer->ship_state}}  {{$order->customer->ship_zip}}
								<br>
								{{$order->customer->ship_country}}<br>
								{{$order->customer->ship_phone}}
							</td>
							<td></td>
							<td><strong>Billing Address:</strong></td>
							<td>
								{{$order->customer->bill_full_name}}<br>
								{{$order->customer->bill_address_1}}<br>
								@if ($order->customer->bill_address_2)
									{{$order->customer->bill_address_2}}<br>
								@endif
								{{$order->customer->bill_city}} {{$order->customer->bill_state}}  {{$order->customer->bill_zip}}
								<br>
								{{$order->customer->bill_country}}<br>
								{{$order->customer->bill_phone}}<br>
								{{$order->customer->bill_email}}
							</td>
						</tr>
						<tr valign = "top">
							<td><strong>Ship Via:</strong></td>
							<td>{{ $order->customer->shipping }}</td>
							<td></td>
							<td><strong>Comments:</strong></td>
							<td>{{$order->order_comments}}</td>
						</tr>


						<tr valign = "top">
							<td colspan = "5">
								<table width = "100%" cellpadding = "2" cellspacing = "0" border = "0">
									<tr height = "10" valign = "top">
										<td colspan = "7" height = "20">
										</td>
									</tr>
									<tr valign = "top">
										<td></td>
										<td><strong>Product</strong></td>
										<td align = "center"></td>
										<td align = "center"><strong>Code</strong></td>
										<td align = "center"><strong>Price</strong></td>
										<td align = "center"><strong>Qty</strong></td>
										<td align = "center"><strong>Total</strong></td>
									</tr>
									<tr height = "7" valign = "top">
										<td colspan = "9">
											<hr size = "1">
										</td>
									</tr>
									
									@setvar($subtotal = 0)
									
									@foreach($order->items as $item)
										<tr valign = "top">
											<td></td>
											<td>
												<img height = "80" width = "80"
												     src = "{{$item->item_thumb}}"
												     border = "0" />
											</td>
											<td align = "left">
												{{$item->item_description}}
												<br>
												{!! \Monogram\Helper::optionTransformer($item->item_option, 1, 1, 0, 1, 0, "<br/>") !!}
												@if($item->shipInfo)
													<br />
													Shipped on {{ substr($item->shipInfo->transaction_datetime, 0, 10) }} by
													{{ $item->shipInfo->mail_class }}
												@endif
											</td>
											{{-- SKU --}}
											<td align = "left">{{$item->item_code}}</td>
											{{-- item unit price --}}
											<td align = "right">{{$item->item_unit_price}}</td>
											{{-- QTY --}}
											<td align = "right" style = "font-size:18px;">
												<strong>{{$item->item_quantity}}</strong>
											</td>
											{{-- Total --}}
											<td align = "right">{{ (($item->item_quantity)  * ($item->item_unit_price)) }}</td>
										</tr>
										
										<tr height = "7" valign = "top">
											<td colspan = "9">
												<hr size = "1">
											</td>
										</tr>
										
										@setvar($subtotal += (($item->item_quantity)  * ($item->item_unit_price)))
										
									@endforeach
									
										<tr>
											<td colspan = "6" align = "right" ><strong>Items Subtotal:</strong></td>
											<td align = "right" ><p>{!! sprintf("$%01.2f", $subtotal) !!} </p></td>
										</tr>
										@if ($order->promotion_value > 0)
											<tr>
												<td colspan = "6" align = "right" ><strong>Promotion: </strong> ({{ $order->promotion_id }}): </td>
												<td align = "right" ><p>-{!! sprintf("$%01.2f", $order->promotion_value) !!} </p></td>
											</tr>
											@setvar($subtotal = $subtotal - $order->promotion_value)
										@endif
										@if ($order->coupon_value > 0)
											<tr>
												<td colspan = "6" align = "right" ><strong>Coupon: </strong> ({{ $order->coupon_id }}): </td>
												<td align = "right" ><p>-{!! sprintf("$%01.2f", $order->coupon_value) !!} </p></td>
											</tr>
											@setvar($subtotal = $subtotal - $order->coupon_value)
										@endif
										<tr>
											<td colspan = "6" align = "right" ><strong>Order Subtotal:</strong></td>
											<td align = "right" ><p>{!! sprintf("$%01.2f", $subtotal) !!} </p></td>
										</tr>
										<tr>
											<td colspan = "6" align = "right" ><strong>Tax:</strong></td>
											<td align = "right" ><p>{!! sprintf("$%01.2f", $order->tax_charge) !!} </p></td>
										</tr>
										<tr>
											<td colspan = "6" align = "right" ><strong>Shipping Cost:</strong></td>
											<td align = "right" ><p>{!! sprintf("$%01.2f", $order->shipping_charge) !!} </p></td>
										</tr>
										<tr>
											<td colspan = "6" align = "right" ><strong>Order Total:</strong></td>
											<td align = "right" ><p>{!! sprintf("$%01.2f", $order->total) !!} </p></td>
										</tr>

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
														<p style = "text-align: center;">We thank you for your business and we hope you enjoy your new {{ $store->store_name }} product.</p>
														@if (date("Y-m-d H:i:s") < '2018-12-25 00:00:00')
															<p style = "text-align: center;">
																Our regular production time is 5-7 business days, but due to high demand during this time of the year 
																our current production time is approximately 7-12 business days plus 3-5 days delivery time. 
																Rest assure that we are working diligently to ensure the timely production and delivery of your order.
															</p>
														@endif
														<p style = "text-align: center;">If for any unlikely reason you are not satisfied with your order please contact us through our website and we will make all efforts to make sure that you are satisfied with your purchase.</p>
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
