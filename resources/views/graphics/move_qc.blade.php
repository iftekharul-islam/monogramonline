<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Move to Production</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap-multiselect.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap-multiselect.js"></script>
	
</head>
<body>
	@include('includes.header_menu')
		<div class = "container" style="width:95%;">
			<ol class = "breadcrumb">
				<li><a href = "{{url('/')}}">Home</a></li>
				<li class = "active">Move to QC</li>
			</ol>
			
			@setvar($sound = 1)
			@setvar($large = 1)
			@include('includes.error_div')
			@include('includes.success_div')
			
			<div class = "col-md-12">
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
						
						 <div class = "form-group col-xs-2"></div>
						 
						{!! Form::open(['name' => 'store_form','url' => '/move_to_qc', 'method' => 'get', 'id' => 'store_form']) !!}
							 <div class = "form-group col-xs-3">
								 {!! Form::select('store_id[]', $stores, $store_id, ['id'=>'store_id', 'class' => 'form-control', 'multiple' => 'multiple']) !!}
							 </div>
							 <div class = "form-group col-xs-2">
								 {!! Form::submit('Filter by Store', ['id' => 'store_button', 'class' => 'btn btn-primary btn-sm form-control']) !!}
							 </div>
					 {!! Form::close() !!}
				 </div>
			 </div>
			</div>
			
			<br><br>
			
			@if (count($to_move) > 0)  
			    
				<h4 class="page-header">{!! $to_move->sum('total') !!} Batches to move to production</h4>
						
				<table class="table">
					
				@foreach ($to_move as $row)
					<tr>
						<td>{{ $row->section->section_name }}</td>
						<td>{{ $row->production_station->station_description }}</td>
						<td align="right">
							<a href="{{ url(sprintf('/batches/list?graphic_found=1&type=P&status=movable&qc_station=%s', $row->production_station_id)) }}"
								target="_blank">{{ $row->total }}</a>
						</td>
					</tr>
			  @endforeach
				
					<tr>
						<th></th>
						<th style="text-align:right;">Total:</th>
						<th style="text-align:right;">{{ $to_move->sum('total') }}</th>
					</tr>
					
				</table>
			@else 
				<br><br>
			  <div class = "alert alert-warning">No batches found</div>
			@endif
		</div>
		
			<script type = "text/javascript">

			  $(function() {
			      // Focus on load
			   		$('#barcode').focus();

				 	$('#store_id').multiselect({includeSelectAllOption:true,
					  nonSelectedText:'Filter By Store',
					  numberDisplayed: 1,});
			  });
				
			</script>
</body>
</html>
