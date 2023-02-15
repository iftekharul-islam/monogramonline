<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Shipment list</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	<link type = "text/css" rel = "stylesheet" href="/assets/css/pikaday.min.css">
	<link rel="stylesheet" href="/assets/css/chosen.min.css">

	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/moment.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/pikaday.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/chosen.jquery.min.js"></script>
</head>

<body>
@include('includes.header_menu')
<div class = "container"  style="min-width: 1400px;">
	<ol class = "breadcrumb">
		<li><a href = "{{url('/')}}">Home</a></li>
		<li>DHL Driver Manifest</li>
	</ol>
	@include('includes.error_div')
	@include('includes.success_div')

	<div class = "col-xs-12">
		{!! Form::open(['method' => 'get', 'url' => url('shippingMainfest'), 'id' => 'search-order']) !!}
		<div class = "form-group col-xs-3">
			<label for = "search_for_first">Search for 1</label>
			{!! Form::text('search_for_first', $request->get('search_for_first'), ['id'=>'search_for_first', 'class' => 'form-control', 'placeholder' => 'Comma delimited']) !!}
		</div>
		<div class = "form-group col-xs-3">
			<label for = "search_in_first">Search in 1</label>
			{!! Form::select('search_in_first', $search_in, $request->get('search_in_first'), ['id'=>'search_in_first', 'class' => 'form-control']) !!}
		</div>
		<div class = "form-group col-xs-3">
			<label for = "search_for_second">Search for 2</label>
			{!! Form::text('search_for_second', $request->get('search_for_second'), ['id'=>'search_for_second', 'class' => 'form-control', 'placeholder' => 'Comma delimited']) !!}
		</div>
		<div class = "form-group col-xs-3">
			<label for = "search_in_first">Search in 2</label>
			{!! Form::select('search_in_second', $search_in, $request->get('search_in_second'), ['id'=>'search_in_second', 'class' => 'form-control']) !!}
		</div>
		<br />

		<div class = "form-group col-xs-3">
			<label for = "start_date">Start date</label>
			<div class = 'input-group date' id = 'start_date_picker'>
				{!! Form::text('start_date', $request->get('start_date'), ['id'=>'start_datepicker', 'class' => 'form-control', 'placeholder' => 'Enter start date', 'autocomplete' => 'off']) !!}
				<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
			</div>
		</div>
		<div class = "form-group col-xs-3">
			<label for = "end_date">End date</label>
			<div class = 'input-group date' id = 'end_date_picker'>
				{!! Form::text('end_date', $request->get('end_date'), ['id'=>'end_datepicker', 'class' => 'form-control', 'placeholder' => 'Enter end date', 'autocomplete' => 'off']) !!}
				<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
			</div>
		</div>
		<div class = "form-group col-xs-2">
			<label for = "status">Store</label>
			<br>
			{!! Form::select('store_id', $stores, $request->get('store_id'), ['id'=>'store_id', 'class' => 'form-control']) !!}
		</div>
		{!! Form::hidden('shipped', $request->get('shipped','0'), ['id'=>'shipped']) !!}
		<div class = "form-group col-xs-2">
			<label for = "" class = ""></label>
			{!! Form::submit('Search', ['id'=>'search', 'style' => 'margin-top: 2px;', 'class' => 'btn btn-primary form-control']) !!}
		</div>

		{!! Form::close() !!}
	</div>

	<div class = "col-xs-12">

		{!! Form::open(['method' => 'get', 'url' => url('shippingMainfest/getDhlManifest'), 'id' => 'dhlManifest-order']) !!}
		<div class = "form-group col-xs-3">
			<label for = "dhlManifest_date">DHL Domestic Manifest date</label>
			<div class = 'input-group date' id = 'dhlManifest_date_picker'>
				{!! Form::text('dhlManifest_date', $request->get('dhlManifest_date'), ['id'=>'dhlManifest_datepicker', 'class' => 'form-control', 'placeholder' => 'Enter DHL Manifest', 'autocomplete' => 'off']) !!}
				<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
			</div>
		</div>

		<div class = "form-group col-xs-2">
			<label for = "" class = ""></label>
			{!! Form::submit('DHL Domestic Manifest', ['id'=>'dhlManifest', 'style' => 'margin-top: 2px;', 'class' => 'btn btn-primary form-control']) !!}
		</div>

		{!! Form::close() !!}


		{!! Form::open(['method' => 'get', 'url' => url('shippingMainfest/getDhlInternationalManifest'), 'id' => 'dhlManifest-order']) !!}
		<div class = "form-group col-xs-3">
			<label for = "dhlInternationalManifest_date">DHL International Manifest date</label>
			<div class = 'input-group date' id = 'dhlInternationalManifest_date_picker'>
				{!! Form::text('dhlInternationalManifest_date', $request->get('dhlInternationalManifest_date'), ['id'=>'dhlInternationalManifest_datepicker', 'class' => 'form-control', 'placeholder' => 'Enter DHL Manifest', 'autocomplete' => 'off']) !!}
				<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
			</div>
		</div>

		<div class = "form-group col-xs-2">
			<label for = "" class = ""></label>
			{!! Form::submit('DHL International Manifest', ['id'=>'dhlManifest', 'style' => 'margin-top: 2px;', 'class' => 'btn btn-primary form-control']) !!}
		</div>

		{!! Form::close() !!}

	</div>

	@if(count($dhlManifestList) > 0)
		<div class = "col-xs-12">
			<h4>
				{{  $totalMag }}
			</h4>
		</div>

		<table class="table">
			<tr>
				<th>ID</th>
				<th>Manifest Date</th>
				<th>Store Id</th>
				<th>Mail Class</th>
				<th>Manifest Id</th>
				<th>PDF Path</th>
				<th>Created By</th>
				<th>Created Date</th>

			</tr>
			@foreach($dhlManifestList as $dhlManifestRpws)

					<tr>
						<td>
							{{ $dhlManifestRpws->id }}
						</td>
						<td>
							{{ $dhlManifestRpws->manifestDate }}
						</td>
						<td>
							{{ $dhlManifestRpws->store_id }}
						</td>
						<td>
							{{ $dhlManifestRpws->mail_class }}
						</td>
						<td>
							{{ $dhlManifestRpws->manifestId }}
						</td>
						<td>

							<a href="{{$dhlManifestRpws->pdf_path}}" class="add-to-cart" target = "_blank">{{ $dhlManifestRpws->pdf_path }}</a>
						</td>
						<td>
							@if(isset($userlist[$dhlManifestRpws->user]))
								{{ $userlist[$dhlManifestRpws->user] }}
								@else
								"Unknown"
							@endif

						</td>
						<td>
							{{ $dhlManifestRpws->created_at }}
						</td>
					</tr>

			@endforeach
		</table>


	@elseif (count($dhlManifestList) > 0)
		<div class = "col-xs-12">
			<div class = "alert alert-warning">
				No Shipments Found.
			</div>
		</div>
	@endif
</div>

@include('/rejections/rejection_modal')

<script type = "text/javascript">
	var picker = new Pikaday(
			{
				field: document.getElementById('start_datepicker'),
				format : "YYYY-MM-DD",
				minDate: new Date('2016-06-01'),
			});

	var picker = new Pikaday(
			{
				field: document.getElementById('end_datepicker'),
				format : "YYYY-MM-DD",
				minDate: new Date('2016-06-01'),
			});

	var picker = new Pikaday(
			{
				field: document.getElementById('dhlManifest_datepicker'),
				format : "YYYY-MM-DD",
				minDate: new Date('2016-06-01'),
			});

	var picker = new Pikaday(
			{
				field: document.getElementById('dhlInternationalManifest_datepicker'),
				format : "YYYY-MM-DD",
				minDate: new Date('2016-06-01'),
			});


</script>
</body>
</html>