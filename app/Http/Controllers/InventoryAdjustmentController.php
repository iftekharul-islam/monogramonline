<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\InventoryAdjustment;
use App\Inventory;
use App\Rejection;

class InventoryAdjustmentController extends Controller
{
    
    public function index(Request $request)
    {
        if ($request->has('tab')) {
          $tab = $request->get('tab');
        } else {
          $tab = 'view';
        }
        
        $view_stock_no = trim($request->get('view_stock_no'));
        $count_stock_no = trim($request->get('count_stock_no'));
        $receive_stock_no = trim($request->get('receive_stock_no'));
        
        if (substr($request->get('reject_item'), 0, 4) == 'ITEM') {
          $reject_item = trim(substr($request->get('reject_item'), 4));
        } else {
          $reject_item = trim($request->get('reject_item'));
        }
        
        if ($view_stock_no == null) {
          $limit = 25;
        } else {
          $limit = 100;
        }
        
        $adjustments = InventoryAdjustment::with('user', 'inventory')
                                            ->searchStockNumber($view_stock_no)
                                            ->where('is_deleted', '0')
                                            ->limit($limit)
                                            ->latest()
                                            ->get();
        
        $count = NULL;
        
        if ($request->has('count_stock_no')) {
          
          $count = Inventory::with('adjustments', 'inventoryUnitRelation')
                      ->leftjoin('purchased_products', 'inventories.stock_no_unique', '=', 'purchased_products.stock_no')
                      ->where('stock_no_unique', $count_stock_no)
                      ->first();
        }
        
        $rejects = Rejection::with('rejection_reason_info')
                              ->whereNull('scrap')
                              ->searchItem($reject_item)
                              ->where('is_deleted', '0')
                              ->get();
                              
        return view('inventory_adjustment.index', compact('tab', 'view_stock_no', 'adjustments', 'count', 'count_stock_no', 
                                                          'receive_stock_no', 'receive', 'reject_item', 'rejects'));
        
    }

    
    public function store(Request $request)
    { 
      
        if ($request->has('count_quantity') && $request->has('count_stock_no')) {
            
            $count_stock_no = $request->get('count_stock_no');
            
            InventoryAdjustment::adjustInventory(3, $request->get('count_stock_no'), $request->get('count_quantity'), $request->get('count_note'));
          
            $inventory = Inventory::where('stock_no_unique', $count_stock_no)->first();
            
            if ($inventory->qty_on_hand == intval($request->get('count_quantity'))) {
              return redirect()->action('InventoryAdjustmentController@index', ['tab' => 'count', 'count_stock_no' => $count_stock_no])
                              ->with('success', "Quantity on hand for $count_stock_no adjusted to " . $request->get('count_quantity'));
            } else {
              return redirect()->action('InventoryAdjustmentController@index', ['tab' => 'count', 'count_stock_no' => $count_stock_no])
                              ->withErrors("Quantity on hand for $count_stock_no incorrect after adjustment");
            }
        
        } elseif ($request->has('adjust_quantity') && $request->has('count_stock_no')) {
            
            $count_stock_no = $request->get('count_stock_no');
            
            if ($request->get('adjust_quantity') == 0) {
              return redirect()->action('InventoryAdjustmentController@index', ['tab' => 'count', 'count_stock_no' => $count_stock_no])
                              ->withErrors("Invalid quantity entered");
            }
          
            InventoryAdjustment::adjustInventory(4, $request->get('count_stock_no'), $request->get('adjust_quantity'),  $request->get('adjust_note'));
          
            return redirect()->action('InventoryAdjustmentController@index', ['tab' => 'count', 'count_stock_no' => $count_stock_no])
                              ->with('success', "$count_stock_no adjusted by " . $request->get('adjust_quantity'));
                
        } elseif ($request->has('rejection_id')) {
            
            $reject = Rejection::with('item.inventoryunit')
                              ->where('id', $request->get('rejection_id'))
                              ->first();
                         
            if ($request->get('action') == 'scrap') {
              
              
              if (count($reject->item->inventoryunit) > 0) {

                foreach ($reject->item->inventoryunit as $stock_no) {
                  
                  if ($stock_no->stock_no_unique != '' && $stock_no->stock_no_unique != 'ToBeAssigned') {
                    
                    //only saves last adjustment ??
                    $reject->scrap = InventoryAdjustment::adjustInventory(5, $stock_no->stock_no_unique, $reject->reject_qty * $stock_no->unit_qty, $reject->id, $reject->item->id);
                    $reject->save();
                    
                    return redirect()->action('InventoryAdjustmentController@index', ['tab' => 'production', 'reject_item' => $request->get('reject_item')])
                                    ->with('success', 'Inventory adjusted for Reject Item ' . $request->get('reject_item'));
                                    
                  } else {

                    $reject->scrap = 0;
                    $reject->save();
                    
                    return redirect()->action('InventoryAdjustmentController@index', ['tab' => 'production', 'reject_item' => $request->get('reject_item')])
                                    ->withErrors('Reject Item ' . $request->get('reject_item') . ' ignored, no stock number found');
                  }
                }   
                
              } else {
                
                $reject->scrap = 0;
                $reject->save();
                
                return redirect()->action('InventoryAdjustmentController@index', ['tab' => 'production', 'reject_item' => $request->get('reject_item')])
                                ->withErrors('Reject Item ' . $request->get('reject_item') . ' ignored, no stock number found');
              }
              
            } elseif ($request->get('action') == 'ignore') {
              
              $reject->scrap = 0;
              $reject->save();
              
              return redirect()->action('InventoryAdjustmentController@index', ['tab' => 'production', 'reject_item' => $request->get('reject_item')])
                              ->with('success', 'Reject Item ' . $request->get('reject_item') . ' ignored.');
            } else {
              return redirect()->action('InventoryAdjustmentController@index', ['tab' => 'production', 'reject_item' => $request->get('reject_item')])
                              ->withErrors('Error when processing Item ' . $request->get('reject_item'));
            }
            
        } elseif ($request->has('reject_quantity') && $request->has('receive_stock_no')) {
            
        } else {
          return redirect()->action('InventoryAdjustmentController@index')
                          ->withErrors('Unrecognized Input');
        }
    }

}
