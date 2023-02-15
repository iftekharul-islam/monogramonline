<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>{{ $station_name }} Status</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	<link rel="stylesheet" href="/assets/css/chosen.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/chosen.jquery.min.js"></script>	
</head>
<body>
	@include('includes.header_menu')
	<div class = "container">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "{{url('/production/status')}}">Production Stations</a></li>
			<li class = "active"><a href = "{{ url(sprintf('/production/status_detail?station=%s',$station)) }}">{{ $station_name }}</a></li>
		</ol>
		
		@include('includes.error_div')
		@include('includes.success_div')
		
		<div class = "col-xs-12">
			{!! Form::open(['url' => '/production/scan_work', 'method' => 'POST']) !!}
			{!! Form::hidden('task', 'scan') !!}
			{!! Form::hidden('from', 'statusDetail') !!}
			<div class = "form-group col-xs-2">
				{!! Form::text('batch_number', '', ['id'=>'batch_barcode', 'class' => 'form-control', 'placeholder' => 'Enter Batch']) !!}
			</div>
			<div class = "form-group col-xs-2">
				{!! Form::password('user', ['id'=>'user_barcode', 'class' => 'form-control', 'autocomplete' => "new-password"]) !!}
			</div>
			<div class = "form-group col-xs-2">
				{!! Form::submit('Scan', ['id'=>'search', 'style' => 'margin-top: 0px;', 'class' => 'btn btn-primary']) !!}
			</div>
			{!! Form::close() !!}
			
			<div class = "form-group col-xs-1"></div>
			
			{!! Form::open(['method' => 'get', 'url' => url('/production/status_detail'), 'id' => 'station-status-form']) !!}
			<div class = "form-group col-xs-5">
				{!! Form::select('station', $stations, isset($station) ? $station : ''  , ['id'=>'station', 'class' => 'form-control']) !!}
			</div>
			{!! Form::close() !!}
		</div>
		
		<br><br><br>
		
		<h4 class="page-header">{{ $station_name }}</h4>
		
		<ul id="myTab" class="nav nav-tabs">
			<li class="active"><a href="#active" data-toggle="tab">Ready for Production By Inventory ({!! count($ready) !!})</a></li>
			<li><a href="#bydate" data-toggle="tab">Ready for Production By Date ({!! count($ready) !!})</a></li>
			<li><a href="#in_progress" data-toggle="tab">In Progress ({!! count($in_progress) !!})</a></li>
			<li><a href="#inventory" data-toggle="tab">Inventory Summary</a></li>
			<li><a href="#activity" data-toggle="tab">Recent Scans</a></li>
		</ul>
		
		<div id="tabContent" class="tab-content">
			
			<div class="tab-pane fade in active" id="active">

				<br><br>
				
				@if(count($ready) > 0)		
					<table class="table" id="batch-table">
						<tr>
							<th style="width:30px;">
								<img src = "{{ url('/assets/images/spacer.gif') }}"
										 width = "50" height = "20" border = "0">
							</th>
							<th>Batch</th>
							<th>Earliest Order</th>
							<th style="width:450px;">Inventory Items</th>
							<th>Next Station</th>
						</tr>
						@foreach($inventory_date as $inventory)
							
							@setvar($batches = $ready->where('inventory_profile', "$inventory")->sortby('min_order_date')->all())
							
								@foreach($batches as $batch)
								
									<tr class="batch-row" batch-number="{{ $batch->batch_number }}">
										<td>
											{{--<input type = "checkbox" name = "batch_number[]" class = "checkbox"
														 value = "{{ $batch->batch_number }}" />--}}
											{!! \App\Task::widget('App\Batch', $batch->id); !!}
										</td>
										<td>
											{{ $batch->batch_number }}
											@if($batch->store)
												<br><br>
												{{ $batch->store->store_name }}
											@endif
										</td>
										<td>{{ substr($batch->min_order_date, 0, 10) }}</td>
										<td>
											<table class="table">
											@foreach ($inventory_totals[$batch->batch_number] as $stockno => $qty)
												@setvar($inv = $inventory_details->where('stock_no_unique', "$stockno")->first())
												<tr>
													<td width="75px">
													@if ($inv && $inv->stock_no_unique != 'ToBeAssigned')
														<img  border = "0" style="height: auto; width: 70px;" src = "{{ $inv->warehouse }}" />
													@else
														<img  border = "0" style="height: auto; width: 70px;" src = "{{ $batch->item_thumb }}" />
													@endif
													</td>
													<td>
														Quantity: <strong style="font-size: 125%;">{{ $qty }}</strong>
														<br>
														@if ($inv && $inv->stock_no_unique != 'ToBeAssigned')
															{{ $inv->stock_no_unique }} 
															<br>
															{{ $inv->stock_name_discription }}
															<br>
															Bin: {{ $inv->wh_bin }}
														@else 
															{{ $batch->item_description }}
															<br>
															<strong style="color:red;">Stock Number Unassigned or Not Found</strong>
														@endif
													</td>
												</tr>
											@endforeach
											</table>
										</td>
										<td>{{ $next_in_route[$batch->batch_route_id] }}</td>
									</tr>
								@endforeach
								
								<tr bgcolor="#F0F0F0"><td colspan=5></td></tr>
								
						@endforeach
					</table>
				@else
						<div class = "alert alert-warning">No batches ready for production.</div>
				@endif
			
			</div>
			
			<div class="tab-pane fade" id="bydate">

				<br><br>
				
				@if(count($ready) > 0)		
					<table class="table" id="bydate-table">
						<tr>
							<th style="width:30px;">
								<img src = "{{ url('/assets/images/spacer.gif') }}"
										 width = "50" height = "20" border = "0">
							</th>
							<th>Batch</th>
							<th>Earliest Order</th>
							<th style="width:450px;">Inventory Items</th>
							<th>Next Station</th>
						</tr>

							@setvar($batches = $ready->sortby('min_order_date')->all())
							
								@foreach($batches as $batch)
								
									<tr class="batch-row" batch-number="{{ $batch->batch_number }}">
										<td>
											{{--<input type = "checkbox" name = "batch_number[]" class = "checkbox"
														 value = "{{ $batch->batch_number }}" />--}}
											{!! \App\Task::widget('App\Batch', $batch->id); !!}
										</td>
										<td>
											{{ $batch->batch_number }}
											@if($batch->store)
												<br><br>
												{{ $batch->store->store_name }}
											@endif
										</td>
										<td>{{ substr($batch->min_order_date, 0, 10) }}</td>
										<td>
											<table class="table">
											@foreach ($inventory_totals[$batch->batch_number] as $stockno => $qty)
												@setvar($inv = $inventory_details->where('stock_no_unique', "$stockno")->first())
												<tr>
													<td width="75px">
													@if ($inv && $inv->stock_no_unique != 'ToBeAssigned')
														<img  border = "0" style="height: auto; width: 70px;" src = "{{ $inv->warehouse }}" />
													@else
														<img  border = "0" style="height: auto; width: 70px;" src = "{{ $batch->item_thumb }}" />
													@endif
													</td>
													<td>
														Quantity: <strong style="font-size: 125%;">{{ $qty }}</strong>
														<br>
														@if ($inv && $inv->stock_no_unique != 'ToBeAssigned')
															{{ $inv->stock_no_unique }} 
															<br>
															{{ $inv->stock_name_discription }}
															<br>
															Bin: {{ $inv->wh_bin }}
														@else 
															{{ $batch->item_description }}
															<br>
															<strong style="color:red;">Stock Number Unassigned or Not Found</strong>
														@endif
													</td>
												</tr>
											@endforeach
											</table>
										</td>
										<td>{{ $next_in_route[$batch->batch_route_id] }}</td>
									</tr>
								@endforeach
								
					</table>
				@else
						<div class = "alert alert-warning">No batches ready for production.</div>
				@endif
			
			</div>
			
			<div class="tab-pane fade" id="in_progress">
				
				<br><br>
				
				@if(count($in_progress) > 0)		
					<table class="table" id="batch-table">
						<tr>
							<th style="width:30px;">
								<img src = "{{ url('/assets/images/spacer.gif') }}"
										 width = "50" height = "20" border = "0">
							</th>
							<th>Batch</th>
							<th></th>
							<th>Work Started</th>
							<th>By</th>
							<th>Elapsed Time</th>
							<th colspan=2></th>
						</tr>
						@foreach($in_progress as $batch)
									<tr batch-number="{{ $batch->batch_number }}">
										<td class="batch-row">
											{{--<input type = "checkbox" name = "batch_number[]" class = "checkbox"
														 value = "{{ $batch->batch_number }}" />--}}
											{!! \App\Task::widget('App\Batch', $batch->id); !!}
										</td>
										<td class="batch-row">
											{{ $batch->batch_number }}
											@if($batch->store)
												<br><br>
												{{ $batch->store->store_name }}
											@endif
										</td class="batch-row">
										<td class="batch-row">
											<img  border = "0" style="height: auto; width: 70px;" src = "{{ $batch->items->first()->item_thumb }}" />
										</td>
										<td class="batch-row">{{ $batch->in_date }}</td>
										<td class="batch-row">{{ $batch->username }}</td>
										<td class="batch-row">{{$batch->elapsed_time }}</td>
										<td width="200">
												{!! Form::open(['url' => '/supervisor/scan_work', 'method' => 'POST']) !!}
												{!! Form::hidden('task', 'force') !!}
												{!! Form::hidden('from', 'statusDetail') !!}
												{!! Form::hidden('batch_number', $batch->batch_number) !!}
												{!! Form::hidden('id', $batch->scan_id) !!}
												{!! Form::password('user', ['class' => 'form-control', 'autocomplete' => "new-password", 'placeholder' => 'Scan Supervisor ID']) !!}
											</td>
											<td>
												{!! Form::submit('Force Close', ['style' => 'margin-top: 0px;', 'class' => 'btn btn-danger']) !!}
												{!! Form::close() !!}
										</td>
									</tr>
								
						@endforeach
					</table>
				@else
						<div class = "alert alert-warning">No batches in progress.</div>
				@endif
			</div>
			
			<div class="tab-pane fade" id="activity">

				<br><br>
											
				@if (count($activity) > 0)

				<div class = "col-xs-12">
					<table class = "table table-bordered">
						<tr>
							<th>Batch</th>
							<th>Scanned In</th>
							<th>By User</th>
							<th>Scanned Out</th>
							<th>By User</th>
						</tr>
						@foreach($activity as $scan)
							<tr>
								<td>
									<a href = "{{ url(sprintf('batches/details/%s',$scan->batch_number)) }}">
										{{ $scan->batch_number }}</a>
								</td>
								<td>{{ $scan->in_date }}</td>
								<td>
									@if ($scan->in_user)
										{{ $scan->in_user->username }}
									@endif
								</td>
								<td>{{ $scan->out_date }}</td>
								<td>
									@if ($scan->out_user)
										{{ $scan->out_user->username }}
									@endif
								</td>
							</tr>
						@endforeach
					</table>
				</div>
				@endif
			</div>
			
			<div class="tab-pane fade" id="inventory">
				<table class="table">
				@foreach ($inventory_details as $inv)
					@if ($inv->stock_no_unique != 'ToBeAssigned')
						<tr height="70px">
							<td></td>
								<td>Quantity: <strong style="font-size: 125%;">
									@if (isset($inventory_summary[$inv->stock_no_unique]))
											{{ $inventory_summary[$inv->stock_no_unique] }}
									@else
											?
									@endif
										</strong>
								</td>
								<td width="75px">
								<img  border = "0" style="height: auto; width: 70px;" src = "{{ $inv->warehouse }}" />
								</td>
								<td>
									<a href="{{ url('inventories?search_in_first=stock_no_unique&operator_first=equals&search_for_first=' . $inv->stock_no_unique) }}"
									 target="_blank">{{ $inv->stock_no_unique }}</a>
									{{ $inv->stock_name_discription }}
								</td>
								<td>Bin: {{ $inv->wh_bin }}</td>
						</tr>
					@endif
				@endforeach
				
				@setvar($no_stock = $ready->where('inventory_profile', 'ToBeAssigned')->all())
				
				@foreach ($no_stock as $batch)
					<tr height="70px">
						<td></td>
							<td>Quantity: <strong style="font-size: 125%;">{{ $batch->quantity }}</strong></td>
							<td width="75px">
							<img  border = "0" style="height: auto; width: 70px;" src = "{{ $batch->item_thumb }}" />
							</td>
							<td>
								<strong>No Stock Number Assigned</strong>
								<br>
								{{ $batch->child_sku }}
								<br>
								{{ $batch->item_description }}
							</td>
							<td></td>
					</tr>
				@endforeach
				</table>
			</div>
			
	</div>

	<script type="text/javascript">
		
		$(function() {
			
				$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
						localStorage.setItem('lastTab', $(this).attr('href'));
				});

				
				var lastTab = localStorage.getItem('lastTab');
				if (lastTab) {
						$('[href="' + lastTab + '"]').tab('show');
				}
				
				$('#batch_barcode').focus();
				
				$('#batch_barcode').bind('keypress keydown keyup', function(e){
					 if(e.keyCode == 13) { 
						 e.preventDefault(); 
						 $('#user_barcode').focus();
					 }
				});
				
		});
	
		$(".batch-row").click(function() {
				$('#batch-table tr').removeClass("info");
				$(this).closest('tr').addClass("info");
				$("#batch_barcode").val($(this).closest('tr').attr('batch-number'));
				$("#user_barcode").focus();
		});
	
		$("select#station").on('change', function(){
			$("form#station-status-form").submit();
		});
		
		$(".chosen_txt").chosen();
		
	</script>
</body>
</html>