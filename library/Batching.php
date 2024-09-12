<?php 

namespace Monogram;

use App\Http\Controllers\GraphicsController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Item;
use App\Order;
use App\Store;
use App\Note;
use App\Batch;
use App\BatchRoute;

class Batching
{

	public static function islocked() 
	{
		if (file_exists(storage_path() . '/autobatch.lock')) {
			if (filemtime(storage_path() . '/autobatch.lock') > (time() - (60 * 60))) {
				return true;
			} else {
				Batching::unlock();
			}
		} 
		
		return false;
	}
	
	public static function lock() 
	{
		touch(storage_path() . '/autobatch.lock');
	}
	
	public static function unlock () 
	{
		if (file_exists(storage_path() . '/autobatch.lock')) {
			unlink(storage_path() . '/autobatch.lock');
		}
	}
	
	public static function auto ($max_units = 0, $store_id = null, $export = 1, $specificOrder = "")
	{
        logger('Batching function auto started');

		$end = date("Y-m-d", strtotime("+1 day"));

        $batch_routes = Batching::createAbleBatches(0, false, "2023-10-10", $end, null, null, $store_id, null, null, $max_units);

		$batches = array();
		
		$count = 0;
		
		foreach($batch_routes as $batch_route) {
			$count++;
			foreach ($batch_route['items'] as $item) {
				$batches[] = sprintf("%s|%s|%s|%s|%s", $count, $batch_route['id'], $item->item_table_id, $item->batch, $item->store_id);
			}
		}


		Batching::createBatch($batches, '', 'active', $export);
		
		return;
	}
	
	public static function failures ()
	{
		$unbatched = Item::with('order.customer', 'parameter_option.route.stations_list.section_info', 'inventoryunit.inventory')
										->join('orders', 'items.order_5p', '=', 'orders.id')
										->whereNull('items.tracking_number')
										->where('items.batch_number', '=', '0')
										->where('items.item_status', '=', '1')
										->where('orders.is_deleted', '0')
										->where('items.is_deleted', '0')
										->take(1500)
										->addSelect([
											DB::raw('items.id AS item_table_id'),
											'items.order_5p',
											'items.item_id',
											'items.item_code',
											'items.child_sku',
											'items.order_id',
											'items.item_quantity',
											'items.item_thumb',
											'items.item_option',
											'items.item_status',
											'items.store_id',
											DB::raw('orders.id as order_table_id'),
											'orders.order_id',
											'orders.order_date',
											'orders.id'
										])
										->get();
		
		$order_statuses = Order::statuses();
		
		$items = array();
		
		foreach ($unbatched as $item) {
			$result = array();
			if ($item->order && $item->order->order_status > 12) {
				$result['hold'] = $order_statuses[$item->order->order_status];
			}
			//test for child sku
			if (!$item->parameter_option) {
				$result['parameter_option'] = 'Child SKU does not exist in 5p';
			} else {
				//test for route
				if ($item->parameter_option->batch_route_id == '115' || $item->parameter_option->batch_route_id == null) {
					$result['route'] =  url( sprintf("/logistics/sku_list?search_for_first=%s&contains_first=equals&search_in_first=child_sku", $item->child_sku));
				}
				if ($item->parameter_option && $item->parameter_option->route && 
						$item->parameter_option->route->stations_list &&
						$item->parameter_option->route->stations_list[0] && 
						$item->parameter_option->route->stations_list[0]->section_info &&
						$item->parameter_option->route->stations_list[0]->section_info->inv_control == '1') {
					//test for stock number 
					if (!$item->inventoryunit || 
							$item->inventoryunit->first()->stock_no_unique == 'ToBeAssigned' || 
							$item->inventoryunit->first()->stock_no_unique == '') {
						$result['stock_no'] = url( sprintf("/logistics/sku_list?search_for_first=%s&contains_first=equals&search_in_first=child_sku", $item->child_sku));
					} else {
						//test for sufficient quantity
						foreach ($item->inventoryunit as $unit) {
							if ($unit->inventory && $unit->inventory->qty_av < ($item->item_quantity * $unit->unit_qty)) {
								$result['qty_av'] = url( sprintf("/inventories?search_for_first=%s&operator_first=in&search_in_first=stock_no_unique", $unit->stock_no_unique));
							}
						}
					}
				}
			}
			
			if ($result != []) {
				$result['item'] = $item;
				$items[] = $result;
			}
		}
		
		return $items;
	}
	
