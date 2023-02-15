<?php

namespace App;

// use Illuminate\Database\Eloquent\Model;
use Monogram\Taskable;

class Purchase extends Taskable
{

	public function products ()
	{
		return $this->hasMany('App\PurchaseProduct', 'purchase_id', 'po_number');
					// ->where('is_deleted', '0');
	}

	public function vendor_details ()
	{
		return $this->belongsTo('App\Vendor', 'vendor_id', 'id');
					// ->where('is_deleted', '0');
	}

	public function scopeSearch ($query, $search_for, $search_in)
	{
		if ( $search_in == "purchase_number" ) {
			return $this->scopeSearchInPurchaseNumber($query, $search_for);
		} elseif ( 'purchase_order_status' == $search_in ) {
			$option = strtolower($search_for);
			if ( in_array($option, [ 'open', 'close', 'partial', ]) ) {
				if ( 'open' == $option ) {
					return $this->scopeSearchInPurchaseOrderStatus($query, 1);
				} elseif ('partial' == $option){
					return $this->scopeSearchInPurchaseOrderStatus($query, 2);
				} elseif ('close' == $option){
					return $this->scopeSearchInPurchaseOrderStatus($query, 3);
				}
			}
		}

		return;
	}

	public function scopeSearchInPurchaseNumber ($query, $search_for)
	{
		$search_for = trim($search_for);
		if ( empty($search_for) ) {
			return false;
		}

		return $query->where('po_number', 'LIKE', sprintf('%%%s%%', $search_for));
	}

	public function scopeSearchInPurchaseOrderStatus ($query, $status)
	{
		$status = (int) $status;
		if ( $status ) {
			return $query->where('o_status', $status);
		}
	}
	
	public function outputArray() 
	{
		return [  'App\Purchase',
							$this->id, 
							url(sprintf('purchases/%s', $this->po_number)),
							'Purchase Order: ' . $this->po_number,
							$this->po_date,
							$this->vendor_details->vendor_name
						];
	}
}
