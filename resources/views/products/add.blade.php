<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Sync product</title>
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
			<li><a href = "{{url('products')}}">Products</a></li>
			<li class = "active">Sync products</li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		<div class = "col-xs-12">
			{!! Form::open(['method' => 'post', 'id' => 'sync-product', 'class' => 'form-horizontal']) !!}
			<div class = "form-group">
				<label for = "store" class = "col-sm-3 control-label">Market/Store</label>
				<div class = "col-sm-6">
					{!! Form::select('store', $stores, null, ['id'=>'store', 'class' => 'form-control']) !!}
				</div>
			</div>
			<div class = "form-group">
				<label for = "product_id_catalogs" class = "col-sm-3 control-label">ID Catalog(s)</label>
				<div class = "col-sm-6">
					{!! Form::text('product_id_catalogs', null, ['id'=>'product_id_catalogs', 'class' => 'form-control', 'placeholder' => 'Enter product id catalogs separated by comma']) !!}
				</div>
			</div>
			<div class = "form-group">
				<div class = "checkbox">
					<label for = "sync_all" class = "col-sm-offset-3 control-label">
						{!! Form::checkbox('sync_all', 1, false, ['id'=>'sync_all']) !!}
						Sync all?
					</label>
				</div>
			</div>

			<div class = "form-group">
				<div class = "col-xs-offset-3 col-sm-6">
					{!! Form::submit('Sync product', ['id' => 'add-order', 'class' => 'btn btn-primary']) !!}
				</div>
			</div>
			{!! Form::close() !!}
		</div>
	</div>
	<script type = "text/javascript" src = "//code.jquery.com/jquery-1.11.3.min.js"></script>
	<script type = "text/javascript" src = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script type = "text/javascript">
		/*document.getElementById('ToOrder').onchange = function ()
		 {
		 document.getElementById('to_order_id').disabled = !this.checked;
		 }*/
	</script>
</body>
</html>