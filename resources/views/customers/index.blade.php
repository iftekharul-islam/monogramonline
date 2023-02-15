<!doctype html>
<html lang = "en">
<head>
    <meta charset = "UTF-8">
    <title>Customers</title>
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
            <li class="active">Customers</li>
        </ol>
        @if(count($customers) > 0)
            <h3 class="page-header">
                Customers
                <a class="btn btn-success btn-sm pull-right" href="{{url('/customer_service/customers/create')}}">Create customer</a>
            </h3>
            <table class = "table table-bordered">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Location</th>
                    <th>Action</th>
                </tr>
                @foreach($customers as $customer)
                    <tr>
                        <td>{{ $count++ }}</td>
                        <td>{{ substr($customer->ship_full_name, 0, 30) }}</td>
                        <td>{{ sprintf("%s, %s, %s, %s, %s - %s", $customer->shipping_address_1, $customer->shipping_address_2, $customer->ship_city, $customer->ship_state, $customer->ship_country, $customer->ship_zip) }}</td>
                        <td>
                            <a href = "{{ url(sprintf("/customer_service/customers/%d", $customer->id)) }}" data-toggle = "tooltip" data-placement = "top"
                                     title = "View this customer"><i class='fa fa-eye text-primary'></i></a>
                            | <a href = "{{ url(sprintf("/customer_service/customers/%d/edit", $customer->id)) }}" data-toggle = "tooltip" data-placement = "top"
                                     title = "Edit this customer"><i class='fa fa-pencil-square-o text-success'></i></a>
                        </td>
                    </tr>
                @endforeach
            </table>
            <div class="col-xs-12 text-center">
                {!! $customers->render() !!}
            </div>
        @else
            <div class = "col-xs-12">
                <div class = "alert alert-warning text-center">
                    <h3>No customer is registered.</h3>
                </div>
            </div>
        @endif
    </div>
    <script type = "text/javascript" src = "//code.jquery.com/jquery-1.11.3.min.js"></script>
    <script type = "text/javascript" src = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script type='text/javascript'>
        $(function ()
        {
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
</body>
</html>