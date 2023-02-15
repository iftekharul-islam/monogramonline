<?php namespace App;

// use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Monogram\Taskable;

class Product extends Taskable
{
	protected $fillable = [ '*' ];
	protected $hidden = [
		'updated_at',
		'created_at',
	];
	private $data_set = [ ];
	public static $searchable_fields = [
		'id_catalog'             => 'ID Catalog',
		'product_model'          => 'SKU',
		'product_name'           => 'Name',
		// 'product_sales_category' => 'Sales category',
		// 'product_price'          => 'Price',
		// 'product_sale_price'     => 'Sale price',
		// 'product_ship_weight'    => 'Ship Weight',
		// 'product_note'           => 'Note',
		// 'product_keywords'       => 'Keywords',
		'product_description'    => 'Description',
		// 'product_brand'          => 'Brand',
		// 'product_availability'   => 'Availability',
		// 'product_default_cost'   => 'Default cost',
		// 'product_video'          => 'Video',
		// 'height'                 => 'Height',
		// 'width'                  => 'Width',
		// 'product_headline'       => 'Headline',
		// 'product_caption'        => 'Caption',
		// 'product_label'          => 'Label',
	];
	// public static $mixingStatues = [
	// 	0 => "No",
	// 	1 => "Yes",
	// ];

	private function tableColumns ()
	{
		$columns = $this->getConnection()
						->getSchemaBuilder()
						->getColumnListing($this->getTable());
		$remove_columns = [
			'updated_at',
			'created_at',
			'id',
			'product_category',
			'product_sub_category',
		];

		return array_diff($columns, $remove_columns);
	}

	public function getDefaultColumnName ($columnName)
	{
		// solution comes from here
		// https://laracasts.com/discuss/channels/eloquent/get-default-value?page=1
		$query = 'SELECT COLUMN_DEFAULT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "' . $this->getTable() . '" AND COLUMN_NAME = "' . $columnName . '"';
		// will return "two"
		$result = DB::select($query);
		$default = null;
		if ( count($result) > 1 ) {
			$default = array_pluck($result, 'COLUMN_DEFAULT')[1];
		} else {
			$default = array_pluck($result, 'COLUMN_DEFAULT')[0];
		}

		return $default;
	}

	public static function getTableColumns ()
	{
		return (new static())->tableColumns();
	}

	// public function images ()
	// {
	// 	return $this->hasMany('App\Image', 'product_id', 'id');
	// }
	
// 	public function batch_route ()
// 	{
// 		return $this->hasOne('App\BatchRoute', 'id', 'batch_route_id');
// 	}

	// public function master_category ()
	// {
	// 	return $this->belongsTo('App\MasterCategory', 'product_master_category', 'id');
	// }

	public function options ()
	{
		return $this->hasMany('App\Option', 'parent_sku', 'product_model');
	}

	// public function vendor ()
	// {
	// 	return $this->belongsTo('App\Vendor', 'vendor_id', 'id');
	// }

	// public function category ()
	// {
	// 	return $this->belongsTo('App\Category', 'product_category', 'id');
	// }
	// 
	// public function sub_category ()
	// {
	// 	return $this->belongsTo('App\SubCategory', 'product_sub_category', 'id');
	// }

	public function production_category ()
	{
		return $this->belongsTo('App\ProductionCategory', 'product_production_category', 'id');
	}

	// public function sales_category ()
	// {
	// 	return $this->belongsTo('App\SalesCategory', 'product_sales_category', 'id');
	// }
	// 
	// public function product_occasion_details ()
	// {
	// 	return $this->belongsTo('App\Occasion', 'product_occasion', 'id')
	// 				->where('is_deleted', 0);
	// }
	// 
	// public function occasions ()
	// {
	// 	return $this->belongsToMany('App\Occasion')
	// 				->withTimestamps();
	// }
	// 
	// public function collections ()
	// {
	// 	return $this->belongsToMany('App\Collection')
	// 				->withTimestamps();
	// }

	public function specifications ()
	{
		return $this->hasOne('App\Specification', 'product_id', 'id');
	}

	// public function magento ()
	// {
	// 	return $this->belongsTo('App\Magento', 'id_catalog', 'id_catalog');
	// }
	// 
	// public function product_collection_details ()
	// {
	// 	return $this->belongsTo('App\Collection', 'product_collection', 'id')
	// 				->where('is_deleted', 0);
	// }

