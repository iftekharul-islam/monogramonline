<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Inventory</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap-multiselect.css">
	<link type = "text/css" rel = "stylesheet" href="/assets/css/pikaday.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap-multiselect.js"></script>	
	<script type = "text/javascript" src = "/assets/js/moment.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/pikaday.min.js"></script>
	<script src = "/assets/js/DYMO.Label.Framework.latest.js" type="text/javascript" charset="UTF-8"> </script>
	<script src = "/assets/js/dymoBarcode.js" type="text/javascript"> </script>
</head>
<body>
	@include('includes.header_menu')
	<div class = "container" style="min-width: 1550px;">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "{{url('inventories')}}" class = "active">Inventory</a></li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		
		<div class = "col-xs-12">
			<div class = "panel panel-default">
				<div class = "panel-heading">Search</div>
				<div class = "panel-body">
					{!! Form::open(['method' => 'get', 'url' => url('inventories'), 'id' => 'search-order']) !!}
					<div class="row">
						<div class = "form-group col-xs-2">
							{!! Form::text('search_for_first', $request->get('search_for_first'), ['id'=>'search_for_first', 'class' => 'form-control', 'placeholder' => 'Search For 1', 'tabindex' => 0]) !!}
						</div>
						<div class = "form-group col-xs-2">
							{!! Form::select('operator_first', $operators, $request->get('operator_first'), ['id'=>'operator_first', 'class' => 'form-control']) !!}
						</div>
						<div class = "form-group col-xs-2">
							{!! Form::select('search_in_first', $search_in, $request->get('search_in_first'), ['id'=>'search_in_first', 'class' => 'form-control']) !!}
						</div>
						
						<div class = "form-group col-xs-2">
							{!! Form::text('search_for_second', $request->get('search_for_second'), ['id'=>'search_for_second', 'class' => 'form-control', 'placeholder' => 'Search For 2']) !!}
						</div>
						<div class = "form-group col-xs-2">
							{!! Form::select('operator_second', $operators, $request->get('operator_second'), ['id'=>'operator_second', 'class' => 'form-control']) !!}
						</div>
						<div class = "form-group col-xs-2">
							{!! Form::select('search_in_second', $search_in, $request->get('search_in_second'), ['id'=>'search_in_second', 'class' => 'form-control']) !!}
						</div>
					</div>
					
					<div class="row">
						<div class = "form-group col-xs-2">
							{!! Form::text('search_for_third', $request->get('search_for_third'), ['id'=>'search_for_third', 'class' => 'form-control', 'placeholder' => 'Search For 3']) !!}
						</div>
						<div class = "form-group col-xs-2">
							{!! Form::select('operator_third', $operators, $request->get('operator_third'), ['id'=>'operator_third', 'class' => 'form-control']) !!}
						</div>
						<div class = "form-group col-xs-2">
							{!! Form::select('search_in_third', $search_in, $request->get('search_in_third'), ['id'=>'search_in_third', 'class' => 'form-control']) !!}
						</div>
						
						<div class = "form-group col-xs-2">
							{!! Form::text('search_for_fourth', $request->get('search_for_fourth'), ['id'=>'search_for_fourth', 'class' => 'form-control', 'placeholder' => 'Search For 4']) !!}
						</div>
						<div class = "form-group col-xs-2">
							{!! Form::select('operator_fourth', $operators, $request->get('operator_fourth'), ['id'=>'operator_fourth', 'class' => 'form-control']) !!}
						</div>
						<div class = "form-group col-xs-2">
							{!! Form::select('search_in_fourth', $search_in, $request->get('search_in_fourth'), ['id'=>'search_in_fourth', 'class' => 'form-control']) !!}
						</div>
					</div>
					
					<div class="row">
						<div class = "form-group col-xs-2">
							{!! Form::select('vendor_id', $vendors, $request->get('vendor_id'), ['id'=>'vendor_id', 'class' => 'form-control']) !!}
						</div>
						<div class = "form-group col-xs-3">
							{!! Form::select('section_ids[]', $sections, $request->get('section_ids'), ['id'=>'section_ids', 'multiple' => 'multiple', 'class' => 'form-control']) !!}
						</div>
						<div class = "form-group col-xs-1" style="text-align:right;">
							{!! Form::label('sortby', 'Sort By:', ['style' => 'margin-top:5px;']) !!}
						</div>
						<div class = "form-group col-xs-2">
							{!! Form::select('sort_by', $sorting, $request->get('sort_by'), ['id'=>'sort_by', 'class' => 'form-control']) !!}
						</div>
						<div class = "form-group col-xs-2">
							{!! Form::select('sorted', ['ASC' => 'Ascending', 'DESC' => 'Descending'], $request->get('sorted'), ['id'=>'sorted', 'class' => 'form-control']) !!}
						</div>
						<div class = "form-group col-xs-2">
							{!! Form::submit('Search', ['id'=>'search', 'style' => 'margin-top: 2px;', 'class' => 'btn btn-primary form-control']) !!}
						</div>
					</div>
					{!! Form::close() !!}
				</div>
			</div>
		</div>

		<div class = "col-xs-12">
			<div class = "panel panel-default collapse" id="calc_panel">
				<div class = "panel-heading">Calculate Ordering Quantities</div>
				<div class = "panel-body">
					{!! Form::open(['method' => 'get', 'url' => url('/inventory_admin/calculate_ordering'), 'id' => 'calculate_ordering']) !!}
						<div class = "form-group col-xs-2" style="text-align:right;">
							{!! Form::label('Use Total Sales from:', '' , ['style' => 'margin-top:10px;']) !!}
						</div>
						<div class = "form-group col-xs-2">
							<div class = 'input-group date'>
								{!! Form::text('start_date', $request->get('start_date'), ['id'=>'start_date_picker', 'class' => 'form-control', 'placeholder' => 'Start date', 'autocomplete' => 'off']) !!}
								<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
							</div>
						</div>
						<div class = "form-group col-xs-2">
							<div class = 'input-group date'>
								{!! Form::text('end_date', $request->get('end_date'), ['id'=>'end_date_picker', 'class' => 'form-control', 'placeholder' => 'End date', 'autocomplete' => 'off']) !!}
								<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
							</div>
						</div>
						<div class = "form-group col-xs-2">
							{!! Form::select('divisor', [
																						'1' => 'Divide by One', 
																						'2' => 'Divide by Two', 
																						'3' => 'Divide by Three', 
																						'4' => 'Divide by Four',
																						'5' => 'Divide by Five',
																						'6' => 'Divide by Six',
																						], null, ['id'=>'divisor', 'class' => 'form-control']) !!}
						</div>
						<div class = "form-group col-xs-1">
							{!! Form::submit('Calculate', ['class' => 'btn btn-primary']) !!}
						</div>

						<div class = "form-group col-xs-3">
							Minimum stock quantity will be set to the sales in the interval selected divided by the divisor selected.
						</div>
					{!! Form::close() !!}
				</div>
			</div>
		</div>
		
		<div class = "col-xs-12">
		
			<h3 class = "page-header">
				Inventory
				<small>
				@if(count($inventories) > 0 ) ({{ $inventories->total() }} items found @if (count($total) > 0) costing: ${!! number_format($total->cost, 2) !!} @endif / {{$inventories->currentPage()}} of {{$inventories->lastPage()}} pages) @endif
				</small>
				<div class="pull-right">
				<button type="button" class="btn btn-info btn-sm" data-toggle="collapse" data-target="#calc_panel">Ordering Quantities</button>
				<a class = "btn btn-success btn-sm" href = "{{url(sprintf('%s','inventories/create'))}}" target = "_blank">Create New Stock</a>
			</div>
			</h3>
			
		<!-- <samp style="color: red;" >* Remember always keep backup (Export) your CSV file before upload. No duplicate value in <b>stock_no_unique</b> column</samp> -->
			@if(count($inventories))
				<table class = "table table-bordered table-condensed" id = "inventory-table">
					<thead>
						<tr>
							<th colspan=2>Inventory Item</th>
							<th>Bin</th>
							<th>On Hand</th>
							<th width="120">Vendor</th>
							<th width="120">Current</th>
							<th width="120">Ordering</th>
							<th width="120">Value</th>
							<th width="120">Sales History</th>
						</tr>
					</thead>
					
					<tbody>
					@setvar($count = 1)
					
					@foreach($inventories as $inventory)
						<tr>
								<td rowspan="2">
									<img  border = "0" style="height: auto; width: 2cm;" src = "{{ $inventory->warehouse }}" />
								</td>
								<td rowspan="2">
									<a href = "{{url(sprintf("logistics/sku_list?search_for_first=%d&search_in_first=stock_number&contains_first=equals", $inventory->stock_no_unique)) }}"
									   target = "_blank">{{ $inventory->stock_no_unique }} </a>
									 : <b>{{ $inventory->stock_name_discription }}</b>
									<br>
									<small><i>
									@if ($inventory->section)
										{{ $inventory->section->section_name }}
									@else
										No Section
									@endif
									</i></small>
									<br>
									<a href = "{{ url(sprintf('inventories/%d/edit',$inventory->id)) }}"
											 data-toggle = "tooltip" data-placement = "top"
											 title = "Edit"><i class = 'glyphicon glyphicon-pencil text-success'></i></a>
									| <a href = "{{ url(sprintf('inventory_admin/duplicate/%s',$inventory->id)) }}"
											data-toggle = "tooltip" data-placement = "top"
											title = "Duplicate"><i class = 'glyphicon glyphicon-paste text-success'></i></a>
									| <a href = "#" onClick = "print_stock_label('{{ $inventory->stock_no_unique }}', '{{ $inventory->wh_bin }}', '{{ $inventory->stock_name_discription }}', '{{ $inventory->warehouse }}',)"
											 data-toggle = "tooltip" data-placement = "top"
											 title = "Print Label"><i class = 'glyphicon glyphicon-print text-success'></i></a>
									| <a href = "{{ url(sprintf('/inventory_admin/delete?id=%s',$inventory->id)) }}"
											 onclick="return confirm('Are you sure you want to delete?')"
											 data-toggle = "tooltip" data-placement = "top"
											 title = "Delete"><i class = 'glyphicon glyphicon-remove text-success'></i></a>
									| <a href = "{{ url(sprintf('/purchases/purchasedinvproducts?search_in=stock_no_exact&search_for=%s', $inventory->stock_no_unique)) }}"
 											target = "_blank"
 											data-toggle = "tooltip" data-placement = "top"
											title = "Vendor Information"><i class = 'glyphicon glyphicon-shopping-cart text-success'></i></a>
									| <a href = "{{ url(sprintf('/purchases?search_in=stock_number&search_for=%s', $inventory->stock_no_unique)) }}"
											target = "_blank"
											data-toggle = "tooltip" data-placement = "top"
											title = "View Purchase Orders"><i class = 'glyphicon glyphicon-usd text-success'></i></a>
									| <a href = "{{ url(sprintf('/inventory_admin/inventory_adjustments?view_stock_no=%s', $inventory->stock_no_unique)) }}"
 											target = "_blank"
 											data-toggle = "tooltip" data-placement = "top"
											title = "View Adjustments"><i class = 'glyphicon glyphicon-asterisk text-success'></i></a>
									| <a onclick='$("#{{ $inventory->id }}").toggle();'
											 data-toggle = "tooltip" data-placement = "top"
											 title = "Show Assigned Child SKUs"><i class = 'glyphicon glyphicon-list-alt text-success'></i></a>
											 {{ count($inventory->inventoryUnitRelation) }}
									| {!! \App\Task::widget('App\Inventory', $inventory->id, 'text-success', 11); !!}
								<br>
								
								<div id="{{ $inventory->id }}" class="toggle" style="display:none;">
									@if ($inventory->inventoryUnitRelation && count($inventory->inventoryUnitRelation) > 0)
										@foreach ($inventory->inventoryUnitRelation as $inventoryUnitRelation)
										<a href = "{{ url(sprintf('/logistics/sku_list?search_for_first=%s&contains_first=0&search_in_first=child_sku', $inventoryUnitRelation->child_sku)) }}"
												 target = "_blank">{{ $inventoryUnitRelation->child_sku }}</a>
											<br>
										@endforeach
									@else
										None Assigned
									@endif
								</div>
									 {{-- $inventory->sku_weight --}} 
								</td>
								<td> 
									{!! Form::text('wh_bin', $inventory->wh_bin , ['id' => 'BIN-' . $inventory->id, 'style'=>'width:100px;']) !!}
									<br>
									{!! Form::button('Update', ['id' => 'MSG-BIN-' . $inventory->id, 'class' => 'btn btn-xs btn-default update_bin']) !!}
								</td>
								<td>
									{!! Form::number('qty_on_hand', $inventory->qty_on_hand , ['id' => 'QTY-' . $inventory->id, 'style'=>'width:100px;', 
																			'min' => 0, 'onkeypress' => 'return isNumberKey(event)', 'tabindex' => $count++]) !!}
									<br>
									{!! Form::button('Update', ['id' => 'MSG-QTY-' . $inventory->id, 'class' => 'btn btn-xs btn-default update_qty']) !!}
								</td>
								<td rowspan="2">
									<table>
										<tr>
											<td>
													@if ($inventory->last_product && $inventory->last_product->vendor)
														{{ $inventory->last_product->vendor->vendor_name }}
													@else
														--
													@endif
											</td>
										</tr>
										@if ($inventory->last_product)
											<tr>
												<td>Lead Time: {{ $inventory->last_product->lead_time_days }}</td>
											</tr>
										@endif
										<tr>
											<td>Purchases: {{ number_format($inventory->total_purchase) }}</td>
										</tr>
									</table>
								</td>
								<td rowspan="2">
									<table>
										<tr data-toggle = "tooltip" data-placement = "top" title = "Allocated: Quantity in production">
											<td>Allocated:</td>
											<td width="40" align="right">
												<span id="ALC-{{ $inventory->id }}">{{ number_format($inventory->qty_alloc) }}</span>
											</td>
										</tr>
										<tr data-toggle = "tooltip" data-placement = "top" title = "Expected: Quantity on Order">
											<td>Expected:</td>
											<td width="40" align="right">{{ number_format($inventory->qty_exp) }}</td>
										</tr>
										@if ($inventory->qty_exp > 0 && $inventory->purchase_products)
											<tr data-toggle = "tooltip" data-placement = "top" title = "Expected: Quantity on Order">
												<td colspan=2>ETA: {{ $inventory->purchase_products->first()->eta }}</td>
											</tr>
										@endif
									</table>
								</td>
								<td rowspan="2">
									<table>
										<tr data-toggle = "tooltip" data-placement = "top" title = "Available: Quantity on hand less allocated">
											<td>Available:</td>
											<td width="20" align="right">
													<span id="AVL-{{ $inventory->id }}">{{ number_format($inventory->qty_av) }}</span>
											</td>
										</tr>
										<tr data-toggle = "tooltip" data-placement = "top" title = "Min. QTY: Minimum stock quantity">
											<td>Min. Qty:</td>
											<td width="40" align="right">{{ number_format($inventory->min_reorder) }}</td>
										</tr>
										@if ($inventory->re_order_qty != 0)
											<tr data-toggle = "tooltip" data-placement = "top" title = "Reorder: Recommended order quantity">
												<td>Reorder:</td>
												<td width="40" align="right">{{ number_format($inventory->re_order_qty) }}</td>
											</tr>
										@endif
									</table>
								</td>
								<td rowspan="2">
									<table>
										<tr data-toggle = "tooltip" data-placement = "top" title = "Cost: Last cost">
											<td>Cost:</td>
											<td width="70" align="right">
												<span id="CST-{{ $inventory->id }}">{{ number_format($inventory->last_cost, 2) }}</span>
											</td>
										</tr>
										<tr data-toggle = "tooltip" data-placement = "top" title = "Value: Quantity on hand times last cost">
											<td>Value:</td>
											<td width="70" align="right">
												<span id="VAL-{{ $inventory->id }}">${!! number_format($inventory->value, 2) !!}</span>
											</td>
										</tr>
									</table>
								</td>
								<td rowspan="2">
									<table>
										<tr>
											<td>30 days:</td>
											<td width="50" align="right">{{ number_format($inventory->sales_30) }}</td>
										</tr>
										<tr>
											<td>90 days:</td>
											<td width="50" align="right">{{ number_format($inventory->sales_90) }}</td>
										</tr>
										<tr>
											<td>Total:</td>
											<td width="50" align="right">{{ number_format($inventory->total_sale) }}</td>
										</tr>
									</table>
								</td>
						</tr>
						<tr>
							<td colspan=2 style="padding:0;text-align:center;">
								@if ($inventory->qty_user)
									<small style="font-size: 80%;">
										QTY updated by {{ $inventory->qty_user->username }} - {{ $inventory->qty_date }}
									</small>
								@endif
							</td>
						</tr>
					@endforeach
					</tbody>
				</table>
				
				<div class = "col-xs-12 text-center">
					{!! $inventories->appends(request()->all())->render() !!}
				</div>
				
			@else
				<div class = "alert alert-warning">No Inventory found.</div>
			@endif
		</div>
	</div>

	<script type = "text/javascript">
		
		$(document).ready(function() {
			
			$('#section_ids').multiselect();
			
			$('#search_for_first').focus();
			
			$('.update_qty').click(function(e) {
				
				var input = '#' + $( this ).attr('id').substring(4);
				var id = $( input ).attr('id').substring(4);
				
				$( this ).html('Loading...');
				
				$.ajax({
					type: 'get',
					url: '{{ url("inventory_admin/ajax_update") }}',
					data: 'id=' + id + '&field=qty_on_hand&value=' + $( input ).val(),
					context: this,
					success: function (response) {
						
						if (response != 'Updated') {
							$( this ).removeClass('btn-default').addClass('btn-danger');
						} else {
							$( this ).removeClass('btn-default').removeClass('btn-danger').addClass('btn-success');
							
							$('#AVL-' + id).html( $( input ).val() - $('#ALC-' + id).html());
							$('#VAL-' + id).html( '$' + ($( input ).val() * $('#CST-' + id).html()).toFixed(2) );
						}
						
						$( this ).html(response);
					}, 
					error: function () {
						$( this ).removeClass('btn-default').addClass('btn-danger');
						$( this ).html('Failed');
					}
				});
				
			});
			
			$('.update_bin').click(function(e) {
				
				var input = '#' + $( this ).attr('id').substring(4);
				var id = $( input ).attr('id').substring(4);
				
				$( this ).html('Loading...');
				
				$.ajax({
					type: 'get',
					url: '{{ url("inventories/ajax_update") }}',
					data: 'id=' + id + '&field=' + $( input ).attr('name') + '&value=' + $( input ).val(),
					context: this,
					success: function (response) {
						
						if (response != 'Updated') {
							$( this ).removeClass('btn-default').addClass('btn-danger');
						} else {
							$( this ).removeClass('btn-default').removeClass('btn-danger').addClass('btn-success');
						}
						
						$( this ).html(response);
					}, 
					error: function () {
						$( this ).removeClass('btn-default').addClass('btn-danger');
						$( this ).html('Failed');
					}
				});
				
			});
			
		});
		
		function isNumberKey(evt)
    {
       var charCode = (evt.which) ? evt.which : event.keyCode
       if(charCode==8)//back space
          return true;
       if (charCode < 48 || charCode > 57)//0-9
       {
          alert("Please Enter Only Numbers.");
          return false;
       }
          
       return true;

    }
		
		var picker = new Pikaday(
		{
				field: document.getElementById('start_date_picker'),
				format : "YYYY-MM-DD",
				minDate: new Date('2016-06-01'),
				maxDate: new Date(),
				yearRange: [2000,2030]      
		});
		
		var picker = new Pikaday(
		{
				field: document.getElementById('end_date_picker'),
				format : "YYYY-MM-DD",
				minDate: new Date('2016-06-01'),
				maxDate: new Date(),
				yearRange: [2000,2030]      
		});
		
		// $("#validate-file").on('click', function ()
		// {
		// 	$("#todo").val('validate');
		// 	$(this).closest('form').submit();
		// });
		// $("#upload-file").on('click', function ()
		// {
		// 	$("#todo").val('upload');
		// 	$(this).closest('form').submit();
		// });
    // 
		// $("button#inventorie_id").on('click', function (){
		// 	event.preventDefault();
		// 	$(this).closest("tr").find("form").submit();
		// });
		
	</script>

</body>
</html>
