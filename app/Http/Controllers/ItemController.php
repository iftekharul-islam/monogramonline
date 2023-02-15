<?php namespace App\Http\Controllers;

use App\Customer;
use App\Http\Requests;
use App\Item;
use App\Option;
use App\Order;
use App\Batch;
use App\Section;
use App\Store;
use App\Wap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use League\Csv\Writer;
use Monogram\Helper;
use Monogram\CSV;
use Monogram\Batching;

class ItemController extends Controller
{

    public function indexGraphic (Request $request)
    {
        
        if (count($request->all()) > 0) {
            //mirror in csv export
            $items = Item::with('order.customer', 'store', 'batch.route', 'shipInfo', 'batch', 'wap_item.bin')
                ->where('is_deleted', '0')
                ->searchStore($request->get('store'))
                ->searchStatus($request->get('status'))
                ->searchSection($request->get('section'))
                ->search($request->get('search_for_first'), $request->get('search_in_first'))
                ->search($request->get('search_for_second'), $request->get('search_in_second'))
                ->searchTrackingDate($request->get('tracking_date'))
                ->searchOrderDate($request->get('start_date'), $request->get('end_date'))
                ->searchBatchDate($request->get('scan_start_date'), $request->get('scan_end_date'))
                ->unBatched($request->get('unbatched'))
                ->latest()
                ->paginate(50);

            $item_sum = Item::where('is_deleted', '0')
                ->searchStore($request->get('store'))
                ->searchStatus($request->get('status'))
                ->searchSection($request->get('section'))
                ->search($request->get('search_for_first'), $request->get('search_in_first'))
                ->search($request->get('search_for_second'), $request->get('search_in_second'))
                ->searchTrackingDate($request->get('tracking_date'))
                ->searchOrderDate($request->get('start_date'), $request->get('end_date'))
                ->searchBatchDate($request->get('scan_start_date'), $request->get('scan_end_date'))
                ->unBatched($request->get('unbatched'))
                ->selectRaw('sum(items.item_quantity) as sum, count(items.id) as count')
                ->first();

            set_time_limit(0);
        }

        $unassignedProductCount = Option::where('batch_route_id', Helper::getDefaultRouteId())->count();

        $unassignedOrderCount = Item::join('parameter_options', 'items.child_sku', '=', 'parameter_options.child_sku')
            ->where('parameter_options.batch_route_id', Helper::getDefaultRouteId())
            ->where('items.is_deleted', '0')
            ->whereIn('items.item_status', [1,4])
            ->where('items.batch_number', '=', '0')
            ->count();

        $emptyStationsCount = count(Helper::getEmptyStation());

        if ( $emptyStationsCount == 0 ) {
            $emptyStationsCount = "";
        } else {
            $emptyStationsCount = $emptyStationsCount . " route have no stations assigned.";
        }

        // $unassigned = 0; $unassignedProductCount=0; $unassignedOrderCount = 0; $emptyStationsCount = 0;

        $search_in = [
            'all'                 => 'All',
            'order'               => 'Order',
            'company'            	=> 'Company',
            'item_id'             => 'Item#',
            'customer'            => 'Customer',
            'coupon_id'           => 'Coupon',
            'bill_email'          => 'Customer Bill Email',
            'state'               => 'State',
            'description'         => 'Description',
            'item_option'         => 'Option',
            'item_code'           => 'SKU',
            'child_sku'           => 'Child SKU',
            'batch'               => 'Batch',
            'batch_creation_date' => 'Batch Creation date',
            'batch_status' 				=> 'Batch Status',
            'tracking_number'     => 'Tracking number',
            'station_name'        => 'Station',
            'stock_number' 				=> 'Stock Number'
        ];

        $stores = Store::list('%', '%', 'none');

        $statuses = Item::getStatusList();

        $order_statuses = Order::statuses();

        if (is_array($request->get('store'))) {
            $store = $request->get('store');
        } else if (strpos($request->get('store'), ',')) {
            $store = explode(',', $request->get('store'));
        } else {
            $store = $request->get('store');
        }

        #return $items;
        return view('items.index_graphic', compact('items', 'orders', 'emptyStationsCount', 'search_in', 'request', 'unassigned', 'item_sum',
            'unassignedProductCount', 'unassignedOrderCount', 'statuses', 'order_statuses',
            'stores', 'store'));
    }

