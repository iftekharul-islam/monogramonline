<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Add Purchase Order</title>
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
	<div class = "container" style="min-width: 1400px;">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "{{url('/purchases')}}">Purchase Orders</a></li>
			<li class = "active"><a href = "{{url('/purchases/create')}}">Add Purchase Order</a></li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		{!! Form::open(['url' => url('/purchases'), 'method' => 'post']) !!}

		<div class = 'row'>
			<div class = 'col-xs-1'>{!!Form::label('po_date','PO Date:')!!}</div>
			<div class = 'col-xs-3'>
				<div class = 'input-group date'>
				{!! Form::text('po_date', date("Y-m-d"), ['id'=>'po_date', 'class' => 'form-control', 'placeholder' => 'PO date', 'autocomplete' => 'off']) !!}
					<span class = "input-group-addon">
                        <span class = "glyphicon glyphicon-calendar"></span>
                    </span>
				</div>
			</div>
			
			<div class = 'col-xs-1'>{!!Form::label('payment_method','Payment:')!!}</div>
			<div class = 'col-xs-3'>
				{!! Form::text('payment_method', null, ['id' => 'payment_method','class' => 'form-control', 'placeholder' => 'Payment Method']) !!}
			</div>
			
			<div class = "col-xs-1"></div>
			<div class = "col-xs-3">
				
			</div>
		</div>
		
		<div class = 'row'>&nbsp;</div>
		
		<div class = 'row'>
			<div class = 'col-xs-1'>{!!Form::label('vendor_id','Vendor ID:')!!}</div>
			<div class = 'col-xs-3'>
				{!! Form::hidden('vendor_id', null, ['id' => 'vendor_id','class' => 'form-control', 'placeholder' => 'Vendor ID']) !!}
				{!! Form::select('vendor_id_select', $vendors, null, ['class'=> 'form-control vendor_id_select', 'id' => 'vendor_id_select']) !!}
			</div>
			
			<div class = 'col-xs-1'>{!!Form::label('vendor_name','Vendor:')!!}</div>
			<div class = 'col-xs-3'>
				{!! Form::text('vendor_name', null, ['id' => 'vendor_name','class' => 'form-control', 'placeholder' => 'Vendor Name','readonly']) !!}
			</div>

			<div class = 'col-xs-1'>{!!Form::label('email','Email:')!!}</div>
			<div class = 'col-xs-3'>
				{!! Form::text('email', null, ['id' => 'email','class' => 'form-control', 'placeholder' => 'Email','readonly']) !!}
			</div>

		</div>

		<div class = 'row'>&nbsp;</div>
		
		<div class = 'row'>
			<div class = 'col-xs-1'>{!!Form::label('zip_code','Zip Code:')!!}</div>
			<div class = 'col-xs-3'>
				{!! Form::text('zip_code', null, ['id' => 'zip_code','class' => 'form-control', 'placeholder' => 'Zip Code','readonly']) !!}
			</div>

			<div class = 'col-xs-1'>{!!Form::label('state','State:')!!}</div>
			<div class = 'col-xs-3'>
				{!! Form::text('state', null, ['id' => 'state','class' => 'form-control', 'placeholder' => 'State','readonly']) !!}
			</div>
			
			<div class = 'col-xs-1'>{!!Form::label('phone_number','Phone:')!!}</div>
			<div class = 'col-xs-3'>
				{!! Form::text('phone_number', null, ['id' => 'phone_number','class' => 'form-control', 'placeholder' => 'Phone Number','readonly']) !!}
			</div>
		</div>
		
		<div class = 'row'>&nbsp;</div>
		
		<div class = 'row'>
			<div class = 'col-xs-1'>{!!Form::label('notes','Notes:')!!}</div>
			<div class = 'col-xs-11'>
				{!! Form::text('notes', null, ['id' => 'notes','class' => 'form-control']) !!}
			</div>
		</div>
		
		<div class = 'row'>&nbsp;</div>
		
		{{-- Code for add item Dynamically --}}
		<table class="table" cellspacing="0" width="100%" id="purchaseTbl">
		<tr>
			<th>Vendor SKU</th>
			<th>Stock Number</th>
			<th class="col-md-3">SKU</th>
			<th>Quantity</th>
			<th>Price</th>
			<th>Subtotal</th>
			<th>Lead Time</th>
			<th>ETA</th>
			<th>{!! Form::button('Add Row',['class'=>'btn btn-success btn-xs', 'id' => 'add-new-row']) !!}</th>
		</tr>
		<tr class="collection">
			<td >
				{!! Form::text("vendor_sku[0]", null, ['id' => "vendor_sku", 'step' => 'any', 'class' => 'form-control']) !!}
				{!! Form::hidden("product_id[0]", null, ['id' => "product_id", 'step' => 'any', 'class' => 'form-control', 'readonly']) !!}
			</td>
			<td >
				{!! Form::text("stock_no[0]", null, ['id' => "stock_no", 'step' => 'any', 'class' => 'form-control', 'readonly']) !!}
			</td>
			<td >
				{!! Form::select("vendor_sku_name[0]", [], '', ['class'=> 'form-control purchasedVendorSku', 'id' => 'purchasedVendorSku']) !!}
			</td>
			<td >
				{!! Form::number("quantity[0]", null, ['id' => "quantity", 'min' => '0', 'step' => 'any', 'class' => 'form-control']) !!}
			</td>
			<td >
				{!! Form::number("price[0]", null, ['id' => "price", 'step' => 'any', 'width' => '4', 'class' => 'form-control']) !!}
			</td>
			<td >
				{!! Form::number("sub_total[0]", null, ['id' => "sub_total", 'step' => 'any', 'class' => 'form-control sub_total']) !!}
			</td>
			<td >
				{!! Form::number("lead_time_days[0]", null, ['id' => "lead_time_days", 'step' => 'any', 'disabled' => true, 'class' => 'form-control']) !!}
			</td>
			<td >
				<div class = 'input-group' >
				{!! Form::text("eta[0]", null, ['class' => 'form-control eta_date', 'placeholder' => 'ETA', 'id' => 'eta_date', 'autocomplete' => 'off']) !!}
					<span class = "input-group-addon">
                        <span class = "glyphicon glyphicon-calendar"></span>
                    </span>
				</div>
			</td>
			<td align="center">
				<a href = "#" id = "delete" class = "delete" data-toggle = "tooltip" data-placement = "top" title = "Delete this row">
						<i class = 'glyphicon glyphicon-remove text-danger' style = "margin-top:10px;"></i>
				</a>
			</td>
		</tr>

		<tr>
			<td colspan="3"></td>
			<td colspan = "2" align="right"><b>Grand Total:</b></td>
			<td>{!! Form::number("grand_total", '0', ['id' => "grand_total", 'step' => 'any', 'class' => 'form-control grand_total']) !!}</td>
			<td></td>
			<td colspan="3">
				{!! Form::submit('Create Purchase Order',['class'=>'btn btn-primary pull-right']) !!}
			</td>
		</tr>
		</table>

		{!! Form::close() !!}
	</div>


	<script type = "text/javascript">
	
		var clone = $("table tr.collection:first").clone(true);
		var purchasedVendorSkus = '';
		
		$(".vendor_id_select").chosen();
		$('.purchasedVendorSku').chosen();
		
		setDatePicker();
		
		var picker = new Pikaday(
		{
				field: document.getElementById('po_date'),
				format : "YYYY-MM-DD", 
		});
		
		function setDatePicker () 
		{
			$('tr.collection:last input#eta_date').each( function(index, element) {
			    var picker = new Pikaday(
					{
			      field: element,
						format : "YYYY-MM-DD",
						minDate: new Date()
			    });
			  });
		}
		
		$("button#add-new-row").on('click', function () {
			
			 clone.clone(true).insertAfter($("table tr.collection").last());
				
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
					
				$('tr.collection:last select#purchasedVendorSku').append(purchasedVendorSkus);
				$('tr.collection:last select#purchasedVendorSku').chosen();
				$('tr.collection:last select#purchasedVendorSku').trigger("chosen:updated");
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

		$("#vendor_id").on('blur', function (event)
		{
			vendor_id= $("#vendor_id").val();
			token = $('input[name=_token]').val();
// 			console.log(vendor_id +" ---- "+ token);
			route = '/purchases/getVendorById';

			$.ajax({
				url: route,
				headers: {'X-CSRF-TOKEN': token},
				type: 'POST',
				dataType: 'json',
				data: {vendor_id : vendor_id},
				success:function(response) {
// 			  		console.log( response );
					$("#email").val(response.email);
					$("#phone_number").val(response.phone_number);
					$("#state").val(response.state);
					$("#vendor_name").val(response.vendor_name);
					$("#zip_code").val(response.zip_code);
			    }
			 });

		});

		$(document).on('change', '.purchasedVendorSku', function() 
		{
			vendor_id = $("#vendor_id").val();
			id = this.value;
			token = $('input[name=_token]').val();
// 			console.log(vendor_id +" ---- "+ vendor_sku);
			route = '/purchases/purchased_inv_products';
			$.ajax({
				url: route,
				headers: {'X-CSRF-TOKEN': token},
				type: 'POST',
				dataType: 'json',
				data: {vendor_id : vendor_id, id : id},
				context: this,
				success:function(response) {
					$(this).closest('tr.collection').find('#vendor_sku').val(response.vendor_sku);
					$(this).closest('tr.collection').find('#product_id').val(response.product_id);
					$(this).closest('tr.collection').find('#stock_no').val(response.stock_no);
					$(this).closest('tr.collection').find('#purchasedVendorSku').val(response.vendor_sku_name);
					$(this).closest('tr.collection').find('#price').val(response.price);
					$(this).closest('tr.collection').find('#lead_time_days').val(response.lead_time_days);
					var eta = new Date();
					eta.setDate(eta.getDate() + response.lead_time_days); 
					$(this).closest('tr.collection').find('#eta_date').val(eta.toISOString().split('T')[0]);
					
					if ($('#vendor_id_select').prop('disabled') == false) {
						$('#vendor_id_select').prop('disabled', true).trigger("chosen:updated");
					}
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

		$(".vendor_id_select").chosen().change(function (event) {
			$('#vendor_id').val($(event.target).val());
			$('#vendor_id').trigger("blur");
			//console.log($(event.target).val());
			getPurchasedVendorSku();
		});
		
		function getPurchasedVendorSku(){
					
					purchasedVendorSkus = '';
    			vendor_id = $('#vendor_id').val();
    			token = $('input[name=_token]').val();
    			route = '/purchases/purchasedVendorSku';
    			// console.log( vendor_id );
    			
    			$.ajax({
    				url: route,
    				headers: {'X-CSRF-TOKEN': token},
    				type: 'POST',
    				dataType: 'json',
    				beforeSend: function(){purchasedVendorSkus = '';},
    				data: {vendor_id : vendor_id},
    				context: this,
    				success:function(response) {
    					$.each(response, function(key, value) {
    							// console.log( key +'----'+ value );
    							purchasedVendorSkus = purchasedVendorSkus + '<option value="'+key+'">'+value+'</option>';
    			      });
							 $(".purchasedVendorSku").append(purchasedVendorSkus);
    					 $(".purchasedVendorSku").trigger("chosen:updated");
    			    },
    			    error: function(response) {
    			    	//console.log( response[index] );
    			    	alert(response);
    			   }
    			 });
		}
	</script>
</body>
</html>