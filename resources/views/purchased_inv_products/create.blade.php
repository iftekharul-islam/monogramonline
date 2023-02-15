<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Create Purchase Product</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/chosen.min.css">
		
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/chosen.jquery.min.js"></script>
</head>
<body>
	@include('includes.header_menu')
	<div class = "container">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "{{url('/purchases/purchasedinvproducts')}}">Purchase Products</a></li>
			<li class = "active">Create Purchase Product</li>
		</ol>

		@include('includes.error_div')

		{!! Form::open(['url' => url('/purchases/purchasedinvproducts'), 'method' => 'post', 'files' => true,'class'=>'form-horizontal','role'=>'form']) !!}
		<div class = 'form-group'>
			{!!Form::label('stock_no','Stock No #:',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = 'col-xs-5'>
				{!! Form::hidden('stock_no', old('stock_no'), ['id' => 'stock_no','class' => 'form-control']) !!}
				{!! Form::select('stock_number', $stock_number, null, ['class'=> 'form-control chosen', 'id' => 'stock_number']) !!}				
			</div>
			<div class = 'col-xs-2'>
				<a class = "btn btn-success btn-sm pull-left" href = "{{url(sprintf('%s','inventories/create'))}}" target = "_blank">Create New Stock</a>
			</div>
		</div>
		<div class = 'form-group'>
			{!!Form::label('stock_name_discription','Name / Discription :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::text('stock_name_discription', null, ['id' => 'stock_name_discription','class'=>'form-control' , 'readonly']) !!}
			</div>
			
		</div>

		<div class = 'form-group'>
			{!!Form::label('sku_weight','Sku Weight: ',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::text('sku_weight', null, ['id' => 'sku_weight','class'=>'form-control', 'readonly']) !!}
			</div>
		</div>
		<div class = 'form-group'>
			{!!Form::label('re_order_qty','Re-order QTY: ',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::text('re_order_qty', null, ['id' => 're_order_qty','class'=>'form-control', 'readonly']) !!}
			</div>
		</div>
		<div class = 'form-group'>
			{!!Form::label('min_reorder','Min Reorder: ',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::text('min_reorder', null, ['id' => 'min_reorder','class'=>'form-control', 'readonly']) !!}
			</div>
		</div>
		<div class = 'form-group'>
			{!!Form::label('adjustment','Adjustment: ',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::text('adjustment', null, ['id' => 'adjustment','class'=>'form-control', 'readonly']) !!}
			</div>
		</div>
		
		<div class = 'form-group'>
			{!!Form::label('unit','Unit :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-2">
				{!! Form::select('unit', $units, null, ['id' => 'unit','class'=>'form-control']) !!}
			</div>
			<div class = "col-xs-1">
				{!!Form::label('unit_qty','QTY:',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			</div>
			<div class = "col-xs-2">
				{!! Form::text('unit_qty', 1, ['id' => 'unit_qty','class'=>'form-control']) !!}
			</div>
		</div>
		<div class = 'form-group'>
			{!!Form::label('unit_price','Unit Price: ',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::text('unit_price', null, ['id' => 'unit_price','class'=>'form-control']) !!}
			</div>
		</div>
		<hr size = "100%">

		<div class = 'form-group'>
			{!!Form::label('vendor_id','Vendor Id: ',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::select('vendor_id', $vendors, null, ['class'=> 'form-control', 'id' => 'vendor_id']) !!}
			</div>
		</div>
		<div class = 'form-group'>
			{!!Form::label('vendor_sku','Vendor Sku: ',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::text('vendor_sku', null, ['id' => 'vendor_sku','class'=>'form-control']) !!}
			</div>
		</div>
		<div class = 'form-group'>
			{!!Form::label('vendor_sku_name','Sku Name: ',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::text('vendor_sku_name', null, ['id' => 'vendor_sku_name','class'=>'form-control']) !!}
			</div>
		</div>
		<div class = 'form-group'>
			{!!Form::label('lead_time_days','Lead Time Days: ',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::text('lead_time_days', null, ['id' => 'lead_time_days','class'=>'form-control']) !!}
			</div>
		</div>
		<div class = 'form-group'>
			<div class = "col-xs-offset-4 col-xs-5">
				{!! Form::submit('Create Purchase Product',['class'=>'btn btn-primary btn-block']) !!}
			</div>
		</div>
		{!! Form::close() !!}
	</div>

	<script type = "text/javascript">
		$("#stock_number").chosen();
		$("#vendor_id").chosen();

		$("#stock_number").chosen().change(function (event) {
			$("#stock_no").val($(event.target).val());
// 			alert("XXXXXXXXXXXXXxx");

			//stock_no= $("#stock_number").val();
			stock_no = $(event.target).val();

			if ( stock_no === "Select a Stock Number" ) {
				return false;
			}

			token = $('input[name=_token]').val();
			//console.log(token);
			route = '/purchases/getuniquestock';

			$.ajax({
				url: route,
				headers: {'X-CSRF-TOKEN': token},
				type: 'POST',
				dataType: 'json',
				data: {data: stock_no},
				success: function (response)
				{
		console.log(response);
					$("#stock_name_discription").val(response.stock_name_discription);
					$("#sku_weight").val(response.sku_weight);
					$("#re_order_qty").val(response.re_order_qty);
					$("#min_reorder").val(response.min_reorder);
					$("#adjustment").val(response.adjustment);

					$("#unit").val(response.unit);
					$("#unit_price").val(response.unit_price);
					$("#vendor_id").val(response.vendor_id);
					$("#vendor_sku").val(response.vendor_sku);
					$("#vendor_sku_name").val(response.vendor_sku_name);
					$("#lead_time_days").val(response.lead_time_days);
				}
			});

		});
		
// 		$("#stock_no").on('change', function (event)
// 		{
// 			var message = {
// 				delete: 'Are you sure?\nAdd new Stock Number?',
// 			};

// 			var action = confirm(message.delete);
// 			if ( action ) {

// 				$(".chosen-single").closest("div").find("span").text("Select a Stock Number");
// 			}

// 		});



		$("#stock_number").on('click', function (event)
		{

// 			//stock_no= $("#stock_number").val();
// 			stock_no = $(".chosen-single").closest("div").find("span").text();

// 			if ( stock_no === "Select a Stock Number" ) {
// 				return false;
// 			}
// 						alert(stock_no);

// 			token = $('input[name=_token]').val();
// 			//console.log(token);
// 			route = '/inventories/getuniquestock';

// 			$.ajax({
// 				url: route,
// 				headers: {'X-CSRF-TOKEN': token},
// 				type: 'POST',
// 				dataType: 'json',
// 				data: {data: stock_no},
// 				success: function (response)
// 				{
// 					$("#stock_name_discription").val(response.stock_name_discription);
// 					$("#sku_weight").val(response.sku_weight);
// 					$("#re_order_qty").val(response.re_order_qty);
// 					$("#min_reorder").val(response.min_reorder);
// 					$("#adjustment").val(response.adjustment);

// 					$("#unit").val(response.unit);
// 					$("#unit_price").val(response.unit_price);
// 					$("#vendor_id").val(response.vendor_id);
// 					$("#vendor_sku").val(response.vendor_sku);
// 					$("#vendor_sku_name").val(response.vendor_sku_name);
// 					$("#lead_time_days").val(response.lead_time_days);
// 				}
// 			});

		});


	</script>
</body>
</html>