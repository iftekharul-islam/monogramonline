<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Requests\InventoryOrderingRequest;
use App\Http\Controllers\Controller;
use App\Inventory;
use App\InventoryUnit;
use App\InventoryAdjustment;
use App\PurchasedInvProducts;
use App\PurchaseProduct;
use Illuminate\Support\Facades\Log;
use App\Item;
use App\Station;
use App\Batch;
use App\Section;
use App\Vendor;
use App\Option;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
	public static $search_in = [
		'stock_no_unique'        => 'Stock Number',
		'stock_name_discription' => 'Description',
		'child_sku'              => 'Child SKU',
		'wh_bin'                 => 'Bin',
		'qty_on_hand'            => 'Quantity on Hand',
		'last_cost'              => 'Last Cost',
		'value'                  => 'Total Value',
		'total_sale'             => 'Total Sales',
		'sales_30'               => '30 Days of Sales',
		'qty_av'                 => 'Quantity Available',
		// 'until_reorder'          => 'Need to Reorder'
	];
	
	public function index (Request $request)
	{
		
		if (!$request->has('sort_by') || $request->get('sort_by') == null) {
			$sort_by = 'inventories.stock_no_unique';
		} else {
			$sort_by = $request->get('sort_by');
		}
		
		$total = Inventory::where('inventories.is_deleted', 0)
								->searchSection($request->get('section_ids'))
								->searchVendor($request->get('vendor_id'))
								->searchCriteria($request->get('search_for_first'), $request->get('search_in_first'), $request->get('operator_first'))
								->searchCriteria($request->get('search_for_second'), $request->get('search_in_second'), $request->get('operator_second'))
								->searchCriteria($request->get('search_for_third'), $request->get('search_in_third'), $request->get('operator_third'))
								->searchCriteria($request->get('search_for_fourth'), $request->get('search_in_fourth'), $request->get('operator_fourth'))
								->selectRaw('SUM(CASE WHEN qty_on_hand > 0 THEN qty_on_hand * last_cost ELSE 0 END ) as cost')
								->first();
								
		$inventories = Inventory::with('qty_user', 'section', 'last_product.vendor', 'purchase_products')
								->where('inventories.is_deleted', 0)
								->searchSection($request->get('section_ids'))
								->searchVendor($request->get('vendor_id'))
								->searchCriteria($request->get('search_for_first'), $request->get('search_in_first'), $request->get('operator_first'))
								->searchCriteria($request->get('search_for_second'), $request->get('search_in_second'), $request->get('operator_second'))
								->searchCriteria($request->get('search_for_third'), $request->get('search_in_third'), $request->get('operator_third'))
								->searchCriteria($request->get('search_for_fourth'), $request->get('search_in_fourth'), $request->get('operator_fourth'))
								->orderBy( $sort_by , $request->get('sorted') ?? 'ASC' )
								->groupBy('inventories.stock_no_unique')
								->paginate(100);
								// ->toSql(); dd($inventories);
		
		$operators = [ 	'in' => 'In', 
										'not_in' => 'Not In', 
										'starts_with' => 'Starts With', 
										'ends_with' => 'Ends With', 
										'equals' => 'Equals', 
										'not_equals' => 'Not Equal',
										'less_than' => 'Less Than', 
										'greater_than' => 'Greater Than', 
										'blank' => 'Is Blank',
										'not_blank' => 'Is Not Blank'   
									];
		
			$sorting = [ 		'inventories.stock_no_unique' => 'Stock Number', 
											'inventories.until_reorder' => 'Need to Reorder',
											'inventories.stock_name_discription' => 'Description', 
											'inventories.wh_bin' => 'Bin', 
											'inventories.qty_on_hand' => 'Quantity on Hand', 
											'inventories.total_sale' => 'Total Sales', 
											'inventories.sales_30' => '30 Days of Sales',
											'inventories.sales_90' => '90 Days of Sales',
											'inventories.last_cost' => 'Last Cost',
											'inventories.value' => 'Total Value',
										];
			
			$sections = Section::where('is_deleted', '0')
														->get()
														->pluck('section_name', 'id')
														->prepend('Unassigned', 'blank');
			
			$vendors = Vendor::where('is_deleted', '0')
														->get()
														->pluck('vendor_name', 'id')
														->prepend('Is Blank', 'blank')
														->prepend('Select a Vendor', '');
																									
		return view('inventories.index', compact('inventories', 'request'))
			->with('inventories', $inventories)
			->with('search_in', static::$search_in)
			->with('operators', $operators)
			->with('sorting', $sorting)
			->with('sections', $sections)
			->with('vendors', $vendors)
			->with('total', $total);
			// ->with('inventory_indexes', $this->inventory_indexes);
	}

	public function updateInventory (Request $request)
	{
		$inventory = Inventory::find($request->get('id'));
		
		if (!$inventory) {
			return 'Stock # not found';
		}
		
		if ($request->has('field')) {
			$field = $request->get('field');
		} else {
			return 'No Field Provided';
		}
		
		$update_flag = 0;
		
		if ($field == 'qty_on_hand' && $inventory->qty_on_hand != $request->get('value')) {
			
				$result = InventoryAdjustment::adjustInventory(2, $inventory->stock_no_unique, intval($request->get('value')));
				
				if ($result != 0) {
					$update_flag = 1;
				} else {
					return 'Not Updated';
				}
		
		} else if ($field == 'wh_bin' && $inventory->wh_bin != $request->get('value')) {
			
			$inventory->wh_bin = $request->get('value');
			$update_flag = 1;
		}
		
		if ($update_flag == 1) {
			
			$inventory->user_id = auth()->user()->id;
			$inventory->save();
			
			return 'Updated';
		}
		
		return 'Nothing to Update';
	}

	public function delete (Request $request)
	{
		
		if (auth()->user()->accesses->where('page', 'inventory_admin')->all()) {
			Inventory::where('id', $request->get('id'))->delete();

			return redirect()
				->back()
				->with('success', "Success Deleted ID#" . $request->get('id'));
		} else {
			return redirect()
				->back()
				->withErrors('You do not have permission to delete inventory items.');
		}
	}

	public function create ($id = null)
	{
		
		$sections = Section::where('is_deleted', '0')
													->get()
													->pluck('section_name', 'id')
													->prepend('Select a Section', '');
													
		if ($id != null) {
			
			$inventory = Inventory::where('is_deleted', '0')->find($id);
			
			if ( ! $inventory ) {
				return redirect()->back()->withErrors("No Record Found");
			}
			return view('inventories.create', compact('inventory', 'sections'));
			
		} else {
			return view('inventories.create', compact('sections'));
		}
	}

	public function store (Request $request)
	{
		//
		$inventoryTbl = new Inventory();
		if ($request->stock_no_unique == null) {
			$inventoryTbl->stock_no_unique = $this->generateStockNoUnique();
		} else {
			$dup = Inventory::where('stock_no_unique', $request->stock_no_unique)->first();
			if (!$dup) {
				$inventoryTbl->stock_no_unique =  $request->stock_no_unique;
			} else {
				return redirect()->back()->withInput()->withErrors('Stock number already in use');
			}
		}
		$inventoryTbl->qty_on_hand = 0;
		$inventoryTbl->stock_name_discription = $request->stock_name_discription;
		$inventoryTbl->sku_weight = $request->sku_weight;
		$inventoryTbl->re_order_qty = $request->re_order_qty;
		$inventoryTbl->min_reorder = $request->min_reorder;
		$inventoryTbl->upc = $request->upc;
		$inventoryTbl->wh_bin = $request->wh_bin;
		$inventoryTbl->warehouse = $request->warehouse;
		$inventoryTbl->section_id = $request->section_id;
		$inventoryTbl->last_cost = $request->last_cost;
		$inventoryTbl->save();

		return redirect()->to("/inventories?search_for_first=" . $inventoryTbl->stock_no_unique . "&search_in_first=stock_no_unique&search_for_second=&search_in_second=stock_no_unique");

	}
	
	private function generateStockNoUnique ()
	{
		$stockNoUnique = Inventory::orderBy('id', 'desc')->first();

		return sprintf("1%05d", ( $stockNoUnique->id + 1 ));
	}
		
	public function show ($id)
	{
		//

	}

	public function edit ($id)
	{
		//
		$sections = Section::where('is_deleted', '0')
													->get()
													->pluck('section_name', 'id')
													->prepend('Select a Section', '');
													
		$inventory = Inventory::where('is_deleted', 0)->find($id);

		if ( ! $inventory ) {
			return redirect()->back()->withErrors("No Record Found");
		}

        /*
         * Create a configuration file for them when they edit them
         */
        $file = "/var/www/5p_oms/Inventories.json";
        $template = [
            "DROPSHIP" => false,
            "DROPSHIP_SKU" => "",
            "DROPSHIP_COST" => 0
        ];
        $finalData = [];

        if(!file_exists($file)) {
            file_put_contents($file, json_encode(
                [
                    $id => $template
                ], JSON_PRETTY_PRINT
            ));
            $finalData = $template;
        } else {
            $data = json_decode(file_get_contents($file), true);

            if(!isset($data[$id])) {
                $data[$id] = $template;

                file_put_contents($file, json_encode(
                    $data, JSON_PRETTY_PRINT
                ));
                $finalData = $data[$id];
            } else {
                $finalData = $data[$id];
            }
        }

        $dropship = $finalData['DROPSHIP'];
        $dropshipSKU = $finalData['DROPSHIP_SKU'];
        $dropshipCost = $finalData['DROPSHIP_COST'];

		return view('inventories.edit', compact('inventory', 'sections', 'dropshipSKU', 'dropshipCost', 'dropship'));
	}

	public function update (Request $request, $id)
	{
		// dd($request->all(), $id);
		$inventoryTbl = Inventory::find($id);
		if (!$inventoryTbl) {
			return redirect()->back()->withInput()->withErrors('Inventory Item not found');
		}
		$inventoryTbl->stock_name_discription = $request->stock_name_discription;
		$inventoryTbl->sku_weight = $request->sku_weight;
		$inventoryTbl->re_order_qty = $request->re_order_qty;
		$inventoryTbl->min_reorder = $request->min_reorder;
		$inventoryTbl->upc = $request->upc;
		$inventoryTbl->wh_bin = $request->wh_bin;
		$inventoryTbl->warehouse = $request->warehouse;
		$inventoryTbl->section_id = $request->section_id;
		$inventoryTbl->last_cost = $request->last_cost;
		$inventoryTbl->save();

        $file = "/var/www/5p_oms/Inventories.json";
        $data = json_decode(file_get_contents($file), true);

        $data[$id]['DROPSHIP_COST'] = $request->dropship_cost;
        $data[$id]['DROPSHIP_SKU'] = $request->dropship_sku;
        $data[$id]['DROPSHIP'] = $request->dropship;

        file_put_contents($file, json_encode(
            $data, JSON_PRETTY_PRINT
        ));


		return redirect()->to("/inventories?search_for_first=" . $inventoryTbl->stock_no_unique . "&search_in_first=stock_no_unique&search_for_second=&search_in_second=stock_no_unique");
	}

	public function destroy ($id)
	{
		//
	}

	public function getStockNoUnique (Request $request)
	{
		$inventory = Inventory::where('stock_no_unique', $request->data)
							  ->first();
		
// 		$purchasedInvProducts = PurchasedInvProducts::where('stock_no', $request->data)
// 													->first();

		// first returns a single row, checking existence of the row does the work
		if ( ( ! $inventory && ! $purchasedInvProducts ) /*|| ($inventory->count() <= 0) && ( $purchasedInvProducts->count() <= 0) */ ) {
			/**  Return Null Fields because not found **/
			return response()->json([
				'stock_name_discription' => '',
				'sku_weight'             => '',
				're_order_qty'           => '',
				'min_reorder'            => '',

// 				'unit'            => '',
// 				'unit_price'      => '',
// 				'vendor_id'       => '',
// 				'vendor_sku'      => '',
// 				'vendor_sku_name' => '',
// 				'lead_time_days'  => '',
			]);
		} else {
			/**  Return Null Fields because  found **/
			return response()->json([
				'stock_name_discription' => $inventory->stock_name_discription,
				'sku_weight'             => $inventory->sku_weight,
				're_order_qty'           => $inventory->re_order_qty,
				'min_reorder'            => $inventory->min_reorder,

// 				'unit'            => $purchasedInvProducts->unit,
// 				'unit_price'      => $purchasedInvProducts->unit_price,
// 				'vendor_id'       => $purchasedInvProducts->vendor_id,
// 				'vendor_sku'      => $purchasedInvProducts->vendor_sku,
// 				'vendor_sku_name' => $purchasedInvProducts->vendor_sku_name,
// 				'lead_time_days'  => $purchasedInvProducts->lead_time_days,
			]);
		}

	}

	public function updateStock () 
	{
			$child_skus = Option::leftJoin('inventory_unit', 'inventory_unit.child_sku', '=', 'parameter_options.child_sku')
										->whereNull('inventory_unit.child_sku')
										->select('parameter_options.child_sku')
										->get();
			
			foreach ($child_skus as $child_sku) {
				
				$unit = new InventoryUnit;
				$unit->child_sku = $child_sku->child_sku;
				$unit->unit_qty = 1;
				$unit->stock_no_unique = 'ToBeAssigned';
				$unit->save();
			}
			
			$inventoryTbl = Inventory::where('is_deleted', '0')->get();
			
			if ( ! $inventoryTbl ) {
				Log::info('updateStockTable: Stock # Query Failed.');
				return false;
			}
			
			$last30 = date("Y-m-d 00:00:00", strtotime('-30 days'));
			$last90 = date("Y-m-d 00:00:00", strtotime('-90 days'));
			$last180 = date("Y-m-d 00:00:00", strtotime('-180 days'));
													
			$items = Item::leftJoin('inventory_unit', 'inventory_unit.child_sku', '=', 'items.child_sku')
								->where('items.is_deleted', '=', '0')
								->selectRaw(
										'inventory_unit.stock_no_unique,
										 SUM(CASE WHEN items.item_status  NOT IN (5,6) THEN inventory_unit.unit_qty * items.item_quantity ELSE 0 END ) as Sales,
										 SUM(CASE WHEN items.item_status  NOT IN (5,6) AND items.created_at > "' . $last30 . 
										 			'" THEN inventory_unit.unit_qty * items.item_quantity ELSE 0 END ) as Sales_30,
										 SUM(CASE WHEN items.item_status  NOT IN (5,6) AND items.created_at > "' . $last90 . 
												 '" THEN inventory_unit.unit_qty * items.item_quantity ELSE 0 END ) as Sales_90,
										 SUM(CASE WHEN items.item_status  NOT IN (5,6) AND items.created_at > "' . $last180 . 
 												'" THEN inventory_unit.unit_qty * items.item_quantity ELSE 0 END ) as Sales_180,
										 SUM(CASE WHEN items.item_status IN (1,3,4,7,9) THEN inventory_unit.unit_qty * items.item_quantity ELSE 0 END ) as Allocated'
								)
								->groupBy('inventory_unit.stock_no_unique')
								->get();
			
			$purchases = PurchaseProduct::where('is_deleted', '0')
											->selectRaw(
												'stock_no,
												 SUM(CASE WHEN purchased_products.receive_quantity > 0 THEN purchased_products.receive_quantity ELSE 0 END ) as Purchases,
												 SUM(balance_quantity) as Expected'
												)
											->groupBy('stock_no')
											->get();
			
			// $inv_products = PurchasedInvProducts::latest()->get();
			
			foreach ($inventoryTbl as $stock) {
					
					$itemQuantity = $items->where('stock_no_unique', $stock->stock_no_unique)->first();
					$purchaseQuantity = $purchases->where('stock_no', $stock->stock_no_unique)->first();
					
					if ($itemQuantity && $itemQuantity->stock_no_unique != NULL){
						
						$stock->total_sale = $itemQuantity->Sales;
						$stock->qty_alloc = $itemQuantity->Allocated;
						$stock->sales_30 = $itemQuantity->Sales_30;
						$stock->sales_90 = $itemQuantity->Sales_90;
						$stock->sales_180 = $itemQuantity->Sales_180;
						
					}else{
						$stock->total_sale = 0;
						$stock->qty_alloc = 0;
						$stock->sales_30 = 0;
						$stock->sales_90 = 0;
						$stock->sales_180 = 0;
					}
					
					if ($purchaseQuantity && $purchaseQuantity->stock_no != NULL){
						$stock->total_purchase = $purchaseQuantity->Purchases;
						$stock->qty_exp = $purchaseQuantity->Expected;
					}else{
						$stock->total_purchase = 0;
						$stock->qty_exp = 0;
					}
					
					$stock->qty_av = $stock->qty_on_hand - $stock->qty_alloc;
					$stock->until_reorder = $stock->qty_av - $stock->min_reorder;
					$stock->save();
			}
			
			return 'success';			
	}
	
	public function calculateOrdering (InventoryOrderingRequest $request) 
	{
		$divisor = $request->get('divisor');
		$start = $request->get('start_date');
		$end = $request->get('end_date');
		
		$items = Item::leftJoin('inventory_unit', 'inventory_unit.child_sku', '=', 'items.child_sku')
							->where('items.is_deleted', '=', '0')
							->selectRaw(
									'inventory_unit.stock_no_unique,
									 SUM(CASE WHEN items.item_status  NOT IN (5,6) AND ' .
									 			' items.created_at > "' . $start . ' 00:00:00" AND items.created_at < "' . $end .
												' 23:59:59" THEN inventory_unit.unit_qty * items.item_quantity ELSE 0 END ) as total'
							)
							->groupBy('inventory_unit.stock_no_unique')
							->get();
		
		if ( ! $items ) {
			Log::info('calculateOrdering:  Sales Query Failed.');
			return false;
		}
							
		$inventoryTbl = Inventory::where('is_deleted', '0')->get();
		
		if ( ! $inventoryTbl ) {
			Log::info('calculateOrdering: Stock # Query Failed.');
			return false;
		}
		
		foreach ($inventoryTbl as $stock) {
				
				// switch ($request->get('interval')) {
				// 	case 'sales_30':
				// 		$total = $stock->sales_30;
				// 		break;
				// 	case 'sales_90':
				// 		$total = $stock->sales_90;
				// 		break;
				// 	case 'sales_180':
				// 		$total = $stock->sales_180;
				// 		break;
				// }
				
				$sales = $items->where('stock_no_unique', $stock->stock_no_unique)->first();
				
				if ($sales) {
					$total = $sales->total / $divisor;
					
					if ($total < 5) {
						$stock->min_reorder = 0;
					} else if ($total < 10) {
						$stock->min_reorder = round( (($total - 1) / 10) + .5 ) * 10;
					} else if ($total < 50) {
						$stock->min_reorder = round($total / 10) * 10;
					} else if ($total >= 50) {
						$stock->min_reorder = round($total / 50) * 50;
					}
				} else {
					$stock->min_reorder = 0;
				}
				
				$stock->save();

		}
		
		return redirect()->action('InventoryController@index')->withSuccess('Inventory Ordering Quantities Updated');
	}
	
	public function getStockReport (Request $request) {
		
		$item = Batch::join('items', 'batches.batch_number', '=', 'items.batch_number')
					->join('stations', 'batches.station_id', '=', 'stations.id')
					->join('inventory_unit', 'items.child_sku', '=', 'inventory_unit.child_sku')
					->join('inventories', 'inventory_unit.stock_no_unique', '=', 'inventories.stock_no_unique')
					->selectRaw('stations.station_name, stations.station_description, inventories.id as inv_id, inventories.stock_no_unique,  
										inventories.wh_bin, inventories.stock_name_discription, inventories.warehouse, 
										items.item_thumb, items.item_code, SUM(items.item_quantity) as total')
					->searchStatus('active')
					->searchStation($request->get('station_id'))
					->groupBy('station_id', \DB::raw('inventories.stock_no_unique'))
					->orderBy('station_id', 'ASC')
					->orderBy('wh_bin', 'ASC')
					->get();
		
		$station_option = Station::selectRaw('CONCAT(stations.station_name, " - ", stations.station_description) AS full_station, stations.id' )
									->where('is_deleted', 0)
									->orderBy('stations.station_name', 'ASC')
									->get()
									->pluck('full_station', 'id')
									->prepend('Select a Station', 0);
										
		return view ( 'inventories.stockreport', compact ( 'item', 'station_option' ))->withRequest($request);
	}

	
	// public function bulkUpdate ()
	// {
	// 	$stock_nos = ['19320',
	// 								'12760',
	// 								'23450',
	// 								'102080',
	// 								'13870',
	// 								'14010',
	// 								'16220',
	// 								'12470',
	// 								'12490',
	// 								'102269',
	// 								'102213',
	// 								'10840',
	// 								'25020',
	// 								'11320',
	// 								'102037',
	// 								'27340',
	// 								'24860',
	// 								'102332',
	// 								'10680',
	// 								'25160',
	// 								'10910',
	// 								'102263',
	// 								'102414',
	// 								'25260',
	// 								'11470',
	// 								'26670',
	// 								'20070',
	// 								'13680',
	// 								'21230',
	// 								'102088',
	// 								'102419',
	// 								'102404',
	// 								'102412',
	// 								'102247'];

		// $row = 1;
		// if (($handle = fopen("/home/jennifer/inventory_counts_0724.csv", "r")) !== FALSE) {
		//     while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
		// 			$stock_no = $data[0];        
		// 			$qty = $data[1];
		// 			
		// 			if (!strpos($qty, '-')) {
		
		// foreach ($stock_nos as $stock_no) {
    // 
		// 				$qty = 0;
    // 
		// 				$purchaseQuantity = \DB::table('purchased_products')
		// 										->where('stock_no', $stock_no)
		// 										->selectRaw('SUM(quantity) as quantity, SUM(receive_quantity) as receive_quantity') 
		// 			              ->groupBy('stock_no') 
		// 			              ->first(); 
    // 
		// 				if (!empty($purchaseQuantity)) { 
		// 			    $purchased = $purchaseQuantity->quantity; 
		// 			    $received = $purchaseQuantity->receive_quantity; 
		// 			  } else { 
		// 			    $purchased = 0; 
		// 			    $received = 0; 
		// 			  } 
    // 
    // 
		// 				$saleQuantity = \DB::table('inventory_unit')
		// 									->leftJoin('items', 'inventory_unit.child_sku', '=', 'items.child_sku')
		// 									->leftJoin('orders', 'items.order_5p', '=', 'orders.id')
		// 									->where('inventory_unit.stock_no_unique', $stock_no)
		// 									->where('items.is_deleted', '=', '0')
		// 									->where('orders.is_deleted', '=', '0')
		// 									->whereIn('orders.order_status', [6,10])
		// 									->select([
		// 											'items.item_quantity',
		// 											'inventory_unit.unit_qty',
		// 									])
		// 									->select(\DB::raw('sum(items.item_quantity * inventory_unit.unit_qty) AS saleQuantity'))
		// 									->get();
    // 
		// 				if ($saleQuantity[0]->saleQuantity != null){
		// 					$sold = $saleQuantity[0]->saleQuantity;
		// 				}else{
		// 					$sold = 0;
		// 				}
    // 
		// 				$adjustQuantity = InventoryAdjustment::where('stock_no_unique', $stock_no)
		// 									->where('is_deleted', '=', '0')
		// 									->select(\DB::raw('sum(quantity) AS adjustQuantity'))
		// 									->get();
    // 
		// 				if ($adjustQuantity[0]->adjustQuantity != null){
		// 					$adjustment_old = $adjustQuantity[0]->adjustQuantity;
		// 				}else{
		// 					$adjustment_old = 0;
		// 				}
    // 
		// 				$new_adjustment = $qty - ($received  - $sold  + $adjustment_old);
    // 
		// 				if ($new_adjustment != 0) {
		// 					$adjustment = new InventoryAdjustment;
		// 					$adjustment->stock_no_unique = $stock_no;
		// 					$adjustment->type = 1;
		// 					$adjustment->quantity = $new_adjustment;
		// 					$adjustment->user_id = 83;
		// 					$adjustment->note = 'Inventory Counted';
		// 					$adjustment->save();
		// 				}
    // 
						// $soldSinceQuantity = \DB::table('inventory_unit')
						// 					->leftJoin('items', 'inventory_unit.child_sku', '=', 'items.child_sku')
						// 					->leftJoin('orders', 'items.order_5p', '=', 'orders.id')
						// 					->where('inventory_unit.stock_no_unique', $stock_no)
						// 					->where('items.is_deleted', '=', '0')
						// 					->where('orders.is_deleted', '=', '0')
						// 					->whereIn('orders.order_status', [6,10])
						// 					->where('items.updated_at', '>', '2017-07-24 00:00:00')
						// 					->select([
						// 							'items.item_quantity',
						// 							'inventory_unit.unit_qty',
						// 					])
						// 					->select(\DB::raw('sum(items.item_quantity * inventory_unit.unit_qty) AS saleQuantity'))
						// 					->get();
						// 
						// if ($soldSinceQuantity[0]->saleQuantity != null){
						// 	$sold = $soldSinceQuantity[0]->saleQuantity;
						// }else{
						// 	$sold = 0;
						// }
						// 
						// $adjustQuantity = InventoryAdjustment::where('stock_no_unique', $stock_no)
						// 					->where('is_deleted', '=', '0')
						// 					->where('type', '2')
						// 					->where('updated_at', '>', '2017-07-24 00:00:00')
						// 					->select(\DB::raw('sum(quantity) AS adjustQuantity'))
						// 					->get();
						// 
						// if ($adjustQuantity[0]->adjustQuantity != null){
						// 	$adjustment_old = $adjustQuantity[0]->adjustQuantity;
						// }else{
						// 	$adjustment_old = 0;
						// }
											
						
		 				// Inventory::addInventoryByStockNumber($stock_no);
            // 
		 				// $inventory = Inventory::where('stock_no_unique', $stock_no)->first();
            // 
		 				// $diff = $qty - $inventory->qty_on_hand;
            // 
						// echo "$stock_no, $qty, $inventory->qty_on_hand, $diff, $sold, $adjustment_old <br>";
		// 			}
		//     }
		// 		
		//     fclose($handle);
		 // }
		// 
		// 
		// echo 'done';
				
		// foreach($stock_nos as $stock_no => $qty) {
		// 	
		// 	$inventory = Inventory::where('stock_no_unique', $stock_no)->count();
		// 	
		// 	if ($inventory == 0) {
		// 		echo  $stock_no . '<br>';
		// 	}
		// }
		// 
		// foreach($stock_nos as $stock_no => $qty) {
		// 	
		// 	//Inventory::addInventoryByStockNumber($stock_no->stock_no_unique, null); 
		// 	
		// 	$purchaseQuantity = \DB::table('purchased_products')
		// 							->where('stock_no', $stock_no)
		// 							->selectRaw('SUM(quantity) as quantity, SUM(receive_quantity) as receive_quantity') 
	  //               ->groupBy('stock_no') 
	  //               ->first(); 
		// 	
		// 	if (!empty($purchaseQuantity)) { 
	  //     $purchased = $purchaseQuantity->quantity; 
	  //     $received = $purchaseQuantity->receive_quantity; 
	  //   } else { 
	  //     $purchased = 0; 
	  //     $received = 0; 
	  //   } 
		// 
		// 	
		// 	$saleQuantity = \DB::table('inventory_unit')
		// 						->leftJoin('items', 'inventory_unit.child_sku', '=', 'items.child_sku')
		// 						->leftJoin('orders', 'items.order_5p', '=', 'orders.id')
		// 						->where('inventory_unit.stock_no_unique', $stock_no)
		// 						->where('items.is_deleted', '=', '0')
		// 						->where('orders.is_deleted', '=', '0')
		// 						->whereIn('orders.order_status', [6,10])
		// 						->select([
		// 								'items.item_quantity',
		// 								'inventory_unit.unit_qty',
		// 						])
		// 						->select(\DB::raw('sum(items.item_quantity * inventory_unit.unit_qty) AS saleQuantity'))
		// 						->get();
		// 	
		// 	if ($saleQuantity[0]->saleQuantity != null){
		// 		$sold = $saleQuantity[0]->saleQuantity;
		// 	}else{
		// 		$sold = 0;
		// 	}
		// 
		// 	$adjustQuantity = InventoryAdjustment::where('stock_no_unique', $stock_no)
		// 						->where('is_deleted', '=', '0')
		// 						->select(\DB::raw('sum(quantity) AS adjustQuantity'))
		// 						->get();
		// 	
		// 	if ($adjustQuantity[0]->adjustQuantity != null){
		// 		$adjustment_old = $adjustQuantity[0]->adjustQuantity;
		// 	}else{
		// 		$adjustment_old = 0;
		// 	}
		// 
		// 	$new_adjustment = ($qty + $sold) - ($received + $adjustment_old);
		// 	
		// 	if ($new_adjustment != 0) {
		// 		$adjustment = new InventoryAdjustment;
		// 		$adjustment->stock_no_unique = $stock_no;
		// 		$adjustment->type = 1;
		// 		$adjustment->quantity = $new_adjustment;
		// 		$adjustment->user_id = 83;
		// 		$adjustment->note = 'Inventory Counted';
		// 		$adjustment->save();
		// 	}
		// 	
		// 	Inventory::addInventoryByStockNumber($stock_no);
		// 	
		// 	$inventory = Inventory::where('stock_no_unique', $stock_no)->first();
		// 	
		// 	echo "$stock_no, $qty, $inventory->qty_on_hand<br>";
		// }
		
		// $stock_nos = Inventory::all();
    // 
		// foreach($stock_nos as $stock_no) {
		// 	Inventory::addInventoryByStockNumber($stock_no->stock_no_unique);
		// }
		
		// $missing_nos = Inventory::leftjoin('inventory_adjustments', 'inventory_adjustments.stock_no_unique', '=', 'inventories.stock_no_unique')
		// 								->whereNull('inventory_adjustments.stock_no_unique')
		// 								->select('inventories.stock_no_unique', 'inventories.stock_name_discription')
		// 								->get();
		// 
		// $missing_nos = Inventory::all();
		// 
		// foreach ($missing_nos as $stock_no) {
		// 
		// 		$stockNumber = $stock_no->stock_no_unique;
		// 		
		// 		$saleQuantity = \DB::table('inventory_unit')
		// 						->leftJoin('items', 'inventory_unit.child_sku', '=', 'items.child_sku')
		// 						->leftJoin('orders', 'items.order_5p', '=', 'orders.id')
		// 						->where('inventory_unit.stock_no_unique', $stockNumber)
		// 						->where('items.is_deleted', '=', '0')
		// 						->where('orders.is_deleted', '=', '0')
		// 						->whereIn('orders.order_status', [6,10])
		// 						->where('orders.order_date', '>', '2016-12-19 00:00:00')
		// 						->select([
		// 								'items.item_quantity',
		// 								'inventory_unit.unit_qty',
		// 						])
		// 						->select(\DB::raw('sum(items.item_quantity * inventory_unit.unit_qty) AS saleQuantity'))
		// 						->get();
		// 		
		// 		if ($saleQuantity[0]->saleQuantity != null){
		// 			$sold = $saleQuantity[0]->saleQuantity;
		// 		}else{
		// 			$sold = 0;
		// 		}
		// 
		// 			$purchaseQuantity = \DB::table('purchased_products')
		// 									->where('stock_no', $stockNumber)
		// 									->where('created_at', '>', '2016-12-19 00:00:00')
		// 									->selectRaw('SUM(quantity) as quantity, SUM(receive_quantity) as receive_quantity') 
		// 	                ->groupBy('stock_no') 
		// 	                ->first(); 
		// 	     
		// 	    if (!empty($purchaseQuantity)) { 
		// 	      $purchased = $purchaseQuantity->quantity; 
		// 	      $received = $purchaseQuantity->receive_quantity; 
		// 	    } else { 
		// 	      $purchased = 0; 
		// 	      $received = 0; 
		// 	    } 
		// 			
		// 			$qty_alloc = \DB::table('inventory_unit')
		// 								->leftJoin('items', 'inventory_unit.child_sku', '=', 'items.child_sku')
		// 								->leftJoin('orders', 'items.order_5p', '=', 'orders.id')
		// 								->where('inventory_unit.stock_no_unique', $stockNumber)
		// 								->where('items.is_deleted', '=', '0')
		// 								->where('orders.is_deleted', '=', '0')
		// 								->whereIn('orders.order_status', [4,11,13,15,17,23])
		// 								->select([
		// 										'items.item_quantity',
		// 										'inventory_unit.unit_qty',
		// 								])
		// 								->select(\DB::raw('sum(items.item_quantity * inventory_unit.unit_qty) AS qty_alloc'))
		// 
		// 								->get();
		// 			// dd($stockNumber, $purchaseQuantity, $receiveQuantity, $saleQuantity[0]->saleQuantity, $qty_alloc[0]->qty_alloc);		
		// 			
		// 			
		// 			if ($qty_alloc[0]->qty_alloc != null){
		// 				$qty_alloc = $qty_alloc[0]->qty_alloc;
		// 			}else{
		// 				$qty_alloc = 0;
		// 			}
		// 			
		// 			$adjustment = InventoryAdjustment::where('stock_no_unique', $stockNumber)->where('type', 1)->count();
		// 			
		// 			if ($adjustment == 0) {
		// 				$adjustment = '';
		// 			} elseif ($adjustment == 1) {
		// 				$adjustment = 'YES';
		// 			} 
		// 			
		// 			$desc = str_replace('"', '', $stock_no->stock_name_discription);
		// 			$desc = str_replace(',', '', $desc);
		// 			
		// 			echo "$stockNumber, $desc, $sold, $received, $qty_alloc, $adjustment <br>";
		// }
		// 	
		// echo 'done';
		
		// private $inventory_indexes = [
		// 	"All",
		// 	"Local WH",
		// 	"0-Expedited Orders ****** (7305)",
		// 	"0-TO BE ASSIGNED TO A VENDOR (6328)",
		// 	"0.0-MONOGRAM ORDERS (6271)",
		// 	"1.0-GRAPHICS RUSH RED LASER*** (7038)",
		// 	"1.0-GRAPHICS RUSH SUBMILATION *** (6784)",
		// 	"1.0-GRAPHICS RUSH-GENERAL *** (6222)",
		// 	"1.0-GRAPHICS RUSH-MONO *** (6255)",
		// 	"1.0-GRAPHICS RUSH-NP *** (6258)",
		// 	"1.0-JUAN REDO (6750)",
		// 	"1.0-JUAN REPAIR (6749)",
		// 	"1.1-APRON GRAPHICS (7282)",
		// 	"3-CREATE BATCH (7835)",
		// 	"3-GINA-GENERAL (6246)",
		// 	"3-PATRICIO (6253)",
		// 	"3-PATRICIO-SOLID GOLD (6254)",
		// 	"4-American Personalized (7499)",
		// 	"4-Baby Aspen (7504)",
		// 	"4-Clay Design (7698)",
		// 	"4-EMBROIDERY (7471)",
		// 	"4-FRECKLE BOX (7465)",
		// 	"4-Gift Basket Drop Shipping (7695)",
		// 	"4-JDS Marketing (6329)",
		// 	"4-Pro Gift Source (7431)",
		// 	"4-Teals Prairie & Co.  (7694)",
		// 	"5-GAVE TO JESSICA (7935)",
		// 	"5-NEED CUSTOMER SERVICE  (6360)",
		// 	"5-ORDER UPDATE (6273)",
		// 	"5-REPAIRS (6398)",
		// 	"5-WAITING FOR ANOTHER PC (6377)",
		// 	"* 5-WAITING FOR ANOTHER PIECE 2 (7857)",
		// 	"5-WAITING FOR INVENTORY (7799)",
		// 	"6-READY TO SHIP (6376)",
		// 	"7-Gift Boxes/Cleaner (6335)",
		// ];
		
		// <div class = "row">
		// 	<div class = "col-md-7">
		// 		<div class = "row">
		// 			<div class = "col-md-12">
		// 				{!! Form::open(['url' => url('imports/inventory'), 'files' => true]) !!}
		// 				{!! Form::hidden('todo', null, ['id' => 'todo']) !!}
		// 				<legend>Import inventory file</legend>
		// 				<div class = "form-group" style="display:none;">
		// 					{!! Form::select('inventory_index', $inventory_indexes, 0, ['id' => 'inventory_index', 'class' => 'form-control']) !!}
		// 				</div>
		// 				<div class = "form-group">
		// 					{!! Form::file('attached_csv', ['id' => 'attached_csv', 'class' => 'form-control', 'accept' => '.csv']) !!}
		// 				</div>
		// 				<div class = "form-group">
		// 					{!! Form::button('Validate file', ['class' => 'btn btn-sm btn-default', 'id' => 'validate-file']) !!}
		// 					{!! Form::button('Upload file', ['class' => 'btn btn-sm btn-success', 'id' => 'upload-file']) !!}
		// 					&nbsp;<a class = "btn btn-success btn-sm pull-left" href = "{{url(sprintf('%s','inventories/create'))}}" target = "_blank">Create New Stock</a>&nbsp;
		// 				</div>
		// 				{!! Form::close() !!}
		// 			</div>
		// 		</div>
		// 	</div>
		// 	<div class = "col-md-5">
		// 		<div class = "row">
		// 			<div class = "col-md-12">
		// 				{!! Form::open(['url' => url('exports/inventory'), 'method' => 'get']) !!}
		// 				<legend>Export inventory file</legend>
		// 				<div class = "form-group" style="display:none;">
		// 					{!! Form::label('export_inventory_index', 'Filter export by shipper =>', ['class' => 'control-label']) !!}
		// 					{!! Form::select('export_inventory_index', $inventory_indexes, 0, ['id' => 'export_inventory_index', 'class' => 'form-control']) !!}
		// 				</div>
		// 				<div class = "form-group">
		// 					{!! Form::submit('Export', ['class' => 'btn btn-sm btn-primary']) !!}
		// 				</div>
		// 				{!! Form::close() !!}
		// 			</div>
		// 		</div>
		// 	</div>
		// </div>
		
	// }
	
	public function download_images() {
		
		$inventory_items = Inventory::select('id', 'stock_no_unique', 'warehouse')
													->whereNotNull('warehouse')
													->where('warehouse', 'NOT LIKE', 'http://order.monogramonline.com%')
													->where('section_id', 17)
													// ->limit(1)
													->get();
		
		foreach ($inventory_items as $item) {
			
				$url = $item->warehouse;
				$img = '/assets/images/inventory_thumb/' . $item->stock_no_unique . substr($item->warehouse, -4);
				try {	
					file_put_contents(base_path() . '/public_html' . $img, file_get_contents($url));
					
					$item->warehouse = 'http://order.monogramonline.com' . $img;
					$item->save();
				} catch (\Exception $e) {
					Log::info($item->warehouse . ' - ' . $e->getMessage());
				}
				// echo $item->stock_no_unique . ' - ' . $item->warehouse . "\n";
		}
		
		return;
	}
}
