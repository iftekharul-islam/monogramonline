<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Print Sublimation</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap-multiselect.css">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/pikaday.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap-multiselect.js"></script>	
	<script type = "text/javascript" src = "/assets/js/moment.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/pikaday.min.js"></script>
	<style>
	
		input[type=number]{
		  width: 50px;
		} 
		
		#wrapper {
		  margin-right: 50px;
			margin-left: 50px;
		}
		
		#content {
		  float: left;
		  width: 1000px;
		}
	
		#sidebar2 {
		  float: right;
		  width: 400px;
		}
		
		#cleared {
		  clear: both;
		}
		
	</style>
	
</head>
<body>
	@include('includes.header_menu')
		<div class = "container" style="width:95%;">
			<ol class = "breadcrumb">
				<li><a href = "{{url('/')}}">Home</a></li>
				<li class = "active"><a href="{{ url('/graphics/print_sublimation') }}">Print Sublimation</a></li>
			</ol>
			@include('includes.error_div')
			@include('includes.success_div')
			
			<div class = "wrapper">
				<div class = "col-xs-12">
					
				  <h4>Send Graphics to Printers</h4>
					<div class="pull-right">
						{!! Form::open(['url' => 'graphics/print_all', 'method' => 'POST', 'id' => 'print_all']) !!}
							<div class="form-group col-xs-10">
									@foreach ($batches as $batch)
										{!! Form::hidden('print_batches[]', $batch->batch_number) !!}
									@endforeach
									{!! Form::select('printer', $printers, null, 
														['id' => 'printer', 'class' => 'form-control', 'onclick' => "return false;", 'placeholder' => 'Select Printer' ]) !!}
							</div>
							<div class = "form-group col-xs-2">
										@setvar($msg = 'Are you sure you want to print these files?')
										{!! Form::submit('Print All' , ['class' => 'btn btn-sm btn-warning', 
																			'onclick' => 'return confirm("' . $msg .'")']) !!}
							</div>
						{!! Form::close() !!}
					</div>

			  </div>
				<div class = "col-xs-12">
					<div class = "panel panel-default">
						<div class = "panel-body">
					    {!! Form::open(['method' => 'get', 'url' => 'graphics/print_sublimation', 'name' => 'filter']) !!}
							<div class = "form-group col-xs-6 col-md-2">
								{!! Form::text('select_batch', $select_batch, ['id'=>'select_batch', 'class' => 'form-control', 
																			'onchange' => 'return false;', 'placeholder' => 'Batch Number']) !!}
							</div>
							<div class = "form-group col-xs-3 col-md-2">
					      {!! Form::select('type', ['soft' => 'Soft', 'hard' => 'Hard', 'other' => 'Other'], isset($type) ? $type : '',  
																				['class' => 'form-control', 'placeholder' => 'Type', 'onclick' => 'return false;']) !!}
					    </div>
							<div class = "form-group col-xs-9 col-md-2">
					      {!! Form::select('production_station_id[]', $stations, isset($production_station_id) ? $production_station_id : '',  
																				['class' => 'form-control', 'id' => 'production_station_id', 'multiple' => 'multiple', 
																				'onclick' => 'return false;']) !!}
					    </div>
							<div class = "form-group col-xs-6 col-md-1">
								@if (count($stores) > 1)
					      	{!! Form::select('store_id[]', $stores, $store_id,  ['id' => 'store_id', 'multiple' => 'multiple', 'class' => 'form-control']) !!}
					    	@endif
							</div>
							<div class = "form-group col-xs-6 col-md-2">
								<div class = 'input-group date' id = 'from_date_picker'>
									{!! Form::text('from_date', $from_date ?? '', ['id'=>'from_datepicker', 'class' => 'form-control', 'placeholder' => 'from', 'onclick' => 'return false;','autocomplete' => 'off']) !!}
									<span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
								</div>
							</div>
							<div class = "form-group col-xs-6 col-md-2">
								<div class = 'input-group date' id = 'to_date_picker'>
									 {!! Form::text('to_date', $to_date ?? '', ['id'=>'to_datepicker', 'class' => 'form-control', 'placeholder' => 'To', 'onclick' => 'return false;']) !!}
								 <span class = "input-group-addon"><span class = "glyphicon glyphicon-calendar"></span></span>
								</div>
							</div>
							{!! Form::select('Set printers', $printers, null,
                                                ['id' => 'printer_number_default', 'class' => 'form-control', 'placeholder' => 'Mass set a printer' ]) !!}


							<div class = "form-group col-xs-6 col-md-1"></div>
							<div class = "form-group col-xs-6 col-md-1">
					      {!! Form::submit('Show' , ['class' => 'btn btn-default']) !!}
					    </div>
					  {!! Form::close() !!}
						</div>	
					</div>
				</div>
				<script type="application/javascript">
					$(document).on('change','#printer_number_default',function(){
						var printer = $('#printer_number_default :selected').text();
                        // #printer_select_X01-602245
						// $('div[id^="list_"]')
						if(printer !== "Mass set a printer") {
							document.querySelectorAll('.printer-option').forEach(function(shit) {
								shit.selectedIndex = $('#printer_number_default option:selected').index()
							});

						}
					});
				</script>
			    
			  <div id="content">
			      @if(isset ($batches) && count($batches) > 0)
							<h4>{{ count($batches) }} batches found</h4>
			        <table class="table">
			         <tbody>
			          @foreach($batches as $batch)
										@if ($batch->to_printer_date != NULL) 
											<tr bgcolor="#ffffe6">
										@else
											<tr>
										@endif
			              <td width="400">
												<a href = "{{ url(sprintf('batches/details/%s', $batch->batch_number)) }}" target="_blank">
																<strong>{{ $batch->batch_number }}</strong></a>
											@if ($batch->items && count($batch->items) > 1)
													({{ count($batch->items) }} Items)
											@endif
											<strong style="color: red;">{{ $stores[$batch->store_id] ?? null }}</strong>
			                
											<br>
											
			                @if (strpos(strtolower($batch->route->csv_extension), 'soft'))
			                  <br>
			                  <div class="button-box print-group" onclick="return false;">
			                    <div class="col-lg-3">
			                      {!! Form::open(['id' => 'move_form_' . $batch->batch_number . rand(10,99)]) !!}
			                      {!! Form::hidden('batch_number', $batch->batch_number) !!}
			                      {!! Form::hidden('printer', 'SOFT-1') !!}
			                      {!! Form::button('SOFT-1' , ['id'=>'move_' . $batch->batch_number, 'class' => 'btn btn-xs btn-primary']) !!}
			                      {!! Form::close() !!}
			                    </div>
			                    <div class="col-lg-3">
			                      {!! Form::open(['id' => 'move_form_' . $batch->batch_number . rand(10,99)]) !!}
			                      {!! Form::hidden('batch_number', $batch->batch_number) !!}
			                      {!! Form::hidden('printer', 'SOFT-2') !!}
			                      {!! Form::button('SOFT-2' , ['id'=>'move_' . $batch->batch_number, 'class' => 'btn btn-xs btn-info']) !!}
			                      {!! Form::close() !!}
			                    </div>
													<div class="col-lg-3">
			                      {!! Form::open(['id' => 'move_form_' . $batch->batch_number . rand(10,99)]) !!}
			                      {!! Form::hidden('batch_number', $batch->batch_number) !!}
			                      {!! Form::hidden('printer', 'SOFT-3') !!}
			                      {!! Form::button('SOFT-3' , ['id'=>'move_' . $batch->batch_number, 'class' => 'btn btn-xs btn-primary']) !!}
			                      {!! Form::close() !!}
			                    </div>
													<div class="col-lg-3">
			                      {!! Form::open(['id' => 'move_form_' . $batch->batch_number . rand(10,99)]) !!}
			                      {!! Form::hidden('batch_number', $batch->batch_number) !!}
			                      {!! Form::hidden('printer', 'EPSON-5') !!}
			                      {!! Form::button('EPSON-5' , ['id'=>'move_' . $batch->batch_number, 'class' => 'btn btn-xs btn-warning']) !!}
			                      {!! Form::close() !!}
			                    </div>
			                  </div>
			                @elseif (strpos(strtolower($batch->route->csv_extension), 'hard'))
			                  <br>
			                  <div class="button-box  print-group" onclick="return false;">
			                    <div class="col-lg-4">
			                      {!! Form::open(['id' => 'move_form_' . $batch->batch_number . rand(10,99)]) !!}
			                      {!! Form::hidden('batch_number', $batch->batch_number) !!}
			                      {!! Form::hidden('printer', 'HARD-1') !!}
			                      {!! Form::button('Send to HARD-1' , ['id'=>'move_' . $batch->batch_number, 'class' => 'btn btn-xs btn-danger']) !!}
			                      {!! Form::close() !!}
			                    </div>
			                    <div class="col-lg-4">
			                      {!! Form::open(['id' => 'move_form_' . $batch->batch_number . rand(10,99)]) !!}
			                      {!! Form::hidden('batch_number', $batch->batch_number) !!}
			                      {!! Form::hidden('printer', 'HARD-2') !!}
			                      {!! Form::button('Send to HARD-2' , ['id'=>'move_' . $batch->batch_number, 'class' => 'btn btn-xs btn-success']) !!}
			                      {!! Form::close() !!}
			                    </div>
													<div class="col-lg-4">
			                      {!! Form::open(['id' => 'move_form_' . $batch->batch_number . rand(10,99)]) !!}
			                      {!! Form::hidden('batch_number', $batch->batch_number) !!}
			                      {!! Form::hidden('printer', 'HARD-3') !!}
			                      {!! Form::button('Send to HARD-3' , ['id'=>'move_' . $batch->batch_number, 'class' => 'btn btn-xs btn-danger']) !!}
			                      {!! Form::close() !!}
			                    </div>
			                  </div>
			                @else
												<div class="print-group" onclick="return false;">
				                  <div class = "col-xs-12">
				                    {!! Form::open(['id' => 'move_form_' . $batch->batch_number . rand(10,99)]) !!}
				                    {!! Form::hidden('batch_number', $batch->batch_number) !!}
				                    <div class = "form-group col-xs-10">
				                      {!! Form::select('printer', $printers, null, 
																			['id' => 'printer_select_' . $batch->batch_number, 'class' => 'form-control printer-option', 'onclick' => "return false;", 'placeholder' => 'Select Printer']) !!}
				                    </div>
				                    <div class = "form-group col-xs-2">
				                      {!! Form::button('Send' , ['id'=>'move_' . $batch->batch_number, 'class' => 'btn btn-sm btn-default', 'style' => 'margin-top: 3px;']) !!}
				                    </div>
				                  {!! Form::close() !!}
				                  </div>	
												</div>
			                @endif
			                
												<div class="print-message" style="display:none;" onclick="return false;">
													<div class = "col-xs-12">
														<div class = "form-group col-xs-10">
													 		<strong style="color: red;">Please Wait...</strong>
														</div>
												 </div>
												</div>
												
											
			              </td>
			              <td width="100">
			                @if ($batch->items && count($batch->items) > 0 && $batch->items->first()->child_sku)
			                  <span data-toggle = "tooltip" data-placement = "top" title = "{{ $batch->items->first()->child_sku }}">
			                    <a href = "{{ url(sprintf('batches/details/%s', $batch->batch_number)) }}" target="_blank">
													<img src = "{{ $batch->items->first()->item_thumb }}" width = "50" height = "50" /></a>
			                  </span>
			                @endif
			              </td>
			              <td width="400">
											@if($batch->type  == 'P')
												<strong style="color: red;">IN PRODUCTION:</strong> 
												<br>
											@elseif($batch->type == 'Q')
												<strong style="color: red;">IN QC:</strong>
												<br>
											@endif
											@if ($batch->production_station)
												Give to: {{ $batch->production_station->station_description }}
												<br>
											@else
												PRODUCTION STATION NOT FOUND: {{ $batch->production_station_id }}
												<br>
											@endif
											@if ($batch->status != 'active')
			                  Batch Status: <strong style="color: red;">{{ $batch->status }}</strong>
											@endif
											<br>
											First Order Date: {{ substr($batch->min_order_date, 0, 10) }}
				             </td>

													<td>Graphic</td>
												<td>
															@if($batch->graphic_found == 'Found' or $batch->graphic_found == 'Unknown')
																<a href="{{ url(sprintf('batches/view_graphic?batch_number=%s',$batch->batch_number)) }}"
																   target="_blank">View Graphics</a>
															@endif
													</td>
										 <td width="200">
											 <table cellpadding="5">
												 <tr>
													 <td align="right">Scale:</td>
													 <td>{!! Form::number('scale', 100, ['id' => 'scale_' . $batch->batch_number, 'onclick' => 'return false;']) !!} %</td>

													 <td align="right">
														 Pdf:
													 </td>
													 <td>
														 {!! Form::checkbox(
                                                         'pdf', 1,
                                                         in_array(substr($batch->items->first()->child_sku, -4), ['5060', '3040']),
                                                         ['id' => 'pdf_' . $batch->batch_number]
                                                         )
                                                         !!}
													 </td>
												 </tr>
												 <!-- <tr>
													 <td align="right">Min Size:</td>
													 <td>{!! Form::number('minsize', null, ['id' => 'minsize_' . $batch->batch_number, 'onclick' => 'return false;']) !!} "</td>
												 </tr> -->
												 <!-- <tr>
													 <td align="right">Mirror:</td>
													 <td>{!! Form::checkbox('mirror', 1, 0, ['id' => 'mirror_' . $batch->batch_number]) !!}</td>
												 </tr> -->

											 </table>
										 </td>
												<td>
													Rejects

											@foreach($batch->items as $item)
												@if ($item->rejections)
													@foreach ($item->rejections as $reject)
														<tr class="warning">
															<td colspan=2></td>
															<td colspan=4>
																Item {{ $item->id }} Rejected {{ $reject->created_at }}
																by {{ $reject->user->username }}
																@if ($reject->rejection_reason_info)
																	- {{ $reject->rejection_reason_info->rejection_message }}
																@endif
																- {{ $reject->rejection_message }}
															</td>
														</tr>
														@endforeach
														@endif
														@endforeach
														</td>
				            </tr>
			          @endforeach
			        </table>
			      @elseif (isset($summary) and count($summary) > 0)
			        <table class="table">
								<tr>
									<th>Production Station</th>
									<th>First order</th>
									<th width=200 style="text-align:right;">Batches</th>
								</tr>
								
								@foreach ($summary as $row)
									<tr>
										<td>{{ $row->production_station->station_description }}</td>
										<td>{{ substr( $row->date, 0, 10 ) }}</td>
										<td align="right">
											<a href="{{ url(sprintf("graphics/print_sublimation?status=movable&graphic_found=1&section=6&station=92&production_station_id=%s&from_date=$from_date&to_date=$to_date", $row->production_station_id)) }}"
												target="_blank">{{ $row->count }}</a>
										</td>
									</tr>
								@endforeach
								
								<tr> 
									<th></th> 
									<th style="text-align:right;">Total:</th> 
									<th width=200 style="text-align:right;"> 
										{{ $summary->sum('count') }} 
									</th> 
								</tr> 
							</table>
						@else
							<div class = "alert alert-warning">Nothing Found</div>
			      @endif
			  </div>
			  
			  <div id="sidebar2">
					@if (!is_array($queues))
						{{ $queues }}
					@else
						@foreach($queues as $unit => $queue)
							<div class="panel panel-default">
								<div class="panel-heading">
									<h4 class="panel-title">
										<a data-toggle="collapse" href="#collapse{!! substr($unit, -1) !!}">
											 {!! str_replace('_', ' ', $unit) !!} ({{ $queue['TOTAL'] }})
										</a>
										<br><br>
										@if($unit != 'PRINTER_5')
											{!! Form::select('config_' . substr($unit, -1), $stations, isset($config[substr($unit, -1)]) ? $config[substr($unit, -1)] : null, 
												 						['class' => 'select_config form-control', 'id' => substr($unit, -1), 'placeholder' => 'Manual Print']) !!}
										@endif
									</h4>
								</div>
								<div id="collapse{!! substr($unit, -1) !!}" class="panel-collapse collapse">
									<div class="panel-body">
										<strong>STAGED XML</strong>
										<br>
										@if (count($queue['STAGED_XML']) > 0)
													@foreach ($queue['STAGED_XML'] as $file)
														{{ $file }} <br>
													@endforeach
										@else
											Nothing<br>
										@endif
										<br>
										<strong>HOT FOLDER</strong>
										<br>
										@if (count($queue['HOT_FOLDER']) > 0)
													@foreach ($queue['HOT_FOLDER'] as $file)
														{{ $file }} <br>
													@endforeach
										@else
											Nothing<br>
										@endif
										<br>
										<strong>RIP QUEUE</strong>
										<br>
										@if (count($queue['RIP_QUEUE']) > 0)
													@foreach ($queue['RIP_QUEUE'] as $file)
														{{ $file }} <br>
													@endforeach
										@else
											Nothing<br>
										@endif
										<br>
										<strong>PRINT QUEUE</strong>
										<br>
										@if (count($queue['PRINT_QUEUE']) > 0)
													@foreach ($queue['PRINT_QUEUE'] as $file)
														{{ $file }} <br>
													@endforeach
										@else
											Nothing
										@endif
									</div>
								</div>
							</div>
						@endforeach
					@endif
			  </div>
			  
			  <div id="cleared"></div>
			  
			</div>
		</div>

			<script type='text/javascript'>
				
				$('#store_id').multiselect({
																			includeSelectAllOption:true,
																			nonSelectedText:'Store',
																			numberDisplayed: 1,
																		});
				$('#production_station_id').multiselect({
																			includeSelectAllOption:true,
																			nonSelectedText:'Station',
																			numberDisplayed: 1,
																		});
				
				$(document).ready(function(){
					setTimeout( function() { 
						$(".print-group").hide();
					}  , 120000 );
				});
				
				var picker = new Pikaday(
				{
						field: document.getElementById('to_datepicker'),
						format : "YYYY-MM-DD",
						minDate: new Date('2016-06-01'),    
				});

				var picker = new Pikaday(
				{
					field: document.getElementById('from_datepicker'),
					format : "YYYY-MM-DD",
					minDate: new Date('2016-06-01'),
				});

				$(function () {

					$('button[id^="move_"]').on('click', function (e) {
						
						form_name = "#" + $(this).closest('form').attr('id');
	          e.preventDefault();
						
						if ($( form_name ).parent().parent().attr('class').indexOf('print-group') !== -1)  {
							$( form_name ).parent().parent().hide();
							
							$( form_name ).closest("tr").find('.print-message').show();
						}
						
						var batch_number = $( form_name ).find('input[name="batch_number"]').val(); 
						var scale = $("#scale_" + batch_number).val();
						// var minsize = $("#minsize_" + batch_number).val();
						// var mirror = $("#mirror_" + batch_number).val();
						
						$( form_name ).append("<input type='hidden' name='scale' value='"+ scale +"'>");
						// $( form_name ).append("<input type='hidden' name='minsize' value='"+ minsize +"'>");
						// $( form_name ).append("<input type='hidden' name='mirror' value='"+ mirror +"'>");

						if($("#pdf_" + batch_number).is(':checked') === true){
							$( form_name ).append("<input type='hidden' name='pdf' value='1'>")
						} else {
							$( form_name ).append("<input type='hidden' name='pdf' value='0'>")
						}


						$.ajax({
							type: 'post',
							url: '{{ url("graphics/move_to_print") }}',
							data: $(form_name).serialize(),
							context: this,
							success: function (response) {
								if (response == 'Batch Summary Creation Error') {

									console.log("Trying request now... ");
									// httpGet("https://order.monogramonline.com/fix/image-load/link/" + batch_number)
									// $(this).closest("tr").find('.print-message').html("Batch Summary Creation Error, Please wait while we retry in 10 seconds!")
									// setTimeout(function (){
									// 	$('#' + e.target.id).click();
									// }, 10000)
									return;

								}
								$(this).closest("tr").find('.print-message').html(response);

							},
							failure: function (response) {
								$(this).closest("tr").find('.print-message').html('Failure');
							}
						});

	        });
					
					$('select[name^="config_"]').on('change', function (e) {
						
						e.preventDefault();
						var number = $(this).attr('id');
						var station = $(this).val();
						
						$.ajax({
							type: 'get',
							url: '{{ url("graphics/printer_config") }}',
							data: 'number=' + number + '&station=' + station,
							success: function (response) {
								if (response != 'success') {
									alert(response);
								}
							},
							failure: function (response) {
								alert(response);
							}
						});

					});
					
	      });


				function httpGet(theUrl)
				{
					var xmlHttp = new XMLHttpRequest();
					xmlHttp.open( "GET", theUrl, false ); // false for synchronous request
					xmlHttp.send( null );
					console.log(xmlHttp.responseText)
					return xmlHttp.responseText;
				}
	    </script>
			
</body>
</html>