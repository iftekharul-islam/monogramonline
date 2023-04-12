<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Products</title>
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
			<li><a href = "{{url('/products')}}">Products</a></li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		<div class = "col-md-12">
					<div class = "col-xs-12">
						{!! Form::open(['method' => 'get', 'id' => 'search-product']) !!}
							<div class = "form-group col-xs-3">
								<label for = "search_for">Search for</label>
								{!! Form::text('search_for', $request->get('search_for'), ['id'=>'search_for', 'class' => 'form-control', 'placeholder' => 'Search for']) !!}
							</div>
							<div class = "form-group col-xs-3">
								<label for = "search_in">Search in</label>
								{!! Form::select('search_in', \App\Product::$searchable_fields, $request->get('search_in'), ['id'=>'search_in', 'class' => 'form-control']) !!}
							</div>
							<div class = "form-group  col-xs-3">
								<label for = "product_production_category">Search in production category</label>
								{!! Form::select('product_production_category[]', $production_categories, $request->get('product_production_category') ?: 'all', ['id'=>'product_production_category', 'class' => 'form-control', 'multiple' => true]) !!}
							</div>
							<div class = "form-group col-xs-2">
								<label for = "" class = ""></label>
								{!! Form::submit('Search', ['id'=>'search', 'style' => 'margin-top: 2px;', 'class' => 'btn btn-primary form-control']) !!}
							</div>
						{!! Form::close() !!}
					</div>
			</div>
		<hr />
		@if(count($products) > 0)
			<h3 class = "page-header">
				Products ({{ $products->total() }} items found / {{$products->currentPage()}} of {{$products->lastPage()}} pages)
				<a style = "margin-bottom:20px" class = "btn btn-success btn-sm pull-right"
				   href = "{{url('/products/create')}}">Create product</a>
			</h3>
			<table class = "table table-bordered">
				<tr>
					<th>SKU</th>
					<th colspan=2>Product</th>
					<th width=100>Action</th>
				</tr>
				@foreach($products as $product)
					<tr data-id = "{{$product->id}}">
						<td>
								<a href = "{{ url(sprintf("/products/%d", $product->id)) }}">{{$product->product_model}}</a>
						</td>
						<td><img src = "{{ $product->product_thumb }}" width = "50" height = "50" /></td>
						<td>
								{{ $product->product_name }}
								<br>
								<a href = "{{$product->product_url}}" target = "_blank">{{ $product->id_catalog }}</a>
						</td>
						<td>
						   <a href = "{{ url(sprintf("/products/%d/edit", $product->id)) }}" data-toggle = "tooltip"
							     data-placement = "top"
							     title = "Edit this product"><i class = 'glyphicon glyphicon-pencil text-success'></i></a>
							| {!! \App\Task::widget('App\Product', $product->id, null, 12); !!}
							<p><b>{{ $product->manufacture->name ?? '' }}</b></p>
						</td>
					</tr>
				@endforeach
			</table>
			
			<div class = "col-xs-12 text-center">
				{!! $products->appends($request->all())->render() !!}
			</div>
			
		@else
			<div class = "col-xs-12">
				<div class = "alert alert-warning text-center">
					No products found.
				</div>
			</div>
		@endif
	</div>
</body>
</html>