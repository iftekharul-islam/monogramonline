<!doctype html>
<html lang = "en">
<head>
    <meta charset = "UTF-8">
    <title>Create category</title>
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
            <li><a href="{{url('/products_config/categories')}}">Categories</a></li>
            <li class="active">Create category</li>
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

        {!! Form::open(['url' => url('/products_config/categories'), 'method' => 'post']) !!}
        <fieldset>
            <legend>Create new category</legend>
            <div class = "form-group col-xs-12">
                {!! Form::label('category_code', 'Category code', ['class' => 'col-xs-2 control-label']) !!}
                <div class = "col-xs-4">
                    {!! Form::text('category_code', null, ['id' => 'category_code', 'class' => "form-control", 'placeholder' => "Enter category code"]) !!}
                </div>
            </div>
            <div class = "form-group col-xs-12">
                {!! Form::label('category_description', 'Description', ['class' => 'col-xs-2 control-label']) !!}
                <div class = "col-xs-4">
                    {!! Form::text('category_description', null, ['id' => 'category_description', 'class' => "form-control", 'placeholder' => "Enter category description"]) !!}
                </div>
            </div>
            <div class = "form-group col-xs-12">
                {!! Form::label('category_display_order', 'Display order', ['class' => 'col-xs-2 control-label']) !!}
                <div class = "col-xs-4">
                    {!! Form::text('category_display_order', null, ['id' => 'category_display_order', 'class' => "form-control", 'placeholder' => "Enter category display order"]) !!}
                </div>
            </div>
            <div class = "col-xs-12">
                <div class = "col-xs-offset-2 col-xs-4">
                    {!! Form::submit('Create category', ['class' => 'btn btn-primary btn-block']) !!}
                </div>
            </div>
        </fieldset>
        {!! Form::close() !!}
    </div>
</body>
</html>