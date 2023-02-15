<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Add child sku</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>

	<style>
		td {
			width: 1px;
			white-space: nowrap;
		}

		td.description {
			white-space: pre-wrap;
			word-wrap: break-word;
			max-width: 1px;
			width: 100%;
		}

		td textarea {
			border: none;
			width: auto;
			-webkit-box-sizing: border-box;
			-moz-box-sizing: border-box;
			box-sizing: border-box;
		}
	</style>
</head>
<body>
	@include('includes.header_menu')
	<div class = "container">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li class = "active">Add new child sku</li>
		</ol>
		@include('includes.error_div')
		<h3 class = "page-header">Add new child sku</h3>
		@setvar($i = 0)
		{!! Form::open(['url' => url('/logistics/add_child_sku'), 'method' => 'post', 'class' => 'form-horizontal']) !!}
		<div class = "form-group">
			{!! Form::label('allow_mixing', 'Allow Mixing', ['class' => 'col-md-2 control-label']) !!}
			<div class = "col-sm-10">
				{!! Form::select('allow_mixing', ['0' => 'No', '1' => 'Yes'], $request->get('allow_mixing') ?? 0, ['class'=> 'form-control', 'id' => 'allow_mixing']) !!}
			</div>
		</div>
		<div class = "form-group">
			{!! Form::label('batch_route_id', 'Batch route', ['class' => 'col-md-2 control-label']) !!}
			<div class = "col-sm-10">
				{!! Form::select('batch_route_id', $batch_routes, '115', ['class'=> 'form-control', 'id' => 'batch_route_id']) !!}
			</div>
		</div>
		<div class = "form-group">
			{!! Form::label('id_catalog', 'ID Catalog', ['class' => 'col-md-2 control-label']) !!}
			<div class = "col-sm-10">
				{!! Form::text('id_catalog', $request->get('id_catalog') ?? null, ['class'=> 'form-control', 'id' => 'id_catalog']) !!}
			</div>
		</div>
		<div class = "form-group">
			{!! Form::label('parent_sku', 'Parent SKU', ['class' => 'col-md-2 control-label']) !!}
			<div class = "col-sm-10">
				{!! Form::text('parent_sku', $request->get('parent_sku') ?? null, ['class'=> 'form-control', 'id' => 'parent_sku']) !!}
			</div>
		</div>
		<div class = "form-group">
			{!! Form::label('child_sku', 'Child SKU', ['class' => 'col-md-2 control-label']) !!}
			<div class = "col-sm-10">
				{!! Form::text('child_sku', $request->get('child_sku') ?? null, ['class'=> 'form-control', 'id' => 'child_sku']) !!}
			</div>
		</div>
		<div class = "form-group">
			{!! Form::label('graphic_sku', 'Graphic SKU', ['class' => 'col-md-2 control-label']) !!}
			<div class = "col-sm-10">
				{!! Form::text('graphic_sku', 'NeedGraphicFile', ['class'=> 'form-control', 'id' => 'graphic_sku']) !!}
			</div>
		</div>
		<div class = "form-group">
			{!! Form::label('sure_3d', "Sure3d", ['class' => 'col-md-2 control-label']) !!}
			<div class = "col-sm-10">
				{!! Form::select('sure3d', ['0' => 'No', '1' => 'Yes'], $request->get('sure3d') ?? null, ['class'=> 'form-control', 'id' => 'sure3d']) !!}
			</div>
		</div>
		<div class = "form-group">
			<div class = "col-sm-offset-2 col-sm-10">
				<button type = "submit" class = "btn btn-primary">Add new child sku</button>
			</div>
		</div>
		{!! Form::close() !!}
	</div>

	<script type = "text/javascript">
		$(function ()
		{
			$('[data-toggle="tooltip"]').tooltip();
		});
		var message = {
			delete: 'Are you sure you want to delete?',
		};
		$(".delete-sku_converter").on('click', function (event)
		{
			event.preventDefault();
			var action = confirm(message.delete);
			if ( action ) {
				$(this).closest('form').submit();
			}
			//return false;
		});
	</script>
	
</body>
</html>