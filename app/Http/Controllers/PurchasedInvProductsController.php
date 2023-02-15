<?php namespace App\Http\Controllers;

use App\Vendor;
use Illuminate\Http\Request;

use App\Inventory;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\PurchasedInvProducts;
use Monogram\Helper;

// purchased_products
class PurchasedInvProductsController extends Controller
{
	private $units = [
										'EA' => 'Each', 
										'CS' => 'Case', 
										'PK' => 'Pack', 
										'SFT' => 'Square Foot'
									];
	
	/**
	 * Display a listing of the resource.
	 * @return \Illuminate\Http\Response
	 */
	public function index (Request $request)
	{

		$purchasedInvProducts = PurchasedInvProducts::with('purchasedInvProduct_details', 'vendor')
													->search($request->get('search_for'), $request->get('search_in'))
													->where('is_deleted', 0)
													// ->orderBy($request->get('sort_by') ?? 'stock_no')
													->latest()
													->paginate(50);
		$search_in = [
			'stock_no'        => "Stock Number",
			'stock_name'      => "Stock Description",
			'vendor_sku'      => "Vendor SKU",
			'vendor_sku_name' => "Vendor SKU name",
			'vendor_name'     => "Vendor Name",
			'vendor_id'       => "Vendor ID",
		];
		
		$units = $this->units;
		
		// $sorting = [
		// 							'stock_no'         => 'Stock Number',
		// 							'vendor_id'        => 'Vendor ID',
		// 							'unit'             => 'Unit'
		// 						];
								
		return view('purchased_inv_products.index', compact('purchasedInvProducts', 'request', 'search_in', 'units'));
	}

