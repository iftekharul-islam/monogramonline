<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Inventory Picking Report</title>
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
	</style>

</head>
<body>
	@include('includes.header_menu')
	<div class = "container">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "/picking/summary">Inventory Summary</a></li>
			<li>Inventory Picking Report</li>
		</ol>

		@include('includes.error_div')
		@include('includes.success_div')

		<div class="row">
			<div class="col-sm-2">
				{!! Form::open([ 'url' => url('/picking/view') ]) !!}
				{!! Form::text('picking_report', null, ['id' => 'scanner', 'class' => "form-control", 'placeholder' => "Find Picking Summary"]) !!}
			</div>
			<div class="col-sm-1">
				{!! Form::submit('Find Summary', ['id'=>'find', 'class' => 'btn btn-xs btn-success','style'=>'margin-top:5px;']) !!}
				{!! Form::close() !!}
			</div>
		</div>
		
		@if(count($item) > 0)
		
		<h4 class = "page-header">{{ $section_name }} {{ $batch_number }} Picking Summary Report</h4>
		
		<div class="row">
			<div class="col-sm-2">
				Report Number: {{ $picking_report }}
			</div>
			<div class="col-sm-3">
				Created: {{ $picking_date }} 
				<br>
				@if (isset($picked_date))
					Picked: {{ $picked_date }} 
				@endif
			</div>
			<div class="col-sm-2">
				Created By: {{ $picking_user }}
				<br>
				@if (isset($picked_date))
					Picked By: {{ $picked_user }} 
				@endif
			</div>
		</div>
		
				
		@if ($task == 'pick')
			{!! Form::open(['url' => url('/picking/pick'), 'id' => 'inventory_picked', 'onsubmit' => 'javascript: setTimeout(function(){location.reload();}, 3500);return true;']) !!}
			{!! Form::hidden('picking_report', $picking_report , ['id' => 'picking_report']) !!}
		@endif
		<br><br>
		<table id="stock_table" class="table table-hover">
			<thead>
			<tr>
				<th></th>
				<th>Station</th>
				<th>Stock #</th>
				<th>Description</th>
				<th>Bin</th>
				<th>Quantity Required</th>
				@if ($task == 'pick')
					<th>Quantity Picked</th>
				@endif
			</tr>
		</thead>
      <tbody>
					
			@foreach($item as $row)
				@if ($row->stock_no_unique != 'ToBeAssigned')
				<tr>
					<td></td>
					<td>
						<span data-toggle = "tooltip" data-placement = "top"
									title = "{{ $row->production_station->station_name }} => {{ $row->production_station->station_description }}">
						{{ $row->production_station->station_name }}
						</span>
					</td>
					
					<td>
						<a href="{{url(sprintf("/inventories?search_for_first=%s&search_in_first=stock_no_unique", $row->stock_no_unique))}}">
							{{ $row->stock_no_unique }}
						</a>
					</td>
					
					<td>{{ $row->stock_name_discription }}</td>
					<td>{{ $row->wh_bin }}</td>
					<td style="text-align:center;">{{ $row->total }}</td>
					@if ($task == 'pick')
						<td>
							{!! Form::hidden('key[]', $row->stock_no_unique . '*^*' . $row->production_station->station_name , ['id' => 'key']) !!}
							{!! Form::hidden($row->stock_no_unique . '*^*' . $row->production_station->station_name . '*required', $row->total , ['id' => 'qty']) !!}
							{!! Form::text($row->stock_no_unique . '*^*' . $row->production_station->station_name . '*picked', $row->total, ['id' => 'input-xsmall', 'class' => "input-xsmall"]) !!}
						</td>
					@endif
				</tr>
				@endif
			@endforeach

			@foreach($unassigned as $row)
				<tr>
					<td></td>
					<td>
						<span data-toggle = "tooltip" data-placement = "top"
									title = "{{ $row->production_station->station_name }} => {{ $row->production_station->station_description }}">
						{{ $row->production_station->station_name }}
						</span>
					</td>
					<td>{{ $row->stock_no_unique }}</td>
					<td colspan="2">{{ $row->item_code }} {{ $row->item_description }}</td>
					
					<td style="text-align:center;">{{ $row->total }}</td>
					@if ($task == 'pick')
						<td>
							{!! Form::hidden('key[]', $row->stock_no_unique . '*^*' . $row->production_station->station_name , ['id' => 'key']) !!}
							{!! Form::hidden($row->stock_no_unique . '*^*' . $row->production_station->station_name . '*required', $row->total , ['id' => 'qty']) !!}
							{!! Form::text($row->stock_no_unique . '*^*' . $row->production_station->station_name . '*picked', $row->total, ['id' => 'input-xsmall', 'class' => "input-xsmall"]) !!}
						</td>
					@endif
				</tr>
			@endforeach
			
			<tr>
				<td colspan="6"></td>
				<td>
					@if ($task == 'pick')
						{!! Form::submit($row->section_name . ' Inventory Picked', ['id'=>'pick', 'class' => 'btn btn-xs btn-success']) !!}
					@endif
				</td>
			</tr>
			
      </tbody>
    </table>
		@if ($task == 'pick')
			{!! Form::close() !!}
		@endif
		
		@else
			<div class = "col-xs-12">
				<div class = "alert alert-warning text-center">
					<h3>No Picking Report</h3>
				</div>
			</div>
		@endif
		
		
	</div>

	<script type = "text/javascript">
	
	$(function() {
				
				$('[data-toggle="tooltip"]').tooltip();
				
			 // Focus on load
			 $('#scanner').focus();
	});

	</script>

</body>
</html>