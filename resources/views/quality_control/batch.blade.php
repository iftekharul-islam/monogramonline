<!doctype html> 
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>QC Batch {{ $batch_number }}</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>

	@if ($label != null)
		@include('prints.includes.label')
	@endif
	
	<style>

	</style>
</head>

<body>
	@include('includes.header_menu')
	<div class = "container">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "{{url('/shipping/qc_station')}}">Quality Control</a></li>
			<li class = "active">Batch {{ $batch_number }}</li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		

					{!! Form::open(['url' => url('shipping/qc_scanIn'), 'method' => 'post', 'id' => 'barcode_form']) !!}
					<div class = "form-group col-xs-4 col-sm-3 col-md-2">
						{!! Form::text('batch_number', '', ['id'=>'batch_barcode', 'class' => 'form-control', 'placeholder' => 'Enter Batch']) !!}
					</div>
					<div class = "form-group col-xs-4 col-sm-3 col-md-2">
						{!! Form::password('user_barcode', ['id'=>'user_barcode', 'class' => 'form-control', 'autocomplete' => "new-password"]) !!}
					</div>
					<div class = "form-group col-xs-4 col-sm-3 col-md-2">
						{!! Form::submit('Open Batch', ['id'=>'search', 'style' => 'margin-top: 0px;', 'class' => 'btn btn-primary btn-sm']) !!}
					</div>
					<div class="col-xs-0 col-sm-3 col-md-6"></div>
					{!! Form::close() !!}

		
			@if($batch)
			
			<div class = "col-xs-12 col-sm-12 col-md-12">
				<h3 class="page-header">
					Batch <a href="{!! url(sprintf('/batches/details/%s', $batch_number)) !!}">{{ $batch->batch_number }}</a>
					@if ($batch->status != 'active') 
						- <span style="color:red">{!! ucfirst($batch->status) !!}</span>
					@endif 
				</h3>
			</div>
			
			<div class = "col-xs-12 col-sm-6 col-md-6">
				<strong>QC in Progress by {{ $batch->scanned_in->in_user->username }}</strong>
				- {{ $batch->scanned_in->in_date }}
			</div>
			<div class = "col-xs-12 col-sm-6 col-md-6" style="text-align:right;">
				@if ($batch->prev_station)
					{{ $batch->prev_station->station_description }}
					- {{ $batch->change_date }}
				@endif				
			</div>
			
			<div class = "col-xs-12">
					<br>
					<table class = "table" id = "batch-items-table">
						
						<tbody>
						@setvar($count = 0)
						@setvar($order = 'start')
						
						@foreach($batch->items as $item)

							<tr class="batch-row" id="{{ $item->order_5p }}">
								@if (($order != $item->order_5p || $order == 'start') && $count < $item->count)
										@if ($item->count > 1)
											@setvar($rowspan = 'rowspan=' . $item->count . ' style=vertical-align:middle')
											@setvar($btn_text = 'QC ' . $item->count . ' Items')
										@else
											@setvar($rowspan = '')
												@setvar($btn_text = 'QC Item')
										@endif
										<td {{ $rowspan }}>
											@if ($item->order)
												Order 
												<a href = "{{url(sprintf('/orders/details/%s', $item->order->id))}}"
												   target = "_blank">{{ $item->order->short_order }}</a>
											@endif
										</td>
									@setvar($count = 0)
								@else 
									@setvar($count++)
								@endif
								<td>
									@if($item->item_status == 'production')
										<br>
										{!! Form::open(['url' => url('shipping/qc_order'), 'method' => 'post', 'id' => 'order-form-' . $item->order_5p]) !!}
										{!! Form::hidden('batch_number', $batch_number) !!}
										{!! Form::hidden('id', $id) !!}
										{!! Form::hidden('order_5p', $item->order_5p) !!}
										{!! Form::button($btn_text, ['style' => 'margin-top: 0px;', 'class' => 'btn btn-success btn-sm']) !!}
										{!! Form::close() !!}
									@elseif($item->item_status == 'wap')
										<br>
										<strong>
										WAP Bin <a href="/wap/details?bin={{ $item->wap_item->bin_id }}" 
											target="_blank">{{ $item->wap_item->bin->name }}</a>
										</strong>
									@elseif($item->item_status == 'shipped')
										Shipped
									@else
										<br>
										{{ ucFirst($item->item_status) }}
									@endif
								</td>
								<td>
										@setvar($thumb = \Monogram\Sure3d::getThumb($item))
										@if($thumb)
											<img src = "{{ $thumb[0] }}" height="100">
										@endif
								</td>
								<td>
									<a href = "{{ $item->item_url }}" target = "_blank">
									<img src = "{{ $item->item_thumb }}" height="100"></a>
								</td>
								<td>
										{{ $item->child_sku }}
										<br>
										{{ $item->item_description }}
										<br>
										@if ($item->item_quantity > 1)
											<strong style="font-size: 125%;">QTY: {{ $item->item_quantity }}</strong>
										@endif
								</td>
								<td>
									{!! $options[$item->id] !!}
								</td>								
							</tr>
														
							@setvar( $order = $item->order_5p )
						@endforeach
						</tbody>
					</table>
				</div>
			@else
				<div class = "alert alert-warning">Batch {{ $batch_number }} not in Quality Control Station.</div>
			@endif
		</div>
		
		@include('/rejections/rejection_modal')
		
		@include('/shipping/shipval_modal')

		<a class="btn btn-primary"  href="{{ url('shipping/qc_list?station_id='. session('station_id')) }}"> < Back To Next shipment</a>
		
	</div>

	<script type = "text/javascript">
	
		$(function ()
		{
			$('[data-toggle="tooltip"]').tooltip();
			
			$('#batch_barcode').focus();
			
		});
		
		$(".batch-row").click(function() {
			var id = $(this).attr('id');
			$("form#order-form-" + id).submit();
		});
		
		$('#batch_barcode').bind('keypress keydown keyup', function(e){
			 if(e.keyCode == 13) { 
				 e.preventDefault(); 
				 $('#user_barcode').focus();
			 }
		});
		
	</script>
</body>
</html>