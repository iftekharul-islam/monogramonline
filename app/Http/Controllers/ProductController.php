<?php 

namespace App\Http\Controllers;

use App\Manufacture;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductAddRequest;
use App\Http\Requests\ProductUpdateRequest;
use Illuminate\Support\Facades\Log;
use App\Product;
use App\Item;
use App\ProductionCategory;
use App\Specification;
use Monogram\Crawler;

class ProductController extends Controller
{
	private $store_id = null;

	public function index (Request $request)
	{
		$products = Product::with('manufacture')->where('is_deleted', 0)
						   ->searchInOption($request->get('search_in'), $request->get("search_for"))
						   ->searchProductionCategory($request->get('product_production_category'))
						   ->latest()
						   ->paginate(50);

		$production_categories = ProductionCategory::where('is_deleted', 0)
													 ->get()
												   ->pluck('production_category_description', 'id')
												   ->prepend('All', 0);
													 
		$count = 1;

		return view('products.index', compact('products', 'count', 'request', 'production_categories'));
	}

	public function create ()
	{

		$production_categories = ProductionCategory::where('is_deleted', 0)
													 ->get()
												   ->pluck('production_category_description', 'id')
												   ->prepend('Select production category', '');
		

		return view('products.create', compact('production_categories'));
	}

	public function store (ProductAddRequest $request)
	{

		$id_catalog = trim($request->get('id_catalog'));
		$product_model = trim($request->get('product_model'));
		$checkExisting = Product::where('id_catalog', $id_catalog)
								->orWhere('product_model', $product_model)
								->first();
		if ( $checkExisting ) {
			return redirect()
				->back()
				->withInput()
				->withErrors([
					'error' => 'Product already exists either with id catalog or model',
				]);
		}

		$product = new Product();
		$product->id_catalog = $id_catalog;
		$product->product_model = $product_model;
		if ( $request->exists('product_upc') ) {
			$product->product_upc = trim($request->get('product_upc'));
		}
		if ( $request->exists('product_asin') ) {
			$product->product_asin = trim($request->get('product_asin'));
		}
		if ( $request->exists('product_default_cost') ) {
			$product->product_default_cost = intval($request->get('product_default_cost'));
		}
		if ( $request->exists('product_url') ) {
			$product->product_url = $request->get('product_url');
		}
		if ( $request->exists('product_name') ) {
			$product->product_name = trim($request->get('product_name'));
		}
		if ( $request->exists('ship_weight') ) {
			$product->ship_weight = floatval($request->get('ship_weight'));
		}
		if ( $request->exists('product_production_category') ) {
			$product->product_production_category = intval($request->get('product_production_category'));
		}
		if ( $request->exists('product_price') ) {
			$product->product_price = floatval($request->get('product_price'));
		}
		if ( $request->exists('product_sale_price') ) {
			$product->product_sale_price = floatval($request->get('product_sale_price'));
		}
		if ( $request->exists('product_wholesale_price') ) {
			$product->product_wholesale_price = $request->get('product_wholesale_price');
		}
		if ( $request->exists('product_thumb') ) {
			$product->product_thumb = $request->get('product_thumb');
		}
		if ( $request->exists('product_description') ) {
			$product->product_description = trim($request->get('product_description'));
		}
		if ( $request->exists('height') ) {
			$product->height = floatval($request->get('height'));
		}
		if ( $request->exists('width') ) {
			$product->width = floatval($request->get('width'));
		}
		$product->save();
		
		return redirect(url('products'))->withSuccess('Product is successfully added.');
	}

	public function show ($id)
	{
		// if searching for inactive or deleted product
		$product = Product::with('production_category', 'manufacture')
						  ->where('is_deleted', 0)
						  ->find($id);
		if ( !$product ) {
			return redirect()->back()->withInput()->withErrors('Product Not Found');
		}

		#return $product;

        $note = "";
		if(strlen($product->product_note) != 0) {
		    $note = explode("@", $product->product_note)[0];
        }

		return view('products.show', compact('product', 'note'));
	}

