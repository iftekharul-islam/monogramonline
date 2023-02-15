<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Vendors</title>
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
			<li class = "active"><a href="/purchases/vendors">Vendors</a></li>
		</ol>

		@include('includes.error_div')
		@include('includes.success_div')
		
		<h3 class = "page-header">
			Vendors
		</h3>
		
		<div class = "col-xs-12">
			{!! Form::open(['method' => 'get', 'url' => url('purchases/vendors'), 'id' => 'search-in-vendors']) !!}
			<div class = "form-group col-xs-4">
				<label for = "search_for">Search for</label>
				{!! Form::text('search_for', $request->get('search_for'), ['id'=>'search_for', 'class' => 'form-control', 'placeholder' => 'Search for']) !!}
			</div>
			<div class = "form-group col-xs-3">
				<label for = "search_in">Search in</label>
				{!! Form::select('search_in', $search_in, $request->get('search_in'), ['id'=>'search_in', 'class' => 'form-control']) !!}
			</div>
			<div class = "form-group col-xs-2">
				<label for = "" class = ""></label>
				{!! Form::submit('Search', ['id'=>'search', 'style' => 'margin-top: 2px;', 'class' => 'btn btn-primary form-control']) !!}
			</div>
			<div class = "form-group col-xs-1">
			</div>
			{!! Form::close() !!}
			<div class = "form-group col-xs-2">
				<label for = "" class = ""></label>
				<a class = "btn btn-success form-control" href = "{{url('/purchases/vendors/create')}}">Create vendor</a>
			</div>
		</div>
		
		@if(count($vendors) > 0)
			<table class = "table table-bordered">
				<tr>
					<th>#</th>
					<th>Name</th>
					<th>Email</th>
					<th>Phone number</th>
					<th>Action</th>
				</tr>
				@foreach($vendors as $vendor)
					<tr data-id = "{{$vendor->id}}">
						<td>{{ $vendor->id }}</td>
						<td>{{ substr($vendor->vendor_name, 0, 30) }}</td>
						<td>{{ $vendor->email }}</td>
						<td>{{ $vendor->phone_number }}</td>
						<td>
							<a href = "{{ url(sprintf("/purchases/vendors/%d", $vendor->id)) }}" data-toggle = "tooltip"
							   data-placement = "top"
							   title = "View this vendor"><i class = 'fa fa-eye text-primary'></i></a>
							| <a href = "{{ url(sprintf("/purchases/vendors/%d/edit", $vendor->id)) }}" data-toggle = "tooltip"
							     data-placement = "top"
							     title = "View this vendor"><i class = 'fa fa-pencil-square-o text-success'></i></a>
							| <a href = "#" class = "delete" data-toggle = "tooltip" data-placement = "top"
							     title = "Delete this vendor"><i class = 'fa fa-times text-danger'></i></a>
						</td>
					</tr>
				@endforeach
			</table>
			{!! Form::open(['url' => url('/purchases/vendors/id'), 'method' => 'delete', 'id' => 'delete-vendor']) !!}
			{!! Form::close() !!}
			<div class = "col-xs-12 text-center">
				{!! $vendors->render() !!}
			</div>
		@else
			<div class = "col-xs-12">
				<div class = "alert alert-warning text-center">
					<h3>No vendor found.</h3>
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
		};
		$("a.delete").on('click', function (event)
		{
			event.preventDefault();
			var id = $(this).closest('tr').attr('data-id');
			var action = confirm(message.delete);
			if ( action ) {
				var form = $("form#delete-vendor");
				var url = form.attr('action');
				form.attr('action', url.replace('id', id));
				form.submit();
			}
		});
	</script>
</body>
</html>