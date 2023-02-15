<html>
<head>
	<style>
		body,td,th {
			font-family: Verdana, Arial, Helvetica, sans-serif;
			font-size: 16px;
			margin-top: 0px;
		}
		hr {
			width:100%;
    	color: black;
	    padding-top: 1px;
		}
	</style>
</head>
<body>
	<table style = "width:350mm;" cellpadding = "0" cellspacing = "0" border = "0">
		<tbody>
		<tr>
			<td>
					<strong style="font-size: 110%;">{{ $batch->route->summary_msg_1 }}</strong>
					<br>
					<strong  style="font-size: 160%;">{{ $batch->route->summary_msg_2 }}</strong>
			</td>
			<td>
					@if ($batch->store)
						<strong style="font-size: 110%;color:red;">{{ $batch->store->store_name }}</strong>
					@endif
			</td>
			<td valign=top>
					Date:
					<br>
					<strong style="font-size: 160%;">{{ substr($batch->min_order_date, 0, 10) }}</strong>
			</td>
			<td valign=top>
					Items: 
					<br>
					<strong style="font-size: 160%;">{{ $batch->items->sum('item_quantity') }}</strong>
			</td>
			<td align="center">&nbsp;</td>
			<td align="right">
				<strong style="font-size: 200%;">{{ $batch->production_station->station_name }}</strong>&nbsp;&nbsp;&nbsp;
					<strong style="font-size: 200%;">{{$batch->batch_number}}</strong>
			</td>
		</tr>
		</tbody>
	</table>
	<table style = "width:350mm;" cellpadding = "0" cellspacing = "0" border = "0">
					<tr>
						<td colspan="6">
							<hr size = "1" />
						</td>
					</tr>
					<tr valign = "top">
						<th align = "left" style = "width:15mm;">Item</th>
						<th align = "center" style = "width:25mm;">Order</th>
						<th align = "center" style = "width:15mm;">Qty</th>
						<th align = "left" style = "width:100mm;">Product</th>
						<th align = "left" style = "width:100mm;">Personalization</th>
						<th align = "left">Inventory</th>
					</tr>
					<tr>
						<td colspan="6">
							<hr size = "1" />
						</td>
					</tr>
					@setvar($count = 0)
					@foreach($batch->items as $row)
							@if(!$row->tracking_number)
								@setvar($count = $count + $row->item_quantity)
								<tr valign = "top" class="nobreak">
									<td align = "left">
										{{ $row->id }}
										@if (count($row->order->shippable_items) > 1)
											<br>
											<strong style="font-size: 110%;">WAP</strong>
										@endif
									</td>
									<td align = "center">{{$row->order->short_order}}</td>
									<td align = "center">{{$row->item_quantity}}</td>
									<td align = "left">
										{{$row->child_sku}}
										<br>
										{{substr($row->item_description,0,150)}}
									</td>
									<td align = "left">
										{!! $options[$row->id] !!}
									</td>
									<td>
										@foreach ($row->inventoryunit as $unit)
											<strong>{{ $unit->stock_no_unique }}</strong>
											@if ($unit->inventory)
												<br>
												{{ $unit->inventory->stock_name_discription }} 
												@if (intval($unit->unit_qty * $row->item_quantity) != 1)
													<br><strong style="font-size: 110%;">QTY: {{ intval($unit->unit_qty * $row->item_quantity) }}</strong>
												@endif
											@else
												<br>Stock Number Not Found
											@endif
										@endforeach
									</td>
								</tr>
								<tr>
									<td colspan="6">
										<hr size = "1" />
									</td>
								</tr>
							@endif
					@endforeach
				</table>
			</td>
		</tr>
	</table>
</tbody>
</body>
</html>
