<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Create Vendor</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet"
	      href = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
</head>
<body>
	@include('includes.header_menu')
	<div class = "container">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "{{url('purchases/vendors')}}">Vendors</a></li>
			<li class = "active">Create vendor</li>
		</ol>

		@include('includes.error_div')


		{!! Form::open(['url' => url('/purchases/vendors'), 'method' => 'post', 'files' => true,'class'=>'form-horizontal','role'=>'form']) !!}
		<div class = "form-group">
			{!!Form::label('image','Vendor image: ',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::file('image', ['id' => 'image', 'accept' => 'image/*', 'class'=>'form-control']) !!}
			</div>
		</div>
		<div class = 'form-group'>
			{!!Form::label('vendor_name','Vendor name :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = 'col-xs-5'>
				{!! Form::text('vendor_name', null, ['id' => 'vendor_name','class' => 'form-control']) !!}
			</div>
		</div>
		<div class = 'form-group'>
			{!!Form::label('zip_code','Zip Code :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::text('zip_code', null, ['id' => 'zip_code','class'=>'form-control']) !!}
			</div>
		</div>
		<div class = 'form-group'>
			{!!Form::label('state','State :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::text('state', null, ['id' => 'state','class'=>'form-control']) !!}
			</div>
		</div>
		<div class = 'form-group'>
			{!!Form::label('country','Country: ',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::text('country', null, ['id' => 'country','class'=>'form-control']) !!}
			</div>
		</div>

		<div class = 'form-group'>
			{!!Form::label('email','Email :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::email('email', null, ['id' => 'email','class' => 'form-control']) !!}
			</div>
		</div>
		<div class = 'form-group'>
			{!!Form::label('phone_number','Phone number :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::text('phone_number', null, ['id' => 'phone_number','class'=>'form-control']) !!}
			</div>
		</div>

		<div class = 'form-group'>
			{!!Form::label('contact_person_name','Contact person name :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::text('contact_person_name', null, ['id' => 'contact_person_name','class'=>'form-control']) !!}
			</div>
		</div>
		<div class = 'form-group'>
			{!!Form::label('account_link','Account link :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::text('account_link', null, ['id' => 'account_link','class'=>'form-control']) !!}
			</div>
		</div>
		<div class = 'form-group'>
			{!!Form::label('account_login','Account login: ',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::text('account_login', null, ['id' => 'account_login','class'=>'form-control']) !!}
			</div>
		</div>
		<div class = 'form-group'>
			{!!Form::label('account_password','Account password :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::text('account_password', null, ['id' => 'account_password','class'=>'form-control']) !!}
			</div>
		</div>

		<div class = 'form-group'>
			{!!Form::label('bank_info','Bank info :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::text('bank_info', null, ['id' => 'bank_info','class'=>'form-control']) !!}
			</div>
		</div>
		<div class = 'form-group'>
			{!!Form::label('paypal_info','Paypal info :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::text('paypal_info', null, ['id' => 'paypal_info','class'=>'form-control']) !!}
			</div>
		</div>
		<div class = 'form-group'>
			{!!Form::label('notes','Notes :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::textarea('notes', null, ['id' => 'notes','class'=>'form-control']) !!}
			</div>
		</div>
		<div class = 'form-group'>
			<div class = "col-xs-offset-4 col-xs-5">
				{!! Form::submit('Create vendor',['class'=>'btn btn-primary btn-block']) !!}
			</div>
		</div>

		{!! Form::close() !!}
	</div>
	<script src = "//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
	<script src = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</body>
</html>