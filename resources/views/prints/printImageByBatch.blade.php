<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Bulk batch update</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet"
	      href = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
	<link type = "text/css" rel = "stylesheet"
	      href = "/assets/css/bootstrap-horizon.css" />
	<link type = "text/css" rel = "stylesheet"
	      href = "//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
	<style>
		.parent-selector {
			width: 200px;
			overflow: auto;
		}
	</style>
</head>
<body>
	@include('includes.header_menu')
	<div class = "container">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li class = "active">Batch Move to Soft, Hard Dir</li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')

		{!! Form::open(['url' => url('prints/movePrintImageByBatch'), 'method' => 'post', 'class'=>'form-horizontal', 'role'=>'form']) !!}
		Directory and File naming rules<br>
			<b>Batch Number</b>-<b>Printer Profile</b>-XXX-XXX<br>
			<b>EX: </b>32449-soft-pen-sub
		<div class = "form-group">
			{!!Form::label('batch_number','Batch#',['class'=>'control-label col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::textarea('batch_number', null, ['id' => 'batch_number','class'=>'form-control']) !!}
			</div>
		</div>
		<div class = "form-group">
			{!!Form::label('destination','Destination',['class'=>'control-label col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::select('destination', $destination, $destinationSelect, ['id' => 'destination','class'=>'form-control']) !!}
			</div>
		</div>
		<div class = "form-group col-xs-6">
			<label for = "move"></label>
			<div class = "col-md-offset-2 col-md-2">
				{!! Form::submit('Move',['id'=>'move','class'=>'btn btn-primary']) !!}
			</div>
		</div>
		{!! Form::close() !!}
		<table class = "table table-bordered">
			<tr>
			<td><b>Batch# Moved Good</b></td>
			<td><b>Batch# Not Found</b></td>
			<td><b>File Not found in Batch# </b></td>
			</tr>
			
		<tr>
			<td>
				@if(isset($batchMoveGood))
					@foreach($batchMoveGood as $batchNumber)
						{{ $batchNumber }}<br>
					@endforeach
				@endif
			</td>
			<td>
				@if(isset($batchNumbersNotFound))
					@foreach($batchNumbersNotFound as $batchNumber)
						{{ $batchNumber }}<br>
					@endforeach
				@endif
			</td>
			<td>
				@if(isset($batchFileNotFound))
					@foreach($batchFileNotFound as $batchNumber)
						{{ $batchNumber }}<br>
					@endforeach
				@endif
			</td>
		</tr>
		</table>

		

	</div>
	<script type = "text/javascript" src = "//code.jquery.com/jquery-1.11.3.min.js"></script>
	<script type = "text/javascript" src = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

	<script type = "text/javascript">

	</script>
</body>
</html>