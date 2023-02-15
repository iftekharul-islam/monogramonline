<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Configure Production</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
</head>
<body>
	@include('includes.header_menu')
	<div class = "container" style="min-width: 1550px;">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li class = "active"><a href = "{{url('/prod_config/work_config')}}">Configure Production</a></li>
		</ol>
		
		@include('includes.error_div')
		@include('includes.success_div')
		
		<h4 class="page-header">Configure Production</h4>
		
		<div class = "form-group col-xs-12">
				{!! Form::open(['method' => 'get']) !!}
				<div class = "col-xs-1"></div>
				<div class = "col-xs-1">
						{!! Form::label('section_id', 'Section:', ['class' => 'control-label', 'style' => 'margin-top:5px;']) !!}
				</div>
				<div class = "col-xs-3">
						{!! Form::select('section_id', $sections, $section_id, ['id'=>'section_id', 'class' => 'form-control']) !!}
				</div>
				<div class = "col-xs-1">
						{!! Form::submit('Filter', ['class' => 'form-control btn btn-primary']) !!}
				</div>
				{!! Form::close() !!}
		</div>
		
		<div class = "col-xs-12">
			<table class="table table-hover">
				
				<tr>
					<th>Section</th>
					<th colspan="2">Station</th>
					<th>Finish Button</th>
					<th>One User In/Out</th>
					<th>Print Labels</th>
					<th>Graphic Type</th>
					<th></th>
				</tr>
				
				@foreach ($stations as $station)
					{!! Form::open(['method' => 'get']) !!}
					{!! Form::hidden('station_id', $station->id) !!}
					{!! Form::hidden('section_id', $section_id) !!}
					<tr>
						<td>
							@if ($station->section_info)
								{{ $station->section_info->section_name }}</td>
							@else
								No Section Found
							@endif
						<td>{{ $station->station_name }}</td>
						<td>{{ $station->station_description }}</td>
						<td>
							{!! Form::select('start_finish', ['0' => 'No Button', '1' => 'Finish Button'], $station->start_finish, ['id' => 'start_finish', 'class' => 'form-control']) !!}
						</td>
						<td>
							{!! Form::select('same_user', ['0' => 'No Restriction', '1' => 'One user scans in/out'], $station->same_user, ['id' => 'same_user', 'class' => 'form-control']) !!}
						</td>
						<!-- <td>
							{!! Form::select('printer_type', ['D' => 'Dymo', 'Z' => 'Zebra'], $station->printer_type, ['id' => 'printer_type', 'class' => 'form-control']) !!}
						</td> -->
						<td>
							{!! Form::select('print_label', ['0' => 'No', '1' => 'Batch Label', '2' => 'Item Label'], $station->print_label, ['id' => 'print_label', 'class' => 'form-control']) !!}
						</td>
						<td>
							{!! Form::select('graphic_type', ['N' => 'None', 'P' => 'Printout', 'F' => 'File'], $station->graphic_type, ['id' => 'graphic_type', 'class' => 'form-control']) !!}
						</td>
						<td>
							{!! Form::submit('Update', ['id' => 'update', 'class' => 'btn btn-primary form-control']) !!}
						</td>
					</tr>
					{!! Form::close() !!}
				@endforeach
			</table>
		</div>
		
	</div>
	
	<script type = "text/javascript">
	

	
	</script>
</body>
</html>