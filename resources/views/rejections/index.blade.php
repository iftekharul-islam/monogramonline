<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Rejects</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">

	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>

	<style>

		td {
			word-wrap:break-word
		}

		.noline {
			border-bottom:hidden;
		}

		.divline {
			border-left:1px solid lightgray;
			white-space: pre-wrap;
		}

		.tooltip-inner {
    	white-space: nowrap;
		}
	</style>

	@if (isset($label) && $label != null)
		@include('prints.includes.label')
	@endif

</head>
<body>
	@include('includes.header_menu')
	<div class = "container" style="width:95%;">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li class = "active"><a href = "{{url('/rejections')}}">Rejects</a></li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')

		<div class = "col-xs-12">

			<h3 class="page-header">Process Rejects </h3>

			<div class="row">
			{!! Form::open(['method' => 'get', 'id' => 'barcode_form']) !!}
				<div class = "form-group col-xs-2">
					{!! Form::text('batch_number', $request->get('batch_number', null), ['id'=>'batch_number', 'class' => 'form-control', 'onchange' => 'return false;', 'placeholder' => 'Batch Number']) !!}
				</div>
				<div class = "form-group col-xs-2">
					{!! Form::select('section', $sections, $request->get('section', null), ['id'=>'section', 'class' => 'form-control', 'onchange' => 'return false;']) !!}
				</div>
				<div class = "form-group col-xs-2">
					{!! Form::select('graphic_status', $graphic_statuses, $request->get('graphic_status', null), ['id'=>'graphic_status', 'class' => 'form-control', 'onchange' => 'return false;']) !!}
				</div>
				<div class = "form-group col-xs-3">
					{!! Form::select('reason', $reasons, $request->get('reason', null), ['id'=>'reason', 'class' => 'form-control', 'onchange' => 'return false;']) !!}
				</div>
				<div class = "form-group col-xs-1">
					{!! Form::submit('Filter', ['class' => 'btn btn-primary']) !!}
				</div>
				<div class = "form-group col-xs-2">
					{!! Form::button('Send All to First Station', ['id'=>'tostart', 'class' => 'btn btn-success']) !!}
				</div>
			{!! Form::close() !!}
			</div>

		@if (isset($summary) && count($summary) > 0)

		<div class="col-md-6 col-xs-12">
			<br>
			<table class="table table-bordered">
				<thead>
					<tr bgcolor="#dae6e6">
						<th width="400">Type</th>
						<th width="400">Reason</th>
						<th width="200" style="text-align:right;">Count</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($summary as $line)
						<tr>
							<td>
								@if (isset($graphic_statuses[$line->graphic_status]))
									{{ $graphic_statuses[$line->graphic_status] }}
								@endif
							</td>
							<td>
								@if (isset($reasons[$line->rejection_reason]))
									{{ $reasons[$line->rejection_reason] }}
								@endif
							</td>
							<td align="right">
								<a href="{{ url(sprintf('/rejections?graphic_status=%d&reason=%d', $line->graphic_status, $line->rejection_reason)) }}">
								{{ $line->count }}
								</a>
							</td>
						</tr>
					@endforeach
					<tr bgcolor="#dae6e6">
						<th colspan=2  style="text-align:right;">Total:</th>
						<th style="text-align:right;">{{ $summary->sum('count') }}</th>
					</tr>
				</tbody>
			</table>
		</div>

		@elseif (count($batch_array) > 0)

			{!! Form::open(['url' => '/rejections/send_to_start', 'method' => 'post', 'id' => 'tostart_form']) !!}
			{!! Form::hidden('batches', implode(',', array_keys($batch_array))) !!}
			{!! Form::hidden('graphic_status', $request->get('graphic_status'))!!}
			{!! Form::hidden('section', $request->get('section'))!!}
			{!! Form::hidden('batch_number', $request->get('batch_number'))!!}
			{!! Form::close() !!}

				<h4>{{ $total_items }} {!! $total_items == 1 ? 'Reject' : 'Rejects' !!} found in {{ count($batch_array) }}  {!! count($batch_array) == 1 ? 'Batch' : 'Batches' !!}</h4>

				<table class = "table">
					<tbody>

					@foreach ($batch_array as $batch => $array)

							{!! Form::open(['url' => url('/rejections/process'), 'method' => 'post', 'id' => 'form-process-' . $batch]) !!}

							{!! Form::hidden('batch_number', $batch, ['id' => 'batch_number']) !!}

							<tr><td colspan="6"></td></tr>

							<tr>
								<td rowspan="{!! count($array['items']) !!}"  bgcolor="#cbdcdc" class="noline">
									<a href = "{{url(sprintf('/batches/details/%s', $batch)) }}"
											 target = "_blank">{{ $batch }}</a>
									<div class="pull-right">
										@if ($array['summaries'] < 1)
											<a href = "{{url(sprintf('/summaries/single?batch_number=%s', $batch)) }}" target = "_blank"
													 data-toggle = "tooltip" data-placement = "top"
													 title = "Print Summary"><i class = 'glyphicon glyphicon-print text-primary'></i></a>
										@else
											<span data-toggle = "tooltip" data-placement = "top"
													 title = "Summary Printed"><i class = 'glyphicon glyphicon-print text-muted'></i></span>
										@endif
										|
										{!! \App\Task::widget('App\Batch', $array['id'], 'text-primary', 12); !!}
									</div>
									<br><br><br>
									{!! Form::select('station_change', $destinations, 'all', ['id'=>'station_change', 'class' => 'chosen_txt form-control']) !!}
									{!! Form::hidden('graphic_status', $request->get('graphic_status', null))!!}
									{!! Form::hidden('section', $request->get('section', null))!!}
									{!! Form::hidden('reason', $request->get('reason', null))!!}
									<br>
									{!! Form::submit('Update Batch ' . $batch, ['id'=>'process-' . $batch, 'class' => 'btn btn-primary btn-sm form-control']) !!}
								</td>

							@foreach($array['items'] as $item)

									<td bgcolor="#dae6e6">
													<a href = "{{url(sprintf('/orders/details/%s', $item->order->id))}}"
															 target = "_blank">{{ $item->order->short_order }}</a>
										<br>
										{{substr($item->order->order_date, 0, 10)}}
										<br>
											Item: {{$item->id }}
										<br>
										@if ($item->item_quantity > 1)
											<strong style="font-size: 125%;">QTY: {{ $item->item_quantity }}</strong>
										@endif
										<br>
										@if (count($item->rejections) > 1)
											<br>
											<strong style="color:red;">Rejected {!! count($item->rejections) !!} Times</strong>
											<br>
										@endif
										<br>
										@if($item->rejection)
											<a href="{{url(sprintf('/rejections/reprint?id=%d', $item->rejection->id))}}" class="btn btn-xs">
												Reprint Label</a>
										@endif
										<br>
										@if (count($array['items']) > 1)
											<a href="{{url(sprintf('/rejections/split?item_id=%d&batch_number=%s', $item->id, $item->batch_number))}}" class="btn btn-xs">
												New Batch</a>
										@endif
									</td>


									<td>
										<a href = "{{ $item->item_url }}" target = "_blank">
										<img src = "{{ $item->item_thumb }}" width="90" height="90"></a>
									</td>

									<td width=45%>
											{{ $item->item_description }}
											<br>
											SKU: {{ $item->child_sku }}
											<br>
											<br>
											@if($item->rejection)
												<strong>{{ $item->rejection->graphic_status }}:</strong>
												@if ($item->rejection->rejection_reason_info)
													{{ $item->rejection->rejection_reason_info->rejection_message }}
													<br>
												@endif
												@if (strlen($item->rejection->rejection_message) > 0)
													<strong>Note:</strong>{{ $item->rejection->rejection_message }}
													<br>
												@endif
												<strong>Rejected:</strong> {{ $item->rejection->created_at }}
													@if ($item->rejection->from_station)
														from {{ $item->rejection->from_station->station_name }}
													@endif
													@if ($item->rejection->user)
														by {{ $item->rejection->user->username }}
													@endif

												@if($item->rejection->supervisor_message)
													<br><strong>Supervisor:</strong>{{ $item->rejection->supervisor_message }} <br>
												@endif

												{!! Form::text('supervisor_message['  . $item->rejection->id . ']', null, ['class' => 'supervisor_message form-control', 'style' => 'min-width: 200px;', 'placeholder' => 'Enter a message']) !!}
											@else
												- Reject information not found -
											@endif
									</td>
									<td colspan=2 class="divline">{{ \Monogram\Helper::jsonTransformer($item->item_option) }}</td>
								</tr>
							@endforeach

							{!! Form::close() !!}
					@endforeach
					</tbody>
				</table>

		@else
				<div class = "alert alert-warning text-center">No Rejects Found</div>
		@endif
	</div>

	<script type = "text/javascript">


		$("#tostart").click(function() {
				var action = confirm("Are you sure you want to move all {{ count($batch_array) }} batches shown to the first station?");
				if ( action ) {
					$("#tostart_form").submit();
				}
		 });


	</script>
</body>
</html>