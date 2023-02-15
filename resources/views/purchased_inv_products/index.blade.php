<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Purchase Products</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
</head>
<body>
	@include('includes.header_menu')
	<div class = "container">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li class = "active">Purchase Products</li>
		</ol>

		@include('includes.error_div')
		@include('includes.success_div')

		<div class = "col-xs-12">
			{!! Form::open(['method' => 'get', 'url' => url('/purchases/purchasedinvproducts'), 'id' => 'search-in-purchased-inventory-products']) !!}
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
			{!! Form::close() !!}
			
			<div class = "form-group col-xs-3">
				<label for = "" class = ""></label>
				<a class = "btn btn-success form-control"
				   href = "{{url('/purchases/purchasedinvproducts/create')}}">Create Purchase Product</a>
			</div>
		</div>

		@if(count($purchasedInvProducts) > 0)
			<h3 class = "page-header">
				Purchase Products
			</h3>
			<table class = "table table-bordered table-striped">
				<tr>
					<th>Stock</th>
					<th width="150">Price</th>
					<th>Vendor Info</th>
					<th width="90">Lead Time</th>
					<th>Action</th>
				</tr>
				@foreach($purchasedInvProducts as $purchasedInvProduct)
					<tr data-id = "{{$purchasedInvProduct->id}}">
						<td>
							@if (count($purchasedInvProduct->purchasedInvProduct_details) > 0)
								<a href="/inventories?operator_first=equals&search_in_first=stock_no_unique&search_for_first={{ $purchasedInvProduct->stock_no }}"
									target="_blank">{{ $purchasedInvProduct->stock_no }}</a>
							@else 
								{{ $purchasedInvProduct->stock_no }}
							@endif
						</td>
						<td align="right">${{ number_format($purchasedInvProduct->unit_price,2) }}</td>
						<td>
							{{ $purchasedInvProduct->vendor->vendor_name }}
							:
							{{ $purchasedInvProduct->vendor_sku }}
						</td>
						<td align="center">{{ $purchasedInvProduct->lead_time_days }}</td>
						<td align="center">
							{{-- <a href = "{{ url(sprintf("/purchases/purchasedinvproducts/%d", $purchasedInvProduct->id)) }}" data-toggle = "tooltip"
							   data-placement = "top"
							   title = "View this vendor"><i class = 'fa fa-eye text-primary'></i></a> --}}
							<a href = "{{ url(sprintf("/purchases/purchasedinvproducts/%d/edit", $purchasedInvProduct->id)) }}"
							   data-toggle = "tooltip"
							   data-placement = "top"
							   title = "View this vendor"><i class = 'glyphicon glyphicon-pencil text-success'></i></a>
							| <a href = "#" class = "delete" data-toggle = "tooltip" data-placement = "top"
							     title = "Delete this vendor"><i class = 'glyphicon glyphicon-remove text-danger'></i></a>
						</td>
					</tr>
					<tr>
						<td>
							@if(count($purchasedInvProduct->purchasedInvProduct_details) > 0)
								@foreach($purchasedInvProduct->purchasedInvProduct_details as $inventory)
									{{ $inventory->stock_name_discription }}
								@endforeach
							@else
								Stock Number Not Found
							@endif
						</td>
						<td align="right">{{ $purchasedInvProduct->unit_qty }} - {{ $units[$purchasedInvProduct->unit] }}</td>
						<td>{{ $purchasedInvProduct->vendor_sku_name }}</td>
						<td colspan=2></td>
					</tr>
				@endforeach
			</table>
			{!! Form::open(['url' => url('/purchases/purchasedinvproducts/id'), 'method' => 'delete', 'id' => 'delete-purchasedinvproducts']) !!}
			{!! Form::close() !!}
			<div class = "col-xs-12 text-center">
				{!! $purchasedInvProducts->render() !!}
			</div>
		@else
			<div class = "col-xs-12">
				<div class = "alert alert-warning">
					No Purchase Products found.
				</div>
			</div>
		@endif
	</div>
	
	<script type = "text/javascript">
		$(function ()
		{
			$('[data-toggle="tooltip"]').tooltip();
			$("#reset").click(function(event){
				$("#search_for").val("");
				$("#search_in").val($("#search_in option:first").val());
				$(this).closest('form').submit();
			});
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
				var form = $("form#delete-purchasedinvproducts");
				var url = form.attr('action');
				form.attr('action', url.replace('id', id));
				form.submit();
			}
		});
	</script>
</body>
</html>