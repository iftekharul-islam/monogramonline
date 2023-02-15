<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Print Summaries</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	
</head>
<body>
	@include('includes.header_menu')
		<div class = "container" style="width:95%;">
			<ol class = "breadcrumb">
				<li><a href = "{{url('/')}}">Home</a></li>
				<li class = "active">Print Summaries</li>
			</ol>
			@include('includes.error_div')
			@include('includes.success_div')
			
			<h4 class="page-header">Batches in Production</h4>
			  
			@if(count($production))
			  
			  <table id="summary_table" class="table">
			  <thead>
			    <tr>
			      <th>Department</th>
						<th></th>
			      <th style="width:400px;">Production Station</th>
			      <th>Summaries <br>to Print</th>
			      <th></th>
			    </tr>
			  </thead>
			   <tbody>
			    @foreach($production as $batch)
			      <tr>
			        <td>
			          @if (!empty($batch->section))
			            {{ $batch->section->section_name }}
			            @setvar($section = $batch->section_id)
			          @else 
			            No Section
			            @setvar($section = '')
			          @endif
								{{ $batch->type }}
			        </td>
							<td>
								@if ($batch->store)
									{{ $batch->store->store_name }}
								@endif
							</td>
			        <td>{{ $batch->production_station->station_name }} => {{ $batch->production_station->station_description }}</td>
			        <td>
			          <a href="{!! url(sprintf("/batches/list?printed=0&production_station=%d&station=%s&status=active&store_id=%s", 
			                                            $batch->production_station_id, isset($station) ? $station : '', $batch->store_id)) !!}" target="_blank">
			          {{ $batch->count }}
			          </a>
			        </td>
			        <td>
			        {!! Form::open(['url' => url('summaries/print'), 'method' => 'post', 'target' => '_blank', 'id' => 'batch_print_form', 'onsubmit' => "setTimeout(function () { window.location.reload(); }, 4000)"]) !!}
			        {!! Form::hidden('printed', '0') !!} 
			        {!! Form::hidden('section', $batch->section_id) !!}
							{!! Form::hidden('store', $batch->store_id) !!}
							{!! Form::hidden('type', $batch->type) !!}
			        {!! Form::hidden('production_station', $batch->production_station_id, ['id' => 'production_station']) !!}
			        {!! Form::submit('Print ' . $batch->count . ' for ' . $batch->production_station->station_name , ['id'=>'print', 'class' => 'btn btn-sm btn-primary']) !!}
			        {!! Form::close() !!}
			        </td>
			      </tr>
			    @endforeach
			    <tbody>
			  </table>
			@else
			  <div class = "col-xs-12">
			    <div class = "alert alert-warning">All production summaries printed</div>
			  </div>
			@endif

			<h4 class="page-header">Batches in Graphics</h4>
			  
			@if(count($graphics))
			  
			  <table id="summary_table" class="table">
			  <thead>
			    <tr>
			      <th style="width:400px;">Graphic Directory</th>
						<th></th>
			      <th>Summaries <br>to Print</th>
			      <th></th>
			    </tr>
			  </thead>
			   <tbody>
			    @foreach($graphics as $batch)
			      <tr>
			        <td>{{ $batch->graphic_dir }} {{ $batch->type }}</td>
							<td>
								@if ($batch->store)
									{{ $batch->store->store_name }}
								@endif
							</td>
			        <td>
			          <a href="{!! url(sprintf("/batches/list?printed=2&graphic_dir=%s&status=active&store_id=%s", $batch->graphic_dir, $batch->store_id)) !!}" target="_blank">
			          {{ $batch->count }}
			          </a>
			        </td>
			        <td>
			        {!! Form::open(['url' => url('summaries/print'), 'method' => 'post', 'target' => '_blank', 'id' => 'batch_print_form', 'onsubmit' => "setTimeout(function () { window.location.reload(); }, 4000)"]) !!} 
			        {!! Form::hidden('printed', '2') !!} 
							{!! Form::hidden('store', $batch->store_id) !!}
							{!! Form::hidden('type', $batch->type) !!}
			        {!! Form::hidden('graphic_dir', $batch->graphic_dir, ['id' => 'graphic_dir']) !!}
			        {!! Form::submit('Print ' . $batch->count . ' in ' . $batch->graphic_dir , ['id'=>'print', 'class' => 'btn btn-sm btn-primary']) !!}
			        {!! Form::close() !!}
			        </td>
			      </tr>
			    @endforeach
			    <tbody>
			  </table>
			@else
			  <div class = "col-xs-12">
			    <div class = "alert alert-warning">All graphics summaries printed</div>
			  </div>
			@endif

			<h4 class="page-header">Summaries printed today</h4>

			@if(count($today))				
			  <table id="printed_table" class="table">
			  <thead>
			    <tr>
			      <th>Department</th>
			      <th style="width:400px;">First Production Station</th>
			      <th>Summaries Printed</th>
			      <th>Printed By</th>
			      <th>Print Time</th>
			    </tr>
			  </thead>
			   <tbody>
			    @foreach($today as $batch)
			      <tr>
			        <td>{{ $batch->section->section_name }}</td>
			        <td>{{ $batch->production_station->station_name }} => {{ $batch->production_station->station_description }}</td>
			        <td>
			          <a href="{!! url(sprintf("/batches/list?printed=1&print_date=%s&printed_by=%d&production_station=%d&status=active", $batch->summary_date, $batch->summary_user_id, $batch->production_station->id)) !!}" target="_blank">
			          {{ $batch->count }}
			          </a>
			        </td>
			        <td>{{ $batch->summary_user->username }}</td>
			        <td>{{ $batch->summary_date }}</td>
			      </tr>
			    @endforeach
			    <tbody>
			  </table>
			@else
			  <div class = "alert alert-warning">No batch summaries printed today</div>
			@endif

			<br><br>
</body>
</html>