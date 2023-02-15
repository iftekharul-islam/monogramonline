<!DOCTYPE html>
<html>
<head>
	<title>{{env('APPLICATION_NAME')}} - Home</title>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link type = "text/css" rel = "stylesheet" href = "/assets/css/bootstrap.min.css">
	<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>


	@include('includes.header_menu')
	<div class = "container">
		@include('includes.error_div')
		@include('includes.success_div')
		<!-- <div class = "col-xs-12">
			<b>Notice Board:</b>
			{{--
			<div class="alert alert-danger">
			  <strong>Danger!</strong> Please don't use Endicia until this message delete
			</div>
			--}}
		</div> -->
		<div class="row">
			<div class = "col-xs-4">
				<div>
					<h5 class = "page-header">Inventory</h5>
					<ul>
						@if(in_array(array_search('purchases', $access), $user_access))<li><a href = "/purchases">Purchase Orders</a></li>@endif
						@if(in_array(array_search('Inventory Admin', $access), $user_access))<li><a href = "/purchases/purchasedinvproducts">Purchase Products List</a></li>@endif
						@if(in_array(array_search('purchases', $access), $user_access))<li><a href = "/purchases/vendors">Vendors</a></li>@endif
						@if(in_array(array_search('Inventory', $access), $user_access))<li><a href = "/inventories">Inventory</a></li>@endif
						<li><a href = "/inventory_admin/inventory_adjustments">Adjustments</a></li>
					</ul>
				</div>
				<div>
					<h5 class = "page-header">Product Management</h5>
					<ul>
						<li><a href = "/logistics/sku_list">Configure Child SKUs</a></li>
						@if(in_array(array_search('Child SKU Config', $access), $user_access))<li><a href = "/logistics/create_child_sku">Create Child SKU</a></li>@endif
						@if(in_array(array_search('Products', $access), $user_access))<li><a href = "/products">Products ( SKUs ) </a></li>@endif
						{{-- <li><a href = "/products/sync">Sync products</a></li> --}}
						@if(in_array(array_search('Specifications', $access), $user_access))<li><a href = "/products_specifications">Product specification sheet</a></li>@endif
					</ul>
				</div>
				<div>
					<h5 class = "page-header">Maintenance</h5>
					<ul>
						<li><a href = "/users">Users</a></li>
						<li><a href = "/prod_config/sections">Sections</a></li>
						<li><a href = "/prod_config/stations">Stations</a></li>
						<!-- <li><a href = "/prod_config/work_config">Configure Production</a></li> -->
						<li><a href = "/prod_config/templates">Route Templates</a></li>
						<li><a href = "/prod_config/batch_routes">Routes</a></li>
						<!-- <li><a href = "/export_station">Export station log</a></li> -->
						<li><a href = "/prod_config/rejection_reasons">Rejection reasons</a></li>
						<li><a href = "/logistics/parameters">Parameters</a></li>
						<!-- <li><a href = "/products_config/master_categories">Categories</a></li> -->
						<li><a href = "/products_config/production_categories">Production Categories</a></li>
						<!-- <li><a href = "/products_config/sales_categories">Sales Category</a></li> -->
						<!-- <li><a href = "/products_config/collections">Collection</a></li> -->
						<!-- <li><a href = "/products_config/occasions">Occasion</a></li> -->
					</ul>
				</div>
			</div>
			<div class = "col-xs-4">
				<div>
					<h5 class = "page-header">Graphics</h5>
					<ul>
						<li><a href = "/preview_batch">Preview Batches</a></li>
						<li><a href = "/prod_report/unbatchable">Unbatchable Items</a></li>
						@if(in_array(array_search('Graphics', $access), $user_access))<li><a href = "/graphics">Create Graphics</a></li>@endif
						@if(in_array(array_search('Graphics', $access), $user_access))<li><a href = "/graphics/print_sublimation">Print Sublimation</a></li>@endif
						@if(in_array(array_search('Graphics', $access), $user_access))<li><a href = "/graphics/sent_to_printer">Sent to Printer</a></li>@endif
						<li><a href = "/summaries/print">Print Batch Summaries</a></li>
						@if(in_array(array_search('Move to Production', $access), $user_access))<li><a href = "/move_to_production">Move to Production</a></li>@endif
						<li><a href = "/move_to_qc">Move to QC</a></li>
					</ul>
				</div>
				<div>
					<h5 class = "page-header">Production</h5>
					<ul>
						<li><a href = "/batches/list">Batch List</a></li>
						<li><a href = "/batches/list_graphic">Batch List Graphic</a></li>
						{{-- <li><a href = "/batches/summaries">Print Batch Summaries</a></li> --}}
						<li><a href = "/picking/summary">Inventory Summary</a></li>
						@if(in_array(array_search('Move Batches', $access), $user_access))<li><a href = "/move_next">Move Batches</a></li>@endif
						@if(in_array(array_search('Production', $access), $user_access))<li><a href = "/production/status">Production Stations</a></li>@endif
						@if(in_array(array_search('Rejects Screen', $access), $user_access))<li><a href = "/rejections">Rejects</a></li>@endif
						@if(in_array(array_search('Backorders', $access), $user_access))<li><a href = "/backorders">Back Orders</a></li>@endif
					</ul>
				</div>
				<div>
					<h5 class = "page-header">Shipping and WAP</h5>
					<ul>
						@if(in_array(array_search('Shipping', $access), $user_access))<li><a href = "/shipping/must_ship">Must Ship Report</a></li>@endif
						@if(in_array(array_search('Shipping', $access), $user_access))<li><a href = "/shipping/qc_station">Quality Control</a></li>@endif
						@if(in_array(array_search('WAP Screen', $access), $user_access))<li><a href = "/wap/index">WAP</a></li>@endif
						@if(in_array(array_search('Shipping', $access), $user_access))<li><a href = "/shipping">Shipment List</a></li>@endif
						@if(in_array(array_search('Shipping', $access), $user_access))<li><a href = "/shippingMainfest">DHL Driver Manifest</a></li>@endif
					</ul>
				</div>
			</div>
			<div class = "col-xs-4">
				<div>
					<h5 class = "page-header">Customer Service</h5>
					<ul>
						@if(in_array(array_search('Orders', $access), $user_access))<li><a href = "/orders/list">Orders</a></li>@endif
						@if(in_array(array_search('Items List', $access), $user_access))<li><a href = "/items">Items List</a></li>@endif
						<li><a href = "/items_graphic">Items List Graphic</a></li>
						@if(in_array(array_search('Customer Service', $access), $user_access))<li><a href = "/customer_service/index">Customer Service</a></li>@endif
						<li><a href = "/customer_service/email_templates">Email Templates</a></li>
						<li><a href = "/customer_service/bulk_email">Send Bulk Emails</a></li>
						@if(in_array(array_search('Manual Orders', $access), $user_access))<li><a href = "/orders/manual"><strong>Manual Orders</strong></a></li>@endif
					</ul>
				</div>
				<div>
					<h5 class = "page-header">Reports</h5>
					<ul>
						<li><a href = "prod_report/station_summary">Stations summary</a></li>
						<li><a href = "/prod_report/summary">Section Report</a></li>
						<li><a href = "/prod_report/summaryfilter">Section Report Filter</a></li>
						<li><a href = "/prod_report/stockreport">Stock Report</a></li>
						<li><a href = "/report/logs">Station logs</a></li>
						<li><a href = "report/rejects">Reject Report</a></li>
						<li><a href = "/report/ship_date">Ship Date Report</a></li>
						<li><a href = "/report/items">Order Items Report</a></li>
						<li><a href = "/prod_report/missing_report">WAP Missing Items</a></li>
						@if(in_array(array_search('Other Reports', $access), $user_access))<li><a href = "/report/sales">Sales Summary</a></li>@endif
						<!-- <li><a href = "/report/profit">Profit Report</a></li> -->
						<li><a href = "/report/coupon">Coupon Report</a></li>
					</ul>
				</div>
				<div>
					<h5 class = "page-header">Marketplace</h5>
					<ul>
						@if(in_array(array_search('Marketplace', $access), $user_access))<li><a href = "/stores">Manage Stores</a></li>@endif
						@if(in_array(array_search('Import Orders', $access), $user_access))<li><a href = "/transfer/import">Import Orders</a></li>@endif
						<li><a href = "/transfer/export">Export Shipments</a></li>
						<li><a href = "/transfer/export?drop=true">Export Shipments (Dropship)</a></li>
					</ul>
				</div>

				<div>
					<h5 class = "page-header">Zakeke Configuration</h5>
					<ul>
						<li><a onclick="handleAxe()" href = "#">Axe & Co</a></li>
						<li><a onclick="handlePWS()" href = "#">PWS</a></li>
					</ul>
					<script type="application/javascript">
						function httpGet(theUrl)
						{
							var xmlHttp = new XMLHttpRequest();
							xmlHttp.open( "GET", theUrl, false ); // false for synchronous request
							xmlHttp.send( null );
							return xmlHttp.responseText;
						}

						function handleAxe() {

							var type = "axe"

							Swal.fire({
								title: 'What do you like to do?</br>Current Status: {{ Cache::get("ZAKEKE_AXE") ? "ON" : "OFF" }}',
								showDenyButton: true,
								showCancelButton: true,
								confirmButtonText: 'Turn Cronjob ON',
								denyButtonText: `Turn Cronjob OFF`,
							}).then((result) => {
								/* Read more about isConfirmed, isDenied below */
								if (result.isConfirmed) {
									Swal.fire('Done, the Cronjob has been turned ON', '', 'success')
									httpGet("zakeke/switch/" + type + "/true")
									setTimeout(function () {
										location.reload();
									}, 200)
								} else if (result.isDenied) {
									Swal.fire('Cronjob has been turned OFF', '', 'info')
									httpGet("zakeke/switch/" + type + "/false")
									setTimeout(function () {
										location.reload();
									}, 1200)
								}

							})
						}

						function handlePWS() {


							var type = "pws";

							Swal.fire({
								title: 'What do you like to do?</br>Current Status: {{ Cache::get("ZAKEKE_PWS") ? "ON" : "OFF" }}',
								showDenyButton: true,
								showCancelButton: true,
								confirmButtonText: 'Turn Cronjob ON',
								denyButtonText: `Turn Cronjob OFF`,
							}).then((result) => {
								/* Read more about isConfirmed, isDenied below */
								if (result.isConfirmed) {
									Swal.fire('Done, the Cronjob has been turned ON', '', 'success')
									httpGet("zakeke/switch/" + type + "/true")
									setTimeout(function () {
										location.reload();
									}, 200)
								} else if (result.isDenied) {
									Swal.fire('Cronjob has been turned OFF', '', 'info')
									httpGet("zakeke/switch/" + type + "/false")
									setTimeout(function () {
										location.reload();
									}, 1200)
								}

							})
						}
					</script>
				</div>
			</div>
		</div>
	</body>
						{{--<li><a href = "/products_config/categories">Sub Category 1</a></li>
						<li><a href = "/products_config/sub_categories">Sub Category 2</a></li>--}}
					</ul>
				</div>
			</div>
		</div>
</html>