	public static function createAbleBatches ($backorder, $paginate = false, $start_date, $end_date, $search_for, $search_in, 
																							$store_id, $section, $sure3d = null, $max_units = 0)
	{
		
		$max_units = intval($max_units);
		
		if ($backorder == 1) {
			$status = 4;
		} else {
			$status = 1;
		}
		
		if ($store_id == NULL) {
			$store_id = '%';
		}
		
		$stores_result = Store::where('is_deleted','0')
													->where('store_id', 'LIKE', $store_id)
													->orderBy('sort_order')
													->get();
		
		$query_array = array();
		
		$together = array();
		
		foreach ($stores_result as $store_result) {
			if ($store_result->batch == 0) {
				$together[] = $store_result->store_id;
			} else {
				
				if ($sure3d == null) {
					$query_array[] = [[$store_result->store_id], 'whereNull'];
				}
				
				$query_array[] = [[$store_result->store_id], 'whereNotNull'];
			}
		}
		
		if (count($together) > 0) {
			
			if ($sure3d == null) {
				$query_array[] = [$together, 'whereNull'];
			}
			
			$query_array[] = [$together, 'whereNotNull'];
		}
		
		$results = array();
		
		if ( empty($search_in) ) {

            foreach ($query_array as $var) {

                $op = $var[1];
                $val = $var[0];

                $routes = BatchRoute::with([
                    'stations_list.section_info',
                    'itemGroups' => function ($q) use ($op, $val, $status, $paginate, $start_date, $end_date) {
                        $joining = $q->join('items', 'items.child_sku', '=', 'parameter_options.child_sku')
                            ->join('orders', 'orders.id', '=', 'items.order_5p')
                            ->join('stores', 'items.store_id', '=', 'stores.store_id')
                            ->leftjoin('inventory_unit', 'items.child_sku', '=', 'inventory_unit.child_sku')
                            ->leftjoin('inventories', 'inventory_unit.stock_no_unique', '=', 'inventories.stock_no_unique')
                            ->where('items.batch_number', '0')
                            ->where('items.item_status', $status)
                            ->$op('items.sure3d')
                            //->where('items.item_option', 'not like', '[]')
                            ->where('items.is_deleted', '0')
                            ->where('orders.is_deleted', '0')
                            ->where('orders.order_date', '>=', $start_date . ' 00:00:00')
                            ->where('orders.order_date', '<=', $end_date . ' 23:59:59')
                            ->whereIn('orders.store_id', $val)
                            ->whereIn('orders.order_status', [4, 11, 12, 7, 9])
                            ->where(function ($query) {
                                return $query->where('parameter_options.batch_route_id', '!=', 115)
                                    ->whereNotNull('parameter_options.batch_route_id');
                            })
                            ->take(1500)
                            ->addSelect([
                                DB::raw('items.id AS item_table_id'),
                                'items.item_id',
                                'items.item_code',
                                'items.order_id',
                                'items.order_5p',
                                'items.item_quantity',
                                'items.item_thumb',
                                'items.sure3d',
                                DB::raw('orders.id as order_table_id'),
                                'orders.order_id',
                                'orders.short_order',
                                'orders.order_date',
                                'orders.id',
                                'stores.store_id',
                                'stores.store_name',
                                'stores.batch',
                                'inventory_unit.stock_no_unique',
                                'inventory_unit.unit_qty',
                                'inventories.qty_av'
                            ]);

                        return $paginate ? $joining->get() : $joining->paginate(10000);
                    },
                ])
                    ->where('batch_routes.is_deleted', 0)
                    ->where('batch_routes.batch_max_units', '>', 0)
                    ->get();

                foreach ($routes as $route) {
                    if (count($route->itemGroups) > 0) {
                        $results[] = $route;
                        // Log::info($route);
                    }
                }
            }

        } elseif ($search_in == "order_only") {
            foreach ($query_array as $var) {

                $op = $var[1];
                $val = $var[0];

                $routes = BatchRoute::with([
                    'stations_list.section_info',
                    'itemGroups' => function ($q) use ($op, $val, $status, $paginate, $start_date, $end_date, $search_for) {
                        $joining = $q->join('items', 'items.child_sku', '=', 'parameter_options.child_sku')
                            ->join('orders', 'orders.id', '=', 'items.order_5p')
                            ->join('stores', 'items.store_id', '=', 'stores.store_id')
                            ->leftjoin('inventory_unit', 'items.child_sku', '=', 'inventory_unit.child_sku')
                            ->leftjoin('inventories', 'inventory_unit.stock_no_unique', '=', 'inventories.stock_no_unique')
                            ->where('items.batch_number', '0')
                            ->where('items.item_status', $status)
                            ->$op('items.sure3d')
                            //->where('items.item_option', 'not like', '[]')
                            ->where('items.is_deleted', '0')
                            ->where('orders.is_deleted', '0')
                            ->where('orders.order_date', '>=', $start_date . ' 00:00:00')
                            ->where('orders.order_date', '<=', $end_date . ' 23:59:59')
                            ->where('orders.id', '=', $search_for)
                            ->whereIn('orders.store_id', $val)
                            ->whereIn('orders.order_status', [4,11,12,7,9])
                            ->where(function ($query) {
                                return $query->where('parameter_options.batch_route_id', '!=', 115)
                                    ->whereNotNull('parameter_options.batch_route_id');
                            })
                            ->take(1500)
                            ->addSelect([
                                DB::raw('items.id AS item_table_id'),
                                'items.item_id',
                                'items.item_code',
                                'items.order_id',
                                'items.order_5p',
                                'items.item_quantity',
                                'items.item_thumb',
                                'items.sure3d',
                                DB::raw('orders.id as order_table_id'),
                                'orders.order_id',
                                'orders.short_order',
                                'orders.order_date',
                                'orders.id',
                                'stores.store_id',
                                'stores.store_name',
                                'stores.batch',
                                'inventory_unit.stock_no_unique',
                                'inventory_unit.unit_qty',
                                'inventories.qty_av'
                            ]);

                        return $paginate ? $joining->get() : $joining->paginate(10000);
                    },
                ])
                    ->where('batch_routes.is_deleted', 0)
                    ->where('batch_routes.batch_max_units', '>', 0)
                    ->get();

                foreach ($routes as $route) {
                    if (count($route->itemGroups) > 0) {
                        $results[] = $route;
                        // Log::info($route);
                    }
                }
            }
		} elseif ($search_in == 'customer') {
							
			if ( ! $search_for ) {
				return;
			}
			
			$search_for = explode(",", trim($search_for));
			
			foreach ($query_array as $var) {
				
				$op = $var[1];
				$val = $var[0];
					
					$routes = BatchRoute::with([
						'stations_list.section_info',
						'itemGroups' => function ($q) use ($op, $val, $status, $paginate, $start_date, $end_date, $search_for, $search_in) {
							$joining = $q->join('items', 'items.child_sku', '=', 'parameter_options.child_sku')
										 ->join('orders', 'orders.id', '=', 'items.order_5p')
										 ->join('customers', 'customers.id', '=', 'orders.customer_id')
										 ->join('stores', 'items.store_id', '=', 'stores.store_id')
										 ->join('inventory_unit', 'items.child_sku', '=', 'inventory_unit.child_sku')
										 ->join('inventories', 'inventory_unit.stock_no_unique', '=', 'inventories.stock_no_unique')
										 ->where('items.batch_number', '0')
										 ->where('items.item_status', $status)
										 ->$op('items.sure3d')
										 ->where('items.is_deleted', '0')
										 ->where('customers.ship_full_name', 'REGEXP', implode("|", $search_for))
										 ->where('orders.is_deleted', '0')
										 ->where('orders.order_date', '>=', $start_date . ' 00:00:00')
										 ->where('orders.order_date', '<=', $end_date . ' 23:59:59')
										 ->whereIn('orders.store_id', $val)
										 ->whereIn('orders.order_status', [4,11,12,7,9])
									 ->where(function ($query) {
											return $query->where('parameter_options.batch_route_id', '!=', 115)
													->whereNotNull('parameter_options.batch_route_id');
											})
										->take(1500)
										->addSelect([
												DB::raw('items.id AS item_table_id'),
													 'items.item_id',
													 'items.item_code',
													 'items.order_id',
													 'items.order_5p',
													 'items.item_quantity',
													 'items.item_thumb',
													 'items.sure3d',
															 DB::raw('orders.id as order_table_id'),
															 'orders.order_id',
															 'orders.short_order',
															 'orders.order_date',
															 'stores.store_id',
															 'stores.store_name',
															 'stores.batch',
															 'inventory_unit.stock_no_unique',
															 'inventory_unit.unit_qty',
															 'inventories.qty_av'
											 ]);

											return $paginate ? $joining->get() : $joining->paginate(10000);
										},
									])
									->where('batch_routes.is_deleted', 0)
									->where('batch_routes.batch_max_units', '>', 0)
									->get();
						
							foreach ($routes as $route) {
								if (count($route->itemGroups) > 0) {
									$results[] = $route;
								}
							}
					}
					
		} else {
			if ( ! $search_for ) {
				return;
			}
			
			foreach ($query_array as $var) {
					
				$op = $var[1];
				$val = $var[0];
					
					$routes = BatchRoute::with([
						'stations_list.section_info',
						'itemGroups' => function ($q) use ($op, $val, $status, $paginate, $start_date, $end_date, $search_for, $search_in) {
							$joining = $q->join('items', 'items.child_sku', '=', 'parameter_options.child_sku')
										 ->join('orders', 'orders.id', '=', 'items.order_5p')
										 ->join('stores', 'items.store_id', '=', 'stores.store_id')
										 ->join('inventory_unit', 'items.child_sku', '=', 'inventory_unit.child_sku')
										 ->join('inventories', 'inventory_unit.stock_no_unique', '=', 'inventories.stock_no_unique')
										 ->where('items.batch_number', '0')
										 ->where('items.item_status', $status)
										 ->$op('items.sure3d')
										 ->where('items.' . $search_in, 'LIKE', '%' . $search_for . '%')
										 ->where('items.is_deleted', '0')
										 ->where('orders.is_deleted', '0')
										 ->where('orders.order_date', '>=', $start_date . ' 00:00:00')
										 ->where('orders.order_date', '<=', $end_date . ' 23:59:59')
										 ->whereIn('orders.store_id', $val)
										 ->whereIn('orders.order_status', [4,11,12,7,9])
										 ->where(function ($query) {
											 return $query->where('parameter_options.batch_route_id', '!=', 115)
															->whereNotNull('parameter_options.batch_route_id');
										 })
										 ->take(1500)
										 ->addSelect([
											 DB::raw('items.id AS item_table_id'),
											 'items.item_id',
											 'items.item_code',
											 'items.order_id',
											 'items.order_5p',
											 'items.item_quantity',
											 'items.item_thumb',
											 'items.sure3d',
											 DB::raw('orders.id as order_table_id'),
											 'orders.order_id',
											 'orders.short_order',
											 'orders.order_date',
											 'orders.id',
											 'stores.store_id',
											 'stores.store_name',
											 'stores.batch',
											 'inventory_unit.stock_no_unique',
											 'inventory_unit.unit_qty',
											 'inventories.qty_av'
										 ]);

							return $paginate ? $joining->get() : $joining->paginate(10000);
						},
					])
									 ->where('batch_routes.is_deleted', 0)
									 ->where('batch_routes.batch_max_units', '>', 0)
									 ->get();
									 
					foreach ($routes as $route) {
						if (count($route->itemGroups) > 0) {
							$results[] = $route;
						}
					}
			}
		}
		
		$batches = array();
		
		foreach ($results as $batch_route) {
			$batch = array();
			
			$items = array();
			
			if ($section && $batch_route->stations_list[0]->section != $section) {
				continue;
			}
			if ($batch_route->stations_list[0]->section != 0 && $batch_route->stations_list[0]->section_info) {
				$inv_control = $batch_route->stations_list[0]->section_info->inv_control;
			} else {
				$inv_control = 0;
			}
			
			$batch['id'] = $batch_route->id;
			$batch['batch_code'] = $batch_route->batch_code;
			$batch['batch_route_name'] = $batch_route->batch_route_name;
			$batch['next_station'] = $batch_route->stations_list[0]->station_name . ' ( ' . $batch_route->stations_list[0]->station_description . ')';
			$batch['batch_max_units'] = $batch_route->batch_max_units;
			
			if($batch_route->batch_max_units) {
				$mixed_groups = $batch_route->itemGroups->groupBy('allow_mixing');
				foreach($mixed_groups as $group_key => $group_values) {
					if($group_key == 0) {
						foreach($group_values->groupBy('child_sku') as $row) {
							$batch['items'] = array();
							foreach($row->chunk($batch_route->batch_max_units) as $chunkedRows) {
								if($batch_route->stations_list->count()) {
									foreach($chunkedRows->sortBy('order_id') as $item) {
										if ($inv_control == 0 || 
												(($item->stock_no_unique != 'NeedsToBeAssigned' && $item->stock_no_unique != null)
													&& ($item->qty_av >= ($item->item_quantity * $item->unit_qty)))) {
											$batch['items'][] = $item; 
										}
									}
									if (count($batch['items']) > 0 && 
												($max_units == 0 || ($max_units == 1 && count($batch['items']) >= $batch_route->batch_max_units))) {
										$batches[] = $batch;
									}
									$batch['items'] = null;
								}
							}
						 }
						} else {
							foreach($group_values->chunk($batch_route->batch_max_units) as $chunkedRows) {
								$batch['items'] = array();
								if($batch_route->stations_list->count()) {
									foreach($chunkedRows->sortBy('order_id') as $item) {
										if ($inv_control == 0 || 
												(($item->stock_no_unique != 'NeedsToBeAssigned' && $item->stock_no_unique != null)
													&& ($item->qty_av >= ($item->item_quantity * $item->unit_qty)))) {
											$batch['items'][] = $item; 
										}
									}
								}
								if (count($batch['items']) > 0 && 
											($max_units == 0 || ($max_units == 1 && count($batch['items']) >= $batch_route->batch_max_units))) {
									$batches[] = $batch;
								}
								$batch['items'] = null;
							}
					}
				}
			}
		} 
		
		return $batches;
	}
	
