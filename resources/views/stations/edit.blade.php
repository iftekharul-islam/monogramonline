<!doctype html>
<html lang = "en">
<head>
    <meta charset = "UTF-8">
    <title>Edit station - {{ $station->station_name}}</title>
    <meta name = "viewport" content = "width=device-width, initial-scale=1">
    <link type = "text/css" rel = "stylesheet"
          href = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
    <style>
        div.apply-margin-top-bottom {
            margin: 5px;
        }
    </style>
</head>
<body>
    <div class = "container apply-margin-top-bottom">
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

        {!! Form::open(['url' => url(sprintf("/stations/%d", $station->id)), 'method' => 'put']) !!}
        <div class = "col-xs-12 apply-margin-top-bottom">
            <div class = "col-xs-3">station_name</div>
            <div class = "col-xs-6">
                {!! Form::text('station_name', $station->station_name, ['id' => 'station_name']) !!}
            </div>
        </div>
        <div class = "col-xs-12 apply-margin-top-bottom">
            <div class = "col-xs-3">station_description</div>
            <div class = "col-xs-6">
                {!! Form::text('station_description', $station->station_description, ['id' => 'station_description']) !!}
            </div>
        </div>

        <div class = "col-xs-12 apply-margin-top-bottom">
            <div class = "col-xs-6">
                {!! Form::submit('Update station') !!}
            </div>
        </div>
        {!! Form::close() !!}

        {!! Form::open(['url' => url(sprintf('/stations/%d', $station->id)), 'method' => 'delete', 'id' => 'delete-station-form']) !!}
        {!! Form::submit('Delete station', ['class'=> 'btn btn-danger', 'id' => 'delete-station-btn']) !!}
        {!! Form::close() !!}
    </div>
    <script type = "text/javascript" src = "//code.jquery.com/jquery-1.11.3.min.js"></script>
    <script type = "text/javascript">
        var message = {
            delete: 'Are you sure you want to delete?',
        };
        $("input#delete-station-btn").on('click', function (event)
        {
            event.preventDefault();
            var action = confirm(message.delete);
            if ( action ) {
                var form = $("form#delete-station-form");
                form.submit();
            }
        });
    </script>
</body>
</html>