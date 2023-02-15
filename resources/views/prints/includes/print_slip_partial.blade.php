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
						<td colspan = "3">
							@if($order->store_id == "yhst-132060549835833")
								ShopOnlineDeals.com
							@elseif($order->store_id == "yhst-128796189915726")
								MonogramOnline.com
							@endif
							<br />
								575 Underhill Blvd <br>
								Suite 216<br> Syosset, NY 11791
							<br />
							@if($order->store_id == "yhst-132060549835833")
								cs@ShopOnlineDeals.com <br>
								www.ShopOnlineDeals.com
							@elseif($order->store_id == "yhst-128796189915726")
								cs@MonogramOnline.com <br>
								www.MonogramOnline.com
							@endif
						</td>
						<td colspan = "2" align = "right" style = "padding-top:10px;">
							<img src = "{{url(sprintf('/assets/images/%s.jpg', $order->store_id))}}"
							     border = "0">
						</td>
					</tr>
					<tr valign = "top">
						<td colspan = "5" align = "center"><strong>
								Packing Slip for order#
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
										{!! \Monogram\Helper::getHtmlBarcode($order->id ."-0") !!}
										<br>Shipping# {{ $order->id."-0"}}
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr valign = "top">
						<td><strong>Ship to:</strong></td>
						<td>
							@if($order->customer->ship_company_name)
								{{$order->customer->ship_company_name}}<br>
							@endif
							{{$order->customer->ship_full_name}}<br>
							{{$order->customer->ship_address_1}}<br>
							@if($order->customer->ship_address_2)
								{{$order->customer->ship_address_2}}<br>
							@endif
							{{$order->customer->ship_city}} {{$order->customer->ship_state}}  {{$order->customer->ship_zip}}
							<br>
							{{$order->customer->ship_country}}<br>
							{{$order->customer->ship_phone}}
						</td>
						<td></td>
						<td><strong>Bill To:</strong></td>
						<td>
							@if($order->customer->bill_company_name)
								{{$order->customer->bill_company_name}}<br>
							@endif
							{{$order->customer->bill_full_name}}<br>
							{{$order->customer->bill_address_1}}<br>
							@if($order->customer->bill_address_2)
							{{$order->customer->bill_address_2}}<br>
							@endif
							{{$order->customer->bill_city}} {{$order->customer->bill_state}}  {{$order->customer->bill_zip}}
							<br>
							{{$order->customer->bill_country}}<br>
							{{$order->customer->bill_phone}}
						</td>
					</tr>
					<tr valign = "top">
						<td><strong>Ship Via:</strong></td>
						<td>{{$order->customer->shipping}}</td>
						<td></td>
						<td></td>
						<td></td>
					</tr>


					<tr valign = "top">
						<td colspan = "5">
							<table width = "100%" cellpadding = "2" cellspacing = "0" border = "0">
								<tr height = "10" valign = "top">
									<td colspan = "9">
										<img src = "{{url('/assets/images/spacer.gif')}}"
										     width = "50" height = "20" border = "0">
									</td>
								</tr>
								<tr valign = "top">
									<td></td>
									<td></td>
									<td align = "left"><strong>Name</strong></td>
									<td align = "left"><strong>Code</strong></td>
									<td align = "right"><strong>Qty</strong></td>
									<td align = "right"></td>
									<td align = "left"><strong>Options</strong></td>
									<td align = "left"><strong>B/O</strong></td>
								</tr>
								<tr height = "10" valign = "top">
									<td colspan = "9">
										<hr size = "1">
									</td>
								</tr>
								@foreach($order->items as $item)
									<tr valign = "top">
										<td>
											<img src = "{{url("/assets/images/box.jpg")}}" border = "0">
										</td>
										<td>
											<img height = "80" width = "80"
											     src = "{{$item->item_thumb}}"
											     border = "0" />
										</td>
										<td align = "left">
											{{$item->item_description}}
											@if($item->shipInfo)
												<br />
												Shipped on {{substr($item->shipInfo->transaction_datetime, 0, 10)}} by
												{{$item->shipInfo->mail_class}}
												<br />
												Trk# <a href = "#">{{$item->shipInfo->tracking_number}}</a>
											@endif
										</td>
										<td align = "left">{{$item->item_code}}</td>
										<td align = "right" style = "font-size:18px;">
											<strong>{{$item->item_quantity}}</strong>
										</td>
										<td align = "right"></td>
										<td align = "left">
											{!! str_replace('Yes(', '<strong  style="font-size: 300%;">Yes</strong>(', \Monogram\Helper::jsonTransformer($item->item_option, "<br/>")) !!}
										</td>
										<td align = "left"></td>
									</tr>

									<tr>
										<td colspan = "8" align = "left" valign = "top">
											{!! \Monogram\Helper::getHtmlBarcode(sprintf("%s", $item->id)) !!} Item# {{ $item->id}}
											@if($item->batch_number)
												{!! \Monogram\Helper::getHtmlBarcode($order->items[0]->batch_number) !!} Barch# {{ $item->batch_number }}
											@endif
										</td>
									</tr>

								@endforeach
								<tr valign = "top">
									<td colspan = "9">
										<hr size = "1">
									</td>
								</tr>
								<tr valign = "top">
									<td colspan = "9">
										<strong>Comments: </strong><strong style="color: red;">{{$order->order_comments}}<strong>
									</td>
							    </tr>
								<tr valign = "top">
									<td align = "center" colspan = "9">
										<table width = "100%" cellpadding = "5" cellspacing = "5" border = "1">
											<tr valign = "top">
												<td align = "center"><p style = "text-align: center;">
														<strong>IMPORTANT INFORMATION:&nbsp; </strong></p>
													<p style = "text-align: center;">
														We encourage you to confirm the contents of this order.&nbsp;</p>
													<p style = "text-align: center;">
														If you have placed an order that consists of multiple line items,&nbsp;</p>
													<p style = "text-align: center;">
														you may receive each item in a separate package.&nbsp;</p>
													<p style = "text-align: center;">
														We thank you for your business and we hope you love your new MonogramOnline.com product. &nbsp;</p>
													<p style = "text-align: center;">
														If for any reason you are not satisfied with your order please contact us at cs@monogramonline.com.</p>
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
	<script>
		window.print();
	</script>
</div>