	public static function createBatch ($batches, $prefix = '', $status = 'active', $export_batch = null)
		{
			try {
                if (Batching::islocked()) {
                    Log::info('Existing AutoBatch '.count($batches).' executing');
                    return false;
                }

                Batching::lock();

                $acceptedGroups = [];

                $current_group = -1;
                set_time_limit(0);

                foreach ( $batches as $preferredBatch ) {
                    list($inGroup, $batch_route_id, $item_id, $batch_separate, $store_id) = explode("|", $preferredBatch);
                    if ( $inGroup != $current_group ) {

                        $current_group = $inGroup;

                        $new_batch = new Batch;

                        $new_batch->batch_route_id = $batch_route_id;
                        $batch = BatchRoute::with('stations_list')
                            ->find($batch_route_id);
                        $new_batch->creation_date = date('Y-m-d H:i:s', strtotime('now'));
                        $new_batch->change_date = date('Y-m-d H:i:s', strtotime('now'));

                        if ($batch_separate != 0) {
                            $new_batch->store_id = $store_id;
                        }

                        $new_batch->batch_number = sprintf("%s%s", $prefix, Batching::getLastBatchNumber() + 1);
                        $new_batch->station_id = $batch->stations_list[0]->station_id;
                        $new_batch->save();
                    }
                    #$batch_code = BatchRoute::find($batch_route_id)->batch_code;
                    #$proposedBatch = sprintf("%s~%s~%s", $today, $batch_code, $max_batch_id);

                    $acceptedGroups[ $inGroup ][] = [
                        $item_id,
                        $new_batch->batch_number,
                    ];

                    logger('item : '. $item_id . ' batch : ' . $new_batch->batch_number );
                }

                #return $acceptedGroups;

                foreach ( $acceptedGroups as $groups ) {
                    set_time_limit(0);

                    foreach ( $groups as $itemGroup ) {
                        $item_id = $itemGroup[0];
                        $batch_number = $itemGroup[1];

                        $item = Item::find($item_id);

                        if ($item->batch_number == 0) {
                            $item->batch_number = $batch_number;
                            // $item->item_taxable = auth()->user()->id;
                            //$item->item_order_status_2 = 2;
                            $item->save();

                            // Add note history by order id
                            $note = new Note();
                            $note->note_text = 'Batch ' . $batch_number . ' created on:' . $item->batch_creation_date .
                                ' for Item ' . $item->id . ' Child_SKU: ' . $item->child_sku;
                            $note->order_id = $item->order_id;
                            $note->order_5p = $item->order_5p;
                            if (auth()->user()) {
                                $note->user_id = auth()->user()->id;
                            } else {
                                $note->user_id = 87;
                            }
                            $note->save();

                            /* add order status to order table*/
                            // $order = Order::where('id', $item->order_5p)
                            // 					->first();
                            // if ( $order ) {
                            // 	$order->order_status = 4;
                            // 	$order->save();
                            // }
                        }
                    }

                    $mindate = Item::join('orders', 'items.order_5p', '=', 'orders.id')
                        ->where('items.batch_number', $batch_number)
                        ->where('items.is_deleted', '0')
                        ->selectRaw('MIN(orders.order_date) as min, count(items.id) as count')
                        ->first();

                    $item = Item::with('parameter_option')->where('batch_number', $batch_number)
                        ->where('is_deleted', '0')
                        ->first();

                    if ($mindate->count > 0) {

                        $date_batch = Batch::where('batch_number', $batch_number)
                            ->first();

                        $date_batch->min_order_date = $mindate->min;
                        $date_batch->status = $status;
                        $date_batch->save();

                        // TODO::old code deprecated
//                        if ($export_batch != null) {
//                            Batch::export($batch_number);
//                        }
//
//                        if (!empty($item) && !empty($item->parameter_option) && !$item->parameter_option->sure3d){
//                            logger('item id:' . $item->id . ' and batch : '. $batch_number .' is not empty or item->parameter_option->sure3d is false');
//                            Batch::export($batch_number);
//                        } else {
//                            logger('item id:' . $item->id . ' and batch : '. $batch_number .' is empty or item->parameter_option->sure3d is true');
//                        }


                    } else {

                        $new_batch->status = 'empty';
                        $new_batch->save();
                    }

                    $options = json_decode($item->item_option, true);

                    if(!empty($item) && !empty($item->parameter_option) && $item->parameter_option->sure3d && isset($options['Custom_EPS_download_link']) && is_object($item->order)) {
                        logger('item id:' . $item->id . ' and batch : '. $batch_number .' is empty or item->parameter_option->sure3d is true');
                        logger('will get the file on archive folder and rename it with batch number');
                        // Get the filename from the URL using pathinfo
                        $file_url = $options['Custom_EPS_download_link'];
                        $file_info = pathinfo($file_url);
                        $filename = $file_info['filename'];
                        $extension = $file_info['extension'];
                        $fileWithExt = $filename. '.' . $extension;

                        // Check if the filename contains $batch_number
                        if (strpos($filename, $batch_number) === false) {
                            // Rename the file
                            $new_filename = $batch_number . '.' . $extension;
                            logger('new file constructed : ' . $new_filename);

                            $old_file_dir = '/media/RDrive/archive/' . $filename . '.' . $extension;
                            $new_file_dir = '/media/RDrive/archive/' . $new_filename;
                            if($extension == 'pdf'){
                                logger('File is pdf :'. $file_url);
                            }
                            // Use shell_exec to copy and rename the file
                            $command = "cp \"$old_file_dir\" \"$new_file_dir\"";
                            shell_exec($command);

                            // Remove the old file
                            $remove_command = "rm \"$old_file_dir\"";
                            shell_exec($remove_command);

                            If (file_exists($old_file_dir)) {
                                unlink($old_file_dir);
                                Log::info('File removed successfully with name :'. $old_file_dir);

                            }
                            // Construct the new URL
                            $new_file_url = 'https://order.monogramonline.com/media/archive/' . $new_filename;


                            $options['Custom_EPS_download_link'] = $new_file_url;

                            $item->item_option = json_encode($options);
                            $item->batch_number = $batch_number;
                            $item->save();

                            logger('batched item : '. $item->id);

                            Log::info("File renamed and replaced successfully with name :". $new_file_url);
                            Log::info("File removed successfully with name :". $file_url);

                            $batch = Batch::where('batch_number', $batch_number)
                                ->first();
                            //TODO :: need to implement
//                            if(isset($batch->section) && $batch->section == 'Sublimation' && $batch_number){
                                logger('section is sublimation, section is :'. $batch->section->section_name. 'batch number is :'. $batch->batch_number);
                                logger('start updating the batch :'. $batch->id);
                                logger('For batch item id :'. $item->id);

                                $batch->status = 2;
                                $batch->section_id = 6;
                                $batch->prev_station_id = $new_batch->station_id;
                                $nextStationId = 92;


                                // move to next station
                                $index = -1;
                                foreach ($batch->route->stations_list as $key => $station) {
                                    if($station->station_id === 264) {
                                        $index = $key;
                                        break;
                                    }
                                }
                                if($index !== -1 && isset($batch->route->stations_list[$index])) {
                                    // Get the next station's ID
                                    $nextStationId = $batch->route->stations_list[$index]->station_id;
                                    logger("Next Station ID: $nextStationId");
                                } else {
                                    logger("Station with ID $batch->station_id not found or no next station and set default :(");
                                }

                                $batch->station_id = $nextStationId;
                                $batch->export_count = 1;
                                $batch->csv_found = 0;
                                $batch->graphic_found = 1;
                                $batch->to_printer = 0;
                                $batch->to_printer_date = null;
                                $batch->archived = 1;
                                $batch->save();

                                logger('batch updated successfully :'. $batch->id);

                                Batch::note($batch->batch_number, $batch->station_id, '111',
                                    'Graphic Uploaded to Main');
//                            }


                        } else {
                            Log::error("File already has the correct batch number." . $file_url);
                        }

//                    $link = $options['Custom_EPS_download_link'];
//                    $ext = pathinfo($link, PATHINFO_EXTENSION);
//                    $file = $item->order->short_order . '-' . $item->id . '.' . $ext;
//
//                    Log::info("ATTEMPTING TO FIX SURE3D IMAGE " . $file);
//
//                    $data = null;

//                   try {
//                       $data = @file_get_contents($link);
//                       @file_put_contents('/media/RDrive/Sure3d/' . $file, $data);
//
//                   } catch (\Exception $exception) {
//
//                    }
//                    $graphic = new GraphicsController();
//
//                    $filename = $batch_number . '.' . $ext;
//                    $filename = $graphic->uniqueFilename('/media/RDrive/' . "archive/", $filename);
//
//
//                    try {
//                        @file_put_contents('/media/RDrive/' . "archive/" . $filename, $data);
//                        @file_get_contents("http://order.monogramonline.com/lazy/upload-download/zakeke/" . $item->id . "?batch_number=" . $batch_number . "&item_id=" . $item->id . "&short_order=" . $item->order->short_order . "&fetch_link_from_zakeke_cli=true&item_index=0");
//
//                    } catch (\Exception $exception) {
//
//                    }
                    } else {
//                    Log::info("ATTEMPTING TO FIX SURE3D IMAGE ERROR, LINK DOES NOT EXIST WITHIN IT");
                        Log::info("Custom_EPS_download_link is not available");
                        logger('item id:' . $item->id . ' and batch : '. $batch_number .' is not empty or item->parameter_option->sure3d is false');
                        Batch::export($batch_number);
                    }

                }

                Batching::unlock();
                logger('Batching::unlocked and finished');

            } catch(\Exception $e) {
                logger('auto batching issue is occurred');
                logger([
                    'message' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                ]);
                return $e->getMessage();
            }
			
			return true;
		}
		
		public static function getLastBatchNumber()
		{
			$batch_number = Batch::where('is_deleted', '0')
											->whereRaw("batch_number NOT RLIKE '^[A-Z]'")
											 ->first([
												 DB::raw('MAX(CAST(batch_number as UNSIGNED)) as last_batch_number'),
											 ]);
			
			$bo_batch_number = Batch::where('is_deleted', '0')
											->whereRaw("batch_number RLIKE '^[A-Z]'")
											 ->first([
												 DB::raw('MAX(CAST(SUBSTRING(batch_number, 5) as UNSIGNED)) as last_batch_number'),
											 ]);

			// $fixed_value = 10000;
			// 		$max_batch_number = count($items) ? $items->first()->batch_number : $fixed_value;
			// 		$last_batch_number = $max_batch_number;
			if ($batch_number->last_batch_number > $bo_batch_number->last_batch_number) {
                Log::info('batch_number->last_batch_number ' . $batch_number->last_batch_number);
				return $batch_number->last_batch_number;
			} else {
                Log::info('batch_number->last_batch_number ' . $bo_batch_number->last_batch_number);
				return $bo_batch_number->last_batch_number;
			}
		}
}