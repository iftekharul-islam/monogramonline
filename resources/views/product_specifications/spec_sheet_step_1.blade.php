<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Product Spec sheet - 1</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet"
	      href = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
	<link type = "text/css" rel = "stylesheet"
	      href = "//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
</head>
<body>
	@include('includes.header_menu')
	<div class = "container">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "{{url('/products_specifications')}}">Product specifications</a></li>
			<li>Product specifications step- 1</li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		{!! Form::open(['url' => url('/products_specifications/step/1'), 'method' => 'post']) !!}
		<fieldset>
			<div class = "form-group">
				{!! Form::label('production_category', 'Production category', ['class' => 'col-md-2 control-label']) !!}
				<div class = "col-md-4">
					{!! Form::select('production_category', $production_categories, null, ['id' => 'production_category', 'class' => "form-control"]) !!}
				</div>
			</div>
			<div class = "form-group">
				<div class = "col-md-offset-2 col-sm-10">
					<div class = "checkbox">
						<label>
							<input type = "checkbox" name = "gift-wrap" value = "yes"> Gift wrapped?
						</label>
					</div>
				</div>
			</div>
			<div class = "form-group">
				<div class = "col-md-offset-2 col-md-4">
					{!! Form::submit('Next', ['class' => 'btn btn-primary']) !!}
				</div>
			</div>
		</fieldset>
		{!! Form::close() !!}
	</div>
</body>
</html>