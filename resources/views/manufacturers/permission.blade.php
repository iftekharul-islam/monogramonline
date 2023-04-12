<!doctype html>
<html lang = "en">
<head>
    <meta charset = "UTF-8">
    <title>Edit Store - {{ $manufacture->name }}</title>
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
        <li><a href = "{{url('manufactures')}}">Manufactures</a></li>
        <li class = "active">Edit Permission to manufacture : {{ $manufacture->name }}</li>
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
    <div class = "col-md-12">
        {!! Form::open(['url' => url(sprintf("manufacture/permission/update/%d", $manufacture->id)), 'method' => 'post','class'=>'form-horizontal','role'=>'form']) !!}
        <div class = "form-group">
            {!!Form::label('user_access','User access: ',['class'=>'control-label col-xs-2'])!!}
            <div class = "col-xs-10">
                @foreach($users as $link => $user)
                    <div class = "checkbox">
                        <label>
                            {!! Form::checkbox('permit_manufactures[]', $user->id, in_array($manufacture->id, $user->permit_manufactures), ['id' => sprintf('user_access-%d', $user->id), 'class'=>'checkbox access-control-checkbox']) !!} <b> {{ $user->username }}</b> ({{ $user->email }})
                        </label>
                        @if (isset($desc[$link]))
                            <small>({{ $desc[$link] }})</small>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        <div class = "form-group">
            <div class = "col-md-10 col-md-offset-2">
                <div class = "checkbox">
                    <label>
                        {!! Form::checkbox('select-deselect-all', '1', false, ['id' => 'select-deselect-all']) !!} Select/Deselect All Permissions
                    </label>
                </div>
            </div>
        </div>
        <div class = "col-md-12">
            <div class = "form-group">
                <div class = "col-xs-8 text-right">
                    {!! Form::submit('Update Manufacture Permission',['class'=>'btn btn-primary']) !!}
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>

</div>

<script type = "text/javascript" src = "//code.jquery.com/jquery-1.11.3.min.js"></script>
<script src = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script type = "text/javascript">
    var state = false;
    $("input#select-deselect-all").on('click', function (event)
    {
        state = !state;
        $("input.access-control-checkbox").prop('checked', state);
    });
</script>
</body>
</html>