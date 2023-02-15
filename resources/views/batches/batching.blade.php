<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Graphics</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<style>
	.border-row {
		border-bottom: 1px solid LightGrey;
		padding-top: 5px;
	}
	.bottom-row {
		padding-top: 5px;
	}
	</style>
</head>
<body>
	@include('includes.header_menu')
		<div class = "container" style="width:95%;">
			<ol class = "breadcrumb">
				<li><a href = "{{url('/')}}">Home</a></li>
				<li class = "active">Batching</li>
			</ol>
			@include('includes.error_div')
			@include('includes.success_div')
			
			<h4 class="page-header">Batching Control</h4>
			
			<div class="col-xs-12 col-sm-6 col-md-4"> 
				<div class="panel panel-default rounded">
					<div class="panel-heading">Batching</div>
					<div class="panel-body">
						<div class="col-xs-12 col-sm-12 col-md-12"> 
							<div class="col-xs-9 col-sm-9 col-md-9 border-row"> 
								Unbatched Items
							</div>
							<div class="col-xs-3 col-sm-3 col-md-3 border-row"> 
								X
							</div>
							<div class="col-xs-9 col-sm-9 col-md-9 border-row"> 
								Needs Route
							</div>
							<div class="col-xs-3 col-sm-3 col-md-3 border-row"> 
								X
							</div>
							<div class="col-xs-9 col-sm-9 col-md-9 border-row"> 
								No Graphic SKU
							</div>
							<div class="col-xs-3 col-sm-3 col-md-3 border-row"> 
								X
							</div>
							<div class="col-xs-9 col-sm-9 col-md-9 border-row"> 
								Graphic SKU not Found
							</div>
							<div class="col-xs-3 col-sm-3 col-md-3 border-row"> 
								X
							</div>
							<div class="col-xs-9 col-sm-9 col-md-9 bottom-row"> 
								XML Settings
							</div>
							<div class="col-xs-3 col-sm-3 col-md-3 bottom-row"> 
								X
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="col-xs-12 col-sm-6 col-md-4"> 
				<div class="panel panel-default rounded">
					<div class="panel-heading">Create Graphics</div>
					<div class="panel-body">
						<div class="col-xs-12 col-sm-12 col-md-12"> 
							<div class="col-xs-9 col-sm-9 col-md-9 border-row"> 
								To Export
							</div>
							<div class="col-xs-3 col-sm-3 col-md-3 border-row"> 
								X
							</div>
							<div class="col-xs-9 col-sm-9 col-md-9 border-row"> 
								Exported
							</div>
							<div class="col-xs-3 col-sm-3 col-md-3 border-row"> 
								X
							</div>
							<div class="col-xs-9 col-sm-9 col-md-9 border-row"> 
								Manual Graphics
							</div>
							<div class="col-xs-3 col-sm-3 col-md-3 border-row"> 
								X
							</div>
							<div class="col-xs-9 col-sm-9 col-md-9 border-row"> 
								Pendant Errors
							</div>
							<div class="col-xs-3 col-sm-3 col-md-3 border-row"> 
								X
							</div>
							<div class="col-xs-9 col-sm-9 col-md-9 bottom-row"> 
								Graphic Found
							</div>
							<div class="col-xs-3 col-sm-3 col-md-3 bottom-row"> 
								X
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="col-xs-12 col-sm-6 col-md-4"> 
				<div class="panel panel-default rounded">
					<div class="panel-heading">Print</div>
					<div class="panel-body">
						<div class="col-xs-12 col-sm-12 col-md-12"> 
							<div class="col-xs-9 col-sm-9 col-md-9 border-row"> 
								Sublimation
							</div>
							<div class="col-xs-3 col-sm-3 col-md-3 border-row"> 
								X
							</div>
							<div class="col-xs-9 col-sm-9 col-md-9 border-row"> 
								OKI
							</div>
							<div class="col-xs-3 col-sm-3 col-md-3 border-row"> 
								X
							</div>
							<div class="col-xs-9 col-sm-9 col-md-9 border-row"> 
								Inkjet
							</div>
							<div class="col-xs-3 col-sm-3 col-md-3 border-row"> 
								X
							</div>
							<div class="col-xs-9 col-sm-9 col-md-9 bottom-row"> 
								Sandblast
							</div>
							<div class="col-xs-3 col-sm-3 col-md-3 bottom-row"> 
								X
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

	<script type = "text/javascript">
				
		$(function ()
		{
			$('[data-toggle="tooltip"]').tooltip();
		});
		
	</script>
</body>
</html>