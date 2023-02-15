<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Sections</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
</head>
<body>
	@include('includes.header_menu')
	<div class = "container" style="min-width: 1550px;"">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li class = "active">Sections</li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		
		<div class = "col-xs-12">
			<h2>Sections</h2>
			<br />
			<div class = "col-sm-12">
			<table class="table" cellspacing="0" cellpadding="0">
				<tr>
					<th>Section Name</th>
					<th>Summaries</th>
					<th>Buttons</th>
					<th>User Scan</th>
					<th>Labels</th>
					<th>Picking</th>
					<th>Inventory</th>
					<th colspan=2></th>
				</tr>
				@foreach($sections as $section)
				<tr>
					{!! Form::open(['url' => url('prod_config/sections_update'), 'method' => 'post']) !!}
					{!! Form::hidden('section', $section->id) !!}
					<td width="200">
						{!! Form::text('section_name', $section->section_name, ['id' => 'section_name', 'class' => "form-control", 'placeholder' => "Enter section name"]) !!}
					</td>
					<td width="250">
						{!! Form::select('summaries', ['0' => 'Print Batch Summaries', '1' => 'No Summaries'], $section->summaries, ['id' => 'summaries', 'class' => 'form-control']) !!}
					</td>
					<td width="150">
						{!! Form::select('start_finish', ['0' => 'No Button', '1' => 'Finish Button'], $section->start_finish, ['id' => 'start_finish', 'class' => 'form-control']) !!}
					</td>
					<td width="200">
						{!! Form::select('same_user', ['0' => 'No Restrictions', '1' => 'One user scans in/out'], $section->same_user, ['id' => 'same_user', 'class' => 'form-control']) !!}
					</td>
					<td width="150">
						{!! Form::select('print_label', ['0' => 'No Labels', '1' => 'Batch Label', '2' => 'Item Label'], $section->print_label, ['id' => 'print_label', 'class' => 'form-control']) !!}
					</td>
					<td width="200">
						{!! Form::select('inventory', ['0' => 'No Report', '1' => 'Before Production', '2' => 'In Production', '3' => 'By batch and Move'], $section->inventory, ['id' => 'inventory', 'class' => 'form-control']) !!}
					</td>
					<td width="200">
						{!! Form::select('inv_control', ['0' => 'None', '1' => 'Require Stock'], $section->inv_control, ['id' => 'inv_control', 'class' => 'form-control']) !!}
					</td>
					<td>{!! Form::submit('update',['class' => 'btn btn-success btn-sm']) !!}</td>
					{!! Form::close() !!}
					{!! Form::open(['url' => url('prod_config/sections_delete'), 'method' => 'post', 'onsubmit' => "return confirm('Are you sure you want to delete?');"]) !!}
					{!! Form::hidden('section', $section->id) !!}
					<td>{!! Form::submit('delete',['class' => 'btn btn-warning btn-sm']) !!}</td>
					{!! Form::close() !!}
				</tr>
				@endforeach
				<tr>
					{!! Form::open(['url' => url('prod_config/sections_update'), 'method' => 'post']) !!}
					<td>{!! Form::text('section_name', null, ['id' => 'section_name', 'class' => "form-control", 'placeholder' => "Enter section name"]) !!}</td>
					<td colspan="2">{!! Form::submit('create new section',['class' => 'btn btn-primary btn-sm']) !!}</td>
					{!! Form::close() !!}
					<td colspan=6></td>
				</tr>
			</table>
			</div>
		</div>
	</div>

	<script type = "text/javascript">
	

	
	</script>
</body>
</html>