<?php

namespace App\Http\Controllers;

use App\InventoryUnit;
use App\Inventory;
use App\Option;
use App\Parameter;

use Illuminate\Http\Request;


use App\Http\Requests;
use App\Http\Controllers\Controller;

class InventoryUnitController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
    	
    	
// //     	$options = Option::with('inventoryunit_relation')
// // 					    	->where('child_sku', 'ZC9083S-AC-7/8inch-18"')
// // 							->take(5)
// // 					    	->get();

//     	$inventorys = Inventory::with('options')
// //     							->take(5)
//     							->get();
    							
//     	foreach ($inventorys as $inventory){
//     		set_time_limit(0);
//     		foreach ($inventory->options as $option){
// //     			dd($option);
//     			Helper::jewelDebug($option->child_sku ." -------- ". $option->stock_number);
    			
//     			$inventoryUnit = new InventoryUnit();
//     			$inventoryUnit->child_sku = $option->child_sku;
    			
//     			$inventoryUnit->stock_no_unique = $option->stock_number;
    			
//     			$inventoryUnit->unit_qty = 1;
//     			$inventoryUnit->save();
//     		}
    		
//     	}
    	
//     	dd(count($inventorys), $inventorys[0]->options, $inventorys[0]->options[0]->stock_number);
        //
        
    	
    	$inventoryUnit = InventoryUnit::where('is_deleted', 0)
						    	->get();
    	
    	return view('inventory_unit.index');
    }

    public function getChildSkuById (Request $request)
    {

    	
    	$inventoryUnit = InventoryUnit::where('child_sku', 'LIKE', $request->child_sku)
						    	->where('is_deleted', 0)
						    	->get();
    	//dd($inventoryUnit->toArray());
    	if ( count($inventoryUnit->toArray()) == 0 ) {
    		return response()->json([]);
    	} else {
    		return response()->json(
    				$inventoryUnit->toArray()
    		);
    	}
    	
    }
    
    /*
     * inventoryunit/save_child_sku
     */
    public function save_child_sku (Request $request)
    {
    	if($request->copyChildSku){
    		
    		$inventoryUnit = InventoryUnit::where('child_sku', $request->childSkus)->get(['stock_no_unique', 'unit_qty'])->toArray();
    		
    		if(count($inventoryUnit) > 0){
	    		
    			InventoryUnit::where('child_sku', $request->childSkusTo)->delete();
	    		
	    		foreach ($inventoryUnit as $stock_no_unique){
	    			$invenUnit = new InventoryUnit();
	    			$invenUnit->child_sku = $request->childSkusTo;
	    			$invenUnit->stock_no_unique = $stock_no_unique['stock_no_unique'];
	    			$invenUnit->unit_qty = $stock_no_unique['unit_qty'];
	    			$invenUnit->save();
	    		}
	    		
    			return redirect()->back()->with('success', "Copy done from ".$request->childSkus." to ". $request->childSkusTo);
    		}else{
    			return redirect()->back()->withErrors([
    					'error' => 'No Stock# found for child_sku '. $request->childSkus,
    			]);
    		}
    	}
    	
    	// one purchase may have many products
    	$index = 0;
    	// index is used to grab the array of products with details.
    	$mchild_sku = $request->get('mchild_sku');
    	$o_unique = array_values( $request->get('stock_no_unique') );
    	$unit_qty = array_values( $request->get('unit_qty') );
    	
    	InventoryUnit::where('child_sku', $mchild_sku)->delete();
    	
    	foreach ( $o_unique as $o_unique_id ) {
    		if (! empty($o_unique[ $index ]) && ! empty($unit_qty[ $index ]) ) {
//     			$inventoryUnit = new InventoryUnit();
//     			$inventoryUnit->child_sku = $mchild_sku;
//     			$inventoryUnit->stock_no_unique = $o_unique[ $index ];
//     			$inventoryUnit->unit_qty = $unit_qty[ $index ];
//     			$inventoryUnit->save();
				Inventory::saveinventoryUnit($mchild_sku, $o_unique[ $index ], $unit_qty[ $index ]);
    			++$index;    			
    		}
    	}
    	
    	$saved = 'saved';
		return response()->json(['status' => $saved] );
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
