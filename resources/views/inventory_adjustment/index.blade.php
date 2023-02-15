<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Inventory Adjustments</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	
	<style>
	.desc {		width:300px; 
						word-wrap:break-word;
					}
	.table-nonfluid {
					   width: auto !important;
						 margin-left: 100px;
					}
					
		#sidebar {
		    height: 300px;
		    width: 600px;
		    float: right;
			}
		 
	</style>
</head>

<body>
	@include('includes.header_menu')
	<div class = "container" style="min-width: 1400px;">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "{{url('/inventory_admin/inventory_adjustments')}}" class = "active">Inventory Adjustments</a></li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		
		<ul id="myTab" class="nav nav-tabs">
			<li @if($tab == 'view') class="active" @endif><a href="#view" data-toggle="tab">View Adjustments</a></li>
			<li @if($tab == 'count') class="active" @endif><a href="#count" data-toggle="tab">Adjust Inventory</a></li>
			<li @if($tab == 'production') class="active" @endif><a href="#production" data-toggle="tab">Production Rejects</a></li>
		</ul>
		
		<br><br>
		
		<div id="tabContent" class="tab-content">
			
			<div class="tab-pane fade @if($tab == 'view') in active @endif" id="view">
				
				<div class = "col-xs-12">
					{!! Form::open(['method' => 'get']) !!}
					<div class = "form-group col-xs-4">
						{!! Form::text('view_stock_no', $view_stock_no, ['id'=>'search_for', 'class' => 'form-control', 'placeholder' => 'Enter Stock Number']) !!}
					</div>
					<div class = "form-group col-xs-2">
						{!! Form::submit('Find', ['id'=>'find', 'style' => 'margin-top: 2px;', 'class' => 'btn btn-primary form-control']) !!}
					</div>
					{!! Form::close() !!}
				</div>

				@if (count($adjustments) > 0)
						
						<table class="table table-hover">
							<thead>
								<tr>
									<th>Date</th>
									<th>Stock Number</th>
									<th>Description</th>
									<th>Quantity</th>
									<th>Type</th>
									<th>Note</th>
									<th>User</th>
								</tr>
							</thead>
							<tbody>
								@foreach ($adjustments as $adjustment)
									<tr>
										<td>{{ $adjustment->created_at }}</td>
										<td>
											<a href="{{ url(sprintf('/inventory_admin/inventory_adjustments?tab=count&count_stock_no=%s', $adjustment->stock_no_unique)) }}">
												{{ $adjustment->stock_no_unique }}</a>
										</td>
										<td class="desc">
											@if ($adjustment->inventory)
												{{ $adjustment->inventory->stock_name_discription }}
											@else 
												Inventory not found
											@endif
										</td>
										<td>{{ $adjustment->quantity }}</td>
										<td>{{ $adjustment->type }}</td>
										<td>{{ $adjustment->note }}</td>
										<td>{{ $adjustment->user->username }}</td>
									</tr>
								@endforeach
							</tbody>
						</table>
				
				@else
				<div class = "col-xs-12">
					<br>
					<div class = "alert alert-warning text-center">
						No adjustments found.
					</div>
				</div>
				@endif
				
			</div>
			
			<div class="tab-pane fade @if($tab == 'count') in active @endif" id="count">
				
				<div class = "col-xs-12">
					{!! Form::open(['method' => 'get']) !!}
					<div class = "form-group col-xs-4">
						{!! Form::text('count_stock_no', $count_stock_no, ['id'=>'search_for', 'class' => 'form-control', 'placeholder' => 'Enter Stock Number']) !!}
					</div>
					<div class = "form-group col-xs-2">
						{!! Form::submit('Find', ['id'=>'find', 'style' => 'margin-top: 2px;', 'class' => 'btn btn-primary form-control']) !!}
					</div>
					{!! Form::hidden('tab', 'count') !!}
					{!! Form::close() !!}
				</div>
				
				<div class="col-xs-12">
					<div class="row"><br></div>
				</div>
				
				@if (count($count) > 0)
						
						<table class="table">
							<tr>
								<td rowspan=2 width="2cm">
									<img  border = "0" style="height: auto; width: 2cm;" src = "{{ $count->warehouse }}" />
								</td>
								<td>
									<h4>
											<a href = "{{url(sprintf("logistics/sku_list?unassigned=0&search_for_first=%d&search_in_first=stock_number", $count->stock_no_unique)) }}"
							 						target = "_blank">{{ $count->stock_no_unique }} </a> : {{ $count->stock_name_discription }} 
									</h4>
								</td>
							</tr>
							<tr>
								<td>Bin: {{ $count->wh_bin }}</td>
							</tr>
						<table>
						
							<div id="sidebar">
								<strong>Update Quantity on Hand:</strong>
									{!! Form::open(['method' => 'post']) !!}
									{!! Form::number('count_quantity', '', ['style' => 'width:70px;margin-right:25px', 'min' => '0']) !!}
									Note:
									{!! Form::text('count_note', 'Quantity on Hand adjusted', ['style' => 'width:200px;margin-right:25px']) !!}
									{!! Form::hidden('count_stock_no', $count_stock_no) !!}
									{!! Form::submit('Update', ['id'=>'onhand', 'class' => 'btn btn-sm btn-success']) !!}
									{!! Form::close() !!}
									
									<br><br><br>
									
									<strong>Add or subtract units:</strong>
										{!! Form::open(['method' => 'post']) !!}
										{!! Form::number('adjust_quantity', '', ['style' => 'width:70px;margin-right:25px']) !!}
										Note:
										{!! Form::text('adjust_note', 'Manual Inventory Adjustment', ['style' => 'width:200px;margin-right:25px']) !!}
										{!! Form::hidden('count_stock_no', $count_stock_no) !!}
										{!! Form::submit('Adjust', ['id'=>'adjust', 'class' => 'btn btn-sm btn-success']) !!}
										{!! Form::close() !!}
							</div>
							
							<table class="table table-nonfluid table-bordered table-hover">
										<tr>
											<td width="200"><strong>Purchases</strong></td>
											<td width="100"> {{ $count->total_purchase }} </td>	
										</tr>
										<tr>
											<td><strong>Sales</strong></td>
											<td> {{ $count->total_sale }} </td>	
										</tr>
										<tr>
											<td><strong>Quantity On Hand</strong></td>
											<td> {{ $count->qty_on_hand }} </td>	
										</tr>
										<tr>
											<td><strong>Allocated</strong></td>
											<td> {{ $count->qty_alloc }} </td>
										</tr>
										<tr>
											<td><strong>Expected</strong></td>
											<td> {{ $count->qty_exp }} </td>
										</tr>
										<tr>
											<td><strong>Available</strong></td>
											<td> {{ $count->qty_av }} </td>
										</tr>
									</table>
								
							<br><br>
							
							
							@if ($count->adjustments)
								<table class="table table-hover">
									<thead>
										<tr>
											<th>Adjustment Date</th>
											<th>Stock Number</th>
											<th>Description</th>
											<th>Quantity</th>
											<th>Type</th>
											<th>Note</th>
											<th>User</th>
										</tr>
									</thead>
									<tbody>
										@foreach ($count->adjustments as $adjustment)
											<tr>
												<td>{{ $adjustment->created_at }}</td>
												<td>{{ $adjustment->stock_no_unique }}</td>
												<td class="desc">{{ $adjustment->inventory->stock_name_discription }}</td>
												<td>{{ $adjustment->quantity }}</td>
												<td>{{ $adjustment->type }}</td>
												<td>{{ $adjustment->note }}</td>
												<td>{{ $adjustment->user->username }}</td>
											</tr>
										@endforeach
									</tbody>
								</table>
						@endif
						
					@else
						<div class = "col-xs-12">
							<br>
							<div class = "alert alert-warning text-center">
								Stock number not found.
							</div>
						</div>
					@endif
				
			</div>
			
			<div class="tab-pane fade @if($tab == 'production') in active @endif" id="production">
				
				<div class = "col-xs-12">
					{!! Form::open(['method' => 'get']) !!}
					<div class = "form-group col-xs-4">
						{!! Form::text('reject_item', $reject_item, ['id'=>'reject_item', 'class' => 'form-control', 'placeholder' => 'Scan Reject']) !!}
					</div>
					<div class = "form-group col-xs-2">
						{!! Form::submit('Find', ['id'=>'find', 'style' => 'margin-top: 2px;', 'class' => 'btn btn-primary form-control']) !!}
					</div>
					{!! Form::hidden('tab', 'production') !!}
					{!! Form::close() !!}
				</div>
				
				<div class="col-xs-12">
					<div class="row"><br></div>
				</div>
				
				@if (count($rejects) > 0)
					<table class="table table-hover table-striped">
						<thead>
							<tr>
								<th>Date</th>
								<th>Stock #</th>
								<th>Item ID</th>
								<th>Status</th>
								<th>Reason</th>
								<th>Message</th>
								<th>User</th>
								<th>Quantity</th>
								<th colspan=2></th>
							</tr>
						</thead>
						<tbody>
							@foreach ($rejects as $reject)
								<tr>
									<td>{{ $reject->created_at }}</td>
									<td> 
										@if ($reject->item->inventoryunit)
											@foreach($reject->item->inventoryunit as $inv)
												{{ $inv->stock_no_unique }} &nbsp;
											@endforeach
										@else
											N/A
										@endif
									</td>
									<td>{{ $reject->item_id }}</td>
									<td>{{ $reject->graphic_status }}</td>
									<td>
										@if ($reject->rejection_reason_info)
											{{ $reject->rejection_reason_info->rejection_message }}
										@endif
									</td>
									<td class="desc">{{ $reject->rejection_message }}</td>
									<td>{{ $reject->user->username }}</td>
									<td>{{ $reject->reject_qty }}</td>
									<td>
										{!! Form::open(['method' => 'post']) !!}
										{!! Form::hidden('rejection_id', $reject->id) !!}
										{!! Form::hidden('action', 'scrap') !!}
										{!! Form::submit('Adjust Inventory', ['id'=>'scrap', 'class' => 'btn btn-sm btn-success']) !!}
										{!! Form::close() !!}
									</td>
									<td>
										{!! Form::open(['method' => 'post']) !!}
										{!! Form::hidden('rejection_id', $reject->id) !!}
										{!! Form::hidden('action', 'ignore') !!}
										{!! Form::submit('Ignore', ['id'=>'ignore', 'class' => 'btn btn-sm btn-warning']) !!}
										{!! Form::close() !!}
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
			@else
				<div class = "col-xs-12">
					<br>
					<div class = "alert alert-warning text-center">
						Reject not found or adjustment already complete.
					</div>
				</div>
			@endif
			</div>
			
		</div>
	</div>
</body>
</html>
