<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Edit a Store</title>
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
			<li><a href = "{{url('stores')}}">Manage Stores</a></li>
			@if ($store)
        <li><a href = "{{url('/stores/' .  $store->id . '/edit')}}">{{ $store->store_name }}</a></li>
      @endif
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		
		@if(!empty($store))
			<br>
			<h3>{{ $store->store_name }}</h3>
			<br>
			{!! Form::open(['url' => 'stores/' . $store->id, 'method' => 'put']) !!}
			<table class="table">
				
					<tr>				
						<td width="400">{!! Form::text('store_id', $store->store_id, ['class' => 'form-control', 'disabled' => '1']) !!}</td>		
						<td width="400">{!! Form::text('store_name', $store->store_name, ['class' => 'form-control']) !!}</td>
						<td width="400">
							{!! Form::select('company', $companies, $store->company, ['class' => 'form-control']) !!}
						</td>	
					</tr>
					<tr>
						<td>
							<strong>Class Name:</strong>
							{!! Form::text('email',$store->email, ['class' => 'form-control', 'placeholder' => 'Store E-mail']) !!}
						</td>
						<td>
							<strong>Class Name:</strong>
							{!! Form::text('class_name', $store->class_name, ['class' => 'form-control']) !!}
						</td>
						<td>
							<strong>Data Input:</strong>
							{!! Form::select('input', \App\Store::inputOptions(), $store->input, ['class' => 'form-control']) !!}
						</td>
					</tr>
					<tr>
						<td>
							<strong>Order Items Changeable:</strong>
							{!! Form::select('change_items', ['1' => 'Yes', '0' => 'No'], $store->change_items, ['id' => 'change_items', 'class' => 'form-control']) !!}
						</td>
						<td>
							<strong>Batch Items:</strong>
							{!! Form::select('batch', \App\Store::batchOptions(), $store->batch, ['class' => 'form-control']) !!}
						</td>
						<td>
							<strong>Quality Control:</strong>
							{!! Form::select('qc', \App\Store::qcOptions(), $store->qc, ['class' => 'form-control']) !!}
						</td>
						
					</tr>
					
					<tr>
						<td>
							<strong>Order Confirmation:</strong>
							{!! Form::select('confirm', \App\Store::notifyOptions(), $store->confirm, ['id' => 'confirm', 'class' => 'form-control']) !!}
						</td>
						<td>
							<strong>Shipping Notification:</strong>
							{!! Form::select('ship', \App\Store::notifyOptions(), $store->ship, ['id' => 'ship', 'class' => 'form-control']) !!}
						</td>
						<td>
							<strong>QuickBooks Export:</strong>
							{!! Form::select('qb_export', ['1' => 'On', '0' => 'Off'], $store->qb_export, ['id' => 'qb_export', 'class' => 'form-control']) !!}
						</td>
					</tr>
					<tr class="banner" @if ($store->confirm < 2) style="display:none" @endif>
						<td colspan=3>
							<strong>Notification Banner Image URL:</strong>
							{!! Form::text('ship_banner_image', $store->ship_banner_image, ['class' => 'form-control']) !!}
						</td>
					</tr>
					<tr class="banner" @if ($store->confirm < 2) style="display:none" @endif>
						<td colspan=3>
							<strong>Banner Image Link:</strong>
							{!! Form::text('ship_banner_url', $store->ship_banner_url, ['class' => 'form-control']) !!}
						</td>
					</tr>
					
					<tr>
						<td>
							<strong>Return Address:</strong>
							{!! Form::text('ship_name',  $store->ship_name, ['id' => 'ship_name', 'class' => 'form-control', 'placeholder' => 'Name']) !!}<br>
							{!! Form::text('address1',  $store->address_1, ['id' => 'ship_name', 'class' => 'form-control', 'placeholder' => 'Address 1']) !!}<br>
							{!! Form::text('address2',  $store->address_2, ['id' => 'ship_name', 'class' => 'form-control', 'placeholder' => 'Address 2']) !!}<br>
							<table>
								<tr>
									<td>
										{!! Form::text('city',  $store->city, ['id' => 'city', 'class' => 'form-control', 'placeholder' => 'City', 'style' => 'width: 200px;']) !!}
									</td>
									<td>
										{!! Form::text('state',  $store->state, ['id' => 'state', 'class' => 'form-control', 'placeholder' => 'State', 'style' => 'width: 100px;']) !!}
									</td>
									<td>
										{!! Form::text('zip',  $store->zip, ['id' => 'zip', 'class' => 'form-control', 'placeholder' => 'Zip', 'style' => 'width: 100px;']) !!}
									</td>
								</tr>
							</table>
							<br>
							<strong>Phone Number:</strong>
							{!! Form::text('phone', $store->phone, ['id' => 'phone', 'class' => 'form-control', 'placeholder' => 'Required for Shipping']) !!}

						    </br>
							<strong>Dropship Qualifier:</strong>
							{!! Form::checkbox('dropship', 1, $dropship, ['id' => 'dropship', 'class' => 'checkbox']) !!}
							</br>
							<strong>Dropship Tracking Import:</strong>
							{!! Form::checkbox('dropship_tracking', 1, $dropshipTracking, ['id' => 'dropship_tracking', 'class' => 'checkbox']) !!}
						</td>
						<td>
							<strong>Validate Addresses:</strong>
							{!! Form::select('validate_addresses', ['0' => 'No', '1' => 'Yes'], $store->validate_addresses, ['id' => 'validate_addresses', 'class' => 'form-control']) !!}
							<br>
							<strong>Change Shipping Method:</strong>
							{!! Form::select('change_method', ['0' => 'No', '1' => 'Yes'], $store->change_method, ['id' => 'change_method', 'class' => 'form-control']) !!}
							<br>
							<strong>Additional Shipping Label:</strong>
							{!! Form::select('ship_label', ['0' => 'No', '1' => 'Yes'], $store->ship_label, ['id' => 'ship_label', 'class' => 'form-control']) !!}
							<br>
							<strong>Packing List:</strong>
							{!! Form::select('packing_list', ['Z' => 'Zebra Label', 'P' => 'PDF Template', 'B' => 'Both'], $store->packing_list, ['id' => 'packing_list', 'class' => 'form-control']) !!}
							
						</td>
						<td>
							<strong>Multiple Package Shipping:</strong>
							{!! Form::select('multi_carton', ['0' => 'No', '1' => 'Yes'], $store->multi_carton, ['id' => 'multi_carton', 'class' => 'form-control']) !!}
							<br>
							<strong>UPS:</strong>
							{!! Form::select('ups_type', ['T' => 'Third Party', 'P' => 'Primary Account'], $store->ups_type, ['id' => 'ups_type', 'class' => 'form-control']) !!}
							<br>
							{!! Form::text('ups_account', $store->ups_account, ['id' => 'ups_account', 'class' => 'form-control', 'placeholder' => 'Leave blank to use house account']) !!}
							<br>
							<strong>FedEx:</strong>
							{!! Form::select('fedex_type', ['T' => 'Third Party', 'P' => 'Primary Account'], $store->fedex_type, ['id' => 'fedex_type', 'class' => 'form-control']) !!}
							<br>
							{!! Form::text('fedex_account', $store->fedex_account, ['id' => 'fedex_account', 'class' => 'form-control', 'placeholder' => 'Account']) !!}
							<br>
							{!! Form::text('fedex_key', $store->fedex_key, ['id' => 'fedex_key', 'class' => 'form-control', 'placeholder' => 'Key']) !!}
							<br>
							{!! Form::text('fedex_password', $store->fedex_password, ['id' => 'fedex_password', 'class' => 'form-control', 'placeholder' => 'Password']) !!}
							<br>
							{!! Form::text('fedex_meter', $store->fedex_meter, ['id' => 'fedex_meter', 'class' => 'form-control', 'placeholder' => 'Meter']) !!}
						</td>
					</tr>
			</table>
			

			<div class="pull-right">
				{!! Form::submit('Update ' . $store->store_name, ['class' => 'btn btn-primary btn-xs']) !!}
				{!! Form::close() !!}
			</div>
			<div>
				{!! Form::open(['url' => 'stores/' . $store->id, 'method' => 'delete', 'id' => 'delete-form']) !!}
				{!! Form::button('Delete ' . $store->store_name, ['id' => 'delete', 'class' => 'btn btn-danger btn-xs']) !!}
				{!! Form::close() !!}
			</div>

		@else
			<div class = "col-xs-12">
				<div class = "alert alert-warning text-center">
					No Stores found.
				</div>
			</div>
		@endif
	</div>
	
<script type = "text/javascript">
	
	$("#confirm").on('change', function(event)
	{
		event.preventDefault();
		
		if ($(this).val() > 1) {
			$(".banner").show();
		} else {
			$(".banner").hide();
		}
	});
	
	$("#fedex_type").on('change', function (event)
	{
		event.preventDefault();
		
		if ($(this).val() == 'P') {
			$("#fedex_password").show();
			$("#fedex_key").show();
			$("#fedex_meter").show();
			$("#fedex_account").attr('placeholder', 'Account');
		} else if ($(this).val() == 'T') {
			$("#fedex_password").hide();
			$("#fedex_key").hide();
			$("#fedex_meter").hide();
			$("#fedex_account").attr('placeholder', 'Leave blank to use house account');
		}
	});

	$('#fedex_type').trigger('change');

	var message = {
			delete: 'Are you sure you want to delete?',
	};
	
	$("#delete").on('click', function (event)
	{
			event.preventDefault();
			var action = confirm(message.delete);
			if ( action ) {
					var form = $("form#delete-form");
					form.submit();
			}
	});
		
</script>

</body>
</html>