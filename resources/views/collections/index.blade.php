<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Collections</title>
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
			<li class = "active">Collections</li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		<div class = "col-xs-12 text-right" style = "margin: 10px 0;">
			<button class = "btn btn-success" type = "button" data-toggle = "collapse" data-target = "#collapsible-top"
			        aria-expanded = "false" aria-controls = "collapsible">Create collection
			</button>
			<div class = "collapse text-left" id = "collapsible-top">
				{!! Form::open(['url' => url('/products_config/collections'), 'method' => 'post']) !!}
				<div class = "form-group col-xs-12">
					{!! Form::label('collection_code', 'Collection code', ['class' => 'col-xs-2 control-label']) !!}
					<div class = "col-sm-4">
						{!! Form::text('collection_code', null, ['id' => 'collection_code', 'class' => "form-control", 'placeholder' => "Enter Collection code"]) !!}
					</div>
				</div>
				<div class = "form-group col-xs-12">
					{!! Form::label('collection_description', 'Description', ['class' => 'col-xs-2 control-label']) !!}
					<div class = "col-sm-4">
						{!! Form::text('collection_description', null, ['id' => 'collection_description', 'class' => "form-control", 'placeholder' => "Enter Collection description"]) !!}
					</div>
				</div>
				<div class = "form-group col-xs-12">
					{!! Form::label('collection_display_order', 'Display order', ['class' => 'col-xs-2 control-label']) !!}
					<div class = "col-sm-4">
						{!! Form::text('collection_display_order', 0, ['id' => 'collection_display_order', 'class' => "form-control", 'placeholder' => "Enter Collection display order"]) !!}
					</div>
				</div>
				<div class = "col-xs-12 apply-margin-top-bottom">
					<div class = "col-xs-offset-2 col-xs-4">
						{!! Form::submit('Create Collection', ['class' => 'btn btn-primary btn-block']) !!}
					</div>
				</div>
				{!! Form::close() !!}
			</div>
		</div>
		@if(count($collections) > 0)
			<div class = "col-xs-12">
				<table class = "table table-bordered">
					<tr>
						<th>#</th>
						<th>Collection code</th>
						<th>Collection description</th>
						<th>Collection display order</th>
						<th>Action</th>
					</tr>
					@foreach($collections as $collection)
						<tr data-id = "{{$collection->id}}">
							<td>{{ $count++ }}</td>
							<td>
								<input class = "form-control" name = "collection_code" type = "text"
								       value = "{{$collection->collection_code}}">
							</td>
							<td>
								<input class = "form-control" name = "collection_description" type = "text"
								       value = "{{$collection->collection_description}}">
							</td>
							<td>
								<input class = "form-control" name = "collection_display_order" type = "text"
								       value = "{{$collection->collection_display_order}}">
							</td>
							{{--<td>{!! Form::text('collection_code', $collection->collection_code, ['class' => 'form-control']) !!}</td>
							<td>{!! Form::text('collection_description', $collection->collection_description, ['class' => 'form-control']) !!}</td>
							<td>{!! Form::text('collection_display_order', $collection->collection_display_order, ['class' => 'form-control']) !!}</td>--}}
							<td>
								{{--<a href = "{{ url(sprintf("/products_config/categories/%d", $collection->id)) }}" class = "btn btn-success">View</a> | --}}
								<a href = "#" class = "update" data-toggle = "tooltip" data-placement = "top"
								   title = "Edit this item"><i class = "fa fa-pencil-square-o text-success"></i> </a>
								| <a href = "#" class = "delete" data-toggle = "tooltip" data-placement = "top"
								     title = "Delete this item"><i class = "fa fa-times text-danger"></i> </a>
							</td>
						</tr>
					@endforeach
				</table>
			</div>
			<div class = "col-xs-12 text-center">
				{!! $collections->render() !!}
			</div>
			{!! Form::open(['url' => url('/products_config/collections/id'), 'method' => 'delete', 'id' => 'delete-collection']) !!}
			{!! Form::close() !!}

			{!! Form::open(['url' => url('/products_config/collections/id'), 'method' => 'put', 'id' => 'update-collection']) !!}
			{!! Form::hidden('collection_code', null, ['id' => 'update_collection_code']) !!}
			{!! Form::hidden('collection_description', null, ['id' => 'update_collection_description']) !!}
			{!! Form::hidden('collection_display_order', null, ['id' => 'update_collection_display_order']) !!}
			{!! Form::close() !!}
		@else
			<div class = "col-xs-12">
				<div class = "alert alert-warning text-center">
					<h3>No collection found.</h3>
				</div>
			</div>
		@endif
		<div class = "col-xs-12 text-right" style = "margin: 10px 0;">
			<button class = "btn btn-success" type = "button" data-toggle = "collapse"
			        data-target = "#collapsible-bottom"
			        aria-expanded = "false" aria-controls = "collapsible">Create collection
			</button>
			<div class = "collapse text-left" id = "collapsible-bottom">
				{!! Form::open(['url' => url('/products_config/collections'), 'method' => 'post']) !!}
				<div class = "form-group col-xs-12">
					{!! Form::label('collection_code', 'collection code', ['class' => 'col-xs-2 control-label']) !!}
					<div class = "col-sm-4">
						{!! Form::text('collection_code', null, ['id' => 'collection_code', 'class' => "form-control", 'placeholder' => "Enter collection code"]) !!}
					</div>
				</div>
				<div class = "form-group col-xs-12">
					{!! Form::label('collection_description', 'Description', ['class' => 'col-xs-2 control-label']) !!}
					<div class = "col-sm-4">
						{!! Form::text('collection_description', null, ['id' => 'collection_description', 'class' => "form-control", 'placeholder' => "Enter collection description"]) !!}
					</div>
				</div>
				<div class = "form-group col-xs-12">
					{!! Form::label('collection_display_order', 'Display order', ['class' => 'col-xs-2 control-label']) !!}
					<div class = "col-sm-4">
						{!! Form::text('collection_display_order', 0, ['id' => 'collection_display_order', 'class' => "form-control", 'placeholder' => "Enter collection display order"]) !!}
					</div>
				</div>
				<div class = "col-xs-12 apply-margin-top-bottom">
					<div class = "col-xs-offset-2 col-xs-4">
						{!! Form::submit('Create production collection', ['class' => 'btn btn-primary btn-block']) !!}
					</div>
				</div>
				{!! Form::close() !!}
			</div>
		</div>
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
				var form = $("form#delete-collection");
				var url = form.attr('action');
				form.attr('action', url.replace('id', id));
				form.submit();
			}
		});

		$("a.update").on('click', function (event)
		{
			event.preventDefault();
			var tr = $(this).closest('tr');
			var id = tr.attr('data-id');

			var code = tr.find('input').eq(0).val();
			var description = tr.find('input').eq(1).val();
			var order = tr.find('input').eq(2).val();

			$("input#update_collection_code").val(code);
			$("input#update_collection_description").val(description);
			$("input#update_collection_display_order").val(order);
			var form = $("form#update-collection");
			var url = form.attr('action');
			form.attr('action', url.replace('id', id));
			form.submit();
		});
	</script>
</body>
</html>