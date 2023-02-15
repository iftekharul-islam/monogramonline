<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Update template</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet"
	      href = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
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
			<li><a href = "{{url('/prod_config/templates')}}">Templates</a></li>
			<li class = "active">Update template</li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		{!! Form::open(['url' => url(sprintf("/prod_config/templates/%d", $template->id)), 'id' => 'template-update', 'method' => 'put', 'class' => 'form-horizontal' ]) !!}
		<div class = "col-xs-12">
			<div class = "form-group">
				{!! Form::label('template_name', 'Template name: ', ['class' => 'control-label col-xs-2']) !!}
				<div class = "col-xs-10">
					{!! Form::text('template_name', $template->template_name, ['id' => 'template_name', 'class' => "form-control", 'placeholder' => "Enter template name"]) !!}
				</div>
			</div>
			<div class = "form-group">
				<div class = "col-xs-6">
					{!! Form::label('repeated_fields', 'Repeated fields: ', ['class' => 'control-label col-xs-4']) !!}
					<div class = "col-xs-8">
						{!! Form::select('repeated_fields', $repeated_fields, $template->repeated_fields, ['id' => 'repeated_fields', 'class' => "form-control",]) !!}
					</div>
				</div>
				<div class = "col-xs-6">
					{!! Form::label('delimited_char', 'Delimited char: ', ['class' => 'control-label col-xs-4']) !!}
					<div class = "col-xs-8">
						{!! Form::select('delimited_char', $delimited_chars, $template->delimited_char, ['id' => 'delimited_char', 'class' => "form-control",]) !!}
					</div>
				</div>
			</div>
			<div class = "form-group">
				<div class = "col-xs-2">
					<div class = "checkbox">
						<label>
							{!! Form::checkbox('show_header', 1, $template->show_header) !!} Show Header
						</label>
					</div>
				</div>
				<div class = "col-xs-2">
					<div class = "checkbox">
						<label>
							{!! Form::checkbox('break_kits', 1, $template->break_kits) !!} Break kits
						</label>
					</div>
				</div>
				<div class = "col-xs-2">
					<div class = "checkbox">
						<label>
							{!! Form::checkbox('is_active', 1, $template->is_active) !!} Active
						</label>
					</div>
				</div>
			</div>
		</div>
		<div class = "col-xs-12">
			<table class = "table" id = "draggable-table">
				<thead>
				<tr>
					<th>Line</th>
					<th>Name</th>
					<th>Cat</th>
					<th>Value</th>
					<th>Width</th>
					<th>Format</th>
					<th>Action</th>
				</tr>
				</thead>
				<tbody id = "draggable-table-rows">
				@setvar($x = 1)
				@if(count($template->options))
					@foreach($template->options as $option)
						<tr>
							<td>{!! Form::checkbox('line_item[]', 1, $option->line_item_field, ['class' => '']) !!}</td>
							<td>{!! Form::text('option_name[]', $option->option_name, ['class' => 'form-control']) !!}</td>
							<td>{!! Form::select('option_category[]', $option_categories, $option->option_category, ['class' => 'form-control']) !!}</td>
							<td>{!! Form::text('value[]', $option->value, ['class' => 'form-control']) !!}</td>
							<td>{!! Form::text('width[]', $option->width, ['class' => 'form-control']) !!}</td>
							<td>{!! Form::select('format[]', $formats, $option->format, ['class' => 'form-control']) !!}</td>
							<td>
								<span class = "text-danger delete-row" data-toggle = "tooltip" data-placement = "top"
								      title = "Delete row"><i class = "fa fa-times"></i> </span>
								<span class = "new-row" data-toggle = "tooltip" data-placement = "top"
								      title = "Add new row"><i class = "fa fa-copy"></i> </span>
								<span class = "move-up" data-toggle = "tooltip" data-placement = "top"
								      title = "Move up"><i class = "fa fa-caret-up"></i> </span>
								<span class = "move-down" data-toggle = "tooltip" data-placement = "top"
								      title = "Move down"><i class = "fa fa-caret-down"></i> </span>
							</td>
							{!! Form::hidden('template_orders[]', null, ['class' => 'hidden-template-order']) !!}
							{!! Form::hidden('line_item_fields[]', null, ['class' => 'hidden-line-item-field']) !!}
						</tr>
					@endforeach
				@endif
				</tbody>
			</table>
		</div>
		<div class = "form-group pull-right">
			{!! Form::button('Add new row', ['id' => 'add-new-row', 'class' => 'btn btn-success']) !!}
			{!! Form::submit('Update', ['id' => 'submit-update', 'class' => 'btn btn-primary']) !!}
		</div>
		{!! Form::close() !!}
	</div>
	<script type = "text/javascript" src = "//code.jquery.com/jquery-1.11.3.min.js"></script>
	<script type = "text/javascript" src = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<script type = "text/javascript">
		var message = {
			delete: 'Are you sure you want to delete?',
		};

		function add_new_row (position)
		{
			var row = '<tr>\
							<td>\
								<input class="" name="line_item_field[]" type="checkbox" value="1">\
							</td>\
							<td>\
								<input class="form-control" name="option_name[]" type="text">\
							</td>\
							<td>\
								<select class="form-control" name="option_category[]">\
									<option value="FIX">Fixed value</option>\
									<option value="header.currentdate">Current date</option>\
									<option value="header.billing.address.firstname">bill First name</option>\
									<option value="header.billing.address.lastname">bill Last name</option>\
									<option value="header.ordernumber">Order number</option>\
									<option value="header.4plordernumber">4PL Order number</option>\
									<option value="header.longordernumber">Full Order number</option>\
									<option value="header.prefixordernumber">D/S prefix &amp; Order number</option>\
									<option value="header.po_num">PO number</option>\
									<option value="header.store">Store</option>\
									<option value="header.storeType">store Type</option>\
									<option value="header.orderdate">order Date</option>\
									<option value="header.saletax.amount">order Tax</option>\
									<option value="header.saletax.rate">Sales Tax Percent</option>\
									<option value="header.shippingamount.amount">shipping Cost</option>\
									<option value="header.subtotal">order Total</option>\
									<option value="header.giftwrap">Gift wrap charge</option>\
									<option value="header.custNum">Customer Number</option>\
									<option value="header.email">email</option>\
									<option value="header.comments">Order comments</option>\
									<option value="header.orderCustom">Gift Message</option>\
									<option value="header.customData">Custom fields</option>\
									<option value="header.orderStatus">Order status code</option>\
									<option value="header.billing.email">email</option>\
									<option value="header.billing.address.company">Bill Company name</option>\
									<option value="header.billing.address.Bill_Name">Bill Name</option>\
									<option value="header.billing.address.line1">bill Address1</option>\
									<option value="header.billing.address.line2">bill Address2</option>\
									<option value="header.billing.address.city">bill City</option>\
									<option value="header.billing.address.state">bill State</option>\
									<option value="header.billing.address.postalcode">bill Zip</option>\
									<option value="header.billing.address.country">bill Country</option>\
									<option value="header.billing.address.scountry">bill Country (short)</option>\
									<option value="header.billing.address.fcountry">bill Country (long)</option>\
									<option value="header.billing.address.phonenumber">bill Phone</option>\
									<option value="header.billing.payment.method">creditCard</option>\
									<option value="header.billing.payment.amount">orderTotal</option>\
									<option value="header.shipping.address.company">Ship Company name</option>\
									<option value="header.shipping.shippingmethod.carrier">ship - carrier</option>\
									<option value="header.shipping.shippingmethod.methodname">shipMethod name</option>\
									<option value="header.shipping.shippingmethod.code">shipMethod code</option>\
									<option value="header.shipping.address.firstname">ship First name</option>\
									<option value="header.shipping.address.lastname">ship Last name</option>\
									<option value="header.shipping.address.Ship_Name">Ship Name</option>\
									<option value="header.shipping.address.line1">ship Address1</option>\
									<option value="header.shipping.address.line2">ship Address2</option>\
									<option value="header.shipping.address.city">ship City</option>\
									<option value="header.shipping.address.state">ship State</option>\
									<option value="header.shipping.address.postalcode">ship Zip</option>\
									<option value="header.shipping.address.country">ship Country</option>\
									<option value="header.shipping.address.scountry">ship Country (short)</option>\
									<option value="header.shipping.address.fcountry">ship Country (long)</option>\
									<option value="header.shipping.address.phonenumber">ship Phone</option>\
									<option value="header.numlines"># of lines</option>\
									<option value="items.itemID">Item ID</option>\
									<option value="items.sku">Item SKU</option>\
									<option value="items.upc">Item UPC</option>\
									<option value="items.vendorSku">Supplier SKU</option>\
									<option value="items.description">Item name</option>\
									<option value="items.unitprice">Item unit price</option>\
									<option value="items.itemCost">Item unit cost</option>\
									<option value="items.quantity">Items Qty</option>\
									<option value="items.itemOptions">Item Options</option>\
									<option value="items.shipperOptions">Item Shipper Notes</option>\
									<option value="items.lineItem">Item Line #</option>\
									<option value="items.lineseq">SEQ #, starts at 0</option>\
									<option value="items.nameOptions">Item name &amp; options</option>\
									<option value="items.subtotal">Line item sub-total</option>\
								</select>\
							</td>\
							<td>\
								<input class="form-control" name="value[]" type="text">\
							</td>\
							<td>\
								<input class="form-control" name="width[]" type="text">\
							</td>\
							<td>\
								<select class="form-control" name="format[]">\
									<option value="1">Integer</option>\
									<option value="2">Float</option>\
									<option value="3">Date, MM/DD/YYYY</option>\
									<option value="4">Date, DD/MM/YYYY</option>\
									<option value="5">Date, YYYY-MM-DD</option>\
									<option value="6">Date, yyyyMMdd</option>\
									<option value="7">Date, MM/DD/YYYY + 5 days</option>\
									<option value="8">Strip phone #</option>\
									<option value="9">Strip prefix</option>\
									<option value="10">Text/Excel</option>\
									<option value="11">Integer, add 1</option>\
									<option value="12">Extract option value</option>\
									<option value="13">Extract custom value</option>\
									<option value="14">Date, MM/DD/YYYY + X days</option>\
									<option value="15">Current Date, MM/DD/YYYY</option>\
									<option value="16">To UpperCase</option>\
									<option value="17">Date, MM-DD-YY HH:MM</option>\
									<option value="18">Date, YYYY-MM-DD HH:MM:SS</option>\
									<option value="19">Text</option>\
								</select>\
							</td>\
							<td>\
								<span class = "text-danger delete-row" data-toggle = "tooltip" data-placement = "top" title = "Delete row"><i class = "fa fa-times"></i> </span>\
								<span class = "new-row" data-toggle = "tooltip" data-placement = "top" title = "Add new row"><i class = "fa fa-copy"></i> </span>\
								<span class = "move-up" data-toggle = "tooltip" data-placement = "top" title = "Move up"><i class = "fa fa-caret-up"></i> </span>\
								<span class = "move-down" data-toggle = "tooltip" data-placement = "top" title = "Move down"><i class = "fa fa-caret-down"></i> </span>\
							</td>\
							<input class="hidden-template-order" name="template_orders[]" type="hidden">\
							<input class="hidden-line-item-field" name="line_item_fields[]" type="hidden">\
					</tr>';
			if ( $(position).length ) {
				$(position).after($(row));
			} else {
				var parent = $("table#draggable-table tbody#draggable-table-rows");
				$(parent).append($(row));
			}

			table_row_repositioning_method();
		}
		function table_row_repositioning_method ()
		{
			$("tbody#draggable-table-rows tr").each(function ()
			{
				var has_next = $(this).next().length ? true : false;
				var has_previous = $(this).prev().length ? true : false;

				if ( has_next ) {
					$(this).find('span.move-down').show();
				} else {
					$(this).find('span.move-down').hide();
				}

				if ( has_previous ) {
					$(this).find('span.move-up').show();
				} else {
					$(this).find('span.move-up').hide();
				}
			});
		}
		$(function ()
		{
			$("body").tooltip({selector: '[data-toggle="tooltip"]'});
			table_row_repositioning_method();
		});
		$("body").on('mousedown', 'table#draggable-table tbody', function (event)
		{
			$('html,body').css('cursor', 'move');
		});
		$("body").on('mouseup', 'table#draggable-table tbody', function (event)
		{
			$('html,body').css('cursor', 'default');
		});
		$("body").on('click', 'button#add-new-row', function (event)
		{
			var tr = $("tbody#draggable-table-rows tr:last");
			if ( !tr ) {
				tr = $("tbody#draggable-table-rows");
			}
			add_new_row(tr);
		});
		$("body").on('click', 'span.new-row', function (event)
		{
			var tr = $(this).closest('tr');
			add_new_row(tr);
		});
		$("body").on('click', 'span.delete-row', function (event)
		{
			var answer = confirm(message.delete);
			if ( answer ) {
				var tr = $(this).closest('tr');
				$(tr).remove();
			}
		});
		$("body").on('click', 'span.move-up', function (event)
		{
			var current_row = $(this).closest('tr');
			var previous_row = current_row.prev();
			if ( previous_row.length ) {
				previous_row.before(current_row);
			}
			table_row_repositioning_method();
		});
		$("body").on('click', 'span.move-down', function (event)
		{
			var current_row = $(this).closest('tr');
			var next_row = current_row.next();
			if ( next_row.length ) {
				next_row.after(current_row);
			}
			table_row_repositioning_method();
		});
		$("form#template-update").on('submit', function (event)
		{
			//event.preventDefault();
			var i = 1;
			var left_blnak = false;
			$("table#draggable-table tbody#draggable-table-rows tr").each(function ()
			{
				var tr = $(this);
				var textbox_value = $(tr).find('input[type="text"]').eq(0).val();
				if ( !textbox_value ) {
					left_blnak = true;
				}
				var hidden_template_order = $(tr).find('input.hidden-template-order');
				$(hidden_template_order).val(i);

				var is_selected = $(tr).find('td:eq(0) input').is(':checked') ? 1 : 0;
				var hidden_line_item_field = $(tr).find('input.hidden-line-item-field');
				$(hidden_line_item_field).val(is_selected);

				++i;
			});

			if ( left_blnak ) {
				alert('Template name field is left blank. Please correct!');
				return false;
			}
		});
	</script>
</body>
</html>