<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Edit Vendor - {{$vendor->vendor_name}}</title>
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
			<li><a href = "{{url('purchases/vendors')}}">Vendors</a></li>
			<li class = "active">Edit vendor</li>
		</ol>

		@include('includes.error_div')
		@include('includes.success_div')

		{!! Form::open(['url' => url(sprintf("/purchases/vendors/%d", $vendor->id)), 'method' => 'put', 'files' => true,'class'=>'form-horizontal','role'=>'form']) !!}
		<div class = "form-group">
			{!!Form::label('image','Vendor image: ',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::file('image', ['id' => 'image', 'accept' => 'image/*', 'class'=>'form-control']) !!}
			</div>
		</div>
		<div class = "form-group">
			{!!Form::label('vendor_name','vendor_name :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::text('vendor_name', $vendor->vendor_name, ['id' => 'vendor_name','class'=>'form-control']) !!}
			</div>
		</div>
		<div class = "form-group">
			{!!Form::label('zip_code','Zip Code :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::text('zip_code', $vendor->zip_code, ['id' => 'zip_code','class'=>'form-control']) !!}
			</div>
		</div>
		<div class = "form-group">
			{!!Form::label('state','State :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::text('state', $vendor->state, ['id' => 'state','class'=>'form-control']) !!}
			</div>
		</div>
		<div class = "form-group">
			{!!Form::label('country','Country: ',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::text('country', $vendor->country, ['id' => 'state','class'=>'form-control']) !!}
			</div>
		</div>

		<div class = "form-group">
			{!!Form::label('email','Email :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::email('email', null, ['id' => 'email', 'placeholder' => 'Insertion will update the email','class'=>'form-control']) !!}
				<p class = "help-block" style = "color: red">If a value is given, will update</p>
			</div>
		</div>
		<div class = "form-group">
			{!!Form::label('phone_number','Phone number :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::text('phone_number', $vendor->phone_number, ['id' => 'state','class'=>'form-control']) !!}
			</div>
		</div>

		<div class = 'form-group'>
			{!!Form::label('contact_person_name','Contact person name :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::text('contact_person_name', $vendor->contact_person_name, ['id' => 'contact_person_name','class'=>'form-control']) !!}
			</div>
		</div>
		<div class = 'form-group'>
			{!!Form::label('account_link','Account link :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::text('account_link', $vendor->link, ['id' => 'account_link','class'=>'form-control']) !!}
			</div>
		</div>
		<div class = 'form-group'>
			{!!Form::label('account_login','Account login: ',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::text('account_login', $vendor->login_id, ['id' => 'account_login','class'=>'form-control']) !!}
			</div>
		</div>
		<div class = 'form-group'>
			{!!Form::label('account_password','Account password :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::text('account_password', $vendor->password, ['id' => 'account_password','class'=>'form-control']) !!}
				<p class = "help-block" style = "color: red">If a value is given, will update</p>
			</div>
		</div>

		<div class = 'form-group'>
			{!!Form::label('bank_info','Bank info :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::text('bank_info', $vendor->bank_info, ['id' => 'bank_info','class'=>'form-control']) !!}
			</div>
		</div>
		<div class = 'form-group'>
			{!!Form::label('paypal_info','Paypal info :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::text('paypal_info', $vendor->paypal_info, ['id' => 'paypal_info','class'=>'form-control']) !!}
			</div>
		</div>
		<div class = 'form-group'>
			{!!Form::label('notes','Notes :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::textarea('notes', $vendor->notes, ['id' => 'notes','class'=>'form-control']) !!}
			</div>
		</div>

		<div class = "form-group">
			<div class = "col-xs-offset-4 col-xs-5">
				{!! Form::submit('Update this vendor',['class'=>'btn btn-primary btn-block']) !!}
			</div>
		</div>
		{!! Form::close() !!}

		{!! Form::open(['url' => url(sprintf('/purchases/vendors/%d', $vendor->id)), 'method' => 'delete', 'id' => 'delete-vendor-form', 'class'=>'form-horizontal','role'=>'form']) !!}
		<div class = "form-group">
			<div class = "col-xs-offset-4 col-xs-5">
				{!! Form::submit('Delete vendor', ['class'=> 'btn btn-primary btn-block btn-danger', 'id' => 'delete-vendor-btn']) !!}
			</div>
		</div>
		{!! Form::close() !!}

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