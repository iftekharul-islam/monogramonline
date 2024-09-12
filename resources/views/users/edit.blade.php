<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Edit User - {{$user->username}}</title>
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
			<li><a href = "{{url('users')}}">Users</a></li>
			<li class = "active">Edit user</li>
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

		<div class = "col-md-12">
			{!! Form::open(['url' => url(sprintf("/users/%d", $user->id)), 'method' => 'put','class'=>'form-horizontal','role'=>'form']) !!}
						<div class = "form-group">
							{!!Form::label('username','Username :',['class'=>'control-label col-xs-2'])!!}
							<div class = "col-xs-5">
								{!! Form::text('username', $user->username, ['id' => 'username','class'=>'form-control']) !!}
							</div>
						</div>
						<div class = "form-group">
							{!!Form::label('password','Password :',['class'=>'control-label col-xs-2'])!!}
							<div class = "col-xs-5">
								{!! Form::password('password', ['id' => 'password', 'placeholder' => 'Insertion will set new password','class'=>'form-control']) !!}
							</div>
						</div>
						<div class = "form-group">
							{!!Form::label('email','Email :',['class'=>'control-label col-xs-2'])!!}
							<div class = "col-xs-5">
								{!! Form::email('email', null, ['id' => 'email', 'placeholder' => 'Insertion will update the email','class'=>'form-control']) !!}
							</div>
						</div>
						<div class = "form-group">
							{!!Form::label('vendor','Vendor :',['class'=>'control-label col-xs-2'])!!}
							<div class = "col-xs-5">
								<?php
									$vendors = ['' => 'Select vendor'] + $vendors;
								?>
								{!! Form::select('vendor', $vendors, $user->vendor ?? null, ['class' => 'form-control']) !!}
							</div>
						</div>
						<div class = "form-group">
							{!!Form::label('remote','Remote Access:',['class'=>'control-label col-xs-2'])!!}
							<div class = "col-xs-5">
								{!! Form::checkbox('remote', 1, $user->remote == 1 ? true : null, ['id' => 'remote', 'style' => 'margin-top:10px;']) !!}
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
											{!! Form::checkbox('user_access[]', $link, in_array($link, $user->accesses->pluck('page')->toArray()), ['id' => sprintf('user_access-%d', $i), 'class'=>'checkbox access-control-checkbox']) !!} {{ $text }}
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
		<div class = "col-md-12">
				<div class = "form-group">
					<div class = "col-xs-8 text-right">
						{!! Form::submit('Update User',['class'=>'btn btn-primary']) !!}
					</div>
					{!! Form::close() !!}
					<!-- {!! Form::open(['url' => url(sprintf('/users/%d', $user->id)), 'method' => 'delete', 'id' => 'delete-user-form', 'class'=>'form-horizontal','role'=>'form']) !!}
					<div class = "col-xs-4 text-right">
						{!! Form::submit('Delete user', ['class'=> 'btn btn-danger', 'id' => 'delete-user-btn']) !!}
					</div>
					{!! Form::close() !!} -->
				</div>
		</div>

	</div>

	<script type = "text/javascript" src = "//code.jquery.com/jquery-1.11.3.min.js"></script>
	<script src = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script type = "text/javascript">
		var message = {
			delete: 'Are you sure you want to delete?',
		};
		$("input#delete-user-btn").on('click', function (event)
		{
			event.preventDefault();
			var action = confirm(message.delete);
			if ( action ) {
				var form = $("form#delete-user-form");
				form.submit();
			}
		});

		var state = false;
		$("input#select-deselect-all").on('click', function (event)
		{
			state = !state;
			$("input.access-control-checkbox").prop('checked', state);
		});
	</script>
</body>
</html>