	public function index (Request $request)
	{
		if (count($request->all()) > 0) {
			//mirror in csv export
			$items = Item::with('order.customer', 'store', 'batch.route', 'shipInfo', 'batch', 'wap_item.bin')
						 ->where('is_deleted', '0')
						 ->searchStore($request->get('store'))
						 ->searchStatus($request->get('status'))
						 ->searchSection($request->get('section'))
						 ->search($request->get('search_for_first'), $request->get('search_in_first'))
						 ->search($request->get('search_for_second'), $request->get('search_in_second'))
						 ->searchTrackingDate($request->get('tracking_date'))
						 ->searchOrderDate($request->get('start_date'), $request->get('end_date'))
						 ->searchBatchDate($request->get('scan_start_date'), $request->get('scan_end_date'))
						 ->unBatched($request->get('unbatched'))
						 ->latest()
						 ->paginate(50); 
			
			$item_sum = Item::where('is_deleted', '0')
						 ->searchStore($request->get('store'))
						 ->searchStatus($request->get('status'))
						 ->searchSection($request->get('section'))
						 ->search($request->get('search_for_first'), $request->get('search_in_first'))
						 ->search($request->get('search_for_second'), $request->get('search_in_second'))
						 ->searchTrackingDate($request->get('tracking_date'))
						 ->searchOrderDate($request->get('start_date'), $request->get('end_date'))
						 ->searchBatchDate($request->get('scan_start_date'), $request->get('scan_end_date'))
						 ->unBatched($request->get('unbatched'))
						 ->selectRaw('sum(items.item_quantity) as sum, count(items.id) as count')
						 ->first();
									 
			set_time_limit(0);
		}
		
		$unassignedProductCount = Option::where('batch_route_id', Helper::getDefaultRouteId())->count();

		$unassignedOrderCount = Item::join('parameter_options', 'items.child_sku', '=', 'parameter_options.child_sku')
										->where('parameter_options.batch_route_id', Helper::getDefaultRouteId())
										->where('items.is_deleted', '0')
										->whereIn('items.item_status', [1,4])
										->where('items.batch_number', '=', '0')
										->count(); 

		$emptyStationsCount = count(Helper::getEmptyStation());

		if ( $emptyStationsCount == 0 ) {
				$emptyStationsCount = "";
		} else {
				$emptyStationsCount = $emptyStationsCount . " route have no stations assigned.";
		}

			// $unassigned = 0; $unassignedProductCount=0; $unassignedOrderCount = 0; $emptyStationsCount = 0;
		
		$search_in = [
				'all'                 => 'All',
				'order'               => 'Order',
				'company'            	=> 'Company',
				'item_id'             => 'Item#',
				'customer'            => 'Customer',
				'coupon_id'           => 'Coupon',
				'bill_email'          => 'Customer Bill Email',
				'state'               => 'State',
				'description'         => 'Description',
				'item_option'         => 'Option',
				'item_code'           => 'SKU',
				'child_sku'           => 'Child SKU',
				'batch'               => 'Batch',
				'batch_creation_date' => 'Batch Creation date',
				'batch_status' 				=> 'Batch Status',
				'tracking_number'     => 'Tracking number',
				'station_name'        => 'Station',
				'stock_number' 				=> 'Stock Number'
		];
		
		$stores = Store::list('%', '%', 'none');
								
		$statuses = Item::getStatusList();
		
		$order_statuses = Order::statuses();
		
		if (is_array($request->get('store'))) {
			$store = $request->get('store');
		} else if (strpos($request->get('store'), ',')) {
			$store = explode(',', $request->get('store'));
		} else {
			$store = $request->get('store');
		}
		
		#return $items;
		return view('items.index', compact('items', 'orders', 'emptyStationsCount', 'search_in', 'request', 'unassigned', 'item_sum',
																				'unassignedProductCount', 'unassignedOrderCount', 'statuses', 'order_statuses', 
																				'stores', 'store'));
	}

