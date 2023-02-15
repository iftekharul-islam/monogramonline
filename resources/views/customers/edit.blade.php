<!doctype html>
<html lang = "en">
<head>
    <meta charset = "UTF-8">
    <title>Edit Customer - {{$customer->ship_full_name}}</title>
    <meta name = "viewport" content = "width=device-width, initial-scale=1">
    <link type = "text/css" rel = "stylesheet"
          href = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
    <link type = "text/css" rel = "stylesheet"
          href = "//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.9.3/css/bootstrap-select.min.css">
    <link type = "text/css" rel = "stylesheet"
          href = "//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">

</head>
<body>
@include('includes.header_menu')
    <div class = "container">
        <ol class="breadcrumb">
            <li><a href="{{url('/')}}">Home</a></li>
            <li><a href="{{url('/customer_service/customers')}}">Customers</a></li>
            <li class="active">Edit customer</li>
        </ol>
        {!! Form::open(['url' => url(sprintf("/customer_service/customers/%d", $customer->id)), 'method' => 'put','class'=>'form-horizontal','role'=>'form']) !!}
        <div class = 'form-group'>
            {!!Form::label('ship_full_name','Ship Full Name :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('ship_full_name', $customer->ship_full_name, ['id' => 'ship_full_name','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
             {!!Form::label('company_name','Company Name :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('company_name', $customer->company_name, ['id' => 'company_name','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
           {!!Form::label('first_name','First Name :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('first_name', $customer->first_name, ['id' => 'first_name','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('last_name','Last Name :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('last_name', $customer->last_name, ['id' => 'last_name','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('shipping_address_1','Shipping Address 1 :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('shipping_address_1', $customer->shipping_address_1, ['id' => 'shipping_address_1','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('shipping_address_2','Shipping Address 2 :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('shipping_address_2', $customer->shipping_address_2, ['id' => 'shipping_address_2','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('ship_city','Ship City :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('ship_city', $customer->ship_city, ['id' => 'ship_city','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('ship_state','Ship State :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('ship_state', $customer->ship_state, ['id' => 'ship_state','class' => 'form-control']) !!}
            </div>
        </div>
        <div class ='form-group'>
             {!!Form::label('ship_country','Ship Country :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('ship_country', $customer->ship_country, ['id' => 'ship_country','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('ship_zip','Ship Zip :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('ship_zip', $customer->ship_zip, ['id' => 'ship_zip','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('ship_phone','Ship Phone :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('ship_phone', $customer->ship_phone, ['id' => 'ship_phone','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('bill_company_name','Bill Company Name :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('bill_company_name', $customer->bill_company_name, ['id' => 'bill_company_name','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('bill_first_name','Bill First Name :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('bill_first_name', $customer->bill_first_name, ['id' => 'bill_first_name','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('bill_last_name','Bill Last Name :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('bill_last_name', $customer->bill_last_name, ['id' => 'bill_last_name','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('bill_address_1','Bill Address 1 :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('bill_address_1', $customer->bill_address_1, ['id' => 'bill_address_1','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('bill_address_2','Bill Address 2 :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('bill_address_2', $customer->bill_address_2, ['id' => 'bill_address_2','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('bill_city','Bill City :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('bill_city', $customer->bill_city, ['id' => 'bill_city','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('bill_state','Bill State :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('bill_state', $customer->bill_state, ['id' => 'bill_state','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('bill_country','Bill Country :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('bill_country', $customer->bill_country, ['id' => 'bill_country','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('bill_zip','Bill Zip :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('bill_zip', $customer->bill_zip, ['id' => 'bill_zip','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            {!!Form::label('bill_phone','Bill Phone :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
            <div class = "col-xs-5">
                {!! Form::text('bill_phone', $customer->bill_phone, ['id' => 'bill_phone','class' => 'form-control']) !!}
            </div>
        </div>
        <div class = 'form-group'>
            <div class = "col-xs-offset-4 col-xs-5">
                {!! Form::submit('Update customer',['class'=>'btn btn-primary btn-block']) !!}
            </div>
        </div>
        {!! Form::close() !!}
    </div>
</body>
</html>