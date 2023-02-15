<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>User - {{$user->username}}</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet"
	      href = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
	<link type = "text/css" rel = "stylesheet"
	      href = "//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
</head>
<body>
	@include('includes.header_menu')
	<div class = "container ">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "{{url('users')}}">Users</a></li>
			<li class = "active">View user</li>
		</ol>
		<div class = "col-xs-offset-1 col-xs-10 col-xs-offset-1">
			<h4 class = "page-header">User details</h4>
			<table class = "table table-hover table-bordered">
				<tr class = "success">
					<td>Username</td>
					<td>{{$user->username}}</td>
				</tr>
				<tr>
					<td>Email</td>
					<td>{{$user->email}}</td>
				</tr>
				<tr>
					<td>Vendor Id</td>
					<td>{{$user->vendor_id}}</td>
				</tr>
				<tr class = "success">
					<td>Zip Code</td>
					<td>{{$user->zip_code}}</td>
				</tr>
				<tr>
					<td>State</td>
					<td>{{$user->state}}</td>
				</tr>
				<tr>
					<td>Remote Access</td>
					<td>
						@if ($user->remote == 1)
							Remote & Local IP addresses
						@else
							Local IP address only
						@endif						
					</td>
				</tr>
				<tr>
					<td>Access</td>
					{{-- http://stackoverflow.com/a/28033817/2190689 --}}
					<td>{{ implode(", ", array_values(array_intersect_key(\App\Access::$pages, array_flip($user->accesses->pluck('page')->toArray())))) ?: "No access is given yet." }}</td>
				</tr>
			</table>
		</div>
		
			<div class = "col-xs-12" style = "margin-bottom: 30px;">
				<div class = "col-xs-offset-1 col-xs-10" style = "margin-bottom: 10px;">
					<a href = "{{ url(sprintf("/users/%d/edit", $user->id)) }}" class = "btn btn-success btn-block">Edit
					                                                                                                this
					                                                                                                user</a>
				</div>
				<div class = "col-xs-offset-1 col-xs-10">
					{!! Form::open(['url' => url(sprintf('/users/%d', $user->id)), 'method' => 'delete', 'id' => 'delete-user-form']) !!}
					{!! Form::submit('Delete user', ['class'=> 'btn btn-danger btn-block', 'id' => 'delete-user-btn']) !!}
					{!! Form::close() !!}
				</div>
			</div>

	</div>
	<script type = "text/javascript" src = "//code.jquery.com/jquery-1.11.3.min.js"></script>
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
	</script>
</body>
</html>