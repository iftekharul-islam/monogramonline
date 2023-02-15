<!doctype html>
<html lang = "en">
<head>
    <meta charset = "UTF-8">
    <title>Category - {{$category->category_code}}</title>
    <meta name = "viewport" content = "width=device-width, initial-scale=1">
    <link type = "text/css" rel = "stylesheet"
          href = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
</head>
<body>
    <div class = "container">
        <a href = "{{ url(sprintf("/products_config/categories/%d/edit", $category->id)) }}" class="btn btn-success">Edit this category</a>
        <table class = "table table-bordered">
            <caption>Category details</caption>
            <tr>
                <td>category_code</td>
                <td>{{$category->category_code}}</td>
            </tr>
            <tr>
                <td>station_description</td>
                <td>{{$category->category_description}}</td>
            </tr>
            <tr>
                <td>category_display_order</td>
                <td>{{$category->category_display_order}}</td>
            </tr>
        </table>
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