	public function csvExport (Request $request) 
	{
							
		if (count($request->all()) > 0) {
			
			$header = [
									'order_5p',
									'order_id',
									'item_code',
									'item_description',
									'item_quantity',
									'item_unit_price',
									'item_taxable',
									'short_order',
									'item_count',
									'shipping_charge',
									'total',
									'order_ip',
									'order_date',
									'bill_email',
									'bill_full_name',
									'item_url',
									'coupon_id',
									'coupon_value',
									'promotion_id',
									'promotion_value'
								];
			
			//ini_set('memory_limit','16M'); 
			set_time_limit(0);
			
			$offset = 0;
			$filename = sprintf("items_%s.csv", date("Y_m_d_His", strtotime('now')));
			
			while ($offset < $request->get('count')) {
				
					$items = Item::join('orders', 'items.order_5p', '=', 'orders.id')
								 ->join('customers', 'orders.customer_id', '=', 'customers.id')
								 ->where('items.is_deleted', '0')
								 ->searchStore(unserialize($request->get('store')))
								 ->searchStatus(unserialize($request->get('status')))
								 ->search($request->get('search_for_first'), $request->get('search_in_first'))
								 ->search($request->get('search_for_second'), $request->get('search_in_second'))
								 ->searchTrackingDate($request->get('tracking_date'))
								 ->searchOrderDate($request->get('start_date'), $request->get('end_date'))
								 ->searchBatchDate($request->get('scan_start_date'), $request->get('scan_end_date'))
								 ->unBatched($request->get('unbatched'))
								 ->latest('items.created_at')
								 ->limit(5000)
								 ->offset($offset)
								 ->get([
									 	'order_5p',
										'items.order_id',
										'item_code',
										'item_description',
										'item_quantity',
										'item_unit_price',
										'item_taxable',
										'short_order',
										'item_count',
										'shipping_charge',
										'total',
										'order_ip',
										'order_date',
										'bill_email',
										'bill_full_name',
										'item_url',
										'orders.coupon_id',
										'orders.coupon_value',
										'orders.promotion_id',
										'orders.promotion_value'
									])->toArray();
					
					$csv = new CSV;
					$pathToFile = $csv->createFile($items, 'assets/exports/', $header, $filename);
					
					$offset += 5000;
			} 
			
			return response()->download($pathToFile)->deleteFileAfterSend(true);
		} 
	}
	
	public function unBatchableItems (Request $request)
	{
		
		$items = Batching::failures();
		
		$order_statuses = Order::statuses();
		
		return view('items.unbatchable', compact('items', 'order_statuses'));

	}
	
	public function autoBatch ($max_units, $store_id = null) 
	{	
		Log::info('AutoBatch Intiated - Max Units ' . $max_units);
		
		Batching::auto($max_units, $store_id);
		
	}
	
	public function getBatch (Request $request)
	{
		$locked = Batching::islocked();
		
		$backorder = $request->get('backorder');
		$store = $request->get('store');
		$section = $request->get('section');
		
		$search_in = [
			''          => 'All',
			'order_id'  => 'Order',
			'id'        => 'Item#',
			'item_code' => 'SKU',
			'child_sku' => 'Child SKU',
			'customer'  => 'Customer',
		];
		
		$stores = Store::list('1', '%');
		
		$sections = Section::where('is_deleted', '0')
								->get()
								->pluck('section_name', 'id')
								->prepend('', '');
		
 		$count = 1;
		$serial = 1;

		$emptyStationsCount = count(Helper::getEmptyStation());
		if ( $emptyStationsCount > 0 ) {
			return redirect(url('/prod_config/batch_routes'))->withErrors(new MessageBag([
				'error' => 'In Routes some Route Station empty<br>Please assign correct Station in route.',
			]));
		}

		if ( ! $request->start_date ) {
			$start_date = "2016-06-01";
		} else {
			$start_date = $request->start_date;
		}

		if ( ! $request->end_date ) {
			$end_date = date("Y-m-d");
		} else {
			$end_date = $request->end_date;
		}

		$search_for_first = $request->search_for_first;
		$search_in_first = $request->search_in_first;
		
		// Item::backOrderItems();
		
		$batch_routes = Batching::createAbleBatches($backorder, true, $start_date, $end_date, $search_for_first, $search_in_first, $store, $section);
		
		return view('items.create_batch', compact('batch_routes', 'count', 'serial', 'request', 'search_in', 'stores', 
																							'sections', 'backorder', 'locked'));
	}

