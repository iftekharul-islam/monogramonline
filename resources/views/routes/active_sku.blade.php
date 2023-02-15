<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Active SKU</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet"
	      href = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
	<link type = "text/css" rel = "stylesheet"
	      href = "//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.9.3/css/bootstrap-select.min.css">
	<link type = "text/css" rel = "stylesheet"
	      href = "//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
	<style>
	</style>
</head>
<body>
	@include('includes.header_menu')
	<div class = "container">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li class = "active">Active SKU</li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		<div class = "col-xs-12">
			@if(count($rows))
				<table class = "table">
					<tr>
						<th>Batch#</th>
						<th>SKU</th>
						<th>Name</th>
						<th>Quantity</th>
						<th>Min-Order date</th>
					</tr>
					@foreach($rows as $row)
						<tr>
							<td>{{ $row['batch_number'] }}</td>
							<td>{{ $row['sku'] }}</td>
							<td>{{ $row['item_name'] }}</td>
							<td>{{ $row['item_count'] }}</td>
							<td>{{ $row['min_order_date'] }}</td>
						</tr>
					@endforeach
					<tr>
						<td></td>
						<td></td>
						<td></td>
						<td>{{ $total }}</td>
						<td></td>
						<td></td>
					</tr>
				</table>
			@else
				<div class = "alert alert-warning">No row found.</div>
			@endif
		</div>
	</div>
	<script type = "text/javascript" src = "//code.jquery.com/jquery-1.11.3.min.js"></script>
	<script type = "text/javascript" src = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script type = "text/javascript">
		$(function ()
		{
			$('[data-toggle="tooltip"]').tooltip();
		});
		$("button#print_batches").on('click', function (event)
		{
			var url = "{{ url('/prints/batches') }}";
			setFormUrlAndSubmit(url);
		});
		$("button#packing_slip").on('click', function (event)
		{
			var url = "{{ url('/prints/batch_packing') }}";
			setFormUrlAndSubmit(url);
		});

		function setFormUrlAndSubmit (url)
		{
			var form = $("form#batch_list_form");
			$(form).attr('action', url);
			$(form).submit();
		}
		var state = false;

		$("button#select_deselect").on('click', function ()
		{
			state = !state;
			$(".checkbox").prop('checked', state);
		});
	</script>
</body>
</html>
