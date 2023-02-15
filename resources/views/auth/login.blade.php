<!doctype html>
<html lang = "en">
<head>
    <meta charset = "UTF-8">
    <meta name = "viewport" content = "width=device-width, initial-scale=1">
    <title>Login</title>
    <link type = "text/css" rel = "stylesheet"
          href = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
    {!! Html::style('assets/css/signin.css') !!}
</head>
<body>
    <div class = "container">

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

        {!! Form::open(['url' => url('/login'), 'method' => 'post','class'=>'form-signin']) !!}
        <h2 class = "form-signin-heading">Please Log in</h2>

        {!!Form::label('email','Email :',['class'=>'sr-only'])!!}

        {!! Form::email('email', null, ['id' => 'email', 'class' => 'form-control', 'placeholder' => 'Enter your email']) !!}


        {!!Form::label('password','Password :',['class'=>'sr-only'])!!}

        {!! Form::password('password', ['id' => 'password', 'class'=>'form-control', 'autocomplete' => "new-password", 'placeholder'=>'Enter your password']) !!}

        {!! Form::submit('Login',['class'=>'btn btn-lg btn-primary btn-block']) !!}

        {!! Form::close() !!}
    </div>
</body>
</html>