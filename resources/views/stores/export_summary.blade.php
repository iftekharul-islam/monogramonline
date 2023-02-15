<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Marketplace Exports</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/pikaday.min.css">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap-multiselect.css">
		
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/moment.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/pikaday.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap-multiselect.js"></script>	

</head>
<body>
	@include('includes.header_menu')
	<div class = "container">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "{{url('/transfer/export')}}">Export Shipments</a></li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		
		@if (isset($csv_summary) && count($csv_summary) > 0)
			
			<h3 class="page-header">Shipment Upload Files</h3>
			 
			<table class="table table-bordered">
				<tr>
					<th>Store</th>
					<th width="150">Shipment Count</th>
				</tr>
				
			@foreach ($csv_summary as $row)

				@if(!isset($drop[$row->store_id]))
					<tr>
						<td>
							{{ $row->store_name }}
						</td>
						<td align="right">
							<a href="{{ url(sprintf('/transfer/export/details?store_id=%s&type=csv', $row->store_id)) }}">
							{{ $row->count }}
							</a>
						</td>
					</tr>
					@endif
			@endforeach
			</table>
			
		@endif

		{{-- A NEW SECTION STARTS--}}

		@if (count($dropship) != 0)

			<h3 class="page-header">Drop Ship Order Files</h3>

			<table class="table table-bordered">
				<tr>
					<th>Store</th>
					<th width="150">Count</th>
				</tr>

				@foreach ($dropship as $row)


					<tr>
						<td>
							{{ $row['NAME'] }}
						</td>
						<td align="right">
							<a href="{{ url(sprintf('/transfer/export/details?store_id=%s&type=csv', $row['ID_REAL'])) }}&dropship=true">
								{{ $row['COUNT'] }}
							</a>
						</td>
					</tr>

				@endforeach


			</table>

		@endif

		{{-- A NEW SECTION ENDS --}}
		
		@if (isset($qb_summary) && count($qb_summary) > 0)
			
			<h3 class="page-header">Quickbooks Exports</h3>
			
			<table class="table table-bordered">
				<tr>
					<th>Store</th>
					<th width="150">Shipment Count</th>
				</tr>
				
			@foreach ($qb_summary as $row)
					<tr>
						<td>
							{{ $row->store_name }}
						</td>
						<td align="right">
							<a href="{{ url(sprintf('/transfer/export/details?store_id=%s&type=qb', $row->store_id)) }}">
							{{ $row->count }}
							</a>
						</td>
					</tr>
			@endforeach
			
			</table>
			
		@endif

		<div class = "col-xs-12">
			<div class = "panel panel-default">
				<div class = "panel-heading">Create Quickbooks Export by Date</div>
				<div class = "panel-body">
					{!! Form::open(['url' => '/transfer/export/qb', 'method' => 'post']) !!}
						<div class = "form-group col-xs-3">
							{!! Form::select('store_ids[]', $stores, $store_ids ?? [], ['id'=>'store_ids', 'multiple' => 'multiple', 'class' => 'form-control']) !!}
						</div>
						<div class = "form-group col-xs-3">
							<div class = 'input-group date' id = 'start_date_picker'>
								{!! Form::text('start_date', null, ['id'=>'start_datepicker', 'class' => 'form-control', 'placeholder' => 'Enter start date', 'autocomplete' => 'off']) !!}
								<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
							</div>
						</div>
						<div class = "form-group col-xs-3">
							<div class = 'input-group date' id = 'end_date_picker'>
								{!! Form::text('end_date', null, ['id'=>'end_datepicker', 'class' => 'form-control', 'placeholder' => 'Enter end date', 'autocomplete' => 'off']) !!}
								<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
							</div>
						</div>
						<div class = "form-group col-xs-2">
							{!! Form::submit('Create', ['id'=>'search', 'style' => 'margin-top: 5px;', 'class' => 'btn btn-primary']) !!}
						</div>
					{!! Form::close() !!}
				</div>
			</div>
		</div>


		<div class = "col-xs-12">
			<div class = "panel panel-default">
				<div class = "panel-heading">Billing CSV Export by Date (Max select 15 Days)</div>
				<div class = "panel-body">
					{!! Form::open(['url' => '/transfer/export/qbcsv', 'method' => 'post']) !!}
					<div class = "form-group col-xs-3">
						{!! Form::select('store_ids[]', $stores, $store_ids ?? [], ['id'=>'store_ids', 'multiple' => 'multiple', 'class' => 'form-control']) !!}
					</div>
					<div class = "form-group col-xs-3">
						<div class = 'input-group date' id = 'start_date_picker'>
							{!! Form::text('start_date', null, ['id'=>'start_datepicker_csv', 'class' => 'form-control', 'placeholder' => 'Enter start date', 'autocomplete' => 'off']) !!}
							<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
						</div>
					</div>
					<div class = "form-group col-xs-3">
						<div class = 'input-group date' id = 'end_date_picker'>
							{!! Form::text('end_date', null, ['id'=>'end_datepicker_csv', 'class' => 'form-control', 'placeholder' => 'Enter end date', 'autocomplete' => 'off']) !!}
							<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
						</div>
					</div>
					<div class = "form-group col-xs-2">
						{!! Form::submit('Create CSV', ['id'=>'search', 'style' => 'margin-top: 5px;', 'class' => 'btn btn-primary']) !!}
					</div>
					{!! Form::close() !!}
				</div>
			</div>
		</div>


	</div>
	
	<script>

		$('#store_ids').multiselect({
												nonSelectedText:'Select Store',
												includeSelectAllOption:true,
												numberDisplayed: 1,
											});
		
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
		
	</script>
	
</body>
</html>