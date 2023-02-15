<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Station Logs</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	<link rel="stylesheet" href="/assets/css/chosen.min.css">
	<link type = "text/css" rel = "stylesheet" href="/assets/css/pikaday.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/chosen.jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/moment.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/pikaday.min.js"></script>
	
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
			<li class = "active"><a href = "{{url('/report/logs')}}">Logs</a></li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		{{-- Search criteria --}}
		<div class = "col-xs-12">
			<div class="row">
				{!! Form::open(['method' => 'get', 'url' => url('report/logs'), 'id' => 'search-station-log']) !!}
				<div class = "form-group col-xs-2">
					<label for = "user_id">Employee</label>
					{!! Form::select('user_id', $users, $request->get('user_id'), ['class'=> 'form-control user_id_select', 'id'=>'user_id']) !!}
					</div>
				<div class = "form-group col-xs-5">
					<label for = "route">Station</label>
					{!! Form::select('station', $stations, $request->get('station'), ['class'=> 'form-control station_select', 'id'=>'station']) !!}
				</div>
				<div class = "form-group col-xs-2">
					<label for = "start_date">Start date</label>
					<div class = 'input-group date'>
						{!! Form::text('start_date', $request->get('start_date'), ['id'=>'start_date_picker', 'class' => 'form-control', 'placeholder' => 'Start date', 'autocomplete' => 'off']) !!}
						<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
					</div>
				</div>
				<div class = "form-group col-xs-2">
					<label for = "end_date">End date</label>
					<div class = 'input-group date'>
						{!! Form::text('end_date', $request->get('end_date'), ['id'=>'end_date_picker', 'class' => 'form-control', 'placeholder' => 'End date', 'autocomplete' => 'off']) !!}
						<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
					</div>
				</div>
				<div class = "form-group col-xs-1">
					<label for = "" class = ""></label>
					{!! Form::submit('Search', ['id'=>'search', 'style' => 'margin-top: 2px;', 'class' => 'btn btn-primary form-control']) !!}
				</div>
				<div class = "form-group col-xs-2">
					<label for = "download_report" class = "">Download Report (CSV)</label>
					<input name="download_report" id="download_report" type="checkbox" class="form-control">
				</div>
			</div>
			{!! Form::close() !!}
		</div>
		{{-- Search criteria ends --}}
		
		@if(count($logs) > 0)
			<h3 class = "page-header">
			
			</h3>
			<div class = "col-xs-12">
				<table class = "table table-bordered">
					<tr>
						<th>Station</th>
						<th>User</th>
						<th>Station Move</th>
						<th>Scan In</th>
						<th>Scan Out</th>
					</tr>
					@foreach($logs as $station_id => $station_users)
						@foreach($station_users as $user_id => $log)
								<tr> 
									<td><strong>{{ $stations[$station_id] ?? '-' }}</strong></td>
									<td>{{ $users[$user_id] ?? '-' }}</td>
									<td>{{ $log['move'] ?? '-' }}</td>
									<td>{{ $log['in_scan'] ?? '-' }}</td>
									<td>{{ $log['out_scan'] ?? '-' }}</td>
								</tr>
						@endforeach
					@endforeach
					
					<tr> 
						<th></th>
						<th><strong>Totals:</strong></th>
						<th>{{ $totals['logs'] ?? '-' }}</th>
						<th>{{ $totals['in_scans'] ?? '-' }}</th>
						<th>{{ $totals['out_scans'] ?? '-' }}</th>
					</tr>
					
				</table>
			</div>
			<div class = "col-xs-12 text-center">
				
			</div>

		@elseif ($request->all() != [])
			<div>&nbsp;</div>
			<div class = "alert alert-warning text-center">
				No results
			</div>

		@endif
	</div>

	<script type = "text/javascript">
		$(".user_id_select").chosen();
		$(".sku_select").chosen();
		$(".station_select").chosen();
		
		var picker = new Pikaday(
		{
				field: document.getElementById('start_date_picker'),
				format : "YYYY-MM-DD",
				minDate: new Date('2016-06-01'),
				maxDate: new Date(),
				yearRange: [2000,2030]      
		});
		
		var picker = new Pikaday(
		{
				field: document.getElementById('end_date_picker'),
				format : "YYYY-MM-DD",
				minDate: new Date('2016-06-01'),
				maxDate: new Date(),
				yearRange: [2000,2030]      
		});
	</script>
</body>
</html>