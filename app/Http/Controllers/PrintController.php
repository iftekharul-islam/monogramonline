<?php

namespace App\Http\Controllers;

use App\BatchRoute;
use App\Item;
use App\Order;
use App\Store;
use App\Customer;
use App\Purchase;
use App\Parameter;
Use App\Ship;
use App\SpecificationSheet;
use App\Inventory;
use App\Batch;
use App\Station;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Monogram\Helper;
use Illuminate\Support\Facades\DB;

class PrintController extends Controller
{
	
	public function packing ($id)
	{
		$order = Order::find($id);

		if ( !$order ) {
			return view('errors.404');
		}
		
		$store = Store::where('store_id', $order->store_id)->first();
		
		return view('prints.packing_slip', compact('order', 'store'));
	}

	public function purchase ($purchase_id)
	{
		$purchase = Purchase::with('vendor_details', 'products.product_details', 'products.inventory')
							->where('id', $purchase_id)
							->first();
		if ( !$purchase ) {
			return view('errors.404');
		}

		#return $purchase;
		return view('prints.purchase', compact('purchase'));
	}

	public function batches (Request $request)
	{ 
		if ( $request->has('batch_numbers') ) {
			
			$batch_numbers = $request->get('batch_numbers');
		
		} else if ($request->has('batch_number')) {
			
			$batch_numbers = $request->get('batch_number');
			
		} else {
			
			if (!$request->has('printed')) {
				log::error('Printed parameter not set - PrintController@batches');
				return "Printed parameter not set";
			}
			
			if ($request->has('production_station')) {

				$batch_numbers = Batch::join('items', 'batches.batch_number', '=', 'items.batch_number') 
										// ->join('sections', function($join)
										// 				{
										// 						$join->on('batches.section_id', '=', 'sections.id')
										// 									->where('sections.inventory', '!=', '1')
										// 									->orWhere(DB::raw('batches.inventory'), '=', '2');
										// 				})
										->leftjoin('inventory_unit', 'items.child_sku', '=', 'inventory_unit.child_sku')
										->searchStatus('active')
										->searchStation($request->get('station'))
										->searchSection($request->get('section'))
										->searchStore($request->get('store'))
										->searchType($request->get('type'))
										->searchProductionStation($request->get('production_station'))
										->searchPrinted($request->get('printed'))
										//->where('batches.min_order_date', '<', '2017-12-07 00:00:00')
										->groupBy('batches.batch_number')
										//->orderBy(DB::raw("DAY(creation_date)"))
										//->orderBy('inventory_unit.stock_no_unique')
										->orderBy('items.child_sku')
										->get()
										->pluck('batch_number');
										
			} else if ($request->has('graphic_dir')) {

				$batch_numbers = Batch::searchStatus('active')
										// ->join('sections', function($join)
										// 				{
										// 						$join->on('batches.section_id', '=', 'sections.id')
										// 									->where('sections.inventory', '!=', '1')
										// 									->orWhere(DB::raw('batches.inventory'), '=', '2');
										// 				})
										->searchGraphicDir($request->get('graphic_dir'))
										->searchStore($request->get('store'))
										->searchType($request->get('type'))
										->searchPrinted($request->get('printed'))
										->groupBy('batches.batch_number')
										->orderBy('batches.batch_number', 'ASC')
										->get()
										->pluck('batch_number'); 
			} else {
				log::error('Unrecognized Input - PrintController@batches');
				return "Unrecognized Input";
			}
			
		} 
		
		if (empty($batch_numbers) || count($batch_numbers) == 0) {
			log::error('No Batches Found - PrintController@batches - production_station: ' .  $request->get('production_station'));
			return "No Batches Found";
		}
		
		$modules = [ ];
		$date  = date("Y-m-d  H:i:s");
				
		foreach ( $batch_numbers as $batch_number ) {
			set_time_limit(0);
			$module = $this->batch_printing_module($batch_number, $date);
			$modules[] = $module->render();
			Batch::note($batch_number,'','1', 'Summary Printed');
		}
		
		if ($request->has('production_station')) {
			$title_station = Station::where('id', $request->get('production_station'))->first();
			$title = $title_station->station_name;
		} elseif ($request->has('station')) {
			$title_station = Station::where('id', $request->get('station'))->first();
			$title = $title_station->station_name;
		} else {
			$title = '';
		}
		
		return view('prints.batch_printer')->with('modules', $modules)->with('title', $title);
	}

	public function singleBatch (Request $request)
	{
		
		if ($request->has('batch_number')) {
			
			$batch_number = $request->get('batch_number');
			
		} else {
			
			log::error('No Batch Found - PrintController@singleBatch - batch: ' .  $request->get('batch_number'));
			return view('errors.404');
		}
		
		set_time_limit(0);
		$module = $this->batch_printing_module($batch_number);
		$modules = [ ];
		$modules[] = $module->render();
		
		return view('prints.batch_printer')->with('modules', $modules);
	}
	
	public function subScreenshot ($batch_number) {
		
		return $this->sublimationSummary($batch_number)->render();
	}
	
