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
			<li class = "active">Quality Control</li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		
		<div class = "col-xs-12">
			
			<div class="row">
				<h3 class="page_header">Quality Control</h3>
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
			
			@if(count($totals))
				
				<table class="table table-hover">
				 <tbody>
				
					@foreach($totals as $section)
					
							<tr class='info clickable' data='{{ url(sprintf('shipping/qc_list?station_id=%d', $section->station_id)) }}'>
								<th>
									@if ($section->section)
										{{ $section->section->section_name }} <br>
										<small>{{ $section->route->batch_route_name }}</small>
									@else
										Section not found
									@endif
								</th>
								<th>
									@if ($section->station)
										{{ $section->station->station_name }}
									@else
										Station not found
									@endif
								</th>
								<th>
											( 
												{{ $section->count }}
												@if($section->count == 1)
													Batch
												@else
													Batches
												@endif 
											)
								</th>
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
			$(".clickable").click(function() {
	        window.location = $(this).attr("data");
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