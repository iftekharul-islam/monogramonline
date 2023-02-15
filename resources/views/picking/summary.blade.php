<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Inventory Summary</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">

	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>

	<style>
		tr {
			font-size: 11px;
		}
		td th {
			table-layout: fixed;
			width: auto;
			white-space: nowrap;
		}
		tr.success{
				cursor:pointer;
		}
		
		.success .sign:after{
			content:"+";
			display:inline-block;      
		}
		
		.success.expand .sign:after{
			content:"-";
		 }

	</style>

</head>
<body>
	@include('includes.header_menu')
	<div class = "container">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li>Inventory Summary</li>
		</ol>

		@include('includes.error_div')
		@include('includes.success_div')

		<div class="row">
			<div class="col-sm-2">
				{!! Form::open([ 'url' => url('/picking/view'), 'id' => 'barcode_form' ]) !!}
				{!! Form::text('picking_report', null, ['id' => 'barcode', 'class' => "form-control", 'placeholder' => "Enter Summary"]) !!}
			</div>
			<div class="col-sm-1">
				{!! Form::submit('Find', ['id'=>'find', 'class' => 'btn btn-xs btn-success', 'style'=>'margin-top:5px;']) !!}
				{!! Form::close() !!}
			</div>
		</div>
		
		<br><br>
		
		<ul id="myTab" class="nav nav-tabs">
			<li class="active"><a href="#toPick" data-toggle="tab">Inventory Required for Active Batches</a></li>
			<li><a href="#pickingNow" data-toggle="tab">Summaries Being Picked ({!! count($picking) !!})</a></li>
			<li><a href="#pickedToday" data-toggle="tab">Summaries Picked Today ({!! count($picked) !!})</a></li>
		</ul>
		
		<div id="tabContent" class="tab-content">
			
			<div class="tab-pane fade in active" id="toPick">
					
					<br>
					
					@if(count($items_1) > 0 || count($items_2) > 0)
					
					<table id="stock_table" class="table table-hover" cellspacing="0" cellpadding="0">
						<thead>
						<tr>
							<th></th>
							<th>Station</th>
							<th>Stock #</th>
							<th>Description</th>
							<th>Bin</th>
							<th>Quantity</th>
						</tr>
					</thead>
			      <tbody>
								
						@setvar($current_section = '')
						
						@foreach($items_1 as $row)
							@if ($row->section_name != $current_section)
							
								<tr class="success expand">
									<td colspan="5">
										<strong>{{ $row['section_name'] }} &nbsp; <span class="sign"></span></strong>
									</td>
									
									<td align="right">
											{!! Form::open(['url' => url('/picking/report'), 'target' => '_blank', 'id' => 'inventory_summary', 'onsubmit' => 'javascript: setTimeout(reload, 3500);']) !!}
											{!! Form::hidden('section_id', $row->section_id , ['id' => 'section_id']) !!}
											{!! Form::hidden('section_name', $row->section_name , ['id' => 'section_name']) !!}
											{!! Form::submit('Print for ' . $row->section_name , ['id'=>'print', 'class' => 'btn btn-xs btn-success']) !!}
											{!! Form::close() !!}
									</td>
									
								</tr>
								@setvar($current_section = $row->section_name)
							@endif
							<tr>
								<td></td>
								
								<td>
									<span data-toggle = "tooltip" data-placement = "top"
												title = "{{ $row->production_station->station_name }} => {{ $row->production_station->station_description }}">
									{{ $row->production_station->station_name }}
									</span>
								</td>
								
								<td>
									@if ($row['stock_no_unique'] != 'ToBeAssigned')
										<a href="{{url(sprintf("/inventories?search_for_first=%s&search_in_first=stock_no_unique", $row['stock_no_unique']))}}">
											{{ $row['stock_no_unique'] }}
										</a>
									@else 
										{{ $row['stock_no_unique'] }}
									@endif
								</td>
								
								<td>{{ $row['stock_name_discription'] }}</td>
								<td>{{ $row['wh_bin'] }}</td>
								<td>{{ $row['total'] }}</td>
							</tr>
						@endforeach
						
						@setvar($current_batch = '')
						
						@foreach($items_2 as $row)
							@if ($row->section_name != $current_section)
							
								<tr class="success expand">
									<td colspan="6">
										<strong>{{ $row['section_name'] }} &nbsp; <span class="sign"></span></strong>
									</td>
									
								</tr>
								@setvar($current_section = $row->section_name)
							@endif
							
							@if ($current_batch != $row->batch_number)
								<tr>
									<td>
										<a href = "{{ url(sprintf('batches/details/%s',$row->batch_number)) }}">
											{{ $row->batch_number }}</a>
									</td>
									
									<td>
										@if ($row->store)
											{{ $row->store->store_name }}
										@endif
									</td>	
										
									<td colspan=3>
									</td>
									
									<td align="right">
											{!! Form::open(['url' => url('/picking/report'), 'target' => '_blank', 'id' => 'inventory_summary', 'onsubmit' => 'javascript: setTimeout(reload, 3500);']) !!}
											{!! Form::hidden('section_id', $row->section_id , ['id' => 'section_id']) !!}
											{!! Form::hidden('section_name', $row->section_name , ['id' => 'section_name']) !!}
											{!! Form::hidden('batch_number', $row->batch_number , ['id' => 'batch_number']) !!}
											{!! Form::submit('Print for ' . $row->batch_number , ['id'=>'print', 'class' => 'btn btn-xs btn-success']) !!}
											{!! Form::close() !!}
									</td>
								</tr>
								
								@setvar($current_batch = $row->batch_number)
							@endif
							
							<tr>
								<td colspan=2>
								</td>
								<td>
									@if ($row['stock_no_unique'] != 'ToBeAssigned')
										<a href="{{url(sprintf("/inventories?search_for_first=%s&search_in_first=stock_no_unique", $row['stock_no_unique']))}}">
											{{ $row['stock_no_unique'] }}
										</a>
									@else 
										{{ $row['stock_no_unique'] }}
									@endif
								</td>
								
								<td>{{ $row['stock_name_discription'] }}</td>
								<td>{{ $row['wh_bin'] }}</td>
								<td>{{ $row['total'] }}</td>
							</tr>

						@endforeach
						
			      </tbody>
			    </table>

					@else
							<div class = "alert alert-warning">All inventory summaries printed</div>
					@endif
					
			</div>
			
			<div class="tab-pane fade" id="pickingNow">
					<br>

					@if(count($picking) > 0)
							
					<table id="picking_table" style="width:800px;" class="table table-hover" cellspacing="0" cellpadding="0">
						<thead>
						<tr>
							<th></th>
							<th>Report #</th>
							<th>Date Printed</th>
							<th>Printed By</th>
							<th colspan=2></th>
						</tr>
					</thead>
						<tbody>
								
						@foreach($picking as $row)
							<tr>
								<td>
									@if ($row->picking_report->batch_number != NULL)
										{{ $row->batch_number }}
										@if ($row->store)
											<br>
											{{ $row->store->store_name }}
										@endif
									@else
										{{ $row->section->section_name }}
									@endif
								</td>
								<td>{{ $row->picking_report_id }}</td>
								<td>{{ $row->picking_report->picking_date }}</td>
								<td>{{ $row->picking_report->picking_user->username }}</td>
								<td>
										{!! Form::open(['url' => url('/picking/report'), 'id' => '', 'target' => '_blank']) !!}
										{!! Form::hidden('picking_report', $row->picking_report_id , ['id' => 'picking_report']) !!}
										{!! Form::hidden('section_id', $row->section_id , ['id' => 'section_id']) !!}
										{!! Form::hidden('section_name', $row->section->section_name , ['id' => 'section_name']) !!}
										{!! Form::hidden('batch_number', $row->batch_number , ['id' => 'batch_number']) !!}
										{!! Form::submit('Reprint' , ['id'=>'reprint', 'class' => 'btn btn-xs btn-primary']) !!}
										{!! Form::close() !!}
								</td>
								<td>
										{!! Form::open(['url' => url('/picking/view'), 'id' => '', 'target' => '_blank']) !!}
										{!! Form::hidden('task', 'view' , ['id' => 'task']) !!}
										{!! Form::hidden('picking_report', $row->picking_report_id , ['id' => 'picking_report']) !!}
										{!! Form::submit('View' , ['id'=>'view', 'class' => 'btn btn-xs btn-warning']) !!}
										{!! Form::close() !!}
								</td>
								<td>
										{!! Form::open(['url' => url('/picking/view'), 'id' => '']) !!}
										{!! Form::hidden('task', 'pick' , ['id' => 'task']) !!}
										{!! Form::hidden('picking_report', $row->picking_report_id , ['id' => 'picking_report']) !!}
										{!! Form::submit('Mark Inventory Picked' , ['id'=>'picked', 'class' => 'btn btn-xs btn-success']) !!}
										{!! Form::close() !!}
								</td>
								<td>
										{!! Form::open(['url' => url('/picking/delete'), 'id' => '', 'onSubmit' => 'confirm("Are you sure you want to delete?")']) !!}
										{!! Form::hidden('task', 'delete' , ['id' => 'task']) !!}
										{!! Form::hidden('picking_report', $row->picking_report_id , ['id' => 'picking_report']) !!}
										{!! Form::submit('Delete' , ['id'=>'delete', 'class' => 'btn btn-xs btn-danger']) !!}
										{!! Form::close() !!}
								</td>
							</tr>
						@endforeach
						
						</tbody>
					</table>
					@else
						<div class = "alert alert-warning">No inventory summaries being picked</div>
					@endif
					
			</div>
			
			<div class="tab-pane fade" id="pickedToday">
					
					<br>		
					@if(count($picked) > 0)
							
					<table id="picked_table" style="width:800px;" class="table table-hover" cellspacing="0" cellpadding="0">
						<thead>
						<tr>
							<th></th>
							<th>Summary</th>
							<th>Date Printed</th>
							<th>Printed By</th>
							<th></th>
						</tr>
					</thead>
						<tbody>
						
						@foreach($picked as $row)
							<tr>
								<td>
									@if ($row->picking_report->batch_number != NULL)
										{{ $row->batch_number }}
										@if ($row->store)
											<br>
											{{ $row->store->store_name }}
										@endif
									@else
										{{ $row->section->section_name }}
									@endif
								</td>
								<td>{{ $row->picking_report_id }}</td>
								<td>{{ $row->picking_report->picking_date }}</td>
								<td>{{ $row->picking_report->picking_user->username }}</td>
								<td>
									{!! Form::open(['url' => url('/picking/view'), 'id' => '', 'target' => '_blank', 'onsubmit' => 'javascript: setTimeout(reload, 3500);return true;']) !!}
									{!! Form::hidden('task', 'view' , ['id' => 'task']) !!}
									{!! Form::hidden('picking_report', $row->picking_report_id , ['id' => 'picking_report']) !!}
									{!! Form::submit('View' , ['id'=>'view', 'class' => 'btn btn-xs btn-warning']) !!}
									{!! Form::close() !!}
								</td>
							</tr>
						@endforeach
						
						</tbody>
					</table>
					@else
						<div class = "alert alert-warning">No inventory summaries picked today</div>
					@endif
			</div>
				
		</div>
	</div>
	
	<script type = "text/javascript">
	
	$(function() {
				
				$('[data-toggle="tooltip"]').tooltip();
				
	     // Focus on load
	     $('#barcode').focus();

	});
	
	$('.success').click(function(){
		$(this).toggleClass('expand').nextUntil('tr.success').slideToggle(100);
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

	function reload ()
	{
		location.reload();
	}
	
	</script>

</body>
</html>