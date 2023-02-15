<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Manual Order</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css" />
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/nprogress.css">
	<link type = "text/css" rel = "stylesheet" href="/assets/css/pikaday.min.css">
		
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/nprogress.js"></script>
	<script type = "text/javascript" src = "/assets/js/jquery.autocomplete.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/moment.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/pikaday.min.js"></script>
	
	<style type = "text/css">
		table {
			font-size: 11px;
		}
		.autocomplete-suggestions { -webkit-box-sizing: border-box; -moz-box-sizing: border-box; box-sizing: border-box; border: 1px solid #999; background: #FFF; cursor: default; overflow: auto; -webkit-box-shadow: 1px 4px 3px rgba(50, 50, 50, 0.64); -moz-box-shadow: 1px 4px 3px rgba(50, 50, 50, 0.64); box-shadow: 1px 4px 3px rgba(50, 50, 50, 0.64); }
		.autocomplete-suggestion { padding: 2px 5px; white-space: nowrap; overflow: hidden; }
		.autocomplete-no-suggestion { padding: 2px 5px;}
		.autocomplete-selected { background: #F0F0F0; }
		.autocomplete-suggestions strong { font-weight: bold; color: #000; }
		.autocomplete-group { padding: 2px 5px; font-weight: bold; font-size: 16px; color: #000; display: block; border-bottom: 1px solid #000; }
	</style>
</head>
<body>
	@include('includes.header_menu')
	<div class = "container">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li><a href = "{{url('orders/list')}}">Orders</a></li>
			<li class = "active">Add Manual Order</li>
		</ol>

		@include('includes.error_div')
		@include('includes.success_div')
		
		{!! Form::open(['method' => 'put', 'url' => url('orders/new'), 'id' => 'manual-order-placement', 'class' => 'form-horizontal']) !!}

			<div class = "form-group">
				<label for = "store" class = "col-md-2 control-label">Store</label>
				<div class = "col-md-3">
					{!! Form::select('store', $stores, null, ['id'=>'store', 'class' => 'form-control', 'placeholder' => 'Choose Store', 'tabindex' => '1']) !!}
				</div>
				<label for = "PO" class = "col-md-1 control-label">PO:</label>
				<div class = "col-md-2">
					{!! Form::text('purchase_order', null, ['id' => 'purchase_order']) !!}
				</div>
				<label for = "date" class = "col-md-1 control-label">Date:</label>
				<div class = "col-md-2">
					{!! Form::text('order_date', date("Y-m-d"), ['id' => 'order_date']) !!}
				</div>
			</div>
			<div class = "row">
				<div class = "col-md-offset-2 col-md-10">
					<table>
						<tr>
							<td colspan=3 style = "font-weight: bold;color: #686869;padding-top:15px">Ship To:</td>
							<td colspan=3 style = "font-weight: bold;color: #686869;padding-left:97px;padding-top:15px">Bill To:</td>
						</tr>
						<tr>
							<td>Full Name</td>
							<td>{!! Form::text('ship_full_name', null, ['id' => 'ship_full_name', 'tabindex' => '2']) !!}</td>
							<td></td>
							<!---->
							<td style = "padding-left:97px">Full Name</td>
							<td>{!! Form::text('bill_full_name', null, ['id' => 'bill_full_name']) !!}</td>
							<td></td>
						</tr>
						<tr>
							<td>Company</td>
							<td>{!! Form::text('ship_company_name', null, ['id' => 'company_name', 'tabindex' => '3']) !!}</td>
							<td></td>
							<!---->
							<td style = "padding-left:97px">Company</td>
							<td>{!! Form::text('bill_company_name', null, ['id' => 'bill_company_name']) !!}</td>
							<td></td>
						</tr>
						<tr>
							<td>First/Last</td>
							<td colspan=2>
								{!! Form::text('ship_first_name', null, ['id' => 'ship_first_name', 'tabindex' => '4']) !!}
								{!! Form::text('ship_last_name', null, ['id' => 'ship_last_name', 'tabindex' => '5']) !!}
							</td>
							<td style = "padding-left:97px">First/last</td>
							<td colspan=2>
								{!! Form::text('bill_first_name', null, ['id' => 'bill_first_name', 'class' => '']) !!}
								{!! Form::text('bill_last_name', null, ['id' => 'bill_last_name', 'class' => '']) !!}
							</td>
						</tr>
						<tr>
							<td>Street</td>
							<td>{!! Form::text('ship_address_1', null, ['id' => 'ship_address_1', 'tabindex' => '6']) !!}</td>
							<td></td>
							<td style = "padding-left:97px">Street</td>
							<td>{!! Form::text('bill_address_1', null, ['id' => 'bill_address_1', 'class' => '']) !!}</td>
							<td></td>
						</tr>
						<tr>
							<td>Line 2</td>
							<td>{!! Form::text('ship_address_2', null, ['id' => 'ship_address_2', 'tabindex' => '7']) !!}</td>
							<td></td>
							<td style = "padding-left:97px">Line 2</td>
							<td>{!! Form::text('bill_address_2', null, ['id' => 'bill_address_2']) !!}</td>
							<td></td>
						</tr>
						<tr>
							<td>City, State</td>
							<td colspan=2>
								{!! Form::text('ship_city', 'XXX', ['id' => 'ship_city', 'tabindex' => '8']) !!}
								{!! Form::text('ship_state', null, ['id' => 'ship_state', 'style' => 'width: 50px;', 'tabindex' => '9']) !!}
							</td>
							<td style = "padding-left:97px">City, State</td>
							<td colspan=2>
								{!! Form::text('bill_city', null, ['id' => 'bill_city']) !!}
								{!! Form::text('bill_state', null, ['id' => 'bill_state', 'style' => 'width: 50px;']) !!}
							</td>
						</tr>
						<tr>
							<td>ZipCode</td>
							<td>{!! Form::text('ship_zip', '000', ['id' => 'ship_zip','style'=>'width: 100px', 'tabindex' => '10']) !!}</td>
							<td></td>
							<td style = "padding-left:97px">ZipCode</td>
							<td>{!! Form::text('bill_zip', null, ['id' => 'bill_zip','style'=>'width: 100px']) !!}</td>
							<td></td>
						</tr>
						<tr>
							<td>Country</td>
							<td>{!! Form::text('ship_country', null, ['id' => 'ship_country', 'tabindex' => '11']) !!}</td>
							<td></td>
							<td style = "padding-left:97px">Country</td>
							<td>{!! Form::text('bill_country', null, ['id' => 'bill_country']) !!}</td>
							<td></td>
						</tr>
						<tr>
							<td>Phone</td>
							<td>{!! Form::text('ship_phone', '8563203210', ['id' => 'company_name', 'tabindex' => '11']) !!}</td>
							<td></td>
							<td style = "padding-left:97px">Phone</td>
							<td>{!! Form::text('bill_phone', null, ['id' => 'bill_phone']) !!}</td>
							<td></td>
						</tr>
						<tr>
							<td>Email</td>
							<td colspan=2>{!! Form::text('bill_email', null, ['id' => 'bill_email', 'style' => 'width:250px;', 'tabindex' => '12']) !!}</td>
							<td colspan=3></td>
						</tr>
						<tr>
							<td>Ship Via:</td>
							<td> 
								{!! Form::select('shipping', $shipping_methods, '', ['id' => 'shipping_method', 'tabindex' => '13']) !!} 
							</td>
							<td colspan=4></td>
						</tr>
					</table>
				</div>
			</div>

			<div class = "row">
				<div class = "col-md-12">
					<br>
					<table class = "table table-bordered" id = "selected-items">
						<thead>
						<tr id="items-header">
							<th colspan=3></th>
							<th width=100>Quantity</th>
							<th width=100>Price</th>
							<th width=100>Total</th>
							<th width=100></th>
						</tr>
					</thead>
					<tbody>
					</tbody>
					</table>
				</div>
			</div>
			
			<div class = "form-group" style="padding:20px;">
				<label for = "search_sku" class = "col-md-2 control-label">Add Item:</label>
				<div class = "col-md-8">
						{!! Form::text('search_sku', null, ['id'=>'search_sku', 'class' => 'form-control autocomplete', 'placeholder' => 'SKU / Name / Id catalog', 'tabindex' => '14']) !!}
				</div>
			</div>
			
			<div class = "row">
				<div class = "col-md-7 text-right">
				</div>
				<div class = "col-md-5 text-right">
					<table>
						<tr>
							<td colspan=2><BR></td>
						</tr>
						<tr>
							<td align = "right" style = "padding-right:40px ">Subtotal:</td>
							<td align = "right">{!! Form::number('subtotal', 0.0, ['id' => 'subtotal', 'readonly' => 'readonly', 'step' => '.01']) !!}</td>
						</tr>
						<tr>
							<td align = "right" style = "padding-right:40px ">Coupon-discount</b>:</td>
							<td align = "right">{!! Form::text('coupon_id', null, ['placeholder' => 'Coupon id']) !!} - {!! Form::number('coupon_value', 0.0, ['id' => 'coupon_value', 'step' => 'any']) !!}</td>
						</tr>
						<tr>
							<td align = "right" style = "padding-right:40px ">Gift Wrap:</td>
							<td align = "right">{!! Form::number('gift_wrap_cost', 0.0, ['id' => 'gift_wrap_cost', 'step' => '.01']) !!}</td>
						</tr>
						<tr>
							<td align = "right" style = "padding-right:40px ">Shipping:</td>
							<td align = "right">{!! Form::number('shipping_charge', 0.0, ['id' => 'shipping_charge', 'step' => '.01']) !!}</td>
						</tr>
						<tr>
							<td align = "right" style = "padding-right:40px ">Insurance:</td>
							<td align = "right">{!! Form::number('insurance', 0.0, ['id' => 'insurance', 'step' => '.01']) !!}</td>
						</tr>
						<tr>
							<td align = "right" style = "padding-right:45px ">Adjustments:</td>
							<td align = "right">{!! Form::number('adjustments', 0.0, ['id' => 'adjustments', 'step' => '.01']) !!}</td>
						</tr>
						<tr>
							<td align = "right" style = "padding-right:45px ">Tax:</td>
							<td align = "right">{!! Form::number('tax_charge', 0.0, ['id' => 'tax_charge', 'step' => '.01']) !!}</td>
						</tr>
						<tr>
							<td align = "right" style = "padding-right:45px ">Total:</td>
							<td align = "right">{!! Form::number('total', 0.0, ['id' => 'total', 'readonly' => 'readonly', 'step' => '.01']) !!}</td>
						</tr>
						<tr>
							<td></td>
							<td>
								<br>
								{!! Form::submit('Add order', ['id' => 'add-order', 'class' => 'btn btn-primary btn-sm']) !!}
							</td>
					</table>
				</div>
			</div>
			<div class = "row" id = "modal-holder"> 
			</div> 
	</div>
		{!! Form::close() !!}
	
	<script type = "text/javascript">

		var picker = new Pikaday(
		{
				field: document.getElementById('order_date'),
				format : "YYYY-MM-DD",    
		});

		$(document).ready(function() {
			$(window).keydown(function(event){
				if(event.keyCode == 13) {
					event.preventDefault();
					return false;
				}
			});
		});

		$('#search_sku').autocomplete({
				minChars: 4,
				serviceUrl: '/orders/manual/ajax_search',
				onSelect: function (suggestion) {
						addItem(suggestion.data, suggestion.id_catalog, suggestion.desc, suggestion.image, suggestion.price, 1);
						$(this).val(''); 
						return false;
				}
		});
		
		function addItem (sku, id_catalog, desc, image, price, quantity) 
		{	
				var unique = Math.floor(Math.random() * 100);
				
				var tr = "<tr id='" + unique + "' id_catalog='" + id_catalog + "' data-sku='" + sku + "'>" +
								"<td> <img src='" + image + "' width='100' /> </td>" + 
								"<td>" + sku + "<br>" + desc + "</td>" +
								"<input type='hidden' name='item_sku[]' value='" + sku + "'>" +
								"<input type='hidden' name='item_id[]' value=''>" +
								"<input type='hidden' name='child_sku[]' value=''>" +
								"<td><textarea name='item_option[]' class='item_option' cols=50 rows=3 wrap='physical'></textarea></td>" +
								"<td><input type='number' name='item_quantity[]' class='item_quantity' step=1 min=1 onChange='updateTotals();' value=" + quantity + "></td>" + 
								"<td><input type='number' name='item_price[]' class='item_price' step=.01 min=0 onChange='updateTotals();' value=" + price + "></td>" + 
								"<td><span class='items-subtotal[]'>" + (price * quantity) + "</span></td>" + 
								"<td>" + "<a href='#' id='crawl' class='btn btn-xs btn-primary' style='width:75px;' onClick='return false;'>Customize</a>" +
								"<br><br>" + 
								"<a href='#' class='delete-row btn btn-xs btn-danger' style='width:75px;' onClick='return false;'>Remove</a>" +
								"</td></tr>";

			$("#selected-items").append(tr);
			
			updateTotals();
		}
		
		$(document).on('click', '.delete-row', function (event)
		{
			event.preventDefault();
			if ( confirm("Are you sure want to delete?") ) {
				$(this).closest('tr').remove();
			}
		});
		
		function ajax (url, method, data, successHandler, errorHandler)
		{
			NProgress.start();
			$.ajax({
				url: url, method: method, data: data, success: function (data, status)
				{
					NProgress.done();
					successHandler(data);
				}, error: function (xhr, status, error)
				{
					NProgress.done();
					errorHandler(xhr);
				}
			})
		}

		$(document).on('click', "#crawl", function ()
		{
			var store_id = $("#store").val();
			if (store_id.trim().length == 0) {
				alert('Please Select a Store');
				return;
			}
			var row = $(this).closest("tr");
			var sku = row.attr('data-sku');
			var id_catalog = row.attr('id_catalog');
			var unique = row.attr('id');
			
			var url = "/orders/manual/product_info";
			
			var data = {
				"sku": sku, "store_id": store_id, "id_catalog": id_catalog, "unique": unique
			};
		
			var method = "GET";

			ajax(url, method, data, fetchProductInformationOnSelect, showProductInformationFetchFailed);

		});
		
		function showProductInformationFetchFailed (xhr)
		{
			alert("Product not found or Something went wrong!");
		}
		
		function fetchProductInformationOnSelect (data) 
		{ 
			var result = data.result; 
			if ( result == false ) { 
				alert('Something went wrong!'); 
			} else { 
				$("#modal-holder").html(result); 
				var unique = data.unique; 
				$("." + unique).modal({ 
					backdrop: 'static', keyboard: false, show: true 
				}); 
			} 
		} 
		
		$(document).on('click', 'button.add-item', function () 
		{ 	
				var body = $(this).closest('div.modal-content').find('div.modal-body'); 
				var unique = body.find('.hidden_unique').val(); 
				var options = '';
				body.find('.option-field').each( function(label,value) {
						options = options + $(this).attr("name") + ' = ' + $(this).val() + "\n";
		    });
				 $("#" + unique).find(".item_option").val(options);
				body.closest('.modal').modal('hide'); 
				body.remove();
				body.empty();
		}); 
		
		$(document).on('click', '.cancel', function () 
		{ 
			$(this).closest('div.modal-content').find('div.modal-body').remove();
		}); 
		
		function updateTotals() 
		{
			var subtotal = 0;
			
			$("#selected-items tr").each( function() {
					if ($(this).attr("id") != 'items-header') {
						var quantity = $(this).find(".item_quantity").val();
						var price = $(this).find(".item_price").val();
						subtotal = parseFloat(subtotal) + (parseInt(quantity) * parseFloat(price));
					}
			});
			
			$("#subtotal").val(Math.round(subtotal*100)/100);
			
			var coupon = $("#coupon_value").val();
			var gift = $("#gift_wrap_cost").val();
			var shipping = $("#shipping_charge").val();
			var insurance = $("#insurance").val();
			var adjustments = $("#adjustments").val();
			var tax = $("#tax_charge").val();
									
			var total = parseFloat(subtotal) - 
									parseFloat(coupon) + 
									parseFloat(gift) + 
									parseFloat(shipping) + 
									parseFloat(insurance) + 
									parseFloat(adjustments) + 
									parseFloat(tax);
			
			$("#total").val(Math.round(total*100)/100);
		}
		
		$("input[type=number]").bind('keyup input', function(){
			updateTotals();
		});
		
		$("#manual-order-placement").submit(function () {
				var store_id = $("#store").val();
				if (store_id.trim().length == 0) {
					alert('Please Select a Store');
					return false;
				}
				
				var bill_email = $("#bill_email").val();
				if (bill_email.trim().length == 0) {
					alert('Please Enter Email');
					return false;
				}
				
				var ship_full_name = $("#ship_full_name").val();
				if (ship_full_name.trim().length == 0) {
					alert('Please Enter Shipping Full Name');
					return false;
				}
		});
	</script>
</body>
</html>