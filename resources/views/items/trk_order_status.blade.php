<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Order status</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">

	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/moment.min.js"></script>
	
	<style>
		table.export-view-table td {
			width: 1px;
			white-space: nowrap;
		}

		td.description {
			white-space: pre-wrap;
			word-wrap: break-word;
			max-width: 1px;
			width: 100%;
		}

	</style>
</head>
<body>
	<div class = "container">
		<ol class = "breadcrumb">
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		<div class = "col-xs-12">
			{!! Form::open(['url' => url('/trk_order_status'), 'method' => 'GET', 'name' => 'trk_order_status', 'onsubmit' => 'return validateS()']) !!}
			<div class = "col-xs-12">
			<table class = "table table-bordered">
				<tr>
					<th>Order</th>
					<th>Email</th>
					<th></th>
				</tr>
				<tr>
					<td>{!! Form::text('order', $request->get('order'), ['id'=>'order', 'class' => 'form-control', 'placeholder' => 'Order Number']) !!}</td>
					<td>{!! Form::text('email', $request->get('email'), ['id'=>'email', 'class' => 'form-control', 'placeholder' => 'email']) !!}</td>
					<td>{!! Form::submit('Search Order',['id' => 'searchOrder', 'class' => 'btn btn-primary btn-block']) !!}</td>
				</tr>
			</table>

			{!! Form::close() !!}

			@if(isset($orderinfo) && count($orderinfo) > 0)
				<table class = "table table-bordered">
					<tr>
						<td>Order ID</td>					<td>{{$orderinfo['short_order']}}</td>
					</tr>
					<tr>
						<td>Name:</td>						<td>{{$orderinfo['ship_full_name']}}</td>
					</tr>
					<tr>
						<td>City/State</td>					<td>{{$orderinfo['ship_city_state']}}</td>
					</tr>
					<tr>
						<td>Items/Subtotal</td>				<td>{{$orderinfo['items_subtotal']}}</td>
					</tr>
					<tr>
						<td>Order date</td>					<td>{{$orderinfo['order_date']}}</td>
					</tr>
					<tr>
						<td>Status</td>						<td>{{$orderinfo['status']}}</td>
					</tr>
					
					@foreach ($orderinfo['items'] as $item => $status)
						
						<tr>
							<td rowspan=2>Item {{ $item }}</td>
							<td>{{ $status[0] }}</td>
						</tr>
						<tr>
							<td>{{ $status[1] }}</td>
						</tr>
						
					@endforeach
				</table>
				
				@else
				
				@if(count($errors->all()) == 0)	
					<div class = "col-xs-12">
						<div class = "alert alert-warning text-center">
							<h3>
								<div id="display_message" >Please write your Order Number and Email</div>
							</h3>
						</div>
					</div>
				@endif	

			@endif
		</div>
	</div>

	<script type = "text/javascript">

	function validateS()  {
		
		   var message = ('This Field Cannot be Empty:\n\n');
		   flag = 0;
		   emailStr = document.forms['trk_order_status'].elements["email"].value
			if ((emailStr.indexOf("@") == -1) || (emailStr.indexOf(".") == -1))
				{
				message = (message + 'Valid email address\n');
					flag = 1;
			}
		   term = trim(document.forms['trk_order_status'].elements["order"].value);
		   if (term == '')  {
			 message = (message + 'order number\n');
			 flag = 1;
			} else {
			 if (term.length < 3 )  {
			 message = (message + 'Order number must be at least 3 characters\n');
			 flag = 1;
			 }
		   }
		   if (flag == 1) {
			 alert(message);
			 return false;
		   } else return true;
		}

		function trim(stringToTrim) {
			return stringToTrim.replace(/^\s+|\s+$/g,"");
		}
	</script>

</body>
</html>