	public function postBatch (Requests\ItemToBatchCreateRequest $request)
	{
		//		return $request->all();
		
		if ($request->get('backorder')) {
			$prefix = 'B01-';
			$status = 'back order';
		} else {
			$prefix = '';
			$status = 'active';
		}
		
		$batches = $request->get('batches');
		
		if (Batching::createBatch($batches, $prefix, $status)) {		
			return redirect(url('preview_batch'));
		} else {
			return redirect(url('preview_batch'))->withErrors('Batching already in progress... try again later');
		}
	}

	public function export_orders (Request $request)
	{
		$batch_numbers = $request->get('batch_number');
		
		if ($batch_numbers == null) {
			return redirect()->back()->withErrors([
					'error' => 'Please Select Batch#',
			]);
		}
		
		$items= Item::with('order')
						->whereIn('batch_number', $batch_numbers)
						->where('is_deleted', '0')
						->WhereNull('tracking_number')
						->get()
						->pluck('order_id')
						->toArray();
		
		$items = array_unique($items);
		
		$file_path = sprintf("%s/assets/exports/batches/", public_path());
		$fully_specified_path = sprintf("%s%s", $file_path, "order_id_".date("Y-M-d H:i:s").".csv");
		$csv = Writer::createFromFileObject(new \SplFileObject($fully_specified_path, 'w+'), 'w');
		
		foreach ( $items as $item ) {
			$csv->insertOne($item->short_order);
		}
		// Download CSV file		
		return response()->download($fully_specified_path);
	}