	public function edit ($id)
	{
		$product = Product::with('specifications')
						  ->where('is_deleted', 0)
						  ->find($id);

		if ( !$product ) {
			return redirect()->back()->withErrors('Product Not Found');
		}
		
		$production_categories = ProductionCategory::where('is_deleted', 0)
													 ->get()
												   ->pluck('production_category_description', 'id')
												   ->prepend('Select production category', '');

        $manufactures = Manufacture::get()->pluck('name', 'id');

		return view('products.edit', compact('product', 'production_categories', 'manufactures'));
	}

	public function update (ProductUpdateRequest $request, $id)
	{ 
		$product = Product::where('is_deleted', 0)
						  ->find($id);
		if ( !$product ) {
			return redirect()->back()->withErrors('Product Not Found');
		}

        if ( $request->exists('manufacture_id') ) {
            $product->manufacture_id = $request->get('manufacture_id');
        }

		if ( $request->exists('id_catalog') ) {
			$product->id_catalog = $request->get('id_catalog');
		}
		if ( $request->exists('product_url') ) {
			$product->product_url = $request->get('product_url');
		}
		if ( $request->exists('product_name') ) {
			$product->product_name = trim($request->get('product_name'));
		}
		if ( $request->exists('ship_weight') ) {
			$product->ship_weight = floatval($request->get('ship_weight'));
		}
		if ( $request->exists('product_upc') ) {
			$product->product_upc = trim($request->get('product_upc'));
		}
		if ( $request->exists('product_asin') ) {
			$product->product_asin = trim($request->get('product_asin'));
		}
		if ( $request->exists('product_wholesale_price') ) {
			$product->product_wholesale_price = $request->get('product_wholesale_price');
		}
		if ( $request->exists('product_production_category') ) {
			$product->product_production_category = intval($request->get('product_production_category'));
		}
		if ( $request->exists('product_price') ) {
			$product->product_price = floatval($request->get('product_price'));
		}
		if ( $request->exists('product_sale_price') ) {
			$product->product_sale_price = floatval($request->get('product_sale_price'));
		}
		if ( $request->exists('product_thumb') ) {
			$product->product_thumb = $request->get('product_thumb');
		}
		if ( $request->exists('product_description') ) {
			$product->product_description = trim($request->get('product_description'));
		}
		if ( $request->exists('height') ) {
			$product->height = floatval($request->get('height'));
		}
		if ( $request->exists('width') ) {
			$product->width = floatval($request->get('width'));
		}


		$desc = $request->get("product_note") . "@" . $request->get("msg_flag");
        $product->product_note = $desc;

		$product->save();
		return redirect()->action('ProductController@show', ['id' => $product->id])
											->withSuccess('Product Updated');
	}
	
	public function ajaxSearch(Request $request) 
	{ 	
		$searchAble = sprintf("%%%s%%", str_replace(' ', '%', trim($request->get('query'))));
		
		$product = Product::where('product_model', "LIKE", $searchAble)
							->orWhere('id_catalog', 'LIKE', $searchAble)
							->orWhere('product_name', 'LIKE', $searchAble)
							->where('is_deleted', 0)
							->selectRaw('id_catalog, product_model, product_name, product_thumb, product_price')
							->get();
		
		$result = array();
		
		foreach ($product as $model) {
			$result[] = [
										'value' => $model->product_model . ' - ' . $model->product_name, 
										'data' => $model->product_model,
										'desc' => $model->product_name, 
										'image' => $model->product_thumb,
										'price' => $model->product_price,
										'id_catalog' => $model->id_catalog
									];
		}

		return response()->json([
			"suggestions" => $result,
		], 200); 
	}
	
