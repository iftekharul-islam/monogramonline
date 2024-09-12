<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Quality Control</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	
	<style>

	</style>
</head>
<body>
	@include('includes.header_menu')
	<div class = "container">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href="{{url('/shipping/qc_station')}}">Quality Control</a></li>
			<li class = "active">{{ $station->station_name }}</li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		
		<div class = "col-xs-12">
			
			<div class="row">
				<h3 class="page_header">{{ $station->station_description }}</h3>
			</div>
			
			<div class="row">
				{!! Form::open(['url' => url('shipping/qc_scanIn'), 'method' => 'post', 'id' => 'barcode_form']) !!}
				<div class = "form-group col-xs-2">
					{!! Form::text('batch_number', '', ['id'=>'batch_barcode', 'class' => 'form-control', 'placeholder' => 'Enter Batch']) !!}
				</div>
{{--				<div class = "form-group col-xs-2">--}}
{{--					{!! Form::password('user_barcode', ['id'=>'user_barcode', 'class' => 'form-control', 'autocomplete' => "new-password"]) !!}--}}
{{--				</div>--}}
				<div class = "form-group col-xs-2">
					{!! Form::submit('Open Batch', ['id'=>'search', 'style' => 'margin-top: 0px;', 'class' => 'btn btn-primary btn-sm']) !!}
				</div>
				{!! Form::close() !!}
			</div>
			
			<div class="row">
		
			@if(count($batches))
				
				<table class="table table-hover">
				 <tbody>
					 
					 <tr class="info"> 
						 <th>Batch</th> 
						 <th>First Order</th> 
						 <th>Lines</th> 
						 <th>Last Scan</th> 
						 <th></th> 
						 <th style="width: 300px;"></th> 
					 </tr> 
							 
					@foreach($batches as $batch)
					
						<tr class="batch-row" batch-number="{{ $batch->batch_number }}"> 
							<td>
								<a href="{{ route('openBatch', ['batch_number' => $batch->batch_number]) }}">{{ $batch->batch_number }}</a>
{{--								<a href = "{{ url(sprintf('batches/details/%s',$batch->batch_number)) }}"  --}}
{{--										target="_blank">{{ $batch->batch_number }}</a> --}}
								@if ($batch->store) 
									<br> 
									{{ $batch->store->store_name }} 
								@endif 
							</td> 
							<td>{{ substr($batch->min_order_date, 0, 10) }}</td> 
							<td> 
								@if ($batch->itemsCount && $batch->itemsCount->first()) 
									{{ $batch->itemsCount->first()->count }} 
								@else 
									0 
								@endif 
							</td> 
							<td> 
								@if ($batch->scanned_in && $batch->scanned_in->station_id == $batch->station_id) 
									<strong>QC in Progress by {{ $batch->scanned_in->in_user->username }}</strong> 
									<br><br> 
								@endif 
								{{ $batch->change_date }} 
							</td> 
							
							@if ($batch->first_item) 
								<td> 
								 <img src = "{{ $batch->first_item->item_thumb }}" width = "70" height = "70" /> 
							 </td> 
								<td>{{ $batch->first_item->item_description }}</td> 
							@else 
								<td colspan=2> 
									No Items 
								</td> 
							@endif 
							
						</tr> 
						
					@endforeach
					
					<tbody>
				</table>
				
			@else
					<div class = "alert alert-warning">No batches in station.</div>
			@endif
			</div>
		</div>
	</div>
	
	<script type = "text/javascript">
	
		$(function ()
		{
			$(".batch-row").click(function() { 
					$("#batch_barcode").val($(this).closest('tr').attr('batch-number')); 
					$("#user_barcode").focus(); 
			}); 

			$('#batch_barcode').focus();
			
			$('#batch_barcode').bind('keypress keydown keyup', function(e){
	       if(e.keyCode == 13) { 
					 e.preventDefault(); 
					 $('#user_barcode').focus();
				 }
	    });
			
		});
				
	</script>
</body>
</html>