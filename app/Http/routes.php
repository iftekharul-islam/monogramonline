<?php

header('Access-Control-Allow-Origin:  *');
//header('Access-Control-Allow-Methods:  POST, GET, OPTIONS, PUT, DELETE');
//header('Access-Control-Allow-Headers:  Content-Type, X-Auth-Token, Origin, Authorization');

get('trk_order_status', 'ItemController@getOrderStatus');

//cron jobs
get('scripts/getInput', 'StoreController@retrieveData');
get('prints/sendbyscript', 'NotificationController@shipNotify');
get('graphics/sort', 'GraphicsController@sortFiles');
get('note_fix', 'DataController@note_job');
get('cleanup', 'DataController@cleanup');
get('auto_batch/{max_units}', 'ItemController@autoBatch');
get('stock_update', 'InventoryController@updateStock');
get('screenshot', 'ReportController@screenshot');
get('download_images', 'ProductController@download_images');
get('download_sure3d', 'GraphicsController@downloadSure3d');

// get('update_skus', 'ProductController@updateSkus');
get('inventory_images', 'InventoryController@download_images');
get('scripts/ship_date', 'OrderController@checkShipDate');
get('tasks/due', 'TaskController@tasksDue');
get('graphics/sub_summary/{batch_number}', 'PrintController@sublimationSummary'); 
get('graphics/sub_screenshot/{batch_number}', 'PrintController@subScreenshot');
get('graphics/print_wasatch', 'GraphicsController@printWasatch');
get('graphics/auto_print', 'GraphicsController@autoPrint');
get('getwasatchstatus', 'WaDashboardController@index');
get('getwasatch11status', 'WaDashboardController@get11Pc');
get('getwasatch23status', 'WaDashboardController@get23Pc');
get('getwasatch130status', 'WaDashboardController@get130Pc');

get('getshopifyorder', 'OrderController@getShopifyOrder');
get('initial_token_generate_request', 'OrderController@initialTokenGenerateRequest');
get('generate_shopify_token', 'OrderController@generateShopifyToken');
get('getShopifyorderbyordernumber', 'OrderController@getShopifyOrderByOrderNumber');
get('synorderbydate', 'OrderController@synOrderByDate');
get('synOrderBetweenId', 'OrderController@synOrderBetweenId');
get('getcouponproducts', 'CouponController@getCouponProducts');


// Dashboard V2
//custom code on route file
get("dashboard/search-order", 'CustomController@searchOrder');
get("import/ship-station", 'CustomController@shipStation');
get("test/order", 'CustomController@testOrder');
get("conversion/image", 'CustomController@conversionImage');
get("conversion/image2", 'CustomController@conversionImage2');
get("tool/order_status/hold/{id}", 'CustomController@orderStatusHold');
get("option_mass", 'CustomController@option_mass');
get("option_mass", 'CustomController@zakeke_switch_type');

get("zakeke/test1", "ZakekeController@test1");
get("zakeke/test2", "ZakekeController@test2");
get("zakeke/test3", "ZakekeController@test3");
get("zakeke/fetch-all/{type}", "ZakekeController@fetchAll");
get("ship_station/check", "ZakekeController@shipStationCheckOrder");
get("custom/batch", "ZakekeController@customBatch");
get("test/sku_info", "ZakekeController@skuInfo");

// custom route function
get("order/shipping_update", "CustomController@orderShippingUpdate");
get("documentation/shipping", "CustomController@documentationShipping");
get("test/mass_delete2", "CustomController@testMassDelete2");
get("test/mass_delete3", "CustomController@testMassDelete3");
get("est/mass_set_other_hold", "CustomController@estMassSetOtherHold");
get("test/mass_delete", "CustomController@testMassDelete");
get("shipment_cache_all", "CustomController@shipmentCacheAll");
get("store_cache", "CustomController@storeCache");
get("batch_info", "CustomController@batchInfo");
get("order_layout", "CustomController@orderLayout");
get("shipment_cache", "CustomController@shipmentCache");
get("order_layout2", "CustomController@orderLayout2");
get("test_store", "CustomController@testStore");
get("test_export", "CustomController@testExport");
get("test_store_inventory", "CustomController@testStoreInventory");
get("test_order_1", "CustomController@testOrder1");
get("order_test33", "CustomController@orderTest33");
get("test_order_test", "CustomController@testOrderTest");
get("store_inventory_child_sku", "CustomController@storeInventoryChildSku");


