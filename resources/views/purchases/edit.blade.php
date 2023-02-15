<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Edit {{ $purchase->po_number }}</title>
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
		text-align: right;
	}
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
	<div class = "container"  style="min-width: 1200px;">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "{{url('/purchases')}}">Purchase Orders</a></li>
			<li class = "active"><a href = "{{url('purchases/' . $purchase->po_number . '/edit')}}">Edit {{ $purchase->po_number }}</a></li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		
	@if (count($purchase) > 0)
		
		{!! Form::open(['url' => url(sprintf("/purchases/%s", $purchase->po_number,null)), 'id' => 'purchases_edit', 'method' => 'put']) !!}
		{!! Form::hidden('po_number', $purchase->po_number, ['id' => 'po_number']) !!}
		{!! Form::hidden('vendor_id', $purchase->vendor_id, ['id' => 'vendor_id']) !!}
		
		<h3>Purchase Order {{ $purchase->po_number }} 
					<div class="pull-right">
							{!! \App\Task::widget('App\Purchase', $purchase->id); !!}
					</div>
		</h3>
		<br>
		<table class='table'>
			<tr>
				<th width="10%"></th>
				
				<th width="12%">Vendor:</th>
				<td width="30%">
						{{ $purchase->vendor_details->vendor_name }}
				</td>

				<th width="12%">Date:</th>			
				<td>{{ $purchase->po_date }}</td>
						
				
			</tr>
			
			<tr>
				<th></th>
				
				<th>Phone: </th>
				<td>{{ $purchase->vendor_details->phone_number }}</td>

				
				<th>Vendor ID: </th>
				<td>{{ $purchase->vendor_id }}</td>
				
			</tr>
			
			<tr>
				<th></th>
				
				<th>Email: </th>
				<td>{{ $purchase->vendor_details->email }}</td>
				
				<th>Tracking:</th>
				<td>{!! Form::text('tracking', $purchase->tracking, ['id' => 'tracking', 'class' => 'form-control']) !!}</td>
			</tr>
			
			<tr>
				<th></th>
				
				<th>Notes: </th>
				<td colspan=3>{!! Form::textarea('notes', $purchase->notes, ['id' => 'notes', 'rows' => '1', 'class' => 'form-control']) !!}</td>
				
			</tr>
		</table>

		<br />
		
			@if($purchase->products)
				<table class="table" cellspacing="0" width="100%">
					
					<thead>
					<tr>
						<th>Vendor SKU</th>
						<th>Stock #</th>
						<th>SKU</th>
						<th>Quantity</th>
						<th>Price</th>
						<th>SubTotal</th>
						<th>ETA</th>
						<th>
							{!! Form::button('Add Row',['class'=>'btn btn-success btn-xs', 'id' => 'add-new-row']) !!}
						</th>
					</tr>
					</thead>
					<tbody>
				
				@setvar($i = 0)
				
				@foreach($purchase->products as $product)
						<tr class="collection">
							<td >
								{!! Form::hidden("rowid[$i]", $product->id, ['id' => "rowid"]) !!}
								{!! Form::text("vendor_sku[$i]", $product->vendor_sku,  ['id' => "vendor_sku", 'step' => 'any', 'class' => 'form-control input-sm']) !!}
								{!! Form::hidden("product_id[$i]", $product->product_id, ['id' => "product_id"]) !!}
								{!! Form::hidden("name[$i]", $product->vendor_sku_name, ['id' => "name"]) !!}
							</td>
							<td >
								{!! Form::text("stock_no[$i]", $product->stock_no, ['id' => "stock_no", 'step' => 'any', 'class' => 'form-control input-sm', 'readonly']) !!}
							</td>
							<td >
								{!! Form::select("vendor_sku_name[$i]", $purchasedVendorSku, $product->product_id, ['class'=> 'form-control purchasedVendorSku', 'id' => 'purchasedVendorSku']) !!}
							</td>
							<td >
								{!! Form::number("quantity[$i]", $product->quantity, ['id' => "quantity", 'step' => 'any', 'min' => '0', 'class' => 'form-control input-sm']) !!}
							</td>
							<td >
								{!! Form::number("price[$i]", sprintf("%01.2f", $product->price), ['id' => "price", 'step' => 'any', 'class' => 'form-control input-sm']) !!}
							</td>
							<td >
								{!! Form::number("sub_total[$i]", sprintf("%01.2f", $product->sub_total), ['id' => "sub_total", 'step' => 'any', 'class' => 'form-control  input-sm sub_total', 'readonly']) !!}
							</td>
							<td >
								<div class = 'input-group'>
								{!! Form::text("eta[$i]", $product->eta, ['class' => 'form-control eta_date', 'id' => 'eta_date', 'placeholder' => 'ETA', 'autocomplete' => 'off']) !!}
									<span class = "input-group-addon">
				                        <span class = "glyphicon glyphicon-calendar"></span>
				                    </span>
								</div>
							</td>
							<td align="center">
								<a href = "#" id = "delete" class = "delete" data-toggle = "tooltip" data-placement = "top" title = "Delete this purchase">
									<i class = 'glyphicon glyphicon-remove text-danger' style = "margin-top:10px;"></i>
								</a>
							</td>
						</tr>
						
						@setvar($i++)
						
				@endforeach	
				 </tbody>
				 <tfoot>
					<tr>
						<td colspan = "3"></td>
						<td colspan = "2" align="right">Grand Total:</td>
						<td>{!! Form::number("grand_total", null,  ['id' => "grand_total", 'step' => 'any', 'class' => 'form-control input-sm grand_total']) !!}</td>
						<td colspan=2>{!! Form::submit('Update Purchase Order',['class'=>'btn btn-primary pull-right']) !!}</td>
					</tr>
				</tfoot>
				</table>
			@endif
			
			{!! Form::close() !!}
			
		</div>

	@else
		<div class = "alert alert-warning">Purchase order not found</div>
	@endif
	
	<script type = "text/javascript">
		
		var clone = $("table tr.collection:last").clone(true);
		
		$('.purchasedVendorSku').chosen();
		
		var size = {{ count($purchase->products) }};
		
		setDatePicker();
		sumSubTotal();
		
		function setDatePicker () 
		{
			$('tr.collection input#eta_date').each( function(index, element) {
			    var picker = new Pikaday(
					{
			      field: element,
						format : "YYYY-MM-DD",
						minDate: new Date()
			    });
			  });
		}
		
		$("button#add-new-row").on('click', function () {
			
			 	clone.clone().insertAfter($("table tr.collection").last());
				
				var size = $("tr.collection").length - 1;
				
				$('tr.collection:last').each(function() {
	        var prefix = "[" + size + "]"; 
	        $(this).find("input").each(function() {
	           this.name = this.name.replace(/\[\d+\]/, prefix); 
						 this.value = '';
					}); 
					$(this).find("select").each(function() {
	 	          this.name = this.name.replace(/\[\d+\]/, prefix);
							this.value = '';
	        });
				});
			  
				$('tr.collection:last input#eta_date').each( function(index, element) {
  			    var picker = new Pikaday(
  					{
  			      field: element,
  						format : "YYYY-MM-DD",
  						minDate: new Date()
  			    });
  			  });
	 	});

		$(document).on('click', 'a#delete', function (event)
		{
			event.preventDefault();
			
			var n = $("tr.collection").length;
			if(n > 1){
				$(this).closest('tr.collection').remove();
				sumSubTotal();
			}else{
				alert("You can not delete all rows.\n");
			}
		});

		$(document).on('keyup change', 'input#quantity', function (e)
		{
			e.preventDefault();
			qntity = getNumber(this.value);
			price = getNumber($(this).closest('tr.collection').find('#price').val());
			$(this).closest('tr.collection').find('#sub_total').val( (qntity * price).toFixed(2) );
			sumSubTotal();
		});

		$(document).on('keyup change', 'input#price', function (e)
		{
			e.preventDefault();
			qntity = getNumber($(this).closest('tr.collection').find('#quantity').val());
			price = getNumber(this.value);
			$(this).closest('td').next().find('#sub_total').val( (qntity * price).toFixed(2) );
			sumSubTotal();
		});

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
		
		function sumSubTotal(){
			gTotal = 0;
			$("input.sub_total").each(function() {
				gTotal = gTotal + getNumber(this.value);
			});
			$('#grand_total').val( gTotal.toFixed(2) );
		}

		$(document).on('change', '.purchasedVendorSku', function() 
		{
			vendor_id = $("#vendor_id").val();
			id = $(this).val();
			token = $('input[name=_token]').val();
			route = '/purchases/purchased_inv_products';
			$.ajax({
				url: route,
				headers: {'X-CSRF-TOKEN': token},
				type: 'POST',
				dataType: 'json',
				data: {vendor_id : vendor_id, id : id},
				context: this,
				success:function(response) {
					$(this).closest('tr.collection').find('#rowid').val();
					$(this).closest('tr.collection').find('#vendor_sku').val(response.vendor_sku);
					$(this).closest('tr.collection').find('#name').val(response.vendor_sku_name);
					$(this).closest('tr.collection').find('#product_id').val(response.product_id);
					$(this).closest('tr.collection').find('#stock_no').val(response.stock_no);
					$(this).closest('tr.collection').find('#price').val(response.price);
					$(this).closest('tr.collection').find('#lead_time_days').val(response.lead_time_days);
					var eta = new Date();
					eta.setDate(eta.getDate() + response.lead_time_days); 
					$(this).closest('tr.collection').find('#eta_date').val(eta.toISOString().split('T')[0]);
				}
			 });
		});

		// Check if empty
		$(document).on('click', 'form :submit', function (e){	
			
			var test = true;
			
			$("input#vendor_sku").each(function() {
				if($(this).val() == ""){
						e.preventDefault();
						alert("Vendor SKU Empty");
						
				}
			});
			
			if (!test) {
				return test;
			}
			
			$("input#stock_no").each(function() {
				if($(this).val() == ""){
						e.preventDefault();
						alert("Stock Number Empty");
						test = false;
				}
			});
			
			if (!test) {
				return test;
			}
			
			$("input#quantity").each(function() {
				if($(this).val() == ""){
						e.preventDefault();
						alert("Quantity field Empty");
						test = false;
				} 
			});
			
			return test;
		});		
		
	</script>
</body>
</html>