	public function product_info (Request $request)
	{
		$sku = $request->get('sku');
		$store_id = $request->get('store_id');
		$id_catalog = $request->get('id_catalog');
		$unique = $request->get('unique');
		
		if ( empty($store_id) ) {
			return response()->json([], 400);
		}
		
		$json = Crawler::getJSON($id_catalog);
		
		$crawled_data = json_decode($json, true); 

		$data = [];
		$data['id_catalog'] = $id_catalog;
		$data['sku'] = $sku;
		$data['result'] = [];
		$statusCode = 200;
		if ( ! is_array($crawled_data) ) {
			$statusCode = 400;
			$data['result'] = false;
		} else {
			$data['unique'] = $unique;
			$data['result'] = view('orders.product_data_generator')
				->with('crawled_data', $crawled_data)
				->with('id_catalog', $id_catalog)
				->with('sku', $sku)
				->with('unique', $unique)
				->render();
		}

		return response()->json($data, $statusCode);
	}

	public function destroy ($id)
	{
		$product = Product::where('is_deleted', 0)
						  ->find($id);
		if ( !$product ) {
			return redirect()->back()->withInput()->withErrors('Product Not Found');
		}

		$product->is_deleted = 1;
		$product->save();

		return redirect(url('products'));
	}
	
	public function download_images() {
		
		set_time_limit(0);
		
		$products = Product::select('id', 'product_model', 'product_thumb')
													->where('product_thumb', 'NOT LIKE', 'http://order.monogramonline.com%')
													->get(); 
		
		foreach ($products as $product) {
						
			if ($product->product_thumb != null) {
			
				try {
					
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_URL,$product->thumb);
						curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1); 
						curl_setopt($ch, CURLOPT_NOBODY, 1);
						curl_setopt($ch, CURLOPT_FAILONERROR, 1);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
						curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);     
						curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
						$result = curl_exec($ch);
						curl_close($ch);
						
				} catch (\Exception $e) {
						//
				}
				
			}
			
			if (!isset($result) || $result == FALSE) {
				
				$url = 'https://www.monogramonline.com/imageurl?sku=' . trim($product->product_model);
        
        $attempts = 0;
      
        do {
      
          try {
            
            $ch = curl_init();
            curl_setopt($ch,CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);     
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)');
            $json=curl_exec($ch);
            
            curl_close($ch);
            
            // $json = file_get_contents($url);
          } catch (\Exception $e) {
            echo $e->getMessage();
            $attempts++;
            Log::info('Magento item thumb: Error ' . $e->getMessage() . ' - attempt # ' . $attempts);
            sleep(2);
            continue;
          }
      
          break;
      
        } while($attempts < 5);
      
        if ($attempts == 5) {
          
          Log::info('Magento item thumb: Failure, SKU ' . $product->product_model);
          continue;
					
        } else {
          
          $decoded = json_decode(substr($json, 5, -6), true);

          if (isset($decoded['image_url']) && $decoded['image_url'] != "Image Not found") {
            $thumb = $decoded['image_url'];
          } else {
						$item = Item::where('item_code', $product->product_model)
														->whereNotNull('item_thumb')
														->select('item_thumb')
														->latest()
														->first();
						if ($item) {
							$thumb = $item->item_thumb;
						} else {
							continue;
						}
					}
        }
			} else {
				$thumb = $product->product_thumb;
			}
				

			$img = '/assets/images/product_thumb/' . $product->product_model . substr($thumb, -4);
			
			try {	
				
				if (file_exists(base_path() . '/public_html' . $img) && filesize(base_path() . '/public_html' . $img) > 0) {
					unlink(base_path() . '/public_html' . $img);
				}
				
				if (!file_exists(base_path() . '/public_html' . $img)) {
					@file_put_contents(base_path() . '/public_html' . $img, file_get_contents($thumb));
				}
				
				if (file_exists(base_path() . '/public_html' . $img) && filesize(base_path() . '/public_html' . $img) > 0) {
					$product->product_thumb = 'http://order.monogramonline.com' . $img;
					$product->save();
				} elseif (file_exists(base_path() . '/public_html' . $img)) {
					unlink(base_path() . '/public_html' . $img);
				}
				
				
			} catch (\Exception $e) {
				Log::info($product->product_model . ' - ' . $e->getMessage());
			}
			
		}
		
		return;
	}

}
