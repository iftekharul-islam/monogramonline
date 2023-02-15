<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Parameters</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap-select.css">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
				
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>

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
			<li class = "active">Parameters</li>
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

			<div class = "col-md-6 col-md-offset-3">
				{!! Form::open(['url' => url("logistics/update_parameters"), 'method' => 'put', 'class' => 'form-horizontal', 'id' => 'parameter-list-form']) !!}

				<table class = "table table-bordered" id = "parameters-table">
					<thead>
					<tr>
						<th class = "text-center">Parameters</th>
					</tr>
					</thead>
					<tbody id = "parameters-table-body">
					@if(count($parameters))
						@foreach($parameters as $parameter)
							<tr>
								<td>
									<div class = "input-group">
										{!! Form::text('parameters[]', $parameter->parameter_value, ['class' => 'form-control parameter', 'id' => sprintf("parameter-%d", $index++), 'placeholder' => 'Parameter value']) !!}
										<div class = "input-group-btn">
											<button type = "button" class = "btn btn-default move-up"
											        data-toggle = "tooltip"
											        data-placement = "top" title = "Move up">
												<span class = "glyphicon glyphicon-menu-up"></span>
											</button>
											<button type = "button" class = "btn btn-default move-down"
											        data-toggle = "tooltip"
											        data-placement = "top" title = "Move down">
												<span class = "glyphicon glyphicon-menu-down"></span>
											</button>
											<button type = "button" class = "btn btn-default removable"
											        data-toggle = "tooltip"
											        data-placement = "top" title = "Remove" id = "addon-{{$index}}">
												<span class = "glyphicon glyphicon-remove text-danger"></span>
											</button>
										</div>
									</div>
								</td>
							</tr>
						@endforeach
					@endif
					</tbody>
					<tfoot>
					<tr class = "text-right">
						<td>
							<button type = "button" class = "btn btn-primary btn-sm add-new-parameter-to-table">
								Add new parameter
							</button>
						</td>
					</tr>
					</tfoot>
				</table>
				<div class = "form-group">
					<div class = "col-sm-6">
						<button type = "submit" class = "btn btn-success">Update</button>
					</div>
				</div>
				{!! Form::close() !!}
			</div>

	</div>


	<script type = "text/javascript">
		$(function ()
		{
			$("body").tooltip({selector: '[data-toggle="tooltip"]'});
			table_row_repositioning_method();
		});
		function add_new_row (position)
		{
			var row = '<tr>\
						<td>\
							<div class="input-group">\
								<input class="form-control parameter" id="parameter-INDEX_NUMBER" placeholder="Parameter value" name="parameters[]" type="text">\
								<div class="input-group-btn">\
									<button type="button" class="btn btn-default move-up" data-toggle="tooltip" data-placement="top" title="Move up">\
										<span class="glyphicon glyphicon-menu-up"></span>\
									</button>\
									<button type="button" class="btn btn-default move-down" data-toggle="tooltip" data-placement="top" title="Move down">\
										<span class="glyphicon glyphicon-menu-down"></span>\
									</button>\
									<button type="button" class="btn btn-default removable" data-toggle="tooltip" data-placement="top" title="Remove" id="addon-19">\
										<span class="glyphicon glyphicon-remove text-danger"></span>\
									</button>\
								</div>\
							</div>\
						</td>\
					</tr>';
			if ( $(position).length ) {
				$(position).after($(row));
			} else {
				var parent = $("table#parameters-table tbody#parameters-table-body");
				$(parent).append($(row));
			}

			table_row_repositioning_method();
		}
		function table_row_repositioning_method ()
		{
			$("tbody#parameters-table-body tr").each(function ()
			{
				var has_next = $(this).next().length ? true : false;
				var has_previous = $(this).prev().length ? true : false;

				if ( has_next ) {
					$(this).find('button.move-down').show();
				} else {
					$(this).find('button.move-down').hide();
				}

				if ( has_previous ) {
					$(this).find('button.move-up').show();
				} else {
					$(this).find('button.move-up').hide();
				}
			});
		}
		$("button.add-new-parameter-to-table").on('click', function (event)
		{
			var tr = $("table tbody#parameters-table-body tr:last");
			if ( !tr ) {
				tr = $("tbody#draggable-table-rows");
			}
			add_new_row(tr);
		});
		$("select#store_id").on('change', function ()
		{
			$(this).closest('form').submit();
		});
		$("body").on('click', 'button.move-up', function (event)
		{
			var current_row = $(this).closest('tr');
			var previous_row = current_row.prev();
			if ( previous_row.length ) {
				previous_row.before(current_row);
			}
			table_row_repositioning_method();
		});
		$("body").on('click', 'button.move-down', function (event)
		{
			var current_row = $(this).closest('tr');
			var next_row = current_row.next();
			if ( next_row.length ) {
				next_row.after(current_row);
			}
			table_row_repositioning_method();
		});

		$("body").on('click', "button.removable", function ()
		{
			var answer = confirm('Are you sure to remove this parameter?');
			if ( answer ) {
				$(this).closest('tr').remove();
			}
			table_row_repositioning_method();
		});
	</script>
</body>
</html>