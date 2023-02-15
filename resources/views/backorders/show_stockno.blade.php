<!doctype html> 
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>View Stock Number</title>
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
			<li><a href = "/backorders">Back Orders</a></li>
			<li>View Stock Number</li>
		</ol>

		@include('includes.error_div')
		@include('includes.success_div')
		
		@include('backorders.includes.scan')
				
		<div class = "col-xs-12">
					
		@if(count($items) > 0)
					
					<table class="table">
						<thead>
						<tr>
							<th colspan=2>Stock Number</th>
							<th>Quantity</th>
							<th>Current Status</th>
							<th width=250>Location</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						
					@foreach ($items as $item)
					
											<tr class="lines">
												<td>
													@if ($item->warehouse != NULL)
														<img  border="0" height="40" src = "{{ $item->warehouse }}" />
													@endif
												</td>
												<td>
													{{ $item->stock_no_unique }}
													<br>
													{{ $item->stock_name_discription }}
												</td>
												<td style="text-align:center;">
													{{ $item->qty }}
												</td>
												<td>
													{{ $item->item_status }}
												</td>
												<td>
														@if ($item->station_id != null)
															{{ $item->station_description }}
														@else
															Unbatched
														@endif
												</td>
												<td>
													{!! Form::open(['name' => 'backorder', 'url' => '/backorders/stock_change', 'method' => 'post', 
																					'onsubmit' => "return confirm('Are you sure?');"]) !!}
													{!! Form::hidden('stock_no_unique', $search_for) !!}
													{!! Form::hidden('item_status', $item->item_status) !!}
													{!! Form::hidden('station_id', $item->station_id) !!}
													@if ($item->item_status == 'production')
														{!! Form::submit('Back Order Items', ['id' => 'backorder', 'class' => 'btn btn-warning']) !!}
													@elseif ($item->item_status == 'back order')
														{!! Form::submit('Mark Arrived', ['id' => 'backorder', 'class' => 'btn btn-info']) !!}
													@else
														UNRECOGNIZED STATUS
													@endif
													{!! Form::close() !!}
												</td>
											</tr>
						@endforeach
					</tbody>
					</table>
				
		@else
			
			<div class = "alert alert-warning text-center">No items found</div>
			
		@endif
		
		</div>
	</div>
</body>
</html>

	<script type = "text/javascript">
	

	</script>
