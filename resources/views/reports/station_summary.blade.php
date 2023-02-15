<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Station summary</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	<link rel="stylesheet" href="/assets/css/chosen.min.css">
	<link type = "text/css" rel = "stylesheet"
				href = "https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css">

	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/chosen.jquery.min.js"></script>
	<script type = "text/javascript" src = "https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
	<script type = "text/javascript" src = "https://cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script>
	
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
		.data {
			border-left: 1px solid #ddd;
			text-align: right;
		}
		.databorder {
			border-left: 3px solid #ddd;
			text-align: right;
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
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li>Active (unshipped) Batches</li>
		</ol>

			<h3 class = "page-header">Not Started & Active (unshipped) Batches By Stations summary</h3>
			
			<table style="border: solid 0px; width: 100%; table-layout: fixed !important;">
				<tr><td>
					<div class = "col-xs-12">
						<div class="row">
							{!! Form::open(['method' => 'get', 'url' => url('prod_report/station_summary'), 'id' => 'filter-summary']) !!}
							<div class = "form-group col-xs-4">
								{!! Form::select('section_id', $section_option, $request->get('section_id'), ['class'=> 'form-control dept_id_select', 'id'=>'section_id']) !!}
							</div>
							<div class = "form-group col-xs-3">
								{!! Form::submit('Filter by Department', ['id'=>'search', 'style' => 'margin-top: 0px;', 'class' => 'btn btn-primary form-control']) !!}
							</div>
							{!! Form::close() !!}
						</div>
					</div>
				</td></tr>
			</table>
		
			@if(count($summaries) > 0)
		<table id="summary_table" class="table" cellspacing="0" cellpadding="0">
			<thead>
			<tr class="toplabel">
				<td colspan="2"></td>
				<td colspan="2">Totals</td>
				<td colspan="5">Order Date Aging</td>
				<td colspan="5">Scan Date Aging</td>
			</tr>
			<tr>
				<th>Station</th>
				<th>Description</th>
				<th>lines</th>
				<th>Qty</th>
				<th>0-3</th>
				<th>4-7</th>
				<th>8-14</th>
				<th>15-21</th>
				<th>21+</th>
				<th>0-3</th>
				<th>4-7</th>
				<th>8-14</th>
				<th>15-21</th>
				<th>21+</th>
			</tr>
		</thead>
        <tbody>
			@foreach($summaries as $summary)
				<tr class="lines">
					<td>
						<a href = "{{url(sprintf("batches/list?route=all&station=%s&status=active", $summary['id']))}}" target = "_blank">{{$summary['station_name']}}</a>
					</td>
					<td>
						{{$summary['station_description']}}
					</td>
					<td class="data">
						{!! Form::open(['method' => 'postt', 'url' => '/move_next', 'target' => '_blank']) !!}
						{!! Form::hidden('station',  $summary['id']) !!}
						{!! Form::submit(number_format($summary['lines_count'],0), ['class' => 'remove_button_css']) !!}
						{!! Form::close() !!}
					<td class="data">{{ number_format($summary['items_count'],0) }}</td>
					<td class="databorder">
							@if ($summary['order_1'] > 0)
								<a href="{{$summary['order_1_link']}}" target = "_blank">{{$summary['order_1']}}</a>
							@else 
								{{$summary['order_1']}}
							@endif
					</td>
					<td class="data">
							@if ($summary['order_2'] > 0)
								<a href="{{$summary['order_2_link']}}" target = "_blank">{{$summary['order_2']}}</a>
							@else 
								{{$summary['order_2']}}
							@endif
					</td>
					<td class="data">
							@if ($summary['order_3'] > 0)
								<a href="{{$summary['order_3_link']}}" target = "_blank">{{$summary['order_3']}}</a>
							@else 
								{{$summary['order_3']}}
							@endif
					</td>
					<td class="data">
							@if ($summary['order_4'] > 0)
								<a href="{{$summary['order_4_link']}}" target = "_blank">{{$summary['order_4']}}</a>
							@else 
								{{$summary['order_4']}}
							@endif
					</td>
					<td class="data_late">
							@if ($summary['order_5'] > 0)
								<a href="{{$summary['order_5_link']}}" target = "_blank">{{$summary['order_5']}}</a>
							@else 
								{{$summary['order_5']}}
							@endif
					</td>
								
					<td class="databorder">
						@if ($summary['scan_1'] > 0)
							<a href="{{$summary['scan_1_link']}}" target = "_blank">{{$summary['scan_1']}}</a>
						@else 
								{{$summary['scan_1']}}
						@endif
					</td>
					<td class="data">
						@if ($summary['scan_2'] > 0)
							<a href="{{$summary['scan_2_link']}}" target = "_blank">{{$summary['scan_2']}}</a>
						@else 
								{{$summary['scan_2']}}
						@endif
					</td>
					<td class="data">
						@if ($summary['scan_3'] > 0)
							<a href="{{$summary['scan_3_link']}}" target = "_blank">{{$summary['scan_3']}}</a>
						@else 
								{{$summary['scan_3']}}
						@endif
					</td>
					<td class="data">
						@if ($summary['scan_4'] > 0)
							<a href="{{$summary['scan_4_link']}}" target = "_blank">{{$summary['scan_4']}}</a>
						@else 
								{{$summary['scan_4']}}
						@endif
					</td>
					<td class="data_late">
						@if ($summary['scan_5'] > 0)
							<a href="{{$summary['scan_5_link']}}" target = "_blank">{{$summary['scan_5']}}</a>
						@else 
								{{$summary['scan_5']}}
						@endif
					</td>
				</tr>
			@endforeach
        </tbody>
        
        <tfoot>
					<tr>
						<td></td>
						<td align="right">SubTotals:</td>
						<td class="data"></td>
						<td class="data"></td>
						<td class="databorder"></td>
						<td class="data"></td>
						<td class="data"></td>
						<td class="data"></td>
						<td class="data"></td>
						<td class="databorder"></td>
						<td class="data"></td>
						<td class="data"></td>
						<td class="data"></td>
						<td class="data"></td>
					</tr>
					@if (!isset($_GET['dept_id']) or $_GET['dept_id'] == '0')
						<tr class="total_footer">
								<td></td>
								<td align="right">Unbatched:</td>
								<td class="data">{{ number_format($unbatched[0]['lines_count'], 0) }}</td>
								<td class="data">{{ number_format($unbatched[0]['items_count'], 0) }}</td>
								<td class="databorder">
									@if ($unbatched[0]['order_1'] > 0)
										<a href="{{$unbatched['link_1']}}" target = "_blank">{{$unbatched[0]['order_1']}}</a>
									@else 
										{{$unbatched[0]['order_1']}}
									@endif
								</td>
								<td class="data">
									@if ($unbatched[0]['order_2'] > 0)
										<a href="{{$unbatched['link_2']}}" target = "_blank">{{$unbatched[0]['order_2']}}</a>
									@else 
										{{$unbatched[0]['order_2']}}
									@endif
								</td>
								<td class="data">
									@if ($unbatched[0]['order_3'] > 0)
										<a href="{{$unbatched['link_3']}}" target = "_blank">{{$unbatched[0]['order_3']}}</a>
									@else 
										{{$unbatched[0]['order_3']}}
									@endif
								</td>
								<td class="data">
									@if ($unbatched[0]['order_4'] > 0)
										<a href="{{$unbatched['link_4']}}" target = "_blank">{{$unbatched[0]['order_4']}}</a>
									@else 
										{{$unbatched[0]['order_4']}}
									@endif
								</td>
								<td class="data_late">
									@if ($unbatched[0]['order_5'] > 0)
										<a href="{{$unbatched['link_5']}}" target = "_blank">{{$unbatched[0]['order_5']}}</a>
									@else 
										{{$unbatched[0]['order_5']}}
									@endif
								</td>
								<td class="databorder" colspan="5"></td>
					</tr>
					<tr class="total_footer">
						<td></td>
						<td align="right">Totals:</td>
						<td class="data">{{ number_format($total_lines + $unbatched[0]['lines_count'], 0) }}</td>
						<td class="data">{{ number_format($total_items + $unbatched[0]['items_count'], 0) }}</td>
						<td class="databorder">{{ number_format($order_1_total + $unbatched[0]['order_1'], 0) }}</td>
						<td class="data">{{ number_format($order_2_total + $unbatched[0]['order_2'], 0) }}</td>
						<td class="data">{{ number_format($order_3_total + $unbatched[0]['order_3'], 0) }}</td>
						<td class="data">{{ number_format($order_4_total + $unbatched[0]['order_4'], 0) }}</td>
						<td class="data">{{ number_format($order_5_total + $unbatched[0]['order_5'], 0) }}</td>
						<td class="databorder">{{ number_format($scan_1_total, 0) }}</td>
						<td class="data">{{ number_format($scan_2_total, 0) }}</td>
						<td class="data">{{ number_format($scan_3_total, 0) }}</td>
						<td class="data">{{ number_format($scan_4_total, 0) }}</td>
						<td class="data">{{ number_format($scan_5_total, 0) }}</td>
					</tr>
					<tr class="total_footer">
						<td></td>
						<td align="right">Items Shipped Today:</td>
						<td class="data">{{ number_format($shipped_today, 0) }}</td>
						<td colspan="11">
					</tr>
				@endif
        </tfoot>
    </table>
			<!-- <a href = "{{url('summary/export')}}">Export Item Table</a> -->

		@else
			<div class = "col-xs-12">
				<div class = "alert alert-warning">No station summary.</div>
			</div>
		@endif
	</div>

	<script type = "text/javascript">

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
	
	$(document).ready(function() {

			var hideTotals = function () {
				
					var info = $('#summary_table').DataTable().page.info();
					
 					if ( info.recordsTotal - info.recordsDisplay > 0 ) {
							$('.total_footer').hide();
					} else {
							$('.total_footer').show();
					}
			}
			
			$('#summary_table').on( 'search.dt', function () { hideTotals(); } ).DataTable( {
				
					"paging":   false,
					
					"columns": [
						{ "searchable": false },
						{ "searchable": true },
						{ "searchable": false },
						{ "searchable": false },
						{ "searchable": false },
						{ "searchable": false },
						{ "searchable": false },
						{ "searchable": false },
						{ "searchable": false },
						{ "searchable": false },
						{ "searchable": false },
						{ "searchable": false },
						{ "searchable": false },
						{ "searchable": false }
					],
	
					"footerCallback": function ( row, data, start, end, display ) {
							var api = this.api(), data;
							
							for (i = 2; i < 14; i++) {
									pageTotal = api
													.column( i , { page: 'current'} )
													.data()
													.reduce( function (a, b) {
															if (b.substr(0,7) == '<a href') {
																	return a + parseInt($(b).text().replace(/,/g , ""),10);
															} else {
																	return a + parseInt(b.replace(/,/g , ""),10);
															}
													}, 0 );

									$( api.column( i ).footer() ).html(
											pageTotal
									);
								
							}
					}
			} );
	} );
	</script>


</body>
</html>