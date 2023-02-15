<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Section Report History</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">

	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>

</head>
<body>
	@include('includes.header_menu')
	<div class = "container">
		
			<ol class = "breadcrumb">
				<li><a href = "{{url('/')}}">Home</a></li>
				<li><a href = "{{url('/prod_report/summary')}}">Section Report</a></li>
				<li><a href = "{{url('/report/history')}}">History</a></li>
			</ol>

			
		<h3 class = "page-header">Section Report History</h3>
			
		
			<ul>
				
			@foreach($contents as $file)
				<li><a href="{{ url('/report/viewPdf?filename='. $file) }}" target="_blank">{{ $file }}</a></li>
			
			@endforeach
			
			</ul>
				
</body>
</html>