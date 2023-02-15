<?php

namespace App;

use App\Http\Controllers\InventoryController;
// use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\InventoryUnit;
use Monogram\AppMailer;
use Monogram\Taskable;

class Inventory extends Taskable
{
	protected $table = "inventories";

	public function setLastCostAttribute ($value) 
	{		
			if ($value == null) {
				$value = 0;
			}
			
			$this->attributes['last_cost'] = $value;	
			
			if (isset($this->attributes['qty_on_hand'])) {
				$total = $value * $this->attributes['qty_on_hand'];
				$this->attributes['value'] = ($total > 0) ? $total : 0;
			} else {
				$this->attributes['value'] = 0;
			}
	}
	
	public function setQtyOnHandAttribute ($value) 
	{	
			if ($value == null) {
				$value = 0;
			}
			
			$this->attributes['qty_on_hand'] = $value;	
			
			if (isset($this->attributes['last_cost'])) {
				$total = $value * $this->attributes['last_cost'];
				$this->attributes['value'] = ($total > 0) ? $total : 0;
			} else {
				$this->attributes['value'] = 0;
			}
	}

	public function options ()
	{
		return $this->hasMany('App\Option', 'stock_number', 'stock_no_unique');
	}

	public function inventoryUnitRelation ()
	{
		return $this->hasMany('App\InventoryUnit', 'stock_no_unique', 'stock_no_unique');
	}
	
	public function adjustments ()
	{
		return $this->hasMany('App\InventoryAdjustment', 'stock_no_unique', 'stock_no_unique')->orderBy('created_at', 'DESC');
	}
	
	public function purchase_products ()
	{
		return $this->hasMany('App\PurchaseProduct', 'stock_no', 'stock_no_unique')->latest();
	}
	
	public function last_product ()
	{
		return $this->hasOne('App\PurchasedInvProducts', 'stock_no', 'stock_no_unique')
								->where('is_deleted', '0')
								->latest();
	}
	
	public function qty_user ()
	{
		return $this->belongsTo('App\User', 'qty_user_id', 'id');
	}
	
	public function section ()
	{
		return $this->belongsTo('App\Section', 'section_id', 'id');
	}
	
	// 	public function inventory_details ()
	// 	{
	// 		return $this->hasMany('App\PurchasedInvProducts', 'stock_no_unique', 'stock_no')
	// 					->where('is_deleted', 0);
	// 	}

	private function tableColumns ()
	{
		$columns = $this->getConnection()
						->getSchemaBuilder()
						->getColumnListing($this->getTable());
		$remove_columns = [
			'id',
			'updated_at',
			'created_at',
			'is_deleted',
		];

		return array_diff($columns, $remove_columns);
	}

	public static function getTableColumns ()
	{
		return ( new static() )->tableColumns();
	}

	public function scopeSearchCriteria ($query, $search_for, $search_in, $operator = null)
	{
		
		$search_for = trim($search_for);
		
		if ( $search_for === null && !strpos($operator, 'blank')) {
			return;
		}
		
		if ( in_array($search_in, array_keys(InventoryController::$search_in)) ) {
			
			if ($search_in == 'stock_no_unique' && strpos($search_for, ',') && $operator == 'in') {
					return $query->whereIn($search_in, explode(',', $search_for));
			} else if ($search_in == 'stock_no_unique' && strpos($search_for, ',') && $operator == 'not_in') {
					return $query->whereNotIn($search_in, explode(',', $search_for));
			} 
			
			switch ($operator) {
				case 'in':
					$op = 'LIKE';
					$search_for = sprintf("%%%s%%", $search_for);
					break;
				case 'not_in':
					$op = 'NOT LIKE';
					$search_for = sprintf("%%%s%%", $search_for);
					break;
				case 'starts_with':
					$op = 'LIKE';
					$search_for = sprintf("%s%%", $search_for);
					break;
				case 'ends_with':
					$op = 'LIKE';
					$search_for = sprintf("%%%s", $search_for);
					break;
				case 'equals':
					$op = '=';
					// $search_for = $search_for;
					break;
				case 'not_equals':
					$op = '!=';
					// $search_for = $search_for;
					break;
				case 'less_than':
					$op = '<';
					// $search_for = $search_for;
					break;
				case 'greater_than':
					$op = '>';
					// $search_for = $search_for;
					break;
				case 'blank':
					return $this->findBlanks($query, $search_in, 0);
					break;
				case 'not_blank':
					return $this->findBlanks($query, $search_in, 1);
					break;
				default:
					$op = 'LIKE';
					$search_for = sprintf("%%%s%%", $search_for);
					break;
			}
			
			if ( 'child_sku' == $search_in ) {
				
				return $query->whereHas('inventoryUnitRelation', function ($query) use ($search_for, $op) {
						$query->where('child_sku', $op, $search_for);
				});
				
			} else {
				
				return $query->where($search_in, $op, $search_for);
				
			} 
			
		}
		
		return;
	}
	
