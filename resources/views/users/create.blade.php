<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Create User</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet"
	      href = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
</head>
<body>
	@include('includes.header_menu')
	<div class = "container">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "{{url('users')}}">Users</a></li>
			<li class = "active">Create user</li>
		</ol>
		@if($errors->any())
			<div class = "col-xs-12">
				<div class = "alert alert-danger">
					<ul>
						@foreach($errors->all() as $error)
							<li>{{$error}}</li>
						@endforeach
					</ul>
				</div>
			</div>
		@endif


		{!! Form::open(['url' => url('/users'), 'method' => 'post','class'=>'form-horizontal','role'=>'form']) !!}
		<div class = "col-md-12">
	
					<div class = 'form-group'>
						{!!Form::label('username','Username :',['class'=>'control-label col-xs-2'])!!}
						<div class = 'col-xs-5'>
							{!! Form::text('username', null, ['id' => 'username','class' => 'form-control']) !!}
						</div>
					</div>
					<div class = 'form-group'>
						{!!Form::label('email','Email :',['class'=>'control-label col-xs-2'])!!}
						<div class = "col-xs-5">
							{!! Form::email('email', null, ['id' => 'email','class' => 'form-control']) !!}
						</div>
					</div>
					<div class = 'form-group'>
						{!!Form::label('password','Password :',['class'=>'control-label col-xs-2'])!!}
						<div class = "col-xs-5">
							{!! Form::password('password', ['id' => 'password','class' => 'form-control', 'autocomplete' => "new-password"]) !!}
						</div>
					</div>
					<div class = "form-group">
						{!!Form::label('vendor','Vendor :',['class'=>'control-label col-xs-2'])!!}
						<div class = "col-xs-5">
							<?php
								$vendors = ['' => 'Select vendor'] + $vendors;
							?>
							{!! Form::select('vendor', $vendors, null, ['class' => 'form-control']) !!}
						</div>
					</div>
					<div class = 'form-group'>
						{!!Form::label('remote','Remote Access :',['class'=>'control-label col-xs-2'])!!}
						<div class = "col-xs-5">
							{!! Form::checkbox('remote', 1, null, ['id' => 'remote', 'style' => 'margin-top:10px;']) !!}
						</div>
					</div>

					<div class = "form-group">
						{!!Form::label('user_access','User access: ',['class'=>'control-label col-xs-2'])!!}
						<div class = "col-xs-10">
							@setvar($i = 1)
							@setvar($desc = \App\Access::$descriptions)
							@foreach(\App\Access::$pages as $link => $text)
								<div class = "checkbox">
									<label>
										{!! Form::checkbox('user_access[]', $link, false, ['id' => sprintf('user_access-%d', $i),'class'=>'checkbox access-control-checkbox']) !!} {{ $text }}
									</label>
									@if (isset($desc[$link]))
										<small>({{ $desc[$link] }})</small>
									@endif
								</div>
							@endforeach
						</div>
					</div>
					<div class = "form-group">
						<div class = "col-md-10 col-md-offset-2">
							<div class = "checkbox">
								<label>
									{!! Form::checkbox('select-deselect-all', '1', false, ['id' => 'select-deselect-all']) !!} Select/Deselect All Permissions
								</label>
							</div>
						</div>
					</div>
				</div>
			<div class = 'form-group'>
				<div class = "col-xs-12 text-right">
					{!! Form::submit('Create User',['class'=>'btn btn-primary']) !!}
				</div>
			</div>

		{!! Form::close() !!}
	</div>
	<script src = "//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
	<script src = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script type = "text/javascript">
		var state = false;
		$("input#select-deselect-all").on('click', function (event)
		{
			state = !state;
			$("input.access-control-checkbox").prop('checked', state);
		});
	</script>
</body>
</html>