<!doctype html> 
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>View Batches</title>
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
			<li>View Batches</li>
		</ol>

		@include('includes.error_div')
		@include('includes.success_div')
		
		@include('backorders.includes.scan')
				
		<div class = "col-xs-12">
					
		@if(count($batch_views) > 0)
			
			@foreach ($batch_views as $batch)
				
				<h3><a href = "{{ url(sprintf('batches/details/%s',$batch->batch_number)) }}" target="_blank">{{ $batch->batch_number }}</a></h3>
				
				<div class="row">
						<div class = "form-group col-xs-1"></div>
						<div class = "form-group col-xs-2">
							<strong>
							Batch Status:
							<br>
							Current Station:
							<br>
							Graphic:
						</strong>
						</div>
						<div class = "form-group col-xs-4">
							{{ ucfirst($batch->status) }}
							<br>
							{{ $batch->station->station_name }} => {{ $batch->station->station_description }}							
							<br>
							{{ $batch->graphic_found }}
						</div>
						
				</div>
				
				@if ($batch->status != 'empty' && $batch->status != 'complete')
				<div class="row">
					@if ($batch->status == 'back order')
						<div class = "form-group col-xs-2">
							{!! Form::open(['name' => 'release', 'url' => '/backorders/arrive', 'method' => 'post', 'id' => 'release']) !!}
							{!! Form::hidden('action', 'release') !!}
							{!! Form::hidden('batch_number', $batch->batch_number) !!}
							{!! Form::submit('Release Backorder', ['id' => 'release', 'class' => 'btn btn-info']) !!}
							{!! Form::close() !!}
						</div>
						@if ($batch->section_id == 6 && $batch->graphic_found == 'Found' && $batch->summary_date == null)
							<div class = "form-group col-xs-2">
								{!! Form::open(['url' => 'summaries/single', 'method' => 'get', 'target' => '_blank',
																					'onsubmit' => "setTimeout(function () { window.location.reload(); }, 500)"]) !!}  
								{!! Form::hidden('batch_number', $batch->batch_number) !!}
								{!! Form::submit('Print Summary', ['class' => 'btn btn-success']) !!}
								{!! Form::close() !!}
							</div>
						@endif
					@else
						<div class = "form-group col-xs-3">
							{!! Form::open(['name' => 'backorder', 'url' => '/backorders/items', 'method' => 'post', 'id' => 'backorder']) !!}
							{!! Form::hidden('action', 'backorder') !!}
							{!! Form::hidden('batch_number', $batch->batch_number) !!}
							{!! Form::submit('Back Order Selected Items', ['id' => 'backorder', 'class' => 'btn btn-warning']) !!}
						</div>
					@endif		
				</div>
				
					<table class="table">
						<thead>
						<tr>
							@if ($batch->status != 'back order')
								<th style="width:30px;">
									<input type="checkbox" name="bo_all" id="bo_all" class="checkbox">	
								</th>
								<th>Select All</th>
							@else
								<th>Order</th>
							@endif
							<th>Item</th>
							<th>Quantity</th>
							<th>Image</th>
							<th>Product</th>
						</tr>
					</thead>
					<tbody>
						
						@foreach($batch->items as $item)
					
									<tr class="lines">
										<td>
										@if ($item->item_status == 'production')
											
												<input type = "checkbox" name = "items[]" class = "bo_checkbox"
															 value = "{{ $item->id }}" />
											
										@endif
										</td>
										<td>
												<a href = "{{ url("orders/details/".$item->order_5p) }}"
													 target = "_blank">
													 	@if (isset($item->order))
															{{ $item->order->short_order }}</a>
														@else
															{{ $item->order_id }}</a>
														@endif
												<br>
												5p Order: <a href = "{{ url("orders/details/".$item->order_5p) }}"
												   target = "_blank">{{ $item->order_5p }}</a>
										</td>
										<td>
												{{ $item->id }}<br>
												Status: {{ $item->item_status }}
										</td>
										<td style="text-align:center;">
											{{ $item->item_quantity }}
										</td>
										<td height="80">
											@if ($item->item_thumb != NULL)
												<img  border = "0" style="height: auto; width: 2cm;" src = "{{ $item->item_thumb }}" />
											@endif
										</td>
										<td>
											{{ $item->item_description }}<br>
											SKU: {{ $item->child_sku }}
										</td>
									</tr>
									
						@endforeach
						
						{!! Form::close() !!}
						
					</tbody>
					</table>
					
					<br><br>
				
				@else 
				
					<div class = "alert alert-danger text-center">Batch is {!! ucfirst($batch->status) !!}</div>
					
				@endif
				
				<br>
			@endforeach
			
		@else
			
			<div class = "alert alert-warning text-center">No Batch found</div>
			
		@endif
		
		</div>
	</div>
</body>
</html>

	<script type = "text/javascript">
	
		var state = false;

		$("#bo_all").on('click', function ()
		{
			state = !state;
			$(".bo_checkbox").prop('checked', state);
		});
		
	</script>
