<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Section Report</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap-multiselect.css">
	<link type = "text/css" rel="stylesheet" href="/assets/css/chosen.min.css">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/pikaday.min.css">

	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap-multiselect.js"></script>	
	<script type = "text/javascript" src = "/assets/js/chosen.jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/moment.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/pikaday.min.js"></script>

	<style>
		tr {
			font-size: 11px;
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
		.remove_button_css { 
		  outline: none;
		  padding: 0px; 
		  border: 0px; 
		  box-sizing: none; 
		  background-color: transparent;
			color: #428bca;
		}
	</style>

</head>
<body>
	@include('includes.header_menu')
	<div class = "container">
		@if (auth()->user())
			<ol class = "breadcrumb">
				<li><a href = "{{url('/')}}">Home</a></li>
				<li>Section Report</li>
			</ol>
		@endif
			
		<h3 class = "page-header">Section Report 
			@if (auth()->user())
				<div class="pull-right"><small><a href="{{ url('/report/history') }}">History</a></small></div>
			@endif
		</h3>
			
		@if (auth()->user())
			<div class = "col-xs-12">
			{!! Form::open(['name' => 'store_form', 'method' => 'get', 'id' => 'store_form']) !!} 
				<div class = "form-group col-xs-1">
					<label>Before:</label>
				</div>
				<div class = "form-group col-xs-2">
					<div class = 'input-group date' id = 'max_date_picker'>
						 {!! Form::text('max_date', $max_date, ['id'=>'max_datepicker', 'class' => 'form-control', 'placeholder' => 'Enter date', 'autocomplete' => 'off']) !!}
					 <span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
			 		</div>
				</div>
				<div class = "form-group col-xs-1">
					<label>Type:</label>
				</div>
				<div class = "form-group col-xs-2">
					{!! Form::select('batch_type', ['' => '', 'B%' => 'Back order', 'R%' => 'Reject', 'X%' => 'Redo'], $batch_type, ['id'=>'batch_type', 'class' => 'form-control']) !!}
				</div>

				<div class = "form-group col-xs-1">
					<label>Store:</label>
				</div>
				 <div class = "form-group col-xs-2">
					 {!! Form::select('store_ids[]', $stores, $store_ids, ['id'=>'store_ids', 'multiple' => 'multiple', 'class' => 'form-control']) !!}
				 </div>

				<div class = "form-group col-xs-2">
					{!! Form::label('Company', 'Company:') !!}
					{!! Form::select('company', $companies, '', ['class' => 'form-control']) !!}
				</div>

				<div class = "form-group col-xs-1">
 					 {!! Form::submit('Filter', ['id' => 'store_button', 'class' => 'btn btn-primary btn-sm form-control']) !!}
 				 </div>
			{!! Form::close() !!}
		 	</div>
		@endif
															
		<table id="summary_table" class="table" cellspacing="0" cellpadding="0">
			<thead>
			<tr class="toplabel">
				<td colspan="2" align="left">{{ $now }}</td>
				<td colspan="2">Totals</td>
				<td colspan="3">Order Date Aging</td>
				<td colspan="3">Scan Date Aging</td>
			</tr>
			<tr>
				<th width="10%">Station</th>
				<th width="25%">Description</th>
				<th width="8%" class="right">lines</th>
				<th width="8%" class="right">Qty</th>
				<th width="8%" class="right">0-3</th>
				<th width="8%" class="right">4-7</th>
				<th width="8%" class="right">7+</th>
				<th width="8%" class="right">0-3</th>
				<th width="8%" class="right">4-7</th>
				<th width="8%" class="right">7+</th>
			</tr>
		</thead>
        <tbody>

			@if (count($CS) > 0 || count($CS_rejects) > 0)
				<tr class="success">
					<td colspan=9>Customer Service</td>
					<td align="right">{{ sprintf("%4.2f", ($CS_rejects->sum('items_count') + $CS->sum('items_count')) / $total * 100) }}%</td>
				</tr>
				
				@foreach($CS as $service)
				<tr class="lines">
					<td colspan=2>{{ $order_statuses[$service->order_status] }} ({{ $service->orders_count }} orders)
					<td class="data"><a href="/customer_service/index?tab={{ $order_statuses[$service->order_status] }}">{{ number_format($service->lines_count) }}</a></td>
					<td class="data">{{ number_format($service->items_count) }}</td>
					<td class="databorder">{{ number_format($service->order_1) }}</td>
					<td class="databorder">{{ number_format($service->order_2) }}</td>
					<td class="databorder">{{ number_format($service->order_3) }}</td>			
					<td class="databorder" colspan="3"></td>
				</tr>
				@endforeach

				@if (count($items) > 0)
					@foreach($items as $summary)

							@if(in_array($summary->station_name, ["S-Wait_for_Pic", "S-GGR-INDIA"]))

							@setvar($section = $summary->section_id)
							@setvar($section_name = $summary->section_name)
							<tr class="lines">
								<td>
									<a href = "{!! url(sprintf("/production/status_detail?station=%s", $summary->station_id)) !!}" target = "_blank">{{ $summary->station_name }}</a>
								</td>
								<td> </td>



								<td class="data">
									{!! Form::open(['method' => 'post', 'url' => '/move_next', 'target' => '_blank']) !!}
									{!! Form::hidden('station',  $summary['station_id']) !!}
									{!! Form::submit(number_format($summary->lines_count), ['class' => 'remove_button_css']) !!}
									{!! Form::close() !!}
								</td>
								<td class="data">{{ number_format($summary->items_count) }}</td>
								<td class="databorder">

									@if(in_array($summary->station_name, ["S-Wait_for_Pic"]))
									@setvar($first = [])
									@setvar($first[1] = number_format($summary->lines_count))
									@setvar($first[2] = number_format($summary->items_count))
									@setvar($first[3] = number_format($summary->order_1))
									@setvar($first[4] = number_format($summary->order_2))
									@setvar($first[5] = number_format($summary->order_3))
									@else
										@setvar($second = [])
										@setvar($second[1] = number_format($summary->lines_count))
										@setvar($second[2] = number_format($summary->items_count))
										@setvar($second[3] = number_format($summary->order_1))
										@setvar($second[4] = number_format($summary->order_2))
										@setvar($second[5] = number_format($summary->order_3))
										@endif

									@if ($summary->order_1 > 0)
										<a href="{!! url(sprintf("/items?search_for_first=%s&search_in_first=station_name&search_for_second=2&search_in_second=batch_status&start_date=%s&end_date=%s&store=%s&status=1",
															$summary->station_name, $date[1], $date[0], $store_link)) !!}" target="_blank">{{ number_format($summary->order_1) }}</a>
									@else
										{{ $summary->order_1 }}
									@endif
								</td>
								<td class="databorder">
									@if ($summary->order_2 > 0)
										<a href="{!! url(sprintf("/items?search_for_first=%s&search_in_first=station_name&search_for_second=2&search_in_second=batch_status&start_date=%s&end_date=%s&store=%s&status=1",
														$summary->station_name, $date[3], $date[2], $store_link)) !!}" target="_blank">{{ number_format($summary->order_2) }}</a>
									@else
										{{ $summary->order_2 }}
									@endif
								</td>
								<td class="databorder">
									@if ($summary->order_3 > 0)
										<a href="{!! url(sprintf("/items?search_for_first=%s&search_in_first=station_name&search_for_second=2&search_in_second=batch_status&start_date=%s&end_date=%s&store=%s&status=1",
														$summary->station_name, $summary->earliest_order_date, $date[4], $store_link)) !!}" target="_blank">{{ number_format($summary->order_3) }}</a>
									@else
										{{ $summary->order_3 }}
									@endif
								</td>

							</tr>
						@endif
					@endforeach
				@endif


				@foreach($CS_rejects as $service)
				<tr class="lines">
					<td colspan=2>{{ $graphic_statuses[$service->graphic_status] }}
					<td class="data"><a href="/rejections?graphic_status={{ $service->graphic_status }}">{{ number_format($service->lines_count) }}</a></td>
					<td class="data">{{ number_format($service->items_count) }}</td>
					<td class="databorder">{{ number_format($service->order_1) }}</td>
					<td class="databorder">{{ number_format($service->order_2) }}</td>
					<td class="databorder">{{ number_format($service->order_3) }}</td>			
					<td class="databorder" colspan="3"></td>
				</tr>
				@endforeach

				<tr>
					<td></td>
					<td align="right">Customer Service SubTotals:</td>
					@if(isset($second))
						<td class="total">{!! number_format($CS->sum('lines_count') + $CS_rejects->sum('lines_count')) + $first[1] + $second[1] !!}</td>
						<td class="total">{!! number_format($CS->sum('items_count') + $CS_rejects->sum('items_count')) + $first[2] + $second[2] !!}</td>
						<td class="totalborder">{!! number_format($CS->sum('order_1') + $CS_rejects->sum('order_1')) + $first[3] + $second[3] !!}</td>
						<td class="total">{!! number_format($CS->sum('order_2') + $CS_rejects->sum('order_2')) + $first[4] + $second[4] !!}</td>
						<td class="total">{!! number_format($CS->sum('order_3') + $CS_rejects->sum('order_3')) + $first[5] + $second[5] !!}</td>
					@else
						@if(isset($first))
						<td class="total">{!! number_format($CS->sum('lines_count') + $CS_rejects->sum('lines_count')) + $first[1]!!}</td>
						<td class="total">{!! number_format($CS->sum('items_count') + $CS_rejects->sum('items_count')) + $first[2]!!}</td>
						<td class="totalborder">{!! number_format($CS->sum('order_1') + $CS_rejects->sum('order_1')) + $first[3]!!}</td>
						<td class="total">{!! number_format($CS->sum('order_2') + $CS_rejects->sum('order_2')) + $first[4]!!}</td>
						<td class="total">{!! number_format($CS->sum('order_3') + $CS_rejects->sum('order_3')) + $first[5] !!}</td>
					@endif
					@endif
					<td class="databorder" colspan="3"></td>
				</tr>
			@endif
			
			@if (count($backorders) > 0)
				<tr class="success">
					<td colspan=9>Back Orders</td>
					<td align="right">{{ sprintf("%4.2f", $backorders->sum('items_count') / $total * 100) }}%</td>
				</tr>
				
				@foreach($backorders as $backorder)
				<tr class="lines">
					<td></td>
					<td>{{ $backorder->section_name }}
					<td class="data">{{ number_format($backorder->lines_count) }}</td>
					<td class="data">{{ number_format($backorder->items_count) }}</td>
					<td class="databorder">{{ number_format($backorder->order_1) }}</td>
					<td class="databorder">{{ number_format($backorder->order_2) }}</td>
					<td class="databorder">{{ number_format($backorder->order_3) }}</td>		
					<td class="databorder">{{ number_format($backorder->scan_1) }}</td>
					<td class="databorder">{{ number_format($backorder->scan_2) }}</td>
					<td class="databorder">{{ number_format($backorder->scan_3) }}</td>	
				</tr>
				@endforeach
				
				<tr>
					<td></td>
					<td align="right">Backorder SubTotals:</td>
					<td class="total">{!! number_format($backorders->sum('lines_count')) !!}</td>
					<td class="total">{!! number_format($backorders->sum('items_count')) !!}</td>
					<td class="totalborder">{!! number_format($backorders->sum('order_1')) !!}</td>
					<td class="total">{!! number_format($backorders->sum('order_2')) !!}</td>
					<td class="total">{!! number_format($backorders->sum('order_3')) !!}</td>
					<td class="totalborder">{!! number_format($backorders->sum('scan_1')) !!}</td>
					<td class="total">{!! number_format($backorders->sum('scan_2')) !!}</td>
					<td class="total">{!! number_format($backorders->sum('scan_3')) !!}</td>
				</tr>
			@endif
			
			@if (count($rejects) > 0)
				<tr class="success">
					<td colspan=9>Rejects</td>
					<td align="right">{{ sprintf("%4.2f", $rejects->sum('items_count') / $total * 100) }}%</td>
				</tr>
				
				@foreach($rejects as $reject)
				<tr class="lines">
					<td>{{ $reject->section_name }}
					<td>{{ $graphic_statuses[$reject->graphic_status] }}
					<td class="data"><a href="/rejections?graphic_status={{ $reject->graphic_status }}&section={{ $reject->section_id }}">{{ number_format($reject->lines_count) }}</a></td>
					<td class="data">{{ number_format($reject->items_count) }}</td>
					<td class="databorder">{{ number_format($reject->order_1) }}</td>
					<td class="databorder">{{ number_format($reject->order_2) }}</td>
					<td class="databorder">{{ number_format($reject->order_3) }}</td>			
					<td class="databorder">{{ number_format($reject->scan_1) }}</td>
					<td class="databorder">{{ number_format($reject->scan_2) }}</td>
					<td class="databorder">{{ number_format($reject->scan_3) }}</td>		
				</tr>
				@endforeach
				
				<tr>
					<td></td>
					<td align="right">Reject SubTotals:</td>
					<td class="total">{!! number_format($rejects->sum('lines_count')) !!}</td>
					<td class="total">{!! number_format($rejects->sum('items_count')) !!}</td>
					<td class="totalborder">{!! number_format($rejects->sum('order_1')) !!}</td>
					<td class="total">{!! number_format($rejects->sum('order_2')) !!}</td>
					<td class="total">{!! number_format($rejects->sum('order_3')) !!}</td>
					<td class="totalborder">{!! number_format($rejects->sum('scan_1')) !!}</td>
					<td class="total">{!! number_format($rejects->sum('scan_2')) !!}</td>
					<td class="total">{!! number_format($rejects->sum('scan_3')) !!}</td>
				</tr>
			@endif
			
		@if (count($items) > 0)
			@foreach($items as $summary)
				@if ($section != $summary->section_id)
					@if ($section != 'start')
					<tr>
						<td></td>
						<td align="right">{{ $section_name }} SubTotals: </td>
						<td class="total">{!! number_format($items->where('section_id', $section)->sum('lines_count')) !!}</td>
						<td class="total">{!! number_format($items->where('section_id', $section)->sum('items_count')) !!}</td>
						<td class="totalborder">{!! number_format($items->where('section_id', $section)->sum('order_1')) !!}</td>
						<td class="total">{!! number_format($items->where('section_id', $section)->sum('order_2')) !!}</td>
						<td class="total">{!! number_format($items->where('section_id', $section)->sum('order_3')) !!}</td>
						<td class="totalborder">{!! number_format($items->where('section_id', $section)->sum('scan_1')) !!}</td>
						<td class="total">{!! number_format($items->where('section_id', $section)->sum('scan_2')) !!}</td>
						<td class="total">{!! number_format($items->where('section_id', $section)->sum('scan_3')) !!}</td>
					</tr>
					@endif

					@setvar($section = $summary->section_id)
					@setvar($section_name = $summary->section_name)
					
					<tr class="success">
						@if ($summary->section_id == '0')
							<td colspan="9">Unassigned</td>
						@else
							<td colspan="9">{{ $summary->section_name }}</td>
						@endif
						<td align="right">{{ sprintf("%4.2f", $items->where('section_id', $section)->sum('items_count') / $total * 100) }}%</td>
					</tr>
				@endif
				@if(!in_array($summary->station_name, ["S-Wait_for_Pic", "S-GGR-INDIA"]))
				<tr class="lines">
					<td>
						<a href = "{!! url(sprintf("/production/status_detail?station=%s", $summary->station_id)) !!}" target = "_blank">{{ $summary->station_name }}</a>
					</td>
					<td>
						{{ $summary->station_description }}
					</td>
					<td class="data">
						{!! Form::open(['method' => 'post', 'url' => '/move_next', 'target' => '_blank']) !!}
						{!! Form::hidden('station',  $summary['station_id']) !!}
						{!! Form::submit(number_format($summary->lines_count), ['class' => 'remove_button_css']) !!}
						{!! Form::close() !!}
					</td>
					<td class="data">{{ number_format($summary->items_count) }}</td>
					<td class="databorder">
							@if ($summary->order_1 > 0)
								<a href="{!! url(sprintf("/items?search_for_first=%s&search_in_first=station_name&search_for_second=2&search_in_second=batch_status&start_date=%s&end_date=%s&store=%s&status=1", 
															$summary->station_name, $date[1], $date[0], $store_link)) !!}" target="_blank">{{ number_format($summary->order_1) }}</a>
							@else 
								{{ $summary->order_1 }}
							@endif
					</td>
					<td class="data">
						@if ($summary->order_2 > 0)
							<a href="{!! url(sprintf("/items?search_for_first=%s&search_in_first=station_name&search_for_second=2&search_in_second=batch_status&start_date=%s&end_date=%s&store=%s&status=1", 
														$summary->station_name, $date[3], $date[2], $store_link)) !!}" target="_blank">{{ number_format($summary->order_2) }}</a>
						@else 
							{{ $summary->order_2 }}
						@endif
					</td>
					<td class="data">
						@if ($summary->order_3 > 0)
							<a href="{!! url(sprintf("/items?search_for_first=%s&search_in_first=station_name&search_for_second=2&search_in_second=batch_status&start_date=%s&end_date=%s&store=%s&status=1", 
														$summary->station_name, $summary->earliest_order_date, $date[4], $store_link)) !!}" target="_blank">{{ number_format($summary->order_3) }}</a>
						@else 
							{{ $summary->order_3 }}
						@endif
					</td>
								
					<td class="databorder">
						@if ($summary->scan_1 > 0)
							<a href="{!! url(sprintf("/items?search_for_first=%s&search_in_first=station_name&search_for_second=2&search_in_second=batch_status&scan_start_date=%s&scan_end_date=%s&store=%s&status=1", 
														$summary['station_name'], $date[1], $date[0], $store_link)) !!}" target = "_blank">{{ number_format($summary->scan_1) }}</a>
						@else 
								{{ $summary->scan_1 }}
						@endif
					</td>
					<td class="data">
						@if ($summary->scan_2 > 0)
							<a href="{!! url(sprintf("/items?search_for_first=%s&search_in_first=station_name&search_for_second=2&search_in_second=batch_status&scan_start_date=%s&scan_end_date=%s&store=%s&status=1", 
														$summary['station_name'], $date[3], $date[2], $store_link)) !!}" target = "_blank">{{ number_format($summary->scan_2) }}</a>
						@else 
								{{ $summary->scan_2 }}
						@endif
					</td>
					<td class="data">
						@if ($summary->scan_3 > 0)
							<a href="{!! url(sprintf("/items?search_for_first=%s&search_in_first=station_name&search_for_second=2&search_in_second=batch_status&scan_start_date=%s&scan_end_date=%s&store=%s&status=1", 
														$summary['station_name'], $summary->earliest_scan_date, $date[4], $store_link)) !!}" target = "_blank">{{ number_format($summary->scan_3) }}</a>
						@else 
								{{ $summary->scan_3 }}
						@endif
					</td>
				</tr>
				@endif
			@endforeach
			
				<tr>
					<td></td>
					@if ($section == '0')
						<td align="right">Unassigned SubTotals:</td>
					@else
						<td align="right">{{ $section_name }} SubTotals: ------</td>
					@endif
					<td class="total">{!! number_format($items->where('section_id', $section)->sum('lines_count')) !!}</td>
					<td class="total">{!! number_format($items->where('section_id', $section)->sum('items_count')) !!}</td>
					<td class="totalborder">{!! number_format($items->where('section_id', $section)->sum('order_1')) !!}</td>
					<td class="total">{!! number_format($items->where('section_id', $section)->sum('order_2')) !!}</td>
					<td class="total">{!! number_format($items->where('section_id', $section)->sum('order_3')) !!}</td>
					<td class="totalborder">{!! number_format($items->where('section_id', $section)->sum('scan_1')) !!}</td>
					<td class="total">{!! number_format($items->where('section_id', $section)->sum('scan_2')) !!}</td>
					<td class="total">{!! number_format($items->where('section_id', $section)->sum('scan_3')) !!}</td>
				</tr>

				</tbody>
				
				<tfoot>
					<tr class="success">
						<td colspan=10 height=30></td>
					</tr>
					<tr class="total_footer">
						<td></td>
						<td align="right"><strong>Production SubTotals:</strong></td>
						<td class="total">{!! number_format($items->sum('lines_count') +  $rejects->sum('lines_count')) !!}</td>
						<td class="total">{!! number_format($items->sum('items_count') +  $rejects->sum('items_count')) !!}</td>
						<td class="totalborder">{!! number_format($items->sum('order_1') +  $rejects->sum('order_1')) !!}</td>
						<td class="total">{!! number_format($items->sum('order_2') +  $rejects->sum('order_2')) !!}</td>
						<td class="total">{!! number_format($items->sum('order_3') +  $rejects->sum('order_3'))  !!}</td>
						<td class="totalborder">{!! number_format($items->sum('scan_1') +  $rejects->sum('scan_1')) !!}</td>
						<td class="total">{!! number_format($items->sum('scan_2') +  $rejects->sum('scan_2')) !!}</td>
						<td class="total">{!! number_format($items->sum('scan_3') +  $rejects->sum('scan_3')) !!}</td>
					</tr>
		@endif

		@if (count($unbatched) > 0)
					<tr class="total_footer">
								<td></td>
								<td align="right">Unbatched:</td>
								<td class="data">{{ number_format($unbatched->lines_count) }}</td>
								<td class="data">{{ number_format($unbatched->items_count) }}</td>
								<td class="databorder">
									@if ($unbatched->order_1 > 0)
										<a href="{!! url(sprintf("/items?start_date=%s&end_date=%s&unbatched=1&store=%s&status=0", $date[1], $date[0], $store_link)) !!}" 
															target = "_blank">{{ number_format($unbatched->order_1) }}</a>
									@else 
										{{ $unbatched->order_1 }}
									@endif
								</td>
								<td class="data">
									@if ($unbatched->order_2 > 0)
										<a href="{!! url(sprintf("/items?start_date=%s&end_date=%s&unbatched=1&store=%s&status=0", $date[3], $date[2], $store_link)) !!}" 
															target = "_blank">{{ number_format($unbatched->order_2) }}</a>
									@else 
										{{ $unbatched->order_2 }}
									@endif
								</td>
								<td class="data">
									@if ($unbatched->order_3 > 0)
										<a href="{!! url(sprintf("/items?start_date=%s&end_date=%s&unbatched=1&store=%s&status=0", $unbatched->earliest_order_date, $date[4], $store_link)) !!}" 
															target = "_blank">{{ number_format($unbatched->order_3) }}</a>
									@else 
										{{ $unbatched->order_3 }}
									@endif
								</td>
								<td class="databorder" colspan="3"></td>
					</tr>
		@endif
		
		@if (count($items) > 0)
					<tr class="total_footer">
						<td></td>
						<td align="right"><strong>Production Totals:</strong></td>
						<td class="total">{!! number_format($items->sum('lines_count') +  $rejects->sum('lines_count') + $unbatched->lines_count) !!}</td>
						<td class="total">{!! number_format($items->sum('items_count') +  $rejects->sum('items_count') + $unbatched->items_count) !!}</td>
						<td class="totalborder">{!! number_format($items->sum('order_1') +  $rejects->sum('order_1') + $unbatched->order_1) !!}</td>
						<td class="total">{!! number_format($items->sum('order_2') +  $rejects->sum('order_2') + $unbatched->order_2) !!}</td>
						<td class="total">{!! number_format($items->sum('order_3') +  $rejects->sum('order_3') + $unbatched->order_3)  !!}</td>
						<td class="totalborder">{!! number_format($items->sum('scan_1') +  $rejects->sum('scan_1')) !!}</td>
						<td class="total">{!! number_format($items->sum('scan_2') +  $rejects->sum('scan_2')) !!}</td>
						<td class="total">{!! number_format($items->sum('scan_3') +  $rejects->sum('scan_3')) !!}</td>
					</tr>
		@endif
		
					@if (count($qc) > 0)
						<tr class="success">
							<td colspan=9>Quality Control</td>
							<td align="right">{{ sprintf("%4.2f", $qc->sum('items_count') / $total * 100) }}%</td>
						</tr>
						
						@foreach($qc as $station)
						<tr class="lines">
							<td>
								<a href = "{!! url(sprintf("/production/status_detail?station=%s", $station->station_id)) !!}" target = "_blank">{{ $station->station_name }}</a>
							</td>
							<td>{{ $station->station_description }}</td>
							<td class="data">{{ number_format($station->lines_count) }}</td>
							<td class="data">{{ number_format($station->items_count) }}</td>
							<td class="databorder">
									@if ($station->order_1 > 0)
										<a href="{!! url(sprintf("/items?search_for_first=%s&search_in_first=station_name&search_for_second=2&search_in_second=batch_status&start_date=%s&end_date=%s&store=%s&status=1", 
																	$station->station_name, $date[1], $date[0], $store_link)) !!}" target="_blank">{{ number_format($station->order_1) }}</a>
									@else 
										{{ $station->order_1 }}
									@endif
							</td>
							<td class="data">
								@if ($station->order_2 > 0)
									<a href="{!! url(sprintf("/items?search_for_first=%s&search_in_first=station_name&search_for_second=2&search_in_second=batch_status&start_date=%s&end_date=%s&store=%s&status=1", 
																$station->station_name, $date[3], $date[2], $store_link)) !!}" target="_blank">{{ number_format($station->order_2) }}</a>
								@else 
									{{ $station->order_2 }}
								@endif
							</td>
							<td class="data">
								@if ($station->order_3 > 0)
									<a href="{!! url(sprintf("/items?search_for_first=%s&search_in_first=station_name&search_for_second=2&search_in_second=batch_status&start_date=%s&end_date=%s&store=%s&status=1", 
																$station->station_name, $station->earliest_order_date, $date[4], $store_link)) !!}" target="_blank">{{ number_format($station->order_3) }}</a>
								@else 
									{{ $station->order_3 }}
								@endif
							</td>
										
							<td class="databorder">
								@if ($station->scan_1 > 0)
									<a href="{!! url(sprintf("/items?search_for_first=%s&search_in_first=station_name&search_for_second=2&search_in_second=batch_status&scan_start_date=%s&scan_end_date=%s&store=%s&status=1", 
																$station->station_name, $date[1], $date[0], $store_link)) !!}" target = "_blank">{{ number_format($station->scan_1) }}</a>
								@else 
										{{ $station->scan_1 }}
								@endif
							</td>
							<td class="data">
								@if ($station->scan_2 > 0)
									<a href="{!! url(sprintf("/items?search_for_first=%s&search_in_first=station_name&search_for_second=2&search_in_second=batch_status&scan_start_date=%s&scan_end_date=%s&store=%s&status=1", 
																$station->station_name, $date[3], $date[2], $store_link)) !!}" target = "_blank">{{ number_format($station->scan_2) }}</a>
								@else 
										{{ $station->scan_2 }}
								@endif
							</td>
							<td class="data">
								@if ($station->scan_3 > 0)
									<a href="{!! url(sprintf("/items?search_for_first=%s&search_in_first=station_name&search_for_second=2&search_in_second=batch_status&scan_start_date=%s&scan_end_date=%s&store=%s&status=1", 
																$station->station_name, $station->earliest_scan_date, $date[4], $store_link)) !!}" target = "_blank">{{ number_format($station->scan_3) }}</a>
								@else 
										{{ $station->scan_3 }}
								@endif
							</td>
						</tr>
						@endforeach
						
						<tr>
							<td></td>
							<td align="right">Quality Control SubTotals:</td>
							<td class="total">{!! number_format($qc->sum('lines_count')) !!}</td>
							<td class="total">{!! number_format($qc->sum('items_count')) !!}</td>
							<td class="totalborder">{!! number_format($qc->sum('order_1')) !!}</td>
							<td class="total">{!! number_format($qc->sum('order_2')) !!}</td>
							<td class="total">{!! number_format($qc->sum('order_3')) !!}</td>
							<td class="totalborder">{!! number_format($qc->sum('scan_1')) !!}</td>
							<td class="total">{!! number_format($qc->sum('scan_2')) !!}</td>
							<td class="total">{!! number_format($qc->sum('scan_3')) !!}</td>
						</tr>
					@endif
		
		@if (count($WAP) > 0)
			<tr class="total_footer">
				<td></td>
				<td align="right">WAP</td>
				<td class="data">{!! number_format($WAP->lines_count) !!}</td>
				<td class="data">{!! number_format($WAP->items_count) !!}</td>
				<td class="databorder">
					<a href="{!! url(sprintf("/items?start_date=%s&end_date=%s&store=%s&status=9", $date[1], $date[0], $store_link)) !!}" 
										target = "_blank">{!! number_format($WAP->order_1) !!}</a>
				</td>
				<td class="data">
					<a href="{!! url(sprintf("/items?start_date=%s&end_date=%s&store=%s&status=9", $date[3], $date[2], $store_link)) !!}" 
										target = "_blank">{!! number_format($WAP->order_2) !!}</a>
				</td>
				<td class="data">
					<a href="{!! url(sprintf("/items?start_date=%s&end_date=%s&store=%s&status=9", $WAP->earliest_order_date, $date[4], $store_link)) !!}" 
										target = "_blank">{!! number_format($WAP->order_3) !!}</a>
				</td>
				<td class="databorder" colspan="3"></td>
			</tr>
		@endif
		
		<tr class="success">
			<td colspan=10 height="30"></td>
		</tr>
		<tr class="total_footer">
			<td></td>
			<td align="right"><strong>Totals:</strong></td>
			<td class="total">{{ number_format( $items->sum('lines_count') + $backorders->sum('lines_count') +  $rejects->sum('lines_count') + 
                              $CS_rejects->sum('lines_count') + $CS->sum('lines_count') + $qc->sum('lines_count') + 
                              $unbatched->lines_count + $WAP->lines_count) }}</td>
			<td class="total">{{ number_format($items->sum('items_count') + $backorders->sum('items_count') +  $rejects->sum('items_count') + 
                              $CS_rejects->sum('items_count') + $CS->sum('items_count') + $qc->sum('items_count') + 
                              $unbatched->items_count + $WAP->items_count) }}</td>
			<td class="totalborder">{{ number_format($items->sum('order_1') + $backorders->sum('order_1') +  $rejects->sum('order_1') + $CS_rejects->sum('order_1') + 
                          $CS->sum('order_1') + $qc->sum('order_1') + $unbatched->order_1 + $WAP->order_1) }}</td>
			<td class="total">{{ number_format($items->sum('order_2') + $backorders->sum('order_2') +  $rejects->sum('order_2') + $CS_rejects->sum('order_2') + 
                          $CS->sum('order_2') + $qc->sum('order_2') + $unbatched->order_2 + $WAP->order_2) }}</td>
			<td class="total">{{ number_format($items->sum('order_3') + $backorders->sum('order_3') +  $rejects->sum('order_3') + $CS_rejects->sum('order_3') + 
                          $CS->sum('order_3') + $qc->sum('order_3') + $unbatched->order_3 + $WAP->order_3) }}</td>
			<td class="totalborder">{{ number_format($items->sum('scan_1') + $qc->sum('scan_1') + $backorders->sum('scan_1') +  $rejects->sum('scan_1')) }}</td>
			<td class="total">{{ number_format($items->sum('scan_2') + $qc->sum('scan_2') + $backorders->sum('scan_2') +  $rejects->sum('scan_2')) }}</td>
			<td class="total">{{ number_format($items->sum('scan_3') + $qc->sum('scan_3') + $backorders->sum('scan_3') +  $rejects->sum('scan_3')) }}</td>
		</tr>
		
		<tr class="success">
			<td colspan=4>Items Shipped Today:</td>
			<td colspan=2>Average Days to Ship</td>
			<td colspan=2>Percentage of Total</td>
			<td colspan=4></td>
		</tr>
		
		@setvar($avg_sum = 0)
		
		@if (count($shipped_today) > 0)
			@foreach ($shipped_today as $ship)
				<tr class="lines">
					<td></td>
					<td>{{ $ship->section_name }}</td>
					<td class="data">{{ number_format($ship->count) }}</td>
					<td></td>
					<td colspan=2 class="data">{{ sprintf("%2.1f", $ship->avgdays) }}</td>
					<td colspan=2 class="data">
						@if ($shipped_today->sum('count') > 0)
							{{ sprintf("%4.2f", $ship->count / $shipped_today->sum('count') * 100) }}%
						@endif
					</td>
					<td colspan=2></td>
				</tr>
				
				@setvar($avg_sum = $avg_sum + ($ship->avgdays * $ship->count))
				
			@endforeach
		@endif
		
		<tr class="total_footer">
			<td colspan=2 align="right"><strong>Total Shipped:</strong></td>
			<td class="total">{{ number_format($shipped_today->sum('count')) }}</td> 
			<td></td>
			<td class="total" colspan=2>
				@if ($shipped_today->sum('count') > 0)
					{!! sprintf("%2.1f", $avg_sum / $shipped_today->sum('count')) !!}
				@endif
			</td>
			<td colspan=7>
		</tr>
		
		<tr class="success">
			<td colspan=10 height="30"></td>
		</tr>
		
		
	</tfoot>
</table>

	</div>
	
	<script type = "text/javascript">

	$(document).ready(function() {

		$('#store_ids').multiselect({includeSelectAllOption:true});
	});
	
	function checkSize(){
		if (window.matchMedia("(min-width: 1024px)").matches) {
			$(".dept_id_select").chosen();
		} else {
			$(".dept_id_select").chosen("destroy");
		}
	}

	$(document).ready(function() {
			
			checkSize();
			$(window).resize(checkSize);
	
	});
	
	var picker = new Pikaday(
	{
			field: document.getElementById('max_datepicker'),
			format : "YYYY-MM-DD",
			minDate: new Date('2016-06-01'),    
	});
	
	</script>


</body>
</html>