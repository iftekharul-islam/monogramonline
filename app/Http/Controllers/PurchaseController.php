<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Product;
use App\Purchase;
use App\PurchaseProduct;
use App\PurchasedInvProducts;
use App\Vendor;
use App\InventoryAdjustment;

class PurchaseController extends Controller
{
	public function index (Request $request)
	{
		$open_purchase_ids = PurchaseProduct::where('balance_quantity', '>', 0)
																				->where('is_deleted', '0')
																				->selectRaw('DISTINCT purchase_id')
																				->get()
																				->pluck('purchase_id');
																				
		$open_purchases = Purchase::with('vendor_details', 'products')
							 ->whereIn('po_number', $open_purchase_ids)
							 ->whereHas('vendor_details', function ($query) use ($request) {
								 if ( $request->get('search_in') == 'vendor_name' ) {
									 $query->searchVendorName($request->get('search_for'));
								 }
							 })
							 ->whereHas('products', function ($query) use ($request) {
								 if ( $request->get('search_in') == 'stock_number' ) {
									 $query->searchStockName($request->get('search_for'));
								 }
							 })
							 ->where('is_deleted', '0')
							 ->search($request->get('search_for'), $request->get('search_in'))
							 ->latest()
							 ->get();
		
	 $closed_purchases = Purchase::with('vendor_details', 'products')
							 ->whereNotIn('po_number', $open_purchase_ids)
							 ->whereHas('vendor_details', function ($query) use ($request) {
								 if ( $request->get('search_in') == 'vendor_name' ) {
									 $query->searchVendorName($request->get('search_for'));
								 }
							 })
							 ->whereHas('products', function ($query) use ($request) {
								 if ( $request->get('search_in') == 'stock_number' ) {
									 $query->searchStockName($request->get('search_for'));
								 }
							 })
							 ->where('is_deleted', '0')
							 ->search($request->get('search_for'), $request->get('search_in'))
							 ->latest()
							 ->paginate(50);


		$search_in = [
			'purchase_number'       => 'PO#',
			'vendor_name'           => 'Vendor name',
			'stock_number'          => 'Stock number',
			'purchase_order_status' => 'PO Status',
		];

		// return $purchases->all();
		
		if ($request->has('page')) {
			$tab = 'closed';
		} elseif (!$request->has('tab')) {
			$tab = 'open';
		} else {
			$tab = $request->get('tab');
		}
		
		return view('purchases.index', compact('open_purchases', 'closed_purchases', 'search_in', 'request', 'tab'));
	}

	public function purchasedVendorSku (Request $request)
	{
		$purchasedVendorSku = PurchasedInvProducts::select(DB::raw('CONCAT(stock_no, " - ", vendor_sku, " - ", vendor_sku_name) AS vendor_sku_name'), 'id')
													->where('vendor_id', $request->vendor_id)
													->where('is_deleted', '0')
													->orderBy('stock_no')
													->get()
													->pluck('vendor_sku_name', 'id')
													->prepend('Select', '0'); 
		
		if ( count($purchasedVendorSku) > 0 ) {
			return response()->json($purchasedVendorSku);
		} else {
			return response()->json([
				'0' => 'Select',
			]);
		}
		 
	}
	
	public function create (Request $request)
	{
		$vendors = Vendor::select(DB::raw('CONCAT(vendors.id, " - ", vendors.vendor_name) AS full_name'), 'vendors.id')
							->rightJoin('purchased_inv_products', 'purchased_inv_products.vendor_id', '=', 'vendors.id')
							->where('vendors.is_deleted', '0')
							->where('purchased_inv_products.is_deleted', '0')
							->get()
							->pluck('full_name', 'id')
							->prepend('Select a vendor', 0);
							
		$purchasedVendorSku = ["''" => 'Select'];
		
		return view('purchases.create', compact('vendors','purchasedVendorSku'));
	}

