<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>{{ $order->short_order }} details</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/nprogress.css">
	<link type = "text/css" rel = "stylesheet" href="/assets/css/pikaday.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/moment.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/pikaday.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/nprogress.js"></script>
	<script type = "text/javascript" src = "/assets/js/jquery.autocomplete.min.js"></script>
				
	<style type = "text/css">
		
		table#items-table th {
			min-width: 100px;
		}

		table#items-table td {
			min-width: 100px;
			text-align: center;
		}
		
		table { 
			font-family: Verdana, Arial, Helvetica, sans-serif; 
			font-size: 10px; 
			color: #000000; 
		} 
		
		.autocomplete-suggestions { -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; border: 1px solid #999; background: #FFF; cursor: default; overflow: auto; -webkit-box-shadow: 1px 4px 3px rgba(50, 50, 50, 0.64); -moz-box-shadow: 1px 4px 3px rgba(50, 50, 50, 0.64); box-shadow: 1px 4px 3px rgba(50, 50, 50, 0.64); }
		.autocomplete-suggestion { padding: 2px 5px; white-space: nowrap; overflow: hidden; }
		.autocomplete-no-suggestion { padding: 2px 5px;}
		.autocomplete-selected { background: #F0F0F0; }
		.autocomplete-suggestions strong { font-weight: bold; color: #000; }
		.autocomplete-group { padding: 2px 5px; font-weight: bold; font-size: 16px; color: #000; display: block; border-bottom: 1px solid #000; }
	</style>
</head>
<body>
	@include('includes.header_menu')
	<div class = "container">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "{{url('orders/list')}}" class="active">Orders</a></li>
			<li><a href = "{{url('orders/details/' . $order->id)}}" class="active">Order {{ $order->short_order }}</a></li>
			<div class="pull-right">
				{!! Form::open(['url' => url('orders/searchOrder'), 'name' => 'search']) !!}
				{!! Form::text('search_input', null, ['id' => 'search_input', 'placeholder' => 'Search Orders']) !!}
				{!! Form::hidden('prev_order', $order->id) !!}
				{!! Form::submit('Go', ['class' => 'btn btn-xs', 'name' => 'go'] ) !!}
				{!! Form::close() !!}
			</div>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')

		<div class="col-xs-12">
			<div class="row">
				<div class="col-xs-3">
					<strong>Order: {{ $order->order_id }}</strong>
					<br>
					<strong>Date: {{ $order->order_date }}</strong>
					<br>
					@if ($order->purchase_order != NULL)
						<strong>PO: {{ $order->purchase_order }}</strong>
					@endif 
				</div>
				<div class="col-xs-3">
					@if ($order->store && auth()->user()->accesses->where('page', 'orders_admin')->all())
						{!! Form::open(['name' => 'store_form', 'url' => '/orders_admin/change_store', 'method' => 'post',
														 'onsubmit' => "return confirm('Are you sure you want to change the store?');"]) !!}
						{!! Form::hidden('order_5p', $order->id) !!}
						{!! Form::select('store_select', $stores, $order->store_id, ['onchange' => 'this.form.submit()', 'class' => 'form-control']) !!}
						{!! Form::close() !!}
					@elseif ($order->store)
						<strong>Store: {{ $order->store->store_name }}	</strong>
					@else
						<strong>STORE: {{ $order->store_id }} NOT FOUND	</strong>
					@endif
				</div>
				<div class="col-xs-1"></div>
				<div class="col-xs-1">
					<strong>Status:</strong>
				</div>
				<div class="col-xs-3">
					@if (count($status_selector) > 1 && 
								count(array_intersect(auth()->user()->accesses->pluck('page')->toArray(), ['supervisor','ship_order','customer_service'])) > 0)
							{!! Form::select('status_select', $status_selector, $statuses[$order->order_status], 
												['id'=>'status_selector', 'onchange' => "change_status();", 'class' => 'form-control']) !!}
					@else
						{{ $statuses[$order->order_status] }}
					@endif
				</div>
				<div class="col-xs-1">
					{!! \App\Task::widget('App\Order', $order->id); !!}
				</div>
			</div>
			<div class="row">
					<hr>
			</div>
		</div>
		
		<div class="col-xs-12">
			<div class="row">

				{!! Form::open(['url' => url('ship_order/update_method'), 'method' => 'post', 'id' => 'method_form']) !!}
				{!! Form::hidden('id', $order->id) !!}
				<div class="col-xs-1">
					<strong>Ship Via:</strong>
				</div>
				<div class="col-xs-3">
					@if($order->store && $order->store->change_method == '1' && $order->carrier != 'MN')
						{!! Form::select('shipping_method', $shipping_methods,  $order->carrier . '*' . $order->method, ['id' => 'shipping_method', 'class' => 'form-control', 'onchange' => "change_shipmethod(this.form);"]) !!}
					@elseif($order->store && $order->store->change_method == '1')
						{!! Form::select('shipping_method', $shipping_methods,  $order->carrier . '*', ['id' => 'shipping_method', 'class' => 'form-control', 'onchange' => "change_shipmethod(this.form);"]) !!}
					@else
						@if (isset($order->carrier))
							{{ $order->carrier }} {{ $order->method }}
						@else 
							DEFAULT SHIPPING
						@endif
					@endif
				</div>
				{!! Form::close() !!}
				
				{!! Form::open(['url' => url('orders/update_shipdate'), 'method' => 'post']) !!}
				{!! Form::hidden('id', $order->id) !!}
				<div class="col-xs-2" style="text-align:right;">
					<strong>Ship By Date:</strong>
				</div>
				<div class="col-xs-3">
					<div class = 'input-group date' style = "padding-left:24px">  
					 {!! Form::text('ship_date', $order->ship_date, ['id'=>'ship_date', 'class' => 'form-control', 'autocomplete' => 'off', 'onBlur' => 'this.form.submit();']) !!} 
						 <span class = "input-group-addon"> 
													 <span class = "glyphicon glyphicon-calendar"></span> 
											 </span> 
					 </div> 
				</div>
				{!! Form::close() !!}
				
				<div class="col-xs-3" style="text-align:right;">
					@if ($order->order_status != 12 && $order->order_status != 7)
						@if ($order->carrier == 'MN' && $order->order_status != 6 && $order->order_status != 8)
							<a href="#" class="btn btn-info" onclick="track_item('all');", >{{ $order->method != '' ? $order->method : 'Manual Ship' }}</a>
						@elseif ($batched == 0 && $order->order_status != 6 && $order->order_status != 8)
							<a href="/ship_order/ship_from_order?order_id={{ $order->id }}&reship=0" class="btn btn-success">Ship</a>
						@elseif ($order->order_status == 10)
							<a href="/ship_order/ship_from_order?order_id={{ $order->id }}&reship=1" class="btn btn-success">Re-Ship Returned Package</a>
						@endif 
					@endif
				</div>
			</div>
			<div class="row">
					<hr>
			</div>
			
		{!! Form::open(['url' => url('orders/'. $order->id), 'method' => 'put', 'name' => 'order']) !!}
		{!! Form::hidden('customer_id', $order->customer_id) !!}
		<table>
			<tr>
				<td colspan=3 style = "font-weight: bold;color: #686869;">Ship To:</td>
				<td colspan=3 style = "font-weight: bold;color: #686869;padding-left:97px;">Bill To:</td>
				<td rowspan=2>
					<button type = "submit" class = "btn btn-primary">Update Order</button>

					@if($batched == 0 or $batched <= 2)
					<button class="btn btn-warning" type="button" onclick="window.location.href = 'https://order.monogramonline.com/custom/batch?order={{$order->id}}'">Batch</button>
						@endif
				</td>
			</tr>
			<tr>
				<td>Company Name</td>
				<td>{!! Form::text('ship_company_name', $order->customer->ship_company_name, ['id' => 'company_name']) !!}</td>
				<td></td>
				<!---->
				<td style = "padding-left:97px">Company Name</td>
				<td>{!! Form::text('bill_company_name', $order->customer->bill_company_name, ['id' => 'bill_company_name']) !!}</td>
				<td colspan=2></td>
			</tr>
			<tr>
				<td>Ship Full Name</td>
				<td>{!! Form::text('ship_full_name', $order->customer->ship_full_name, ['id' => 'full_name']) !!}</td>
				<td></td>
				<!---->
				<td style = "padding-left:97px"></td>
				<td colspan=3></td>
			</tr>
			<tr>
				<td>First/last Name</td>
				<td>{!! Form::text('ship_first_name', $order->customer->ship_first_name, ['id' => 'ship_first_name']) !!}</td>
				<td>{!! Form::text('ship_last_name', $order->customer->ship_last_name, ['id' => 'ship_last_name']) !!}</td>
				<td style = "padding-left:97px">First/last Name</td>
				<td>{!! Form::text('bill_first_name', $order->customer->bill_first_name, ['id' => 'bill_first_name', 'class' => '']) !!}</td>
				<td>{!! Form::text('bill_last_name', $order->customer->bill_last_name, ['id' => 'bill_last_name', 'class' => '']) !!}</td>
				<td></td>
			</tr>
			<tr>
				<td>Addr1</td>
				<td>{!! Form::text('ship_address_1', $order->customer->ship_address_1, ['id' => 'ship_address_1']) !!}</td>
				<td></td>
				<td style = "padding-left:97px">Addr1</td>
				<td>{!! Form::text('bill_address_1', $order->customer->bill_address_1, ['id' => 'bill_address_1', 'class' => '']) !!}</td>
				<td colspan=2></td>
			</tr>
			<tr>
				<td>Addr2</td>
				<td>{!! Form::text('ship_address_2', $order->customer->ship_address_2, ['id' => 'ship_address_2']) !!}</td>
				<td></td>
				<td style = "padding-left:97px">Addr2</td>
				<td>{!! Form::text('bill_address_2', $order->customer->bill_address_2, ['id' => 'bill_address_2']) !!}</td>
				<td colspan=2></td>
			</tr>
			<tr>
				<td>City, State, Zip</td>
				<td>{!! Form::text('ship_city', $order->customer->ship_city, ['id' => 'ship_city']) !!}</td>
				<td>{!! Form::text('ship_state', $order->customer->ship_state, ['id' => 'ship_state']) !!}</td>
				<td style = "padding-left:97px">City, State, Zip</td>
				<td>{!! Form::text('bill_city', $order->customer->bill_city, ['id' => 'bill_city']) !!}</td>
				<td>{!! Form::text('bill_state', $order->customer->bill_state, ['id' => 'bill_state']) !!}</td>
				<td colspan=2></td>
			</tr>
			<tr>
				<td></td>
				<td>{!! Form::text('ship_zip', $order->customer->ship_zip, ['id' => 'ship_zip','style'=>'width: 100px']) !!}</td>
				<td></td>
				<td style = "padding-left:97px"></td>
				<td>{!! Form::text('bill_zip', $order->customer->bill_zip, ['id' => 'bill_zip','style'=>'width: 100px']) !!}</td>
				<td colspan=2></td>
			</tr>
			<tr>
				<td>Country</td>
				<td>{!! Form::text('ship_country', $order->customer->ship_country, ['id' => 'ship_country']) !!}</td>
				<td></td>
				<td style = "padding-left:97px">Country</td>
				<td>{!! Form::text('bill_country', $order->customer->bill_country, ['id' => 'bill_country']) !!}</td>
				<td colspan=2></td>
			</tr>
			<tr>
				<td>Phone</td>
				<td>{!! Form::text('ship_phone', $order->customer->ship_phone, ['id' => 'company_name']) !!}</td>
				<td></td>
				<td style = "padding-left:97px">Phone</td>
				<td>{!! Form::text('bill_phone', $order->customer->bill_phone, ['id' => 'bill_phone']) !!}</td>
				<td colspan=2></td>
			</tr>
		</table>
		<!--{!! Form::text('ship_email', $order->customer->ship_email, ['id' => 'ship_email']) !!}-->
		
		<table>
			<tr>
				<td>
					<hr style = "width: 470px; color: black; background-color:black;margin-top: 10px" size = "1" />
				</td>
				<td>
					<hr style = "margin-left:58px;width: 470px; color: black; background-color:black;margin-top:  10px"
					    size = "1" />
				</td>
			</tr>
		</table>

		<table>
			<tr>
				<td><label><b>Email</b></label></td>
				<td style = "padding-left:20px">
					{!! Form::text('bill_email', $order->customer->bill_email, ['id' => 'bill_email','style'=>'width: 300px']) !!}
					@if($order->customer->bill_email)
						<button type = "button" class = "btn btn-link" data-toggle = "modal"
						        data-target = "#large-email-modal-lg" 
										onclick="open_email({{ $order->id }},'{{ $order->short_order }}','{{ $order->customer->bill_email }}', 0)">
							<i class = "glyphicon glyphicon-envelope"></i>
						</button>
						|
						<a href = "{{ url(sprintf('orders/list?search_for_first=%s&operator_first=equals&search_in_first=email',$order->customer->bill_email)) }}"
								 data-toggle = "tooltip" data-placement = "top"  class = "btn btn-link" target = "_blank"
								 title = "View Orders for this Email Address"><i class = 'glyphicon glyphicon-eye-open'></i></a>
					@endif
				</td>
			</tr>
			<tr>
				<td>
					<label><b>Customer comment:</b></label>
				</td>
				<td style = "padding-left:20px">
					{!! Form::textarea('order_comments', $order->order_comments, ['id' => 'order_comments','rows' => '2', 'style'=>'width: 300px']) !!}
				</td>
			</tr>
			<tr>
				<td>
					<label><b>Shipper Message:</b></label>
				</td>
				<td style = "padding-left:20px">
					{!! Form::textarea('ship_message', $order->ship_message, ['id' => 'ship_message','rows' => '8', 'style'=>'width: 300px']) !!}
				</td>
			</tr>
		</table>
		<hr style = "width: 100%; color: black; background-color:black;margin-top:  10px" size = "1" />
		<table class="table" id = "items-table">
			<thead>
			<tr>
				<th colspan=3></th>
				<th width=100>Quantity</th>
				<th width=100>Price</th>
				<th width=200></th>
			</tr>
			</thead>
			<tbody>
			@setvar($sub_total = 0)
			@foreach($order->items as $item)
				@setvar( $sub_total += ($item->item_quantity * $item->item_unit_price))
				<tr class="item-line">
					{!! Form::hidden("item_id[]", $item->id) !!}
					{!! Form::hidden("item_sku[]", $item->item_code) !!}
					<td width=100>
						<a href = "{{ url($item->item_url ? $item->item_url : '#') }}" target = "_blank">

							<img src = "{{$item->item_thumb}}" width='150' height="150"/>
						</a>
						<br>
						@if ($order->store && $order->store->change_items == '1' && 
									$item->item_status != 'cancelled' && $item->item_status != 'shipped')
							<a style = 'color:gray' class="delete-item"
								href = "{{ url(sprintf("/items/delete_item/%s/%d", $order->id, $item->id)) }}">Cancel</a>
						@elseif ($order->store && $order->store->change_items == '1' && $item->item_status == 'cancelled')
							<a style = 'color:gray' href = "{{ url(sprintf("/items/restore_item/%s/%d", $order->id, $item->id)) }}">Restore</a>
						@endif
					</td>
					<td>
						@if (auth()->user()->accesses->where('page', 'orders_admin')->all())
							{!! Form::text("item_description[]", $item->item_description, ['style' => 'width:100%']) !!}
						@else
							<a href = "{{ url($item->item_url ? $item->item_url : '#') }}" target = "_blank">{{$item->item_description}}</a>
							{!! Form::hidden("item_description[]", $item->item_description) !!}
						@endif
						
						<br>
						Item ID: {{ $item->id }}
						<br><br>
						
						@if (count($item->allChildSkus) > 0 && (empty($item->parameter_option) || $item->parameter_option->batch_route_id == 115))
							@setvar($child_skus = array())
							@setvar($child_skus[$item->child_sku] = $item->child_sku)
							@foreach ($item->allChildSkus as $sku)
								@setvar($child_skus[$sku->child_sku] = $sku->child_sku)
							@endforeach
							{!! Form::select("child_sku[]", $child_skus, $item->child_sku, ['class' => 'child_sku']) !!}
						@else
							{!! Form::text("child_sku[]", $item->child_sku, ['class' => 'child_sku']) !!}
						@endif
						/
						<a style = 'color:red'
						   href = "{{ url(sprintf("/logistics/sku_list?search_for_first=%s&contains_first=in&search_in_first=parent_sku", $item->item_code)) }}"
						   target = "_blank">{{$item->item_code}}</a>
					</td>
					<td>
						{!! Form::textarea("item_option[]", \Monogram\Helper::jsonTransformer($item->item_option), ['id' => 'item_option', 'rows' => '3']) !!}
					</td>
					<td>
						@if (auth()->user()->accesses->where('page', 'orders_admin')->all())

							{!! Form::number("item_quantity[]", $item->item_quantity, ['id' => $item->item_id,'class' => 'item_quantity', 'step' => '1', 'min' => '1']) !!}
						@else
							{!! Form::number("item_quantity[]", $item->item_quantity, ['class' => 'item_quantity', 'readonly' => 'true']) !!}
						@endif
					</td>
					<td>
						@if (auth()->user()->accesses->where('page', 'orders_admin')->all())
							{!! Form::number("item_price[]", $item->item_unit_price, ['class' => 'item_price', 'step' => '.01', 'min' => '0']) !!}
						@else
							{!! Form::number("item_price[]", $item->item_unit_price, ['class' => 'item_price', 'readonly' => 'true']) !!}
						@endif
					</td>
					<td>
						@if($item->item_status == 'production')
								@if($item->batch)
									<p>
										View batch:
										<a href = "{{ url(sprintf("/batches/details/%s", $item->batch_number)) }}"
										   target = "_blank">{{ $item->batch_number }}</a>
											 
										@if(isset($item->batch->station))
											<br>
											<span>{{ $item->batch->station->station_name }} {{ $item->batch->station->station_description }} </span> 
												<br>
												{{ $item->batch->change_date }}
										@endif
									</p>
								@elseif ($item->batch_number != '0')
									No Batch Record
								@else
									Unbatched
								@endif
						@elseif ($item->item_status == 'wap')
							@if ($item->wap_item)
								WAP Bin 
								<a href = "{{ url(sprintf("/wap/details?bin=%s", $item->wap_item->bin_id)) }}"
									 target = "_blank">{{ $item->wap_item->bin->name }}</a>
								<br>
								Added:
								<br>
								{{ $item->wap_item->created_at }}
							@else
								WAP BIN: NOT FOUND
							@endif
						@elseif ($item->item_status == 'rejected' && $item->rejection)
							@if ($item->rejection->graphic_status != 'Redo Item')
								Rejected 
							@else
								Redo 
							@endif
							<a href = "{{ url(sprintf("/batches/details/%s", $item->batch_number)) }}"
								 target = "_blank">{{ $item->batch_number }}</a>
							<br>
							@if ($item->rejection->rejection_reason_info)
								{{ $item->rejection->rejection_reason_info->rejection_message }}
								<br>
							@endif
							{{ $item->rejection->rejection_message }}
							<br>
							{{ $item->rejection->created_at }}
						@elseif ($item->item_status == 'rejected' && !$item->rejection)
							REJECTED BUT NO REJECTION RECORD	
						@elseif ($item->item_status == 'back order')
							Back Order 
							@if (!$item->batch)
								Unbatched
							@endif
							
							@if($item->inventoryunit)
								@setvar($eta = 0)
								@foreach($item->inventoryunit as $unit)
									@if($unit->open_po)
										@if ($unit->open_po->eta > $eta)
											@setvar($eta = $unit->open_po->eta)
										@endif
									@endif
								@endforeach
								
								@if ($eta > 0) 
									<br>
									ETA: {{ $eta }}
								@endif
							@endif
						@elseif ($item->item_status != 'shipped')
							{!! ucfirst($item->item_status) !!}
							@if ($item->batch)
								<a href = "{{ url(sprintf("/batches/details/%s", $item->batch_number)) }}"
									 target = "_blank">{{ $item->batch_number }}</a>
								<br>
								{{ $item->batch->change_date }}
							@endif
						@endif

						@if($item->shipInfo && $item->shipInfo->tracking_number)
									<a href="{!! sprintf("/shipping?search_for_first=%s&search_in_first=unique_order_id", $item->shipInfo->unique_order_id) !!}">
									Shipped</a> on {{date("m/d/y", strtotime($item->shipInfo->postmark_date ?: "now" ))}}
									<br>
									@if(!strpos(strtolower($item->shipInfo->mail_class), 'innovation'))
										{{ $item->shipInfo->mail_class }}
										<br>
									@endif
									<a href = "{{ \Monogram\Helper::getTrackingUrl($item->shipInfo->shipping_id) }}" target = "_blank"> Trk# {{ $item->shipInfo->shipping_id }}</a>
									<br>
									@if ($item->batch)
										<a href = "{{ url(sprintf("/batches/details/%s", $item->batch_number)) }}"
											 target = "_blank"> Batch {{ $item->batch_number }}</a>
											 <br>
									@endif
									<br>
									{!! Form::button('Redo Item' , ['id'=>'redo-item', 'onclick' => "redo_item($item->id, $item->item_quantity);", 'class' => 'btn btn-xs btn-danger']) !!}
						@elseif ($item->item_status == 'shipped' && $item->tracking_number != null)
									<a href = "{{ \Monogram\Helper::getTrackingUrl($item->tracking_number) }}" target = "_blank">Trk# {{ $item->tracking_number }}</a>
						@elseif ($item->item_status != 'shipped' && $item->item_status != 'cancelled' && $order->carrier != 'MN')
									<br><br>
									{!! Form::button('Enter Tracking' , ['id'=>'shipitem', 'onclick' => "track_item($item->id, '$item->item_id');", 'class' => 'btn btn-xs btn-success']) !!}
						@endif
					</td>
				</tr>
			@endforeach
			</tbody>
		</table>
		
		<div class = "row">
			@if ($order->store && $order->store->change_items == '1' && $order->order_status != 8)
			<div class = "form-group" style="padding:20px;">
				<label for = "search_sku" class = "col-md-2 control-label">Add Item:</label>
				<div class = "col-md-8">
						{!! Form::text('search_sku', null, ['id'=>'search_sku', 'class' => 'form-control autocomplete', 'placeholder' => 'SKU / Name / Id catalog', 'tabindex' => '14']) !!}
						<br>
				</div>
			</div>
			@endif
		</div>
	
		<div class = "row">
			<table style = "margin-left:775px">
			<tr>
				<td align = "right" style = "padding-right:40px ">Subtotal:</td>
				<td align = "right">$<span id="subtotal">{{sprintf("%02.2f",$sub_total)}}</span></td>
			</tr>
			<tr>
				<td align = "right" style = "padding-right:40px ">Promotion <b>({{ $order->promotion_id }})</b>:</td>
				<td align = "right">$<span id="promotion_value">{!!sprintf("%02.2f",$order->promotion_value)!!}</span></td>
			</tr>
			<tr>
				<td align = "right" style = "padding-right:40px ">Coupon <b>({{ $order->coupon_id }})</b>:</td>
				<td align = "right">$<span id="coupon_value">{!!sprintf("%02.2f",$order->coupon_value)!!}</span></td>
			</tr>
			<tr>
				<td align = "right" style = "padding-right:40px ">Gift Wrap:</td>
				<td align = "right">{!! Form::number('gift_wrap_cost', sprintf("%02.2f",$order->gift_wrap_cost), ['id' => 'gift_wrap_cost', 'step' => '.01', 'min' => '0', 'style'=>'width:60px']) !!}</td>
			</tr>
			<tr>
				<td align = "right" style = "padding-right:40px ">Shipping:</td>
				<td align = "right">$<span id="shipping_charge">{{sprintf("%02.2f",$order->shipping_charge)}}</span></td>
			</tr>
			<tr>
				<td align = "right" style = "padding-right:40px ">Insurance:</td>
				<td align = "right">$ {!! Form::number('insurance', sprintf("%0.2f", $order->insurance), ['id' => 'insurance', 'step' => '.01', 'min' => '0', 'style'=>'width:60px']) !!}</td>
			</tr>
			<tr>
				<td align = "right" style = "padding-right:45px ">Adjustments:</td>
				<td align = "right">$ {!! Form::number('adjustments', sprintf("%02.2f",$order->adjustments), ['id' => 'adjustments', 'step' => '.01', 'style'=>'width:60px']) !!}</td>
			</tr>
			<tr>
				<td align = "right" style = "padding-right:45px ">Tax:</td>
				<td align = "right">$<span id="tax_charge">{{sprintf("%02.2f",$order->tax_charge)}}</span></td>
			</tr>
			<tr>
				<td align = "right" style = "padding-right:45px ">Total:</td>
				<td align = "right">$<span id="total">{{sprintf("%02.2f",$order->total)}}</total></td>
			</tr>
			<tr>

			</tr>
		</table>
		<div align = "right">
			<button type = "submit" class = "btn btn-primary">Update Order</button>
		</div>
		<hr style = "width: 100%; color: black; background-color:black;margin-top:  10px" size = "1" />
		<table class = "table" >
			<thead>
				<td><p style = "color:#686869;border: 1px solid;padding:4px 540px 4px 10px ">
						<b>Customer Interactions</b> <a style = 'color:#ff8001' href = "#"
						                                id = 'add-note'>(add a note/reminder)</a>
					</p>
				</td>
			<tr style = "display: none;">
				<td>
					{!! Form::textarea('note', null, ['id' => 'note', 'placeholder' => 'Add a note']) !!}
					<br />
					{!! Form::submit('add note', ['id' => 'instant-add-note', 'class' => 'btn btn-link', 'style' => 'display: none;']) !!}
				</td>
			</tr>
			</thead>
		</table>
		<table id = "detailsTbl" class = "table" >
		<thead>
			<tr>
				<td>
					Date and Action By
				</td>
				<td>
					Note
				</td>
			</tr>
		</thead>
    <tbody>
			@foreach($order->notes as $note)
				@setvar($count = 0)
				@setvar($lines = explode("\n", $note->note_text))
				<tr>
					<td>
						({{$note->created_at}}) by: {{$note->user->username}}
					</td>
					<td>
					@foreach ($lines as $line)
						{{ $line }}
						@if(count($lines) > 1)
							<br>
						@endif
					@endforeach
					</td>
				<tr>
			@endforeach
    </tbody>
		</table>


		<table style = "margin-bottom: 30px;">
			<tr>
				<td>
					<a href = "{{ url(sprintf('orders/packing/%s', $order->id)) }}">Print Packing slip</a>
					 | <a href = "{{ url(sprintf('orders/addmanual/%s', $order->id)) }}">Manual Re-order</a>
				</td>
			</tr>
		</table>
	</div>
	</div>
	{!! Form::close() !!}
	
	</div>
	
	<div class = "modal fade" id = "status-modal" tabindex = "-1" role = "dialog" aria-labelledby = "myModalLabel">
		<div class = "modal-dialog modal-md" role = "document">
			<div class = "modal-content">
				<div class = "modal-header">
					<button type = "button" class = "close" data-dismiss = "modal" aria-label = "Close">
						<span aria-hidden = "true">&times;</span></button>
					<h4 class = "modal-title" id = "myModalLabel">Why are you changing the status of this order?</h4>
				</div>
				<div class = "modal-body">
					{!! Form::open(['name' => 'status', 'url' => 'orders/status_change/', 'method' => 'post', 'id' => 'status']) !!}
					{!! Form::hidden('order', $order->id, ['id' => 'order']) !!}
					{!! Form::hidden('new_status', '', ['id' => 'new_status']) !!}
					{!! Form::hidden('current_status', $order->order_status, ['id' => 'current_status']) !!}
					<div class="form-group">
						{!! Form::text('status_note', '', ['id' => 'status_note', 'class' => 'form-control', 'placeholder' => 'Enter Reason']) !!}
					</div>
					<div class="form-group">
						<div class="text-right">
							{!! Form::submit('Update' , ['class' => 'btn btn-sm btn-success']) !!}
						</div>
					</div>
					{!! Form::close() !!}
				</div>
			</div>
		</div>
	</div>
	
	<div class = "modal fade" id = "ship-modal" tabindex = "-1" role = "dialog" aria-labelledby = "myModalLabel">
		<div class = "modal-dialog modal-md" role = "document">
			<div class = "modal-content">
				<div class = "modal-header">
					<button type = "button" class = "close" data-dismiss = "modal" aria-label = "Close">
						<span aria-hidden = "true">&times;</span></button>
					<h4 class = "modal-title" id = "myModalLabel">Manual Shipping Instructions</h4>
				</div>
				<div class = "modal-body">
					{!! Form::open(['name' => 'ship_method', 'url' => 'ship_order/update_method', 'method' => 'post', 'id' => 'ship_method']) !!}
					{!! Form::hidden('id', $order->id, ['id' => 'order']) !!}
					{!! Form::hidden('method', 'MN') !!}
					<div class="form-group">
						{!! Form::text('method_note', '', ['id' => 'method_note', 'class' => 'form-control', 'placeholder' => 'Enter instructions']) !!}
					</div>
					<div class="form-group">
						<div class="text-right">
							{!! Form::submit('Update' , ['class' => 'btn btn-sm btn-success']) !!}
						</div>
					</div>
					{!! Form::close() !!}
				</div>
			</div>
		</div>
	</div>
	
	<div class = "modal fade" id = "track-modal" tabindex = "-1" role = "dialog" aria-labelledby = "myModalLabel">
	  <div class = "modal-dialog modal-sm" role = "document">
	    <div class = "modal-content">
	      <div class = "modal-header">
	        <button type = "button" class = "close" data-dismiss = "modal" aria-label = "Close">
	          <span aria-hidden = "true">&times;</span></button>
	        <h4 class = "modal-title" id = "myModalLabel">Enter Tracking Number</h4>
	      </div>

	      <div class = "modal-body">
					{!! Form::open(['name' => 'track', 'url' => '/ship_order/item_tracking', 'method' => 'get', 'id' => 'track']) !!}
					{!! Form::hidden('track_item_id', '', ['id' => 'track_item_id']) !!}
			 		{!! Form::hidden('track_order_id', $order->id, ['id' => 'track_order_id']) !!}
                    {!! Form::hidden('track_shopify_order_id', $order->short_order, ['id' => 'track_shopify_order_id']) !!}
			 		{!! Form::hidden('track_shopify_item_line_id','', ['id' => 'track_shopify_item_line_id']) !!}
                    {!! Form::hidden('track_shopify_item_quantity','', ['id' => 'track_shopify_item_quantity']) !!}
					{!! Form::select('method', $shipping_methods, '', ['id' => 'method', 'class' => 'form-control', 'placeholder' => 'UPS MAIL INNOVATIONS']) !!}
					{!! Form::text('track_number', '', ['id' => 'track_number', 'class' => 'form-control', 'placeholder' => 'Tracking Number']) !!}
					{!! Form::submit('Ship Item' , ['class' => 'btn btn-sm btn-success']) !!}
					{!! Form::close() !!}
	      </div>
	    </div>
	  </div>
	</div>

	<div class = "modal fade" id = "redo-modal" tabindex = "-1" role = "dialog" aria-labelledby = "myModalLabel">
	  <div class = "modal-dialog modal-sm" role = "document">
	    <div class = "modal-content">
	      <div class = "modal-header">
	        <button type = "button" class = "close" data-dismiss = "modal" aria-label = "Close">
	          <span aria-hidden = "true">&times;</span></button>
	        <h4 class = "modal-title" id = "myModalLabel">Produce Item Again</h4>
	      </div>
	      <div class = "modal-body">
					{!! Form::open(['name' => 'redo', 'url' => '/customer_service/redo', 'method' => 'post', 'id' => 'redo_form']) !!}
					{!! Form::hidden('item_id', '', ['id' => 'redo_item_id']) !!}
					<div class = "form-group">
	          {!! Form::select('reason_to_reject', App\RejectionReason::getReasons(), null, ['id' => 'reason-to-reject', 'class' => 'form-control']) !!}
	        </div>
					<div class = "form-group" id="qty-group">
						 Quantity: {!! Form::number('redo_quantity', 1, [ 'id' => 'redo_item_qty',  'min' => '1']) !!}
					</div>
					<div class = "form-group">
						{!! Form::text('redo_reason', '', ['id' => 'redo_reason', 'class' => 'form-control', 'placeholder' => 'Explanation']) !!}
					</div>
					<div class = "form-group">
						{!! Form::submit('Redo Item' , ['class' => 'btn btn-sm btn-danger']) !!}
					</div>
					{!! Form::close() !!}
	      </div>
	    </div>
	  </div>
	</div>
	
	@include('email_templates.email_modal')
	
	<div class = "row" id = "modal-holder"> 
	</div> 
	
	<script type = "text/javascript">
		
		// @if ($order->store && $order->store->change_items == '0')
		// 	$(document).ready(function() {
		// 		$(".item_quantity").prop("readonly", true); 
		// 		$(".item_price").prop("readonly", true);
		// 	});
		// @endif
		
		function change_status () {
			var choice = $("#status_selector").val();
			$("#new_status").val(choice);
			$("#status-modal").modal('show');
		}
		
		function change_shipmethod (form) {
			if ($("#shipping_method").val() == 'MN*') {
				$("#ship-modal").modal('show');
			} else {
				$( form ).submit();
			}
		}
		function getItemQuantity(id) {
			const quantity = $('#' + id).val();
			return quantity;
        }
		function track_item (id, item_id)
		{ 
			$("#track_item_id").val(id);
			$("#track_shopify_item_line_id").val(item_id);
			const quantity = getItemQuantity(item_id);
			$("#track_shopify_item_quantity").val(quantity);
			$("#track-modal").modal('show');
		}
		
		function redo_item(id, qty)
		{ 
			$("#redo_item_id").val(id);
			if (qty > 1) {
				$("#redo_item_qty").val(qty);
				$("#qty-group").show();
			} else {
				$("#qty-group").hide();
			}
			$("#redo-modal").modal('show');
		}
		
		$("a#add-note").on('click', function (event)
		{
			event.preventDefault();
			$("textarea#note").closest('tr').show();
			$(this).hide();
			$("#instant-add-note").show();
		});
		
		$('.delete-item').click(function(event) 
		{
			event.preventDefault();
			if (confirm("Are you sure want to cancel this item?"))   {  
				window.location = $(this).attr('href');
			}
		});
	</script>

	<script type = "text/javascript">
		var picker = new Pikaday(
		{
				field: document.getElementById('ship_date'),
				format : "YYYY-MM-DD",
				minDate: new Date('{{ date('Y-m-d') }}'),    
		});
	</script>

	<script type = "text/javascript">
		$('#search_sku').autocomplete({
				minChars: 4,
				serviceUrl: '/orders/manual/ajax_search',
				onSelect: function (suggestion) {
						addItem(suggestion.data, suggestion.id_catalog, suggestion.desc, suggestion.image, suggestion.price, 1);
						$(this).val(''); 
						return false;
				}
		});
		
		function addItem (sku, id_catalog, desc, image, price, quantity) 
		{	
				var unique = Math.floor(Math.random() * 100);
				
				var tr = "<tr id='" + unique + "' id_catalog='" + id_catalog + "' data-sku='" + sku + "' class='item-line'>" +
								"<td> <img src='" + image + "' width='100' /> </td>" + 
								"<td>" + sku + "<br>" + desc + "</td>" +
								"<input type='hidden' name='item_sku[]' value='" + sku + "'>" +
								"<input type='hidden' name='item_id[]' value=''>" +
								"<input type='hidden' name='child_sku[]' value=''>" +
								"<td><textarea name='item_option[]' class='item_option' cols=50 rows=3 wrap='physical'></textarea></td>" +
								"<td><input type='number' name='item_quantity[]' class='item_quantity' step=1 min=1 onChange='updateTotals();' value=" + quantity + "></td>" + 
								"<td><input type='number' name='item_price[]' class='item_price' step=.01 min=0 onChange='updateTotals();' value=" + price + "></td>" + 
								"<td>" + "<a href='#' id='crawl' class='btn btn-xs btn-primary' style='width:75px;' onClick='return false;'>Customize</a>" +
								"<br><br>" + 
								"<a href='#' class='delete-row btn btn-xs btn-danger' style='width:75px;' onClick='return false;'>Remove</a>" +
								"</td></tr>";

			$("#items-table").append(tr);
			
			updateTotals();
		}
		
		$(document).on('click', '.delete-row', function (event)
		{
			event.preventDefault();
			if ( confirm("Are you sure want to delete?") ) {
				$(this).closest('tr').remove();
			}
		});
		
		function ajax (url, method, data, successHandler, errorHandler)
		{
			NProgress.start();
			$.ajax({
				url: url, method: method, data: data, success: function (data, status)
				{
					NProgress.done();
					successHandler(data);
				}, error: function (xhr, status, error)
				{
					NProgress.done();
					errorHandler(xhr);
				}
			})
		}

		$(document).on('click', "#crawl", function ()
		{
			var store_id = '{{ $order->store_id}}';
			var row = $(this).closest("tr");
			var sku = row.attr('data-sku');
			var id_catalog = row.attr('id_catalog');
			var unique = row.attr('id');
			
			var url = "/orders/manual/product_info";
			
			var data = {
				"sku": sku, "store_id": store_id, "id_catalog": id_catalog, "unique": unique
			};
		
			var method = "GET";

			ajax(url, method, data, fetchProductInformationOnSelect, showProductInformationFetchFailed);

		});
		
		function showProductInformationFetchFailed (xhr)
		{
			alert("Product not found or Something went wrong!");
		}
		
		function fetchProductInformationOnSelect (data) 
		{ 
			var result = data.result; 
			if ( result == false ) { 
				alert('Something went wrong!'); 
			} else { 
				$("#modal-holder").html(result); 
				var unique = data.unique; 
				$("." + unique).modal({ 
					backdrop: 'static', keyboard: false, show: true 
				}); 
			} 
		} 
		
		$(document).on('click', 'button.add-item', function () 
		{ 	
				var body = $(this).closest('div.modal-content').find('div.modal-body'); 
				var unique = body.find('.hidden_unique').val(); 
				var options = '';
				body.find('.option-field').each( function(label,value) {
						options = options + $(this).attr("name") + ' = ' + $(this).val() + "\n";
				});
				 $("#" + unique).find(".item_option").val(options);
				body.closest('.modal').modal('hide'); 
				body.remove();
				body.empty();
		}); 
		
		$(document).on('click', '.cancel', function () 
		{ 
			$(this).closest('div.modal-content').find('div.modal-body').remove();
		}); 
		
		function updateTotals() 
		{
			var subtotal = 0;
			
			$("#items-table tr").each( function() {
					if ($(this).attr("class") == 'item-line') {
						var quantity = $(this).find(".item_quantity").val();
						var price = $(this).find(".item_price").val();
						subtotal = parseFloat(subtotal) + (parseInt(quantity) * parseFloat(price));
					}
			});
			
			$("#subtotal").text(Math.round(subtotal*100)/100);
			
			var promotion = $("#promotion_value").text();
			var coupon = $("#coupon_value").text();
			var gift = $("#gift_wrap_cost").val();
			var shipping = $("#shipping_charge").text();
			var insurance = $("#insurance").val();
			var adjustments = $("#adjustments").val();
			var tax = $("#tax_charge").text();

			var total = parseFloat(subtotal) - 
									parseFloat(coupon) -
									parseFloat(promotion) +
									parseFloat(gift) + 
									parseFloat(shipping) + 
									parseFloat(insurance) + 
									parseFloat(adjustments) + 
									parseFloat(tax);
			
			$("#total").text(Math.round(total*100)/100);
		}
		
		$("input[type=number]").bind('keyup input', function(){
			updateTotals();
		});
	</script>
</body>
</html>