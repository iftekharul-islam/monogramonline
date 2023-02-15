<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Receive {{ $purchase->po_number }}</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/chosen.min.css">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/pikaday.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/moment.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/pikaday.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/chosen.jquery.min.js"></script>
	<style>
	input[type=number] {
		width: 90px;
	}
	</style>
</head>
<body>
	@include('includes.header_menu')
	<div class = "container"  style="min-width: 1200px;">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "{{url('/purchases')}}">Purchase Orders</a></li>
			<li class = "active"><a href = "{{url('/purchases/receive?po_number=' . $purchase->po_number)}}">Receive {{ $purchase->po_number }}</a></li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		{!! Form::open(['url' => url("/purchases/receive"), 'id' => 'receive', 'method' => 'put', 'files' => true,'class'=>'form-horizontal','role'=>'form']) !!}
		
  	<div style="">
  	<h3>Purchase Order {{ $purchase->po_number }}</h3>
  	{!! Form::hidden('po_number', $purchase->po_number, ['id' => 'po_number']) !!}
  	<br />
  	<table class='table'>
  		<tr>
  			<td width="10%"></td>
  			
  			<th width="12%">Vendor:</th>
  			<td width="30%">
  					{{ $purchase->vendor_details->vendor_name }}
  					{!! Form::hidden('vendor_name', $purchase->vendor_details->vendor_name, ['id' => 'vendor_name']) !!}
  			</td>

  			<th width="12%">Date:</th>			
  			<td>{{ $purchase->po_date }}
  						{!! Form::hidden('po_date', $purchase->po_date, ['id' => 'po_date']) !!}
  			</td>
  					
  			
  		</tr>
  		
  		<tr>
  			<td></td>
  			
  			<th>Phone: </th>
  			<td>{{ $purchase->vendor_details->phone_number }}
  					{!! Form::hidden('phone_number', $purchase->vendor_details->phone_number, ['id' => 'phone_number']) !!}
  			</td>

  			
  			<th>Vendor ID: </th>
  			<td>{{ $purchase->vendor_details->id }}
  					{!! Form::hidden('vendor_id', $purchase->vendor_details->id, ['id' => 'vendor_id']) !!}
  			</td>
  			
  		</tr>
  		
  		<tr>
  			<td></td>
  			
  			<th>Email: </th>
  			<td>{{ $purchase->vendor_details->email }}
  					{!! Form::hidden('email', $purchase->vendor_details->email, ['id' => 'email']) !!}
  			</td>
  			
  			<td colspan="2"></td>
  		</tr>
  	</table>
  	</div>
  	<br />
    
		{{-- Code for add item Dynamically --}}
		@setvar($i = 0)
		@if($purchase->products)
			<table class="table" cellspacing="0">
				<thead>
				<tr>
          <th class = 'col-xs-1'>Vendor SKU</th>
          <th class = 'col-xs-1'>Stock #</th>
          <th class = 'col-xs-3'>SKU</th>
          <th class = 'col-xs-1'>Quantity</th>
          <th class = 'col-xs-2'>Date Received</th>
          <th class = 'col-xs-1'>Quantity Received</th>
					<th class = 'col-xs-1'>Previously Received</th>
          <th class = 'col-xs-1'>Balance</th>
				</tr>
				</thead>
				<tbody>
			@foreach($purchase->products as $product)
				
					<tr class="collection">
							{!! Form::hidden("id[$i]", $product->id,  ['id' => "id"]) !!}
							{!! Form::hidden("purchase_id[$i]", $product->purchase_id,  ['id' => "purchase_id"]) !!}
							{!! Form::hidden("vendor_sku[$i]", $product->vendor_sku,  ['id' => "vendor_sku"]) !!}
							{!! Form::hidden("product_id[$i]", $product->product_id, ['id' => "product_id"]) !!}
							{!! Form::hidden("stock_no[$i]", $product->stock_no, ['id' => "stock_no"]) !!}
							{!! Form::hidden("vendor_sku_name[$i]", $product->vendor_sku_name,  ['id' => "vendor_sku_name"]) !!}
							{!! Form::hidden('purchasedVendorSku', $product->vendor_sku, ['id' => 'purchasedVendorSku']) !!}
							{!! Form::hidden("quantity[$i]", $product->quantity, ['id' => "quantity"]) !!}
							{!! Form::hidden("price[$i]", $product->price, ['id' => "price"]) !!}
							<td>{{$product->vendor_sku}}</td>
              <td>{{$product->stock_no}}</td>
              <td>{{$product->product_details->vendor_sku_name}}</td>
              <td>{{$product->quantity}}</td>
            	<td>
								<div class = 'input-group'>
									@if (!empty($product->receive_date) && $product->receive_date != '0000-00-00')
										{!! Form::text("receive_date[$i]", $product->receive_date, ['id' => "receive_date", 'class' => 'form-control', 'autocomplete' => 'off']) !!}
									@else 
										{!! Form::text("receive_date[$i]", $date, ['id' => "receive_date", 'class' => 'form-control', 'autocomplete' => 'off']) !!}
									@endif
									<span class = "input-group-addon">
																<span class = "glyphicon glyphicon-calendar"></span>
														</span>
								</div>
							</td>
							<td>
							{!! Form::number("receive_quantity[$i]", 0, ['id' => "receive_quantity", 'min' => '0', 'step' => 'any', 'class' => 'form-control']) !!}
							</td>
							<td>
							{!! Form::number("receive_previous[$i]", $product->receive_quantity, ['id' => "receive_previous", 'class' => 'form-control', 'disabled' => 'true']) !!}
							</td>
							<td>
							{!! Form::number("balance_quantity[$i]", $product->balance_quantity, ['id' => "balance_quantity", 'step' => 'any', 'class' => 'form-control', 'readonly']) !!}
							</td>
					</tr>
				
				@setvar(++$i)
			@endforeach
 			</tr>	
			<tr>
				<td colspan="5"></td>
				<td colspan="2">
					{!! Form::submit('Receive Inventory',['class'=>'btn btn-primary form-control']) !!}
				</td>
			</tr>	
			 </tbody>
			</table>
		@endif
		</div>

		{!! Form::close() !!}
	</div>

	<script type = "text/javascript">
		
		setDatePicker();
		
		function setDatePicker () 
		{
			$('tr.collection input#receive_date').each( function(index, element) {
					var picker = new Pikaday(
					{
						field: element,
						format : "YYYY-MM-DD",
					});
				});
		}
	
		function getNumber(number){
			number = parseFloat(number);
			var intRegex = /^\d+$/;
			var floatRegex = /^((\d+(\.\d *)?)|((\d*\.)?\d+))$/;

			if(intRegex.test(number) || floatRegex.test(number)) {
				return number;
			}else{
				return 0
			}
		}
		
		$(document).on('change', 'input#receive_date', function ()
		{
			if(checkValidDate( this.value ) === true){
// 				console.log( "Valide date" )
			}else{
				alert(" Please insert correct YYYY-MM-DD\nDate format");
				$(this).val('');
				return false;
			}
		});

		function checkValidDate(date) {
			var dateFormat = "YYYY-MM-DD";
			return moment(date, dateFormat, true).isValid();
		}
		
		$(document).on('change', 'input#receive_quantity', function ()
		{
			var qty = getNumber($(this).closest('tr').find('#quantity').val());
			var prev = getNumber($(this).closest('tr').find('#receive_previous').val());
			var newbalance = qty - prev - getNumber($(this).val());
			$(this).closest('tr').find('#balance_quantity').val(newbalance);
		});

</script>
</body>
</html>