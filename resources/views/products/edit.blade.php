<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Edit Product - {{ $product->id_catalog}}</title>
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
			<li class = "active">Edit product</li>
		</ol>
		@include('includes.error_div')
		
		<div class = "col-md-12">
		{!! Form::open(['url' => url(sprintf("/products/%d", $product->id)), 'method' => 'put', 'class'=>'form-horizontal']) !!}
			<div class="pull-right">
				{!! \App\Task::widget('App\Product', $product->id); !!}
			</div>
			<div class = "form-group">
				{!!Form::label('product_production_category','Production category: ',['class'=>'control-label col-xs-2'])!!}
				<div class = "col-xs-5">
					{!! Form::select('product_production_category', $production_categories, $product->product_production_category, ['id' => 'product_production_category','class'=>'form-control']) !!}
				</div>
			</div>
			<div class = "form-group">
				{!!Form::label('product_model','Model(SKU): ',['class'=>'control-label col-xs-2'])!!}
				<div class = "col-xs-5">
					{!! Form::hidden('product_model',  $product->product_model) !!}
					{!! Form::text('product_model',  $product->product_model, ['id' => 'product_model','class'=>'form-control', 'disabled' => 'true']) !!}
				</div>
			</div>
			<div class = "form-group">
				{!!Form::label('product_name','Product name: ',['class'=>'control-label col-xs-2'])!!}
				<div class = "col-xs-5">
					{!! Form::text('product_name', $product->product_name, ['id' => 'product_name','class'=>'form-control']) !!}
				</div>
			</div>
			<div class = "form-group">
				{!!Form::label('manufacture_id','Manufacture: ',['class'=>'control-label col-xs-2'])!!}
				<div class = "col-xs-5">
					{!! Form::select('manufacture_id', $manufactures, $product->manufacture_id, ['id' => 'manufacture_id','class'=>'form-control']) !!}
				</div>
			</div>
			<div class = "form-group">
				{!!Form::label('product_description','Product description: ',['class'=>'control-label col-xs-2'])!!}
				<div class = "col-xs-10">
					{!! Form::textarea('product_description', $product->description, ['id' => 'product_description','class'=>'form-control', 'rows' => 8]) !!}
				</div>
			</div>
			
			<hr />
			
			<div class = "form-group">
				{!!Form::label('id_catalog','ID:',['class'=>'control-label col-xs-2'])!!}
				<div class = "col-xs-5">
					{!! Form::text('id_catalog', $product->id_catalog, ['id' => 'id_catalog','class'=>'form-control']) !!}
				</div>
			</div>
			<div class = "form-group">
				{!!Form::label('product_url','Product URL: ',['class'=>'control-label col-xs-2'])!!}
				<div class = "col-xs-5">
					{!! Form::text('product_url', $product->product_url, ['id' => 'product_url','class'=>'form-control']) !!}
				</div>
			</div>
			<div class = "form-group">
				{!!Form::label('product_thumb','Thumb / Insert image: ',['class'=>'control-label col-xs-2'])!!}
				<div class = "col-xs-5">
					{!! Form::text('product_thumb', $product->product_thumb, ['id' => 'product_thumb','class'=>'form-control']) !!}
				</div>
			</div>
			
			<hr />
			
			<div class = "form-group">
				{!!Form::label('ship_weight','Ship weight: ',['class'=>'control-label col-xs-2'])!!}
				<div class = "col-xs-5">
					{!! Form::number('ship_weight', $product->ship_weight, ['id' => 'ship_weight','class'=>'form-control', 'step' => 'any']) !!}
				</div>
			</div>
			<div class = "form-group">
				{!!Form::label('height','Height: ',['class'=>'control-label col-xs-2'])!!}
				<div class = "col-xs-5">
					{!! Form::number('height', $product->height, ['id' => 'height','class'=>'form-control', 'step' => 'any']) !!}
				</div>
			</div>
			<div class = "form-group">
				{!!Form::label('width','Width: ',['class'=>'control-label col-xs-2'])!!}
				<div class = "col-xs-5">
					{!! Form::number('width', $product->width, ['id' => 'width','class'=>'form-control', 'step' => 'any']) !!}
				</div>
			</div>
			
			<hr />
			
			<div class = "form-group">
				{!!Form::label('product_upc','UPC: ',['class'=>'control-label col-xs-2'])!!}
				<div class = "col-xs-5">
					{!! Form::text('product_upc', $product->product_upc, ['id' => 'product_upc','class'=>'form-control']) !!}
				</div>
			</div>
			<div class = "form-group">
				{!!Form::label('product_asin','ASIN: ',['class'=>'control-label col-xs-2'])!!}
				<div class = "col-xs-5">
					{!! Form::text('product_asin', $product->product_asin, ['id' => 'product_asin','class'=>'form-control']) !!}
				</div>
			</div>
			
			<hr />
			
			<div class = "form-group">
				{!!Form::label('product_price','Product price: ',['class'=>'control-label col-xs-2'])!!}
				<div class = "col-xs-5">
					{!! Form::number('product_price', $product->product_price, ['id' => 'product_price','class'=>'form-control', 'step' => 'any']) !!}
				</div>
			</div>
			<div class = "form-group">
				{!!Form::label('product_sale_price','Product sale price: ',['class'=>'control-label col-xs-2'])!!}
				<div class = "col-xs-5">
					{!! Form::number('product_sale_price', $product->product_sale_price, ['id' => 'product_sale_price','class'=>'form-control', 'step' => 'any']) !!}
				</div>
			</div>
			<div class = "form-group">
				{!!Form::label('product_wholesale_price','Wholesale price: ',['class'=>'control-label col-xs-2'])!!}
				<div class = "col-xs-5">
					{!! Form::number('product_wholesale_price', $product->product_wholesale_price, ['id' => 'product_wholesale_price','class'=>'form-control', 'step' => 'any']) !!}
				</div>
			</div>

			<div class = "form-group">
				{!!Form::label('product_msg_flag','Show Popup: ',['class'=>'control-label col-xs-2'])!!}
				<div class = "col-xs-5">
					@if(strlen($product->product_note) === 0)
						{!! Form::checkbox('msg_flag', 0, 0, ['id' => 'msg_flag', 'class' => 'checkbox']) !!}
					@else
						{!! Form::checkbox('msg_flag', 1, (bool) explode("@", $product->product_note)[1], ['id' => 'msg_flag', 'class' => 'checkbox']) !!}
					@endif

				</div>
			</div>
			<div class = "form-group">
				{!!Form::label('product_product_note','Popup Message Note: ',['class'=>'control-label col-xs-2'])!!}
				<div class = "col-xs-5">
					@if(strlen($product->product_note) === 0)
						{!! Form::textarea('product_note', "", ['id' => 'product_note','class'=>'form-control', 'rows' => 2]) !!}
					@else
						{!! Form::textarea('product_note', explode("@", $product->product_note)[0], ['id' => 'product_note','class'=>'form-control', 'rows' => 2]) !!}
					@endif
				</div>
			</div>


			<div class = "form-group">
				<div class = "col-md-2 pull-right">
					{!! Form::submit('Update product',['class'=>'btn btn-primary btn-block']) !!}
				</div>
			</div>
		{!! Form::close() !!}
		</div>
		
	</div>

</body>
</html>