	public function store (Request $request)
	{
		
		$product_ids = $request->get('product_id');
		$stock_nos = $request->get('stock_no');
		$vendor_skus = $request->get('vendor_sku');
		$quantitys = $request->get('quantity');
		$prices = $request->get('price');
		$sub_totals = $request->get('sub_total');
		$etas = $request->get('eta');
		//----------
		
		$count = 0;
		
		if (count($vendor_skus) > 0) {
			foreach ($vendor_skus as $sku) {
				if ($sku != '0') {
					$count++;
				}
			}
		}
		
		if ($count == 0) {
			return redirect()->back()->withErrors('No Products in Purchase Order');
		}
		
		$new_purchase_number = sprintf("%06d", Purchase::count() + 1);
		
		$purchase = new Purchase();
		$purchase->po_number = $new_purchase_number;
		$purchase->po_date = trim($request->get('po_date'));
		$purchase->payment_method = trim($request->get('payment_method'));
		$purchase->vendor_id = trim($request->get('vendor_id'));
		$purchase->grand_total = trim($request->get('grand_total'));
		$purchase->o_status = trim($request->get('o_status'));
		$purchase->notes = trim($request->get('notes'));
		if (auth()->user()) {
			$purchase->user_id = auth()->user()->id;
		} else {
			$purchase->user_id = 87;
		}
		$purchase->save();
		
		$sku_names = PurchasedInvProducts::where('vendor_id', $purchase->vendor_id)->get()->pluck('vendor_sku_name', 'id');
		
		foreach (array_keys($product_ids) as $index ) {
			if ( ! empty($vendor_skus[ $index ]) && $vendor_skus[ $index ] != '0' 
						&& ! empty($quantitys[ $index ]) && ! empty($sub_totals[ $index ]) ) {
				$purchased_products = new PurchaseProduct();
				$purchased_products->purchase_id = $new_purchase_number;
				$purchased_products->product_id = $product_ids[ $index ];
				$purchased_products->stock_no = $stock_nos[ $index ];
				$purchased_products->vendor_sku = $vendor_skus[ $index ];
				$purchased_products->vendor_sku_name = $sku_names[ $product_ids[ $index ] ];
				$purchased_products->quantity = $quantitys[ $index ];
				$purchased_products->price = $prices[ $index ];
				$purchased_products->sub_total = $sub_totals[ $index ];
				$purchased_products->eta = $etas[ $index ];
				$purchased_products->balance_quantity = $quantitys[ $index ];
				$purchased_products->save();
			}
		}
		
		return redirect()->action('PurchaseController@index')->withSuccess('Purchase Order ' . $new_purchase_number . ' created.');
	}

	public function show ($id)
	{
		$purchase = Purchase::with('products', 'vendor_details')
							->find($id);
		
		if (!$purchase) {
			return redirect()->back()->withErrors('Purchase order not found');
		}
		
		return view('purchases.show', compact('purchase'));
	}

	public function edit ($id)
	{
		//
		$purchase = Purchase::with('products.product_details', 'vendor_details')
							->where('po_number', $id)
							->where('is_deleted', '0')
							->first();
		
		if (!$purchase) {
			$purchasedVendorSku = null;
			$vendors = null;
			return view('purchases.edit', compact('vendors','purchase', 'purchasedVendorSku'));
		}
							
		$vendors = Vendor::select(DB::raw('CONCAT(vendors.id, " - ", vendors.vendor_name) AS full_name'), 'vendors.id')
							->rightJoin('purchased_inv_products', 'purchased_inv_products.vendor_id', '=', 'vendors.id')
							->where('vendors.is_deleted', '0')
							->get()
							->pluck('full_name', 'vendors.id')
							->prepend('Select a vendor', 0);
							
		$purchasedVendorSku = Purchase::leftJoin('purchased_inv_products', 'purchased_inv_products.vendor_id', '=', 'purchases.vendor_id')
							->select(DB::raw('CONCAT(purchased_inv_products.vendor_sku, " - ", purchased_inv_products.vendor_sku_name) AS vendor_sku_name'), 'purchased_inv_products.id')
							->where('purchases.is_deleted', '0')
							->where('purchased_inv_products.is_deleted', '0')
							->where('purchases.vendor_id', $purchase->vendor_id)
							->get()
							->pluck('vendor_sku_name', 'id')
							->prepend('Select', '');

		return view('purchases.edit', compact('vendors','purchase', 'purchasedVendorSku'));

	}

