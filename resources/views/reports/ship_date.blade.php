<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Ship Date Report</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/pikaday.min.css">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap-multiselect.css">
		
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/moment.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/pikaday.min.js"></script>
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
			<li><a href = "{{url('/report/ship_date')}}">Ship Date Report</a></li>
		</ol>

		<h3 class = "page-header">Ship Date Report</h3>

		<div class = "col-xs-12">
			{!! Form::open(['method' => 'get']) !!}
				<div class = "form-group col-xs-4">
					<label for = "store_ids">Store</label>
					<br>
					{!! Form::select('store_ids[]', $stores, $store_ids ?? [], ['id'=>'store_ids', 'multiple' => 'multiple', 'class' => 'form-control']) !!}
				</div>
				<div class = "form-group col-xs-3">
					<label for = "start_date">Start date</label>
					<div class = 'input-group date' id = 'start_date_picker'>
						{!! Form::text('start_date', $start_date, ['id'=>'start_datepicker', 'class' => 'form-control', 'placeholder' => 'Enter start date', 'autocomplete' => 'off']) !!}
						<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
					</div>
				</div>
				<div class = "form-group col-xs-3">
					<label for = "end_date">End date</label>
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

		@if (count($shipped_today) > 0)
			<table class="table" cellspacing="0" cellpadding="0">
				<thead>
					<tr class="warning">
						<th colspan=2>

						</th>
						<th class="data">Items Shipped</th>
						<th class="data">Qty Shipped</th>
						<th class="data">Average Days to Ship</th>
						<th class="data">Max Days to Ship</th>
						<th class="data">Percentage of Total</th>
					</tr>
				</thead>
				<tbody>
					@setvar($count=0)

					@foreach ($shipped_today as $ship)

						@if ($ship->ship_count > 0)
							@if (!isset($section) || $section != $ship->section_num)

								@setvar($class = 'row_' . $count++)

								@setvar($section = $ship->section_num)

								@setvar($section_count = $shipped_today->where('section_num', $section)->sum('ship_count'))
								@setvar($section_qty = $shipped_today->where('section_num', $section)->sum('item_quantity'))
									<tr>
										<th width="50">
											<a onclick='TR_toggle("{{ $class }}");'
		 	 								 data-toggle = "tooltip" data-placement = "top"
		 	 								 title = "Show SKUs"><i class = 'glyphicon glyphicon-chevron-down'></i></a>
										</th>
										<th width="200">
											{{ isset($sections[$ship->section_num]) ? $sections[$ship->section_num] : null }}
										</th>
										<th class="data">
											{!! number_format($section_count) !!}
										</th>
										<th class="data">
											{!! number_format($section_qty ) !!}
										</th>
										<th class="data">
											@if($section_count > 0)
												{!! number_format($shipped_today->where('section_num', $section)->sum('diff') / $section_count, 1) !!}
											@endif
										</th>
										<th class="data">
											{!! number_format($shipped_today->where('section_num', $section)->max('maxdays')) !!}
										</th>
										<th class="data">
											@if ($shipped_today->sum('ship_count') > 0)
												{{ sprintf("%4.2f", $section_count / $shipped_today->sum('ship_count') * 100) }}%
											@endif
										</th>
									</tr>
							@endif

							<tr class="{{ $class }} secondary collapse out" bgcolor="WhiteSmoke">
								<td></td>
								<td>
									{{ isset($stores[$ship->store_id]) ? $stores[$ship->store_id] : 'Store not Found' }}
								</td>
								<td class="data">
									{{ number_format($ship->ship_count) }}
								</td>
								<td class="data">{{ $ship->item_quantity  }}</td>
								<td class="data">{{ sprintf("%2.1f", $ship->avgdays) }}</td>
								<td class="data">{{ sprintf("%2.1f", $ship->maxdays) }}</td>
								<td class="data">
									@if ($shipped_today->where('section_num', $section)->sum('ship_count') > 0)
										{{ sprintf("%4.2f", $ship->ship_count / $shipped_today->where('section_num', $section)->sum('ship_count') * 100) }}%
									@endif
								</td>
							</tr>
							@setvar($avg_sum = $avg_sum + ($ship->avgdays * $ship->ship_count))
						@endif
					@endforeach

					<tr class="total_footer">
						<td align="right" colspan=2>
							<strong>Total Shipped:</strong>
						</td>
						<td class="data">
							{{ number_format($shipped_today->sum('ship_count')) }}
						</td>
						<td class="data">{{ number_format($shipped_today->sum('item_quantity')) }}</td>
						<td class="data">
							@if ($shipped_today->sum('ship_count') > 0)
								{!! sprintf("%2.1f", $shipped_today->sum('diff') / $shipped_today->sum('ship_count')) !!}
							@endif
						</td>
						<td></td>
						<td></td>
					</tr>

					<tr class="warning">
						<td colspan=7 height="30"></td>
					</tr>
				</tbody>
			</table>
		@endif
	</div>

<script>

	$('#store_ids').multiselect({
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

	function TR_toggle(className) {
		if($("." + className).hasClass("out")) {
				$("." + className).addClass("in");
				$("." + className).removeClass("out");
		} else {
				$("." + className).addClass("out");
				$("." + className).removeClass("in");
		}
	}
</script>

</body>
</html>