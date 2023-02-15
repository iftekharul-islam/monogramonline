<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Item;
use App\Order;
use App\Batch;
use App\BatchRoute;
use App\InventoryUnit;
use App\PurchaseProduct;
use Monogram\Helper;

class BackorderController extends Controller
{
  
  public function index (Request $request) {
    
    //Item::backOrderItems();
            
    if ($request->has('stock_no')) { 
     if (!$this->arrivedByStockNo($request->get('stock_no'))) { 
       session()->flash('error', 'Error encountered while marking ' . $request->get('stock_no') . ' arrived.'); 
     } 
   } 
    
   if ($request->has('item_code')) { 
     if (!$this->arrivedByItemCode($request->get('item_code'))) { 
       session()->flash('error', 'Error encountered while marking ' . $request->get('item_code') . ' arrived.'); 
     } 
   } 
   
    set_time_limit(0);
    
    $batched =  Item::leftjoin('inventory_unit', 'items.child_sku', '=', 'inventory_unit.child_sku')
                  ->leftjoin('inventories', 'inventory_unit.stock_no_unique', '=', 'inventories.stock_no_unique')
                  ->where('items.item_status', 4)
                  ->where('items.batch_number', '!=', '0')
                  ->where('items.is_deleted', '0')
                  ->orderBy('inventories.stock_no_unique')
                  ->get();
                  
    $unbatched = Item::leftjoin('inventory_unit', 'items.child_sku', '=', 'inventory_unit.child_sku')
                  ->leftjoin('inventories', 'inventory_unit.stock_no_unique', '=', 'inventories.stock_no_unique')
                  ->where('items.item_status', 4)
                  ->where('items.batch_number', '0')
                  ->where('items.is_deleted', '0')
                  ->orderBy('inventories.stock_no_unique')
                  ->get();
    
    $batched_stock_nos = array_unique($batched->pluck('stock_no_unique')->all());
    $unbatched_stock_nos = array_unique($unbatched->pluck('stock_no_unique')->all());
    
    $stock_nos = array_unique(array_merge($batched_stock_nos, $unbatched_stock_nos));
    
    $purchases = PurchaseProduct::where('balance_quantity', '>', 0)
                                        ->whereIn('stock_no', $batched_stock_nos)
                                        ->where('is_deleted', '0')
                                        ->get();
                                        
    return view ( 'backorders.index', compact ( 'batched', 'unbatched', 'stock_nos', 'scan_batch', 'batch_view', 'purchases' ) );
  }
  

