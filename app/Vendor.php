<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
	//
	public function scopeSearchVendorName ($query, $vendor_name)
	{
		$vendor_name = trim($vendor_name);
		
		if ( empty($vendor_name) ) {
			return false;
		}

		return $query->where('vendor_name', 'LIKE', sprintf('%%%s%%', $vendor_name))
					 ->where('is_deleted', 0);
	}
	
	public function scopeSearch ($query, $search_in, $search_for)
	{
		$value = trim($search_for);
		
		if ( empty($search_for) ) {
			return false;
		}

		return $query->where($search_in, 'LIKE',  sprintf('%%%s%%', $search_for))
					 ->where('is_deleted', 0);
	}
}