	public function groupedItems ()
	{
		/*return $this->hasMany('App\Item', 'item_id', 'id_catalog')
					->whereNull('batch_number')
					->where('is_deleted', 0)
					->select([
						'id',
						'item_id',
						'order_id',
					]);*/
		return $this->hasMany('App\Item', 'item_code', 'product_model')
					->whereNull('batch_number')
					->where('is_deleted', 0)
					->select([
						'id',
						'item_id',
						'item_code',
						'order_id',
					]);
	}

	private function recursion ($categories)
	{
		$this->data_set = array_merge($this->data_set, $categories->pluck('id')
																  ->toArray());
		foreach ( $categories as $category ) {
			$child_count = $category->children()
									->count();

			if ( $child_count > 0 ) {
				$this->recursion($category->children()
										  ->get());
			}
		}
	}

	private function trimmer ($haystack, $needle)
	{
		if ( is_array($haystack) ) {
			return array_diff($haystack, [ $needle ]);
		}

		return [ ];
	}

	public function scopeSearchInOption ($query, $search_in, $search_for)
	{

		// search in field means, in which field you want to search
		if ( $search_in && in_array($search_in, array_keys(static::$searchable_fields)) ) {
			if ( $search_in == 'id_catalog' ) {
				return $this->scopeSearchIdCatalog($query, $search_for);
			} elseif ( $search_in == 'product_model' ) {
				return $this->scopeSearchProductModel($query, $search_for);
			} elseif ( $search_in == 'product_name' ) {
				return $this->scopeSearchProductName($query, $search_for);
			// } elseif ( $search_in == 'product_sales_category' ) {
			// 	return $this->scopeSearchProductSalesCategory($query, $search_for);
			// } elseif ( $search_in == 'product_price' ) {
			// 	return $this->scopeSearchProductPrice($query, $search_for);
			// } elseif ( $search_in == 'product_sale_price' ) {
			// 	return $this->scopeSearchProductSalePrice($query, $search_for);
			// } elseif ( $search_in == 'product_ship_weight' ) {
			// 	return $this->scopeSearchProductShipWeight($query, $search_for);
			// } elseif ( $search_in == 'product_keywords' ) {
			// 	return $this->scopeSearchProductKeywords($query, $search_for);
			} elseif ( $search_in == 'product_description' ) {
				return $this->scopeSearchProductDescription($query, $search_for);
			// } elseif ( $search_in == 'product_brand' ) {
			// 	return $this->scopeSearchProductBrand($query, $search_for);
			// } elseif ( $search_in == 'product_availability' ) {
			// 	return $this->scopeSearchProductAvailability($query, $search_for);
			// } elseif ( $search_in == 'product_default_cost' ) {
			// 	return $this->scopeSearchProductDefaultCost($query, $search_for);
			// } elseif ( $search_in == 'product_video' ) {
			// 	return $this->scopeSearchProductVideo($query, $search_for);
			// } elseif ( $search_in == 'height' ) {
			// 	return $this->scopeSearchProductHeight($query, $search_for);
			// } elseif ( $search_in == 'width' ) {
			// 	return $this->scopeSearchProductWidth($query, $search_for);
			// } elseif ( $search_in == 'product_headline' ) {
			// 	return $this->scopeSearchProductHeadline($query, $search_for);
			// } elseif ( $search_in == 'product_caption' ) {
			// 	return $this->scopeSearchProductCaption($query, $search_for);
			// } elseif ( $search_in == 'product_label' ) {
			// 	return $this->scopeSearchProductLabel($query, $search_for);
			// } elseif ( $search_in == 'product_note' ) {
			// 	return $this->scopeSearchProductNote($query, $search_for);
			}
		}

		return;
	}

