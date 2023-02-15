<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Order;
use App\Item;
use App\Store;
use App\Station;
use App\Section;
use App\Rejection;
use App\Wap;
use Illuminate\Support\Facades\Cache;

class ReportController extends Controller
{

	public function salesSummary (Request $request) {
		
			$request->has('start_date') ? $start_date = $request->get('start_date') : $start_date = date("Y-m-d");
			$request->has('end_date') ? $end_date = $request->get('end_date') : $end_date = date("Y-m-d");
			$request->has('grouping') ? $grouping = $request->get('grouping') : $grouping = 'coupon';
			
			if ($grouping == 'coupon') {
				
				$orders = Order::where('is_deleted', '0')
										->withinDate($start_date, $end_date)
										->selectRaw("CASE WHEN (orders.promotion_id LIKE 'g%' or orders.promotion_id LIKE 'es%'
																							or orders.promotion_id LIKE ' g%' or orders.promotion_id LIKE 'G%') 
																			THEN 'Groupon' ELSE orders.promotion_id END as col1,
																	CASE WHEN (orders.coupon_id LIKE 'g%' or orders.coupon_id LIKE 'es%'
																							or orders.coupon_id LIKE ' g%' or orders.coupon_id LIKE 'G%') 
																			THEN 'Groupon' ELSE orders.coupon_id END as col2,
																	SUM(orders.total) as orders_total, SUM(orders.shipping_charge) as shipping_total, 
																	count(*) as order_count")
										->StoreId($request->get('store'))
										->groupBy('col1')
										->groupBy('col2')
										->get(); 
				
				$order_items = Order::join('items', 'orders.id', '=', 'items.order_5p')
												->leftjoin('products', 'items.item_code', '=', 'products.product_model')
												->where('orders.is_deleted', '0')
												->withinDate($start_date, $end_date)
												->selectRaw("CASE WHEN (orders.promotion_id LIKE 'g%' or orders.promotion_id LIKE 'es%'
																									or orders.promotion_id LIKE ' g%' or orders.promotion_id LIKE 'G%') 
																					THEN 'Groupon' ELSE orders.promotion_id END as promotion,
																			CASE WHEN (orders.coupon_id LIKE 'g%' or orders.coupon_id LIKE 'es%'
																									or orders.coupon_id LIKE ' g%' or orders.coupon_id LIKE 'G%') 
																					THEN 'Groupon' ELSE orders.coupon_id END as coupon,
																			items.item_code, items.item_thumb, products.product_name,
																			sum(items.item_quantity) as quantity")
												->StoreId($request->get('store'))
												->groupBy('promotion')
												->groupBy('coupon')
												->groupBy('items.item_code')
												->orderBy('quantity', 'DESC')
												->get(); 
												
				$headers = ['col1' => 'Promotion', 'col2' => 'Coupon'];
								
			} else if ($grouping == 'store') {
				
				$orders = Order::leftjoin('stores', 'orders.store_id', '=', 'stores.store_id')
										->where('orders.is_deleted', '0')
										->withinDate($start_date, $end_date)
										->selectRaw("orders.store_id, stores.store_name as col1, '' as col2,
																	SUM(orders.total) as orders_total, SUM(orders.shipping_charge) as shipping_total, 
																	count(*) as order_count")
										->StoreId($request->get('store'))
										->groupBy('store_id')
										->get(); 
				
				$order_items = Order::join('items', 'orders.id', '=', 'items.order_5p')
										->leftjoin('stores', 'orders.store_id', '=', 'stores.store_id')
										->leftjoin('products', 'items.item_code', '=', 'products.product_model')
										->where('orders.is_deleted', '0')
										->withinDate($start_date, $end_date)
										->StoreId($request->get('store'))
										->selectRaw("stores.store_name, items.item_code, products.product_name, items.item_thumb, 
																			sum(items.item_quantity) as quantity")
										->groupBy('stores.store_name')
										->groupBy('items.item_code')
										->orderBy('quantity', 'DESC')
										->get(); 
										
				$headers = ['col1' => 'Store', 'col2' => ''];

			}
									
			$group_list = ['coupon' => 'Coupon', 'store' => 'Store'];
			
			
			return view('reports.sales_summary', compact('orders', 'order_items', 'group_list', 'grouping', 'start_date', 'end_date', 'headers'));

	} 
	
	public function profitSummary (Request $request) {
		
		$orders = Order::with('items.inventoryunit.inventory')
						 ->where('is_deleted', '0')
						 ->storeId($request->get('store'))
						 ->status($request->get('status'))
						 ->searchShipping($request->get('shipping_method'))
						 ->withinDate($request->get('start_date'), $request->get('end_date'))
						 ->search($request->get('search_for_first'), $request->get('operator_first'), $request->get('search_in_first'))
						 ->search($request->get('search_for_second'), $request->get('operator_second'), $request->get('search_in_second'))
						 ->latest()
						 ->paginate(50);
		
		$order_info = array();
		
		foreach ($orders as $order) {
				
				$order_info[$order->id] = array();
				$order_info[$order->id]['items'] = array();
				$total_cost = 0;
				$total_labor = 0;
				
				foreach ($order->items as $item) {
						$cost = 0;
						
						if ($item->inventoryunit) {
							
							foreach ($item->inventoryunit as $unit) {
								if ($unit->inventory) {
									$cost += ($unit->inventory->last_cost * $unit->unit_qty);
								}
							}
						
						}
						
						$total_cost += $cost;
						$total_labor += 2;
						
						$order_info[$order->id]['items'][] = [ 'item_code' => $item->item_code, 'item_description' => $item->item_description, 'item_quantity' => $item->item_quantity, 'item_unit_price' => $item->item_unit_price, 'cost' => $cost, 'labor' => 2.00];
				}
				 
				$shipping_cost = 3;
				
				$order_info[$order->id]['shipping_cost'] = $shipping_cost;
				$order_info[$order->id]['total_cost'] = $total_cost;
				$order_info[$order->id]['total_labor'] = $total_labor;
				
				if ($total_cost > 0) {
					$profit = $order->total - $total_cost - $total_labor - $shipping_cost;
					$order_info[$order->id]['profit'] = '$' . number_format($profit, 2);
					if ($order->total != 0) {
						$order_info[$order->id]['margin'] = intval(($profit / $order->total) * 100) . '%';
					} else {
						$order_info[$order->id]['margin']  = '-';
					}
					
				} else {
					$order_info[$order->id]['profit'] = '-';
					$order_info[$order->id]['margin'] = '-';
				}
				
		}
	
		$statuses = Order::statuses();
		
		$stores = Store::list('%', '%', ['All', 'all']);
		
		$operators = [ 	'in' => 'In', 
										'not_in' => 'Not In', 
										'starts_with' => 'Starts With', 
										'ends_with' => 'Ends With', 
										'equals' => 'Equals', 
										'not_equals' => 'Not Equal',
										'less_than' => 'Less Than', 
										'greater_than' => 'Greater Than', 
										// 'blank' => 'Is Blank',
										// 'not_blank' => 'Is Not Blank'   
									];
										
		return view('reports.profit_summary', compact('orders', 'order_info', 'stores', 'statuses', 'operators', 'request'))
								->with('search_in', Order::$search_in);
		
	}
	
	public function section(Request $request) {
		
		$request->has('store_ids') ? $store_ids = $request->get('store_ids') : $store_ids = null;
        $request->has('company') ? $company_id = $request->get('company') : $company_id = null;
		$request->has('max_date') ? $max_date = $request->get('max_date') . ' 23:59:59' : $max_date = date("Y-m-d") . ' 23:59:59';
		$request->has('batch_type') ? $batch_type = $request->get('batch_type') : $batch_type = '%';

		$dates = array();
		$date[] = date("Y-m-d");
		$date[] = date("Y-m-d", strtotime('-3 days') );
		$date[] = date("Y-m-d", strtotime('-4 days') );
		$date[] = date("Y-m-d", strtotime('-7 days') );
		$date[] = date("Y-m-d", strtotime('-8 days') );
		
		$items = Item::join('batches', 'batches.batch_number', '=', 'items.batch_number')
										->join('orders', 'items.order_5p', '=', 'orders.id')
										->join('stations', 'batches.station_id', '=', 'stations.id')
										->join('sections', 'stations.section', '=', 'sections.id')
										->searchStore($store_ids)
										->where('batches.status', 2)
										->where('batches.batch_number', 'LIKE', $batch_type)
										->where('batches.min_order_date', '<', $max_date)
										->where('items.item_status', 1)
										->where('stations.type', '!=', 'Q')
										//->where('orders.order_status', 4)
										->groupBy ( 'stations.station_name' )
										//->groupBy ( 'orders.order_status' )
										->orderBy ( 'sections.section_name' )
										->orderBy ( 'stations.type', 'ASC')
										->orderBy ( 'stations.station_description', 'ASC')
										->selectRaw("
											SUM(items.item_quantity) as items_count, 
											count(items.id) as lines_count, 
											stations.station_name,
											stations.station_description,
											stations.type,
											batches.station_id,
											stations.section as section_id,
											sections.section_name,
											DATE(MIN(orders.order_date)) as earliest_order_date,
											DATE(MIN(batches.change_date)) as earliest_scan_date,
											COUNT(IF(orders.order_date >= '{$date[1]} 00:00:00', items.id, NULL)) as order_1,
											COUNT(IF(orders.order_date >= '{$date[3]} 00:00:00' AND orders.order_date <= '{$date[2]} 23:59:59', items.id, NULL)) as order_2,
											COUNT(IF(orders.order_date <= '{$date[4]} 23:59:59', items.id, NULL)) as order_3,
											COUNT(IF(batches.change_date >= '{$date[1]} 00:00:00', items.id, NULL)) as scan_1,
											COUNT(IF(batches.change_date >= '{$date[3]} 00:00:00' AND batches.change_date <= '{$date[2]} 23:59:59', items.id, NULL)) as scan_2,
											COUNT(IF(batches.change_date <= '{$date[4]} 23:59:59', items.id, NULL)) as scan_3
											")
										->get();
		
		$qc = Item::join('batches', 'batches.batch_number', '=', 'items.batch_number')
										->join('orders', 'items.order_5p', '=', 'orders.id')
										->join('stations', 'batches.station_id', '=', 'stations.id')
										->searchStore($store_ids)
										->where('batches.status', 2)
										->where('batches.batch_number', 'LIKE', $batch_type)
										->where('batches.min_order_date', '<', $max_date)
										->where('items.item_status', 1)
										->where('stations.type', 'Q')
										//->where('orders.order_status', 4)
										->groupBy ( 'stations.station_name' )
										//->groupBy ( 'orders.order_status' )
										->orderBy ( 'stations.station_description', 'ASC')
										->selectRaw("
											SUM(items.item_quantity) as items_count, 
											count(items.id) as lines_count, 
											stations.station_name,
											stations.station_description,
											stations.type,
											batches.station_id,
											DATE(MIN(orders.order_date)) as earliest_order_date,
											DATE(MIN(batches.change_date)) as earliest_scan_date,
											COUNT(IF(orders.order_date >= '{$date[1]} 00:00:00', items.id, NULL)) as order_1,
											COUNT(IF(orders.order_date >= '{$date[3]} 00:00:00' AND orders.order_date <= '{$date[2]} 23:59:59', items.id, NULL)) as order_2,
											COUNT(IF(orders.order_date <= '{$date[4]} 23:59:59', items.id, NULL)) as order_3,
											COUNT(IF(batches.change_date >= '{$date[1]} 00:00:00', items.id, NULL)) as scan_1,
											COUNT(IF(batches.change_date >= '{$date[3]} 00:00:00' AND batches.change_date <= '{$date[2]} 23:59:59', items.id, NULL)) as scan_2,
											COUNT(IF(batches.change_date <= '{$date[4]} 23:59:59', items.id, NULL)) as scan_3
											")
										->get ();
										
		$backorders = Item::join('orders', 'items.order_5p', '=', 'orders.id')
										->join('batches', 'items.batch_number', '=', 'batches.batch_number')
										->join('sections', 'batches.section_id', '=', 'sections.id')
										->searchStore($store_ids)
										->where('batches.min_order_date', '<', $max_date)
										->where('batches.batch_number', 'LIKE', $batch_type)
										->where('items.item_status', 4)
										->where('items.is_deleted', '0')
										->groupBy ( 'batches.section_id' )
										->selectRaw("
											SUM(items.item_quantity) as items_count, 
											count(items.id) as lines_count, 
											batches.section_id,
											sections.section_name,
											DATE(MIN(orders.order_date)) as earliest_order_date,
											DATE(MIN(batches.change_date)) as earliest_scan_date,
											COUNT(IF(orders.order_date >= '{$date[1]} 00:00:00', items.id, NULL)) as order_1,
											COUNT(IF(orders.order_date >= '{$date[3]} 00:00:00' AND orders.order_date <= '{$date[2]} 23:59:59', items.id, NULL)) as order_2,
											COUNT(IF(orders.order_date <= '{$date[4]} 23:59:59', items.id, NULL)) as order_3,
											COUNT(IF(batches.change_date >= '{$date[1]} 00:00:00', items.id, NULL)) as scan_1,
											COUNT(IF(batches.change_date >= '{$date[3]} 00:00:00' AND batches.change_date <= '{$date[2]} 23:59:59', items.id, NULL)) as scan_2,
											COUNT(IF(batches.change_date <= '{$date[4]} 23:59:59', items.id, NULL)) as scan_3
											")
										->get (); 

		$rejects = Item::join('rejections', 'items.id', '=', 'rejections.item_id')
									 ->join('orders', 'items.order_5p', '=', 'orders.id')
									 ->join('batches', 'items.batch_number', '=', 'batches.batch_number')
									 ->join('sections', 'batches.section_id', '=', 'sections.id')
									 ->searchStore($store_ids)
									 ->where('batches.min_order_date', '<', $max_date)
									 ->where('batches.batch_number', 'LIKE', $batch_type)
									 ->where ( 'items.is_deleted', '0' )
									 ->where( 'rejections.complete', '0')
									 ->whereNotIn('rejections.graphic_status', [4,5]) // exclude CS rejects
									 ->searchStatus ( 'rejected' )
									 ->groupBy('batches.section_id', 'rejections.graphic_status')
									 ->selectRaw("
										 SUM(items.item_quantity) as items_count, 
										 count(items.id) as lines_count, 
										 rejections.graphic_status,
										 batches.section_id,
										 sections.section_name,
										 DATE(MIN(orders.order_date)) as earliest_order_date,
										 COUNT(IF(orders.order_date >= '{$date[1]} 00:00:00', items.id, NULL)) as order_1,
										 COUNT(IF(orders.order_date >= '{$date[3]} 00:00:00' AND orders.order_date <= '{$date[2]} 23:59:59', items.id, NULL)) as order_2,
										 COUNT(IF(orders.order_date <= '{$date[4]} 23:59:59', items.id, NULL)) as order_3,
										 COUNT(IF(batches.change_date >= '{$date[1]} 00:00:00', items.id, NULL)) as scan_1,
										 COUNT(IF(batches.change_date >= '{$date[3]} 00:00:00' AND batches.change_date <= '{$date[2]} 23:59:59', items.id, NULL)) as scan_2,
										 COUNT(IF(batches.change_date <= '{$date[4]} 23:59:59', items.id, NULL)) as scan_3
										 ")
									 ->get(); //dd($rejects);
															 
															 
		$WAP = Item::join('orders', 'items.order_5p', '=', 'orders.id')
										->where('items.item_status', 9)
										->where('items.is_deleted', '0')
										->where('orders.order_status', 4) // exclude address holds
										->where('items.batch_number', 'LIKE', $batch_type)
										->searchStore($store_ids)
										->where('orders.order_date', '<', $max_date)
										->selectRaw("
											SUM(items.item_quantity) as items_count, 
											count(items.id) as lines_count, 
											DATE(MIN(orders.order_date)) as earliest_order_date,
											COUNT(IF(orders.order_date >= '{$date[1]} 00:00:00', items.id, NULL)) as order_1,
											COUNT(IF(orders.order_date >= '{$date[3]} 00:00:00' AND orders.order_date <= '{$date[2]} 23:59:59', items.id, NULL)) as order_2,
											COUNT(IF(orders.order_date <= '{$date[4]} 23:59:59', items.id, NULL)) as order_3
											")
										->first (); 

		$CS_rejects = Item::join('rejections', 'items.id', '=', 'rejections.item_id')
									 ->join('orders', 'items.order_5p', '=', 'orders.id')
									 ->searchStore($store_ids)
									 ->where('orders.order_date', '<', $max_date)
									 ->where ( 'items.is_deleted', '0' )
									 ->where('items.batch_number', 'LIKE', $batch_type)
									 ->where( 'rejections.complete', '0')
									 ->whereIn('rejections.graphic_status', [4,5])
									 ->searchStatus ( 'rejected' )
									 ->groupBy('rejections.graphic_status')
									 ->selectRaw("
										 SUM(items.item_quantity) as items_count, 
										 count(items.id) as lines_count, 
										 rejections.graphic_status,
										 DATE(MIN(orders.order_date)) as earliest_order_date,
										 COUNT(IF(orders.order_date >= '{$date[1]} 00:00:00', items.id, NULL)) as order_1,
										 COUNT(IF(orders.order_date >= '{$date[3]} 00:00:00' AND orders.order_date <= '{$date[2]} 23:59:59', items.id, NULL)) as order_2,
										 COUNT(IF(orders.order_date <= '{$date[4]} 23:59:59', items.id, NULL)) as order_3")
									 ->get();
																	 
		$CS = Item::join('orders', 'items.order_5p', '=', 'orders.id')
										->searchStore($store_ids)
										->where('orders.order_date', '<', $max_date)
										->where('orders.order_status', '>', 9)
										->where('orders.is_deleted', '0')
										->where('items.batch_number', 'LIKE', $batch_type)
										->groupBy('orders.order_status')
										->selectRaw("
											orders.order_status,
											count(DISTINCT orders.id) as orders_count,
											SUM(items.item_quantity) as items_count, 
											count(items.id) as lines_count, 
											DATE(MIN(orders.order_date)) as earliest_order_date,
											COUNT(IF(orders.order_date >= '{$date[1]} 00:00:00', items.id, NULL)) as order_1,
											COUNT(IF(orders.order_date >= '{$date[3]} 00:00:00' AND orders.order_date <= '{$date[2]} 23:59:59', items.id, NULL)) as order_2,
											COUNT(IF(orders.order_date <= '{$date[4]} 23:59:59', items.id, NULL)) as order_3
											")
										->get();
										
		$sections_result = Section::get();
		$sections = array();
		$section_totals = array();
		
		$sections[0] = '0';
		$section_totals[0] = array('lines' => 0, 'qty' => 0, 'order_1' => 0, 
			'order_2' => 0, 'order_3' => 0, 'scan_1' => 0, 'scan_2' => 0, 'scan_3' => 0);
			
		foreach ($sections_result as $section) {
			$sections[$section->id] = $section->section_name;
			$section_totals[$section->section_name] = array('lines' => 0, 'qty' => 0, 'order_1' => 0, 
				'order_2' => 0, 'order_3' => 0, 'scan_1' => 0, 'scan_2' => 0, 'scan_3' => 0);
		}
		

		$unbatched = Item::join('orders', 'items.order_5p', '=', 'orders.id')
											->searchStore($store_ids)
											->where('orders.order_date', '<', $max_date)
											->whereNull('items.tracking_number')
											->where('items.batch_number', '=', '0')
											->where('items.batch_number', 'LIKE', $batch_type)
											->where('items.item_status', '=', '1')
											->whereIn('orders.order_status', [4,11,12,7,9])
											->where('orders.is_deleted', '0')
											->where('items.is_deleted', '0')
											->selectRaw("
												items.id, orders.order_date, items.item_quantity,
												SUM(items.item_quantity) as items_count, 
												count(items.id) as lines_count,
												DATE(MIN(orders.order_date)) as earliest_order_date,
												COUNT(IF(orders.order_date >= '{$date[1]} 00:00:00', items.id, NULL)) as order_1,
												COUNT(IF(orders.order_date >= '{$date[3]} 00:00:00' AND orders.order_date <= '{$date[2]} 23:59:59', items.id, NULL)) as order_2,
												COUNT(IF(orders.order_date <= '{$date[4]} 23:59:59', items.id, NULL)) as order_3
												")
											->first();
											
		$today = date("Y-m-d");
	
		$shipped_today = Item::join('batches', 'items.batch_number', '=', 'batches.batch_number')
													->join('sections', 'batches.section_id', '=', 'sections.id')
													->searchStore($store_ids)
													->where('batches.min_order_date', '<', $max_date)
													->where('batches.batch_number', 'LIKE', $batch_type)
													->searchTrackingDate($today) 
													->selectRaw('sections.section_name, AVG(DATEDIFF(\'' . $today . '\', items.created_at)) AS avgdays, count(items.id) as count')
													->groupBy('sections.section_name')
													->get();
		
		$rejected_today = Rejection::where('created_at', '>', date("Y-m-d 00:00:00"))->count();
		
		$graphic_statuses = Rejection::graphicStatus();
		
		$order_statuses = Order::statuses();
		
		$section = 'start';
		
		$now = date("F j, Y, g:i a");   
		
		$total = $items->sum('items_count') + $backorders->sum('items_count') +  $rejects->sum('items_count') + 
							$CS_rejects->sum('items_count') + $CS->sum('items_count') + $qc->sum('items_count') + 
							$unbatched->items_count + $WAP->items_count;
							
		$stores = Store::list('%', '%', 'none');

		foreach ($date as $key => $value) {
			if ($value > substr($max_date, 0, 10)) {
					$date[$key] = substr($max_date, 0, 10);
			} 
		}

		if ($store_ids == null) {
			$store_link = null;
		} else {
			$store_link = implode(',',$store_ids);
		}

        $companies = Store::$companies;

      //  dd($items);
		return view('reports.section', compact ('items', 'qc', 'backorders', 'rejects', 'WAP', 'unbatched', 'shipped_today', 'rejected_today',
																							'date', 'section', 'graphic_statuses', 'now', 'CS', 'order_statuses', 'CS_rejects', 'total',
																							'stores', 'store_ids', 'store_link', 'max_date', 'batch_type','companies'
																						));
	}


    public function sectionFilter(Request $request) {

        $request->has('store_ids') ? $store_ids = $request->get('store_ids') : $store_ids = null;
        $request->has('company') ? $company_id = $request->get('company') : $company_id = null;
        $request->has('max_date') ? $max_date = $request->get('max_date') . ' 23:59:59' : $max_date = date("Y-m-d") . ' 23:59:59';
        $request->has('batch_type') ? $batch_type = $request->get('batch_type') : $batch_type = '%';

        $dates = array();
        $date[] = date("Y-m-d");
        $date[] = date("Y-m-d", strtotime('-3 days') );
        $date[] = date("Y-m-d", strtotime('-4 days') );
        $date[] = date("Y-m-d", strtotime('-7 days') );
        $date[] = date("Y-m-d", strtotime('-8 days') );


//        $stationsList = Station::selectRaw('id, CONCAT(station_name, " - ", station_description) as station_title')
//            ->get()
//            ->pluck('station_title', 'id');

        $stationsList = [
            "Customer Service",
            "Back Orders",
            "Rejects",
            "Drop Ship",
            "House",
            "Jewelry",
            "Leather",
            "Natico",
            "Production Graphic Dept",
            "Red Laser",
            "Sublimation",
            "Quality Control",
            "Items Shipped Today"
        ];
        $temp = [];
        foreach ($stationsList as $value) {
            $temp[$value] = $value;
        }
        $stationsList = $temp;

        $filters = [];

        if(Cache::has("REPORT_FILTERS")) {
            $filters = array_merge($filters, array_keys(Cache::get("REPORT_FILTERS")));
        }
        $temp = [];
        foreach ($filters as $value) {
            $temp[$value] = $value;
        }
        $filters = $temp;

        $selectedStations = $request->get("stations", 0);

        if(is_int($selectedStations)) {
            $selectedStations = $stationsList;
        }

        if(count($filters) == 0) {
            $filters[] = "No saved filters!";
        }

        $items = Item::join('batches', 'batches.batch_number', '=', 'items.batch_number')
            ->join('orders', 'items.order_5p', '=', 'orders.id')
            ->join('stations', 'batches.station_id', '=', 'stations.id')
            ->join('sections', 'stations.section', '=', 'sections.id')
            ->searchStore($store_ids)
            ->where('batches.status', 2)
            ->where('batches.batch_number', 'LIKE', $batch_type)
            ->where('batches.min_order_date', '<', $max_date)
            ->where('items.item_status', 1)
            ->where('stations.type', '!=', 'Q')
            //->where('orders.order_status', 4)
            ->groupBy ( 'stations.station_name' )
            //->groupBy ( 'orders.order_status' )
            ->orderBy ( 'sections.section_name' )
            ->orderBy ( 'stations.type', 'ASC')
            ->orderBy ( 'stations.station_description', 'ASC')
            ->selectRaw("
											SUM(items.item_quantity) as items_count, 
											count(items.id) as lines_count, 
											stations.station_name,
											stations.station_description,
											stations.type,
											batches.station_id,
											stations.section as section_id,
											sections.section_name,
											DATE(MIN(orders.order_date)) as earliest_order_date,
											DATE(MIN(batches.change_date)) as earliest_scan_date,
											COUNT(IF(orders.order_date >= '{$date[1]} 00:00:00', items.id, NULL)) as order_1,
											COUNT(IF(orders.order_date >= '{$date[3]} 00:00:00' AND orders.order_date <= '{$date[2]} 23:59:59', items.id, NULL)) as order_2,
											COUNT(IF(orders.order_date <= '{$date[4]} 23:59:59', items.id, NULL)) as order_3,
											COUNT(IF(batches.change_date >= '{$date[1]} 00:00:00', items.id, NULL)) as scan_1,
											COUNT(IF(batches.change_date >= '{$date[3]} 00:00:00' AND batches.change_date <= '{$date[2]} 23:59:59', items.id, NULL)) as scan_2,
											COUNT(IF(batches.change_date <= '{$date[4]} 23:59:59', items.id, NULL)) as scan_3
											")
            ->get();

        $qc = Item::join('batches', 'batches.batch_number', '=', 'items.batch_number')
            ->join('orders', 'items.order_5p', '=', 'orders.id')
            ->join('stations', 'batches.station_id', '=', 'stations.id')
            ->searchStore($store_ids)
            ->where('batches.status', 2)
            ->where('batches.batch_number', 'LIKE', $batch_type)
            ->where('batches.min_order_date', '<', $max_date)
            ->where('items.item_status', 1)
            ->where('stations.type', 'Q')
            //->where('orders.order_status', 4)
            ->groupBy ( 'stations.station_name' )
            //->groupBy ( 'orders.order_status' )
            ->orderBy ( 'stations.station_description', 'ASC')
            ->selectRaw("
											SUM(items.item_quantity) as items_count, 
											count(items.id) as lines_count, 
											stations.station_name,
											stations.station_description,
											stations.type,
											batches.station_id,
											DATE(MIN(orders.order_date)) as earliest_order_date,
											DATE(MIN(batches.change_date)) as earliest_scan_date,
											COUNT(IF(orders.order_date >= '{$date[1]} 00:00:00', items.id, NULL)) as order_1,
											COUNT(IF(orders.order_date >= '{$date[3]} 00:00:00' AND orders.order_date <= '{$date[2]} 23:59:59', items.id, NULL)) as order_2,
											COUNT(IF(orders.order_date <= '{$date[4]} 23:59:59', items.id, NULL)) as order_3,
											COUNT(IF(batches.change_date >= '{$date[1]} 00:00:00', items.id, NULL)) as scan_1,
											COUNT(IF(batches.change_date >= '{$date[3]} 00:00:00' AND batches.change_date <= '{$date[2]} 23:59:59', items.id, NULL)) as scan_2,
											COUNT(IF(batches.change_date <= '{$date[4]} 23:59:59', items.id, NULL)) as scan_3
											")
            ->get ();

        $backorders = Item::join('orders', 'items.order_5p', '=', 'orders.id')
            ->join('batches', 'items.batch_number', '=', 'batches.batch_number')
            ->join('sections', 'batches.section_id', '=', 'sections.id')
            ->searchStore($store_ids)
            ->where('batches.min_order_date', '<', $max_date)
            ->where('batches.batch_number', 'LIKE', $batch_type)
            ->where('items.item_status', 4)
            ->where('items.is_deleted', '0')
            ->groupBy ( 'batches.section_id' )
            ->selectRaw("
											SUM(items.item_quantity) as items_count, 
											count(items.id) as lines_count, 
											batches.section_id,
											sections.section_name,
											DATE(MIN(orders.order_date)) as earliest_order_date,
											DATE(MIN(batches.change_date)) as earliest_scan_date,
											COUNT(IF(orders.order_date >= '{$date[1]} 00:00:00', items.id, NULL)) as order_1,
											COUNT(IF(orders.order_date >= '{$date[3]} 00:00:00' AND orders.order_date <= '{$date[2]} 23:59:59', items.id, NULL)) as order_2,
											COUNT(IF(orders.order_date <= '{$date[4]} 23:59:59', items.id, NULL)) as order_3,
											COUNT(IF(batches.change_date >= '{$date[1]} 00:00:00', items.id, NULL)) as scan_1,
											COUNT(IF(batches.change_date >= '{$date[3]} 00:00:00' AND batches.change_date <= '{$date[2]} 23:59:59', items.id, NULL)) as scan_2,
											COUNT(IF(batches.change_date <= '{$date[4]} 23:59:59', items.id, NULL)) as scan_3
											")
            ->get ();

        $rejects = Item::join('rejections', 'items.id', '=', 'rejections.item_id')
            ->join('orders', 'items.order_5p', '=', 'orders.id')
            ->join('batches', 'items.batch_number', '=', 'batches.batch_number')
            ->join('sections', 'batches.section_id', '=', 'sections.id')
            ->searchStore($store_ids)
            ->where('batches.min_order_date', '<', $max_date)
            ->where('batches.batch_number', 'LIKE', $batch_type)
            ->where ( 'items.is_deleted', '0' )
            ->where( 'rejections.complete', '0')
            ->whereNotIn('rejections.graphic_status', [4,5]) // exclude CS rejects
            ->searchStatus ( 'rejected' )
            ->groupBy('batches.section_id', 'rejections.graphic_status')
            ->selectRaw("
										 SUM(items.item_quantity) as items_count, 
										 count(items.id) as lines_count, 
										 rejections.graphic_status,
										 batches.section_id,
										 sections.section_name,
										 DATE(MIN(orders.order_date)) as earliest_order_date,
										 COUNT(IF(orders.order_date >= '{$date[1]} 00:00:00', items.id, NULL)) as order_1,
										 COUNT(IF(orders.order_date >= '{$date[3]} 00:00:00' AND orders.order_date <= '{$date[2]} 23:59:59', items.id, NULL)) as order_2,
										 COUNT(IF(orders.order_date <= '{$date[4]} 23:59:59', items.id, NULL)) as order_3,
										 COUNT(IF(batches.change_date >= '{$date[1]} 00:00:00', items.id, NULL)) as scan_1,
										 COUNT(IF(batches.change_date >= '{$date[3]} 00:00:00' AND batches.change_date <= '{$date[2]} 23:59:59', items.id, NULL)) as scan_2,
										 COUNT(IF(batches.change_date <= '{$date[4]} 23:59:59', items.id, NULL)) as scan_3
										 ")
            ->get(); //dd($rejects);


        $WAP = Item::join('orders', 'items.order_5p', '=', 'orders.id')
            ->where('items.item_status', 9)
            ->where('items.is_deleted', '0')
            ->where('orders.order_status', 4) // exclude address holds
            ->where('items.batch_number', 'LIKE', $batch_type)
            ->searchStore($store_ids)
            ->where('orders.order_date', '<', $max_date)
            ->selectRaw("
											SUM(items.item_quantity) as items_count, 
											count(items.id) as lines_count, 
											DATE(MIN(orders.order_date)) as earliest_order_date,
											COUNT(IF(orders.order_date >= '{$date[1]} 00:00:00', items.id, NULL)) as order_1,
											COUNT(IF(orders.order_date >= '{$date[3]} 00:00:00' AND orders.order_date <= '{$date[2]} 23:59:59', items.id, NULL)) as order_2,
											COUNT(IF(orders.order_date <= '{$date[4]} 23:59:59', items.id, NULL)) as order_3
											")
            ->first ();

        $CS_rejects = Item::join('rejections', 'items.id', '=', 'rejections.item_id')
            ->join('orders', 'items.order_5p', '=', 'orders.id')
            ->searchStore($store_ids)
            ->where('orders.order_date', '<', $max_date)
            ->where ( 'items.is_deleted', '0' )
            ->where('items.batch_number', 'LIKE', $batch_type)
            ->where( 'rejections.complete', '0')
            ->whereIn('rejections.graphic_status', [4,5])
            ->searchStatus ( 'rejected' )
            ->groupBy('rejections.graphic_status')
            ->selectRaw("
										 SUM(items.item_quantity) as items_count, 
										 count(items.id) as lines_count, 
										 rejections.graphic_status,
										 DATE(MIN(orders.order_date)) as earliest_order_date,
										 COUNT(IF(orders.order_date >= '{$date[1]} 00:00:00', items.id, NULL)) as order_1,
										 COUNT(IF(orders.order_date >= '{$date[3]} 00:00:00' AND orders.order_date <= '{$date[2]} 23:59:59', items.id, NULL)) as order_2,
										 COUNT(IF(orders.order_date <= '{$date[4]} 23:59:59', items.id, NULL)) as order_3")
            ->get();

        $CS = Item::join('orders', 'items.order_5p', '=', 'orders.id')
            ->searchStore($store_ids)
            ->where('orders.order_date', '<', $max_date)
            ->where('orders.order_status', '>', 9)
            ->where('orders.is_deleted', '0')
            ->where('items.batch_number', 'LIKE', $batch_type)
            ->groupBy('orders.order_status')
            ->selectRaw("
											orders.order_status,
											count(DISTINCT orders.id) as orders_count,
											SUM(items.item_quantity) as items_count, 
											count(items.id) as lines_count, 
											DATE(MIN(orders.order_date)) as earliest_order_date,
											COUNT(IF(orders.order_date >= '{$date[1]} 00:00:00', items.id, NULL)) as order_1,
											COUNT(IF(orders.order_date >= '{$date[3]} 00:00:00' AND orders.order_date <= '{$date[2]} 23:59:59', items.id, NULL)) as order_2,
											COUNT(IF(orders.order_date <= '{$date[4]} 23:59:59', items.id, NULL)) as order_3
											")
            ->get();

        $sections_result = Section::get();
        $sections = array();
        $section_totals = array();

        $sections[0] = '0';
        $section_totals[0] = array('lines' => 0, 'qty' => 0, 'order_1' => 0,
            'order_2' => 0, 'order_3' => 0, 'scan_1' => 0, 'scan_2' => 0, 'scan_3' => 0);

        foreach ($sections_result as $section) {
            $sections[$section->id] = $section->section_name;
            $section_totals[$section->section_name] = array('lines' => 0, 'qty' => 0, 'order_1' => 0,
                'order_2' => 0, 'order_3' => 0, 'scan_1' => 0, 'scan_2' => 0, 'scan_3' => 0);
        }


        $unbatched = Item::join('orders', 'items.order_5p', '=', 'orders.id')
            ->searchStore($store_ids)
            ->where('orders.order_date', '<', $max_date)
            ->whereNull('items.tracking_number')
            ->where('items.batch_number', '=', '0')
            ->where('items.batch_number', 'LIKE', $batch_type)
            ->where('items.item_status', '=', '1')
            ->whereIn('orders.order_status', [4,11,12,7,9])
            ->where('orders.is_deleted', '0')
            ->where('items.is_deleted', '0')
            ->selectRaw("
												items.id, orders.order_date, items.item_quantity,
												SUM(items.item_quantity) as items_count, 
												count(items.id) as lines_count,
												DATE(MIN(orders.order_date)) as earliest_order_date,
												COUNT(IF(orders.order_date >= '{$date[1]} 00:00:00', items.id, NULL)) as order_1,
												COUNT(IF(orders.order_date >= '{$date[3]} 00:00:00' AND orders.order_date <= '{$date[2]} 23:59:59', items.id, NULL)) as order_2,
												COUNT(IF(orders.order_date <= '{$date[4]} 23:59:59', items.id, NULL)) as order_3
												")
            ->first();

        $today = date("Y-m-d");

        $shipped_today = Item::join('batches', 'items.batch_number', '=', 'batches.batch_number')
            ->join('sections', 'batches.section_id', '=', 'sections.id')
            ->searchStore($store_ids)
            ->where('batches.min_order_date', '<', $max_date)
            ->where('batches.batch_number', 'LIKE', $batch_type)
            ->searchTrackingDate($today)
            ->selectRaw('sections.section_name, AVG(DATEDIFF(\'' . $today . '\', items.created_at)) AS avgdays, count(items.id) as count')
            ->groupBy('sections.section_name')
            ->get();

        $rejected_today = Rejection::where('created_at', '>', date("Y-m-d 00:00:00"))->count();

        $graphic_statuses = Rejection::graphicStatus();

        $order_statuses = Order::statuses();

        $section = 'start';

        $now = date("F j, Y, g:i a");

        $total = $items->sum('items_count') + $backorders->sum('items_count') +  $rejects->sum('items_count') +
            $CS_rejects->sum('items_count') + $CS->sum('items_count') + $qc->sum('items_count') +
            $unbatched->items_count + $WAP->items_count;

        $stores = Store::list('%', '%', 'none');

        foreach ($date as $key => $value) {
            if ($value > substr($max_date, 0, 10)) {
                $date[$key] = substr($max_date, 0, 10);
            }
        }

        if ($store_ids == null) {
            $store_link = null;
        } else {
            $store_link = implode(',',$store_ids);
        }

        $companies = Store::$companies;


        $selectedFilter = $request->has("selected") ? $request->get("selected") : 0;

        return view('reports.sectionFilter', compact ('items', 'qc', 'backorders', 'rejects', 'WAP', 'unbatched', 'shipped_today', 'rejected_today',
            'date', 'section', 'graphic_statuses', 'now', 'CS', 'order_statuses', 'CS_rejects', 'total',
            'stores', 'store_ids', 'store_link', 'max_date', 'batch_type','companies', 'stationsList', 'filters', "selectedStations", "selectedFilter"
        ));
    }

    /**
	 * Show Station Summary
	 * @return Ambigous <\Illuminate\View\View, \Illuminate\Contracts\View\Factory>
	 */
	public function stationSummary(Request $request) {
		
		$dates = array();
		$date[] = date("Y-m-d");
		$date[] = date("Y-m-d", strtotime('-3 days') );
		$date[] = date("Y-m-d", strtotime('-4 days') );
		$date[] = date("Y-m-d", strtotime('-7 days') );
		$date[] = date("Y-m-d", strtotime('-8 days') );
		$date[] = date("Y-m-d", strtotime('-14 days') );
		$date[] = date("Y-m-d", strtotime('-15 days') );
		$date[] = date("Y-m-d", strtotime('-21 days') );
		$date[] = date("Y-m-d", strtotime('-22 days') );
		
		if ($request->has('section_id')) {
			$section_id = $request->get('section_id');
		} else {
			$section_id = '%';
		}
		
		$items = Item::join('batches', 'items.batch_number', '=', 'batches.batch_number')
										->join('orders', 'items.order_5p', '=', 'orders.id')
										->join('stations', 'batches.station_id', '=', 'stations.id')
										->where('batches.status', 2)
										->where('items.item_status', 1)
										->where('orders.is_deleted', '0')
										->where('items.is_deleted', '0')
										->where('batches.section_id', 'LIKE', $section_id)
										->groupBy ( 'stations.station_name' )
										->orderBy ('stations.type')
										->selectRaw("
											SUM(items.item_quantity) as items_count, 
											count(items.id) as lines_count, 
											stations.id as station_id,
											stations.station_description,
											stations.station_name,
											stations.type,
											DATE(MIN(batches.min_order_date)) as earliest_order_date,
											DATE(MIN(batches.change_date)) as earliest_scan_date,
											COUNT(IF(orders.order_date >= '{$date[1]} 00:00:00', items.id, NULL)) as order_1,
											COUNT(IF(orders.order_date >= '{$date[3]} 00:00:00' AND orders.order_date <= '{$date[2]} 23:59:59', items.id, NULL)) as order_2,
											COUNT(IF(orders.order_date >= '{$date[5]} 00:00:00' AND orders.order_date <= '{$date[4]} 23:59:59', items.id, NULL)) as order_3,
											COUNT(IF(orders.order_date >= '{$date[7]} 00:00:00' AND orders.order_date <= '{$date[6]} 23:59:59', items.id, NULL)) as order_4,
											COUNT(IF(orders.order_date <= '{$date[8]} 23:59:59', items.id, NULL)) as order_5,
											COUNT(IF(batches.change_date >= '{$date[1]} 00:00:00', items.id, NULL)) as scan_1,
											COUNT(IF(batches.change_date >= '{$date[3]} 00:00:00' AND batches.change_date <= '{$date[2]} 23:59:59', items.id, NULL)) as scan_2,
											COUNT(IF(batches.change_date >= '{$date[5]} 00:00:00' AND batches.change_date <= '{$date[4]} 23:59:59', items.id, NULL)) as scan_3,
											COUNT(IF(batches.change_date >= '{$date[7]} 00:00:00' AND batches.change_date <= '{$date[6]} 23:59:59', items.id, NULL)) as scan_4,
											COUNT(IF(batches.change_date <= '{$date[8]} 23:59:59', items.id, NULL)) as scan_5
											")
										->get ();

									
		$unbatched = Item::rightJoin('orders', 'items.order_5p', '=', 'orders.id')
										->whereNull('items.tracking_number')
										->where('items.batch_number', '=', '0')
										->whereIn('orders.order_status', [4,11,12,7,9])
										->where('orders.is_deleted', '0')
										->where('items.is_deleted', '0')
										->selectRaw("
											SUM(items.item_quantity) as items_count, 
											count(items.id) as lines_count,
											DATE(MIN(orders.order_date)) as earliest_order_date,
											COUNT(IF(orders.order_date >= '{$date[1]} 00:00:00', items.id, NULL)) as order_1,
											COUNT(IF(orders.order_date >= '{$date[3]} 00:00:00' AND orders.order_date <= '{$date[2]} 23:59:59', items.id, NULL)) as order_2,
											COUNT(IF(orders.order_date >= '{$date[5]} 00:00:00' AND orders.order_date <= '{$date[4]} 23:59:59', items.id, NULL)) as order_3,
											COUNT(IF(orders.order_date >= '{$date[7]} 00:00:00' AND orders.order_date <= '{$date[6]} 23:59:59', items.id, NULL)) as order_4,
											COUNT(IF(orders.order_date <= '{$date[8]} 23:59:59', items.id, NULL)) as order_5
											")
										->get ();
		
		$unbatched = $unbatched->toArray();
		
		$unbatched['link_1'] = url ( sprintf ( "/items?start_date=%s&end_date=%s&unbatched=1&status=0", $date[1], $date[0] ) );
		$unbatched['link_2'] = url ( sprintf ( "/items?start_date=%s&end_date=%s&unbatched=1&status=0", $date[4], $date[2] ) );
		$unbatched['link_3'] = url ( sprintf ( "/items?start_date=%s&end_date=%s&unbatched=1&status=0", $date[5], $date[4] ) );
		$unbatched['link_4'] = url ( sprintf ( "/items?start_date=%s&end_date=%s&unbatched=1&status=0", $date[7], $date[6] ) );
		$unbatched['link_5'] = url ( sprintf ( "/items?start_date=%s&end_date=%s&unbatched=1&status=0", $unbatched[0]['earliest_order_date'], $date[8] ) );

		$shipped_today = Item::searchTrackingDate(date("Y-m-d"))
										->where('is_deleted', '0')
										->count();

		$stations = Station::all('id', 'station_name', 'station_description' )->toArray();
		$stations_arrays = [];
		foreach ( $stations as $stations_array ) {
				$stations_arrays[$stations_array['station_name']] = $stations_array;
		}
		
		$summaries = [];
		
		$total_lines = 0;
		$total_items = 0;
		
		$order_1_total = 0;
		$order_2_total = 0;
		$order_3_total = 0;
		$order_4_total = 0;
		$order_5_total = 0;
		
		$scan_1_total = 0;
		$scan_2_total = 0;
		$scan_3_total = 0;
		$scan_4_total = 0;
		$scan_5_total = 0;
		
		foreach ( $items as $station ) {
				$summary = [ ];
			
				$summary['station_name'] = $station['station_name'];
				$summary['id'] = $station['station_id'];
				$summary['station_description'] = $station['station_description'];
						//$stations_arrays[$station['station_name']]['station_description'];
				$summary['lines_count'] = $station['lines_count'];
				$summary['items_count'] = $station['items_count'];
				
				$summary['order_1'] = $station['order_1'];
				$summary['order_1_link'] = url ( sprintf ( "/items?search_for_first=%s&search_in_first=station_name&search_for_second=2&search_in_second=batch_status&start_date=%s&end_date=%s&status=1", $station['station_name'], $date[1], $date[0] ) );
				$order_1_total += $summary['order_1'];
				
				$summary['order_2'] = $station['order_2'];
				$summary['order_2_link'] = url ( sprintf ( "/items?search_for_first=%s&search_in_first=station_name&search_for_second=2&search_in_second=batch_status&start_date=%s&end_date=%s&status=1", $station['station_name'], $date[3], $date[2] ) );
				$order_2_total += $summary['order_2'];
				
				$summary['order_3'] = $station['order_3'];
				$summary['order_3_link'] = url ( sprintf ( "/items?search_for_first=%s&search_in_first=station_name&search_for_second=2&search_in_second=batch_status&start_date=%s&end_date=%s&status=1", $station['station_name'], $date[5], $date[4] ) );
				$order_3_total += $summary['order_3'];
				
				$summary['order_4'] = $station['order_4'];
				$summary['order_4_link'] = url ( sprintf ( "/items?search_for_first=%s&search_in_first=station_name&search_for_second=2&search_in_second=batch_status&start_date=%s&end_date=%s&status=1", $station['station_name'], $date[7], $date[6] ) );
				$order_4_total += $summary['order_4'];
				
				$summary['order_5'] = $station['order_5'];
				$summary['order_5_link'] = url ( sprintf ( "/items?search_for_first=%s&search_in_first=station_name&search_for_second=2&search_in_second=batch_status&start_date=%s&end_date=%s&status=1", $station['station_name'], $station['earliest_order_date'], $date[8] ) );
				$order_5_total += $summary['order_5'];
				
				$summary['scan_1'] = $station['scan_1'];
				$summary['scan_1_link'] = url ( sprintf ( "/items?search_for_first=%s&search_in_first=station_name&search_for_second=2&search_in_second=batch_status&scan_start_date=%s&scan_end_date=%s&status=1", $station['station_name'], $date[1], $date[0] ) );
				$scan_1_total += $summary['scan_1'];
				
				$summary['scan_2'] = $station['scan_2'];
				$summary['scan_2_link'] = url ( sprintf ( "/items?search_for_first=%s&search_in_first=station_name&search_for_second=2&search_in_second=batch_status&scan_start_date=%s&scan_end_date=%s&status=1", $station['station_name'], $date[3], $date[2] ) );
				$scan_2_total += $summary['scan_2'];
				
				$summary['scan_3'] = $station['scan_3'];
				$summary['scan_3_link'] = url ( sprintf ( "/items?search_for_first=%s&search_in_first=station_name&search_for_second=2&search_in_second=batch_status&scan_start_date=%s&scan_end_date=%s&status=1", $summary['station_name'], $date[5], $date[4] ) );
				$scan_3_total += $summary['scan_3'];
				
				$summary['scan_4'] = $station['scan_4'];
				$summary['scan_4_link'] = url ( sprintf ( "/items?search_for_first=%s&search_in_first=station_name&search_for_second=2&search_in_second=batch_status&scan_start_date=%s&scan_end_date=%s&status=1", $summary['station_name'], $date[7], $date[6] ) );
				$scan_4_total += $summary['scan_4'];
				
				$summary['scan_5'] = $station['scan_5'];
				$summary['scan_5_link'] = url ( sprintf ( "/items?search_for_first=%s&search_in_first=station_name&search_for_second=2&search_in_second=batch_status&scan_start_date=%s&scan_end_date=%s&status=1", $summary['station_name'], $station['earliest_scan_date'], $date[8] ) );
				$scan_5_total += $summary['scan_5'];
				
				$total_lines += $summary['lines_count'];
				$total_items += $summary['items_count'];
				
				//$summary['lines_link'] = url ( sprintf ( "/move_next?station=%s", $station['station_id']) );
				$summaries [] = $summary;
		}
		
		$section_option = Section::where('is_deleted', 0)
							->get()
							->pluck('section_name', 'id')
							->prepend('Select a Department', 0);
		
		
		return view ( 'reports.station_summary', compact ( 'summaries', 'total_lines', 'total_items', 'stations_arrays',
								'order_1_total', 'order_2_total', 'order_3_total', 'order_4_total', 'order_5_total',
								'scan_1_total', 'scan_2_total', 'scan_3_total', 'scan_4_total', 'scan_5_total',
								'unbatched', 'section_option', 'shipped_today'))->withRequest($request);
	}
	
	public function orderDateReport(Request $request) {
		
		set_time_limit(0);
		
		$request->has('start_date') ? $start_date = $request->get('start_date') : $start_date = date("Y-m-d");
		$request->has('end_date') ? $end_date = $request->get('end_date') : $end_date = date("Y-m-d");
		$request->has('grouping') ? $grouping = $request->get('grouping') : $grouping = 'section';
		$store_filter = $request->get('store_filter');
		
		if ($grouping == 'stock_no') {
			
			$ordered_today = Item::leftjoin('stores', 'items.store_id', '=', 'stores.store_id') 
													->leftjoin('inventory_unit', 'items.child_sku', '=', 'inventory_unit.child_sku') 
													->leftjoin('inventories', 'inventory_unit.stock_no_unique', '=', 'inventories.stock_no_unique') 
													->join('orders', 'items.order_5p', '=', 'orders.id')
													->searchStore($store_filter)
													->where('items.is_deleted', '0') 
													->where('items.item_status', '!=', 6)
													->where('orders.order_date', '>=', $start_date . ' 00:00:00')
													->where('orders.order_date', '<=', $end_date . ' 23:59:59')
													->selectRaw('stores.store_name, items.store_id, inventory_unit.stock_no_unique as col1, inventories.stock_name_discription as col2, ' .
																				'inventories.warehouse as img, sum(items.item_quantity) as sum') 
													->groupBy('inventory_unit.stock_no_unique', 'items.store_id') 
													->orderBy('sum', 'DESC') 
													->orderBy('col1') 
													->orderBy('items.store_id') 
													->get(); 
														
		} else if ($grouping == 'section2') {
			
			$ordered_today = Item::leftjoin('stores', 'items.store_id', '=', 'stores.store_id')
														->leftjoin('parameter_options', 'items.child_sku', '=', 'parameter_options.child_sku')
														->leftjoin('batch_routes', 'parameter_options.batch_route_id', '=', 'batch_routes.id')
														->leftjoin('batch_route_station', 'batch_routes.id', '=', 'batch_route_station.batch_route_id')
														->leftJoin('stations', function ($join) {
																				$join->on('stations.id', '=', DB::raw('(SELECT id FROM stations WHERE stations.id = batch_route_station.station_id LIMIT 1)'));
																			})                          
														->leftjoin('sections', 'stations.section', '=', 'sections.id')
														->join('orders', 'items.order_5p', '=', 'orders.id')
														->searchStore($store_filter)
														->where('items.is_deleted', '0')
														->where('items.item_status', '!=', 6)
														->where('orders.order_date', '>=', $start_date . ' 00:00:00')
														->where('orders.order_date', '<=', $end_date . ' 23:59:59')
														->where('stations.type', 'Q')
														->selectRaw('stores.store_name, items.store_id, sections.id, sections.section_name as col1, ' .
																					'sum(items.item_quantity) as sum')
														->groupBy('sections.id', 'items.store_id')
														->orderBy('col1')
														// ->orderBy('items.store_id')
														->get();
		
		} else if ($grouping == 'section') {
			
			$ordered_today = Item::leftjoin('stores', 'items.store_id', '=', 'stores.store_id')
														->leftjoin('batches', 'items.batch_number', '=', 'batches.batch_number')
														->leftjoin('sections', 'batches.section_id', '=', 'sections.id')
														->join('orders', 'items.order_5p', '=', 'orders.id')
														->searchStore($store_filter)
														->where('items.is_deleted', '0')
														->where('items.item_status', '!=', 6)
														->where('orders.order_date', '>=', $start_date . ' 00:00:00')
														->where('orders.order_date', '<=', $end_date . ' 23:59:59')
														->selectRaw('stores.store_name, items.store_id, sections.id, sections.section_name as col1, ' .
																					'sum(items.item_quantity) as sum')
														->groupBy('sections.id', 'items.store_id')
														->orderBy('col1')
														// ->orderBy('items.store_id')
														->get();
														
		} else if ($grouping == 'sku') {
			
		$ordered_today = Item::leftjoin('stores', 'items.store_id', '=', 'stores.store_id')
														->leftjoin('products', 'items.item_code', '=', 'products.product_model')
														->join('orders', 'items.order_5p', '=', 'orders.id')
														->searchStore($store_filter)
														->where('items.is_deleted', '0')
														->where('items.item_status', '!=', 6)
														->where('orders.order_date', '>=', $start_date . ' 00:00:00')
														->where('orders.order_date', '<=', $end_date . ' 23:59:59')
														->selectRaw('stores.store_name, items.store_id, 
																					items.item_code as col1, products.product_name as col2, products.product_thumb as img, 
																					sum(items.item_quantity) as sum')
														->groupBy('items.item_code', 'items.store_id')
														->orderBy('sum', 'DESC')
														->orderBy('col1')
														->orderBy('items.store_id')
														->get();
		}
		
		$store_ids = array_unique($ordered_today->pluck('store_id')->sortBy('store_id')->all());
		
		$grand_total = $ordered_today->sum('sum');
		
		$store_totals = array();
		foreach ($store_ids as $store_id) {
			$store_totals[$store_id] = 0;
		}
		
		$groups = array_unique($ordered_today->pluck('col1')->all());
		
		$stores = Store::whereIn('store_id', $store_ids)
										->orderBy('sort_order')
										->select('store_name', 'store_id')
										->get(); 
		
		$group_list = ['stock_no' => 'Stock Number', 'sku' => 'SKU', 'section' => 'Section', 'section2' => 'Section (Including Unbatched)'];
		
		$store_list = Store::where('is_deleted', 0)
						->where('invisible', '0')
						 ->orderBy('sort_order')
						 ->get()
						 ->pluck('store_name', 'store_id')
						 ->prepend('Filter by Store', '');
						 
		return view('reports.order_date', compact ('ordered_today', 'start_date', 'end_date', 'stores', 'store_totals', 
																									'grand_total', 'groups', 'group_list', 'grouping', 'store_filter', 'store_list'));
	}

	public function shipDateReport(Request $request) {
		
		set_time_limit(0);
		
		$request->has('start_date') ? $start_date = $request->get('start_date') : $start_date = date("Y-m-d");
		$request->has('end_date') ? $end_date = $request->get('end_date') : $end_date = date("Y-m-d");
		$store_ids = $request->get('store_ids');
		
		if (is_array($store_ids)) {
			$store_str = implode(',', $store_ids);
		} else {
			$store_str = '';
		}
		
		$shipped_today = Item::leftjoin('batches', 'items.batch_number', '=', 'batches.batch_number')
													->join('shipping', function($join) use ($start_date, $end_date) 
																 { 
																		 $join->on('items.tracking_number', '=', 'shipping.tracking_number') 
																					 ->where('shipping.transaction_datetime', '>=', $start_date . ' 00:00:00') 
																					 ->where('shipping.transaction_datetime', '<=', $end_date . ' 23:59:59'); 
																 }) 
													->searchStore($store_ids)
													->selectRaw('IF(batches.id IS NOT NULL, batches.section_id, 0) as section_num,
																items.store_id, batches.section_id, 
																SUM(items.item_quantity) as item_quantity,
																COUNT(shipping.id) as ship_count,
																SUM(DATEDIFF(shipping.transaction_datetime, items.created_at)) as diff,
																AVG(IF(shipping.id IS NOT NULL, DATEDIFF(shipping.transaction_datetime, items.created_at), NULL)) AS avgdays,
																MAX(IF(shipping.id IS NOT NULL, DATEDIFF(shipping.transaction_datetime, items.created_at), NULL)) AS maxdays')
													->groupBy('section_id')
													->groupBy('items.store_id')
													->orderBy('section_id')
													->get();
		
		// $rejected_today = Rejection::join('items', 'rejections.item_id', '=', 'items.id')
		// 															->join('batches', 'rejections.to_batch', '=', 'batches.batch_number')
		// 															->where('rejections.created_at', '>', $start_date . ' 00:00:00')
		// 															->where('rejections.created_at', '<', $end_date . ' 23:59:59')
		// 															->selectRaw('batches.section_id as section_num, batches.section_id,
		// 																						items.store_id, COUNT(DISTINCT rejections.id) as count')
		// 															->groupBy('batches.section_id')
		// 															->groupBy('items.store_id')
		// 															->get(); 
													
		$stores = Store::list('%', '%', 'none');

		$sections = Section::select('id', 'section_name')
								->get()
								->pluck('section_name', 'id')
								->prepend('Unbatched', '0');

		return view('reports.ship_date', compact ('shipped_today',  'start_date', 'end_date', 'stores', 'sections', 
																							'store_ids', 'store_str'));
	}
			
	public function section_history () 
	{
		$directory = storage_path() . '/reports/';
		$contents = array_diff(scandir($directory), array('..', '.')); 
	
		return view('reports.history', compact('directory', 'contents'));

	}
	
	public function screenshot (Request $request) {
		
		if ($request->ip() == '96.57.0.130' || $request->ip() == '127.0.0.1') {
		
			return $this->section(new Request)->render();
		
		} else {
			
			return view('errors.404');
		}
	}
	
	public function viewPdf (Request $request) {
	
		header("Content-type: application/pdf");
		header("Content-Disposition: inline; filename=" . $request->get('filename'));
		@readfile(storage_path() . '/reports/' . $request->get('filename'));
		
	}
	
	public function mustShipReport (Request $request)
	{
		$store_id = $request->get('store_id');
		
		$orders = Order::with('items.batch', 'items.batch.station', 'store', 'customer')
											->where('is_deleted', '0')
											->whereNotIn('order_status', [6,8,10])
											->whereNotNull('ship_date')
											->orderBy('ship_date', 'ASC')
											->storeId($store_id)
											->get();
											
		$dates = array_unique($orders->pluck('ship_date')->toArray());
		
		$statuses = Order::statuses(0);
		
		$stores = Store::list();
		
		return view('reports.must_ship', compact('orders', 'dates', 'statuses', 'stores', 'store_id'));
	}
	
	public function rejectsDetail (Request $request) {
		
		if ($request->has('item_id')) {
			
			$item_id = $this->formatItem( $request->get('item_id') );
			
			$items = Rejection::with('rejection_reason_info', 'user', 'item.batch.scans', 'from_station')
													->where('item_id', $item_id)
													->orderBy('created_at', 'DESC')
													->get();
													
		} else if ($request->all() != []) {
			
			$rejects = Item::join('rejections', 'items.id', '=', 'rejections.item_id')
										->where('items.is_deleted', '0')
										->where('items.item_status', '!=', 6)
										->search($request->get('item_code'), 'item_code')
										->searchStore($request->get('store_ids'))
										->searchSection($request->get('section'))
										->searchDate($request->get('start_date'), $request->get('end_date'))
										->searchShipDate($request->get('start_ship_date'), $request->get('end_ship_date'))
										->selectRaw('DISTINCT rejections.item_id')
										->get()
										->pluck('item_id');

			$items = Rejection::with('rejection_reason_info', 'user', 'item', 'from_station', 'from_batch_info.scans')
														->whereNotIn('graphic_status', [4,5])
														->whereIn('item_id', $rejects)
														->orderBy('created_at', 'DESC')
														->get();
														
		} else {
				$items = [];
		}
		
		$stores = Store::list('%', '%', 'none');
		
		$sections = Section::select('id', 'section_name')
								->get()
								->pluck('section_name', 'id');
								
		return view('reports.rejects_detail', compact('items', 'request', 'stores', 'sections'));
	}
	
	public function couponReport (Request $request) {
		
		$request->has('start_date') ? $start_date = $request->get('start_date') : $start_date = date("Y-m-d");
		$request->has('end_date') ? $end_date = $request->get('end_date') : $end_date = date("Y-m-d");
		$request->has('store_ids') ? $store_ids = $request->get('store_ids') : $store_ids = null;
		
		set_time_limit(0);
		ini_set('memory_limit','256M');
		
		$coupons = Order::where('orders.is_deleted', '0')
									->selectRaw(" 
										(CASE WHEN (SUBSTRING(TRIM(LOWER(promotion_id)), 1, 1) = 'g')
														THEN SUBSTRING(TRIM(LOWER(promotion_id)), 1, 5)
											WHEN (SUBSTRING(TRIM(LOWER(promotion_id)), 1, 2) = 'es') 
														THEN SUBSTRING(TRIM(LOWER(promotion_id)), 1, 5)
											WHEN (SUBSTRING(TRIM(LOWER(coupon_id)), 1, 1) = 'g')
														THEN SUBSTRING(TRIM(LOWER(coupon_id)), 1, 5)
											WHEN (SUBSTRING(TRIM(LOWER(coupon_id)), 1, 2) = 'es')
														THEN SUBSTRING(TRIM(LOWER(coupon_id)), 1, 5)
											WHEN (promotion_id IS NOT NULL and promotion_id != '') THEN TRIM(LOWER(promotion_id))
											WHEN (coupon_id IS NOT NULL and coupon_id != '') THEN TRIM(LOWER(coupon_id))
											ELSE 'No Coupon'
											END) AS coupon,
											SUM(orders.total) as order_total, SUM(shipping_charge) as shipping_total, 
											COUNT(orders.id) as order_count")
									->StoreId($request->get('store_ids'))
									->withinDate($start_date, $end_date)
									->orderBy('order_total', 'DESC')
									->groupBy('coupon')
									->get(); 
		
		$coupon_items = Order::join('items', 'orders.id', '=', 'items.order_5p')
									->leftjoin('shipping', 'items.tracking_number', '=', 'shipping.tracking_number')
									->where('orders.is_deleted', '0')
									->where('items.is_deleted', '0')
									->selectRaw(" 
										(CASE WHEN (SUBSTRING(TRIM(LOWER(promotion_id)), 1, 1) = 'g')
														THEN SUBSTRING(TRIM(LOWER(promotion_id)), 1, 5)
											WHEN (SUBSTRING(TRIM(LOWER(promotion_id)), 1, 2) = 'es') 
														THEN SUBSTRING(TRIM(LOWER(promotion_id)), 1, 5)
											WHEN (SUBSTRING(TRIM(LOWER(coupon_id)), 1, 1) = 'g')
														THEN SUBSTRING(TRIM(LOWER(coupon_id)), 1, 5)
											WHEN (SUBSTRING(TRIM(LOWER(coupon_id)), 1, 2) = 'es')
														THEN SUBSTRING(TRIM(LOWER(coupon_id)), 1, 5)
											WHEN (promotion_id IS NOT NULL and promotion_id != '') THEN TRIM(LOWER(promotion_id))
											WHEN (coupon_id IS NOT NULL and coupon_id != '') THEN TRIM(LOWER(coupon_id))
											ELSE 'No Coupon'
											END) AS coupon,
											SUM(IF(items.item_status = 2, items.item_quantity, 0)) as shipped,
											SUM(IF(items.item_status = 2 && shipping.id IS NOT NULL, 
															DATEDIFF(shipping.transaction_datetime, orders.order_date), null)) as ship_days,
											SUM(items.item_quantity) as item_qty")
									->StoreId($request->get('store_ids'))
									->withinDate($start_date, $end_date)
									->groupBy('coupon')
									->get();
									
		$stores = Store::list('%', '%', 'none');
		
		return view('reports.coupon', compact('coupons', 'coupon_items', 'start_date', 'end_date', 'store_ids', 'stores'));
		
	}
	
	public function wapSummary (Request $request) {
		
				$dates = array();
				$date[] = date("Y-m-d");
				$date[] = date("Y-m-d", strtotime('-3 days') );
				$date[] = date("Y-m-d", strtotime('-4 days') );
				$date[] = date("Y-m-d", strtotime('-7 days') );
				$date[] = date("Y-m-d", strtotime('-8 days') );
				
				$request->has('store_ids') ? $store_ids = $request->get('store_ids') : $store_ids = null;
				
				$WAP = Order::join('items', 'items.order_5p', '=', 'orders.id')
												->join('wap_items', 'items.id', '=', 'wap_items.item_id')
												->where('items.is_deleted', '0')
												->where('items.item_status', 9)
												->StoreId($store_ids)
												->selectRaw("
													orders.order_status,
													SUM(items.item_quantity) as wap_items, 
													count(items.id) as lines_count, 
													count(DISTINCT orders.id) as order_count,
													DATE(MIN(orders.order_date)) as earliest_order_date,
													COUNT(IF(orders.order_date >= '{$date[1]} 00:00:00', items.id, NULL)) as order_1,
													COUNT(IF(orders.order_date >= '{$date[3]} 00:00:00' AND orders.order_date <= '{$date[2]} 23:59:59', items.id, NULL)) as order_2,
													COUNT(IF(orders.order_date <= '{$date[4]} 23:59:59', items.id, NULL)) as order_3,
													COUNT(IF(wap_items.created_at >= '{$date[1]} 00:00:00', items.id, NULL)) as added_1,
													COUNT(IF(wap_items.created_at >= '{$date[3]} 00:00:00' AND wap_items.created_at <= '{$date[2]} 23:59:59', items.id, NULL)) as added_2,
													COUNT(IF(wap_items.created_at <= '{$date[4]} 23:59:59', items.id, NULL)) as added_3
													")
												->groupBy('order_status')
												->get(); dd($WAP);
				
				// $bins = Wap::with('items', 'order.shippable_items')
				// 						->join('orders', 'wap.order_id', '=', 'orders.id')
				// 						->whereNotNull('wap.order_id')
				// 						->selectRaw("wap.id, wap.order_id, orders.id, orders.order_date, orders.order_status, wap.name,
				// 								(CASE 
				// 									WHEN (orders.order_date >= '{$date[1]} 00:00:00') 
				// 										THEN 'order_1'
				// 									WHEN (orders.order_date >= '{$date[3]} 00:00:00' AND orders.order_date <= '{$date[2]} 23:59:59') 
				// 										THEN 'order_2'
				// 									WHEN (orders.order_date <= '{$date[4]} 23:59:59') 
				// 										THEN 'order_3'
				// 									END) as date_group")
				// 						->get();dd($bins[1]);
				
				$summary = array();
				$incomplete = array();
				$total_bins = ['order_1' => 0, 'order_2' => '0', 'order_3' => '0'];
				$total_items = ['order_1' => 0, 'order_2' => '0', 'order_3' => '0'];
				
				$statuses = Order::statuses();
				
				foreach ($bins as $bin) {
					
					echo $bin->date_group . ' - ' . $bin->items . "\n";

					$total_items[$bin->date_group] += count($bin->items);
					$total_bins[$bin->date_group] += 1;
					
					if ($bin->order->order_status != 4) {
						$summary[$statuses[$bin->order->order_status]][$bin->date_group][$bin->name] = count($bin->items);
					} else if (count($bin->order->shippable_items) == count($bin->items)) {
						$summary['READY'][$bin->date_group][$bin->name] = count($bin->items);
					} else if (count($bin->order->shippable_items) > count($bin->items)) {
						$summary['ZZ_INCOMPLETE'][$bin->date_group][$bin->name] = count($bin->items);
						$incomplete[] = $bin;
					} else {
						$summary['ERROR'][$bin->date_group][$bin->name] = count($bin->items);
					}
				}
				
				asort($summary);
				// $wap_items = WapItem::count();
				
				$stores = Store::list('%', '%', 'none');
				
				return view('reports.wap', compact('summary', 'totals', 'store_ids', 'stores'));
				
	}
	
	public function salesReport (Request $request) {
		
		$request->has('start_date') ? $start_date = $request->get('start_date') : $start_date = date("Y-m-d");
		$request->has('end_date') ? $end_date = $request->get('end_date') : $end_date = date("Y-m-d");
		$request->has('store_ids') ? $store_ids = $request->get('store_ids') : $store_ids = null;
		
		set_time_limit(0);
		ini_set('memory_limit','256M');
		
		$sales = Order::where('orders.is_deleted', '0')
									->where('orders.order_status', '!=', 8)
									->selectRaw("store_id, 
															SUM(orders.total) as order_total, 
															SUM(shipping_charge) as shipping_total, 
															COUNT(orders.id) as order_count")
									->StoreId($request->get('store_ids'))
									->withinDate($start_date, $end_date)
									->groupBy('store_id')
									->orderBy('order_total', 'DESC')
									->get();
		
		$total_amount = $sales->sum('order_total');
		
		$sale_items = Order::join('items', 'orders.id', '=', 'items.order_5p')
									->leftjoin('shipping', 'items.tracking_number', '=', 'shipping.tracking_number')
									->leftjoin('batches', 'items.batch_number', '=', 'batches.batch_number') 
									->leftjoin('sections', 'batches.section_id', '=', 'sections.id') 
									->where('orders.is_deleted', '0')
									->where('items.is_deleted', '0')
									->where('orders.order_status', '!=', 8)
									->where('items.item_status', '!=', 6)
									->selectRaw("orders.store_id, batches.section_id,
															(CASE WHEN (items.batch_number = '0') THEN 'Unbatched'
																		WHEN (batches.section_id IS NULL or sections.id IS NULL) THEN 'Invalid Section'
																		ELSE sections.section_name
																END) as header,
															SUM(IF(items.item_status = 2, items.item_quantity, 0)) as shipped,
															SUM(IF(items.item_status = 2 && shipping.id IS NOT NULL, 
																			DATEDIFF(shipping.transaction_datetime, orders.order_date), null)) as ship_days,
															SUM(items.item_quantity) as item_qty")
									->StoreId($request->get('store_ids'))
									->withinDate($start_date, $end_date)
									->groupBy('orders.store_id')
									->groupBy('batches.section_id')
									->orderBy('item_qty', 'DESC')
									->get();
									
		$stores = Store::list('%', '%', 'none');
		
		$sections = Section::select('id', 'section_name')
								->get()
								->pluck('section_name', 'id');
								
		return view('reports.sales', compact('sales', 'sale_items', 'start_date', 'end_date', 'store_ids',
		 																			'stores', 'sections', 'total_amount'));
		
	}
	
	public function itemsReport (Request $request) {
		
		$request->has('start_date') ? $start_date = $request->get('start_date') : $start_date = date("Y-m-d");
		$request->has('end_date') ? $end_date = $request->get('end_date') : $end_date = date("Y-m-d");
		$request->has('group') ? $group = $request->get('group') : $group = 'stock_no_unique';
		$request->has('limit') && $request->get('limit') != 0 ? $limit = $request->get('limit') : $limit = 25;
		
		$store_ids = $request->get('store_ids');
		
		if (is_array($store_ids)) {
			$store_str = implode(',', $store_ids);
		} else {
			$store_str = '';
		}
		
		$section = $request->get('section');
		
		set_time_limit(0);
		ini_set('memory_limit','256M');

		$items = Item::join('orders', 'items.order_5p', '=', 'orders.id')
									->leftjoin('shipping', 'items.tracking_number', '=', 'shipping.tracking_number')
									->leftjoin('products', 'items.item_code', '=', 'products.product_model') 
									->where('items.is_deleted', '0')
									->where('items.item_status', '!=', 6)
									->selectRaw("items.item_code, products.product_name, products.product_thumb, products.id as product_id,
															SUM(IF(items.item_status = 2, items.item_quantity, 0)) as shipped,
															SUM(IF(items.item_status = 2 && shipping.id IS NOT NULL, 
																			DATEDIFF(shipping.transaction_datetime, orders.order_date), null)) as ship_days,
															MAX(IF(items.item_status = 2 && shipping.id IS NOT NULL, 
																			DATEDIFF(shipping.transaction_datetime, orders.order_date), null)) as maxdays,
															SUM(items.item_quantity) as item_qty
															")
									->searchStore($store_ids)
									->searchDate($start_date, $end_date)
									->groupBy('items.item_code')
									->orderBy('item_qty', 'DESC')
									->limit($limit)
									->get();
		
		$skus = $items->pluck('item_code');
		
		$rejects = Item::leftjoin('rejections', 'items.id', '=', 'rejections.item_id')
									->where('items.is_deleted', '0')
									->where('items.item_status', '!=', 6)
									->whereIn('items.item_code', $skus)
									->selectRaw("items.item_code,COUNT(DISTINCT rejections.id) as count")
									->searchStore($store_ids)
									->searchDate($start_date, $end_date)
									->groupBy('items.item_code')
									->get();
									
		$stores = Store::list('%', '%', 'none');
		
		$sections = Section::select('id', 'section_name')
								->get()
								->pluck('section_name', 'id');
								
		return view('reports.items', compact('items', 'rejects', 'start_date', 'end_date', 'store_ids', 'stores', 'store_str', 
																					'sections', 'section', 'group', 'limit'));
		
	}
	
	private function formatItem($input) {
		
		$input = trim($input);
		
		if (substr($input, 0, 4) == 'ITEM') {
				return substr($input, 4);
		} else {
				return $input;
		}
		
	}
}