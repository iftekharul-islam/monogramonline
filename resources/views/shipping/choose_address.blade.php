<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Choose Shipping Address</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet"
	      href = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
	<link type = "text/css" rel = "stylesheet"
	      href = "//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
</head>
<body>
	@include('includes.header_menu')
	<div class = "container">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li>Choose Shipping Address</li>
		</ol>
		@include('includes.error_div')
		@include('includes.success_div')

		<div class = "col-xs-12 text-right" style = "margin: 10px 0;">
			
			<div class = "col-xs-12">
				<div class = "text-left"><h3>Order Address:</h3></div>
				<br>
				<div class = "text-left">
					{{ $customer['ship_full_name'] }}<br>
					@if ($customer['ship_company_name'] != NULL) 
						{{ $customer['ship_company_name'] }}<br>
					@endif
					{{ $customer['ship_address_1'] }}<br>
					@if ($customer['ship_address_2'] != NULL) 
						{{ $customer['ship_address_2'] }}<br>
					@endif
					{{ $customer['ship_city'] }}, {{ $customer['ship_state'] }}<br>
					{{ $customer['ship_zip'] }}<br>
					{{ $customer['ship_country'] }}<br>
				</div>
			</div>
			
			@if (count($ambiguousAddress) > 0)	
				<div class = "col-xs-12">
					<div class = "text-left"><h3>Suggested Addresses</h3></div>
				</div>
				
				@setvar($count = 1)
				
				@foreach($ambiguousAddress as $address)
				<div class = "col-xs-12">
					<br>
					<div class = "text-left">
						<div>
							{{ $count++ }}) {{	$address['addressLine']	}}, {{ $address['region'] }}
							{{-- address1={{ $address['addressLine'] }}&city={{ $address['politicalDivision2'] }}&state_city={{ $address['politicalDivision1'] }}&postal_code={{ $address['postcodePrimaryLow'] }}&country={{ $address['countryCode'] }} --}}
							&nbsp;&nbsp;
							<a href = "{{ url(sprintf("/shipping_address_update?
								customer_id=%d
								&order_id=%s
								&origin=%s
								&batch_number=%s
								&address1=%s
								&city=%s
								&state_city=%s
								&postal_code=%s
								&country=%s", 
								$customer_id,
								$order_id, 
								$origin,
								$batch_number,
								$address['addressLine'], 
								$address['politicalDivision2'], 
								$address['politicalDivision1'], 
								$address['postcodePrimaryLow'], 
								$address['countryCode'] 
								)) }}">Use This Address
							</a>
						</div>
					</div>
				</div>							
				@endforeach
			@endif
	<script type = "text/javascript" src = "//code.jquery.com/jquery-1.11.3.min.js"></script>
	<script type = "text/javascript" src = "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</body>
</html>