<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Scan Work</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>

	<script src = "/assets/js/DYMO.Label.Framework.latest.js" type="text/javascript" charset="UTF-8"> </script>
	<script src = "/assets/js/dymoBarcode.js" type="text/javascript"> </script>
	<style>
		table {
			font-size: 14px;
		}

		td {
			word-wrap:break-word
		}
		
		tr.finished {
			background-color:#78909C;
		}
	</style>
</head>
<body>
	@include('includes.header_menu')
	<div class = "container" style="min-width: 1400px;"">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "{{url('/production/status')}}">Production Stations</a></li>
			<li class = "active">
				@if (isset($station))
					<a href = "{{ url(sprintf('/production/status_detail?station=%s',$station->id)) }}">
					{{ $station->station_name }}</a>
				@else
					Station Not Found
				@endif
			</li>
			<li class = "active">
				@if ($batch)
					Batch {{ $batch->batch_number }}
				@else
					Scan Work
				@endif
			</li>
		</ol>
		
		@include('includes.error_div')
		@include('includes.success_div')

		<div class = "col-xs-12">
				{!! Form::open(['method' => 'post']) !!}
				{!! Form::hidden('task', 'scan') !!}
				{!! Form::hidden('from', 'scanWork') !!}
				<div class = "form-group col-xs-2">
					{!! Form::text('batch_number', '', ['id'=>'batch_barcode', 'class' => 'form-control', 'placeholder' => 'Enter Batch']) !!}
				</div>
				<div class = "form-group col-xs-2">
					{!! Form::password('user', ['id'=>'user_barcode', 'class' => 'form-control', 'autocomplete' => "new-password"]) !!}
				</div>
				<div class = "form-group col-xs-2">
					{!! Form::submit('Scan', ['id'=>'search', 'style' => 'margin-top: 0px;', 'class' => 'btn btn-primary btn-sm']) !!}
				</div>
				{!! Form::close() !!}
				<div class = "form-group col-xs-3"></div>
		</div>
		
		@if (count($batch) != 0)
		
			<div class = "col-xs-12">
				<h3 class="page-header">
					<a href = "{{ url(sprintf('batches/details/%s',$batch->batch_number)) }}"
					target = "_blank">Batch {{ $batch->batch_number }}</a>
					<div class="pull-right">
						<small>{{ $batch->route->batch_code }} / {{ $batch->route->batch_route_name }} =>
										{!! $stations !!}</small>
					</div>
				</h3>
			</div>
			

			@if ($message)
				@if($status == 'IN')
					<div class="col-xs-12 alert alert-warning">
				@elseif($status == 'MOVED')
					<div class="col-xs-12 alert alert-success">
				@else
					<div class="col-xs-12 alert alert-info">
				@endif
						<div class="col-xs-2">
							<h4> {{ $message[0] }} </h4>
						</div>
						<div class="col-xs-5">
							<h4> {{ $message[1] }} </h4>
						</div>
						<div class="col-xs-2">
							@if ($status == 'IN' && $batch->section->start_finish == '1')
								{!! Form::open(['method' => 'post', 'id' => 'finish_form']) !!}
								{!! Form::hidden('batch_number', $batch->batch_number) !!}
								{!! Form::hidden('user', $user) !!}
								{!! Form::hidden('task', 'finish') !!}
								{!! Form::hidden('from', 'scanWork') !!}
								{!! Form::button('Finish Work' , ['id'=>'finish', 'style' => 'margin-top: 0px;', 'class' => 'btn btn-primary']) !!}
								{!! Form::close() !!}
							@elseif ($batch->section->start_finish == '0' && $batch->section->print_label == '1')
								<a href = "#" class="btn btn-success" 
									onClick = "print_tray_label('{{ $batch->batch_number }}', '{{ $batch->items->sum("item_quantity") }}',  
																							'{{ substr( $batch->min_order_date ?? $batch->creation_date, 0, 10) }}')"> 
									Print Batch Label</a>
							@endif
						</div>
						<div class="col-xs-1">
							@if ($status == 'IN' ||  $status == 'MOVED')
								{!! Form::open(['method' => 'post']) !!}
								{!! Form::hidden('batch_number', $batch->batch_number) !!}
								{!! Form::hidden('user', $user) !!}
								{!! Form::hidden('from', 'scanWork') !!}
								@if ($status == 'IN')
									{!! Form::hidden('task', 'undoScan') !!}
								@elseif ($status == 'MOVED') 
									{!! Form::hidden('task', 'undoMove') !!}
									{!! Form::hidden('prev_station', $prev_station) !!}
								@endif
								{!! Form::submit('Undo' , ['id'=>'undo', 'style' => 'margin-top: 0px;', 'class' => 'btn btn-danger']) !!}
								{!! Form::close() !!}	
							@endif
						</div>
						<div class="col-xs-2">
							<a href = "{{ url(sprintf('/production/status_detail?station=%s',$station->id)) }}" class="btn btn-primary">
							Choose Another Batch</a>
						</div>
					</div>
			@endif
				
				<div class = "col-xs-12">
					<table class="table" id="batch-items-table">
						<tbody>
						@foreach($batch->items as $item)
							<tr item-id="{{$item->id}}" class="{{$item->id}}">
								<td colspan=4>
									<strong style="font-size: 120%;">{{ $item->item_description }}</strong>
								</td>
							</tr>
							<tr item-id="{{$item->id}}" class="{{$item->id}}">
								<td width=125>
									Order# <a href = "{{url(sprintf('/orders/details/%s', $item->order->id))}}"
										 target = "_blank">{{ $item->order->short_order }}</a>
									<br>
									Item# {{$item->id}}
									<br>
									@if ($item->item_quantity != 1)
										<strong style="font-size:150%">QTY: {{$item->item_quantity}}</strong>
									@endif
									<img src = "{{ $item->item_thumb }}" width="90" height="90" />
								</td>
								<td>
									@setvar($thumb = \Monogram\Sure3d::getThumb($item))
									@if($thumb)
										<img src = "{{ $thumb[0] }}" width="{{ $thumb[1] }}" height="{{ $thumb[2] }}">
									@endif
								</td>
								<td align="left">
									<table class="table">
										<tr>
											<td>SKU</td>
											<td><strong>{{ $item->child_sku }}</strong></td>
										</tr>
										@foreach ( json_decode($item->item_option, true) as $optionKey => $optionValue )
											@if($optionKey != 'Confirmation_of_Order_Details' && $optionKey != 'couponcode')
												<tr>
													<td>{{ str_replace('_', ' ', str_replace (['Select', 'Choose'], '', $optionKey)) }}</td>
													<td><strong>{{ $optionValue }}</strong></td>
												</tr>
											@endif
										@endforeach
										
									</table>
								</td>
								<td align="left" width=400>
									<strong>Inventory:</strong>
									<br>
									@if ($item->inventoryunit)
										@foreach ($item->inventoryunit as $unit)
											@if ($unit->stock_no_unique != 'ToBeAssigned')
												@if (intval($unit->unit_qty * $item->item_quantity) != 1)
													<strong style="font-size:150%">{{ intval($unit->unit_qty * $item->item_quantity) }}</strong>  
												@endif
												{{ $unit->stock_no_unique }} 
												@if ($unit->inventory)
													@if ($unit->inventory->wh_bin != null && $unit->inventory->wh_bin != '0')
														- {{ $unit->inventory->wh_bin }}
													@endif
													{!! \App\Task::widget('App\Inventory', $unit->inventory->id, null, 15); !!}
													<br>
													{{ $unit->inventory->stock_name_discription }}<br>
													
												@endif
											@endif
										@endforeach
									@endif
									<br>
									@if ($item->spec_sheet)
										<a href = "{{ url(sprintf('/products_specifications/%s', $item->spec_sheet->id)) }}" 
												target = "_blank">Production Instruction</a>
										<br>
									@endif
								</td>
							</tr>
						@endforeach
						</tbody>
					</table>
				</div>
				</table>
			
			@endif
	</div>
	
	<script type = "text/javascript">
			
	$(function() {
			// Focus on load
			$('#batch_barcode').focus();
			
			$('#batch_barcode').bind('keypress keydown keyup', function(e){
	       if(e.keyCode == 13) { 
					 e.preventDefault(); 
					 $('#user_barcode').focus();
				 }
	    });
			
			$('#finish').click(function(e) {
					
					e.preventDefault();
					
					@if ($batch && $batch->station_id == $batch->production_station_id)
						print_tray_label('{{ $batch->batch_number }}', '{{ $batch->items->sum("item_quantity") }}', 
																			'{{ substr( $batch->min_order_date ?? $batch->creation_date, 0, 10) }}');
					@endif
					
					$('#finish_form').submit();
				});
	});
	
	$(document).ready(function () {
    $('tr').click(function () {
				var item = '.' + $(this).attr('item-id'); 
        if($(item).hasClass('finished')) {
            $(item).removeClass('finished');
        } else {
            $(item).addClass('finished');
        }
    });
});


	</script>
	
</body>
</html>