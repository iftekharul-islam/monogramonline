<!doctype html>
<html lang = "en">
<head>
    <meta charset = "UTF-8">
    <title>Station - {{$station->station_name}}</title>
    <meta name = "viewport" content = "width=device-width, initial-scale=1">
    <link type = "text/css" rel = "stylesheet"
          href = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
</head>
<body>
    <div class = "container">
        <a href = "{{ url(sprintf("/stations/%d/edit", $station->id)) }}" class="btn btn-success">Edit this station</a>
        <table class = "table table-bordered">
            <caption>Station details</caption>
            <tr>
                <td>station_name</td>
                <td>{{$station->station_name}}</td>
            </tr>
            <tr>
                <td>station_description</td>
                <td>{{$station->station_description}}</td>
            </tr>
        </table>
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