	public function getOrderStatus (Request $request)
	{

		$orderNumber = trim($request->get('order'));
		$email = trim($request->get('email'));
		$orderinfo = [];

		if ( ( ! empty ($orderNumber) ) ) {
			// Start coder for Valide Input
			$rules = [
				'order' => 'required',
				'email' => 'required|email',
			];

			$inputs = [
				'order' => $request->get('order'),
				'email' => $request->get('email'),
			];

			$validator = Validator::make($inputs, $rules);

			if ( $validator->fails() ) {
				return view('items.trk_order_status')->with('request', $request)->withErrors($validator);
			}
			// End coder for Valide Input

			// ----------------
			// 			$orders = Order::with ('items', 'shipping', 'customer' )
			$orders = Order::with('items.shipInfo', 'customer')// 						->where('short_order','like', $orderNumber)
						   ->where('short_order', 'LIKE', '%' . $orderNumber . '%')// 						->where('bill_email','=', $email)
						   ->where('is_deleted', '0')
							 ->latest()
							 ->get();

			if ( $orders->count() == 0 ) {
				return view('items.trk_order_status')
					->with('request', $request)
					->withErrors('Incorrect Order# ' . $orderNumber . ' or Email: ' . $email . '<br>Please verify your Order# or email');
			}
			
			$order_info = array();
			
			foreach ( $orders as $key => $order ) {

				if ( strtolower($order->customer->bill_email ) == strtolower($email) ) {
				
						//---- Insert for display front end.
						$orderinfo['short_order'] = $order->short_order;
						$orderinfo['ship_full_name'] = $order->customer->ship_full_name;
						$orderinfo['ship_city_state'] = $order->customer->ship_city . ', ' . $order->customer->ship_state;
						$orderinfo['items_subtotal'] = $order->item_count . ' /' . $order->total;
						$orderinfo['order_date'] = $order->order_date;
						// $orderinfo['shipping'] = $order->customer->shipping;
						$statuses = Order::statuses();
						$orderinfo['status'] = $statuses[$order->order_status];
						
						$item_status = array();
						
						foreach ($order->items as $item) {
							
							if ($item->item_status == 'production' || $item->item_status == 'rejected' || $item->item_status == 'wap') {
								
								$item_status[$item->id] = [$item->item_description, 'In Production'];
								
							} elseif ($item->item_status == 'shipped') {
								
								if ($item->tracking_number[0] == '8') {
									$track = $item->shipinfo->shipping_id;
								} else {
									$track = $item->tracking_number;
								}
								
								$item_status[$item->id] = [$item->item_description, 'Shipped ' . $item->shipinfo->mail_class . ' - ' . $track];
								
							} elseif ($item->item_status == 'back order') { 
								
								$item_status[$item->id] = [$item->item_description, 'In Back Order'];
								
							} else {
								
								$item_status[$item->id] = [$item->item_description, $item->item_status];
							}
						}
						
						$orderinfo['items'] = $item_status;
						
						// if ( empty($order->items->first()->shipInfo) ) {
						// 	if ($order->items->first()->tracking_number != NULL) {
						// 		$orderinfo['tracking'] = $order->items->first()->tracking_number;
						// 		$station = "Shipped";
						// 	} else {
						// 		$orderinfo['tracking'] = NULL;
						// 		$station = Station::where('is_deleted', 0)
						// 						  ->where('station_name', $order->items->first()->station_name)
						// 						  ->limit(1)
						// 						  ->get();
						// 		if ( count($station) > 0 ) {
						// 			// 						$station = $order->items->first()->station_name." > ".$station->first()->station_status;
						// 			$station = $station->first()->station_status;
						// 		} else {
						// 			$station = "New order received. In queue for production";
						// 		}
						// 	}
						// } else {
						// 	$orderinfo['tracking'] = $order->items->first()->shipInfo->shipping_id;
						// 	// 					dd(Helper::getTrackingUrl($order->items->first()->tracking_number));
						// 	$station = "Shipped";
						// }

						// 		dd($order);
						//
						
						return view('items.trk_order_status')
							->with('request', $request)
							->with('orderinfo', $orderinfo);
				}
			}
			
			return view('items.trk_order_status')
				->with('request', $request)
				->withErrors('Email:' . $email . ' not found for Order# ' . $orderNumber);
			
		}
		
		return view('items.trk_order_status')->with('request', $request);
		
	}

	public function delete_item_id ($order_5p, $item_id)
	{
		$item = Item::where('id', $item_id)
							->whereNull('tracking_number')
							->first();
		
		if (!$item) {
			return redirect()->back()->withErrors('Item ' . $item_id . ' not found.');
		}
							
		$item->item_status = 6;
		$item->save();
		
		if ($item->item_status == 'wap') {
			Wap::removeItem($item->id, $item->order_5p);
		}
		
		if ($item->batch_number != '0') {
			Batch::isFinished($item->batch_number);
		} 
		
		$order_item_count = Item::where('order_5p', $order_5p)
								->where('item_status', '!=', 6)
								->where('item_status', '!=', 2)
								->where('is_deleted', '0')
								->count();
								
		if ( $order_item_count == 0 ) {
			$order = Order::find($order_5p);
			
			if ($order->order_status != 6) {
				$order->order_status = 8;
				$order->save();
			}
			
		}

		return redirect()
			->back()
			->with('success', "Item #" . $item_id . " cancelled.");
	}

	public function restore_item_id ($order_5p, $item_id)
	{
		$item = Item::where('id', $item_id)
							->where('item_status', 6)
							->first();
		
		if (!$item) {
			return redirect()->back()->withErrors('Item ' . $item_id . ' not found.');
		}
		
		$order = Order::find($order_5p);
		
		if (!$order) {
			return redirect()->back()->withErrors('Order ' . $order_5p . ' not found.');
		}
		
		$item->batch_number = '0';
		$item->item_status = 1;
		$item->save();
		
		if ( $order->order_status != 4 ) {
			
			$order->order_status = 4;
			$order->save();
			
		}

		return redirect()
			->back()
			->with('success', "Item #" . $item_id . " restored.");
	}
}
