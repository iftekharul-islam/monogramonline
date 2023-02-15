<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Vendor - {{$vendor->vendor_name}}</title>
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
			<li><a href = "{{url('purchases/vendors')}}">Vendors</a></li>
			<li class = "active">View vendor</li>
		</ol>
		<div class = "col-xs-offset-1 col-xs-10 col-xs-offset-1">
			<h4 class = "page-header">Vendor details</h4>
			<table class = "table table-hover table-bordered">
				<tr>
					<td>Vendor name</td>
					<td>{{$vendor->vendor_name}}</td>
				</tr>
				<tr>
					<td>Email</td>
					<td>{{$vendor->email}}</td>
				</tr>
				<tr>
					<td>Zip Code</td>
					<td>{{$vendor->zip_code}}</td>
				</tr>
				<tr>
					<td>State</td>
					<td>{{$vendor->state}}</td>
				</tr>
				<tr>
					<td>Country</td>
					<td>{{$vendor->country}}</td>
				</tr>
				<tr>
					<td>Phone number</td>
					<td>{{$vendor->phone_number}}</td>
				</tr>
				<tr>
					<td>Image</td>
					<td>
						@if($vendor->image)
							<a href = "{{$vendor->image}}" target = "_blank"><img src = "{{$vendor->image}}"
							                                                      width = "70"
							                                                      height = "70"
							                                                      style = "margin-right: 10px; margin-bottom: 2px;"></a>
						@else
							No image available
						@endif
					</td>
				</tr>
				<tr>
					<td>Contact person name</td>
					<td>{{$vendor->contact_person_name}}</td>
				</tr>
				<tr>
					<td>Account link</td>
					<td>
						@if($vendor->link)
							<a href = "{{$vendor->link}}">{{$vendor->link}}</a>
						@else
							N/A
						@endif
					</td>
				</tr>
				<tr>
					<td>Account login id</td>
					<td>{{$vendor->login_id}}</td>
				</tr>
				<tr>
					<td>Bank info</td>
					<td>{{$vendor->bank_info}}</td>
				</tr>
				<tr>
					<td>Paypal info</td>
					<td>{{$vendor->paypal_info}}</td>
				</tr>
			</table>
		</div>
		<div class = "col-xs-12" style = "margin-bottom: 30px;">
			<div class = "col-xs-offset-1 col-xs-10" style = "margin-bottom: 10px;">
				<a href = "{{ url(sprintf("/purchases/vendors/%d/edit", $vendor->id)) }}" class = "btn btn-success btn-block">Edit
				                                                                                                    this
				                                                                                                    vendor</a>
			</div>
			<div class = "col-xs-offset-1 col-xs-10">
				{!! Form::open(['url' => url(sprintf('/purchases/vendors/%d', $vendor->id)), 'method' => 'delete', 'id' => 'delete-vendor-form']) !!}
				{!! Form::submit('Delete vendor', ['class'=> 'btn btn-danger btn-block', 'id' => 'delete-vendor-btn']) !!}
				{!! Form::close() !!}
			</div>
		</div>
	</div>
	<script type = "text/javascript" src = "//code.jquery.com/jquery-1.11.3.min.js"></script>
	<script type = "text/javascript">
		var message = {
			delete: 'Are you sure you want to delete?',
		};
		$("input#delete-vendor-btn").on('click', function (event)
		{
			event.preventDefault();
			var action = confirm(message.delete);
			if ( action ) {
				var form = $("form#delete-vendor-form");
				form.submit();
			}
		});
	</script>
</body>
</html>