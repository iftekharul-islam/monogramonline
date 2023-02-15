<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Order;
use App\Item;
use App\Customer;
use App\Rejection;
use App\RejectionReason;
use App\Note;
use Monogram\Helper;

class CsController extends Controller
{

	public function index (Request $request)
	{
		if ($request->has('tab')) {
			$arr = explode(' ',trim($request->get('tab')));
			$tab = strtolower($arr[0]);
		} else {
			$tab = 'address';
		}
		
		if ($tab == 'index') {
			$tab = 'address';
		}
		
		$count = array();
		
		if ($tab == 'address') {
			$addresses = Order::with('customer', 'notes')
										->where('order_status', 11)
										->where('is_deleted', '0')
										->get();
			
			$count['address'] = count($addresses);
			
		} else {
			$count['address'] = Order::where('order_status', 11)
																->where('is_deleted', '0')
																->count();
		}
		
		if ($tab == 'rejects') {
			
			if (!$request->has('rejection_reason') && !$request->has('reject_batch')) {
				
				$reject_summary = Rejection::join('items', 'rejections.item_id', '=', 'items.id')
																->where('items.is_deleted', '0')
																->where('items.item_status', 3)
																->where( 'complete', '0' )
																->where('graphic_status', 4)
																->selectRaw('rejection_reason, COUNT(rejections.id) as count')
																->groupBy('rejection_reason')
																->orderBy('rejections.rejection_reason', 'ASC')
																->get(); 
				
				$reasons = RejectionReason::getReasons();
				
			} else {
				
				$rejects = Item::with ( 'rejection.rejection_reason_info', 'rejection.user', 'rejection.from_station', 'order', 'batch' )
									 ->where ( 'is_deleted', '0' )
									 ->searchStatus ( 'rejected' )
									 ->searchGraphicStatus(4)
									 ->searchRejectionReason($request->get('rejection_reason'))
									 ->searchBatch($request->get('reject_batch'))
									 ->orderBy('id', 'ASC')
									 ->get(); 
									 
				$reject_batches = array();
				
				foreach ($rejects as $reject) {
					$reject_batches[$reject->batch_number][] = $reject;
				}
			}
		}
		
		$count['reject'] = Item::searchStatus ( 'rejected' )
																->searchGraphicStatus(4)
																->where('is_deleted', '0')
																->count();
																					
		if ($tab == 'incompatible') {
			$incompatible = Order::with('customer', 'store', 'items')
										->where('order_status', 15)
										->where('is_deleted', '0')
										->get();
										
			$count['incompatible'] = count($incompatible);

		} else {
			
			$count['incompatible'] = Order::where('order_status', 15)
																	->where('is_deleted', '0')
																	->count();
		}
		
		if ($tab == 'payment') {
			$payment = Order::with('customer', 'items', 'store', 'hold_reason')
										->where('order_status', 13)
										->where('is_deleted', '0')
										->get();
										
			$count['payment'] = count($payment);

		} else {
			
			$count['payment'] = Order::where('order_status', 13)
																	->where('is_deleted', '0')
																	->count();
		}
		
		if ($tab == 'shipping') {
			$shipping = Order::with('customer', 'items', 'store', 'hold_reason', 'wap')
										->whereIn('order_status', [7,12])
										->where('is_deleted', '0')
										->get();
										
			$count['shipping'] = count($shipping);

		} else {
			
			$count['shipping'] = Order::whereIn('order_status', [7,12])
																	->where('is_deleted', '0')
																	->count();
		}
		
		if ($tab == 'other') {
			$other = Order::with('store', 'customer', 'items', 'store', 'hold_reason')
										->where('order_status', 23)
										->where('is_deleted', '0')
										->get();
										
			$count['other'] = count($other);

		} else {
			
			$count['other'] = Order::where('order_status', 23)
																	->where('is_deleted', '0')
																	->count();
		}
		
		if ($tab == 'updates') {
			$updates = Note::with('order.store', 'user')
										->where('note_text', 'LIKE', 'CS:%')
										->limit(100)
										->latest()
										->get();
		}
		
		// $backorders = Item::with('inventoryunit', 'order.notes', 'order.customer')
		//               ->where('item_status', 4)
		//               ->where('items.batch_number', '!=', 0)
		//               ->where('is_deleted', '0')
		//               ->orderBy('item_code')
		//               ->orderBy('id', 'ASC')
		//               ->get();
		
		if ($tab == 'backorder') {
			
			if ($request->has('stock_no_unique')) {
				$backorders = Item::with('order.notes', 'order.customer')
											->join('inventory_unit', 'items.child_sku', '=', 'inventory_unit.child_sku')
											->where('items.item_status', 4)
											->where('items.batch_number', '!=', '0')
											->where('items.is_deleted', '0')
											->where('inventory_unit.stock_no_unique', $request->get('stock_no_unique'))
											->orderBy('items.created_at')
											->get();
				
			} else {
				$bo_summary = Item::join('inventory_unit', 'items.child_sku', '=', 'inventory_unit.child_sku')
											->join('inventories', 'inventory_unit.stock_no_unique', '=', 'inventories.stock_no_unique')
											->join('orders', 'items.order_5p', '=', 'orders.id')
											->where('items.item_status', 4)
											->where('items.is_deleted', '0')
											->selectRaw('inventory_unit.stock_no_unique, inventories.stock_name_discription,
																	MIN(orders.order_date) as min_date,COUNT(items.id) as qty')
											->groupBy('inventory_unit.stock_no_unique')
											->orderBy('min_date')
											->get(); 
			}
		}
		
		$count['backorder'] = Item::where('items.item_status', 4)
																->where('items.batch_number', '!=', '0')
																->where('items.is_deleted', '0')
																->count();
		
		if ($tab == 'reship') {
			$reship = Order::with('customer', 'items')
										->where('order_status', 10)
										->where('is_deleted', '0')
										->get();
										
			$count['reship'] = count($reship);

		} else {

			$count['reship'] = Order::where('order_status', 10)
																->where('is_deleted', '0')
																->count();
		}
		
		$statuses = Order::statuses();
		
		return view('customer_service.index', compact('tab', 'addresses', 'incompatible', 'payment', 'shipping', 'other', 'backorders', 
																									'reject_batches', 'reship', 'updates', 'statuses', 'count', 'bo_summary',
																									'reject_summary', 'reasons'));
	}

	public function ajaxButton(Request $request)
	{
		$order = Order::find($request->get('order_5p'));
		
		if ($order) {
			
			$order->order_status = 4;
			$order->save();
			
			Order::note('Customer Service ' . $request->get('tab') . ' Hold Released ' . $order->order_id, $order->id, $order->order_id);
			
				if ($order && $request->get('action') == 'ignore') {
			
					$customer = Customer::find($order->customer_id);
					
					if ($customer) {
						$customer->ignore_validation = TRUE;
						$customer->save();
						
						Order::note("Ignore address Validation", $order->id, $order->order_id);
					
					} else {
						return 'Customer Not Found';
					}
			}
			return 'Success';
		} else if (!$order) {
			return 'Error: Not Found';
		}
	}
}
