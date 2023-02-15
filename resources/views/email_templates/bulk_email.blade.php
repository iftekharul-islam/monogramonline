<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Send Bulk Emails</title>
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
			<li class = "active">Bulk emails</li>
		</ol>

		@include('includes.error_div')
		@include('includes.success_div')

		{!! Form::open(['url' => url('/customer_service/bulk_email'), 'method' => 'post', 'id' => 'send_bulk_email', 'class' => 'form-horizontal' ]) !!}
		<div class = "col-xs-12">
			<div class = "form-group">
				<div class = "col-xs-3">
					{!! Form::label('order_ids', 'Order IDs: (Comma separated)', ['class' => 'control-label']) !!}
					<br>
					{!! Form::select('id_type', ['' => 'Store Order ID', '5p' => '5p Order ID'], $id_type, ['class' => 'form-control']) !!}
				</div>
				<div class = "col-xs-9">
					{!! Form::textarea('order_ids', $order_ids, ['id' => 'order_ids', 'class' => "form-control", 'placeholder' => 'Order ids']) !!}
				</div>
			</div>
			<div class = "form-group">
				{!! Form::label('template', 'Select a template: ', ['class' => 'control-label col-xs-3']) !!}
				<div class = "col-xs-9">
					{!! Form::select('template', $templates, null,  ['id' => 'message-types', 'class' => "form-control",]) !!}
				</div>
			</div>
		</div>
		<div class = "form-group col-md-12 text-right">
			<div class = "form-group">{!! Form::submit('Send', ['id' => 'submit-update', 'class' => 'btn btn-primary']) !!}</div>
		</div>
		{!! Form::close() !!}
	</div>

	<script type = "text/javascript">
		
	</script>
</body>
</html>