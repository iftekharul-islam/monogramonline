<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Edit {{$purchasedInvProducts->name}}</title>
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
			<li class = "active">Edit Purchase Product</li>
		</ol>

		@include('includes.error_div')
		@include('includes.success_div')

		{!! Form::open(['url' => url(sprintf("/purchases/purchasedinvproducts/%d", $purchasedInvProducts->id)), 'method' => 'put', 'files' => true,'class'=>'form-horizontal','role'=>'form']) !!}
		<div class = 'form-group'>
			{!!Form::label('stock_no','Stock No #:',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = 'col-xs-5'>
				{!! Form::text('stock_no', $purchasedInvProducts->stock_no, ['id' => 'stock_no', 'readonly','class' => 'form-control']) !!}
			</div>
		</div>
		<div class = 'form-group'>
			{!!Form::label('stock_name_discription','Name / Discription :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				@if (count($inventorie) > 0)
					{!! Form::text('stock_name_discription', $inventorie[0]->stock_name_discription , ['id' => 'stock_name_discription','class'=>'form-control', 'disabled' => true]) !!}
				@else
					Stock number not found
				@endif
			</div>
		</div>
		<div class = 'form-group'>
			{!!Form::label('unit','Unit :',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-2">
				{!! Form::select('unit', $units, $purchasedInvProducts->unit, ['id' => 'unit','class'=>'form-control']) !!}
			</div>
			<div class = "col-xs-1">
				{!!Form::label('unit_qty','QTY:',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			</div>
			<div class = "col-xs-2">
				{!! Form::text('unit_qty', $purchasedInvProducts->unit_qty, ['id' => 'unit_qty','class'=>'form-control']) !!}
			</div>
		</div>
		<div class = 'form-group'>
			{!!Form::label('unit_price','Unit Price: ',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::text('unit_price', $purchasedInvProducts->unit_price, ['id' => 'unit_price','class'=>'form-control']) !!}
			</div>
		</div>

		<hr size="100%">
		<div class = 'form-group'>
			{!!Form::label('vendor_id','Vendor Id: ',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{{-- {!! Form::text('vendor_id', $purchasedInvProducts->vendor_id, ['id' => 'vendor_id','class'=>'form-control']) !!} --}}
				{!! Form::select('vendor_id', $vendors, $purchasedInvProducts->vendor_id, ['class'=> 'form-control', 'id' => 'vendor_id']) !!}
			</div>
			
		</div>

		<div class = 'form-group'>
			{!!Form::label('vendor_sku','Vendor Sku: ',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::text('vendor_sku', $purchasedInvProducts->vendor_sku, ['id' => 'vendor_sku','class'=>'form-control']) !!}
			</div>
		</div>

		<div class = 'form-group'>
			{!!Form::label('vendor_sku_name','Sku Name: ',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::text('vendor_sku_name',  $purchasedInvProducts->vendor_sku_name, ['id' => 'vendor_sku_name','class'=>'form-control']) !!}
			</div>
		</div>

		<div class = 'form-group'>
			{!!Form::label('lead_time_days','Lead Time Days: ',['class'=>'control-label col-xs-offset-2 col-xs-2'])!!}
			<div class = "col-xs-5">
				{!! Form::text('lead_time_days', $purchasedInvProducts->lead_time_days, ['id' => 'lead_time_days','class'=>'form-control']) !!}
			</div>
		</div>

		<div class = 'form-group'>
			<div class = "col-xs-offset-4 col-xs-5">
				{!! Form::submit('Edit Purchase Product',['class'=>'btn btn-primary btn-block']) !!}
			</div>
		</div>
		{!! Form::close() !!}

	</div>

	<script type = "text/javascript">
		$("#vendor_id").chosen();
	</script>		
</body>
</html>