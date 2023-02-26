<?php

namespace App\Http\Controllers;

use App\Access;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Http\Requests;
use App\Http\Requests\StoreCreateRequest;
use App\Http\Controllers\Controller;
use App\Store;
use App\Order;
use App\Ship;
use Market;
use Market\Quickbooks;
use Monogram\CSV;
use Monogram\GXSConnection;
use Monogram\Batching;

class StoreController extends Controller
{

		public static function retrieveData () {

//			$gxs = new GXSConnection;
//			$gxs->getFiles();

			$stores = Store::where('is_deleted', '0')
											->orderBy('sort_order')
											->get();

			foreach  ($stores as $store) {

				if ($store->input == '1')	{
					//load class dynamically
					try {
						$className =  'Market\\' . $store->class_name;
						$controller =  new $className;
						$controller->getInput($store->store_id);

						if ($store->batch == '2')	{
							Batching::auto(0, $store->store_id, null);
						}
					} catch (\Exception $e) {
						Log::error($e->getMessage());
					}

				}
			}

//			$gxs->sendFiles();
		}


		public static function backOrderNotify($storeID, $item_ids) {

			//

		}

    public function importZakeke(Request $request)
    {
        $csv = new CSV;
        $data = $csv->intoArray($request->file("import")->getPathname(), ",");

        $temp = [];
        foreach ($data as $datum) {
            foreach ($datum as $value) {
                $temp[] = $value;
            }
        }
        $data = $temp;
        unset($temp);

        $stats = [
            "NOT_FOUND" => [],
            "QUANTITY_MORE_THAN_ONE" => [],
            "STATUS_ISSUE" => [],
            "ORDER_MATCHED" => 0
        ];

        $orders = Order::with("items")
            ->whereIn("short_order", $data)
            ->get();


        $stats['NOT_FOUND'] = $data;
        unset($data);

        /*
         * Remove found orders from the NOT_FROUND
         * Only leaving the ones that aren't found
         */
        foreach ($orders as $order) {
            if(in_array($order->short_order, $stats['NOT_FOUND'])) {
                unset($stats['NOT_FOUND'][array_search($order->short_order, $stats['NOT_FOUND'])]);
            }
        }

        /**
         * Adding the order short_order here
         * will not add it to the
         * @see $stats['ORDER_MATCHED']++
         */
        $filters = [];

        foreach ($orders as $order) {


            foreach ($order->items as $item) {


                /*
                 * Check if contains more than 2 stuff
                 */
                if ($item->item_quantity >= 2 or count($order->items) >= 2) {
                    $stats['QUANTITY_MORE_THAN_ONE'][] = $order->short_order;
                } else {
                    if (!isset($filters[$order->short_order])) {

                        /*
                         * Check if item is on hold
                         */
                        if ($order->order_status != 23) {
                            if(!in_array($order->short_order, $stats['QUANTITY_MORE_THAN_ONE'])) {
                                $stats['STATUS_ISSUE'][] = $order->short_order;
                            }
                        } else {
                            // Not on hold can continue
                            $stats['ORDER_MATCHED']++;
                            $filters[] = $order->short_order;
                        }
                    }

                }
            }
        }

        /*
         * Now use the filters array, supply it to the zakeke bin I made in GoLang to fetch the links
         */

        $zakekeFilters = implode(",", $filters);
        $response = shell_exec("zakeke " . $zakekeFilters);

        $data = @json_decode($response, true);



        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            return redirect()->back()->withErrors('Zakeke API seems to be down, try again later!');
        } else {
            /*
             * Now start loadingthrough order again, and set their graphic.
             */


            $ctx = stream_context_create(array('http'=>
                array(
                    'timeout' => 300,  //1200 Seconds is 20 Minutes
                )
            ));

            foreach ($orders as $order) {


                foreach ($order->items as $item) {

                    // Make sure the batch is not empty
                    if ($item->batch_number !== "") {

                        /*
                         * Ensure it exists, and the PNG link is not empty
                         */
                        if (isset($data[$order->short_order]['Links'][0]['PDF']) && $data[$order->short_order]['Links'][0]['PDF'] !== "") {



                            $link = $data[$order->short_order]['Links'][0]['PDF'];
                            $linkEncoded = base64_encode($link);
                            $batch = $item->batch_number;
                            $id = $item->id;
                            file_get_contents("http://order.monogramonline.com/lazy/link?link=$linkEncoded&batch_number=$batch&item_id=$id", false, $ctx);
                        }
                    }
                }
            }
        }

