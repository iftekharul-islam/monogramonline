<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Product - {{$product->id_catalog}}</title>
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
			<li class = "active">View product</li>
		</ol>
		<div class = "col-xs-12">
			<h4 class = "page-header">Product details</h4>
			<div class = "col-md-12">
				<div class="pull-right">
					{!! \App\Task::widget('App\Product', $product->id); !!}
				</div>
				<table class = "table table-bordered">
					<tr>
						<td width="200">Production category</td>
						<td>{{$product->production_category ? $product->production_category->production_category_description : "N/A"}}</td>
					</tr>
					<tr>
						<td>Model(SKU)</td>
						<td>{{$product->product_model}}</td>
					</tr>
					<tr>
						<td>Product name</td>
						<td>{{$product->product_name}}</td>
					</tr>
					<tr>
						<td>Product description</td>
						<td>{{$product->product_description}}</td>
					</tr>
					<tr>
						<td>ID</td>
						<td>{{$product->id_catalog}}</td>
					</tr>
					<tr>
						<td>Product URL</td>
						<td>
							<a href = "{{$product->product_url}}" target = "_blank">
								{{$product->product_url}}
							</a>
						</td>
					</tr>
					<tr>
						<td>Thumb / Insert image</td>
						<td>
							@if($product->product_thumb)
								<img src = "{{$product->product_thumb}}" />
							@else
								No image is set
							@endif
						</td>
					</tr>
					<tr>
						<td>Weight</td>
						<td>{{$product->ship_weight}}</td>
					</tr>
					<tr>
						<td>Height</td>
						<td>{{$product->height}}</td>
					</tr>
					<tr>
						<td>Width</td>
						<td>{{$product->width}}</td>
					</tr>
					<tr>
						<td>UPC</td>
						<td>{{$product->product_upc}}</td>
					</tr>
					<tr>
						<td>ASIN</td>
						<td>{{$product->product_asin}}</td>
					</tr>
					<tr>
						<td>Product price</td>
						<td>{{$product->product_price}}</td>
					</tr>
					<tr>
						<td>Product sale price</td>
						<td>{{$product->product_sale_price}}</td>
					</tr>
					<tr>
						<td>Wholesale price</td>
						<td>{{$product->product_wholesale_price}}</td>
					</tr>
					<tr>
						<td>Show Popup</td>
						<td>{!! Form::checkbox('msg_flag', (bool) explode("@", $product->product_note)[1] ?? 1, (bool) explode("@", $product->product_note)[1] ?? 1, ['id' => 'msg_flag', 'class' => 'checkbox']) !!}</td>
					</tr>
					<tr>
						<td>Popup Message Note</td>
							<td>{{ $note }}</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</body>
</html>
