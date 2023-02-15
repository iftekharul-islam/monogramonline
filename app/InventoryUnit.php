<?php

namespace App;

use App\Http\Controllers\InventoryUnitController;
use Illuminate\Database\Eloquent\Model;

class InventoryUnit extends Model
{
    //
	protected $table = "inventory_unit";
	
	public function inventory ()
	{
		return $this->belongsTo('App\Inventory', 'stock_no_unique', 'stock_no_unique');
	}
	
	public function items ()
	{
		return $this->hasMany('App\Item', 'child_sku', 'child_sku');
	}
	
	public function options ()
	{
		return $this->hasMany('App\Option', 'child_sku', 'child_sku');
	}
	
	public function open_po()
	{
		return $this->hasOne('App\PurchaseProduct', 'stock_no', 'stock_no_unique')
									->where('balance_quantity', '>', 0)
									->where('is_deleted', '0')
									->oldest();
	}
	
}
