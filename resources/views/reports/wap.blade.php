<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Ship Date Report</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap-multiselect.css">
		
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap-multiselect.js"></script>	
	
	<style>
		tr {
			font-size: 12px;
		}
		
		.data {
			text-align:right;
		}
		
	</style>

</head>
<body>
	@include('includes.header_menu')
	<div class = "container" style="min-width: 800px;">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "{{url('/report/wap')}}">WAP Summary</a></li>
		</ol>

		<h3 class = "page-header">WAP Summary</h3>
		
		<div class = "col-xs-6">
			{!! Form::open(['method' => 'get']) !!}
				<div class = "form-group col-xs-4">
					<label for = "store_ids">Store</label>
					<br>
					{!! Form::select('store_ids[]', $stores, $store_ids ?? [], ['id'=>'store_ids', 'multiple' => 'multiple', 'class' => 'form-control']) !!}
				</div>
				<div class = "form-group col-xs-2">
					<label for = "" class = ""></label>
					{!! Form::submit('Filter', ['id'=>'filter', 'style' => 'margin-top: 5px;', 'class' => 'btn btn-primary btn-sm form-control']) !!}
				</div>
			{!! Form::close() !!}
		</div>		
		
		@if (count($summary) > 0)
			<table class="table" cellspacing="0" cellpadding="0">
				
				<thead>
				<tr class="toplabel">
					<td align="left"></td>
					<td colspan="3" align="center">Order Date Aging</td>
				</tr>
				<tr>
					<th></th>
					<th width="100" class="data">0-3</th>
					<th width="100" class="data">4-7</th>
					<th width="100" class="data">7+</th>
				</tr>
				</thead>
			
				<tbody>
					
				@if (count($totals) > 0)
					<tr>
						<th>Total</th>
						<th class="data">
							@if(isset($totals['order_1']))
								{{ number_format($totals['order_1']) }}
							@else
								0
							@endif
						</th>
						<th class="data">
							@if(isset($totals['order_2']))
								{{ number_format($totals['order_2']) }}
							@else
								0
							@endif
						</th>
						<th class="data">
							@if(isset($totals['order_3']))
								{{ number_format($totals['order_3']) }}
							@else
								0
							@endif
						</th>
					</tr>
				@endif
				
				@foreach ($summary as $label => $row)
					<tr>
						<td>{{ str_replace('ZZ_', '', $label) }}</td>
						<td class="data">
							@if(isset($row['order_1']))
								{{ number_format(count($row['order_1'])) }}
							@else
								0
							@endif
						</td>
						<td class="data">
							@if(isset($row['order_2']))
								{{ number_format(count($row['order_2'])) }}
							@else
								0
							@endif
						</td>
						<td class="data">
							@if(isset($row['order_3']))
								{{ number_format(count($row['order_3'])) }}
							@else
								0
							@endif
						</td>	
					</tr>
				@endforeach
				</tbody>
			</table>
		@endif
	</div>
	
<script>

	$('#store_ids').multiselect({
											includeSelectAllOption:true,
											numberDisplayed: 1,
										});
										
</script>

</body>
</html>