	private function findBlanks ($query, $search_in, $flag = 0) {
		
		if ( $search_in == 'stock_no_unique'  && $flag == 0 ) {
			
			return $query->where('stock_no_unique', '');
			
		} elseif ( 'stock_name_description' == $search_in && $flag == 0 ) {
			
			return $query->whereNull('stock_name_discription');
		
		} elseif ( 'wh_bin' == $search_in && $flag == 0 ) {
			
			return $query->where('wh_bin', '');
		
		} elseif ( 'qty_on_hand' == $search_in && $flag == 0 ) {
			
			return $query->where('qty_on_hand', -1);
		
		} elseif ( 'last_cost' == $search_in && $flag == 0 ) {
			
			return $query->where('last_cost', 0.00);
			
		} elseif ( 'child_sku' == $search_in && $flag == 0 ) {
			
			return $query->doesntHave('inventoryUnitRelation');
			
		} else if ( $search_in == 'stock_no_unique'  && $flag == 1 ) {
			
			return $query->where('stock_no_unique', '!=',  '');
			
		} elseif ( 'stock_name_description' == $search_in && $flag == 1 ) {
			
			return $query->whereNotNull('stock_name_discription');
		
		} elseif ( 'wh_bin' == $search_in && $flag == 1 ) {
			
			return $query->where('wh_bin', '!=', '');
			
		} elseif ( 'qty_on_hand' == $search_in && $flag == 1) {
			
			return $query->where('qty_on_hand', '!=', -1);
		
		} elseif ( 'last_cost' == $search_in && $flag == 1 ) {
			
			return $query->where('last_cost', '>', 0);
			
		} elseif ( 'child_sku' == $search_in && $flag == 1 ) {
			
			return $query->has('inventoryUnitRelation');
		}
	}
	
	public function scopeSearchSection ($query, $section) 
	{
		if ($section == '' || null === $section || $section == []) {
			return;
		}
		
		if ($section == 'blank') {
			return $query->doesntHave('section')
										->orWhere('section_id', '0')
										->orWhereNull('section_id');
		}
		
		if (is_array($section)) {
			return $query->whereIn('section_id', $section);
		}
		
		return $query->where('section_id', $section);
	}
	
	public function scopeSearchVendor ($query, $vendor) 
	{
		if ($vendor == '') {
			return;
		}
		
		if ($vendor == 'blank') {
			return $query->doesntHave('last_product');
		}
		
		return $query->whereHas('last_product', function($q) use ($vendor) {
									return $q->where('vendor_id', $vendor);
							});
	}
	
	/*
	 * Call this for save / update in inventory_unit Table
	 */
	public static function saveinventoryUnit($child_sku, $stock_no_unique, $unit_qty){
		
		$inventoryUnit = InventoryUnit::where('child_sku', $child_sku)
									 ->where('stock_no_unique', $stock_no_unique)
									 ->first();
		// If not found
		if ( ! $inventoryUnit ) {
			// Insert new inventory record in inventory table
			$inventoryUnit = new InventoryUnit();
			$inventoryUnit->child_sku = $child_sku;
			$inventoryUnit->stock_no_unique = $stock_no_unique;
			$inventoryUnit->unit_qty = $unit_qty;
			$inventoryUnit->save();
		} else {
			$inventoryUnit->unit_qty = $unit_qty;
			$inventoryUnit->save();
		}

	}
	
	public function outputArray() 
	{
		return [  'App\Inventory',
							$this->id, 
							url(sprintf('inventories?search_for_first=%s&operator_first=equals&search_in_first=stock_no_unique', $this->stock_no_unique)),
							'Stock Number: ' . $this->stock_no_unique,
							$this->stock_name_discription,
							null
						];
	}
}
