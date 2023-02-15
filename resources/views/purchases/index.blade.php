<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Purchase Orders</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
</head>
<body>
	@include('includes.header_menu')
	<div class = "container" style="min-width: 1200px;">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li class = "active"><a href = "{{url('/purchases')}}">Purchase Orders</a></li>
		</ol>

		@include('includes.error_div')
		@include('includes.success_div')

		<div class = "col-xs-12">
			{!! Form::open(['method' => 'get', 'url' => url('purchases'), 'id' => 'search-in-purchases']) !!}
			<div class = "form-group col-xs-4">
				<label for = "search_for">Search for</label>
				{!! Form::text('search_for', $request->get('search_for'), ['id'=>'search_for', 'class' => 'form-control', 'placeholder' => 'Search for']) !!}
			</div>
			<div class = "form-group col-xs-3">
				<label for = "search_in">Search in</label>
				{!! Form::select('search_in', $search_in, $request->get('search_in'), ['id'=>'search_in', 'class' => 'form-control']) !!}
			</div>
			<div class = "form-group col-xs-2">
				<label for = "" class = ""></label>
				{!! Form::submit('Search', ['id'=>'search', 'style' => 'margin-top: 2px;', 'class' => 'btn btn-primary form-control']) !!}
			</div>
			<div class = "form-group col-xs-1">
			</div>
			{!! Form::close() !!}
			<div class = "form-group col-xs-2">
				<label for = "" class = ""></label>
				<a class = "btn btn-success form-control" href = "{{url('/purchases/create')}}" style='margin-top: 2px;'>New Purchase</a>
			</div>
		</div>
		
		<br><br><br><br><br>
		
		<ul id="myTab" class="nav nav-tabs">
			<li @if($tab == 'open') class="active" @endif><a href="#open" data-toggle="tab">Open Purchase Orders ({!! count($open_purchases) !!})</a></li>
			<li @if($tab == 'closed') class="active" @endif><a href="#closed" data-toggle="tab">Closed Purchase Orders</a></li>
		</ul>
		
		<br><br>
		
		<div id="tabContent" class="tab-content">
			
			<div class="tab-pane fade @if($tab == 'open') in active @endif" id="open">
						@if(count($open_purchases) > 0)
							
							<table class = "table table-bordered">
								<tr>
									<th width=150>Purchase Order</th>
									<th width=100>Date</th>
									<th>Vendor</th>
									<th>Products</th>
									<th>Balance</th>
									<th>Tracking</th>
									<th width=150>Action</th>
								</tr>
								
								@foreach($open_purchases as $purchase)
									<tr data-id = "{{$purchase->id}}">
										<td>
											<a href = "{{ url(sprintf("/purchases/%s", $purchase->po_number)) }}"> {{ $purchase->po_number }}</a>
										</td>
										<td>{{ substr($purchase->created_at, 5, 5) }}</td>
										<td>
											<a href = "{{ url(sprintf("/purchases/vendors/%s", $purchase->vendor_id)) }}">
												{{ $purchase->vendor_details->vendor_name }}
											</a>
										</td>
										<td>{{ $purchase->products->sum('quantity') }}</td>
										<td>
												{{ $purchase->products->sum('balance_quantity') }}
										</td>
										<td>
											@if(strlen($purchase->tracking) > 0)
												<a href="{!! Monogram\Helper::getTrackingUrl($purchase->tracking) !!}" target="_blank">{{ $purchase->tracking }}</a>
											@endif
										</td>
										<td>
											<a href = "{{ url(sprintf("/purchases/%s/edit", $purchase->po_number)) }}"
											     data-toggle = "tooltip"
											     data-placement = "top"
											     title = "Edit this purchase"><i class = 'glyphicon glyphicon-pencil text-primary'></i></a>
											| <a href = "{{ url(sprintf("/purchases/receive?po_number=%s", $purchase->po_number)) }}"
											     data-toggle = "tooltip"
											     data-placement = "top"
											     title = "Receive this purchase"><i class = 'glyphicon glyphicon-inbox text-primary'></i></a>
											| <a href = "#" class = "delete" data-toggle = "tooltip" data-placement = "top"
											     title = "Delete this purchase"><i class = 'glyphicon glyphicon-remove text-primary'></i></a>
											| <a href = "{{url(sprintf("/purchases/print/%d", $purchase->id))}}" class = "print"
											     data-toggle = "tooltip" data-placement = "top"
											     title = "Print purchase slip"><i class = 'glyphicon glyphicon-print text-primary'></i></a>
											| {!! \App\Task::widget('App\Purchase', $purchase->id, null, 10); !!}
										</td>
									</tr>
								@endforeach
							</table>
							{!! Form::open(['url' => url('/purchases/id'), 'method' => 'delete', 'id' => 'delete-purchase']) !!}
							{!! Form::close() !!}
						@else
							<div class = "col-xs-12">
								<div class = "alert alert-warning">
									No open purchase orders found.
								</div>
							</div>
						@endif
		</div>
	
		<div class="ttab-pane fade @if($tab == 'closed') in active @endif" id="closed">
						
						@if(count($closed_purchases) > 0)
							
							<table class = "table table-bordered">
								<tr>
									<th>#</th>
									<th>Added on</th>
									<th>Vendor name</th>
									<th>Products</th>
									<th>Balance</th>
									<th>Tracking</th>
									<th>Action</th>
								</tr>
								
								@setvar($count = 1)
								
								@foreach($closed_purchases as $purchase)
									<tr data-id = "{{$purchase->id}}">
										<td>{{ $count++ }}) <a
													href = "{{ url(sprintf("/purchases/%s", $purchase->po_number)) }}"> {{ $purchase->po_number }}</a>
										</td>
										<td>{{ substr($purchase->created_at, 0, 10) }}</td>
										<td>{{ substr($purchase->vendor_details->vendor_name , 0, 30) }}</td>
										<td>{{ $purchase->products->count() }}</td>
										<td>
												{{ $purchase->products->sum('balance_quantity') }}
										</td>
										<td>
											@if(strlen($purchase->tracking) > 0)
												<a href="{!! Monogram\Helper::getTrackingUrl($purchase->tracking) !!}" tracget="_blank">{{ $purchase->tracking }}</a>
											@endif
										</td>
										<td>
											<a href = "{{url(sprintf("/purchases/print/%d", $purchase->id))}}" class = "print"
													 data-toggle = "tooltip" data-placement = "top"
													 title = "Print purchase slip"><i class = 'glyphicon glyphicon-print text-primary'></i></a>
										</td>
									</tr>
								@endforeach
							</table>
						
						<div class = "col-xs-12 text-center">
							{!! $closed_purchases->render() !!}
						</div>
						@else
							<div class = "col-xs-12">
								<div class = "alert alert-warning">
									No closed purchase orders found.
								</div>
							</div>
						@endif
		</div>

	<script type = "text/javascript">
		$(function ()
		{
			$('[data-toggle="tooltip"]').tooltip();
	
		});
		
		var message = {
			delete: 'Are you sure you want to delete?',
		};
		
		$("a.delete").on('click', function (event)
		{
			event.preventDefault();
			var id = $(this).closest('tr').attr('data-id');
			var action = confirm(message.delete);
			if ( action ) {
				var form = $("form#delete-purchase");
				var url = form.attr('action');
				form.attr('action', url.replace('id', id));
				form.submit();
			}
		});
	</script>
</body>
</html>