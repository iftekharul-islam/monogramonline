<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<head>
	<meta http-equiv = "Content-Type" content = "text/html; charset=utf-8" />
	<title>{{ $section_name }} Inventory</title>
	<style type = "text/css">
		
		body,td,th {
			font-family: Verdana, Arial, Helvetica, sans-serif;
			font-size: 12px;
			color: #000000; 
			margin-top: 0px;
			text-align:left;
		}
		
		table { 
			page-break-inside:auto 
		}
		
		tr { 
			page-break-inside:avoid; 
			page-break-after:auto 
		}

		h2 {
		  font-family: Verdana, Arial, Helvetica, sans-serif;
			font-size: 18px;
			color: #000000; 
			font-weight:bold;
		}
		
		@page {
			width: 210mm;
			height: 297mm;
			margin-top: 10mm;
		}
		
		@media print {
			a[href]:after {
				content:none;
			}
		}
	</style>
	
	<script>
		window.print();
	</script>
	
</head>
<body>
	@if(count($item) > 0)
	<table style = "width:195mm;" cellpadding = "0" cellspacing = "0" >
		<tr>
			<td>
				<h2>
					@if ($batch_number != NULL)
						{{ $batch_number }}
						@if ($item->first()->store)
							{{ $item->first()->store->store_name }}
						@endif
					@else
						{{ $section_name }}
					@endif
					Inventory
				</h2>
				Print Date: {{ $report_date }}
				<br><br>
			</td>
			<td style="text-align:center;">
				<br>{!! \Monogram\Helper::getHtmlBarcode($report_id) !!}<br>
				Report Code: {{ $report_id }}
			</td>
		</tr>
	</table>
	
	<table style = "width:195mm;" cellpadding = "4" cellspacing = "0"  border = "1">
		<thead>
		<tr>
			<th>Station</th>
			<th>Stock #</th>
			<th>Description</th>
			<th>Bin</th>
			<th style="text-align:center;">Quantity</th>
			<th>Picked</th>
		</tr>
	</thead>
		<tbody>

		@foreach($item as $row)
			@if ($row['stock_no_unique'] != 'ToBeAssigned')
					<tr>
						<td>{{ $row->production_station->station_name }}</td>
						<td>{{ $row['stock_no_unique'] }}</td>				
						<td>{{ $row['stock_name_discription'] }}</td>
						<td>{{ $row['wh_bin'] }}</td>
						<td style="text-align:right;">{{ $row['total'] }}</td>
						<td></td>
					</tr>
			@endif
		@endforeach
		
		@foreach($unassigned as $row)
				
					<tr>
						<td>{{ $row->production_station->station_name }}</td>
						<td>{{ $row['stock_no_unique'] }}</td>				
						<td colspan="2">
							{{ $row['child_sku'] }}
							<br>
							 {{ $row['item_description'] }}
						</td>
						<td style="text-align:right;">{{ $row['total'] }}</td>
						<td></td>
					</tr>
					
		@endforeach
		
		</tbody>
	</table>

	@else
	
	<h3>No Inventory Summary.</h3>

	@endif
	
</body>
</html>
