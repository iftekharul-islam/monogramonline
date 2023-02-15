<!doctype html>
<html lang = "en">
<head>
    <meta charset = "UTF-8">
    <title>Create Customer</title>
    <meta name = "viewport" content = "width=device-width, initial-scale=1">
    <link type = "text/css" rel = "stylesheet"
          href = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
    <link type = "text/css" rel = "stylesheet"
          href = "//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">


</head>
<body>
    @include('includes.header_menu')
    <div class = "container">
        <ol class="breadcrumb">
            <li><a href="{{url('/')}}">Home</a></li>
            <li><a href="{{url('/customer_service/customers')}}">Customers</a></li>
            <li class="active">Create customer</li>
        </ol>
        {!! Form::open(['url' => url('/customer_service/customers'), 'method' => 'post','class'=>'form-horizontal','role'=>'form']) !!}
        <div class = 'form-group'>
            {!!Form::label('ship_full_name','Ship Full Name :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('ship_full_name', null, ['id' => 'ship_full_name','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('company_name','Company Name :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('company_name', null, ['id' => 'company_name','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('first_name','First Name :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('first_name', null, ['id' => 'first_name','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('last_name','Last Name :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('last_name', null, ['id' => 'last_name','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('shipping_address_1','Shipping Address 1 :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('shipping_address_1', null, ['id' => 'shipping_address_1','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('shipping_address_2','Shipping Address 2 :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('shipping_address_2', null, ['id' => 'shipping_address_2','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('ship_city','Ship City :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('ship_city', null, ['id' => 'ship_city','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('ship_state','Ship State :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('ship_state', null, ['id' => 'ship_state','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('ship_country','Ship Country :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('ship_country', null, ['id' => 'ship_country','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('ship_zip','Ship Zip :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('ship_zip', null, ['id' => 'ship_zip','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('ship_phone','Ship Phone :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('ship_phone', null, ['id' => 'ship_phone','class' => 'form-control']) !!}
            </div>
        </div>
        <hr />
        <div class = "checkbox" style = "margin-bottom: 3px;">
            <div class = "col-xs-offset-4 col-xs-5">
                <label>
                    {!! Form::checkbox('same_as_shipping', true, false, ['id' => 'same_as_billing']) !!} Billing info
                                                                                                         same as
                                                                                                         shipping
                </label>
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('bill_company_name','Bill Company Name :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('bill_company_name', null, ['id' => 'bill_company_name','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('bill_first_name','Bill First Name :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('bill_first_name', null, ['id' => 'bill_first_name','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('bill_last_name','Bill Last Name :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('bill_last_name', null, ['id' => 'bill_last_name','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('bill_address_1','Bill Address 1 :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('bill_address_1', null, ['id' => 'bill_address_1','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('bill_address_2','Bill Address 2 :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('bill_address_2', null, ['id' => 'bill_address_2','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('bill_city','Bill City :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('bill_city', null, ['id' => 'bill_city','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('bill_state','Bill State :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('bill_state', null, ['id' => 'bill_state','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('bill_country','Bill Country :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('bill_country', null, ['id' => 'bill_country','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('bill_zip','Bill Zip :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('bill_zip', null, ['id' => 'bill_zip','class' => 'form-control','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('bill_phone','Bill Phone :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('bill_phone', null, ['id' => 'bill_phone','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            <div class = "col-xs-offset-4 col-xs-5">
                {!! Form::submit('Create customer',['class'=>'btn btn-primary btn-block']) !!}
            </div>
        </div>
        {!! Form::close() !!}
    </div>
    <script src = "//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</body>
</html>