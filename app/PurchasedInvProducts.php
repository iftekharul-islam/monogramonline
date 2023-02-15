<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchasedInvProducts extends Model
{
	protected $table = "purchased_inv_products";

	public function purchasedInvProduct_details ()
	{
		return $this->hasMany('App\Inventory', 'stock_no_unique', 'stock_no')
					->where('is_deleted', '0');
	}
	
	public function vendor ()
	{
		return $this->belongsTo('App\Vendor', 'vendor_id', 'id')
					->where('is_deleted', '0');
	}
	
	public function scopeSearch ($query, $search_for, $search_in)
	{
		if ( $search_in == 'stock_no' ) {
			return $this->scopeSearchInStockNumber($query, $search_for);
		} elseif ( $search_in == 'stock_no_exact' ) {
			return $this->scopeSearchInStockNumber($query, $search_for, 1);
		} elseif ( $search_in == 'vendor_sku' ) {
			return $this->scopeSearchInVendorSKU($query, $search_for);
		} elseif ( $search_in == 'vendor_sku_name' ) {
			return $this->scopeSearchInVendorSKUName($query, $search_for);
		} elseif ( $search_in == 'stock_name' ) {
			return $this->scopeSearchStockDescription($query, $search_for);
		} elseif ( $search_in == 'vendor_name' ) {
			return $this->scopeSearchVendorName($query, $search_for);
		} elseif ( $search_in == 'vendor_id' ) {
			return $this->scopeSearchVendorId($query, $search_for);
		} else {
			return false;
		}
	}
	
	public function scopeSearchVendorId ($query, $id) {
		
		$query->where('vendor_id', $id);
	}
	
	public function scopeSearchVendorName ($query, $name) {
		
		$query->whereHas('vendor', function ($query) use ($name) {
							return $query->where('vendor_name', 'LIKE', sprintf("%%%s%%", $name));
						});
	}
	
	public function scopeSearchStockDescription ($query, $text) {
		
		$query->whereHas('purchasedInvProduct_details', function ($query) use ($text) {
							return $query->where('stock_name_discription', 'LIKE', sprintf("%%%s%%", $text));
						});
	}
	
	public function scopeSearchInStockNumber ($query, $stock_number, $exact = 0)
	{
		$stock_number = trim($stock_number);
		if ( empty($stock_number) ) {
			return false;
		}
		
		if ($exact == 0) {
				return $query->where('stock_no', 'LIKE', sprintf("%%%s%%", $stock_number));
		} else {
				return $query->where('stock_no', $stock_number);
		}
	}

	public function scopeSearchInVendorSKU ($query, $vendor_sku)
	{
		$vendor_sku = trim($vendor_sku);
		if ( empty($vendor_sku) ) {
			return false;
		}

		return $query->where('vendor_sku', 'LIKE', sprintf("%%%s%%", $vendor_sku));
	}

	public function scopeSearchInVendorSKUName ($query, $vendor_sku_name)
	{
		$vendor_sku_name = trim($vendor_sku_name);
		if ( empty($vendor_sku_name) ) {
			return false;
		}

		return $query->where('vendor_sku_name', 'LIKE', sprintf("%%%s%%", $vendor_sku_name));
	}

}
