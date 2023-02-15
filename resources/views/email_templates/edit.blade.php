<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Update email template</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet"
	      href = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
	<link type = "text/css" rel = "stylesheet"
	      href = "//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
	<style>

	</style>
</head>
<body>
	@include('includes.header_menu')
	<div class = "container">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "{{url('/customer_service/email_templates')}}">Email Templates</a></li>
			<li class = "active">Update email template</li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		<div class = "col-md-12">
			<div class = "col-md-8">
				{!! Form::open(['url' => url(sprintf("/customer_service/email_templates/%d", $template->id)), 'id' => 'template-update', 'method' => 'put', 'class' => 'form-horizontal' ]) !!}
				<div class = "col-xs-12">
					<div class = "form-group">
						{!! Form::label('message_type', 'Message type: ', ['class' => 'control-label col-xs-3']) !!}
						<div class = "col-xs-9">
							{!! Form::text('message_type', $template->message_type, ['id' => 'message_type', 'class' => "form-control", 'placeholder' => "Message type"]) !!}
						</div>
					</div>
					<div class = "form-group">
						{!! Form::label('message_title', 'Message subject: ', ['class' => 'control-label col-xs-3']) !!}
						<div class = "col-xs-9">
							{!! Form::text('message_title', $template->message_title, ['id' => 'message_title', 'class' => "form-control",]) !!}
						</div>
					</div>
					<div class = "form-group">
						{!! Form::label('message', 'Message template: ', ['class' => 'control-label col-xs-3']) !!}
						<div class = "col-xs-9">
							{!! Form::textarea('message', $template->message, ['id' => 'message', 'class' => "form-control",]) !!}
						</div>
					</div>
				</div>
				<div class = "form-group col-md-12 text-right">
					{!! Form::submit('Update', ['id' => 'submit-update', 'class' => 'btn btn-primary']) !!}
				</div>
				{!! Form::close() !!}
			</div>
			<div class = "col-md-4">
				<p>email template keywords</p>
				<ul class = "list-unstyled">
					@foreach($EMAIL_TEMPLATE_KEYWORDS as $keyword => $keyword_replacer)
						<li><b>{{ $keyword }}</b> >> {{ $keyword_replacer[0] }}</li>
					@endforeach
				</ul>
			</div>
		</div>
	</div>
	<script type = "text/javascript" src = "//code.jquery.com/jquery-1.11.3.min.js"></script>
	<script src = "//cdn.ckeditor.com/4.5.9/full/ckeditor.js"></script>
	<script type = "text/javascript" src = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script type = "text/javascript">
		var message = {
			delete: 'Are you sure you want to delete?',
		};
		var editor = CKEDITOR.replace('message');
	</script>
</body>
</html>