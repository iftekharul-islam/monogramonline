<!doctype html>
<html lang = "en">
<head>
    <meta charset = "UTF-8">
    <title>Edit category - {{ $category->category_code}}</title>
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

        {!! Form::open(['url' => url(sprintf("/products_config/categories/%d", $category->id)), 'method' => 'put']) !!}
            <div class = "col-xs-12 apply-margin-top-bottom">
                <div class = "col-xs-3">category_code</div>
                <div class = "col-xs-6">
                    {!! Form::text('category_code', $category->category_code, ['id' => 'category_code']) !!}
                </div>
            </div>
            <div class = "col-xs-12 apply-margin-top-bottom">
                <div class = "col-xs-3">category_description</div>
                <div class = "col-xs-6">
                    {!! Form::text('category_description', $category->category_description, ['id' => 'category_description']) !!}
                </div>
            </div>
            <div class = "col-xs-12 apply-margin-top-bottom">
                <div class = "col-xs-3">category_display_order</div>
                <div class = "col-xs-6">
                    {!! Form::text('category_display_order', $category->category_display_order, ['id' => 'category_display_order']) !!}
                </div>
            </div>

        <div class = "col-xs-12 apply-margin-top-bottom">
            <div class = "col-xs-6">
                {!! Form::submit('Update category') !!}
            </div>
        </div>
        {!! Form::close() !!}

        {!! Form::open(['url' => url(sprintf('/products_config/categories/%d', $category->id)), 'method' => 'delete', 'id' => 'delete-category-form']) !!}
        {!! Form::submit('Delete category', ['class'=> 'btn btn-danger', 'id' => 'delete-category-btn']) !!}
        {!! Form::close() !!}
    </div>
    <script type = "text/javascript" src = "//code.jquery.com/jquery-1.11.3.min.js"></script>
    <script type = "text/javascript">
        var message = {
            delete: 'Are you sure you want to delete?',
        };
        $("input#delete-category-btn").on('click', function (event)
        {
            event.preventDefault();
            var action = confirm(message.delete);
            if ( action ) {
                var form = $("form#delete-category-form");
                form.submit();
            }
        });
    </script>
</body>
</html>