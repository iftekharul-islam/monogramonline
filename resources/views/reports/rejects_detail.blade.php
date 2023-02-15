<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Reject Report</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap-multiselect.css">
	<link type = "text/css" rel = "stylesheet" href="/assets/css/pikaday.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap-multiselect.js"></script>	
	<script type = "text/javascript" src = "/assets/js/moment.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/pikaday.min.js"></script>
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
			<li>Reject Report</li>
		</ol>

			<h3 class = "page-header">
				{{ $request->get('item_code') }} Reject Report
			</h3>

			<div class = "col-xs-12">
				<div class = "panel panel-default">
					<div class = "panel-heading">Search</div>
					<div class = "panel-body">
						{!! Form::open(['method' => 'get', 'id' => 'barcode_form']) !!}
						<div class="row">
							<div class = "form-group col-xs-2">
								{!! Form::text('item_id', '', ['id'=>'barcode', 'class' => 'form-control', 'placeholder' => 'Scan Reject or Enter Item']) !!}
							</div>
							<div class = "form-group col-xs-2">
								<div class = 'input-group date'>
									{!! Form::text('start_date', $request->get('start_date'), ['id'=>'start_datepicker', 'class' => 'form-control', 'placeholder' => 'Enter start date', 'autocomplete' => 'off']) !!}
									<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
								</div>
							</div>
							<div class = "form-group col-xs-2">
								<div class = 'input-group date'>
									{!! Form::text('end_date', $request->get('end_date'), ['id'=>'end_datepicker', 'class' => 'form-control', 'placeholder' => 'Enter end date', 'autocomplete' => 'off']) !!}
									<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
								</div>
							</div>
							<div class = "form-group col-xs-3">
								{!! Form::select('store[]', $stores, $request->get('store'), ['id'=>'store', 'multiple' => 'multiple', 'class' => 'form-control']) !!}
							</div>
							<div class = "form-group col-xs-2">
								{!! Form::submit('Find', ['id'=>'Find', 'class' => 'btn btn-primary form-control']) !!}
							</div>
						</div>
						{!! Form::close() !!}
					</div>
				</div>
			</div>
		
		@if (count($items) > 0)
			
			<h4>{{ count($items) }} Rejects Found</h4>
				
			<table id="reject_table" class="table" cellspacing="0" cellpadding="0">
				
	        <tbody>
			
					@setvar($item_group = $items->groupBy('item_id'))

					@foreach($item_group as $item_id)
						<tr>
							<td colspan=8>
						</tr>
						<tr bgcolor="#ffeecc" class="reject">
							<td width="90">Item: {{ $item_id->first()->item->id }}</td>
							<td>Order: <a href="/orders/details/{{ $item_id->first()->item->order_5p }}" target="_blank">{{ $item_id->first()->item->order_5p }}</a></td>
							<td colspan=6>{{ $item_id->first()->item->item_description }}</td>
						</tr>
						
						@foreach($item_id as $reject)
							
								<tr class="reject">
									<td></td>
									<td width="200">
										Rejected {{ substr($reject->created_at, 0, 10) }}
										@if ($reject->from_station) 
											at {{ $reject->from_station->station_name }}
										@endif
									</td>
									<td width="90">By: {{ $reject->user->username }}</td>
									<td width="250">
											Batch: 
											<a href="/batches/details/{{ $reject->from_batch }}" target="_blank">{{ $reject->from_batch }}</a> >> 
											<a href="/batches/details/{{ $reject->to_batch }}" target="_blank">{{ $reject->to_batch }}</a>
									</td>
									<td>{{ $reject->graphic_status }}</td>
									<td>
										@if ($reject->rejection_reason_info)
											{{ $reject->rejection_reason_info->rejection_message }}
										@endif
									</td>
									<td width="200">
											@if ($reject->scrap == null)
												Inventory Not Processed
											@elseif ($reject->scrap > 0)
												Inventory Adjusted
											@elseif ($reject->scrap == 0)
												No Inventory Adjustment
											@else
												ERROR
											@endif
									</td>
									<td>{{ $reject->rejection_message }}</td>
								</tr>

								@if (isset($reject->from_batch_info->scans))
									@foreach($reject->from_batch_info->scans as $scan)
										@if($scan->station->type == 'P')
										<tr bgcolor="#fff7e6" class="reject">
											<td colspan=2></td>
											<td>Scan:</td>
											<td colspan=5>{{ $scan->station->station_name }}
												&nbsp;&nbsp;
												IN: {{ $scan->in_user->username }} {{ $scan->in_date }}
												&nbsp;&nbsp;
												@if ($scan->out_user)
													OUT: {{ $scan->out_user->username }} {{ $scan->in_date }}
												@endif
											</td>
										</tr>
										@endif
									@endforeach
								@endif
								
						@endforeach
						
					@endforeach
					
					<tr>
						<td colspan=8>
					</tr>
					
					</tbody>
					
			</table>
		@endif

	</div>

	<script type = "text/javascript">
	
		$(document).ready(function() {
			
			$('#store').multiselect({includeSelectAllOption:true,
																nonSelectedText:'Filter By Store',
																numberDisplayed: 1,});
		});

		var picker = new Pikaday(
		{
				field: document.getElementById('start_datepicker'),
				format : "YYYY-MM-DD",
				minDate: new Date('2016-06-01'),
				maxDate: new Date(),
				yearRange: [2000,2030]      
		});
		
		var picker = new Pikaday(
		{
				field: document.getElementById('end_datepicker'),
				format : "YYYY-MM-DD",
				minDate: new Date('2016-06-01'),
				maxDate: new Date(),
				yearRange: [2000,2030]      
		});
		
		$(function ()
		{
			$('#barcode').focus();
		});

		$(document).ready(function() {
				var pressed = false; 
				var chars = []; 
				$(window).keypress(function(e) {
						if (e.which >= 48 && e.which <= 57) {
								chars.push(String.fromCharCode(e.which));
						}
						console.log(e.which + ":" + chars.join("|"));
						if (pressed == false) {
								setTimeout(function(){
										if (chars.length >= 10) {
												var barcode = chars.join("");
												console.log("Barcode Scanned: " + barcode);
												// assign value to some input (or do whatever you want)
												$("#barcode").val(barcode);
												$("#barcode_form").submit();
										}
										chars = [];
										pressed = false;
								},500);
						}
						pressed = true;
				});
		});
	
	</script>

</body>
</html>