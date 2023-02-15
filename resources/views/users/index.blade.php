<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Users</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script src = "/assets/js/DYMO.Label.Framework.latest.js" type="text/javascript" charset="UTF-8"> </script>
	<script src = "/assets/js/dymoBarcode.js" type="text/javascript"> </script>
	
	<style>

	table {
	  border:1px solid black;
	  overflow: hidden;
	}
	
	td, th {
	  position: relative;
	}
	
	tr:hover {
	  background-color: #ffa;
	}
	
	td:hover::after,
	th:hover::after {
	  content: "";
	  position: absolute;
	  background-color: #ffa;
	  left: 0;
	  top: -5000px;
	  height: 10000px;
	  width: 100%;
	  z-index: -1;
	}
	
	table th {
	  border-bottom:1px solid black;
	}

	th.rotate {
	  height:150px;
	  white-space: nowrap;
	  position:relative;
	}

	th.rotate > div {
	  transform: rotate(270deg);
	  position:absolute;
	  left:0;
	  right:0;
	  top: 120px;
	  margin:auto;
	}

	</style>
</head>
<body>
	@include('includes.header_menu')
	<div class = "container" style="min-width: 1550px;"">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li class = "active">Users</li>
		</ol>

		@include('includes.error_div')
		@include('includes.success_div')

		@if(count($users) > 0)
			<h3 class = "page-header">
				Users
				
					<a class = "btn btn-success btn-sm pull-right" href = "{{url('/users/create')}}">Create user</a>

			</h3>
			<table class = "table table-bordered table-hover">
				<tr>
					<th width="100">Action</th>
					<th>Name</th>
					<th width="25">Remote</th>
					@foreach(\App\Access::$pages as $text)
						<th class="rotate"><div><span>{{ $text }}</span></div></th>
					@endforeach
				</tr>
				@foreach($users as $user)
					<tr data-id = "{{$user->id}}">
						<td>
							@setvar($user_code = 'USER' . intval($user->id * 8) . '9')
							
								<a href = "{{ url(sprintf("/users/%d/edit", $user->id)) }}" data-toggle = "tooltip"
								     data-placement = "top"
								     title = "Edit this user"><i class = 'glyphicon glyphicon-pencil text-success'></i></a>
								| <a href = "#" onClick = "print_user_label('{{ ucFirst($user->username) }}', '{{ $user_code }}')"
											data-toggle = "tooltip" data-placement = "top"
		 									title = "Print Barcode"><i class = 'glyphicon glyphicon-print text-success'></i></a>
								| <a href = "#" class = "delete" data-toggle = "tooltip" data-placement = "top"
								     title = "Delete this user"><i class = 'glyphicon glyphicon-remove text-danger'></i></a>

						</td>
						<td>{{ substr($user->username, 0, 30) }}</td>
						<td>
							@if ($user->remote) 
								<i class = 'glyphicon glyphicon-ok'></i>
							@endif
						</td>
						@setvar($i = 1)
						@foreach(\App\Access::$pages as $link => $text)
							<td width="20">
									@if (in_array($link, $user->accesses->pluck('page')->toArray())) 
										<i class = 'glyphicon glyphicon-ok' data-toggle="tooltip" data-placement="top" title="{{ $text }}">
									@endif
							</td>
						@endforeach
					</tr>
				@endforeach
			</table>
			{!! Form::open(['url' => url('/users/id'), 'method' => 'delete', 'id' => 'delete-user']) !!}
			{!! Form::close() !!}
			<div class = "col-xs-12 text-center">
				{!! $users->render() !!}
			</div>
		@else
			<div class = "col-xs-12">
				<div class = "alert alert-warning text-center">
					<h3>No user found.</h3>
				</div>
			</div>
		@endif
	</div>

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
				var form = $("form#delete-user");
				var url = form.attr('action');
				form.attr('action', url.replace('id', id));
				form.submit();
			}
		});
	</script>
</body>
</html>