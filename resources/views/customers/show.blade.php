<!doctype html>
<html lang = "en">
<head>
    <meta charset = "UTF-8">
    <title>Customer - {{$customer->ship_full_name}}</title>
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
            <li class="active">View customer</li>
        </ol>
        <div class = "row">
            <div class = "col-xs-6">
                <table class = "table table-hover table-bordered">
                    <caption>SHIPPING ADDRESS</caption>
                    <tr class = "success">
                        <td>Ship Full Name</td>
                        <td>{{$customer->ship_full_name}}</td>
                    </tr>
                    <tr>
                        <td>Company Name</td>
                        <td>{{$customer->company_name}}</td>
                    </tr>
                    <tr class = "success">
                        <td>First Name</td>
                        <td>{{$customer->first_name}}</td>
                    </tr>
                    <tr>
                        <td>Last Name</td>
                        <td>{{$customer->last_name}}</td>
                    </tr>
                    <tr class = "success">
                        <td>Shipping Address 1</td>
                        <td>{{$customer->shipping_address_1}}</td>
                    </tr>
                    <tr>
                        <td>Shipping Address 2</td>
                        <td>{{$customer->shipping_address_2}}</td>
                    </tr>
                    <tr class = "success">
                        <td>Ship City</td>
                        <td>{{$customer->ship_city}}</td>
                    </tr>
                    <tr>
                        <td>Ship State</td>
                        <td>{{$customer->ship_state}}</td>
                    </tr>
                    <tr class = "success">
                        <td>Ship Country</td>
                        <td>{{$customer->ship_country}}</td>
                    </tr>
                    <tr>
                        <td>Ship Zip</td>
                        <td>{{$customer->ship_zip}}</td>
                    </tr>
                    <tr class = "success">
                        <td>Ship Phone</td>
                        <td>{{$customer->ship_phone}}</td>
                    </tr>
                </table>
            </div>
            <div class = "col-xs-6">
                <table class = "table table-hover table-bordered">
                    <caption>BILLING ADDRESS</caption>
                    <tr class = "success">
                        <td>Bill Company Name</td>
                        <td>{{$customer->bill_company_name}}</td>
                    </tr>
                    <tr>
                        <td>Bill First Name</td>
                        <td>{{$customer->bill_first_name}}</td>
                    </tr>
                    <tr class = "success">
                        <td>Bill Last Name</td>
                        <td>{{$customer->bill_last_name}}</td>
                    </tr>
                    <tr>
                        <td>Bill Address 1</td>
                        <td>{{$customer->bill_address_1}}</td>
                    </tr>
                    <tr class = "success">
                        <td>Bill Address 2</td>
                        <td>{{$customer->bill_address_2}}</td>
                    </tr>
                    <tr>
                        <td>Bill City</td>
                        <td>{{$customer->bill_city}}</td>
                    </tr>
                    <tr class = "success">
                        <td>Bill State</td>
                        <td>{{$customer->bill_state}}</td>
                    </tr>
                    <tr>
                        <td>Bill Country</td>
                        <td>{{$customer->bill_country}}</td>
                    </tr>
                    <tr class = "success">
                        <td>Bill Zip</td>
                        <td>{{$customer->bill_zip}}</td>
                    </tr>
                    <tr>
                        <td>Bill Phone</td>
                        <td>{{$customer->bill_phone}}</td>
                    </tr>
                </table>
            </div>
        </div>
        <div class = "row">
            <div class = "col-xs-12">
                <a class = "btn btn-success btn-block" href = "{{ url(sprintf("/customer_service/customers/%d/edit", $customer->id)) }}">Edit
                                                                                                                        this
                                                                                                                        customer</a>
            </div>
        </div>
    </div>
</body>
</html>