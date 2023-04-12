<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Manufactures</title>
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
			<li class = "active">Manufactures</li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')

		<div class = "col-xs-12 text-right" style = "margin: 10px 0;">
			<button class = "btn btn-success" type = "button" data-toggle = "collapse" data-target = "#collapsible-top"
			        aria-expanded = "false" aria-controls = "collapsible">Create new Manufacture
			</button>
			<div class = "collapse text-left" id = "collapsible-top">
				{!! Form::open(['url' => url('/manufactures'), 'method' => 'post']) !!}
				<div class = "form-group col-xs-12">
					{!! Form::label('name', 'Name', ['class' => 'col-xs-2 control-label']) !!}
					<div class = "col-sm-4">
						{!! Form::text('name', null, ['id' => 'name', 'class' => "form-control", 'placeholder' => "Enter name"]) !!}
					</div>
				</div>
				<div class = "form-group col-xs-12">
					{!! Form::label('description', 'Description', ['class' => 'col-xs-2 control-label']) !!}
					<div class = "col-sm-4">
						{!! Form::textarea('description', null, ['id' => 'description', 'class' => "form-control", 'placeholder' => "Enter description"]) !!}
					</div>
				</div>
				<div class = "col-xs-12 apply-margin-top-bottom">
					<div class = "col-xs-offset-2 col-xs-4">
						{!! Form::submit('Create Manufacture',['class' => 'btn btn-primary btn-block']) !!}
					</div>
				</div>
				{!! Form::close() !!}
			</div>
		</div>
		@if(count($data))
			<div class = "col-xs-12">
				<table class = "table table-bordered">
					<tr>
						<th>#</th>
						<th>Name</th>
						<th>Description</th>
						<th colspan="3" class="text-center">Action</th>
					</tr>
					@foreach($data as $key=>$item)
						<tr>
							{!! Form::open(['url' => url('/manufactures/' . $item->id), 'method' => 'put']) !!}
							<td>{{ $key+1 }}</td>
							<td><input name = "name" class="form-control" type = "text" value = "{{$item->name}}"></td>
							<td><textarea type = "text" class=" form-control" name="description">{{$item->description}}</textarea> </td>
							<td class="text-center">
								{!! Form::submit('update',['class' => 'btn btn-success btn-sm']) !!}
							</td>
								{!! Form::close() !!}
								{!! Form::open(['url' => url('manufactures/' . $item->id), 'method' => 'delete', 'onsubmit' => "return confirm('Are you sure you want to delete?');"]) !!}
								{!! Form::hidden('section', $item->id) !!}
							<td class="text-center">
								{!! Form::submit('delete',['class' => 'btn btn-warning btn-sm']) !!}
							</td>
								{!! Form::close() !!}
								{!! Form::open(['url' => url('manufacture_control/' . $item->id), 'method' => 'get']) !!}
							<td>
								{!! Form::submit('Access',['class' => 'btn btn-info btn-sm']) !!}
							</td>
								{!! Form::close() !!}
						</tr>
					@endforeach
				</table>
			</div>
		@else
			<div class = "col-xs-12">
				<div class = "alert alert-warning text-center">
					<h3>No Manufacture found.</h3>
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