	/**
	 * Show the form for creating a new resource.
	 * @return \Illuminate\Http\Response
	 */
	public function create ()
	{
		//.
		$stock_number = Inventory::where('is_deleted', 0)
								 ->orderBy('stock_no_unique')
								 ->select([
									 \DB::raw('CONCAT(stock_no_unique," - ",stock_name_discription) as concatenated_stock_no_unique'),
									 'stock_no_unique',
								 ])
								 ->get()
								 ->pluck('concatenated_stock_no_unique', 'stock_no_unique')
								 ->prepend('Select a Stock Number', 'Select a Stock Number');
		
		$vendors = Vendor::where('is_deleted', 0)
						 ->orderBy('vendor_name')
						 ->select([
						 		\DB::raw('CONCAT(id," - ",vendor_name) as as_vendor_name'),
						 		'id',
						 ])
						 ->get()
						 ->pluck('as_vendor_name', 'id')
						 ->prepend('Please Select Vendor', '');;
		
		$units = $this->units;
		
		return view('purchased_inv_products.create', compact('stock_number', 'vendors', 'units'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function store (Requests\PurchasedInvProductsCreateRequest $request)
	{
		// Check if new_stock_number exist and push
		if ( ! empty($request->stock_no) ) {
			// Check new_stock_number exist in inventories Table
			// Check new_stock_number exist in purchased_inv_products Table
			// 			Helper::insert_stock_number($request->stock_no);
		} else {
			$request->stock_number = $request->stock_number;
		}

// 		$inventorie = Inventory::where('is_deleted', 0)
// 							   ->where('stock_no_unique', $request->get('stock_no'))
// 							   ->get();

// 		if ( $inventorie->count() <= 0 ) {
// 			/**  Add a new  stock_no_unique in inventories Table **/
// 			$inventorie = new Inventory();
// 			$inventorie->stock_no_unique = trim($request->get('stock_no'));
// 			$inventorie->stock_name_discription = trim($request->get('stock_name_discription'));
// 			$inventorie->sku_weight = trim($request->get('sku_weight'));
// 			$inventorie->re_order_qty = trim($request->get('re_order_qty'));
// 			$inventorie->min_reorder = trim($request->get('min_reorder'));
// 			$inventorie->adjustment = trim($request->get('adjustment'));
// 			$inventorie->save();
// 		} else {
// 			/**  Update  stock_no_unique in inventories Table **/
// 			$inventorie[0]->stock_name_discription = trim($request->get('stock_name_discription'));
// 			$inventorie[0]->sku_weight = trim($request->get('sku_weight'));
// 			$inventorie[0]->re_order_qty = trim($request->get('re_order_qty'));
// 			$inventorie[0]->min_reorder = trim($request->get('min_reorder'));
// 			$inventorie[0]->adjustment = trim($request->get('adjustment'));
// 			$inventorie[0]->save();
// 		}

		$purchasedInvProducts = PurchasedInvProducts::where('is_deleted', 0)
							   ->where('stock_no', $request->get('stock_no'))
							   ->where('vendor_id', $request->get('vendor_id'))
							   ->where('vendor_sku', $request->get('vendor_sku'))
							   ->first();
// dd($request->all(), $purchasedInvProducts);
		if(!$purchasedInvProducts){
			/**  Add a new  stock_no_unique in inventories Table **/
			$purchasedInvProducts = new PurchasedInvProducts();
			$purchasedInvProducts->stock_no = trim($request->get('stock_no'));
			$purchasedInvProducts->unit = trim($request->get('unit'));
			$purchasedInvProducts->unit_qty = trim($request->get('unit_qty'));
			$purchasedInvProducts->unit_price = trim($request->get('unit_price'));
			$purchasedInvProducts->vendor_id = trim($request->get('vendor_id'));
			$purchasedInvProducts->vendor_sku = trim($request->get('vendor_sku'));
			$purchasedInvProducts->vendor_sku_name = trim($request->get('vendor_sku_name'));
			$purchasedInvProducts->lead_time_days = trim($request->get('lead_time_days'));
			
			if (auth()->user()) {
				$purchasedInvProducts->user_id = auth()->user()->id;
			} else {
				$purchasedInvProducts->user_id = 87;
			}
			
			$purchasedInvProducts->save();
		}else {
			return redirect()->back()->withErrors([
					'error' => 'Can not insert<br>Same stock_no: '.$request->get('stock_no').', vendor_id: '.$request->get('vendor_id').' and vendor_sku:'.$request->get('vendor_sku').' already exist',
			]);
		}

		return redirect()->action('PurchasedInvProductsController@index')->withSuccess('Purchase Product created successfully.');
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function show ($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function edit ($id)
	{
		$purchasedInvProducts = PurchasedInvProducts::find($id);
		$inventorie = Inventory::where('is_deleted', 0)
							   ->where('stock_no_unique', $purchasedInvProducts->stock_no)
							   ->get(); 
								 
		if ( ! $purchasedInvProducts ) {
			return view('errors.404');
		}

		$vendors = Vendor::where('is_deleted', 0)
		->orderBy('vendor_name')
		->select([
				\DB::raw('CONCAT(id," - ",vendor_name) as as_vendor_name'),
				'id',
		])
		->get()
		->pluck('as_vendor_name', 'id');
		
		$units = $this->units;
		
		return view('purchased_inv_products.edit', compact('purchasedInvProducts', 'inventorie', 'vendors', 'units'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  int                      $id
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function update (Requests\PurchasedInvProductsUpdateRequest $request, $id)
	{

		$purchasedInvProducts = PurchasedInvProducts::find($id);
		if ( ! $purchasedInvProducts ) {
			return view('errors.404');
		}
		$purchasedInvProducts->stock_no = trim($request->get('stock_no'));
		$purchasedInvProducts->unit = trim($request->get('unit'));
		$purchasedInvProducts->unit_qty = trim($request->get('unit_qty'));
		$purchasedInvProducts->unit_price = trim($request->get('unit_price'));
		$purchasedInvProducts->vendor_id = trim($request->get('vendor_id'));
		$purchasedInvProducts->vendor_sku = trim($request->get('vendor_sku'));
		$purchasedInvProducts->vendor_sku_name = trim($request->get('vendor_sku_name'));
		$purchasedInvProducts->lead_time_days = trim($request->get('lead_time_days'));
		
		if (auth()->user()) {
			$purchasedInvProducts->user_id = auth()->user()->id;
		} else {
			$purchasedInvProducts->user_id = 87;
		}
		
		$purchasedInvProducts->save();

// 		/**  Update  stock_no_unique in inventories Table **/
// 		$inventorie = Inventory::where('is_deleted', 0)
// 							   ->where('stock_no_unique', $request->get('stock_no'))
// 							   ->get();
// 		$inventorie[0]->stock_name_discription = trim($request->get('stock_name_discription'));
// 		$inventorie[0]->sku_weight = trim($request->get('sku_weight'));
// 		$inventorie[0]->re_order_qty = trim($request->get('re_order_qty'));
// 		$inventorie[0]->min_reorder = trim($request->get('min_reorder'));
// 		$inventorie[0]->adjustment = trim($request->get('adjustment'));
// 		$inventorie[0]->save();

		session()->flash('success', 'Purchase Product Update successfully.');

		//return redirect()->route('purchasedinvproducts.index');
		return redirect()->back();
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int $id
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function destroy ($id)
	{
		$purchasedInvProducts = PurchasedInvProducts::find($id);
		if ( ! $purchasedInvProducts ) {
			return view('errors.404');
		}
		
		if (auth()->user()) {
			$purchasedInvProducts->user_id = auth()->user()->id;
		} else {
			$purchasedInvProducts->user_id = 87;
		}
		
		$purchasedInvProducts->is_deleted = '1';
		$purchasedInvProducts->save();

		return redirect()->action('PurchasedInvProductsController@index')->withSuccess('Purchase Product successfully deleted.');
	}
	
}
