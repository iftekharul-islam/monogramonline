<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Order Items Report</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/pikaday.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/moment.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/pikaday.min.js"></script>

	<style>
		tr {
			font-size: 12px;
		}
		tr.toplabel {
			text-align: center;
			background-color: #f2f2f2;
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
		.right {
			text-align: right;
		}
		.data {
			border-left: 1px solid #ddd;
			text-align: right;
		}
		.databorder {
			border-left: 3px solid #ddd;
			text-align: right;
		}
		.total {
			border-left: 1px solid #ddd;
			text-align: right;
			font-weight: bold;
		}
		.totalborder {
			border-left: 3px solid #ddd;
			text-align: right;
			font-weight: bold;
		}
		.data_late {
			border-left: 1px solid #ddd;
			text-align: right;
		}
		.data_late a:link {
			color: #FF0000;
		}
		th.rotate {
		  /* Something you can count on */
		  height: 100px;
		  white-space: nowrap;
		}

		th.rotate > div {
		  transform:
		    rotate(330deg);
		  width: 30px;
		}
		th.rotate > div > span {
		  border-bottom: 1px solid #ccc;
		  padding: 5px 10px;
		}
	</style>

</head>
<body>
	@include('includes.header_menu')
	<div class = "container" style="min-width: 800px;">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li>Order Items Report</li>
		</ol>

		<h3 class = "page-header">Order Items</h3>
		
		<div class = "col-xs-12">
			{!! Form::open(['method' => 'get']) !!}
				<div class = "form-group col-xs-3">
					<label>Store:</label>
					{!! Form::select('store_filter', $store_list , $store_filter, ['id'=>'store_filter', 'class' => 'form-control']) !!}
				</div>
				<div class = "form-group col-xs-3">
					<label>Group By:</label>
					{!! Form::select('grouping', $group_list , $grouping, ['id'=>'grouping', 'class' => 'form-control']) !!}
				</div>
				<div class = "form-group col-xs-2">
					<label for = "start_date">Start Order date</label>
					<div class = 'input-group date' id = 'start_date_picker'>
						{!! Form::text('start_date', $start_date, ['id'=>'start_datepicker', 'class' => 'form-control', 'placeholder' => 'Enter start date', 'autocomplete' => 'off']) !!}
						<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
					</div>
				</div>
				<div class = "form-group col-xs-2">
					<label for = "end_date">End Order date</label>
					<div class = 'input-group date' id = 'end_date_picker'>
						{!! Form::text('end_date', $end_date, ['id'=>'end_datepicker', 'class' => 'form-control', 'placeholder' => 'Enter end date', 'autocomplete' => 'off']) !!}
						<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
					</div>
				</div>
				<div class = "form-group col-xs-2">
					<label for = "" class = ""></label>
					{!! Form::submit('Search', ['id'=>'search', 'style' => 'margin-top: 5px;', 'class' => 'btn btn-primary btn-sm form-control']) !!}
				</div>
			{!! Form::close() !!}
		</div>		
		
		@setvar($avg_sum = 0)
		
		@if (count($ordered_today) > 0)
			<table class="table" cellspacing="0" cellpadding="0">
				<thead>
					<tr class="success">
						<th colspan=3>
							@if (isset($group_list[$grouping]))
								{{ $group_list[$grouping] }}
							@endif
						</th>
						@foreach ($stores as $store)
							<th class="rotate" width="100"><div><span>@if($store) {{ $store->store_name }} @endif</span></div></th>
						@endforeach
						<th width="200" colspan=2>Total</th>
					</tr>
				</thead>
				<tbody>
					
					@foreach ($groups as $group)
						
						@setvar($lines = $ordered_today->where('col1', $group)->all())
						@setvar($total = 0)
						
						<tr class="lines" height="70">
							<td>
								{{ $group }}
							</td>
							<td>
								@if ($lines[key($lines)]['img'])
									<img  border = "0" style="height: auto; width: 2cm;" src = "{{ $lines[key($lines)]['img'] }}" />
								@endif
							</td>
							<td>{{ $lines[key($lines)]['col2'] ? $lines[key($lines)]['col2'] : '' }}</td>
							@foreach ($stores as $store)
								<td class="data" width="100">
								@foreach ($lines as $line)
											@if ($store->store_id == $line['store_id'])
												{!! number_format($line['sum']) !!}
												@setvar($total += $line['sum'])
												@setvar($store_totals[$line['store_id']] += $line['sum'])
											@endif
								@endforeach
								</td>
							@endforeach
							<td class="total" width="100">
								{{ number_format($total) }}
							</td>
							<td class="total" width="100">
								{{ sprintf("%4.2f", $total / $grand_total * 100) }}%
							</td>
						</tr>
					@endforeach
				</tbody>
				
				<tfoot>
					<tr class="success">
						<td colspan="{{ count($stores) + 5 }}"></td>
					</tr>
					<tr class="total_footer" height="70">
						<td colspan=2></td>
						<td>Totals</td>
						@foreach ($stores as $store)
							<td class="data" width="100">
								{{ number_format($store_totals[$store->store_id]) }}
								<br><br>
								{{ sprintf("%4.2f", $store_totals[$store->store_id] / $grand_total * 100) }}%
							</td>
						@endforeach
						<td class="total" width="100">{{ number_format($grand_total) }}</td>
						<td class="total" width="100"></td>
					</tr>
					<tr class="success">
						<td colspan="{{ count($stores) + 5 }}"></td>
					</tr>
				</tfoot>
				
			</table>
		@endif
	</div>
	
	<br>
	
<script>
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