/*
 * Get's all the ID of the inventory from the file
 * Then use that to create a cache of child SKU, leading to the inventory ID
 * which can be used to fetch it on the file (Inventories.json)
 */
get("test_order_test2", "CustomController@testOrderTest2");
get("dropship_inventory", "CustomController@dropshipInventory");
get("fetch_order", "CustomController@fetchOrder");
get("test_order_2", "CustomController@testOrder2");
get("shipping-test", "CustomController@shippingTest");
get("prices-test", "CustomController@pricesTest");

//
get("code/test", "CustomController@codeTest");
get("filters/add/{name}", "CustomController@filtersAdd");
get("filters/delete/{name}", "CustomController@filtersDelete");
get("filters/view/{nameFilter}", "CustomController@filtersView");
get("filters/all", "CustomController@filtersAll");
get("filters/clear", "CustomController@filtersClear");
get("fix/image-load/link/{batch_number}", "CustomController@fixImageLoadLink");

// DHL TESTING API --------------------------------------------------------------------------
get('getdhltoken','ShippingController@getDhlAccessToken');
get('dhl_track/{tracking}', 'ShippingController@dhlTrack');
#---------------------------------------------------------------------------


get("lazy/upload-download/{id}", 'GraphicsController@uploadFileFromLink');
get("lazy/upload-download/zakeke/{id}", 'GraphicsController@uploadFileUsingLink');

get("lazy/mass", 'GraphicsController@uploadFileFromLinkMass');

get("lazy/link", 'GraphicsController@uploadFileUsingLink');

//move to QC
get('move_to_qc', 'GraphicsController@selectToMoveQc');
post('move_to_qc/show_batch', 'GraphicsController@showBatchQc');


