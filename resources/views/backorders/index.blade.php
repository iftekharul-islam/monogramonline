<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Back Orders</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">

	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>

	<style>
		tr {
			font-size: 12px;
		}
		tr.lines:nth-child(even) {
			background-color: #f2f2f2;
		}
		tr.lines:hover {
			background-color: #FEF9E7;
		}
		td th {
			table-layout: fixed;
			width: auto;
			white-space: nowrap;
		}
		.data {
			border-left: 1px solid #ddd;
			border-right: 1px solid #ddd;
			text-align: right;
		}
		input[type=number]{
		  width: 80px;
		}
	</style>

</head>
<body>
	@include('includes.header_menu')
	<div class = "container">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li>Back Orders</li>
		</ol>

		@include('includes.error_div')
		@include('includes.success_div')

		<h3 class="page-header">Back Orders</h3>
		
		@include('backorders.includes.scan')
		
		<ul id="myTab" class="nav nav-tabs">
			<li class="active"><a href="#batched" data-toggle="tab">Backordered Items ({!! count($batched) !!})</a></li>
			<li><a href="#unbatched" data-toggle="tab">Unbatched Backorders ({!! count($unbatched) !!})</a></li>
		</ul>
		
		<div id="tabContent" class="tab-content">		
				
				<div class="tab-pane fade in active" id="batched">
					
					@setvar($result = $batched)
					@setvar($tab_value = 'batched')
					
					@include('backorders.includes.list')
					
					@setvar($result = null)
					
				</div>
				
				<div class="tab-pane fade" id="unbatched">
					
					@setvar($result = $unbatched)
					@setvar($tab_value = 'unbatched')
					
					@include('backorders.includes.list')
					
					@setvar($result = null)
					
				</div>
				
			</div>
	</div>
</body>
</html>