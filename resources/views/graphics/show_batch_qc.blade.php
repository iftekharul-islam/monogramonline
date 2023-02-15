<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Move to Production</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	@if ($label != null)
		@include('prints.includes.label')
	@endif
</head>
<body>
	@include('includes.header_menu')
	<div class = "container" style="width:95%;">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li class = "active"><a href="/move_to_qc">Move to QC</a></li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')

		<script>
			var audio = new Audio('/assets/sound/beep.mp3');
			audio.play();
		</script>		
		
		<div class = "col-xs-12">
			
			<div class = "col-xs-12">
				<div class="form-group">
					{!! Form::open(['name' => 'barcode_form', 'url' => '/move_to_qc/show_batch', 'method' => 'post', 'id' => 'barcode_form']) !!}
						<div class = "form-group col-xs-3">
							{!! Form::text('scan_batches', '', ['id'=>'barcode', 'class' => 'form-control', 'placeholder' => 'Enter Batch']) !!}
						</div>
						<div class = "form-group col-xs-2">
							{!! Form::submit('Scan Batch', ['id'=>'move_button', 'class' => 'btn btn-primary btn-sm form-control']) !!}
						</div>
					{!! Form::close() !!}
			 </div>
		 </div>
		 
		 <div class="row"><div class = "col-xs-12">&nbsp;</div></div>
		 
		@if(count((array)$to_move) > 0)
			
			<div class="row">
				
					<div class = "col-xs-4">
						<h3>
							<a href = "{{ url(sprintf('batches/details/%s',$to_move->batch_number)) }}" 
									target="_blank">Batch {{ $to_move->batch_number }}</a>
						</h3>
					</div>
					<div class = "col-xs-6">
						<h3>
							Moved to {{ $to_move->station->station_description }}
						</h3>
					</div>
					<div class = "col-xs-2">
							@if ($to_move->summary_date == null)
									{!! Form::open(['url' => 'summaries/single', 'method' => 'get', 'target' => '_blank',
																						'onsubmit' => "setTimeout(function () { window.location.reload(); }, 500)"]) !!}  
									{!! Form::hidden('batch_number', $to_move->batch_number) !!}
									{!! Form::submit('Print Summary', ['class' => 'btn btn-success btn-sm form-control']) !!}
									{!! Form::close() !!}
							@else
									Summary Printed by {{ $to_move->summary_user->username }} <br>{{ $to_move->summary_date }}
							@endif
					</div>
				
				<div class="row"><div class = "col-xs-12">&nbsp;</div></div>
				
				<table class="table" id="items-table">
					<tbody>
					@setvar($col = 0)
					
					@foreach($to_move->items as $item)
						@if ($col == 0)
							<tr>
							@setvar($col = 1)
						@elseif ($col == 1)
							@setvar($col = 2)
						@elseif ($col == 2)
							</tr>
							<tr>
							@setvar($col = 1)
						@endif
							<td style="width:100px;">
								<img src = "{{str_replace("http://", "https://", $item->item_thumb)}}" width="90" height="90" />
								<br>
								Order <a href = "{{url(sprintf('/orders/details/%s', $item->order->id))}}"
									 target = "_blank">{{ $item->order->short_order }}</a>
								<br>
								Sku {{ $to_move->items[0]->child_sku }}
							</td>
							<td style="width:100px;">
								@if ($item->item_quantity != 1)
									<strong  style="font-size: 150%;">QTY: {{ $item->item_quantity }}</strong>
								@endif
								
								<br>
								{!! Form::open(['name' => 'reject-' . $item->id, 'url' => '/reject_item', 'method' => 'get', 'id' => 'reject-' . $item->id]) !!}
								{!! Form::hidden('item_id', $item->id, ['id' => 'item_id']) !!}
								{!! Form::hidden('origin', 'MP', ['id' => 'origin']) !!}
								{!! Form::hidden('scan_batches', $to_move->batch_number) !!}
								{!! Form::button('Reject Item' , ['id'=>'reject-' . $item->item_quantity, 'class' => 'btn btn-sm btn-danger']) !!}
								{!! Form::close() !!}
							</td>
							<td style="width:300px;">
								{!! Form::textarea('nothing', \Monogram\Helper::jsonTransformer($item->item_option),['rows' => '8', 'cols' => '45']) !!}
							</td>
					@endforeach
					
						@if ($col == 1)
							<td style="width:100px;"></td>
							<td style="width:100px;"></td>
							<td style="width:300px;"></td>
						@endif
					
						</tr>
					</tbody>
				</table>
			</div>
			</table>
		@endif
	</div>
	
	@include('/rejections/rejection_modal')
	
	<script type = "text/javascript">

		$(function() {
				// Focus on load
				 $('#barcode').focus();
		});
		
	</script>
</body>
</html>
	

