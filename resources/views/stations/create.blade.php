<!doctype html>
<html lang = "en">
<head>
    <meta charset = "UTF-8">
    <title>Create station</title>
    <meta name = "viewport" content = "width=device-width, initial-scale=1">
    <link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">

  	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
  	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
</head>
<body>
    @include('includes.header_menu')
    <div class = "container">
        <ol class="breadcrumb">
            <li><a href="{{url('/')}}">Home</a></li>
            <li><a href="{{url('stations')}}">Stations</a></li>
            <li class="active">Create station</li>
        </ol>

        @include('includes.error_div')
    		@include('includes.success_div')

        {!! Form::open(['url' => url('/prod_config/stations'), 'method' => 'post']) !!}
        <div class = "form-group col-xs-12">
          {!! Form::label('station_name', 'Station Name', ['class' => 'col-xs-2 control-label']) !!}
          <div class = "col-sm-4">
            {!! Form::text('station_name', null, ['id' => 'station_name', 'class' => "form-control", 'placeholder' => "Enter station name"]) !!}
          </div>
        </div>
        <div class = "form-group col-xs-12">
          {!! Form::label('station_description', 'Description', ['class' => 'col-xs-2 control-label']) !!}
          <div class = "col-sm-4">
            {!! Form::text('station_description', null, ['id' => 'station_description', 'class' => "form-control", 'placeholder' => "Enter station description"]) !!}
          </div>
        </div>
        <div class = "form-group col-xs-12">
          {!! Form::label('Section', 'Section', ['class' => 'col-xs-2 control-label']) !!}
          <div class = "col-sm-4">
            {!! Form::select('section', $sections, '') !!}
          </div>
        </div>
        <div class = "form-group col-xs-12">
          {!! Form::label('type', 'Type', ['class' => 'col-xs-2 control-label']) !!}
          <div class = "col-sm-4">
            {!! Form::select('type', $types, '') !!}
          </div>
        </div>
        <div class = "col-xs-12 apply-margin-top-bottom">
          <div class = "col-xs-offset-2 col-xs-4">
            {!! Form::submit('Create station',['class' => 'btn btn-primary btn-block']) !!}
          </div>
        </div>
        {!! Form::close() !!}
    </div>
</body>
</html>