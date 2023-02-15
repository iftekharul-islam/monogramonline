<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Export/Import options coded SKUs CSV file</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet"
	      href = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
	<link type = "text/css" rel = "stylesheet"
	      href = "//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.9.3/css/bootstrap-select.min.css">
	<link type = "text/css" rel = "stylesheet"
	      href = "//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
	<style>
		table {
			table-layout: fixed;
			font-size: 12px;
		}

		td {
			width: auto;
		}
	</style>
</head>
<body>
	@include('includes.header_menu')
	<div class = "container">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li class = "active">Export/Import options coded SKUs CSV file</li>
		</ol>
		@if($errors->any())
			<div class = "alert alert-danger">
				<ul>
					@foreach($errors->all() as $error)
						<li>{!! $error !!}</li>
					@endforeach
				</ul>
			</div>
		@endif
		@if(session('success'))
			<div class = "alert alert-success">{!! session('success') !!}</div>
		@endif
		<div class = "col-xs-12">
			{!! Form::open(['method' => 'post', 'class' => "form-horizontal", 'files' => true, 'id' => 'sku-import-form']) !!}
			<div class = "form-group">
				{!! Form::label('store_id', "Store id", ['class'=> 'col-xs-2 control-label']) !!}
				<div class = "col-sm-6">
					{!! Form::select('store_id', $stores->pluck('store_name', 'store_id')->prepend('Select a store', 'all'), 'all', ['id'=>'store_id', 'class' => 'form-control']) !!}
				</div>
			</div>
			<div class = "form-group">
				{!! Form::label('file', "Choose file", ['class'=> 'col-xs-2 control-label']) !!}
				<div class = "col-sm-6">
					{!! Form::file('file', ['id'=>'file', 'class' => 'form-control']) !!}
				</div>
			</div>
			{!! Form::hidden('action', null, ['id' => 'action']) !!}
			<div class = "form-group">
				<div class = "col-sm-offset-2 col-sm-10">
					<button type = "button" id = "validate" class = "btn btn-warning">Validate</button>
					<button type = "button" id = "upload-bulk" class = "btn btn-primary">Upload Bulk</button>
					<button type = "button" id = "upload-new" class = "btn btn-default">Upload New</button>
					<button type = "button" id = "export" class = "btn btn-success">Export</button>
					<button type = "button" id = "show" class = "btn btn-info">Show</button>
				</div>
			</div>
			{!! Form::close() !!}
		</div>
		<div class = "col-xs-12" id = "show-info-div" style = "display: none;">
			<h3 class = "page-header">Allowed parameter options are</h3>
			<div class = "col-xs-12" id = "show-info"></div>
		</div>
	</div>
	<script type = "text/javascript" src = "//code.jquery.com/jquery-1.11.3.min.js"></script>
	<script type = "text/javascript" src = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script type = "text/javascript">
		var empty = '<ol class="list-group"></ol>';
		var store_parameters = {};
		@foreach($store_parameters as $store_id => $parameter_value)
				store_parameters['{{ $store_id }}'] = '{!! $parameter_value !!}';
		@endforeach
		$("select").on('change', function (event)
		{
			var show_center = $("div#show-info-div");
			var selected = $(this).val();
			if ( selected == 'all' ) {
				show_center.hide();
				return;
			}

			var parameters = store_parameters[selected];

			if ( parameters == empty ) {
				parameters = "<div class='alert alert-warning'>No parameter option is found</div>";
			}

			show_center.show();
			$("div#show-info").empty();
			$("div#show-info").html(parameters);
		});
		$("button#upload-bulk").on('click', function (event)
		{
			var store_id = $("select#store_id").val();
			var file = $("input#file").val();
			if ( store_id == 'all' ) {
				alert('Please, select a store');
				return;
			}

			if ( !file ) {
				alert('Please, select a file');
				return;
			}

			$("input#action").val('upload-bulk');

			$("form#sku-import-form").submit();
		});

		$("button#upload-new").on('click', function (event)
		{
			var store_id = $("select#store_id").val();
			var file = $("input#file").val();
			if ( store_id == 'all' ) {
				alert('Please, select a store');
				return;
			}

			if ( !file ) {
				alert('Please, select a file');
				return;
			}

			$("input#action").val('upload-new');

			$("form#sku-import-form").submit();
		});

		$("button#validate").on('click', function (event)
		{
			event.preventDefault();
			var store_id = $("select#store_id").val();
			var file = $("input#file").val();
			if ( store_id == 'all' ) {
				alert('Please, select a store');
				return;
			}

			if ( !file ) {
				alert('Please, select a file');
				return;
			}

			$("input#action").val('validate');
			$("form#sku-import-form").submit();
		});

		$("button#export").on('click', function (event)
		{
			event.preventDefault();
			var store_id = $("select#store_id").val();

			if ( store_id == 'all' ) {
				alert('Please, select a store');
				return;
			}

			$("input#action").val('export');
			$("form#sku-import-form").submit();
		});

		$("button#show").on('click', function (event)
		{
			event.preventDefault();
			var store_id = $("select#store_id").val();

			if ( store_id == 'all' ) {
				alert('Please, select a store');
				return;
			}

			var url = "/logistics/sku_list";
			window.open(url, '_blank');

		});
	</script>
</body>
</html>