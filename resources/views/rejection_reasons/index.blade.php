<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Rejection messages</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet"
	      href = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
	<link type = "text/css" rel = "stylesheet"
	      href = "//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
	
	<style>
	#submitButton {
	  font-family: FontAwesome;
	}
	</style>
</head>
<body>
	@include('includes.header_menu')
	<div class = "container">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li class = "active">Rejection messages</li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		<div class = "col-xs-12 text-right" style = "margin: 10px 0;">
			<button class = "btn btn-success" type = "button" data-toggle = "collapse" data-target = "#collapsible-top"
			        aria-expanded = "false" aria-controls = "collapsible">Add rejection message
			</button>
			<div class = "collapse text-left" id = "collapsible-top">
				{!! Form::open(['url' => url('/prod_config/rejection_reasons'), 'method' => 'post']) !!}
			<div class = "form-group col-xs-12">
					{!! Form::label('rejection_message', 'Rejection message', ['class' => 'col-xs-2 control-label']) !!}
					<div class = "col-sm-4">
						{!! Form::text('rejection_message', null, ['id' => 'rejection_message', 'class' => "form-control", 'placeholder' => "Enter rejection message"]) !!}
					</div>
				</div>
				<div class = "col-xs-12 apply-margin-top-bottom">
					<div class = "col-xs-offset-2 col-xs-4">
						{!! Form::submit('Add', ['class' => 'btn btn-primary btn-block']) !!}
					</div>
				</div>
				{!! Form::close() !!}
			</div>
		</div>
		@if(count($rejection_reasons) > 0)
			<div class = "col-xs-12">
				<table class = "table table-bordered">
					<tr>
						<th colspan=2></th>
						<th>Rejection message</th>
						<th></th>
					</tr>
					@foreach($rejection_reasons as $rejection_reason)
						<tr data-id = "{{$rejection_reason->id}}">
							@setvar($count++)
							<td width="100">
								@if($count > 1)
									<a href = "{{ url(sprintf('/prod_config/rejection_reasons/sort/up/%s', $rejection_reason->id)) }}"
											data-toggle = "tooltip" data-placement = "top"
											title = "Move Up"><i class = 'glyphicon glyphicon-chevron-up'></i></a>
								@endif
							</td>
							<td width="100">
								@if($count < count($rejection_reasons))
									<a href = "{{ url(sprintf('/prod_config/rejection_reasons/sort/down/%s', $rejection_reason->id)) }}"
											data-toggle = "tooltip" data-placement = "top"
											title = "Move Down"><i class = 'glyphicon glyphicon-chevron-down'></i></a>
								@endif
							</td>
							<td>
								{{ $rejection_reason->rejection_message }}
							</td>
							<td>
								{!! Form::open(['url' => url('/prod_config/rejection_reasons/' . $rejection_reason->id), 'method' => 'delete', 'id' => 'delete-rejection-reason']) !!}
								{!! Form::submit('Delete', ['class'=>'btn btn-danger btn-xs']) !!}
								{!! Form::close() !!}
							</td>
						</tr>
					@endforeach
				</table>
			</div>
			<div class = "col-xs-12 text-center">
				{!! $rejection_reasons->render() !!}
			</div>

		@else
			<div class = "col-xs-12">
				<div class = "alert alert-warning text-center">
					<h3>No rejection reason found.</h3>
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
	</script>
</body>
</html>