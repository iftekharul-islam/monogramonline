<!doctype html> 
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Batch preview</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	<link type = "text/css" rel = "stylesheet" href="/assets/css/pikaday.min.css">

	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/moment.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/pikaday.min.js"></script>
	<style>
		table {
			table-layout: fixed;
			font-size: 11px;
		}

		td {
			width: auto;
		}

		img {
			width: 50px;
			height: 50px;
		}
	</style>
</head>
<body>
	@include('includes.header_menu')
	<div class = "container">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li class = "active">Preview Batch</li>
		</ol>

		@include('includes.error_div')
		@include('includes.success_div')

		<div class = "col-xs-12">
			<div class="row">
				{!! Form::open(['url' => url('preview_batch'), 'method' => 'get']) !!}
				{!! Form::hidden('backorder', isset($backorder) ? $backorder : '' , ['id' => 'backorder']) !!}
				<div class = "form-group col-xs-2">
					<label for = "search_for_first">Search for</label>
					{!! Form::text('search_for_first', $request->get('search_for_first'), ['id'=>'search_for_first', 'class' => 'form-control', 'placeholder' => 'Comma delimited']) !!}
				</div>
				<div class = "form-group col-xs-2">
					<label for = "search_in_first">Search in</label>
					{!! Form::select('search_in_first', $search_in, $request->get('search_in_first'), ['id'=>'search_in_first', 'class' => 'form-control']) !!}
				</div>
				<div class = "form-group col-xs-2">
					<label for = "store">Store</label>
					{!! Form::select('store', $stores, $request->get('store'), ['id'=>'store', 'class' => 'form-control']) !!}
				</div>
				<div class = "form-group col-xs-2">
					<label for = "Section">Section</label>
					{!! Form::select('section', $sections, $request->get('section'), ['id'=>'section', 'class' => 'form-control']) !!}
				</div>
				<div class = "form-group col-xs-2">
					<label for = "start_date">Order Start date</label>
					<div class = 'input-group date' id = 'start_date_picker'>
						{!! Form::text('start_date', $request->get('start_date'), ['id'=>'start_datepicker', 'class' => 'form-control', 'placeholder' => 'Enter start date', 'autocomplete' => 'off']) !!}
						<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
					</div>
				</div>
				<div class = "form-group col-xs-2">
					<label for = "end_date">Order End date</label>
					<div class = 'input-group date' id = 'end_date_picker'>
						{!! Form::text('end_date', $request->get('end_date'), ['id'=>'end_datepicker', 'class' => 'form-control', 'placeholder' => 'Enter end date', 'autocomplete' => 'off']) !!}
						<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
					</div>
				</div>
			</div>
			<div class="row">
				<div class = "form-group col-xs-11"></div>
				<div class = "form-group col-xs-1">
						{!! Form::submit('Search', ['class' => 'btn btn-success']) !!}
				</div>
			</div>
				{!! Form::close() !!}
		</div>
		
		@if(count($batch_routes) > 0)
		
			<div><br><br><br></div>
				
			<div class="row">
				<div class = "form-group col-xs-7">
					<ul class="nav nav-pills nav-xs">
					  <li role="instock" @if(!$backorder) class="active" @endif ><a href="/preview_batch">In Stock</a></li>
					  <li role="backorder" @if($backorder) class="active" @endif ><a href="/preview_batch?backorder=1">Back Orders</a></li>
					</ul>
				</div>

				{!! Form::open(['url' => url('preview_batch'), 'method' => 'post']) !!}
				
				{!! Form::hidden('backorder', $backorder) !!}
				<div class = "form-group col-xs-2">
						{!! Form::checkbox('select-deselect', 1, false, ['id' => 'select-deselect', 'style' => 'margin-top:10px;']) !!} Select / Deselect all
				</div>
				<div class = "form-group col-xs-1">
					@if (!$locked)
							{!! Form::submit('Create batch', ['class' => 'btn btn-success']) !!}
					@else
						{!! Form::button('Auto Batch Running', ['class' => 'btn btn-warning']) !!}
					@endif
				</div>
			</div>
			
			<div class = "row">
				<div class = "col-xs-12">
					<table class = "table">
						<tr>
							<th>Batch #</th>
							<th>S.L #</th>
							<th>Batch S.L #</th>
							<th></th>
							<th>Item ID<br>Order #</th>
							<th>Order date<br>Store</th>
							<th>SKU</th>
							<th>Quantity</th>
						</tr>
					</table>
				</div>
				
				@foreach($batch_routes as $batch_route)
					
						<div class = "col-xs-12"> 
							<table class = "table">
								<tr data-id = "{{$batch_route['id']}}">
									<td>{{ $count }}</td>
									<td colspan="2">Route: {{ $batch_route['batch_code'] }} = {{ $batch_route['batch_route_name'] }}</td>
									<td>{!! Form::checkbox('select-deselect', 1, false, ['id' => 'group-select']) !!}</td>
									<td colspan="3" > Next station >>> {{ $batch_route['next_station'] }} )</td>
									<td></td>
								</tr>
								@setvar($row_serial = 1)
								
								@foreach ($batch_route['items'] as $item)
								
									<tr>
										<td><img src = "{{$item->item_thumb}}" /></td>
										<td>{{$serial++}}</td>
										<td>{{$row_serial++}}</td>
										<td>{!! Form::checkbox('batches[]', sprintf("%s|%s|%s|%s|%s", $count, $batch_route['id'], $item->item_table_id, $item->batch, $item->store_id) ,false, ['class' => 'checkable']) !!}</td>
										<td>
												***{{ $item->item_table_id }}
												<br>
				   							<a href = "{!! url(sprintf('orders/details/%s', $item->order_5p)) !!}"
											   target = "_blank">{{ $item->short_order }}
											</a>
										</td>
										<td>
												{{substr($item->order_date, 0, 10)}}
												<br>
												{{ $item->store_name }}
										</td>
										<td>
											<a href = "{{ url(sprintf("logistics/sku_list?search_for_first=%s&search_in_first=child_sku", $item->child_sku)) }}"
											   target = "_blank">{{$item->item_code}}</a>
										</td>
										<td>{{$item->item_quantity}}</td>
									</tr>
								@endforeach
								
								<tr>
									<td colspan=7></td>
									<td>
										<span class = "item_selected">{{ $row_serial - 1 }}</span> of <span
												class = "item_total">{{ $batch_route['batch_max_units'] }}</span> Max
									</td>
								</tr>
								@setvar(++$count)
							</table>
						</div>
						
				@endforeach
				
			</div>
			<div class="row">
				<div class = "form-group col-xs-7"></div>
				<div class = "form-group col-xs-2">
						{!! Form::checkbox('select-deselect', 1, false, ['id' => 'select-deselect', 'style' => 'margin-top:10px;']) !!} Select / Deselect all
				</div>
				<div class = "form-group col-xs-1">
					@if (!$locked)
							{!! Form::submit('Create batch', ['class' => 'btn btn-success']) !!}
					@else
						{!! Form::button('Auto Batch Running', ['class' => 'btn btn-warning']) !!}
					@endif
				</div>
			</div>
			{!! Form::close() !!}
		@else
			<div class = "col-xs-12">
				<div class = "alert alert-warning">
					No batches to create.
				</div>
			</div>
		@endif
	</div>

	<script type = "text/javascript">
		
		$(document).ready(function(){
			setTimeout( function() { 
    		$(".create_batch").hide();
  		}  , 180000 );
		});
		
		$(function(){
        $(".create_batch").on('click',function() {
            $(".create_batch").hide();
        }); 
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
		
		var state = false;
		
		$("input#select-deselect").click(function (event)
		{
			state = !state;
			$("input[type='checkbox']").not($(this)).prop('checked', state);
			$("table").each(function ()
			{
				updateTableInfo($(this));
			});
		});
		
		$("input#group-select").on("click", function (event)
		{
			var table = $(this).closest('table');
			var state = $(this).prop('checked');
			table.find('tr').not(':first').not(':last').each(function ()
			{
				$(this).find('input:checkbox').prop('checked', state);
			});

			updateTableInfo(table);
		});
		
		$("input.checkable").not('input#select-deselect, input.group-select').on('click', function (event)
		{
			var table = $(this).closest('table');
			var item_selected = getSelectedItemCount(table);
			var item_total = table.find('tr').not(':first').not(':last').length;
			//$(table).find('span.item_selected').text(item_selected);
			updateTableInfo(table);
			$(table).find('tr').eq(0).find('input:checkbox').prop('checked', item_selected == item_total);
		});

		function updateTableInfo (table)
		{
			$(table).find('span.item_selected').text(getSelectedItemCount(table));
		}

		function getSelectedItemCount (table)
		{
			var total_selected = 0;
			table.find('tr').not(':first').not(':last').each(function ()
			{
				if ( $(this).find('input:checkbox').prop('checked') == true ) {
					++total_selected;
				}
			});
			return total_selected;
		}
	</script>
</body>
</html>