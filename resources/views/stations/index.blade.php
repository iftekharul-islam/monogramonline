<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Stations</title>
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
			<li class = "active">Stations</li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')

		<div class = "col-xs-12 text-right" style = "margin: 10px 0;">
			<button class = "btn btn-success" type = "button" data-toggle = "collapse" data-target = "#collapsible-top"
			        aria-expanded = "false" aria-controls = "collapsible">Create new station
			</button>
			<div class = "collapse text-left" id = "collapsible-top">
				{!! Form::open(['url' => url('/prod_config/stations'), 'method' => 'post']) !!}
				<div class = "form-group col-xs-12">
					{!! Form::label('station_name', 'Station Name', ['class' => 'col-xs-2 control-label']) !!}
					<div class = "col-sm-4">
						{!! Form::text('station_name', null, ['id' => 'station_name', 'class' => "form-control", 'placeholder' => "Enter station name"]) !!}
					</div>
				</div>
				<div class = "form-group col-xs-12">
					{!! Form::label('station_description', 'Description', ['class' => 'col-xs-2 control-label']) !!}
					<div class = "col-sm-4">
						{!! Form::text('station_description', null, ['id' => 'station_description', 'class' => "form-control", 'placeholder' => "Enter station description"]) !!}
					</div>
				</div>
				<div class = "form-group col-xs-12">
					{!! Form::label('Section', 'Section', ['class' => 'col-xs-2 control-label']) !!}
					<div class = "col-sm-4">
						{!! Form::select('section', $sections, '') !!}
					</div>
				</div>
				<div class = "form-group col-xs-12">
					{!! Form::label('type', 'Type', ['class' => 'col-xs-2 control-label']) !!}
					<div class = "col-sm-4">
						{!! Form::select('type', $types, '') !!}
					</div>
				</div>
				<div class = "col-xs-12 apply-margin-top-bottom">
					<div class = "col-xs-offset-2 col-xs-4">
						{!! Form::submit('Create station',['class' => 'btn btn-primary btn-block']) !!}
					</div>
				</div>
				{!! Form::close() !!}
			</div>
		</div>
		
		@if(count($stations) > 0)
			<div class = "col-xs-12">
				<table class = "table table-bordered">
					<tr>
						<th>#</th>
						<th>Station name</th>
						<th>Station description</th>
						<th>Status on the<br>
						My Orders portal</th>
						<th>Section</th>
						<th>Type</th>
						<th>Action</th>
					</tr>
					@foreach($stations as $station)
						<tr>
							{!! Form::open(['url' => url('/prod_config/stations/' . $station->id), 'method' => 'put']) !!}
							<td>{{ $count++ }}</td>
							<td><input name = "station_name" type = "text" value = "{{$station->station_name}}" readonly="readonly"></td>
							<td><input name = "station_description" type = "text" value = "{{$station->station_description}}"></td>
							<td><input name = "station_status" type = "text" value = "{{$station->station_status}}"></td>
							<td>{!! Form::select('section', $sections, $station->section) !!}</td>
							<td>{!! Form::select('type', $types, $station->type) !!}</td>
							<td>{!! Form::submit('Update', ['class' => 'btn btn-xs btn-primary']) !!}</td>
							{!! Form::close() !!}
						</tr>
					@endforeach
				</table>
			</div>
		@else
			<div class = "col-xs-12">
				<div class = "alert alert-warning text-center">
					<h3>No station found.</h3>
				</div>
			</div>
		@endif

	</div>

	<script type = "text/javascript">
		$(function ()
		{
			$('[data-toggle="tooltip"]').tooltip();
		});
	</script>
	
</body>
</html>