  public function show(Request $request) {
    
    $stock_no_unique = null;
    $scan_batch = null;
    $batch_views = null;
    
    if ($request->get('search_in') == 'batch_number' || $request->has('scan_batch')) {  
      
      if ($request->has('scan_batch')) {
        $batch_list = explode(',', rtrim(trim($request->get('scan_batch')), ','));
      } else {
        $batch_list = explode(',', rtrim(trim($request->get('search_for')), ','));
      }
			
      $batch_array = array();
    
    // if ($request->has('scan_batch')) {
    //    $batch_list = explode(',', rtrim(trim($request->get('scan_batch')), ','));
       
       
    		foreach ($batch_list as $batch) {
    				if ($batch == NULL) {
                continue;
            } else if (substr( trim($batch) , 0, 4) == 'BATC') {
    					$batch_array[] = substr( trim($batch), 4);
    				} else {
    					$batch_array[] = trim($batch);
    				}
      	} 
          
            $batch_views = Batch::with('items', 'station')
                              ->whereIn('batch_number', $batch_array)
                              ->get();
      return view ( 'backorders.show_batch', compact ( 'batch_views' ) );
      
    } else if ($request->get('search_in') == 'stock_no_unique') {
      
      $search_for = $request->get('search_for');
      $search_in = $request->get('search_in');
      
      $items = Item::with('order', 'batch')
                            ->leftjoin('inventory_unit', 'items.child_sku', '=', 'inventory_unit.child_sku')
                            ->leftjoin('inventories', 'inventory_unit.stock_no_unique', '=', 'inventories.stock_no_unique')
                            ->leftjoin('batches', 'items.batch_number', '=', 'batches.batch_number')
                            ->leftjoin('stations', 'batches.station_id', '=', 'stations.id')
                            ->where('inventory_unit.stock_no_unique', $search_for)
                            ->whereIn('item_status', [1,4])
                            ->where('items.is_deleted', '0')
                            ->orderby('item_status')
                            ->selectRaw('items.item_status, 
                                        inventories.stock_no_unique, inventories.warehouse, inventories.stock_name_discription,
                                        batches.station_id, stations.station_description, stations.type,
                                        sum(items.item_quantity * inventory_unit.unit_qty) as qty')
                            ->groupBy('item_status')
                            ->groupBy('type')
                            ->get(); 
                            
      return view ( 'backorders.show_stockno', compact ( 'items', 'search_for', 'search_in' ) );
    
    } else { 
                $scan_batch = null; 
                $batch_views = null; 
    }
    
  }
  
  
  public function batchArrive (Request $request) {
     
    if (!$request->has('batch_number')) {
      return redirect()->back()->withErrors('No Batch Selected');
    }
    
    $batch_number = $request->get('batch_number');
    
    $items = Item::where('batch_number', $batch_number)
                ->where('is_deleted', '0')
                ->searchStatus('back order')
                ->orderBy('id', 'ASC')
                ->get();
    
    if (count($items) < 1) {
      return redirect()->action('BackorderController@show', ['scan_batch' => $batch_number])
                        ->withErrors("No Items in Batch");
    }
        
    $batch = Batch::where('batch_number', $batch_number)->first();

    $batch->status = 'active';
    $batch->save();
                      
    foreach ($items as $item) {
      
      $item->item_status = 'production';
      $item->save();

    }
    
    return redirect()->action('BackorderController@show', ['scan_batch' => $batch_number])
                      ->withSuccess("Batch put into production");
  }
  
  public function stockNumber(Request $request) 
  { 
    if (!$request->has('stock_no_unique')) {
      return redirect()->back()->withErrors('No Stock Number Provided');
    }
    
    if ($request->get('item_status') == 'production') {
      $old_status = 1;
      $new_status = 'back order';
      $message = 'Backordered';
    } else if ($request->get('item_status') == 'back order') {
      $old_status = 4;
      $new_status = 'production';
      $message = 'Arrived';
    } else {
      return redirect()->back()->withErrors('Status not recognized');
    }
    
    if (!$request->has('station_id')) {
      $items = Item::join('inventory_unit', 'items.child_sku', '=', 'inventory_unit.child_sku')
                        ->where('inventory_unit.stock_no_unique', $request->get('stock_no_unique'))
                        ->where('item_status', $old_status)
                        ->where('batch_number', '0')
                        ->where('items.is_deleted', '0')
                        ->selectRaw('items.id, items.order_id, items.item_status, items.order_5p, items.batch_number')
                        ->get(); 
                        
      foreach ($items as $item) {
        $item->item_status = $new_status;
        $item->save();
        Order::note('Item ' . $item->id . ' ' . $message, $item->order_5p);
      }
      
      return redirect()->action('BackorderController@show', 
                                ['search_for' => $request->get('stock_no_unique'), 'search_in' => 'stock_no_unique'])
                                ->withSuccess("Items $message");
    } else {
      $items = Item::join('inventory_unit', 'items.child_sku', '=', 'inventory_unit.child_sku')
                        ->join('batches', 'items.batch_number', '=', 'batches.batch_number')
                        ->where('inventory_unit.stock_no_unique', $request->get('stock_no_unique'))
                        ->where('item_status', $old_status)
                        ->where('items.is_deleted', '0')
                        ->where('station_id', $request->get('station_id'))
                        ->selectRaw('items.id, items.order_id, items.item_status, items.order_5p, items.batch_number')
                        ->get();
    }
    
    if ($new_status == 'back order') {
      
      $error = 0;;
      
      foreach ($items as $item) {

          $result = $this->backorder($item->id); 
          
          if ($result['order_5p']) {
            Order::note('Item ' . $item->id . ' ' . $message, $result['order_5p']);
          } else {
            $error++;
          }
          
          if ($error > 0) {
            return redirect()->action('BackorderController@show', 
                                    ['search_for' => $request->get('stock_no_unique'), 'search_in' => 'stock_no_unique'])
                                    ->withErrors("$error Item(s) could not be $message");
          }
      }
      
    } else {
      $this->arrived($items);
    }
    
    return redirect()->action('BackorderController@show', 
                              ['search_for' => $request->get('stock_no_unique'), 'search_in' => 'stock_no_unique'])
                              ->withSuccess("Items $message");
  }

  
  public function itemsBackorder(Request $request) 
  { 
    if (!$request->has('items')) {
      return redirect()->back()->withErrors('No Items Selected');
    }
        
    $batches = array();
    $error = null;
    
    if ($request->has('batch_number')) {
      $batches[] = $request->get('batch_number');
    }
    
    foreach ($request->get('items') as $item_id) {
        
        $result = $this->backorder($item_id);
        
        if ($result['order_5p']) {      
          Order::note("Item $item_id Back Ordered", $result['order_5p']);
        } else {
          $error++;
        }
        
        $batches[] = $result['batch_number'];
    }
    
    $batches = array_unique($batches);
    
    if ($error > 0 && $request->has('batch_number')) {
      return redirect()->action('BackorderController@show', ['scan_batch' => implode(',', $batches)])
                      ->withErrors("$error Item(s) could not be back ordered");
    } else if ($error > 0 && !$request->has('batch_number')) {
      return redirect()->action('BackorderController@show', 
                              ['search_for' => $request->get('stock_no_unique'), 'search_in' => 'stock_no_unique'])
                              ->withErrors("$error Item(s) could not be back ordered");
    } else if ($request->has('batch_number')) {
      return redirect()->action('BackorderController@show', ['scan_batch' =>  implode(',', $batches)])
                        ->withSuccess("Items Backordered");
    } else {
      return redirect()->action('BackorderController@show', 
                              ['search_for' => $request->get('stock_no_unique'), 'search_in' => 'stock_no_unique'])
                              ->withSuccess("Items Backordered");
    }
  }
  
  
  public function itemsArrived(Request $request) 
  {
    if (!$request->has('items')) {
      return redirect()->back()->withErrors('No Items Selected');
    }
    
    $items = Item::whereIn('id', $request->get('items'))
                  ->where('item_status', 4)
                  ->get();
    
    if (!$items) {
      return redirect()->back()->withErrors('Items not found');
    }
    
    $this->arrived($items);
    
    $batches = array_unique($items->pluck('batch_number')->toArray());
    
    if ($request->has('batch_number')) {
      return redirect()->action('BackorderController@show', ['scan_batch' =>  implode(',', $batches)])
                        ->withSuccess("Items Released");
    } else if ($request->has('stock_no_unique')) {
      return redirect()->action('BackorderController@show', 
                              ['search_for' => $request->get('stock_no_unique'), 'search_in' => 'stock_no_unique'])
                              ->withSuccess("Items Released");
    } else {
      return redirect()->action('BackorderController@index')->withSuccess("Items Released");
    }
  }
  
  private function arrivedByStockNo($stock_no) {
     
    $skus = InventoryUnit::where('stock_no_unique', $stock_no)
                          ->where('is_deleted', '0')
                          ->get()
                          ->pluck('child_sku');
    
    $items = Item::whereIn('child_sku', $skus)
                  ->where('is_deleted', '0')
                  ->searchStatus('back order')
                  ->orderBy('id', 'ASC')
                  ->get();
    
    if (count($items) < 1) {
      return false;
    }
    
    return $this->arrived($items);
  }


  private function arrivedByItemCode($item_code) {
    
    $items = Item::where('item_code', $item_code)
                  ->where('is_deleted', '0')
                  ->searchStatus('back order')
                  ->orderBy('id', 'ASC')
                  ->get();

    if (count($items) < 1) {
      return false;
    }
    
    return $this->arrived($items);
    
    // if ($items->sum('item_quantity') <= $qty) {
    //     return $this->arrived($items);
    // } else {
    //     return $this->chooseBatches($items, $qty);
    // }
    
  }
  
  
  private function arrived($items) {
    
    $batch_numbers =  array_unique( $items->pluck('batch_number')->all() );

    $batches = Batch::whereIn('batch_number', $batch_numbers)->get();

    foreach ($batches as $batch) {

      $batch->status = 'active';
      $batch->save();

    }

    foreach ($items as $item) {

      $item->item_status = 'production';
      $item->save();

    }

    return true;
  }


  private function chooseBatches($items, $qty) {
        
    $batch_numbers =  array_unique( $items->pluck('batch_number')->all());
    
    $batches = Batch::whereIn('batch_number', $batch_numbers)->orderBy('min_order_date', 'ASC')->get();

    if (count($batches) < 1) {
      return false;
    }
    
    $remaining_qty = $qty;
      
    foreach ($batches as $batch) {
    
      $batch_qty = $items->where('batch_number', $batch->batch_number)->sum('item_quantity');
      
      if ($batch_qty <= $remaining_qty && $remaining_qty > 0) {

        $batch->status = 'active';
        $batch->save();
        
        $batch_items = $items->where('batch_number', $batch->batch_number)->all();
        
        foreach ($batch_items as $item) {
          $item->item_status = 'production';
          $item->save();
        }
        
        $remaining_qty = $remaining_qty - $batch_qty;
        
      }  //else skip batch
    }
    
    if ($remaining_qty > 0) {
      //split oldest batch
       $oldest_batch = $items->where('item_status', 'back order')->first();
       
       $new_batch_number = Batch::getNewNumber($oldest_batch->batch_number, 'B');
       
       $old_batch = Batch::where('batch_number', $oldest_batch->batch_number)->first();

       $new_batch = new Batch;
       $new_batch->batch_number = $new_batch_number;
       $new_batch->status = 'active';
       $new_batch->section_id = $old_batch->section_id;
       $new_batch->batch_route_id = $old_batch->batch_route_id;
       $new_batch->production_station_id = $old_batch->production_station_id;
       $new_batch->station_id = $old_batch->station_id;
       $new_batch->store_id = $old_batch->store_id;
       $new_batch->prev_station_id = $old_batch->prev_station_id;
       $new_batch->creation_date =  date("Y-m-d H:i:s");
       
       $items = $items->where('batch_number', $oldest_batch->batch_number)->all();
       
       foreach ($items as $item) {
         
         if ($item->item_quantity <= $remaining_qty && $remaining_qty > 0) {
           
           $item->item_status = 'production';
           $item->batch_number= $new_batch_number;
           $item->save();
           
           $remaining_qty = $remaining_qty - $item->item_quantity;
         }
       }
       
       if ($remaining_qty > 0) {
         Log::error("Could not mark all arrived, $remaining_qty remain. Batch: $new_batch_number" );
       } 
    }
    
    return true;
    
  }

  private function backorder ($item, $backorder_qty = 'X') 
  {    
    if (!$item) {
      return FALSE;
    }
    
    $item = Item::find($item);
    
    if ($item->batch_number != '0') {
    
      $batch_number = $item->batch_number;
      $quantity = $item->item_quantity;
      
      $original_batch_number = Batch::getOriginalNumber($batch_number);
      
      $bo_batch = Batch::with('items')
                      ->select('batch_number')
                      ->searchStatus('back order')
                      ->where('batch_number', 'LIKE', 'B%' . $original_batch_number)
                      ->latest()
                      ->get();

      $old_batch = Batch::where('batch_number', $batch_number)
                    ->first();
      
      $item_stock_no = InventoryUnit::where('child_sku', $item->child_sku)
                            ->where('is_deleted', '0')
                            ->first();
      
      if ($item_stock_no) {
        $item_stock_no = $item_stock_no->stock_no_unique;
      } else {
        $item_stock_no = null;
      }
      
      if (count($bo_batch) > 0 && count($bo_batch->first()->items) > 0) {
        $bo_batch_stock_no = InventoryUnit::where('child_sku', $bo_batch->first()->items->first()->child_sku)
                              ->where('is_deleted', '0')
                              ->first();
        
        if ($bo_batch_stock_no) {
          $bo_batch_stock_no = $bo_batch_stock_no->stock_no_unique;
        } else {
          $bo_batch_stock_no = null;
        }
        $bo_batch_status = $bo_batch->first()->items->first()->item_status;
      } else {
        $bo_batch_stock_no = null;
        $bo_batch_status = null;
      }
      
      if (count($bo_batch) > 0 && $bo_batch_status == 'back order' && $bo_batch_stock_no == $item_stock_no) {
        
        $new_batch_number = $bo_batch->first()->batch_number;
        
      } else {
        $new_batch_number = Batch::getNewNumber($batch_number, 'B');
        
        $bo_batch = new Batch;
        $bo_batch->batch_number = $new_batch_number;
        $bo_batch->section_id = $old_batch->section_id;
        $bo_batch->batch_route_id = $old_batch->batch_route_id;
        $bo_batch->production_station_id = $old_batch->production_station_id;
        $bo_batch->store_id = $old_batch->store_id;
        if (in_array($old_batch->section_id, [6,10,18]) && $old_batch->graphic_found == 'Found') {
          //keep sublimation, sandblast at current station and save graphic
          $bo_batch->station_id = $old_batch->production_station_id;
          $bo_batch->graphic_found = '1';
          $bo_batch->to_printer = $old_batch->to_printer;
        } else {
          $route = BatchRoute::with('stations_list')
                              ->where('id', $old_batch->batch_route_id)
                              ->first(); 
          $bo_batch->station_id = $route->stations_list->first()->station_id;
        }
        
        $bo_batch->creation_date = date("Y-m-d H:i:s");
        $bo_batch->change_date = date("Y-m-d H:i:s");
        $bo_batch->status = 'back order';
        $bo_batch->save();
      }
    } else {
      $new_batch_number = '0';
    }
    
    if ($backorder_qty == $item->item_quantity || $backorder_qty == 'X' || $backorder_qty == 0) {
      
        $item->item_status = 'back order';
        $item->batch_number = $new_batch_number;
        $item->save();
        
        $bo_id = $item->id;
      
    } else {
      
        $update_qty = $item->item_quantity - $backorder_qty;
        
        $bo_item = $item->replicate();
        
        $item->item_quantity = $update_qty;
        $item->save();
        
        $bo_item->item_quantity = $backorder_qty;
        $bo_item->item_status = 'back order';
        $bo_item->batch_number = $new_batch_number ?? '0';
        $bo_item->save();
        
        $bo_id = $bo_item->id;
    }
    
    Batch::isFinished($old_batch->batch_number ?? null);
    
    return ['order_5p' => $item->order_5p, 'batch_number' => $new_batch_number];
    
  }
  
}
