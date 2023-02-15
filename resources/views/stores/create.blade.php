<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Create a Store</title>
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
			<li>Create a Store</li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')
		
			<br>
			<h3>Create Store</h3>
			<br>
			{!! Form::open(['url' => 'stores', 'method' => 'post']) !!}
			
			<table class="table">
				
					<tr>
						<td width="400">
							{!! Form::label('Name', 'Name:') !!}
							{!! Form::text('store_name', '', ['class' => 'form-control']) !!}
						</td>
						<td width="400">
							{!! Form::label('ID', 'ID:') !!}
							{!! Form::text('store_id', '', ['class' => 'form-control']) !!}
						</td>	
						<td width="400">
							{!! Form::label('Company', 'Company:') !!}
							{!! Form::select('company', $companies, '', ['class' => 'form-control']) !!}
						</td>	
					</tr>
					<tr>
						<td>
							<strong>E-mail:</strong>
							{!! Form::text('email', '', ['class' => 'form-control']) !!}
						</td>
						<td>
							<strong>Class Name:</strong>
							{!! Form::text('class_name', '', ['class' => 'form-control']) !!}
						</td>
						<td>
							<strong>Data Input:</strong>
							{!! Form::select('input', \App\Store::inputOptions(), '', ['class' => 'form-control']) !!}
						</td>
					</tr>
					<tr>
						<td>
							<strong>Order Items Changeable:</strong>
							{!! Form::select('change_items', ['1' => 'Yes', '0' => 'No'], '', ['id' => 'change_items', 'class' => 'form-control']) !!}
						</td>
						<td>
							<strong>Batch Items:</strong>
							{!! Form::select('batch', \App\Store::batchOptions(), '', ['class' => 'form-control']) !!}
						</td>
						<td>
							<strong>Quality Control:</strong>
							{!! Form::select('qc', \App\Store::qcOptions(), '', ['class' => 'form-control', 'disabled' => '1']) !!}
						</td>
						<td>
							
						</td>
					</tr>
					
					<tr>
						<td>
							<strong>Order Confirmation:</strong>
							{!! Form::select('confirm', \App\Store::notifyOptions(), '', ['id' => 'confirm', 'class' => 'form-control']) !!}
						</td>
						<td>
							<strong>Shipping Notification:</strong>
							{!! Form::select('ship', \App\Store::notifyOptions(), '', ['id' => 'ship', 'class' => 'form-control']) !!}
						</td>
						<td>
							<strong>QuickBooks Export:</strong>
							{!! Form::select('qb_export', ['1' => 'On', '0' => 'Off'], '', ['id' => 'qb_export', 'class' => 'form-control']) !!}
						</td>
					</tr>
					<tr class="banner" style="display:none">
						<td colspan=3>
							<strong>Notification Banner Image URL:</strong>
							{!! Form::text('ship_banner_image', '', ['class' => 'form-control']) !!}
						</td>
					</tr>
					<tr class="banner" style="display:none">
						<td colspan=3>
							<strong>Banner Image Link:</strong>
							{!! Form::text('ship_banner_url', '', ['class' => 'form-control']) !!}
						</td>
					</tr>
					
					<tr>
						<td>
							<strong>Return Address:</strong>
							{!! Form::text('ship_name',  '',  ['id' => 'ship_name', 'class' => 'form-control', 'placeholder' => 'Name']) !!}<br>
							{!! Form::text('address1',  '', ['id' => 'address1', 'class' => 'form-control', 'placeholder' => 'Address 1']) !!}<br>
							{!! Form::text('address2',  '', ['id' => 'address2', 'class' => 'form-control', 'placeholder' => 'Address 2']) !!}<br>
							<table>
								<tr>
									<td>
										{!! Form::text('city',  '', ['id' => 'city', 'class' => 'form-control', 'placeholder' => 'City', 'style' => 'width: 200px;']) !!}
									</td>
									<td>
										{!! Form::text('state',  '', ['id' => 'state', 'class' => 'form-control', 'placeholder' => 'State', 'style' => 'width: 100px;']) !!}
									</td>
									<td>
										{!! Form::text('zip',  '', ['id' => 'zip', 'class' => 'form-control', 'placeholder' => 'Zip', 'style' => 'width: 100px;']) !!}
									</td>
								</tr>
							</table>
							<br>
							<strong>Phone Number:</strong>
							{!! Form::text('phone', '', ['id' => 'phone', 'class' => 'form-control', 'placeholder' => 'Required for Shipping']) !!}
						</td>
						<td>
							<strong>Validate Addresses:</strong>
							{!! Form::select('validate_addresses', ['0' => 'No', '1' => 'Yes'], '', ['id' => 'validate_addresses', 'class' => 'form-control']) !!}
							<br>
							<strong>Change Shipping Method:</strong>
							{!! Form::select('change_method', ['0' => 'No', '1' => 'Yes'], '', ['id' => 'change_method', 'class' => 'form-control']) !!}
							<br>
							<strong>Additional Shipping Label:</strong>
							{!! Form::select('ship_label', ['0' => 'No', '1' => 'Yes'], '', ['id' => 'ship_label', 'class' => 'form-control']) !!}
							<br>
							<strong>Packing List:</strong>
							{!! Form::select('packing_list', ['Z' => 'Zebra Label', 'P' => 'PDF Template', 'B' => 'Both'], '', ['id' => 'packing_list', 'class' => 'form-control']) !!}
							
						</td>
						<td>
							<strong>Multiple Package Shipping:</strong>
							{!! Form::select('multi_carton', ['0' => 'No', '1' => 'Yes'], '', ['id' => 'multi_carton', 'class' => 'form-control']) !!}
							<br>
							<strong>UPS:</strong>
							{!! Form::select('ups_type', ['T' => 'Third Party', 'P' => 'Primary Account'], 'T', ['id' => 'ups_type', 'class' => 'form-control']) !!}
							<br>
							{!! Form::text('ups_account','', ['id' => 'ups_account', 'class' => 'form-control', 'placeholder' => 'Leave blank to use house account']) !!}
							<br>
							<strong>FedEx:</strong>
							{!! Form::select('fedex_type', ['T' => 'Third Party', 'P' => 'Primary Account'], 'T', ['id' => 'fedex_type', 'class' => 'form-control']) !!}
							<br>
							{!! Form::text('fedex_account', '', ['id' => 'fedex_account', 'class' => 'form-control', 'placeholder' => 'Account']) !!}
							<br>
							{!! Form::text('fedex_key', '', ['id' => 'fedex_key', 'class' => 'form-control', 'placeholder' => 'Key']) !!}
							<br>
							{!! Form::text('fedex_password', '', ['id' => 'fedex_password', 'class' => 'form-control', 'placeholder' => 'Password']) !!}
							<br>
							{!! Form::text('fedex_meter', '', ['id' => 'fedex_meter', 'class' => 'form-control', 'placeholder' => 'Meter']) !!}
						</td>
					</tr>
					
			</table>
			
			<div class="pull-right">
				{!! Form::submit('Create Store', ['class' => 'btn btn-primary']) !!}
				{!! Form::close() !!}
			</div>
			
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
		
	</script>
</body>
</html>