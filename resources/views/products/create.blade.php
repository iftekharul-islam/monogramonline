<!doctype html>
<!--suppress JSUnresolvedVariable -->
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Create product</title>
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
			<li><a href = "{{url('products')}}">Products</a></li>
			<li class = "active">Create product</li>
		</ol>
		@include('includes.error_div')
		
		<div class = "col-md-12">
		{!! Form::open(['url' => url('/products'), 'method' => 'post', 'class'=>'form-horizontal', 'role'=>'form']) !!}
			<div class = "form-group">
				{!!Form::label('product_production_category','Production category: ',['class'=>'control-label col-xs-2'])!!}
				<div class = "col-xs-5">
					{!! Form::select('product_production_category', $production_categories, null, ['id' => 'product_production_category','class'=>'form-control']) !!}
				</div>
			</div>
			<div class = "form-group">
				{!!Form::label('product_model','Model(SKU): ',['class'=>'control-label col-xs-2'])!!}
				<div class = "col-xs-5">
					{!! Form::text('product_model', null, ['id' => 'product_model','class'=>'form-control']) !!}
				</div>
			</div>
			<div class = "form-group">
				{!!Form::label('product_name','Product name: ',['class'=>'control-label col-xs-2'])!!}
				<div class = "col-xs-5">
					{!! Form::text('product_name', null, ['id' => 'product_name','class'=>'form-control']) !!}
				</div>
			</div>
			<div class = "form-group">
				{!!Form::label('product_description','Product description: ',['class'=>'control-label col-xs-2'])!!}
				<div class = "col-xs-10">
					{!! Form::textarea('product_description', null, ['id' => 'product_description','class'=>'form-control', 'rows' => 8]) !!}
				</div>
			</div>
			
			<hr />
			
			<div class = "form-group">
				{!!Form::label('id_catalog','ID:',['class'=>'control-label col-xs-2'])!!}
				<div class = "col-xs-5">
					{!! Form::text('id_catalog', null, ['id' => 'id_catalog','class'=>'form-control']) !!}
				</div>
			</div>
			<div class = "form-group">
				{!!Form::label('product_url','Product URL: ',['class'=>'control-label col-xs-2'])!!}
				<div class = "col-xs-5">
					{!! Form::text('product_url', null, ['id' => 'product_url','class'=>'form-control']) !!}
				</div>
			</div>
			<div class = "form-group">
				{!!Form::label('product_thumb','Thumb / Insert image: ',['class'=>'control-label col-xs-2'])!!}
				<div class = "col-xs-5">
					{!! Form::text('product_thumb', null, ['id' => 'product_thumb','class'=>'form-control']) !!}
				</div>
			</div>
			
			<hr />
			
			<div class = "form-group">
				{!!Form::label('ship_weight','Ship weight: ',['class'=>'control-label col-xs-2'])!!}
				<div class = "col-xs-5">
					{!! Form::number('ship_weight', null, ['id' => 'ship_weight','class'=>'form-control', 'step' => 'any']) !!}
				</div>
			</div>
			<div class = "form-group">
				{!!Form::label('height','Height: ',['class'=>'control-label col-xs-2'])!!}
				<div class = "col-xs-5">
					{!! Form::number('height', null, ['id' => 'height','class'=>'form-control', 'step' => 'any']) !!}
				</div>
			</div>
			<div class = "form-group">
				{!!Form::label('width','Width: ',['class'=>'control-label col-xs-2'])!!}
				<div class = "col-xs-5">
					{!! Form::number('width', null, ['id' => 'width','class'=>'form-control', 'step' => 'any']) !!}
				</div>
			</div>
			
			<hr />
			
			<div class = "form-group">
				{!!Form::label('product_upc','UPC: ',['class'=>'control-label col-xs-2'])!!}
				<div class = "col-xs-5">
					{!! Form::text('product_upc', null, ['id' => 'product_upc','class'=>'form-control']) !!}
				</div>
			</div>
			<div class = "form-group">
				{!!Form::label('product_asin','ASIN: ',['class'=>'control-label col-xs-2'])!!}
				<div class = "col-xs-5">
					{!! Form::text('product_asin', null, ['id' => 'product_asin','class'=>'form-control']) !!}
				</div>
			</div>
			
			<hr />
			
			<div class = "form-group">
				{!!Form::label('product_price','Product price: ',['class'=>'control-label col-xs-2'])!!}
				<div class = "col-xs-5">
					{!! Form::number('product_price', null, ['id' => 'product_price','class'=>'form-control', 'step' => 'any']) !!}
				</div>
			</div>
			<div class = "form-group">
				{!!Form::label('product_sale_price','Product sale price: ',['class'=>'control-label col-xs-2'])!!}
				<div class = "col-xs-5">
					{!! Form::number('product_sale_price', null, ['id' => 'product_sale_price','class'=>'form-control', 'step' => 'any']) !!}
				</div>
			</div>
			<div class = "form-group">
				{!!Form::label('product_wholesale_price','Wholesale price: ',['class'=>'control-label col-xs-2'])!!}
				<div class = "col-xs-5">
					{!! Form::number('product_wholesale_price', null, ['id' => 'product_wholesale_price','class'=>'form-control', 'step' => 'any']) !!}
				</div>
			</div>
			<div class = "form-group">
				<div class = "col-md-2 pull-right">
					{!! Form::submit('Create product',['class'=>'btn btn-primary btn-block']) !!}
				</div>
			</div>
		{!! Form::close() !!}
		</div>
	</div>
	
	<script type = "text/javascript">

	</script>
</body>
</html>