<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Product Specs</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet"
	      href = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
	<link type = "text/css" rel = "stylesheet"
	      href = "//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
	<link type = "text/css" rel = "stylesheet"
	      href = "/assets/css/bootstrap-horizon.css" />
	<style>
		.parent-selector {
			width: 200px;
			overflow: auto;
		}
	</style>
</head>
<body>
	@include('includes.header_menu')
	<div class = "container">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li>Product specifications</li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		<div class = "col-md-12">
			<ul class = "nav nav-tabs" role = "tablist">
				{{--<li role = "presentation">
					<a href = "#tab-export-import" aria-controls = "info" role = "tab"
					   data-toggle = "tab">Export/Import</a>
				</li>--}}
				<li role = "presentation" class = "active">
					<a href = "#tab-search" aria-controls = "description" role = "tab"
					   data-toggle = "tab">Search</a>
				</li>
			</ul>
			<div class = "clearfix"></div>
			<div class = "tab-content" style = "margin-top: 20px;">
				<div role = "tabpanel" class = "tab-pane fade" id = "tab-export-import">
					<div class = "col-xs-6">
						{!! Form::open(['url' => url('products/import'), 'files' => true, 'id' => 'importer']) !!}
						<div class = "form-group">
							{!! Form::file('csv_file', ['required' => 'required', 'class' => 'form-control', 'accept' => '.csv']) !!}
						</div>
						<div class = "form-group">
							{!! Form::submit('Import', ['class' => 'btn btn-info']) !!}
						</div>
						{!! Form::close() !!}
					</div>
					<div class = "col-xs-6">
						<a class = "btn btn-info pull-right" href = "{{url('products/export')}}">Export products</a>
					</div>
				</div>
				<div role = "tabpanel" class = "tab-pane fade in active" id = "tab-search">
					<div class = "col-xs-12">
						{!! Form::open(['method' => 'get', 'id' => 'search-product']) !!}
						<div class = "col-md-12">
							<div class = "form-group col-xs-3">
								<label for = "search_for_1">Search for 1 </label>
								{!! Form::text('search_for_1', $request->get('search_for_1'), ['id'=>'search_for_1', 'class' => 'form-control', 'placeholder' => 'Search for']) !!}
							</div>
							<div class = "form-group col-xs-3">
								<label for = "search_in_1">Search in 1</label>
								{!! Form::select('search_in_1', \App\SpecificationSheet::$searchable_fields, $request->get('search_in_1'), ['id'=>'search_in', 'class' => 'form-control']) !!}
							</div>
							<div class = "form-group col-xs-3">
								<label for = "search_for_2">Search for 2</label>
								{!! Form::text('search_for_2', $request->get('search_for_2'), ['id'=>'search_for_2', 'class' => 'form-control', 'placeholder' => 'Search for']) !!}
							</div>
							<div class = "form-group col-xs-3">
								<label for = "search_in_2">Search in 2</label>
								{!! Form::select('search_in_2', \App\SpecificationSheet::$searchable_fields, $request->get('search_in_2'), ['id'=>'search_in', 'class' => 'form-control']) !!}
							</div>
						</div>
						<div class = "col-md-12">
							<div class = "form-group col-xs-3">
								<label for = "production_category">Search in production category</label>
								{!! Form::select('production_category', $production_categories, $request->get('production_category') ?: 'all', ['id'=>'production_category', 'class' => 'form-control']) !!}
							</div>
							<div class = "form-group col-xs-3">
								<label for = "status">Search in status</label>
								{!! Form::select('status', $statuses, $request->get('status'), ['id'=>'production_category', 'class' => 'form-control']) !!}
							</div>
							<div class = "form-group col-md-3">
								<label for = "status">Search in web image status</label>
								{!! Form::select('web_image_status', \App\SpecificationSheet::$webImageStatus, $request->get('web_image_status'), ['id'=>'web_image_status', 'class' => 'form-control']) !!}
							</div>
							<div class = "form-group col-md-3">
								<label for = "status">Search in Make sample</label>
								{!! Form::select('make_sample', $make_sample_data, $request->get('make_sample', 'all'), ['id'=>'make_sample', 'class' => 'form-control']) !!}
							</div>
						</div>
						<div class = "col-md-12">
							<div class = "form-group col-xs-3 pull-right">
								<div class = "col-md-6">
									<label for = "" class = ""></label>
									{!! Form::button('Reset', ['id'=>'reset', 'type' => 'reset', 'style' => 'margin-top: 2px;', 'class' => 'btn btn-warning form-control']) !!}
								</div>
								<div class = "col-md-6">
									<label for = "" class = ""></label>
									{!! Form::submit('Search', ['id'=>'search', 'style' => 'margin-top: 2px;', 'class' => 'btn btn-primary form-control']) !!}
								</div>
							</div>
						</div>
						{{--<div class = "form-group col-xs-2">
							<label for = "" class = ""></label>
							{!! Form::submit('Search', ['id'=>'search', 'style' => 'margin-top: 2px;', 'class' => 'btn btn-primary form-control']) !!}
						</div>--}}
						{!! Form::close() !!}
					</div>
				</div>
			</div>
		</div>
		<hr />
		@if(isset($specSheets) && count($specSheets) > 0)
			<h3 class = "page-header">
				Specs ({{ $specSheets->total() }} items found / {{$specSheets->currentPage()}} of {{$specSheets->lastPage()}} pages)
				<a style = "margin-bottom:20px" class = "btn btn-success btn-sm pull-right"
				   href = "{{url('products_specifications/step/1')}}">Create product spec</a>
			</h3>
			{{-- redaundant form --}}
			{!! Form::open(['url' => url('/products_specifications/sheets'), 'method' => 'get']) !!}
			<table class = "table">
				<thead>
				<tr>
					<th>Print</th>
					<th>Remove</th>
					<th>#</th>
					<th>Product Spec name</th>
					<th>Production category</th>
					<th>SKU</th>
					{{--<th>Image</th>--}}
					<th>Web image status</th>
					<th>Action</th>
				</tr>
				</thead>
				<tbody>
				@foreach($specSheets as $specSheet)
					<tr data-id = "{{$specSheet->id}}" class = "text-center">
						<td>
							<a href = "{{  url(sprintf("/products_specifications/sheets?spec_id[]=%d",$specSheet->id)) }}">Print</a>
						</td>
						<td>
							<a href = "#" class = "delete" data-toggle = "tooltip" data-placement = "top"
							   title = "Delete this product"><i class = 'fa fa-times text-danger'></i></a>
						</td>
						<td>{{ $count++ }}</td>
						<td> {{ $specSheet->product_name }}</td>
						<td> {{ $specSheet->production_category->production_category_description }} </td>
						<td>{{ $specSheet->product_sku }}</td>
						{{--<td>
							@foreach(json_decode($specSheet->images) as $image)
								<a target = "_blank" href = "{{$image}}"><img src = "{{ $image }}"
								                                              style = "width: 50px; height: 50px;" /></a>
							@endforeach
						</td>--}}
						<td>{{ $specSheet->web_image_status ? \App\SpecificationSheet::$webImageStatus[$specSheet->web_image_status] : "N/A" }}</td>
						<td>
							<a href = "{{ url(sprintf("/products_specifications/%d", $specSheet->id)) }}"
							   data-toggle = "tooltip"
							   data-placement = "top"
							   title = "View this product spec"><i class = 'fa fa-eye text-warning'></i></a>
							| <a href = "{{ url(sprintf("/products_specifications/%d/edit", $specSheet->id)) }}"
							     data-toggle = "tooltip"
							     data-placement = "top"
							     title = "Edit this product spec"><i
										class = 'fa fa-pencil-square-o text-success'></i></a>
						</td>
					</tr>
				@endforeach
				</tbody>
				{{--<tfoot>
				<tr colspan = "7">
					<td>
						{!! Form::submit('Print slip', ['id' => 'print-slip', 'class' => 'btn btn-link']) !!}
					</td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
				</tfoot>--}}
			</table>
			{!! Form::close() !!}
			{!! Form::open(['url' => url('/products_specifications/id'), 'method' => 'delete', 'id' => 'delete-product']) !!}
			{!! Form::close() !!}

			{!! Form::open(['url' => url('/products_specifications/id'), 'method' => 'put', 'id' => 'update-product']) !!}
			{!! Form::close() !!}
			<div class = "col-xs-12 text-center">
				{!! $specSheets->appends($request->all())->render() !!}
			</div>
		@else
			<h3 class = "page-header">
				Specs <a style = "margin-bottom:20px" class = "btn btn-success btn-sm pull-right"
				         href = "{{url('products_specifications/step/1')}}">Create product spec</a>
			</h3>
			<div class = "col-xs-12">
				<div class = "alert alert-warning text-center">
					<h3>No product spec found.</h3>
				</div>
			</div>
		@endif
	</div>
	<script type = "text/javascript" src = "//code.jquery.com/jquery-1.11.3.min.js"></script>
	<script type = "text/javascript" src = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script type = "text/javascript">
		$(function ()
		{
			$('[data-toggle="tooltip"]').tooltip();
		});
		var message = {
			delete: 'Are you sure you want to delete?',
			update: 'Are you sure you want to update?',
			error: "You've not selected any route value to update",
		};
		$("a.delete").on('click', function (event)
		{
			event.preventDefault();
			var id = $(this).closest('tr').attr('data-id');
			var action = confirm(message.delete);
			if ( action ) {
				var form = $("form#delete-product");
				var url = form.attr('action');
				form.attr('action', url.replace('id', id));
				form.submit();
			}
		});

		$("#print-slip").click(function (e)
		{
			e.preventDefault();
			var count = $(".printable:checked").length;
			if ( count == 0 ) {
				alert('Select checkbox to print');
				return false;
			} else {
				$(this).closest('form').submit();
			}
		});
	</script>
</body>
</html>