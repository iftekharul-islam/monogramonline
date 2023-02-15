<!doctype html>
<html lang = "en">
<head>
    <meta charset = "UTF-8">
    <title>Create Batch</title>
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
            <li><a href="{{url('/prod_config/batch_routes')}}">Batch routes</a></li>
            <li class="active">Create batch routes</li>
        </ol>
        @if($errors->any())
            <div class = "col-xs-12">
                <div class = "alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{$error}}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {!! Form::open(['url' => url('/prod_config/batch_routes'), 'method' => 'post',]) !!}
        <div class = "form-group col-xs-12">
            {!! Form::label('batch_code', 'Batch code', ['class' => 'col-xs-2 control-label']) !!}
            <div class = "col-sm-4">
                {!! Form::text('batch_code', null, ['id' => 'batch_code', 'class' => "form-control", 'placeholder' => "Enter batch code"]) !!}
            </div>
        </div>
        <div class = "form-group col-xs-12">
            {!! Form::label('batch_route_name', 'Route name', ['class' => 'col-xs-2 control-label']) !!}
            <div class = "col-sm-4">
                {!! Form::text('batch_route_name', null, ['id' => 'batch_route_name', 'class' => "form-control", 'placeholder' => "Enter batch route name"]) !!}
            </div>
        </div>
        <div class = "form-group col-xs-12">
            {!! Form::label('batch_max_units', 'Max units', ['class' => 'col-xs-2 control-label']) !!}
            <div class = "col-sm-4">
                {!! Form::text('batch_max_units', null, ['id' => 'batch_max_units', 'class' => "form-control", 'placeholder' => "Enter batch max units"]) !!}
            </div>
        </div>
        <div class = "form-group col-xs-12">
            {!! Form::label('batch_stations', 'Stations', ['class' => 'col-xs-2 control-label']) !!}
            <div class = "col-sm-4">
                {!! Form::select('batch_stations[]', $stations, null, ['id' => 'batch_stations', 'multiple' => true, 'class' => 'selectpicker form-control', "data-live-search" => "true"]) !!}
            </div>
        </div>
        <div class = "form-group col-xs-12">
            {!! Form::label('batch_options', 'Options', ['class' => 'col-xs-2 control-label']) !!}
            <div class = "col-sm-4">
                {!! Form::textarea('batch_options', null, ['id' => 'batch_options', 'rows' => 4, 'class' => "form-control", 'placeholder' => "Enter batch options"]) !!}
            </div>
        </div>
        <div class = "form-group col-xs-12">
            <div class = "col-sm-offset-2 col-sm-4">
                {!! Form::submit('Create batch route', ['class' => 'btn btn-primary btn-block']) !!}
            </div>
        </div>
        {!! Form::close() !!}
    </div>
    <script type = "text/javascript" src = "//code.jquery.com/jquery-1.11.3.min.js"></script>
    <script type = "text/javascript" src = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script type = "text/javascript"
            src = "//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.9.3/js/bootstrap-select.min.js"></script>
    <script type = "text/javascript">
        $('.selectpicker').selectpicker();
    </script>
</body>
</html>