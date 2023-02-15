<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Must Ship</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
</head>
<body>
	@include('includes.header_menu')
	<div class = "container">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "{{url('shipping/must_ship')}}">Must Ship</a></li>
		</ol>

			<h3 class = "page-header">
				Orders By Ship Date
				<div class = "pull-right">
			 		{!! Form::open(['name' => 'store_form', 'method' => 'get', 'id' => 'store_form']) !!}          
					<div class = "form-group col-xs-6">
						{!! Form::select('store_id', $stores, $store_id, ['id'=>'store_id', 'class' => 'form-control']) !!}
					</div>
					<div class = "form-group col-xs-6">
						{!! Form::submit('Filter by Store', ['id' => 'store_button', 'class' => 'btn btn-primary btn-sm form-control']) !!}
					</div>
					{!! Form::close() !!}
				</div>
			</h3>
			
			@if(count($orders) > 0)
				<table id="summary_table" class="table" cellspacing="0" cellpadding="0">
				<tbody>
						@foreach($orders as $order)
							@if($order->ship_date > date("Y-m-d"))
								<tr bgcolor="#F8F9F9">
							@else
								<tr class="danger">
							@endif
								<td width=100>
									Ship {{ substr($order->ship_date,5) }}
								</td>
								<td width=150>
									<a href = "{{url(sprintf("orders/details/%s", $order->id))}}" target = "_blank">{{ $order->short_order }}</a>
								</td>
								<td width=400>
									@if ($order->store)
										{{ $order->store->store_name }}
									@else 
										STORE {{ $order->store_id }} NOT FOUND
									@endif
								</td>
								<td colspan=2>
									@if ($order->customer)
										{{ $order->customer->ship_full_name }}
									@else 
										CUSTOMER NOT FOUND
									@endif
								</td>
								<td>
									@if (isset($statuses[$order->order_status]))
										{{ $statuses[$order->order_status] }}
									@else 
										STATUS NOT FOUND
									@endif
								</td>
							</tr>
							@foreach($order->items as $item)
								<tr>
									<td></td>
									<td>
										<img src="{{ $item->item_thumb }}" height=50>
									</td>
									<td>
										{{ $item->item_description }}
									</td>
									<td>
										@if ($item->batch_number != '0')
											<a href="{{ url(sprintf('batches/details/%s',$item->batch_number)) }}"
													 target = "_blank">{{ $item->batch_number }}</a>
										@else 
											Unbatched
										@endif
									</td>
									<td colspan=2>
										@if ($item->batch && $item->batch->station && $item->item_status == 'production')
											{{ $item->batch->station->station_name }} => {{ $item->batch->station->station_description }}
										@elseif ($item->item_status != 'production')
											{{ ucfirst($item->item_status) }}
										@endif
										<br>
										@if ($item->batch)
											Last Scan: {{ $item->batch->change_date }}
										@endif
									</td>
								</tr>
							@endforeach
						@endforeach
				</tbody>
				</table>
			@else
				<div class = "col-xs-12">
					<div class = "alert alert-warning">No Orders.</div>
				</div>
			@endif
	</div>

</body>
</html>