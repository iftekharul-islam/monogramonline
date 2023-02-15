<!doctype html> 
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Move Batches</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	<link rel="stylesheet" href="/assets/css/chosen.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/chosen.jquery.min.js"></script>	

	<style>
		table {
			table-layout: fixed;
			font-size: 12px;
		}

		td {
			width: auto;
		}
		
	</style>
</head>
<body>
	@include('includes.header_menu')
	<div class = "container">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li class = "active">Move Export Images</li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')

		<div class = "col-md-12">
			<div class = "panel panel-default">
				<div class = "panel-heading">Search</div>
				<div class = "panel-body">
					{!! Form::open(['url' => '/production/expoert', 'method' => 'POST']) !!}
				<div class = "col-xs-12">

					<div class="form-group">
						<div class = "form-group col-xs-4">
							<label>Find Batches</label>
							{!! Form::text('scan_batches', $scan_batches, ['id'=>'barcode', 'class' => 'form-control', 'placeholder' => 'Enter Batches']) !!}
						</div>

										
						<div class = "form-group col-xs-2">
							{!! Form::submit('Export', ['id'=>'export_button', 'style' => 'margin-top: 20px;', 'class' => 'btn btn-primary btn-sm form-control']) !!}
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<script type = "text/javascript">
		
		$(function() {
				// Focus on load
				 $('#barcode').focus();
		});

			
	</script>
</body>
</html>