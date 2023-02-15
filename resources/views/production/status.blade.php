<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Production Stations</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	<link rel="stylesheet" href="/assets/css/chosen.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/chosen.jquery.min.js"></script>	

<style>
.chosen-container-single .chosen-single {
		height: 33px;
		border-radius: 3px;
		border: 1px solid #CCCCCC;
}
.chosen-container-single .chosen-single span {
		padding-top: 2px;
}
.chosen-container-single .chosen-single div b {
		margin-top: 2px;
}
</style>
</head>

<body>
	@include('includes.header_menu')
	<div class = "container">
		
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "{{url('/production/status')}}">Production Stations</a></li>
		</ol>
		
		@include('includes.error_div')
		@include('includes.success_div')
		
		<div class = "col-xs-12">
			{!! Form::open(['url' => '/production/scan_work', 'method' => 'POST']) !!}
			{!! Form::hidden('task', 'scan') !!}
			{!! Form::hidden('from', 'status') !!}
			<div class = "form-group col-xs-2">
				{!! Form::text('batch_number', '', ['id'=>'batch_barcode', 'class' => 'form-control', 'placeholder' => 'Enter Batch']) !!}
			</div>
			<div class = "form-group col-xs-2">
				{!! Form::password('user', ['id'=>'user_barcode', 'class' => 'form-control', 'autocomplete' => "new-password"]) !!}
			</div>
			<div class = "form-group col-xs-2">
				{!! Form::submit('Scan', ['id'=>'search', 'style' => 'margin-top: 0px;', 'class' => 'btn btn-primary']) !!}
			</div>
			{!! Form::close() !!}
			
			<div class = "form-group col-xs-1"></div>
			
			{!! Form::open(['method' => 'get', 'url' => url('/production/status_detail'), 'id' => 'station-status-form']) !!}
			<div class = "form-group col-xs-5">
				{!! Form::select('station', $stations, isset($station) ? $station : ''  , ['id'=>'station', 'class' => 'form-control']) !!}
			</div>
			{!! Form::close() !!}
		</div>
		
		<br><br><br>
		
		<div class = "col-xs-12">
			
		@if(count($stations_status) > 0)
			
			@foreach ($sections as $section)
			
				@setvar($stations = $stations_status->where('section_id', $section->id)->all())
				
					@if(count($stations) > 0)
					
							<div class="panel panel-primary accordion" data-id="{{ $section->id }}"> 
								
								 <a class="accordion-toggle" data-toggle="collapse" data-parent=".accordion" href="#collapse{{ $section->id }}" style="text-decoration: none;">
								    <div class="panel-heading">
								            <i class = 'glyphicon glyphicon-plus text-primary'></i> &nbsp; 
														{{ $section->section_name }} ({{ array_sum(array_column($stations, 'batch_count')) }})
								    </div>
						      </a>    
									
									<div id="collapse{{ $section->id }}" class="panel-collapse collapse 
																@if($section->id == $user_section) in @else out @endif">

										<div class="panel-body"> 

											<table class="table table-hover">
												<tr>
													<th width=10></th>
													<th colspan=2 width=300>Station</th>
													<th width=200>Earliest Order</th>
													@if ($section->inventory == '1')
														<th width=150 style="text-align:right;">Picking</th>
													@endif
													<th width=150 style="text-align:right;">To Produce</th>
													<th width=150 style="text-align:right;">In Progress</th>
												</tr>
											@foreach($stations as $station)
												@if($station->min_date < $very_late)
													@setvar($class = 'blink_me text-danger')
												@elseif($station->min_date < $late)
													@setvar($class = 'text-danger')
												@else
													@setvar($class = '')
												@endif
												<tr class='clickable-row' data-href='{{ url(sprintf('/production/status_detail?station=%s',$station->station_id)) }}'>
													<td></td>
													<td>{{ $station->station_name }}</td>
													<td>{{ $station->station_description }}</td>
													<td><span class="{{ $class }}">{{ $station->min_date }}</span></td>
													@if ($section->inventory == '1')
														<td  align="right">{{ $station->pick }}</td>
													@endif
													<td align="right">{{ $station->ready }}</td>
													<td align="right">{{ $station->scanned }}</td>
												</tr>
											@endforeach
										
											</table>

										</div> 

									</div>
									
							</div> 
				@endif
				
			@endforeach
				
		@else
			<div class = "col-xs-12">
				<div class = "alert alert-warning text-center">
					<h3>No Stations found.</h3>
				</div>
			</div>
		@endif
		
		</div>
	
	</div>

<script>
	$(".clickable-row").click(function() {
			window.location = $(this).data("href");
	});
	
	$(function() {
			// Focus on load
			$('#batch_barcode').focus();
			
			localStorage.setItem('lastTab', '');
			
			$('#batch_barcode').bind('keypress keydown keyup', function(e){
				 if(e.keyCode == 13) { 
					 e.preventDefault(); 
					 $('#user_barcode').focus();
				 }
			});
			
	});
	
	$("select#station").on('change', function(){
		$("form#station-status-form").submit();
	});
	
	$(".chosen_txt").chosen();
			
	$('.accordion').on('shown.bs.collapse', function (e) {
    var id = $(this).attr('data-id');
		
		$.ajax({
			type: 'get',
			url: '{{ url("/production/user_section") }}',
			data: 'data-id=' + id
		});
		
	});
	
</script>

</body>
</html>