	// public function scopeSearchProductNote ($query, $note)
	// {
	// 	$note = trim($note);
	// 	if ( empty( $note ) ) {
	// 		return;
	// 	}
	// 
	// 	return $query->where('product_note', "LIKE", sprintf("%%%s%%", $note));
	// }
	// 
	// public function scopeSearchProductLabel ($query, $label)
	// {
	// 	$label = trim($label);
	// 	if ( empty( $label ) ) {
	// 		return;
	// 	}
	// 
	// 	return $query->where('product_label', "LIKE", sprintf("%%%s%%", $label));
	// }
	// 
	// public function scopeSearchProductCaption ($query, $caption)
	// {
	// 	$caption = trim($caption);
	// 	if ( empty( $caption ) ) {
	// 		return;
	// 	}
	// 
	// 	return $query->where('product_caption', "LIKE", sprintf("%%%s%%", $caption));
	// }
	// 
	// public function scopeSearchProductHeadline ($query, $headline)
	// {
	// 	$headline = trim($headline);
	// 	if ( empty( $headline ) ) {
	// 		return;
	// 	}
	// 
	// 	return $query->where('product_headline', "LIKE", sprintf("%%%s%%", $headline));
	// }
	// 
	// public function scopeSearchProductHeight ($query, $height)
	// {
	// 	if ( !$height ) {
	// 		return;
	// 	}
	// 
	// 	return $query->where('height', floatval($height));
	// }
	// 
	// public function scopeSearchProductWidth ($query, $width)
	// {
	// 	if ( !$width ) {
	// 		return;
	// 	}
	// 
	// 	return $query->where('width', floatval($width));
	// }
	// 
	// public function scopeSearchProductVideo ($query, $video_link)
	// {
	// 	$video_link = trim($video_link);
	// 	if ( empty( $video_link ) ) {
	// 		return;
	// 	}
	// 
	// 	return $query->where('product_video', "LIKE", sprintf("%%%s%%", $video_link));
	// }
	// 
	// public function scopeSearchProductAvailability ($query, $term)
	// {
	// 	$term = trim($term);
	// 	if ( empty( $term ) ) {
	// 		return;
	// 	}
	// 
	// 	return $query->where('product_availability', "LIKE", sprintf("%%%s%%", $term));
	// }
	// 
	// public function scopeSearchProductBrand ($query, $brand)
	// {
	// 	$brand = trim($brand);
	// 	if ( empty( $brand ) ) {
	// 		return;
	// 	}
	// 
	// 	return $query->where('product_brand', "LIKE", sprintf("%%%s%%", $brand));
	// }

	public function scopeSearchProductDescription ($query, $description)
	{
		$description = trim($description);
		if ( empty( $description ) ) {
			return;
		}

		return $query->where('product_description', "LIKE", sprintf("%%%s%%", $description));
	}

	// public function scopeSearchProductKeywords ($query, $keyword)
	// {
	// 	$keyword = trim($keyword);
	// 	if ( empty( $keyword ) ) {
	// 		return;
	// 	}
	// 
	// 	return $query->where('product_keywords', "LIKE", sprintf("%%%s%%", $keyword));
	// }
	// 
	// public function scopeSearchProductDefaultCost ($query, $cost)
	// {
	// 	if ( !$cost ) {
	// 		return;
	// 	}
	// 
	// 	return $query->where('product_default_cost', floatval($cost));
	// }
	// 
	// public function scopeSearchProductShipWeight ($query, $weight)
	// {
	// 	if ( !$weight ) {
	// 		return;
	// 	}
	// 
	// 	return $query->where('ship_weight', floatval($weight));
	// }
	// 
	// public function scopeSearchProductSalePrice ($query, $price)
	// {
	// 	if ( !$price ) {
	// 		return;
	// 	}
	// 
	// 	return $query->where('product_sale_price', floatval($price));
	// }
	// 
	// public function scopeSearchProductPrice ($query, $price)
	// {
	// 	if ( !$price ) {
	// 		return;
	// 	}
	// 
	// 	return $query->where('product_price', floatval($price));
	// }
	// 
	// public function scopeSearchProductSalesCategory ($query, $sales_category)
	// {
	// 	/*if ( !$sales_category ) {
	// 		return;
	// 	}
	// 	$replaced = str_replace(" ", "", $sales_category);
	// 	$values = explode(",", trim($replaced, ","));*/
	// 	if ( !$sales_category || $sales_category == 'all' ) {
	// 		return;
	// 	}
	// 
	// 	$sales_categories = SalesCategory::where('sales_category_code', $sales_category)
	// 									 ->get()
	// 									 ->pluck('id');
	// 
	// 	//->toArray();
	// 
	// 	return $query->whereIn('product_sales_category', $sales_categories);
	// }

	public function scopeSearchIdCatalog ($query, $id_catalog)
	{
		if ( !$id_catalog ) {
			return;
		}
		$replaced = str_replace(" ", "", $id_catalog);
		$values = explode(",", trim($replaced, ","));

		return $query->where('id_catalog', 'REGEXP', implode("|", $values));
	}

