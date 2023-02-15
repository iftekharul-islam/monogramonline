<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Stock Report</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	<link rel="stylesheet" href="/assets/css/chosen.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/chosen.jquery.min.js"></script>
	
	<style>
	.chosen-container-single .chosen-single {
	    height: 33px;
	    border-radius: 3px;
	    border: 1px solid #CCCCCC;
	}
	.chosen-container-single .chosen-single span {
	    padding-top: 2px;
	}
	.chosen-container-single .chosen-single div b {
	    margin-top: 2px;
	}
	</style>

</head>
<body>
	@include('includes.header_menu')
	<div class = "container">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "{{url('/prod_report/stockreport')}}">Stock Report</a></li>
		</ol>

		<h3 class = "page-header">Current Stock Required</h3>

			<div class = "col-xs-12">
				<div class="row">
					{!! Form::open(['method' => 'get', 'url' => url('prod_report/stockreport'), 'id' => 'filter-stockreport']) !!}
					<div class = "form-group col-xs-5">
						{!! Form::select('station_id', $station_option, $request->get('station_id'), ['class'=> 'form-control', 'id'=>'station_id']) !!}
					</div>
					<div class = "form-group col-xs-3">
						{!! Form::submit('Filter by Station', ['id'=>'search', 'style' => 'margin-top: 0px;', 'class' => 'btn btn-primary form-control']) !!}
					</div>
					{!! Form::close() !!}
				</div>
			</div>


		@if(count($item) > 0)
		
			<table id="stock_table" class="table table-striped" cellspacing="0" cellpadding="0">
				<thead>
				<tr>
					<th width="300">Station</th>
					<th></th>
					<th colspan=2>Stock #</th>
					<th>Description</th>
					<th>Bin</th>
					<th>Quantity</th>
				</tr>
			</thead>
	    <tbody>
			
			@foreach($item as $row)
			
				<tr height="30">
					<td>{{$row['station_name']}} - {{$row['station_description']}}</td>
					<td>
						@if ($row['stock_no_unique'] != 'ToBeAssigned' && $row['warehouse'] != NULL)
							<img  border = "0" height="30" src = "{{ $row['warehouse'] }}" />
						@else
							<img  border = "0" height="30" src = "{{ $row['item_thumb'] }}" />
						@endif
					</td>
					<td>
						@if ($row['stock_no_unique'] != 'ToBeAssigned')
							<a href="{{url(sprintf("/inventories?search_for_first=%s&search_in_first=stock_no_unique", $row['stock_no_unique']))}}">
								{{$row['stock_no_unique']}}
							</a>
						@else 
							<span style="color:red;">{{ $row['item_code'] }}</span>
						@endif
					</td>
					<td width="40">
						@if ($row['stock_no_unique'] != 'ToBeAssigned')
							{!! \App\Task::widget('App\Inventory', $row['inv_id'], 'text-primary', 11); !!}
						@endif
					</td>
					<td>{{$row['stock_name_discription']}}</td>
					<td>{{$row['wh_bin']}}</td>
					<td align="right">{{$row['total']}}</td>
					
			@endforeach
      
			</tbody>
    	</table>

		@else
			<div class = "col-xs-12">
				<div class = "alert alert-warning">
					No stock required
				</div>
			</div>
		@endif
	</div>
	
	<script type = "text/javascript">
		$("#station_id").chosen();
	</script>
</body>
</html>