	public function update (Request $request, $id)
	{
		$purchase = Purchase::where('po_number', $id)
							->where('is_deleted', '0')
							->first();
		
		if ($request->has('tracking') && $purchase->tracking != $request->get('tracking')) {
			$purchase->tracking = $request->get('tracking');
			
		}
		
		if ($request->has('notes') && $purchase->notes != $request->get('notes')) {
			$purchase->notes = $request->get('notes');
			
		}
		
		if ($purchase->grand_total != $request->get('grand_total')) {
			$purchase->grand_total = $request->get('grand_total');
		}
		
		$purchase->save();
		
		$rowids = $request->get('rowid'); 
		$product_ids = $request->get('product_id');
		$stock_nos = $request->get('stock_no');
		$vendor_skus = $request->get('vendor_sku');
		$vendor_sku_names = $request->get('name');
		$quantitys = $request->get('quantity');
		$prices = $request->get('price');
		$sub_totals = $request->get('sub_total'); 
		$etas = $request->get('eta');

		//----------
		foreach ($product_ids as $key => $value) {
			
			$value = trim($value);
			if (empty($value))
				return redirect ()->back ()->withErrors ( "Please select vendor_sku" );
			
			if(($quantitys[$key] == 0) || empty($quantitys[$key]))
				return redirect ()->back ()->withErrors ( "Please insert quantity" );
		}
		
		$purchased_products = PurchaseProduct::where('purchase_id', $id)
																					->where('is_deleted', '0')
																					->latest()
																					->get();
																					
		if (auth()->user()) {
			$user_id = auth()->user()->id;
		} else {
			$user_id = 87;
		}
		
		if (count($purchased_products) > count($product_ids)) { 
			
			if (!empty(array_filter($rowids))) {
				$deleted_products = PurchaseProduct::where('purchase_id', $id)
																							->where('is_deleted', '0')
																							->whereNotIn('id', $rowids)
																							->update([  
																													'is_deleted' => '1',
																						 							'user_id' => $user_id
																												]);
			} else {
				$deleted_products = PurchaseProduct::where('purchase_id', $id)
																							->where('is_deleted', '0')
																							->update([ 	
																													'is_deleted' => '1',
																						 							'user_id' => $user_id
																						 						]);
			}
		}
		
		foreach (array_keys($product_ids) as $index) {
			
			
			
			if ( ! empty($vendor_skus[ $index ]) && ! empty($quantitys[ $index ]) && ! empty($sub_totals[ $index ]) ) {
				
				if (isset($rowids[ $index ]) && $rowids[ $index ] != '') {
					$purchased_product = $purchased_products->find($rowids[ $index ]);
				} else {
					$purchased_product = 0;
				}
				
				if (!$purchased_product) {
					$purchased_product = new PurchaseProduct();
					$purchased_product->purchase_id = $id;
					$purchased_product->balance_quantity = $quantitys[ $index ];
				} else {
					$purchased_product->balance_quantity = $quantitys[ $index ] - $purchased_product->receive_quantity;
				}
				
				$updated = $purchased_product->updated_at;
				
				$purchased_product->product_id = $product_ids[ $index ];
				$purchased_product->stock_no = $stock_nos[ $index ];
				$purchased_product->vendor_sku = $vendor_skus[ $index ];
				$purchased_product->vendor_sku_name = $vendor_sku_names[ $index ];
				$purchased_product->quantity = $quantitys[ $index ];
				$purchased_product->price = $prices[ $index ];
				$purchased_product->sub_total = $sub_totals[ $index ];
				$purchased_product->eta = $etas[ $index ];
				$purchased_product->user_id = $user_id;
				$purchased_product->save();
			}
			
		}
		
		return redirect()
			->back()
			->with('success', 'Purchases is successfully updated.');

	}

		
	public function receive (Request $request)
	{
			if (!$request->has('po_number')) {
				return redirect()->back()->withError('No Purchase Order Selected.');
			}
			
			$date = Date('Y-m-d');
			
			$purchase = Purchase::where('po_number', $request->get('po_number'))
								->where('is_deleted', '0')
								->get();
			
			if (count($purchase) == 0) {
				return view('errors.404');
			} else if (count($purchase) > 1) {
				session()->flash('Error', 'More than one purchase order with number : ' . $id);
			}
			
			$purchase = $purchase[0];
			
			$purchase_items = PurchaseProduct::where('purchase_id', $request->get('po_number'))
											->where('is_deleted', '=', '0')
											->get();

			if ($request->has('product_id')) {
				
				$ids = array_values(( $request->get('id') ));
				$product_ids = array_values(( $request->get('product_id') ));
				$stock_nos = array_values(( $request->get('stock_no') ));
				
				$receive_dates = array_values(( $request->get('receive_date') ));
				$receive_quantitys = array_values(( $request->get('receive_quantity') ));
				$balance_quantitys = array_values(( $request->get('balance_quantity') ));
				
				foreach ( array_keys ($ids) as $index ) { 
			
					$item = $purchase_items->where('id', intval($ids[$index]))->first();
					
					if (!$item || count($item) == 0) {
						return view('purchases.receive', compact ( 'purchase', 'date' ) )
									->withErrors('error', 'Product ID not found ' . $product_ids[ $index ] . ' in Purchase order.');
					}
					
					if ($item && !empty($receive_quantitys[ $index ]) && $item->quantity >= $receive_quantitys[ $index ] 
								&& $item->balance_quantity >= $receive_quantitys[ $index ]) {
						
						// $adjustment = $receive_quantitys[ $index ] - $item->receive_quantity;
						$item->receive_date = $receive_dates[ $index ];
						$item->receive_quantity = $receive_quantitys[ $index ] + $item->receive_quantity;
						$item->balance_quantity = $balance_quantitys[ $index ];
						$item->save();
						
						InventoryAdjustment::adjustInventory(6, $stock_nos[ $index ],  $receive_quantitys[ $index ], $request->get('po_number'), $item->id);
						
					} else if ($item->quantity < $receive_quantitys[ $index ] ) {
						
						return view('purchases.receive', compact ( 'purchase', 'date' ) )
												->withErrors($item->stock_no . ' receive quantity greater than quantity ordered');
												
					} else if ($item->balance_quantity < $receive_quantitys[ $index ]) {
						
						return view('purchases.receive', compact ( 'purchase', 'date' ) )
												->withErrors($item->stock_no . ' receive quantity greater than PO Balance');
					}
				}
				return view('purchases.receive', compact ( 'purchase', 'date' ) )->with('success', ['Purchase is received.']);
			}
		
		$date = Date('Y-m-d');
		
		return view('purchases.receive', compact ( 'purchase', 'date' ) );
	}