        $message = "We have successfully fetched the graphic for the following orders:</br> " . $this->pretty($filters);

        if(isset($stats['NOT_FOUND']) && count($stats['NOT_FOUND']) >= 1) {
        //    $message .= "\nOrders that we couldn't be found: " . $this->pretty($stats['NOT_FOUND']);
        }
        if(isset($stats['STATUS_ISSUE']) && count($stats['STATUS_ISSUE']) >= 1) {
            $message .= "\nOrders that status did not match filter: " . $this->pretty($stats['STATUS_ISSUE']);
        }
        if(isset($stats['QUANTITY_MORE_THAN_ONE']) && count($stats['QUANTITY_MORE_THAN_ONE']) >= 1) {
            $message .= "\nOrders we couldn't process because it had multiple lines: " . $this->pretty($stats['QUANTITY_MORE_THAN_ONE']);
        }
        return redirect()->back()->withInput()->withSuccess($message);
    }

        public function pretty($array) {

            $arr = explode(",", implode(",", $array));
            $str ="";
            Foreach(array_chunk($arr,5) as $sub){
                $str .= trim(implode(", ",$sub)) .",<br>\n";
            }
            return $str;
        }


		public function import(Request $request)
		{
			$orders = [];
			$errors = null;

			if($request->has("dropship")){
                $store = Store::where("store_id", $request->get('store_id'))->first();
                $data = Market\Dropship::export($request, $store);

                $orders = Order::with('items', 'customer', 'store')
                    ->whereIn('id', $data['orders'])
                    ->get();
            } else {
                if ($request->has('store_id')) {
                    $store = Store::where('store_id', $request->get('store_id'))->first();

                    if ($store->input == '3') {
                        $className = 'Market\\' . $store->class_name;

                        $controller = new $className;
                        $result = $controller->importCsv($store, $request->file('import'));
                        $errors = $result['errors'];
                        $orders = Order::with('items', 'customer', 'store')
                            ->whereIn('id', $result['order_ids'])
                            ->get();

                        if ($store->batch == '2') {
                            $store_ids = array_unique($orders->pluck('store_id')->toArray());

                            foreach ($store_ids as $store_imported) {
                                Batching::auto(0, $store_imported);
                            }
                        }
                    }

                } else if ($request->has('download_file')) {

                    $pathToFile = storage_path() . '/EDI/download/' . $request->get('download_file');

                    if (file_exists($pathToFile)) {
                        return response()->download($pathToFile)->deleteFileAfterSend(true);
                    } else {
                        $errors[] = $request->get('download_file') . ' Not Found';
                    }
                }
            }
			
      $downloads = array_diff(scandir(storage_path() . '/EDI/download'), array('..', '.')); 
      
			$import_stores = Store::where('is_deleted', '0')
											->where('input', '3')
											
											->orderBy('sort_order')
											->get()
											->pluck('store_name', 'store_id');
											
			// return redirect()->action('OrderController@getList')->withErrors($errors);

            $file = "/var/www/order.monogramonline.com/Store.json";

            $data = [];
            if(file_exists($file)) {
                $data = json_decode(file_get_contents($file), true);
            }
            $import_storesTracking = new Collection();

            $storeNames = [];

            foreach ($data as $name => $dt) {
                if($dt['DROPSHIP_IMPORT']) {
                    $storeNames[] = $name;
                }
            }
            if(count($storeNames) !== 0) {
                $import_storesTracking = Store::whereIn("store_name", $storeNames)
                    ->get()
                    ->pluck("store_name", "store_id");
            }


            $orderIds = "";
            foreach ($orders as $order) {
                if(count($orderIds) !== 0) {
                    $orderIds  .= "," . $order->id;
                } else {
                    $orderIds  = $order->id;
                }
            }
			return view('stores.import', compact('import_stores', 'orders', 'downloads', "import_storesTracking", "orderIds"))->withErrors($errors);
		}
		
		public function exportSummary(Request $request)
		{

            $loadDrop = $request->get("drop", false);


            $qb_summary = Ship::join('stores', 'shipping.store_id', '=', 'stores.store_id')
												->selectRaw('stores.store_id, stores.store_name, COUNT(*) as count')
												->whereNull('shipping.qb_export')
												->where('stores.qb_export', '1')
												->where('shipping.is_deleted', '0')
												->groupBy('store_id')
												->get();
			
			$csv_summary = Ship::join('stores', 'shipping.store_id', '=', 'stores.store_id')
												->selectRaw('shipping.store_id, stores.store_name, COUNT(*) as count')
												->whereNull('shipping.csv_export')
												->where('stores.ship', '4')
												->where('shipping.is_deleted', '0')
												->groupBy('store_id')
												->get();


			$stores =  Store::where('is_deleted', '0')
									->where('qb_export', '1')
									->where('invisible', '0')
									->orderBy('sort_order')
									->get()
									->pluck('store_name', 'store_id');



            $file = "/var/www/order.monogramonline.com/Store.json";

            $data = [];
            if(file_exists($file)) {
                $data = json_decode(file_get_contents($file), true);
            }


            /*
             * Get all the stores that has dropshipment stuff
             */
            $storesNew =  Cache::remember("stores_all", 1, function() {
                return Store::all();
            });


            /*
             * Store.json
             */
            $file = "/var/www/order.monogramonline.com/Store.json";

            $data = [];
            if(file_exists($file)) {
                $data = json_decode(file_get_contents($file), true);
            }

            $dropship = [];


            if($loadDrop) {
                foreach ($storesNew as $storeD) {
                    if (isset($data[$storeD->store_name]) && $data[$storeD->store_name]['DROPSHIP']) {

                        $id = $storeD->store_id;


                        /*
                         * A new implementation of no cache, avoid issue where item are old
                         */
                        $toAdd = \App\Order::with("items", "customer", "items.shipInfo")
                            ->whereHas("items", function ($query) use ($id) {
                                return $query->where("store_id", $id)
                                    ->whereIn("child_sku", Cache::get('SKU_TO_INVENTORY_ID')['ALL'])
                                    ->withinDate(Carbon::createFromDate(2021, 11, 10)->toDateString(), Carbon::now()->addMonth(5)->toDateString());
                            })
                            ->where("order_status", "<=", "4")
                            ->whereNotIn("id", Cache::get("SHIPMENT_CACHE"))
                            ->get();

                        if (count($toAdd) != 0) {
                            Cache::forget("stores_items_$id");
                            Cache::add("stores_items_$id", $toAdd, 60 * 24);
                            $total[] = $toAdd;
                        }
                        // ---------- Ends here

                        $temp = Cache::get("stores_items_$id");

                        if (count($temp) >= 1) {
                            $dropship[$storeD->id] = [
                                "ID" => $storeD->id,
                                "ID_REAL" => $storeD->store_id,
                                "NAME" => $storeD->store_name,
                                "COUNT" => count($temp)
                            ];
                        }
                    }
                }
            }



			return view('stores.export_summary', compact('qb_summary', 'csv_summary', 'stores', "dropship"));
		}
		
		public function exportDetails(Request $request)
		{
			$store = Store::where('store_id', $request->get('store_id'))->first();
			$type = $request->get('type');

			if ($type == 'qb') {
				
				$title = 'QuickBooks';
				
				if ($store->qb_export != '1') {
					return redirect()->back()->withErrors($store->store_name . ' is not configured for Quickbooks Export');
				}
				
				$field = 'qb_export';
													
			} else if ($type == 'csv') {
				
				$title = 'Shipment';
				
				if ($store->ship != '4') {

                    /*
                     * Only if it's not a dropship
                     */
					if(!$request->has("dropship")) {
                        return redirect()->back()->withErrors($store->store_name . ' is not configured for CSV Export');
                    }
				}
				
				$field = 'csv_export';
			}

            if(!$request->has("dropship")) {
                $details = Ship::with('order')
                    ->where('store_id', $request->get('store_id'))
                    ->whereNull($field)
                    ->where('is_deleted', '0')
                    ->get();
            }

            $dropship = [];

            if($request->has("dropship")) {
                $id = $request->get("store_id");
                $name = Store::where("store_id", $id)->first()->store_name;

                $dropship = \App\Order::with(
                    [
                        "items",
                        "customer",
                        "items.shipInfo" => function($query) {
                           return $query->whereNull('tracking_number');
                        }
                    ]
                )
                    ->whereHas("items", function ($query) use ($id) {
                        return $query->where("store_id", $id)
                            ->whereIn("child_sku", Cache::get('SKU_TO_INVENTORY_ID')['ALL'])
                            ->withinDate(Carbon::createFromDate(2021, 11, 10)->toDateString(), Carbon::now()->addMonth(5)->toDateString());
                    })
                    ->where("order_status", "<=", "4")
                    ->whereNotIn("id", Cache::get("SHIPMENT_CACHE"))
                    ->get();


            }

            $isDropship = count($dropship) !== 0;
			return view('stores.export_details', compact('title', 'store', 'details', 'type', "dropship", "isDropship"));
		}
		
		public function createExport(Request $request) {


			$shipments = Ship::with('items')
											->whereIn('id', $request->get('ship_ids'))
											->where('is_deleted', '0')
											->get();
			
			if ($request->get('type') == 'qb') {
				
				$pathToFile = Quickbooks::export($shipments);
				
				Ship::whereIn('id', $request->get('ship_ids'))
												->where('is_deleted', '0')
												->update(['qb_export' => '1']);
				
			} else if ($request->get('type') == 'csv' && !$request->has('$dropship')) {
				
				$store = Store::where('store_id', $request->get('store_id'))->first();
				$pathToFile = null;
				
				if ($store->ship == '4')	{



					try {
						$className =  'Market\\' . $store->class_name; 
						$controller =  new $className;
						$pathToFile = $controller->exportShipments($store->store_id, $shipments);
						
						Ship::whereIn('id', $request->get('ship_ids'))
														->where('is_deleted', '0')
														->update(['csv_export' => '1']);
														
					} catch (\Exception $e) {
						Log::error($e->getMessage());
					}
					
				}
            }


            if($request->has('$dropship')) {


                $id = $request->get("store_id");
                $store = Store::where('store_id', $request->get('store_id'))->first();
                $name = $store->store_name;

                /*
                 * A new implementation of no cache, avoid issue where item are old
                 */
                $toAdd =  \App\Order::with("items", "customer", "items.shipInfo")
                    ->whereHas("items", function ($query) use ($id) {
                        return $query->where("store_id", $id)
                            ->whereIn("child_sku", Cache::get('SKU_TO_INVENTORY_ID')['ALL'])
                            ->withinDate(Carbon::createFromDate(2021, 11, 10)->toDateString(), Carbon::now()->addMonth(5)->toDateString());
                    })
                    ->where("order_status", "<=", "4")
                    ->whereNotIn("id", Cache::get("SHIPMENT_CACHE"))
                    ->get();

                if(count($toAdd) != 0) {
                    Cache::forget("stores_items_$id");
                    Cache::add("stores_items_$id", $toAdd, 60 * 24);
                    $total[] = $toAdd;
                }

                $temp = Cache::get("stores_items_$id");

                if($request->has("ship_ids")) {
                    $removeList = $request->get("ship_ids", []);

                    /*
                     * Filter out the orders depending on the page checkbox
                     */
                    foreach ($temp as $index => $order) {
                        if(!in_array($order->id, $removeList)) {
                            unset($temp[$index]);
                        }
                    }
                }

                $path = Market\Dropship::handle($store, $temp);

                if ($path !== "") {
                    return response()->download($path)->deleteFileAfterSend(true);
                } else {
                    return redirect()->back()->withErrors('Error trying to create a dropship export file');
                }
            }

			if ($pathToFile != null) {
				if (!is_array($pathToFile)) {
					return response()->download($pathToFile)->deleteFileAfterSend(false);
				} else {
					
				}
			} else {
				return redirect()->back()->withErrors('Error creating export file');
			}
		}

    /**
     * @return \Illuminate\Contracts\View\Factory
     * |\Illuminate\Foundation\Application|\Illuminate\View\View
     */
		public function index()
		{
				$stores = Store::with('store_items')
									->where('is_deleted', '0')
									->orderBy('sort_order')
                                    ->where('permit_users', 'like', "%".auth()->user()->id ."%")
									->get();
				
				$companies = Store::$companies;
				
				return view('stores.index', compact('stores', 'companies'));
		}

		/**
		 * Show the form for creating a new resource.
		 *
		 * @return \Illuminate\Http\Response
		 */
		public function create()
		{
				$companies = Store::$companies;
				
				return view('stores.create', compact('input_options', 'companies'));
		}

		/**
		 * Store a newly created resource in storage.
		 *
		 * @param  \Illuminate\Http\Request  $request
		 * @return \Illuminate\Http\Response
		 */
		public function store(StoreCreateRequest $request)
		{
				$sort = Store::selectRaw('MAX(sort_order) as num')->first();
				
				$store = new Store;
				
				$new_id = strtolower($request->get('store_id'));
				$new_id = str_replace(' ', '', $new_id);
				$new_id = preg_replace('/[^\w]+/', '-', $new_id);
				
				$store->store_id = $new_id;
				
				$store->store_name = $request->get('store_name');
				$store->company = $request->get('company');
				$store->qb_export = $request->get('qb_export') ? $request->get('qb_export') : '0';
				$store->sort_order = $sort->num + 1;
				$store->class_name = $request->get('class_name');
				$store->email = $request->get('email');
				$store->input = $request->get('input');
				$store->change_items = $request->get('change_items') ? $request->get('change_items') : '0';
				$store->qc = $request->get('qc') ? $request->get('qc') : '0';
				$store->batch = $request->get('batch') ? $request->get('batch') : '0';
				$store->print = $request->get('print') ? $request->get('print') : '0';
				$store->confirm = $request->get('confirm') ? $request->get('confirm') : '0';
				$store->backorder = $request->get('backorder') ? $request->get('backorder') : '0';
				$store->ship_banner_url = $request->get('ship_banner_url') ? $request->get('ship_banner_url') : '';
				$store->ship_banner_image = $request->get('ship_banner_image') ? $request->get('ship_banner_image') : '';
				$store->ship = $request->get('ship') ? $request->get('ship') : '0';
				$store->validate_addresses = $request->get('validate_addresses') ? $request->get('validate_addresses') : '0';
				$store->change_method = $request->get('change_method') != null ? $request->get('change_method') : '1';
				$store->ship_label = $request->get('ship_label');
				$store->packing_list = $request->get('packing_list');
				$store->multi_carton = $request->get('multi_carton');
				
				$store->ups_type = $request->get('ups_type');
				$store->ups_account = $request->get('ups_account');
				
				$store->fedex_type = $request->get('fedex_type');
				$store->fedex_account = $request->get('fedex_account');
				$store->fedex_password = $request->get('fedex_password');
				$store->fedex_key = $request->get('fedex_key');
				$store->fedex_meter = $request->get('fedex_meter');
				
				$store->ship_name = $request->get('ship_name');
				$store->address_1 = $request->get('address1');
				$store->address_2 = $request->get('address2');
				$store->city = $request->get('city');
				$store->state = $request->get('state');
				$store->zip = $request->get('zip');
				$store->phone = $request->get('phone');
				
				$store->save();
				
				return redirect()->action('StoreController@index')
									->with('success', $store->store_name . ' Created');
		}

		/**
		 * Display the specified resource.
		 *
		 * @param  int  $id
		 * @return \Illuminate\Http\Response
		 */
		public function show($id)
		{
				// $store = Store::find($id);
				// 
				// $input_options = Store::inputOptions();
				// $batch_options = Store::batchOptions();
				// $notify_options = Store::notifyOptions();
				// 
				// return view('stores.show', compact('store', 'input_options', 'batch_options', 'notify_options'));
		}

		/**
		 * Show the form for editing the specified resource.
		 *
		 * @param  int  $id
		 * @return \Illuminate\Http\Response
		 */
		public function edit($id)
		{
			$store = Store::find($id);

			$companies = Store::$companies;

            $file = "/var/www/order.monogramonline.com/Store.json";
            $dropship = false;
            $dropshipTracking = false;

            if(file_exists($file)) {
                $data = json_decode(file_get_contents($file), true);

                if(isset($data[$store->store_name])) {
                    $dropship = $data[$store->store_name]['DROPSHIP'];
                    $dropshipTracking = $data[$store->store_name]['DROPSHIP_IMPORT'];
                }
            }

			return view('stores.edit', compact('store', 'companies', 'dropship', "dropshipTracking"));
		}

		/**
		 * Update the specified resource in storage.
		 *
		 * @param  \Illuminate\Http\Request  $request
		 * @param  int  $id
		 * @return \Illuminate\Http\Response
		 */
		public function update(Request $request, $id)
		{
			if (!$request->has('email')) {
				return redirect()->back()->withInput()->withErrors('Email Required');
			}
			$store = Store::find($id);

			$store->store_name = $request->get('store_name');
			$store->company = $request->get('company');
			$store->qb_export = $request->get('qb_export') ? $request->get('qb_export') : '0';
			$store->class_name = $request->get('class_name');
			$store->email = $request->get('email');
			$store->input = $request->get('input');
			$store->change_items = $request->get('change_items') ? $request->get('change_items') : '0';
			$store->batch = $request->get('batch') ? $request->get('batch') : '0';
			$store->print = $request->get('print') ? $request->get('print') : '0';
			$store->qc = $request->get('qc') ? $request->get('qc') : '0';
			$store->confirm = $request->get('confirm') ? $request->get('confirm') : '0';
			$store->backorder = $request->get('backorder') ? $request->get('backorder') : '0';
			$store->ship_banner_url = $request->get('ship_banner_url') ? $request->get('ship_banner_url') : '';
			$store->ship_banner_image = $request->get('ship_banner_image') ? $request->get('ship_banner_image') : '';
			$store->ship = $request->get('ship') ? $request->get('ship') : '0';
			$store->validate_addresses = $request->get('validate_addresses') ? $request->get('validate_addresses') : '0';
			$store->change_method = $request->get('change_method') != null ? $request->get('change_method') : '1';
			$store->ship_label = $request->get('ship_label');
			$store->packing_list = $request->get('packing_list');
			$store->multi_carton = $request->get('multi_carton');
			
			$store->ups_type = $request->get('ups_type');
			$store->ups_account = $request->get('ups_account');
			$store->fedex_type = $request->get('fedex_type');
			$store->fedex_account = $request->get('fedex_account') ;
			$store->fedex_password = $request->get('fedex_password');
			$store->fedex_key = $request->get('fedex_key');
			$store->fedex_meter = $request->get('fedex_meter');
			
			$store->ship_name = $request->get('ship_name');
			$store->address_1 = $request->get('address1');
			$store->address_2 = $request->get('address2');
			$store->city = $request->get('city');
			$store->state = $request->get('state');
			$store->zip = $request->get('zip');
			$store->phone = $request->get('phone');

            /*
        * Create a configuration file for them when they edit them
        */
            $file = "/var/www/order.monogramonline.com/Store.json";
            $template = [
                "DROPSHIP" => (bool) $request->get('dropship'),
                "DROPSHIP_IMPORT" => (bool) $request->get("dropship_tracking")
            ];

            if(!file_exists($file)) {
                file_put_contents($file, json_encode(
                    [
                        $store->store_name => $template
                    ], JSON_PRETTY_PRINT
                ));
            } else {
                $data = json_decode(file_get_contents($file), true);

                if(!isset($data[$store->store_name])) {
                    $data[$store->store_name] = $template;

                    file_put_contents($file, json_encode(
                        $data, JSON_PRETTY_PRINT
                    ));
                } else {
                    $data[$store->store_name] = $template;
                    file_put_contents($file, json_encode(
                        $data, JSON_PRETTY_PRINT
                    ));
                }
            }
            // -------------------

			$store->save();
			
			return redirect()->action('StoreController@edit', ['id' => $id])
							->with('success', $store->store_name . ' Updated');
		}
		
		public function sortOrder($direction, $id)
		{
			$store = Store::find($id);
			
			if (!$store) {
				Log::error('Store sort: Store not Found');
				return redirect()->action('StoreController@index')->withError('Store not Found');
			}
		
			if ($direction == 'up') {
				$new_order = $store->sort_order - 1;
			} else if ($direction == 'down') {
				$new_order = $store->sort_order + 1;
			} else {
				Log::error('Store sort: Direction not recognized');
				return redirect()->action('StoreController@index')->withError('Sort direction not recognized');
			}
			
			$switch = Store::where('sort_order', $new_order)->get();
			
			if (count($switch) > 1) {
				Log::error('Store sort: More than one store with same sort order');
				return redirect()->action('StoreController@index')->withError('Sort Order Error');
			}
			
			if (count($switch) == 1) {
				$switch->first()->sort_order = $store->sort_order;
				$switch->first()->save();
			}
			
			$store->sort_order = $new_order;
			$store->save();
			
			return redirect()->action('StoreController@index');
		}
		
		public function visible($id)
		{
			$store = Store::find($id);
			
			if (!$store) {
				Log::error('Store visible: Store not Found');
				return redirect()->action('StoreController@index')->withError('Store not Found');
			}
		
			if ($store->invisible == '0') {
				$store->invisible = '1';
			} else {
				$store->invisible = '0';
			} 
			
			$store->save();
			
			return redirect()->action('StoreController@index');
		}

		public function destroy($id)
		{
			$store = Store::find($id);
			$store->is_deleted = '1';
			$store->save();

			return redirect()->action('StoreController@index');
		}
		
		public function qbExport (Request $request) {
			
			if (!$request->has('store_ids') || !$request->has('start_date') || !$request->has('end_date')) {
				return redirect()->back()->withInput()->withErrors('Stores and dates required to create Quickbooks export');
			}
			
			$shipments = Ship::with('items')
											->whereIn('store_id', $request->get('store_ids'))
											->where('transaction_datetime', '>=', $request->get('start_date') . ' 00:00:00')
											->where('transaction_datetime', '<=', $request->get('end_date') . ' 23:59:59')
											->where('is_deleted', '0')
											->get();
			
			$pathToFile = Quickbooks::export($shipments);
			
			$ids = $shipments->pluck('id')->toArray();
			
			Ship::whereIn('id', $ids)->update(['qb_export' => '1']);
			
			if ($pathToFile != null) {
				return response()->download($pathToFile)->deleteFileAfterSend(false);
			}
		}

    public function qbCsvExport(Request $request)
    {

        if (!$request->has('store_ids') || !$request->has('start_date') || !$request->has('end_date')) {
            return redirect()->back()->withInput()->withErrors('Stores and dates required to create CSV export');
        }

        try {
            $shipments = Ship::  join('items', 'items.tracking_number', '=', 'shipping.tracking_number')
                ->join('orders', 'items.order_5p', '=', 'orders.id')
                ->whereIn('shipping.store_id', $request->get('store_ids'))
                ->where('shipping.transaction_datetime', '>=', $request->get('start_date') . ' 00:00:00')
                ->where('shipping.transaction_datetime', '<=', $request->get('end_date') . ' 23:59:59')
                ->where('shipping.is_deleted', '0')
//                ->limit(5)
//                ->selectRaw('sum(items.item_quantity) as sum, count(items.id) as count')
                ->get();
//                ->get(['item_code', 'item_quantity', 'item_unit_price', 'purchase_order', 'order_date','transaction_datetime']);


//                $shipments = Ship::  join('items', 'items.tracking_number', '=', 'shipping.tracking_number')
//                ->join('orders', 'items.order_5p', '=', 'orders.id')
////                ->whereIn('shipping.store_id', $request->get('store_ids'))
//                ->where('orders.order_date', '>=', $request->get('start_date') . ' 00:00:00')
//                ->where('orders.order_date', '<=', $request->get('end_date') . ' 23:59:59')
//                ->where('orders.is_deleted', '0')
//                ->limit(5)
//                ->get(['item_code', 'item_quantity', 'item_unit_price', 'purchase_order', 'order_date','transaction_datetime']);
//
////            $shipments = Item::with('order')
////            $shipments = Item::with('order')
//            $shipments = Item::with('order')
//            ->where('is_deleted', '0')
//                ->searchStore('524339241')
//                ->searchStatus('2')
//                ->searchSection($request->get('section'))
//                ->searchOrderDate($request->get('start_date'), $request->get('end_date'))
////                ->selectRaw('sum(items.item_quantity) as sum, count(items.id) as count')
////                ->limit(5)
////                ->pluck('id')
////                ->get(['items.item_code', 'items.item_quantity', 'items.item_unit_price', 'order.purchase_order', 'order.order_date','order.created_at']);
////                ->select('items.item_code', 'items.item_quantity','items.item_unit_price')
//            ->get();

            set_time_limit(0);
            $pathToFile = Quickbooks::csvExport($shipments);

            if ($pathToFile != null) {
                return response()->download($pathToFile)->deleteFileAfterSend(false);
            }

        } catch (Exception $e) {
            Log::error('Error Creating qbCsvExport - ' . $e->getMessage());
        }
    }

    public function storeAccess($id)
    {
        $store = Store::find($id);
        if(!in_array(auth()->user()->id, $store->permit_users)){
            abort(403);
        }
        $users = User::where('is_deleted', 0)->get();

        return view('stores.permission', compact('users', 'store'));
    }

    public function storePermissionUpdate(Request $request, $id)
    {
        $store = Store::find($id);
        if($store){
            $store->permit_users = $request->user_access;
            $store->save();
            $users = count($request->user_access) ? $request->user_access : [];

            foreach($users as $user){
                $exist_access = Access::where('user_id', $user)->where('page', 'permission')->count();
                if(!$exist_access){
                    $access = new Access();
                    $access->user_id = $user;
                    $access->page = 'permission';
                    $access->save();
                }

            }
            return redirect()->route('stores.index');
        }

        return redirect()->back();
    }
}