	public function scopeSearchProductModel ($query, $product_model)
	{
		if ( !$product_model ) {
			return;
		}
		$replaced = str_replace(" ", "", $product_model);
		$values = explode(",", trim($replaced, ","));

		return $query->where('product_model', 'REGEXP', implode("|", $values));
	}

	public function scopeSearchProductName ($query, $product_name)
	{
		if ( !$product_name ) {
			return;
		}
		$product_name = trim($product_name);

		return $query->where('product_name', 'LIKE', sprintf("%%%s%%", $product_name));
	}

// 	public function scopeSearchRoute ($query, $route_ids)
// 	{

// 		if ( !$route_ids || !is_array($route_ids) ) {
// 			return;
// 		}
// 		$stripped_values = $this->trimmer($route_ids, 0);
// 		if ( count($stripped_values) == 0 ) {
// 			return;
// 		}

// 		return $query->whereIn('batch_route_id', $stripped_values);
// 	}

	// public function scopeSearchMasterCategory ($query, $product_master_category_id)
	// {
	// 	if ( !$product_master_category_id || $product_master_category_id == 'all' ) {
	// 		return;
	// 	}
	// 	$this->data_set[] = $product_master_category_id;
	// 
	// 	$categories = MasterCategory::where('parent', $product_master_category_id)
	// 								->get();
	// 
	// 	$this->recursion($categories);
	// 
	// 	$category_id_list = $this->data_set;
	// 
	// 	#dd($category_id_list);
	// 	return $query->whereIn('product_master_category', $category_id_list);
	// 
	// 	#return $query->where('product_master_category', $product_master_category);
	// }
	// 
	// public function scopeSearchCategory ($query, $product_category)
	// {
	// 	if ( !$product_category || $product_category == 'all' ) {
	// 		return;
	// 	}
	// 
	// 	return $query->where('product_category', $product_category);
	// }
	// 
	// public function scopeSearchSubCategory ($query, $sub_category)
	// {
	// 	if ( !$sub_category || $sub_category == 'all' ) {
	// 		return;
	// 	}
	// 
	// 	return $query->where('product_sub_category', $sub_category);
	// }

	public function scopeSearchProductionCategory ($query, $production_category)
	{
		if ( !$production_category || !is_array($production_category) ) {
			return;
		}
		$stripped_values = $this->trimmer($production_category, 'all');

		if ( count($stripped_values) == 0 || (count($stripped_values) == 1 && $stripped_values[0] == '0')) {
			return;
		}

		return $query->whereIn('product_production_category', $stripped_values);
		/*if ( !$production_category || $production_category == 'all' ) {
			return;
		}
		return $query->where('product_production_category', intval($production_category));*/
	}

	// public function scopeSearchProductCollection ($query, $product_collection_id)
	// {
	// 	if ( !$product_collection_id || !is_array($product_collection_id) ) {
	// 		return;
	// 	}
	// 	$stripped_values = $this->trimmer($product_collection_id, 0);
	// 
	// 	if ( count($stripped_values) == 0 ) {
	// 		return;
	// 	}
	// 	$product_ids = DB::table('collection_product')
	// 					 ->whereIn('collection_id', $stripped_values)
	// 					 ->get()
	// 					 ->pluck('product_id');
	// 
	// 	return $query->whereIn('id', $product_ids);
	// }
	// 
	// public function scopeSearchProductOccasion ($query, $product_occasion_id)
	// {
	// 	if ( !$product_occasion_id || !is_array($product_occasion_id) ) {
	// 		return;
	// 	}
	// 	$stripped_values = $this->trimmer($product_occasion_id, 0);
	// 
	// 	if ( count($stripped_values) == 0 ) {
	// 		return;
	// 	}
	// 
	// 	$product_ids = DB::table('occasion_product')
	// 					 ->whereIn('occasion_id', $product_occasion_id)
	// 					 ->get()
	// 					 ->pluck('product_id');
	// 
	// 	return $query->whereIn('id', $product_ids);
	// 	#return $query->where('product_occasion', intval($product_occasion_id));*/
	// }
	
	public function outputArray() 
	{
		return [  'App\Product',
							$this->id, 
							url(sprintf('products/%s/edit', $this->id)),
							'Product: ' . $this->product_model,
							$this->product_name,
							null
						];
	}
}
