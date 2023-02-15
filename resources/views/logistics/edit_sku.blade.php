<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Edit sku data</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/chosen.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/chosen.jquery.min.js"></script>	
	
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
			<li class = "active">SKU Converter details</li>
		</ol>
		@include('includes.error_div')
		@if($options)
			<h3 class = "page-header">Edit</h3>
			@setvar($i = 0)
			{!! Form::open(['url' => url('/logistics/edit_sku'), 'method' => 'put', 'class' => 'form-horizontal']) !!}
			{!! Form::hidden("unique_row_value", $options->unique_row_value) !!}
			
			
			<div class = "form-group">
				{!! Form::label('allow_mixing', "Allow Mixing", ['class' => 'col-md-2 control-label']) !!}
				<div class = "col-sm-10">
					{!! Form::select('allow_mixing', ['0' => 'No', '1' => 'Yes'], $options->allow_mixing, ['class'=> 'form-control', 'id' => 'allow_mixing']) !!}
				</div>
			</div>
			<div class = "form-group">
				{!! Form::label('batch_route_id', "Batch route", ['class' => 'col-md-2 control-label']) !!}
				<div class = "col-sm-10">
					{!! Form::select('batch_route_id', $batch_routes, $options->batch_route_id, ['class'=> 'form-control', 'id' => 'batch_route_id']) !!}
				</div>
			</div>
			<div class = "form-group">
				{!! Form::label('id_catalog', "ID", ['class' => 'col-md-2 control-label']) !!}
				<div class = "col-sm-10">
					{!! Form::text('id_catalog', $options->id_catalog, ['class'=> 'form-control', 'id' => 'id_catalog']) !!}
				</div>
			</div>
			<div class = "form-group">
				{!! Form::label('parent_sku', "Parent SKU", ['class' => 'col-md-2 control-label']) !!}
				<div class = "col-sm-10">
					{!! Form::text('parent_sku', $options->parent_sku, ['class'=> 'form-control', 'id' => 'parent_sku']) !!}
				</div>
			</div>
			
			<div class = "form-group">
				{!! Form::label('child_sku', "Child SKU", ['class' => 'col-md-2 control-label']) !!}
				<div class = "col-sm-10">
					{!! Form::text('child_sku', $options->child_sku, ['class'=> 'form-control', 'id' => 'child_sku']) !!}
				</div>
			</div>
			<div class = "form-group">
				{!! Form::label('graphic_sku', "Graphic SKU", ['class' => 'col-md-2 control-label']) !!}
				<div class = "col-sm-10">
					{!! Form::text('graphic_sku', $options->graphic_sku, ['class'=> 'form-control', 'id' => 'graphic_sku']) !!}
				</div>
			</div>
			<div class = "form-group">
				{!! Form::label('sure_3d', "Sure3d", ['class' => 'col-md-2 control-label']) !!}
				<div class = "col-sm-10">
					{!! Form::select('sure3d', ['0' => 'No', '1' => 'Yes'], $options->sure3d, ['class'=> 'form-control', 'id' => 'sure3d']) !!}
				</div>
			</div>
			<div class = "form-group">
				{!! Form::label('bypass_option', "Bypass Options", ['class' => 'col-md-2 control-label']) !!}
				<div class = "col-sm-10">
					{!! Form::select('bypass_option', ['0' => 'No', '1' => 'Yes'], $bypass, ['class'=> 'form-control', 'id' => 'bypass_option']) !!}
				</div>
			</div>
			<div class = "form-group">
				<div class = "col-sm-offset-2 col-sm-10">
					<button type = "submit" class = "btn btn-primary">Update</button>
				</div>
			</div>
			{!! Form::close() !!}
		@else
			<div class = "col-xs-12">
				<div class = "alert alert-warning text-center">
					<h3>No sku converter parameter found.</h3>
				</div>
			</div>
		@endif
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

		$(".chosen").chosen();

		$("#new_stock_number").on('change', function (event)
		{
			var message = {
					delete: 'Are you sure?\nAdd new Stock Number?',
			};
			var action = confirm(message.delete);
			if ( action ) {
				
				$(".chosen-single").closest("div").find("span").text("Select a Stock Number");
			}
			
		});
	</script>
</body>
</html>