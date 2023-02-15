<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>{{ $design->StyleName }}</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/flexselect.css" media = "screen">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/liquidmetal.js"></script>
	<script type = "text/javascript" src = "/assets/js/jquery.flexselect.js"></script>
</head>
<body>
  @include('includes.header_menu')
  <div class = "container" style="width:95%;">
    <ol class = "breadcrumb">
      <li><a href = "{{url('/')}}">Home</a></li>
      <!-- <li><a href = "{{url('/graphics/designs')}}">Configure Graphics</a></li> -->
      <li>{{ $design->StyleName }}</li>
    </ol>
    
    @include('includes.error_div')
    @include('includes.success_div')
    
		<h3 class="page-header">
			Graphic SKU: {{ $design->StyleName }}
		</h3>
		
		{!! Form::open(['method' => 'PUT', 'url' => url('/graphics/designs/' . $design->id), 'id' => 'upload_form', 'files'=>'true']) !!}
		
    <div class="col-xs-12 col-sm-12 col-md-3" align="center">
			@if($thumb != null)
				<img src="{{ $thumb }}">
			@endif
			
			<div class="col-xs-10">
				{!! Form::file('template', ['style' => 'margin-top:7px;', 'class' => 'form-control']) !!}
			</div>
			<div class="col-xs-2">
				{!! Form::submit('Upload', ['class' => 'btn btn-sm btn-info', 'style' => 'margin-top:8px;']) !!}
			</div>
			
		</div>
		
		{!! Form::hidden('Sno', $xml['Sno'] ?? '') !!}
		{!! Form::hidden('StyleName', $xml['StyleName'] ?? $design->StyleName) !!}
		
		<div class="col-xs-12 col-sm-12 col-md-9">
			
			<div class="col-xs-12 col-sm-12 col-md-6">
				<div class="row">
					<div class="col-xs-2">
					</div>
					<div class="col-xs-1">
						{!! Form::label('P1','P1:', ['style' => 'margin-top:7px;']) !!}
					</div>
					<div class="col-xs-6">
						{!! Form::select('FontP1', $fonts, $xml['FontP1'] ?? '', ['class' => 'form-control font_select']) !!}
					</div>
					<div class="col-xs-1">
						{!! Form::label('sizeP1','Size:', ['style' => 'margin-top:7px;']) !!}
					</div>
					<div class="col-xs-2">
						{!! Form::number('FontSizeP1', $xml['FontSizeP1'] ?? '0.1', ['min'=> 0, 'step' => '.1', 'style' => 'width:70px;', 'class' => 'form-control']) !!}
					</div>
				</div>
				<div class="row">
					<hr>
				</div>
				<div class="row">
					<div class="col-xs-2">
					</div>
					<div class="col-xs-1">
						{!! Form::label('P2','P2:', ['style' => 'margin-top:7px;']) !!}
					</div>
					<div class="col-xs-6">
						{!! Form::select('FontP2', $fonts, $xml['FontP2'] ?? '', ['class' => 'form-control font_select']) !!}
					</div>
					<div class="col-xs-1">
						{!! Form::label('sizeP2','Size:', ['style' => 'margin-top:7px;']) !!}
					</div>
					<div class="col-xs-2">
						{!! Form::number('FontSizeP2', $xml['FontSizeP2'] ?? '0.1', ['min'=> 0, 'step' => '.1', 'style' => 'width:70px;', 'class' => 'form-control']) !!}
					</div>
				</div>
				<div class="row">
					<hr>
				</div>
				<div class="row">
					<div class="col-xs-2">
					</div>
					<div class="col-xs-1">
						{!! Form::label('P3','P3:', ['style' => 'margin-top:7px;']) !!}
					</div>
					<div class="col-xs-6">
						{!! Form::select('FontP3', $fonts, $xml['FontP3'] ?? '', ['class' => 'form-control font_select']) !!}
					</div>
					<div class="col-xs-1">
						{!! Form::label('sizeP3','Size:', ['style' => 'margin-top:7px;']) !!}
					</div>
					<div class="col-xs-2">
						{!! Form::number('FontSizeP3', $xml['FontSizeP3'] ?? '0.1', ['min'=> 0, 'step' => '.1', 'style' => 'width:70px;', 'class' => 'form-control']) !!}
					</div>
				</div>
				<div class="row">
					<hr>
				</div>
				<div class="row">
					<div class="col-xs-2">
					</div>
					<div class="col-xs-1">
						{!! Form::label('P4','P4:', ['style' => 'margin-top:7px;']) !!}
					</div>
					<div class="col-xs-6">
						{!! Form::select('FontP4', $fonts, $xml['FontP4'] ?? '', ['class' => 'form-control font_select']) !!}
					</div>
					<div class="col-xs-1">
						{!! Form::label('sizeP4','Size:', ['style' => 'margin-top:7px;']) !!}
					</div>
					<div class="col-xs-2">
						{!! Form::number('FontSizeP4', $xml['FontSizeP4'] ?? '0.1', ['min'=> 0, 'step' => '.1', 'style' => 'width:70px;', 'class' => 'form-control']) !!}
					</div>
				</div>
				<div class="row">
					<hr>
				</div>
				<div class="row">
					<div class="col-xs-2">
					</div>
					<div class="col-xs-1">
						{!! Form::label('P5','P5:', ['style' => 'margin-top:7px;']) !!}
					</div>
					<div class="col-xs-6">
						{!! Form::select('FontP5', $fonts, $xml['FontP5'] ?? '', ['class' => 'form-control font_select']) !!}
					</div>
					<div class="col-xs-1">
						{!! Form::label('sizeP5','Size:', ['style' => 'margin-top:7px;']) !!}
					</div>
					<div class="col-xs-2">
						{!! Form::number('FontSizeP5', $xml['FontSizeP5'] ?? '0.1', ['min'=> 0, 'step' => '.1', 'style' => 'width:70px;', 'class' => 'form-control']) !!}
					</div>
				</div>
				<div class="row">
					<hr>
				</div>
				<div class="row">
					<div class="col-xs-2">
					</div>
					<div class="col-xs-1">
						{!! Form::label('P6','P6:', ['style' => 'margin-top:7px;']) !!}
					</div>
					<div class="col-xs-6">
						{!! Form::select('FontP6', $fonts, $xml['FontP6'] ?? '', ['class' => 'form-control']) !!}
					</div>
					<div class="col-xs-1">
						{!! Form::label('sizeP6','Size:', ['style' => 'margin-top:7px;']) !!}
					</div>
					<div class="col-xs-2">
						{!! Form::number('FontSizeP6', $xml['FontSizeP6'] ?? '0.1', ['min'=> 0, 'step' => '.1', 'style' => 'width:70px;', 'class' => 'form-control']) !!}
					</div>
				</div>
			</div>
			<div class="col-xs-12 col-sm-12 col-md-6">
				<div class="row">
					<div class="col-xs-2">
					</div>
					<div class="col-xs-1">
						{!! Form::label('B1','B1:', ['style' => 'margin-top:7px;']) !!}
					</div>
					<div class="col-xs-6">
						{!! Form::select('FontB1', $fonts, $xml['FontB1'] ?? '', ['class' => 'form-control font_select']) !!}
					</div>
					<div class="col-xs-1">
						{!! Form::label('sizeB1','Size:', ['style' => 'margin-top:7px;']) !!}
					</div>
					<div class="col-xs-2">
						{!! Form::number('FontSizeB1', $xml['FontSizeB1'] ?? '0.1', ['min'=> 0, 'step' => '.1', 'style' => 'width:70px;', 'class' => 'form-control']) !!}
					</div>
				</div>
				<div class="row">
					<hr>
				</div>
				<div class="row">
					<div class="col-xs-2">
					</div>
					<div class="col-xs-1">
						{!! Form::label('B2','B2:', ['style' => 'margin-top:7px;']) !!}
					</div>
					<div class="col-xs-6">
						{!! Form::select('FontB2', $fonts, $xml['FontB2'] ?? '', ['class' => 'form-control font_select']) !!}
					</div>
					<div class="col-xs-1">
						{!! Form::label('sizeB2','Size:', ['style' => 'margin-top:7px;']) !!}
					</div>
					<div class="col-xs-2">
						{!! Form::number('FontSizeB2', $xml['FontSizeB2'] ?? '0.1', ['min'=> 0, 'step' => '.1', 'style' => 'width:70px;', 'class' => 'form-control']) !!}
					</div>
				</div>
				<div class="row">
					<hr>
				</div>
				<div class="row">
					<div class="col-xs-2">
					</div>
					<div class="col-xs-1">
						{!! Form::label('B3','B3:', ['style' => 'margin-top:7px;']) !!}
					</div>
					<div class="col-xs-6">
						{!! Form::select('FontB3', $fonts, $xml['FontB3'] ?? '', ['class' => 'form-control font_select']) !!}
					</div>
					<div class="col-xs-1">
						{!! Form::label('sizeB3','Size:', ['style' => 'margin-top:7px;']) !!}
					</div>
					<div class="col-xs-2">
						{!! Form::number('FontSizeB3', $xml['FontSizeB3'] ?? '0.1', ['min'=> 0, 'step' => '.1', 'style' => 'width:70px;', 'class' => 'form-control']) !!}
					</div>
				</div>
				<div class="row">
					<hr>
				</div>
				<div class="row">
					<div class="col-xs-2">
					</div>
					<div class="col-xs-1">
						{!! Form::label('B4','B4:', ['style' => 'margin-top:7px;']) !!}
					</div>
					<div class="col-xs-6">
						{!! Form::select('FontB4', $fonts, $xml['FontB4'] ?? '', ['class' => 'form-control font_select']) !!}
					</div>
					<div class="col-xs-1">
						{!! Form::label('sizeB4','Size:', ['style' => 'margin-top:7px;']) !!}
					</div>
					<div class="col-xs-2">
						{!! Form::number('FontSizeB4', $xml['FontSizeB4'] ?? '0.1', ['min'=> 0, 'step' => '.1', 'style' => 'width:70px;', 'class' => 'form-control']) !!}
					</div>
				</div>
				<div class="row">
					<hr>
				</div>
				<div class="row">
					<div class="col-xs-2">
					</div>
					<div class="col-xs-1">
						{!! Form::label('B5','B5:', ['style' => 'margin-top:7px;']) !!}
					</div>
					<div class="col-xs-6">
						{!! Form::select('FontB5', $fonts, $xml['FontB5'] ?? '', ['class' => 'form-control font_select']) !!}
					</div>
					<div class="col-xs-1">
						{!! Form::label('sizeB5','Size:', ['style' => 'margin-top:7px;']) !!}
					</div>
					<div class="col-xs-2">
						{!! Form::number('FontSizeB5', $xml['FontSizeB5'] ?? '0.1', ['min'=> 0, 'step' => '.1', 'style' => 'width:70px;', 'class' => 'form-control']) !!}
					</div>
				</div>
				<div class="row">
					<hr>
				</div>
				<div class="row">
					<div class="col-xs-2">
					</div>
					<div class="col-xs-1">
						{!! Form::label('B6','B6:', ['style' => 'margin-top:7px;']) !!}
					</div>
					<div class="col-xs-6">
						{!! Form::select('FontB6', $fonts, $xml['FontB6'] ?? '', ['class' => 'form-control font_select']) !!}
					</div>
					<div class="col-xs-1">
						{!! Form::label('sizeB6','Size:', ['style' => 'margin-top:7px;']) !!}
					</div>
					<div class="col-xs-2">
						{!! Form::number('FontSizeB6', $xml['FontSizeB6'] ?? '0.1', ['min'=> 0, 'step' => '.1', 'style' => 'width:70px;', 'class' => 'form-control']) !!}
					</div>
				</div>
			</div>

			<div class="col-xs-12 col-sm-6 col-md-6">
				<div class="row">
					<hr>
				</div>
				<div class="col-xs-2" align="right">
				</div>
				<div class="col-xs-3" align="right">
					{!! Form::label('case','Change Case:', ['style' => 'margin-top:7px;']) !!}
				</div>
				<div class="col-xs-5">
					{!! Form::select('ChangeCase', $cases, $xml['ChangeCase'] ?? 'No Change', ['class' => 'form-control']) !!}
				</div>
				<div class="col-xs-2">
					&nbsp;
				</div>
			</div>
			
			<div class="col-xs-12 col-sm-6 col-md-4">
				<div class="row">
					<hr>
				</div>
				<div class="row">
					<div class="col-xs-2" align="right">
						{!! Form::checkbox('CombineP', 1, $xml['CombineP'] ?? '0', ['style' => 'width:1.3em;', 'class' => 'form-control']) !!}
					</div>
					<div class="col-xs-10">
						{!! Form::label('combP','Combine P1 to P6 and Load in P1', ['style' => 'margin-top:12px;']) !!}
					</div>
				</div>
				<div class="row">
					<div class="col-xs-2" align="right">
						{!! Form::checkbox('CombineB', 1, $xml['CombineB'] ?? '0', ['style' => 'width:1.3em;', 'class' => 'form-control']) !!}
					</div>
					<div class="col-xs-10">
						{!! Form::label('combB','Combine B1 to B6 and Load in B1', ['style' => 'margin-top:12px;']) !!}
					</div>
				</div>
				<div class="row">
					<div class="col-xs-2" align="right">
						{!! Form::checkbox('SingleOrder', 1, $xml['SingleOrder'] ?? '0', ['style' => 'width:1.3em;', 'class' => 'form-control']) !!}
					</div>
					<div class="col-xs-10">
						{!! Form::label('single','Single Order', ['style' => 'margin-top:12px;']) !!}
					</div>
				</div>
			</div>
			<div class="col-xs-12 col-sm-12 col-md-2">
					<div class="row">
						<hr>
					</div>
					<div class="row" align="center">
						{!! Form::submit('Save', ['class' => 'btn btn-lg btn-primary']) !!}
					</div>
				</div>
			</div>
			
			{!! Form::close() !!}
			
		</div>
	</div>
	
	<script>
		$('.font_select').flexselect();
	</script>
</body>
</html>