	public function sublimationSummary ($batch_number) { 
		 
		$batch = Batch::with('items.order.shippable_items', 'items.store', 'route.stations_list', 'items.inventoryunit.inventory') 
						->where('batch_number', $batch_number) 
						->first(); 
		 
		if (!$batch) { 
			return false; 
		} 
		
		$options = array();
		
		foreach ($batch->items as $item) {
			$options[$item->id] = Helper::optionTransformer($item->item_option, 1, 1, 1, 1, 0, '<br>');
		}
		 
		return view('prints.sublimation_summary', compact('batch', 'options')); 
	 
	} 
	
	public function print_stock_no_unique (Request $request)
	{
		$inventory = Inventory::find($request->get('stock_no_unique'));
// 		dd($request->all(), $inventory);
		return view('prints.print_stock_no_unique')->with('inventory', $inventory);
	}
	
	private function batch_printing_module ($batch_number, $date = NULL)
	{
		if ($date == NULL) {
			$date  = date("Y-m-d  H:i:s");
		}
		
		$batch = Batch::with('items.order.customer', 'items.store', 'route.stations_list', 'station', 'items.product', 'items.inventoryunit')
					->where('batch_number', '=', $batch_number)
					->latest('creation_date')
					->first();

		if ( !$batch ) {
			return view('errors.404');
		}
		
		$stock = array();
		
		foreach ( $batch->items as $item ) {
			
			if ($item->inventoryunit) {
				
				foreach ($item->inventoryunit as $unit) {
					$stockno = $unit->stock_no_unique;

					if( array_key_exists ( $stockno , $stock )) {
						$stock[$stockno] += $item->item_quantity * $unit->unit_qty;
					} else {
						$stock[$stockno] = $item->item_quantity * $unit->unit_qty;
					}
				}
			}
		}
		
		if (!empty($stock)) {
			$inventory = Inventory::whereIn('stock_no_unique',array_keys($stock))
							->groupBy('stock_no_unique')
							->get();
		} else {
			$inventory = NULL;
		}
		
		$next_station_name = '';
		$station_list = $batch->route->stations_list;
		$grab_next = false;
		
		foreach ( $station_list as $station ) {
			
				if ( $grab_next ) {
					$grab_next = false;
					$next_station_name = $station->station_name;
					break;
				}
				if ( $station->station_name == $batch->station->station_name ) {
					$grab_next = true;
				}
		}

		// if ( !empty( $current_station_by_url ) ) {
		// 	$current_station_name = $current_station_by_url;
		// }

		// if ( $current_station_name == '' ) {
		// 	$current_station_name = Helper::getSupervisorStationName();
		// }

		#$bar_code = Helper::getHtmlBarcode($batch_number);
		//$statuses = Helper::getBatchStatusList();
		$route = BatchRoute::with('stations', 'template')
							 ->find($batch->batch_route_id);
		$stations = BatchRoute::routeThroughStations($batch->batch_route_id, $batch->station->station_name);

		$count = 1;
		
		$batch->summary_date = $date;
		$batch->summary_user_id = auth()->user()->id;
		$batch->summary_count = $batch->summary_count + 1;
		$batch->save();
		
		return view('prints.printing_module', compact('batch', 'inventory', 'stock', 'batch_status', 
								'next_station_name', 'batch_number', 'route', 'stations', 'count'));
	}

	private function getOrderFromId ($order_ids) // get an id or an array of order id
	{
		if ( is_array($order_ids) ) {
			$orders = Order::with('customer', 'items.shipInfo')
							 ->whereIn('id', $order_ids)
							 ->where('is_deleted', '0')
							 ->get();

			return $orders;
		} else {
			$order = Order::with('customer', 'items.shipInfo')
							->where('id', $order_ids)
							->where('is_deleted', '0')
							->first();

			return $order;
		}

	}
	
	private function getPackingModulesFromOrder ($params) // get each order row
	{
		#dd($params instanceof Collection);
		$orders = [ ];
		if ( $params instanceof Collection ) {
			$orders = $params; // is this a collection? if yes, then it's an array
		} else {
			$orders[] = $params; // if it is not a collection, then it's a single order
		}
		$modules = [ ];
		foreach ( $orders as $order ) {
			$modules[] = view('prints.includes.print_slip_partial', compact('order'))->render();
		}

		return $modules;
	}

	public function print_spec_sheet (Request $request)
	{
		$specs = SpecificationSheet::with('production_category')
									 ->whereIn('id', $request->get('spec_id'))
									 ->get();
		if ( !$specs ) {
			return redirect()
				->back()
				->withErrors([
					'error' => 'No spec sheet was chosen',
				]);
		}
		$modules = [ ];
		foreach ( $specs as $spec ) {
			$modules[] = $this->spec_print_module($spec);
		}

		return view('prints.spec_sheet')->with('modules', $modules);
	}

	private function spec_print_module ($spec)
	{
		return view('prints.includes.print_spec_partial')
			->with('spec', $spec)
			->render();
	}

}