// auth middleware enabled controller
Route::group([ 'middleware' => [ 'auth' ] ], function () {
	Route::group([ 'middleware' => 'user_has_access' ], function () {
		get('/', 'HomeController@index');
		get('/homepage2', 'HomeController@index2');
		get('logout', 'AuthenticationController@getLogout');


		// DHL Mainfest
        get('shippingMainfest', 'DhlManifestController@index')->name('shipShow');
        get('shippingMainfest/getDhlManifest', 'DhlManifestController@getDhlManifest');
        get('shippingMainfest/getDhlInternationalManifest', 'DhlManifestController@getDhlInternationalManifest');

//        get('getDhlManifest','DhlManifestController@getDhlManifest');
//        get('getDhlInternationalManifest','DhlManifestController@getDhlInternationalManifest');

		//reporting
		get('report/sales_summary', 'ReportController@salesSummary');
		get('report/profit', 'ReportController@profitSummary');
		get('report/order_date', 'ReportController@orderDateReport');
		get('report/history', 'ReportController@section_history');
		get('report/viewPdf', 'ReportController@viewPdf');
		get('report/logs', 'StationLogController@index');
		get('report/data', 'DataController@index');
		get('shipping/must_ship', 'ReportController@mustShipReport');
		get('report/rejects', 'ReportController@rejectsDetail');
		get('report/ship_date', 'ReportController@shipDateReport');
		get('report/coupon', 'ReportController@couponReport');
		get('report/sales', 'ReportController@salesReport');
		get('report/items', 'ReportController@itemsReport');
		
		//production Reports
		get('prod_report/summary', 'ReportController@section');
		get('prod_report/summaryfilter', 'ReportController@sectionFilter');
		get('prod_report/station_summary', 'ReportController@stationSummary');
		get('prod_report/unbatchable', 'ItemController@unBatchableItems');
		get('prod_report/stockreport', 'InventoryController@getStockReport');
		get('prod_report/missing_report', 'WapController@missingReport');
		get('prod_report/wap', 'ReportController@wapSummary');
		
		//configure production
		get('prod_config/sections', 'SectionController@index');
		post('prod_config/sections_update', 'SectionController@store');
		post('prod_config/sections_assign', 'SectionController@assign');
		post('prod_config/sections_delete', 'SectionController@delete');
		resource('prod_config/batch_routes', 'BatchRouteController');
		post('prod_config/import_batch_routes', 'ImportController@importBatchRoute');
		get('prod_config/export_batch_routes', 'ExportController@batch_routes');
		resource('prod_config/templates', 'TemplateController', [
			'except' => [ 'create' ],
		]);
		get('prod_config/rejection_reasons/sort/{direction}/{id}', 'RejectionReasonController@sortOrder');
		resource('prod_config/rejection_reasons', 'RejectionReasonController');
		resource('prod_config/stations', 'StationController');
		get('prod_config/work_config', 'ProductionController@workConfig');
				
		//purchases
		get('purchases/receive', 'PurchaseController@receive');
		put('purchases/receive', 'PurchaseController@receive');
		post('purchases/getVendorById', 'PurchaseController@getVendorById');
		post('purchases/purchased_inv_products', 'PurchaseController@getPurchasedInvProducts');
		// get('autocomplete',array('as'=>'autocomplete'));
		// post('purchases/searchajax', 'PurchaseController@autoComplete');
		post('purchases/purchasedVendorSku', 'PurchaseController@purchasedVendorSku');
		resource('purchases/vendors', 'VendorController');
		get('purchases/print/{purchase_id}', 'PrintController@purchase');
		resource('purchases/purchasedinvproducts', 'PurchasedInvProductsController');
		resource('purchases', 'PurchaseController');
		post('purchases/getuniquestock','InventoryController@getStockNoUnique');
		
		//user Admin
		resource('users', 'UserController');
		get('users/barcode/{id}', 'UserController@barcode');
		
		//products configuration
		resource('products_config/collections', 'CollectionController');
		resource('products_config/occasions', 'OccasionController');
		resource('products_config/categories', 'CategoryController');
		resource('products_config/sub_categories', 'SubCategoryController');
		resource('products_config/production_categories', 'ProductionCategoryController');
		resource('products_config/sales_categories', 'SalesCategoryController');
		get('products_config/master_categories/get_next/{parent_category_id}', 'MasterCategoryController@getNext');
		resource('products_config/master_categories', 'MasterCategoryController');
		
		post('products/change_mixing_status', 'ProductController@change_mixing_status');
		get('products/unassigned', 'ProductController@unassigned');
		get('products/sync', 'ProductController@getSync');
		post('products/sync', 'ProductController@postSync');
		post('products/post_to_yahoo', 'ProductController@post_to_yahoo');
		#get('products/import', 'ProductController@getAddProductsByCSV');
		post('products/import', 'ProductController@import');
		get('products/export', 'ProductController@export');
		
		resource('products', 'ProductController');
		
		get('products_specifications/step/{id?}', 'ProductSpecificationController@getSteps');
		post('products_specifications/step/{id}', 'ProductSpecificationController@postSteps');
		get('products_specifications/copy/{categoty_id}/{product_sku}', 'ProductSpecificationController@copyProduct');
		get('products_specifications/sheets', 'PrintController@print_spec_sheet');
		resource('products_specifications', 'ProductSpecificationController');
		
		//Configure Child Skus
		get('logistics/add_child_sku', 'LogisticsController@get_add_child_sku');
		post('logistics/add_child_sku', 'LogisticsController@post_add_child_sku');
		get('logistics/create_child_sku', 'LogisticsController@create_child_sku');
		post('logistics/create_child_sku', 'LogisticsController@post_create_child_sku');
		post('logistics/post_preview', 'LogisticsController@post_preview');
		
		//batching
		get('preview_batch', 'ItemController@getBatch');
		post('preview_batch', 'ItemController@postBatch');
		
		//graphics
		// get('graphics/dash', 'GraphicsController@dash');
		get('graphics', 'GraphicsController@index');
		get('graphics/print_sublimation', 'GraphicsController@showSublimation');
		post('graphics/print_all', 'GraphicsController@printAllSublimation');
		get('graphics/printer_config', 'GraphicsController@printerConfig');
		get('graphics/delete_file/{graphic_dir}/{file}', 'GraphicsController@deleteFile');
		post('graphics/move_to_print', 'GraphicsController@printSublimation');
		post('graphics/reprint_graphic', 'GraphicsController@reprintGraphic');
		get('graphics/reprint_bulk', 'GraphicsController@reprintBulk');
		get('graphics/sent_to_printer', 'GraphicsController@sentToPrinter');
		post('graphics/complete_manual', 'GraphicsController@completeManual');
		get('graphics/export_batch/{id}/{force}/{format}', 'BatchController@export_batch');
		get('graphics/export_batchbulk', 'BatchController@export_bulk');
		post('graphics/export_batchbulk', 'BatchController@export_bulk');
		post('graphics/upload_file', 'GraphicsController@uploadFile');
		// get('graphics/resize/{id}', 'GraphicsController@resizeSure3d');
		// get('graphics/resizeBatch/{id}', 'GraphicsController@resizeBatch');
		get('graphics/resizeBatch/{id}/{max_size}', 'GraphicsController@resizeBatchMaxSize');
		get('graphics/resizeNatico', 'GraphicsController@resizeNaticoImages');
        get('graphics/download_sure3d_by_item_id', 'GraphicsController@downloadSure3dByItemId');



        resource('graphics/designs', 'DesignController');
		
		//move to production
		get('move_to_production', 'GraphicsController@selectToMove');
		post('move_to_production/show_batch', 'GraphicsController@showBatch');

		//print summaries
		get('summaries/print', 'GraphicsController@selectSummaries');
		post('summaries/print', 'PrintController@batches');
		get('supervisor/print_summaries', 'PrintController@batches');
		get('summaries/single', 'PrintController@singleBatch');
		
		//picking
		get('picking/summary', 'PickingController@getInventorySummary');
		post('picking/report', 'PickingController@printInventoryReport');
		post('picking/view', 'PickingController@viewInventoryReport');
		post('picking/pick', 'PickingController@pickInventoryReport');
		post('picking/delete', 'PickingController@deleteInventoryReport');
		
		//inventory
		get('inventories/ajax_update','InventoryController@updateInventory');
		resource('inventories', 'InventoryController');
		resource('inventoryunit', 'InventoryUnitController');
		
		//inventory Admin
		get('inventory_admin/ajax_update','InventoryController@updateInventory');
		get('inventory_admin/calculate_ordering','InventoryController@calculateOrdering');
		get('inventory_admin/duplicate/{id}', 'InventoryController@create');
		get('inventory_admin/delete', 'InventoryController@delete'); 
		resource('inventory_admin/inventory_adjustments', 'InventoryAdjustmentController');
		
		resource('tasks', 'TaskController');
		
		//manual orders
		get('orders/manual', 'OrderController@getManual');
		post('orders/manual', 'OrderController@postManual');
		get('orders/manual/ajax_search', 'ProductController@ajaxSearch');
		get('orders/manual/product_info', 'ProductController@product_info');
		get('orders/addmanual/{order_id}', 'OrderController@manualReOrder');
		get('orders/ajax', 'OrderController@ajax');
		
		post('orders/mailer', 'NotificationController@getMailMessage');
		post('orders/send_mail', 'NotificationController@send_mail');
		
		//orders & items
		get('orders/details/{order_id}', 'OrderController@details')->name('orderShow');
		post('orders/status_change', 'OrderController@changeStatus');
        get('orders/balk_status_change', 'OrderController@bulkChangeStatus');
		post('orders/searchOrder', 'OrderController@searchOrder');
		post('ship_order/update_method', 'OrderController@updateMethod');
		post('orders/update_shipdate', 'OrderController@updateShipDate');
		get('orders/list', 'OrderController@getList');
		resource('items', 'ItemController', ['except' => 'show']);
		resource('orders', 'OrderController');
		get('orders/packing/{id}', 'PrintController@packing');
        get('items_graphic', 'ItemController@indexGraphic');

		post('orders_admin/change_store', 'OrderController@updateStore');
		get('orders_admin/bulk_release', 'BatchController@releaseBulk');
		
		//production
		get('batches/list', 'BatchController@index');
        get('batches/list_graphic', 'BatchController@indeGraphic');
		get('batches/details/{batch_number}', 'BatchController@show')->name('batchShow');
		get('batches/tray_label/{id}', 'BatchController@trayLabel');
		get('batches/view_graphic', 'GraphicsController@viewGraphic');
		
		get('reject_item', 'RejectionController@reject');
		
		get('move_next', 'ProductionController@openMoveNext');
		post('move_next', 'ProductionController@moveNextStation');

        get('production/expoert', 'ProductionController@openExpoertImage');
        post('production/expoert', 'ProductionController@expoertImage');
		
		//production
		get('production/status', 'ProductionController@status');
		get('production/status_detail', 'ProductionController@statusDetail');
		get('production/scan_work', 'ProductionController@openScanWork');
		post('production/scan_work', 'ProductionController@scanWork');
		get('production/user_section', 'ProductionController@ajaxSection');
		
		post('reject_batch', 'RejectionController@rejectBatch');
		
		//production supervisor
		post('supervisor/scan_work', 'ProductionController@scanWork');
		post('supervisor/move_batch', 'ProductionController@moveNextStation');
		get('supervisor/release/{batch_number}', 'BatchController@release');
		
		// get('export_station', 'StationController@getExportStationLog');
		// post('export_station', 'StationController@postExportStationLog');
		
		//rejection Admin
		get('rejections/', 'RejectionController@index');
		post('rejections/process', 'RejectionController@process');
		get('rejections/split', 'RejectionController@splitBatch');
		get('rejections/reprint', 'RejectionController@reprintLabel');
		post('rejections/send_to_start', 'RejectionController@sendToStart');
		
		//backorders
		get('backorders', 'BackorderController@index');
		get('backorders/batch', 'BackorderController@getBatch');
		get('backorders/show', 'BackorderController@show');
		post('backorders/items', 'BackorderController@itemsBackorder');
		post('backorders/arrive', 'BackorderController@batchArrive');
		post('backorders/items_arrive', 'BackorderController@itemsArrived');
		post('backorders/stock_change', 'BackorderController@stockNumber');
		
		get('items/delete_item/{order_id}/{item_id}', 'ItemController@delete_item_id');
		get('items/restore_item/{order_id}/{item_id}', 'ItemController@restore_item_id');
		get('items/child_sku/{item_id}', 'ItemController@refreshChildSku');
		
		//shipping supervisor
		get('ship_order/ship_from_order', 'ShippingController@shipFromOrder');
		get('ship_order/item_tracking', 'ShippingController@manualShip');
		post('ship_order/returned', 'ShippingController@shipmentReturned');
		post('ship_order/ship_items', 'ShippingController@shipItems');
		get('ship_order/void/{shipment_id}/{order_5p}', 'ShippingController@void');

		//shipper
		post('shipping/ship_items', 'ShippingController@shipItems');
		get('shipping', 'ShippingController@index')->name('shipShow');
		post('shipping', 'ShippingController@index');
		get('shipping/ship_items', 'ShippingController@shipItems');
		post('shipping/add_wap', 'WapController@addItems');
		post('shipping/bad_address', 'WapController@bad_address');
		get('shipping/qc_station', 'QcController@index');
		get('shipping/qc_list', 'QcController@showStation');
		post('shipping/qc_scanIn', 'QcController@scanIn');
		get('shipping/qc_redirect', 'QcController@scanIn');
		get('shipping/qc_batch', 'QcController@showBatch')->name('qcShow');
		post('shipping/qc_order', 'QcController@showOrder');
		get('shipping/qc_order', 'QcController@showOrder')->name('qcOrder');
		
		//wap
		get('wap/index', 'WapController@index');
		get('wap/details', 'WapController@showBin')->name('wapShow');
		get('wap/reprint', 'WapController@reprintWapLabel');
		
		// get('shipping_address_update', 'ShippingController@shippingAddressUpdate');
	
		get('exports/export_orders', 'ItemController@export_orders');
		post('exports/orders', 'OrderController@csvExport');
		post('exports/items', 'ItemController@csvExport');
		
		//customer service
		get('customer_service/index', 'CsController@index');
		get('customer_service/ajax_button', 'CsController@ajaxButton');
		resource('customer_service/customers', 'CustomerController');
		get('customer_service/csProcess', 'RejectionController@csProcess');
		get('customer_service/bulk_email', 'NotificationController@bulk_email');
		post('customer_service/bulk_email', 'NotificationController@bulk_email_post');
		get('customer_service/test/{order_id}/{type}', 'NotificationController@test');
		resource('customer_service/email_templates', 'EmailTemplateController');
		post('customer_service/redo', 'RejectionController@redoItem');
		
		//configure skus
		get('logistics/update_ajax', 'LogisticsController@update_ajax');
		get('logistics/sku_list', 'LogisticsController@sku_list');
		post('logistics/update_skus', 'LogisticsController@update_skus');
		get('logistics/edit_sku', 'LogisticsController@edit_sku');
		put('logistics/edit_sku', 'LogisticsController@update_sku');
		get('logistics/parameters', 'LogisticsController@parameters');
		put('logistics/update_parameters', 'LogisticsController@update_parameters');
		//post('logistics/update_parameter_option/{unique_row}', 'LogisticsController@update_parameter_option');
		delete('logistics/delete_sku/{unique_row_value}', 'LogisticsController@delete_sku');
		
		//stores
		get('stores/test', 'StoreController@email_test');
		get('stores/inventory', 'StoreController@inventoryUpload');
		get('stores/sort/{direction}/{id}', 'StoreController@sortOrder');
		get('stores/visible/{id}', 'StoreController@visible');
		resource('stores', 'StoreController');
		get('store/permission/{id}', 'StoreController@storeAccess')->name('store-permission');
		post('store/permission/update/{id}', 'StoreController@storePermissionUpdate')->name('store-permission-update');

		get('stores/items/{store_id}', 'StoreItemController@index');
		get('stores/items/get_csv/{store_id}', 'StoreItemController@getCSV');
		post('stores/items/post_csv', 'StoreItemController@uploadCSV');
		post('stores/items/add', 'StoreItemController@store');
		post('stores/items/update', 'StoreItemController@update');
		get('stores/items/delete/{id}', 'StoreItemController@destroy');
		
		//import order
        get('transfer/importZakeke', 'StoreController@importZakeke');
        post('transfer/importZakeke', 'StoreController@importZakeke');

		get('transfer/import', 'StoreController@import');
		post('transfer/import', 'StoreController@import');
		get('transfer/export', 'StoreController@exportSummary'); 
		get('transfer/export/details', 'StoreController@exportDetails');
		post('transfer/export/create', 'StoreController@createExport'); 
				
		post('transfer/export/qb', 'StoreController@qbExport');
        post('transfer/export/qbcsv', 'StoreController@qbCsvExport');

	});
});

// guest middleware enabled controller
Route::group([ 'middleware' => [ 'guest' ] ], function () {
	get('login', 'AuthenticationController@getLogin');
	get('login2', 'AuthenticationController@getLogin2');
	post('login', 'AuthenticationController@postLogin');
	post('hook', 'OrderController@hook');
});

// // Redefinition of routes
// get('home', function () {
// 	return redirect(url('/'));
// });

Route::group([ 'prefix' => 'auth' ], function () {
	get('login', 'AuthenticationController@getLogin');
    get('login2', 'AuthenticationController@getLogin2');
	get('logout', 'AuthenticationController@getLogout');
});

Event::listen('illuminate.query', function ($q) {
	#Log::info($q);
});
