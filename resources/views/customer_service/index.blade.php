<!doctype html>
<html lang = "en">
<head>
	<meta charset = "UTF-8">
	<title>Customer Service</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/pikaday.min.css">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/chosen.min.css">
	
	<script type = "text/javascript" src = "/assets/js/jquery.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/bootstrap.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/moment.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/pikaday.min.js"></script>
	<script type = "text/javascript" src = "/assets/js/chosen.jquery.min.js"></script>	
	
	<style>
	td {
		word-wrap:break-word
	}
	
	.noline {
		border-bottom:hidden;
	}
	
	.tooltip-inner {
		white-space: pre-wrap;
	}
	
	.divline {
	  border-left:1px solid lightgray;
	  border-right:1px solid lightgray;
	  white-space: pre-wrap;
	}
	</style>
	
</head>
<body>
	
	@include('email_templates.email_modal')
	
	@include('includes.header_menu')
	<div class = "container" style="width:95%;">
		<ol class = "breadcrumb">
			<li><a href = "{{url('/')}}">Home</a></li>
			<li>Customer Service Issues</li>
		</ol>
		
		@include('includes.error_div')
		@include('includes.success_div')
		
		<ul id="myTab" class="nav nav-tabs">
			<li @if($tab == 'address') class="active" @endif><a href="/customer_service/index?tab=address">Bad Addresses ({!! $count['address'] !!})</a></li>
			<li @if($tab == 'backorder') class="active" @endif><a href="/customer_service/index?tab=backorder">Backorders ({!! $count['backorder'] !!})</a></li>
			<li @if($tab == 'rejects') class="active" @endif><a href="/customer_service/index?tab=rejects">CS Rejects ({!! $count['reject'] !!})</a></li>
			<li @if($tab == 'reship') class="active" @endif><a href="/customer_service/index?tab=reship">Returned Shipments ({!! $count['reship'] !!})</a></li>
			<li @if($tab == 'incompatible') class="active" @endif><a href="/customer_service/index?tab=incompatible">Incompatible ({!! $count['incompatible'] !!})</a></li>
			<li @if($tab == 'payment') class="active" @endif><a href="/customer_service/index?tab=payment">Payment Holds ({!! $count['payment'] !!})</a></li>
			<li @if($tab == 'shipping') class="active" @endif><a href="/customer_service/index?tab=shipping">Shipping Holds ({!! $count['shipping'] !!})</a></li>
			<li @if($tab == 'other') class="active" @endif><a href="/customer_service/index?tab=other">Other Holds ({!! $count['other'] !!})</a></li>
			<li @if($tab == 'updates') class="active" @endif><a href="/customer_service/index?tab=updates">Update Log</a></li>
		</ul>
		
		<div id="tabContent" class="tab-content">
			
			<div class="tab-pane fade @if($tab == 'address') in active @endif" id="address">
				@if (isset($addresses) && count($addresses) > 0)
				 <br>
				 @include('customer_service.includes.addresses')
				@endif
			</div>
			
			<div class="tab-pane fade @if($tab == 'backorder') in active @endif" id="backorder">
				@if ((isset($backorders) && count($backorders) > 0) || 
							(isset($bo_summary) && count($bo_summary) > 0))
				 <br>
				  @include('customer_service.includes.backorders')
				@endif
			</div>
			
			<div class="tab-pane fade @if($tab == 'rejects') in active @endif" id="rejects">
			 <br>
			 	@if(isset($reject_batches) && count($reject_batches) > 0)
	 				
					<div class="col-md-6">
						<h4>
						<a href = "{{ url('/customer_service/index?tab=rejects') }}"
                data-toggle = "tooltip" data-placement = "top"
                title = "Back to Summary">
                <i class = 'glyphicon glyphicon-arrow-left text-primary' style="font-size:20px;"></i>
            </a>{{ count($reject_batches) }} Batches Found
					</h4>
					</div>
			 		@include('rejections.cs_rejects');
	 			
				@elseif (isset($reject_summary) && count($reject_summary) > 0)
				
					@include('customer_service.includes.reject_summary')
					
				@else
						<div class = "alert alert-warning text-center">No Rejects Found</div>
				@endif
			</div>
			
			<div class="tab-pane fade @if($tab == 'reship') in active @endif" id="reship">
				@if (isset($reship) && count($reship) > 0)
				 <br>
				 @include('customer_service.includes.reships')
				@endif
			</div>
			
			<div class="tab-pane fade @if($tab == 'incompatible') in active @endif" id="incompatible">
				@if (isset($incompatible) && count($incompatible) > 0)
					<br>
					 @include('customer_service.includes.incompatible')
				@endif
			</div>
			
			<div class="tab-pane fade @if($tab == 'payment') in active @endif" id="payment">
				@if (isset($payment) && count($payment) > 0)
					<br>
					@include('customer_service.includes.payment')
				@endif
			</div>
			
			<div class="tab-pane fade @if($tab == 'other') in active @endif" id="other">
				@if (isset($other) && count($other) > 0)
					<br>
					@include('customer_service.includes.other')
				@endif
			</div>
			
			<div class="tab-pane fade @if($tab == 'shipping') in active @endif" id="shipping">
				@if (isset($shipping) && count($shipping) > 0)
					<br>
					@include('customer_service.includes.shipping')
				@endif
			</div>
			
			<div class="tab-pane fade @if($tab == 'updates') in active @endif" id="updates">
				@if (isset($updates) && count($updates) > 0)
					<br>
					@include('customer_service.includes.updates')
				@endif
			</div>
			
		</div>
	</div>
		
	<script type = "text/javascript">

		var picker = new Pikaday(
		{
				field: document.getElementById('eta_datepicker'),
				format : "dddd MMMM Do, YYYY",
				minDate: new Date('2016-06-01'),    
		});
			 
		 $(".ajax_form").submit(function(event) {
			 
			 event.preventDefault();
			 var submit = $(this).find(':submit');
			 submit.val('Wait...');
			 submit.attr("disabled","disabled"); 
			 
			 $.ajax({
				 method: 'get',
				 url: '{{ url("/customer_service/ajax_button") }}',
				 data: $(this).serialize(),
				 context: this,
				 success: function (response) {
					 submit.val(response);
					 if (response != 'Success') { 
					 	submit.addClass('btn-danger');
					}
				},
				failure: function (response) {
					 submit.val(response);
					 submit.addClass('btn-danger');
				 } 
			 });
		 });
			 
	</script>
	
</body>
</html>