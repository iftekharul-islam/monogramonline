<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>View Graphic</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	
</head>
<body>
	@include('includes.header_menu')
		<div class = "container" style="width:95%;">
			<ol class = "breadcrumb">
				<li><a href = "{{url('/')}}">Home</a></li>
				<li class = "active">View Graphic</li>
			</ol>
			@include('includes.error_div')
			@include('includes.success_div')
			
			<h3 class="page-header">Batch {{ $batch_number }} Graphics</h3>
			
			<h4>File: {{ substr($file, strrpos($file, '/') + 1) }}</h4>
			
			<br>
			
			<div class = "col-xs-12">
				<div class = "col-xs-7">
					@if(isset($file_names))
						@foreach($file_names as $thumb)
							<img src="{{ url('assets/images/graphics/' . substr($thumb, 0, strpos($thumb, '.')) . '.jpg') }}">
						@endforeach
					@endif
				</div>
				<div class = "col-xs-5">
					@if (isset($files) && count($files) > 0)
						<strong>All Files for this batch:</strong>
						<ul>
						@foreach ($files as $date => $graphic)
							<li>
							{{ date("Y-m-d H:i:s", $date) }} : 
			        <a href = "{{ url(sprintf('graphics/view_graphic?batch_number=%s&file=%s', $batch_number, $graphic)) }}"
			           target = "_blank">{{ substr($graphic, strrpos($graphic, '/') + 1) }}</a>
							</li>
			      @endforeach
						</ul>
					@endif
				</div>
			</div>
			
		</div>
</body>
</html>