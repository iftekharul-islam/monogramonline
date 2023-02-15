<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Occasions</title>
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
			<li class = "active">Occasions</li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		<div class = "col-xs-12 text-right" style = "margin: 10px 0;">
			<button class = "btn btn-success" type = "button" data-toggle = "collapse" data-target = "#collapsible-top"
			        aria-expanded = "false" aria-controls = "collapsible">Create occasion
			</button>
			<div class = "collapse text-left" id = "collapsible-top">
				{!! Form::open(['url' => url('/products_config/occasions'), 'method' => 'post']) !!}
				<div class = "form-group col-xs-12">
					{!! Form::label('occasion_code', 'Collection code', ['class' => 'col-xs-2 control-label']) !!}
					<div class = "col-sm-4">
						{!! Form::text('occasion_code', null, ['id' => 'occasion_code', 'class' => "form-control", 'placeholder' => "Enter Collection code"]) !!}
					</div>
				</div>
				<div class = "form-group col-xs-12">
					{!! Form::label('occasion_description', 'Description', ['class' => 'col-xs-2 control-label']) !!}
					<div class = "col-sm-4">
						{!! Form::text('occasion_description', null, ['id' => 'occasion_description', 'class' => "form-control", 'placeholder' => "Enter Collection description"]) !!}
					</div>
				</div>
				<div class = "form-group col-xs-12">
					{!! Form::label('occasion_display_order', 'Display order', ['class' => 'col-xs-2 control-label']) !!}
					<div class = "col-sm-4">
						{!! Form::text('occasion_display_order', 0, ['id' => 'occasion_display_order', 'class' => "form-control", 'placeholder' => "Enter Collection display order"]) !!}
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
		@if(count($occasions) > 0)
			<div class = "col-xs-12">
				<table class = "table table-bordered">
					<tr>
						<th>#</th>
						<th>Collection code</th>
						<th>Collection description</th>
						<th>Collection display order</th>
						<th>Action</th>
					</tr>
					@foreach($occasions as $occasion)
						<tr data-id = "{{$occasion->id}}">
							<td>{{ $count++ }}</td>
							<td>
								<input class = "form-control" name = "occasion_code" type = "text"
								       value = "{{$occasion->occasion_code}}">
							</td>
							<td>
								<input class = "form-control" name = "occasion_description" type = "text"
								       value = "{{$occasion->occasion_description}}">
							</td>
							<td>
								<input class = "form-control" name = "occasion_display_order" type = "text"
								       value = "{{$occasion->occasion_display_order}}">
							</td>
							{{--<td>{!! Form::text('occasion_code', $occasion->occasion_code, ['class' => 'form-control']) !!}</td>
							<td>{!! Form::text('occasion_description', $occasion->occasion_description, ['class' => 'form-control']) !!}</td>
							<td>{!! Form::text('occasion_display_order', $occasion->occasion_display_order, ['class' => 'form-control']) !!}</td>--}}
							<td>
								{{--<a href = "{{ url(sprintf("/products_config/categories/%d", $occasion->id)) }}" class = "btn btn-success">View</a> | --}}
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
				{!! $occasions->render() !!}
			</div>
			{!! Form::open(['url' => url('/products_config/occasions/id'), 'method' => 'delete', 'id' => 'delete-occasion']) !!}
			{!! Form::close() !!}

			{!! Form::open(['url' => url('/products_config/occasions/id'), 'method' => 'put', 'id' => 'update-occasion']) !!}
			{!! Form::hidden('occasion_code', null, ['id' => 'update_occasion_code']) !!}
			{!! Form::hidden('occasion_description', null, ['id' => 'update_occasion_description']) !!}
			{!! Form::hidden('occasion_display_order', null, ['id' => 'update_occasion_display_order']) !!}
			{!! Form::close() !!}
		@else
			<div class = "col-xs-12">
				<div class = "alert alert-warning text-center">
					<h3>No occasion found.</h3>
				</div>
			</div>
		@endif
		<div class = "col-xs-12 text-right" style = "margin: 10px 0;">
			<button class = "btn btn-success" type = "button" data-toggle = "collapse"
			        data-target = "#collapsible-bottom"
			        aria-expanded = "false" aria-controls = "collapsible">Create occasion
			</button>
			<div class = "collapse text-left" id = "collapsible-bottom">
				{!! Form::open(['url' => url('/products_config/occasions'), 'method' => 'post']) !!}
				<div class = "form-group col-xs-12">
					{!! Form::label('occasion_code', 'occasion code', ['class' => 'col-xs-2 control-label']) !!}
					<div class = "col-sm-4">
						{!! Form::text('occasion_code', null, ['id' => 'occasion_code', 'class' => "form-control", 'placeholder' => "Enter occasion code"]) !!}
					</div>
				</div>
				<div class = "form-group col-xs-12">
					{!! Form::label('occasion_description', 'Description', ['class' => 'col-xs-2 control-label']) !!}
					<div class = "col-sm-4">
						{!! Form::text('occasion_description', null, ['id' => 'occasion_description', 'class' => "form-control", 'placeholder' => "Enter occasion description"]) !!}
					</div>
				</div>
				<div class = "form-group col-xs-12">
					{!! Form::label('occasion_display_order', 'Display order', ['class' => 'col-xs-2 control-label']) !!}
					<div class = "col-sm-4">
						{!! Form::text('occasion_display_order', 0, ['id' => 'occasion_display_order', 'class' => "form-control", 'placeholder' => "Enter occasion display order"]) !!}
					</div>
				</div>
				<div class = "col-xs-12 apply-margin-top-bottom">
					<div class = "col-xs-offset-2 col-xs-4">
						{!! Form::submit('Create production occasion', ['class' => 'btn btn-primary btn-block']) !!}
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
				var form = $("form#delete-occasion");
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

			$("input#update_occasion_code").val(code);
			$("input#update_occasion_description").val(description);
			$("input#update_occasion_display_order").val(order);
			var form = $("form#update-occasion");
			var url = form.attr('action');
			form.attr('action', url.replace('id', id));
			form.submit();
		});
	</script>
</body>
</html>