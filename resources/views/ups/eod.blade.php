<!doctype html>
<html lang = "en">
<head>
    <meta charset = "UTF-8">
    <title>UPS End Of Day</title>
    <meta name = "viewport" content = "width=device-width, initial-scale=1">
    <link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
    <link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap-multiselect.css">
    <link type = "text/css" rel = "stylesheet" href="/assets/css/pikaday.min.css">

    <script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
    <script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
    <script type = "text/javascript" src = "/assets/js/bootstrap-multiselect.js"></script>
    <script type = "text/javascript" src = "/assets/js/moment.min.js"></script>
    <script type = "text/javascript" src = "/assets/js/pikaday.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.1.5/dist/sweetalert2.all.min.js"></script>
    <style>
        tr {
            font-size: 11px;
        }
        .reject {
            border-left:solid thin;
            border-left-color:#cccccc;
            border-right:solid thin;
            border-right-color:#cccccc;
        }
    </style>

</head>
<body>
@include('includes.header_menu')
<div class = "container" style="min-width: 1400px;">
    <ol class = "breadcrumb">
        <li><a href = "{{url('/')}}">Home</a></li>
        <li>UPS End Of Day</li>
    </ol>

    <h3 class = "page-header">
        UPS End Of Day
    </h3>

    <div class = "col-xs-12">
        <div class = "panel panel-default">
            <div class = "panel-heading">Only use this button to specify it's the end of the day. Doing this will auto upgrade all shipments that weren't shipped by today</div>
            <div class = "panel-body">
                <div class="row">
                   <form>
                       <button type="submit" class="btn btn-primary form-control end-of-day">End Of Day</button>
                   </form>
            </div>
        </div>
    </div>
</div>

    <script type="text/javascript">
        $(document).ready(function() {
            $('form').on('submit', function(e){
                e.preventDefault();

                var success = false

                if(success) {
                    Swal.fire(
                        'Success',
                        'You started the End Of Day',
                        'success'
                    )
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: "This part is not yet finished, please try again later",
                    })
                }
            });
        });
        </script>


</body>
</html>