	public function destroy ($id)
	{
		$purchase = Purchase::find($id);
		if ( ! $purchase ) {
			return view('errors.404');
		}

		// remove the purchase along with purchase products;

		$purchase->is_deleted = '1';
		$purchase->save();

		PurchaseProduct::where('purchase_id', $purchase->po_number)
					   ->update([ 'is_deleted' => '1' ]);

		return redirect()->route('purchases.index')->withSuccess('Purchase is deleted');
	}

	public function getVendorById (Request $request)
	{
		$vendor = Vendor::find($request->vendor_id);
		if ( ! $vendor ) {
			/**  Return Null Fields because not found **/
			return response()->json([
				'vendor_name'  => '',
				'email'        => '',
				'zip_code'     => '',
				'state'        => '',
				'phone_number' => '',
			]);
		} else {
			/**  Return Null Fields because  found **/
			return response()->json([
				'vendor_name'  => $vendor->vendor_name,
				'email'        => $vendor->email,
				'zip_code'     => $vendor->zip_code,
				'state'        => $vendor->state,
				'phone_number' => $vendor->phone_number,
			]);
		}
	}

	public function getPurchasedInvProducts (Request $request)
	{
		$purchasedInvProducts = PurchasedInvProducts::find($request->get('id'));

		if ( ! $purchasedInvProducts ) {
			/**  Return Null Fields because not found **/
			return response()->json([
				'product_id'      => '',
				'stock_no'        => '',
				'vendor_sku'      => '',
				'price'           => '',
				'vendor_sku_name' => '',
				'lead_time_days'  => '',
			]);
		} else {
			/**  Return Null Fields because  found **/
			return response()->json([
				'product_id'      => $purchasedInvProducts->id,
				'stock_no'        => $purchasedInvProducts->stock_no,
				'vendor_sku'      => $purchasedInvProducts->vendor_sku,
				'price'           => $purchasedInvProducts->unit_price,
				'vendor_sku_name' => $purchasedInvProducts->vendor_sku_name,
				'lead_time_days'  => $purchasedInvProducts->lead_time_days,
			]);
		}
	}

	public function autoComplete (Request $request)
	{
		//dd($request->all());
		$query = $request->get('serchTxt', '');

		$vendors = Vendor::where('is_deleted', '0')
						 ->where('vendor_name', 'LIKE', '%' . $query . '%')
						 ->get()
						 ->take(5);

		$data = [];
		foreach ( $vendors as $key => $vendor ) {
			$data[] = [
				'value' => $vendor->id . " - " . $vendor->vendor_name,
				'id'    => $vendor->id,
			];
		}
		if ( count($data) ) {
			return response()->json($data);
		} else {
			return response()->json([
				'value' => 'No Result Found',
				'id'    => '',
			]);
		}
	}
	
}
