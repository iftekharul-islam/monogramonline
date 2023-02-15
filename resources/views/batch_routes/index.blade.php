<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Batch routes</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet"
	      href = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
	<link type = "text/css" rel = "stylesheet"
	      href = "//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.9.3/css/bootstrap-select.min.css">
	<link type = "text/css" rel = "stylesheet"
	      href = "//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
	<link rel = "stylesheet" href = "{{url('assets/css/common.css')}}" type = "text/css" />
	<link type = "text/css" href = "{{url('assets/css/ui.multiselect.css')}}" rel = "stylesheet" />
	<link type = "text/css" href = "http://yandex.st/jquery-ui/1.8.11/themes/humanity/jquery.ui.all.min.css"
	      rel = "stylesheet" />
	<style>
		td th {
			font-size: 12px;
		}
	</style>
</head>
<body style = "background:#ffffff ;font-family: Verdana, Arial, Helvetica, sans-serif;color: #000000;">
	@include('includes.header_menu')
	<div style = "min-width: 1550px; margin-left: 10px;">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li class = "active">Batch rotes</li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		<div class = "col-xs-12" >

			<label style = "margin-left:330px">Manage WorkFlow Routes</label>
			<p>Note: Must use a Production and QC Station in each Route</p>
			</table>

		</div>
		<div class = "row">
			<div class = "col-md-12">
				<ul class = "nav nav-tabs" role = "tablist">
					<li role = "presentation">
						<a href = "#tab-export-import" aria-controls = "info" role = "tab"
						   data-toggle = "tab">Export/Import</a>
					</li>
					<li role = "presentation" class = "active">
						<a href = "#tab-batch-list" aria-controls = "description" role = "tab"
						   data-toggle = "tab">Batches</a>
					</li>
				</ul>
				<div class = "clearfix"></div>
				<div class = "tab-content" style = "margin-top: 20px;">
					<div role = "tabpanel" class = "tab-pane fade" id = "tab-export-import">
						<div class = "col-xs-4">
							{!! Form::open(['url' => url('prod_config/import_batch_routes'), 'files' => true, 'id' => 'importer']) !!}
							<div class = "form-group">
								{!! Form::file('csv_file', ['required' => 'required', 'class' => 'form-control', 'accept' => '.csv']) !!}
							</div>
							<div class = "form-group">
								{!! Form::submit('Import', ['class' => 'btn btn-info']) !!}
							</div>
							{!! Form::close() !!}
						</div>
						<div class = "col-xs-2">
							<a class = "btn btn-info pull-right"
							   href = "{{url('/prod_config/export_batch_routes')}}">Export Batch routes</a>
						</div>
					</div>
					<div role = "tabpanel" class = "tab-pane fade in active" id = "tab-batch-list">
						<div class = "col-xs-12">
							@if(count($batch_routes) > 0)
								<table>
									<tr>
										<th style = "padding-bottom:10px; text-align: center;"><b> </b></th>
										<th style = "padding-bottom:10px; text-align: center;"><b>Batch code</b></th>
										<th style = "padding-bottom:10px; text-align: center;"><b>Route name</b></th>
										<th style = "padding-bottom:10px; text-align: center;"><b>Max unit</b></th>
										<th style = "padding-bottom:10px; text-align: center;"><b>Stations</b></th>
										<th style = "padding-bottom:10px; text-align: center;"><b>Export<br>template</b></th>
										<th style = "padding-bottom:10px; text-align: center;"><b>Nesting</b></th>
										<th style = "padding-bottom:10px; text-align: center;"><b>Directory / File Extension</b></th>
										<th style = "padding-bottom:10px; text-align: center;"><b>Options<br>(Comma delimited)</b></th>
										<th style = "padding-bottom:10px; text-align: center;"><b>Action</b></th>
									</tr>

									@foreach($batch_routes as $batch_route)
										<tr data-id = "{{$batch_route->id}}" id = "{{ $batch_route->batch_code }}">
											<td style = "vertical-align: top;margin-right:20px;padding-bottom:7px"><a
														href = "#"
														class = "delete"
														data-toggle = "tooltip"
														data-placement = "top"
														title = "Delete this item">
													<i class = "fa fa-times text-danger"></i> </a>
											</td>
											<td style = "vertical-align: top;padding-bottom:7px;">{!! Form::text('s_batch_code', $batch_route->batch_code, ['style'=>'width:100px;margin-right:10px;margin-left:5px','readonly'=>'readonly']) !!}</td>
											<td style = "vertical-align: top;padding-bottom:7px;">
													{!! Form::text('s_batch_route_name', $batch_route->batch_route_name, ['style'=>'width:250px;margin-right:10px']) !!}
													<br><br>
													Summary Header 1:<br>
													{!! Form::text('s_summary_header_1', $batch_route->summary_msg_1, ['style'=>'width:200px;margin-right:25px']) !!}
													<br><br>
													Summary Header 2:<br>
													{!! Form::text('s_summary_header_1', $batch_route->summary_msg_2, ['style'=>'width:200px;margin-right:25px']) !!}
											</td>
											<td style = "vertical-align: top;padding-bottom:7px;">{!! Form::text('s_batch_max_units', $batch_route->batch_max_units, ['style'=>'width:50px;margin-right:25px']) !!}</td>
											<td style = "vertical-align: top;padding-bottom:7px;">{!! Form::textarea('s_batch_stations', implode(",\n", array_map(function($station) { return $station['station_name']; }, $batch_route->stations_list->toArray())), ['style'=>'width:120px;height:130px;margin-right:10px;overflow-y: scroll;']) !!}</td>
											<td style = "vertical-align: top;padding-bottom:7px;">{!! Form::select('s_export_template', $templates, $batch_route->export_template, ['style'=>'width:70px;margin-right:25px']) !!}</td>
											<td style = "vertical-align: top;padding-bottom:7px;">{!! Form::select('nesting', ['0' => 'No', '1' => 'Yes'], $batch_route->nesting, ['style'=>'width:70px;margin-right:25px']) !!}</td>
											<td style = "vertical-align: top;padding-bottom:7px;">
													CSV Directory:<br>
													{!! Form::text('s_export_dir', $batch_route->export_dir, ['style'=>'width:150px;margin-right:25px', 'placeholder' => 'Directory']) !!}
													<br><br>
													File Extension:<br>
													{!! Form::text('s_csv_extension', $batch_route->csv_extension, ['style'=>'width:150px;margin-right:25px', 'placeholder' => 'File Extension']) !!}
													<br><br>
													Graphic Directory:<br>
													{!! Form::text('s_graphic_dir', $batch_route->graphic_dir, ['style'=>'width:150px;margin-right:25px', 'placeholder' => 'Directory']) !!}
											</td>
											<td style = "vertical-align: top;padding-bottom:7px;">{!! Form::textarea('s_batch_options', $batch_route->batch_options, ['style'=>'width:120px;height:80px;margin-left:25px;margin-right:70px']) !!}</td>
											<td style = "vertical-align: top;padding-bottom:7px;">
												<a href = "#" class = "update" data-toggle = "tooltip"
												   data-placement = "top"
												   title = "Edit this item">
													<button>update</button>
												</a>

											</td>
										</tr>
										<tr>
											<td colspan=9>
												<hr>
											</td>
										</tr>
									@endforeach
								</table>

								<div class = "col-xs-12 text-center">
									{!! $batch_routes->render() !!}
								</div>
								{!! Form::open(['url' => url('/prod_config/batch_routes/id'), 'method' => 'delete', 'id' => 'delete-batch-route']) !!}
								{!! Form::close() !!}

								{!! Form::open(['url' => url('/prod_config/batch_routes/id'), 'method' => 'put', 'id' => 'update-batch-routes']) !!}
								{!! Form::hidden('batch_code', null, ['id' => 'update_batch_code']) !!}
								{!! Form::hidden('batch_route_name', null, ['id' => 'update_batch_route_name']) !!}
								{!! Form::hidden('summary_header_1', null, ['id' => 'update_summary_header_1']) !!}
								{!! Form::hidden('summary_header_2', null, ['id' => 'update_summary_header_2']) !!}
								{!! Form::hidden('batch_max_units', null, ['id' => 'update_batch_max_units']) !!}
								{!! Form::hidden('batch_export_template', null, ['id' => 'update_batch_export_template']) !!}
								{!! Form::hidden('batch_nesting', null, ['id' => 'update_nesting']) !!}
								{!! Form::hidden('batch_stations', null, ['id' => 'update_batch_stations']) !!}
								{!! Form::hidden('export_dir', null, ['id' => 'update_export_dir']) !!}
								{!! Form::hidden('csv_extension', null, ['id' => 'update_csv_extension']) !!}
								{!! Form::hidden('graphic_dir', null, ['id' => 'update_graphic_dir']) !!}
								{!! Form::hidden('batch_options', null, ['id' => 'update_batch_options']) !!}
								{!! Form::close() !!}

							@else
								<div class = "col-xs-12">
									<div class = "alert alert-warning text-center">
										<h3>No batch route found.</h3>
									</div>
								</div>
							@endif
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class = "col-xs-12 ">
			{!! Form::open(['url' => url('/prod_config/batch_routes'), 'method' => 'post', 'id' => 'create-batch-route']) !!}
			<table>
				<tr>
					<td style = "vertical-align: top;"> {!! Form::text('batch_code', null, ['id' => 'batch_code', 'placeholder' => "Enter batch code", 'style'=>'width:100px']) !!} </td>
					<td style = "vertical-align: top;padding-left:10px">{!! Form::text('batch_route_name', null, ['id' => 'batch_route_name', 'placeholder' => "Enter batch route name", 'style'=>'width:250px']) !!}  </td>
					<td style = "vertical-align: top;padding-left:10px"> {!! Form::text('batch_max_units', null, ['id' => 'batch_max_units','style'=>'width:70px' , 'placeholder' => "Enter batch max units"]) !!} </td>
					<td style = "vertical-align: top;padding-left:10px">
						{!! Form::select('batch_stations[]', $stations, null, ['id' => 'countries', 'multiple' => true, 'class' => 'multiselect','style'=>'height:200px']) !!}
					</td>
				</tr>
			</table>
			<br>
			<div class = "col-sm-offset-2 col-sm-4">
				{!! Form::submit('Add', ['style'=>'margin-left:700px;padding:5px 15px']) !!}
			</div>
			{!! Form::close() !!}

		</div>
		<hr style = "width: 100%; color: black; background-color:black;margin-top: 10px" size = "1" />
	</div>

	{{--<script type = "text/javascript" src = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>--}}
	{{--<script type = "text/javascript"
			src = "//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.9.3/js/bootstrap-select.min.js"></script>--}}
	<script type = "text/javascript" src = "{{ url('assets/js/jquery-1.7.2.min.js') }}"></script>
	<script type = "text/javascript" src = "{{ url('assets/js/jquery-ui.js') }}"></script>
	<script type = "text/javascript" src = "{{ url('assets/js/ui.multiselect.js') }}"></script>
	<script type = "text/javascript" src = "//code.jquery.com/jquery-1.11.3.min.js"></script>
	<script type = "text/javascript">
		var newer = jQuery.noConflict();
	</script>
	<script type = "text/javascript" src = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script type = "text/javascript">
		$(function ()
		{
			$(".multiselect").multiselect();
		});
		/*$(function ()
		 {
		 $(".multiselect").multiselect();
		 });*/
		var message = {
			delete: 'Are you sure you want to delete?',
		};
		$("a.delete").on('click', function (event)
		{
			event.preventDefault();
			var id = $(this).closest('tr').attr('data-id');
			var action = confirm(message.delete);
			if ( action ) {
				var form = $("form#delete-batch-route");
				var url = form.attr('action');
				form.attr('action', url.replace('id', id));
				form.submit();
			}
		});
		$("a.update").on('click', function (event)
		{
			debugger;
			event.preventDefault();
			var tr = $(this).closest('tr');
			var id = tr.attr('data-id');
			var code = tr.find('input').eq(0).val();
			var route = tr.find('input').eq(1).val();
			var msg_1 = tr.find('input').eq(2).val();
			var msg_2 = tr.find('input').eq(3).val();
			var unit = tr.find('input').eq(4).val();
			var csv_extension = tr.find('input').eq(6).val();
			var export_dir = tr.find('input').eq(5).val();
			var graphic_dir = tr.find('input').eq(7).val();
			var stations = tr.find('textarea').eq(0).val();
			var export_template = tr.find('select').eq(0).val();
			var nesting = tr.find('select').eq(1).val();
			var options = tr.find('textarea').eq(1).val();

			$("input#update_batch_code").val(code);
			$("input#update_batch_route_name").val(route);
			$("input#update_summary_header_1").val(msg_1);
			$("input#update_summary_header_2").val(msg_2);
			$("input#update_batch_max_units").val(unit);
			$("input#update_batch_stations").val(stations);
			$("input#update_batch_export_template").val(export_template);
			$("input#update_nesting").val(nesting);
			$("input#update_csv_extension").val(csv_extension);
			$("input#update_export_dir").val(export_dir);
			$("input#update_graphic_dir").val(graphic_dir);
			$("input#update_batch_options").val(options);
			
			var form = $("form#update-batch-routes");
			var url = form.attr('action');
			form.attr('action', url.replace('id', id));
			form.submit();
		});

		var form = $("form#create-batch-route");

		$(form).on('submit', function ()
		{

			$("ul.selected li").each(function ()
			{
				var selected_id = $(this).attr('data-selected-id');
				if ( selected_id ) {
					$(form).append("<input type='hidden' value='" + selected_id + "' name='batch_route_order[]' />");
				}
			});
		});

	</script>
</body>
</html>