<?php 
 
namespace Monogram; 
 
use App\Option; 
use App\InventoryUnit; 
use App\Batch;
use App\BatchRoute;
use App\Walmart_EDI; 
use App\Order;
use App\Item;
use App\StoreItem;
use App\Note;
use App\Customer;
use App\Parameter;
use App\Product;
use App\ProductOption;
use App\ProductOptionValue;
use App\SpecificationSheet;
use App\Rejection;
use App\InventoryAdjustment;
use App\Inventory;
use App\Design;
use App\Wap;
use App\WapItem;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\DB;
use Monogram\CSV;
use Excel;
use Monogram\Sure3d;
use Monogram\Helper;
use App\AmazonFC;
use App\Http\Controllers\GraphicsController;
use Market\CommerceHub;

class Fixup 
{ 
  public function fixJCP () {
    
    $c = new CommerceHub;
    
    $items = Item::where('store_id', 'jcp-01')->where('is_deleted','0')->get();
    
    $store_items = StoreItem::where('store_id', 'jcp-01')->get();
    
    foreach ($items as $item) {
      $ex = explode(':', $item->edi_id);
    
      $item->item_unit_price = 0;
      
      if (isset($ex[0])) {
        $store_item = $store_items->where('vendor_sku', $ex[0])->first();
        if ($store_item) {
          $item->item_unit_price = $store_item['cost'];
        }
      }
      
      $item->save();
      
      $c->setOrderTotals($item->order_5p);
    }
    
    echo 'done';
  }
  
  public function zulilyPrices () {
    
    $store_items = StoreItem::where('store_id', 'zul-01')->get();
    
    foreach ($store_items as $store_item) {
      $item = Item::where('store_id', 'zul-01')
                    ->where('item_code', $store_item->parent_sku)
                    ->latest()
                    ->first();
      
      if (!$item) {
        echo $store_item->child_sku . "\n";
        continue;
      }
      
      $store_item->cost = $item->item_unit_price;
      $store_item->save();
    }
    
    echo 'done';
  }
  
  public function loadJCP () {
    
    $csv = new CSV;
    $items = $csv->intoArray(storage_path() . '/searchvendorAllData-inv-warehouse.csv', ",");
    
    foreach ($items as $row) {
      if ($row[11] == 'Available') {
        $item = new StoreItem;
        $item->store_id = 'jcp-01';
        $item->vendor_sku = $row[3];
        $item->parent_sku = $row[0];
        $item->child_sku = $row[0];
        $item->upc = $row[4];
        $item->cost = $row[10];
        $item->description = $row[5];
        $item->save();
      }
    }
    
    $csv = new CSV;
    $items = $csv->intoArray(storage_path() . '/jcp_prices.csv', ",");
    
    foreach ($items as $row) {
      $item = StoreItem::where('parent_sku', $row[0])->first();
      
      if (!$item) {
        print_r($row);
        continue;
      }
      
      $item->cost = $row[2];
      
      if ($row[1] != ''  && $item->upc != $row[1]) {
        $item->upc = $row[1];
      }
      
      $item->save();
    }
    
    echo 'done';
  }
  
  public function fixCanceled () {
    $orders = Order::with('items')
                      // ->where('total', '!=', 0)
                      ->where('order_status', 8)
                      ->where('order_date', '>', '2018-01-01 00:00:00')
                      ->get(); 

    foreach ($orders as $order) {
      $items_cancelled = true;
      $subtotal = 0;
      foreach ($order->items as $item) {
        if ($item->item_status != 'cancelled') {
          $items_cancelled = false;
        }
        $subtotal += $item->item_unit_price * $item->item_quantity;
      }
      if ($items_cancelled == true) {
        $order->total = 0;
        $order->adjustments = ($subtotal - $order->coupon_value - $order->promotion_value + $order->gift_wrap_cost +
  											       $order->insurance + $order->shipping_charge + $order->tax_charge) * -1;
        $order->save();
      }
    }
    
    echo 'done';
  }
  public function addZulilyItems() {
    
    $csv = new CSV;
    $items = $csv->intoArray(storage_path() . '/zulily/zulily_upcs.csv', ",");
    
    foreach ($items as $row) {
      $i = new StoreItem;
      $i->store_id = 'zul-01';
      $i->vendor_sku = $row[0];
      $i->description = $row[1];
      $i->child_sku = $row[2];
      $i->upc = $row[3];
      
      $product = Helper::findProduct($row[2]);
      if ($product) {
        $i->parent_sku = $product->product_model;
      }
      
      $i->save();
    }
    
    echo 'done';
  }
  
  public function fixPWSthumbs () {
    $pws_images = glob('public_html/assets/images/spec_sheet/PWS*');
    
    foreach ($pws_images as $file) {
      $sku = substr($file, strrpos($file, '/') + 1);
      $sku = substr($sku, 0, strpos($sku, '-'));
      
      $product = Product::where('product_model', $sku)
                          ->whereNull('product_thumb')
                          ->first();
      
      if ($product) {
        $product->product_thumb = str_replace('public_html', 'http://order.monogramonline.com', $file);
        $product->save();
        echo $product->product_thumb . "\n";
      }
    }
    
    echo 'done';
  }
  
  public function brokenlinks () {
    $products = Product::select('id', 'product_model', 'product_thumb')
                        ->where('product_thumb', 'LIKE', 'http://order.monogramonline.com%')
                        ->get();
                        
    foreach ($products as $product) {
      $img = '/assets/images/product_thumb/' . $product->product_model . substr($product->product_thumb, -4);
      if (!file_exists(base_path() . '/public_html' . $img)) {
        $product->product_thumb = null;
        $product->save();
      }
    }
    
    echo 'done';
  }
  
  public function processRejects ()
  {
    $rejects = Rejection::with('item.inventoryunit')
                          ->whereNull('scrap')
                          ->where('graphic_status', 7)
                          ->where('is_deleted', '0')
                          ->get();
                          
    foreach ($rejects as $reject) {
    
              $reject->scrap = 0;
              $reject->save();

    }
    
    echo 'done';
  }
  
  public function naticoImages () {
    
    $csv = new CSV;
    $images = $csv->intoArray(storage_path() . '/natico_images.csv', ",");
    
    foreach ($images as $line) {
      
      $stock_no = trim($line[0]);
      
      $db = Inventory::where('stock_no_unique', $stock_no)->first();
      
      if ($db) {
        $db->warehouse = $line[1];
        $db->save();
      } else {
        echo $stock_no . " Not Found \n";
      }
      
    }
    
    echo 'done';
  }
  
  public function PWSlinks() {
    
    $results = Item::where('item_code', 'LIKE', 'PWS%')
                      ->select('item_code','item_thumb')
                      ->groupBy('item_code','item_thumb')
                      ->get();
                      
    foreach ($results as $result) {
      $product = Product::where('product_model', $result->item_code)->first();
      
      if ($product && !strpos($result->item_thumb, 'http://order.monogramonline.com/assets/images/product_thumb/')) {
        $product->product_thumb = $result->item_thumb;
        $product->save();
      } else if (strpos($result->item_thumb, 'http://order.monogramonline.com/assets/images/product_thumb/')) {
        echo $result->item_code . ' LOCAL URL' . "\n";
      } else {
        echo $result->item_code . ' NOT FOUND' . "\n";
      }
    }
    
    echo 'done';
  }
  
  public function canceled () {
    
    $orders = Order::with('items')
                      ->where('order_date', '>', '2018-01-01 00:00:00')
                      ->where('order_status', 8)
                      ->where('total', '>', 0)
                      ->get();
                      
    foreach ($orders as $order) {
      
      $total = 0;
      foreach ($order->items as $item) {
        $total += $item->item_quantity * $item->item_unit_price;
      }
      
      $adjustment_total = $total - $order->coupon_value - $order->promotion_value + $order->adjustments +   
                            $order->shipping_charge + $order->tax_charge;
      
      $order->adjustments += ($adjustment_total * -1);
      
      $order->total = 0;
      $order->save();
      
      
    }
  }
  
  public function updateImageUrls () {
    
    $products = Product::where('product_orderable', '0')
                        ->where('product_thumb', 'NOT LIKE', 'http://order.monogramonline.com%')
                        ->limit(1000)
                        ->get();
    
    foreach ($products as $product) {
      
      try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$product->product_thumb);
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
      
      if ($result == FALSE) {
        
        Log::info('Magento item thumb: retrieving thumbnail for ' . $product->product_model);
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
          
        } else {
          
          $decoded = json_decode(substr($json, 5, -6), true);

          if (isset($decoded['image_url']) && $decoded['image_url'] != "Image Not found") {
            echo $product->product_model . "\n";
            $product->product_thumb = $decoded['image_url'];
            $product->save();
          } 
          
        }
      }

      $product->product_orderable = '1';
      $product->save();
      
    }
  }
  
  public function ExportEverything () {
    
    $batches = Batch::join('stations', 'batches.station_id', '=', 'stations.id')
                  ->whereIn('batches.status', [2,4])
                  ->where('stations.type', 'G')
                  ->whereNull('export_date')
                  ->where('graphic_found', '0')
                  ->orderBy('min_order_date')
                  ->select('batch_number')
                  ->limit(500)
                  ->get();
    
    foreach ($batches as $batch) {
      $msg = Batch::export($batch->batch_number);
    }
    
    echo 'done';
  }
  
  public function reject () {
      
      $batches = Batch::with('items', 'route.stations_list')
                        ->whereIn('station_id', [94,79,71])
                        ->where('change_date', '<', '2018-12-17 00:00:00')
                        ->searchStatus('active')
                        ->get();
      
      echo 'Results: ' . count($batches) . "\n";
      
      foreach ($batches as $old_batch) {
        
        echo 'Batch: ' . $old_batch->batch_number . "\n";
        
        foreach ($old_batch->items as $item) {
          
          echo 'Item: ' . $item->id . "\n";
          
          if ($item->item_status == 'production') {
            
            $original_batch_number = Batch::getOriginalNumber($old_batch->batch_number);
            
            $reject_batch = Batch::join('items', 'batches.batch_number', '=', 'items.batch_number')
                            ->join('rejections', 'items.id', '=', 'rejections.item_id')
                            ->select('batches.batch_number')
                            ->where('batches.status', 3)
                            ->where('items.item_status', 3)
                            ->where('rejections.graphic_status', 1)
                            ->where('batches.batch_number', 'LIKE', 'R%' . $original_batch_number)
                            ->get();
            
            if (count($reject_batch) > 0) {
            
              $new_batch_number = $reject_batch->first()->batch_number;
            
            } else {
            
              $reject_batch = new Batch;
              $reject_batch->batch_number = Batch::getNewNumber($old_batch->batch_number, 'R');
              $reject_batch->save();
              $new_batch_number = $reject_batch->batch_number;
              $reject_batch->section_id = $old_batch->section_id;
              $reject_batch->station_id = $old_batch->route->stations_list->first()->station_id; 
              $reject_batch->batch_route_id = $old_batch->batch_route_id;
              $reject_batch->production_station_id = $old_batch->production_station_id;
              $reject_batch->store_id = $old_batch->store_id;
              $reject_batch->creation_date = date("Y-m-d H:i:s");
              $reject_batch->change_date = date("Y-m-d H:i:s");
              $reject_batch->status = 'active';
              $reject_batch->save();
            
            }
            
            $item->batch_number = $new_batch_number;
            $item->save();
            
            $rejection = new Rejection;
            $rejection->item_id = $item->id;
            $rejection->complete = '1';
            $rejection->scrap = 0;
            $rejection->graphic_status = 1;
            $rejection->rejection_reason = 60;
            $rejection->rejection_message = 'Rejected automatically from QC - not found';
            $rejection->reject_qty = $item->item_quantity;
            $rejection->rejection_user_id = '83';
            $rejection->from_station_id =  $old_batch->station_id;
            $rejection->to_station_id =  $reject_batch->station_id;
            $rejection->from_batch =  $old_batch->batch_number; 
            $rejection->to_batch =  $new_batch_number;
            $rejection->from_screen =  'auto';
            $rejection->save();
            
            Order::note("Item " . $item->id . " rejected from QC - missing", $item->order_5p, $item->order_id);
            
            Batch::isFinished($old_batch->batch_number);
          
            $msg = Batch::export($new_batch_number, '0');
        
            if (isset($msg['error'])) {
              Batch::note( $new_batch_number, '', 0, $msg['error']);
            }
          }
        }
      }
      
    }
  
      public function zulilyorderID() {
        
        $orders = Order::with('items','customer') 
                  ->where('store_id', 'zul-01')
                  ->get();
                  
        foreach ($orders as $order) {
          $order->customer->order_id = $order->order_id;
          $order->customer->save();
          
          foreach ($order->items as $item) {
            $item->order_id = $order->order_id;
            $item->save();
          }
        }
        
        return 'done';
      }
      
      public function errorSure3d () {
        
        set_time_limit(0);
          
        $items = Batch::join('items', 'batches.batch_number', '=', 'items.batch_number')
                      ->join('orders', 'items.order_5p', '=', 'orders.id')
                      ->join('stations', 'batches.station_id', '=', 'stations.id')
                      ->whereIn('batches.status', [2,4])
                      ->where('stations.type', 'G')
                      ->where('graphic_found', '>', 1)
                      ->whereNotNull('items.sure3d')
                      ->selectRaw('batches.batch_number')
                      ->groupBy('batch_number')
                      ->orderBy('batches.min_order_date')
                      ->get(); 
        
        foreach ($items as $item) {
            
          Batch::export($item->batch_number);
          // $ch = curl_init();
          // curl_setopt($ch, CURLOPT_URL, $item->sure3d);
          // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
          // $data = curl_exec ($ch);
          // curl_close ($ch);
          // 
          // $destination = '/media/RDrive/Sure3d/' . $item->short_order . '-' . $item->item_id .  '.eps';
          // $file = fopen($destination, "w+");
          // fputs($file, $data);
          // fclose($file);
          echo $item->short_order . "\n";
        }
        
        return;
      }
        
      public function manualSure3d () {
        
        set_time_limit(0);
        
        $manual_list = array_diff(scandir(GraphicsController::$manual_dir), array('..', '.')); 
        
        $batch_numbers = array();
        
        foreach ($manual_list as $dir) {
          
          $ex = explode('-', $dir);
          
          // if ($ex[0][0] == 'R' || $ex[0][0] == 'X') {
          //   continue;
          // } else
          if (is_numeric($ex[0])) {
            $batch_numbers[$ex[0]] = $dir;
          } elseif (isset($ex[1])) {
            $batch_numbers[$ex[0] . '-' . $ex[1]] = $dir;
          } else {
            continue;
          }       
        } 
        
        $batches = Batch::with('items')
                      ->join('stations', 'batches.station_id', '=', 'stations.id')
                      // ->whereIn('batches.status', [2,4])
                      // ->where('stations.type', 'G')
                      ->whereIn('batch_number', array_keys($batch_numbers))
                      ->orderBy('min_order_date')
                      ->get();
                      
        
        foreach ($batches as $batch) {
          
          echo $batch->batch_number . ' - ' . $batch->status . ' - ' . $batch->type . "\n";
          
          if (($batch->status != 'active' && $batch->status != 'back order') || $batch->type != 'G') {
            unlink(GraphicsController::$manual_dir . $batch_numbers[$batch->batch_number]);
          }
          
          // $changed = false;
          // foreach($batch->items as $item) {
          //   //all batches there now have one item
          //   $options = json_decode($item->item_option, true);
          //   if (isset($options["Custom_EPS_download_link"]) && $item->sure3d == null) {
          //     $item->sure3d = $options["Custom_EPS_download_link"];
          //     $item->save();
          //     $changed = true;
          //   }
          // }
          // 
          // if ($changed) {
          //   Batch::export($batch->batch_number);
          //   echo $batch->batch_number . "\n";
          // }
        }
        
        echo 'done';
      }
      
      private function fixPillows ()
      {
        $batches = Batch::with('items')
                          ->where('batch_number', 'LIKE', 'B01-%')
                          ->where('status', 2)
                          ->where('station_id', 215)
                          ->where('created_at', '>', '2018-11-07 15:46:00')
                          ->where('created_at', '<', '2018-11-07 15:47:00')
                          ->get();
                          
        foreach ($batches as $batch) {
          
          $old_batch = Batch::where('batch_number', substr($batch->batch_number, 4))->first();
          
          if (!$old_batch) {
            echo 'OLD BATCH NOT FOUND ' . $batch->batch_number . "/n";
            continue;
          }
          
          foreach($batch->items as $item) {
            $item->batch_number = $old_batch->batch_number;
            $item->save();
          }
          
          $old_batch->status = 'active';
          $old_batch->save();
          Batch::isFinished($batch->batch_number);
          
        }
      }
      
      private function setWapCount() {
        
        $bins = Wap::whereNotNull('order_id')
                      ->get();
        
        foreach ($bins as $bin) {
          $items = WapItem::where('bin_id', $bin->id)
                            ->oldest()
                            ->get();
                            
          $count = 1;
          
          foreach ($items as $item) {
            $item->item_count = $count;
            $item->save();
            $count++;
          }
        }
        
        echo 'done';
      }
      
      private function fixmess () {
        
        $csv = new CSV;
        $results = $csv->intoArray(storage_path() . '/fixed.csv', ",");
      
        foreach ($results as $index => $line) {
          $item = Item::find($line[0]);
          $item->item_unit_price = $line[1];
          $item->save();
          
          $order = Order::find($item->order_5p);
      		$order->item_count = count($order->items);
          $total = 0;
          foreach ($order->items as $item) {
            $total += $item->item_quantity * $item->item_unit_price;
          }
          $order->total = sprintf("%01.2f", $total);
          $order->save();
        }
        
        // $pathToFile = $csv->createFile($new, storage_path() , ['id','price'] , '/fixed.csv');
        // return $pathToFile;
      }
      
      public function fixZulily () {
        
        $files = array();
        $prices = array();
        
        $reader = Excel::load('storage/zulily/347638.xls');
        $results = $reader->get()->toArray();
        $files[] = $results;
        
      
        foreach ($files as $results) {
          foreach ($results as $line) {
            $store_item = StoreItem::where('store_id', 'zul-01')
                                      ->where('vendor_sku', $line['zulily_product_id'])
                                      ->first();
            
            if (!$store_item) {
              $store_item = new StoreItem;
              $store_item->store_id = 'zul-01';
              $store_item->vendor_sku = $line['zulily_product_id'];
              echo $line['zulily_product_id'] . "added \n";
              $store_item->description = $line['product_name'];
              $product = Helper::findProduct($line['zulily_product_id']);
              $store_item->parent_sku = isset($product->product_model) ? $product->product_model : $line['vendor_sku'];
              $store_item->child_sku = $line['vendor_sku'];
            }
            
            $store_item->upc = $line['upc'];
            
            if ($line['units_sold'] != '0' && !isset($prices[$line['zulily_product_id']])) {
              $store_item->cost = number_format($line['sales'] / $line['units_sold'], 2);
              $prices[$line['zulily_product_id']] = number_format($line['sales'] / $line['units_sold'], 2);
            }
            
            $store_item->save();
          }
        }
        
        $items = Item::where('items.store_id', 'zul-01')
                    ->where('items.item_unit_price', 0)
                    ->selectRaw('items.id, items.item_unit_price, items.order_5p, items.edi_id, items.store_id')
                    ->get();
                                
        foreach ($items as $item) {
          
          if (isset($prices[$item->edi_id])) {
            $item->item_unit_price = $prices[$item->edi_id];
            $item->save();
            
            $order = Order::find($item->order_5p);
        		$order->item_count = count($order->items);
            $total = 0;
            foreach ($order->items as $item) {
              $total += $item->item_quantity * $item->item_unit_price;
            }
            $order->total = sprintf("%01.2f", $total);
            $order->save();
          } else {
            Log::error('EDI Item not found: ' . $item->edi_id . ' - ' . $item->order_5p);
            echo $item->edi_id . "\n";
          }
        }
        
        echo 'done';
      }
      
      private function loadAmazon (){
        
        // $reader = Excel::load('/home/jennifer/Downloads/Amazon_North_America_Fulfillment_Center_Address_List.xlsx');
        // $results = $reader->get()->toArray();
        
        $csv = new CSV;
        $results = $csv->intoArray(storage_path() . '/Amazon_Fulfillment_Center_Missing.csv', ",");
        
        foreach ($results as $line) { 
            $fc = AmazonFC::where('code', $line[0]);
            if (!$fc) {
              $a = new AmazonFC;
              $a->code = $line[0];
              $a->name = $line[1];
              $a->address_1 = $line[2];
              $a->address_2 = $line[3];
              $a->city = $line[4];
              $a->state = $line[5];
              $a->zip = $line[6];
              $a->country = substr($line[7], 0, 2);
              $a->save();
              echo $line[0] . ' ADDED' . "/n";
            } else {
              echo $line[0] . ' already in db' . "/n";
            }
        }
        
        print 'done';
      }
      
      private function loadNatico () {
        
        // $csv = new CSV;
        // $images = $csv->intoArray(storage_path() . '/natico_productlist.csv', ",");
        
        $csv = new CSV;
        $results = $csv->intoArray(storage_path() . '/Natico.csv', ",");
        
        // $reader = Excel::load('storage/Inventorylist.xlsx');
        // $results = $reader->get()->toArray();
        
        foreach ($results as $line) {
          
          if ($line[0] == 'ItemCode' || $line[0] == '') {
            continue;
          }
          
          $db = Inventory::where('stock_no_unique', $line[0])->first();
          
          if ($db) {
            
            $db->last_cost = $line[1];
            
            if ($line[2] != '#N/A' && $db->upc != $line[2]) {
              $db->upc != $line[2];
            }
            
            $db->save();
          }
          // } else {
          // 
          //     $key = array_search('Item No: ' . $line[0], array_column($images, 1));
          // 
          //     if ($key != false) {
          //       $link = $images[$key][2];
          //     } else {
          //       $link = '';
          //     }
          // 
          //     $inv = new Inventory;
          //     $inv->stock_no_unique = $line[0];
          //     $inv->upc = ($line[3] != '#N/A') ? $line[3] : '';
          //     $inv->stock_name_discription = $line[1];
          //     $inv->section_id = 17;
          //     $inv->warehouse = $link;
          //     $inv->last_cost = $line[2];
          //     $inv->user_id = 87;
          //     $inv->save();
          // 
          //     $unit = new InventoryUnit;
          //     $unit->stock_no_unique = $line[0];
          //     $unit->child_sku = $line[0];
          //     $unit->unit_qty = 1;
          //     $unit->user_id = 87;
          //     $unit->save();
          // 
          //     $product = new Product();
          //     $product->product_model = $line[0];
          //     $product->id_catalog = $line[0];
          //     $product->product_name = $line[1];
          //     $product->product_upc = $line[3];
          //     $product->product_thumb = $link;
          //     $product->save();
          // 
          //     $sku = new Option;
          //     $sku->unique_row_value = Helper::generateUniqueRowId();
          //     $sku->id_catalog = $line[0];
          //     $sku->graphic_sku = '';
          //     $sku->parent_sku = $line[0];
          //     $sku->child_sku = $line[0];
          //     $sku->allow_mixing = 1;
          //     $sku->batch_route_id = 309;
          //     try{
          //       $sku->save();
          //     } catch (\Exception $e) {
          //       echo 'Option in DB ' . $line[0] . "\n";
          //     }
          // 
          //   }
        }
        
        // foreach ($bins as $line) {
        // 
        //   if ($line[0] == 'Item Code' || $line[0] == '' || $line[0] == 'ALL ITEMS LIST' || $line[2] == '') {
        //     continue;
        //   }
        // 
        //   $db = Inventory::where('stock_no_unique', $line[0])->first();
        // 
        //   if ($db) {
        //     $db->wh_bin = $line[2];
        //     $db->save();
        //   } else {
        //     echo 'Natico Bin Product not found: ' . $line[0] . "\n";
        //   }
        // }
        
        echo 'done';
      }
      
      private function closeEmptyBatches () {
        
        $batch_list = DB::select("SELECT batches.batch_number FROM batches
                                  LEFT JOIN items ON batches.batch_number = items.batch_number
                                  WHERE items.batch_number IS NULL 
                                  AND batches.status !=8");
        foreach ($batch_list as $batch) {
          Batch::isFinished($batch->batch_number);
        }
        
        echo 'done';
      }
      public function updatestrawSKUs () {
        
        $items = Item::whereIn('item_code', ['FB10189', 'FB10186', 'FB10075', 'FB10076'])->get();
        
        foreach ($items as $item) {
          $item->child_sku = Helper::getChildSku($item);
          $item->save();
        }
        
        echo 'done';
      }
      
      private function loadDesigns() {
        $graphic_skus = Option::all(); 
        foreach ($graphic_skus as $style) {
          $o = Design::where('StyleName', $style->graphic_sku)->first();
          
          if (!$o) {
            $d = new Design;
            $d->StyleName = $style->graphic_sku;
            $d->user_id = 87;
            $d->save();
          }
        }
      }
      
      private function symLinkTest() {
        
        return @symlink('/media/RDrive/archive/R01-201990-COPY-1.eps','/media/RDrive/Sublimation/R01-201990-COPY-1.eps');
        
      }
      
      public function changeStoreId ($old, $new) {
        
        $orders = Order::where('store_id', $old)
                  ->get();
        
        foreach ($orders as $order) {
          $order->store_id = $new;
          $order->order_id = str_replace($old . '-', '', $order->order_id);
          $order->save();
        }
        
        $items = Item::where('store_id', $old)
                  ->get();
        
        foreach ($items as $item) {
          $item->store_id = $new;
          $item->order_id = str_replace($old . '-', '', $item->order_id);
          $item->save();
        }
        
        $customers = Customer::where('order_id', 'LIKE', $old . '%')
                  ->get();
        
        foreach ($customers as $customer) {
          $customer->order_id = str_replace($old . '-', '', $customer->order_id);
          $customer->save();
        }
        
        $notes = Note::where('order_id', 'LIKE', $old . '%')
                  ->get();
        
        foreach ($notes as $note) {
          $note->order_id = str_replace($old . '-', '', $note->order_id);
          $note->save();
        }
        
        $notes = Note::where('note_text', 'LIKE', '%' . $old . '%')
                  ->get();
        
        foreach ($notes as $note) {
          $note->note_text = str_replace($old . '-', '', $note->note_text);
          $note->save();
        }
        
        $batches = Batch::where('store_id', $old . '%')->get();
        
        foreach ($batches as $batch) {
          $batch->store_id = $new;
          $batch->save();
        }
        
        echo 'done';
      }
      
      private function mo_products () {
        $csv = new CSV;
        $skus = $csv->intoArray(storage_path() . '/momo_db_products.csv', ",");
        
        foreach ($skus as $line) {
          $sku = $line[3];
          $product = Product::where('product_model', $sku)->first();
          
          if (!$product) {
            $product = new Product;
            $product->product_model = $sku;
            echo 'Product added : ' . $sku . "\n";
          }
          
          $product->id_catalog = $line[5];
          $product->product_url = 'https://www.monogramonline.com/' . $line[6];
          if (substr($product->product_thumb,0,39) != 'http://order.monogramonline.com/assets/') {
            $product->product_thumb = 'https://www.monogramonline.com/media/catalog/product/cache/1/image/9df78eab33525d08d6e5fb8d27136e95/' . $line[4];
          }
          $product->product_description = $line[0];
          $product->product_name = $line[1];
          $product->product_price = $line[2];
          
          $product->save();
        }
      }
      
      private function mo_options () {
        
        $csv = new CSV;
        $skus = $csv->intoArray(storage_path() . '/momo_db_custom_options.csv', ",");
        
        $option_id = 0;
        $option_db_id = 0;
        
        foreach ($skus as $line) {
          if ($line[10] == 'Confirmation of Order Details') {
            continue;
          }
          
          $sku = $line[14];
          
          // $product = Product::where('product_model', $sku)->get();
          // 
          // if (count($product) == 0) {
          // 
          // }
          
          if ($line[1] == 'field') {
            $row = new ProductOption;
            $row->product_model = $sku;
            $row->type = 'T';
            $row->sort_order = $line[8];
            $row->title = $line[10];
            $row->max_chars = $line[4];
            $row->save();
          } else {
            if ($option_id != $line[0]) {
              $row = new ProductOption;
              $row->product_model = $sku;
              $row->type = 'D';
              $row->sort_order = $line[8];
              $row->title = $line[10];
              $row->max_chars = $line[4];
              $row->save();
              $option_db_id = $row->id;
              $option_id = $line[0];
            } 
                        
            $ddrow = new ProductOptionValue;
            $ddrow->option_id = $option_db_id;
            $ddrow->value = $line[18];
            $ddrow->price = $line[11];
            $ddrow->save();
          }
          
        }
        
      }
      
      private function wayfair () {
        $csv = new CSV;
        $skus = $csv->intoArray(storage_path() . '/29004_full_catalog_export.csv', ",");
        $options = $csv->intoArray(storage_path() . '/wayfair_options.csv', ",");
        
        $product_options = array();
        
        foreach ($options as $option) {
          $product_options[$option[0]] [$option[5]] = $option[8];
        }
        
        foreach ($skus as $line) {
          $sku = $line[0];
          $name = $line[4];
          $wsku = $line[25];
          $category = $line[24];
          $wholesale = $line[12];
          $retail = $line[13]; 
          
          $product = Product::where('product_model', $sku)->get();
          
          if (count($product) == 0) {
            
            $id_catalog = 'monogramonline-inc-' .
                  strtolower(str_replace(' ', '-', str_replace(["'",'"','.',' &',','], '', $name)) . '-' . $wsku);
            $url = 'https://www.wayfair.com/pdp/' . $id_catalog . '.html';
            // $image_name = str_replace(' ', '+', urlencode($name)) . '.jpg';
            // 
            // $ch = curl_init();
            // curl_setopt($ch,CURLOPT_URL,$url);
            // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
            // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    
            // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);     
            // $page=curl_exec($ch);
            // curl_close($ch);
            // 
            // dd($page);
            // $name_offset = strpos($page,$image_name);
            // $start_offset = strrpos($page,'https',(strlen($page) - $name_offset)*-1);
            // $image = substr($page,$start_offset, $name_offset - $start_offset +  strlen($image_name));
            
            $new = new Product;
            $new->product_model = $sku;
            $new->product_url = $url;
            $new->id_catalog = $id_catalog;
            $new->store_id = 'Wayfair';
            $new->product_name = $name;
            $new->product_note = $category;
            $new->product_price = $retail;
            $new->product_wholesale_price = $wholesale;
            // $new->product_thumb = $image;
            $new->save();
            
            }
            
            $db_options = ProductOption::where('product_model', $sku)->get();
            
            if (count($db_options) == 0 && isset($product_options[$wsku])) {
              $options = $product_options[$wsku];
              
              $count = 1;
              
              foreach ($options as $title => $length) {
                $row = new ProductOption;
                $row->product_model = $sku;
                $row->type = 'T';
                $row->sort_order = $count++;
                $row->title = $title;
                $row->max_chars = $length;
                $row->save();
              }
            }
          } 
      }
      
      public function orphanTemplates () {
        $templates = array_diff(scandir('/media/RDrive/Pendants/PendantEngine/Templates/'), array('..', '.')); 
        $count = 0;
        
        foreach ($templates as $template) {
          
          if (substr(strtolower($template), -3) !=  '.ai') {
            echo 'Not AI file: ' . $template . "\n";
            continue;
          }
          
          $name = substr($template, 0, -3);
          
          $found = Option::where('graphic_sku', $name)
                            ->count();
          
          if ($found < 1) {
            echo 'Template not in 5p: ' . $template . "\n";
            $count++;
          }
        }
        
        echo 'done - ' . $count;
      }
      
      private function mapSvgs() {
        
        $svgs = array_diff(scandir('/media/RDrive/temp/svg/'), array('..', '.')); 
      
        $result = array();
        foreach ($svgs as $svg) {
            $input = file_get_contents('/media/RDrive/temp/svg/' . $svg);
            $doc = simplexml_load_string($input);
            $json = json_encode($doc);
            $array = json_decode($json,TRUE);
            
            $found = $this->array_key_exists_recursive("text",$array);
            
            if (!$found) {
              echo 'NOT FOUND: ' . $svg . "\n";
            }
        }
        
        echo 'done';
      }
      
      private function array_key_exists_recursive($key, $arr)
      {
          if (array_key_exists($key, $arr)) {
              return true;
          }
          foreach ($arr as $currentKey => $value) {
              if (is_array($value)) {
                  return $this->array_key_exists_recursive($key, $value);
              }
          }
          return false;
      }
      
      public function selectTemplate () {
        
        $templates = Option::selectRaw('DISTINCT graphic_sku, count(items.id) as item_count')
                    ->join('items', 'items.child_sku', '=', 'parameter_options.child_sku')
                    ->where('parameter_options.child_sku', 'LIKE', 'SB%')
                    ->where('parameter_options.template', 1)
                    ->where('parameter_options.xml', 1)
                    ->groupBy('parameter_options.child_sku')
                    ->orderBy('item_count', 'DESC')
                    ->limit(100)
                    ->get();
                    
        foreach($templates as $template) {
          $done = copy('/media/RDrive/Pendants/PendantEngine/Templates/' . $template->graphic_sku . '.ai','/media/RDrive/temp/ai/' . $template->graphic_sku . '.ai');
          if (!$done) {
            echo 'Failed to copy ' . $template->graphic_sku;
          }
        }
        
        echo 'done';
      }
      
      private function MHskus_fix () 
      {
        $csv = new CSV;
        $data = $csv->intoArray(storage_path() . '/MH_skus.csv', ",");
        
        foreach ($data as $line) {
          
          $product = Product::where('product_model', $line[1])->first();
          
          if ($product) {
            continue;
          }
          
          $units = InventoryUnit::where('child_sku', 'LIKE', $line[2] . '%')->get();
          
          foreach ($units as $unit) {
            if ($unit->stock_no_unique == 'ToBeAssigned') {
              $unit->delete();
            } else {
              echo $line[2] . ' - ' . $unit->stock_no_unique . "\n";
              continue;
            }
          }
          
          $units = InventoryUnit::where('child_sku', 'LIKE', $line[1] . '%')->get();
          
          foreach ($units as $unit) {
            $unit->child_sku = substr($unit->child_sku, 2);
            $unit->save();
          }
        }
        
        echo 'done';
      }
      
      private function MHskus () {
        
        $csv = new CSV;
        $data = $csv->intoArray(storage_path() . '/MH_skus.csv', ",");
        
        foreach ($data as $line) {
          
          $product = Product::where('product_model', $line[1])->first();
          
          if (!$product) {
            echo $line[1] . ' not found' . "\n";
            continue;
          }
          
          try {
            $product->product_model = $line[2];
            $product->save();
          } catch (\Exception $e) {
            echo $line[1] . ' - ' . $e->getMessage() . "\n";
            continue;
          }
          
          $spec = SpecificationSheet::where('product_sku', $line[1])->first();
          
          $spec->product_sku = $line[2];
          $spec->save();
          
          $options = Option::where('parent_sku', $line[1])->get();
          
          foreach ($options as $option) {
            
            $new = substr($option->child_sku, 2);
            
            $option->parent_sku = $line[2];
            $option->child_sku = $new;
            $option->save();
            
            $units = InventoryUnit::where('child_sku', $option->child_sku)->get();
            
            foreach ($units as $unit) {
              $unit->child_sku = $new;
              $unit->save();
            }
          }
          
        }
        
        echo 'done';
      }
      
      public function Sure3dLinks() {
        
        $csv = new CSV;
        $data = $csv->intoArray(storage_path() . '/sure3d-missing-links.csv', ",");
        // $data = $csv->intoArray('/home/jennifer/Documents/sure3d-missing-links.csv', ",");
        
        foreach ($data as $line) {
          
          if ($line[0] == null) {
            break;
          }
          
          $order = Order::with('items')
                          ->where('short_order', $line[0])
                          ->where('is_deleted', '0')
                          ->latest()
                          ->first();
                          // ->toSql();
                  
          if (!$order) {
            echo 'Order not found : ' . $line[0] . "\n";
            continue;
          }
          
          if ($order->items->count() == 1) {
            $order->items->first()->sure3d = $line[1];
            
            $options = json_decode($order->items->first()->item_option, true);
            
            $options['Custom_EPS_download_link'] = $line[1];
            
            $order->items->first()->item_option = json_encode($options);
            
            $order->items->first()->save();
            
            echo $order->items->first()->batch_number . "\n";
            
          } else {
            echo 'Items > 1 : ' . $order->short_order . "\n";
          }
        }
        
        echo 'done';
      }
      
      private function unbatched () {
        
        $dates = array();
    		$date[] = date("Y-m-d");
    		$date[] = date("Y-m-d", strtotime('-3 days') );
    		$date[] = date("Y-m-d", strtotime('-4 days') );
    		$date[] = date("Y-m-d", strtotime('-7 days') );
    		$date[] = date("Y-m-d", strtotime('-8 days') );
        
        $max_date = date("Y-m-d H:i:s");
        
        $unbatched = Item::join('orders', 'items.order_5p', '=', 'orders.id')
    										//->searchStore(null)
    										//->where('orders.order_date', '<', $max_date)
    										->whereNull('items.tracking_number')
    										->where('items.batch_number', '=', '0')
    										->whereIn('orders.order_status', [4,11,12])
    										->where('orders.is_deleted', '0')
    										->where('items.is_deleted', '0')
                        ->selectRaw('orders.order_date')
    										// ->selectRaw("
    										// 	items.id, orders.order_date, items.item_quantity,
    										// 	SUM(items.item_quantity) as items_count, 
    										// 	count(items.id) as lines_count,
    										// 	DATE(MIN(orders.order_date)) as earliest_order_date,
    										// 	COUNT(IF(orders.order_date >= '{$date[1]} 00:00:00', items.id, NULL)) as order_1,
    										// 	COUNT(IF(orders.order_date >= '{$date[3]} 00:00:00' AND orders.order_date <= '{$date[2]} 23:59:59', items.id, NULL)) as order_2,
    										// 	COUNT(IF(orders.order_date <= '{$date[4]} 23:59:59', items.id, NULL)) as order_3
    										// 	")
    										->get(); dd($unbatched);
      }
      
      private function PU_SKUS () {
        
        $reader = Excel::load(storage_path() . '/PMO_ProductList.xlsx');
        $results = $reader->noHeading()->get()->toArray();
        // dd($results);
        foreach ($results as $line) {
          
          $product = Product::where('product_model', $line[5])->first();
          
          if ($product) {
              echo 'Updating ' .  $line[5] . '<br>';
              $product->product_wholesale_price = $line[3];
              $product->save();
          }
        }
        return;
      }
      
      public function Sure3dBatch() {
                          
        $items = Item::with('order')
                      ->where('item_option', 'LIKE', '%Photo%Custom_EPS%')
                      ->whereNotIn('item_status', [2,6])
                      ->get();
        
        foreach ($items as $item) {
          if ($item->sure3d == null) {
            $options = json_decode($item->item_option, true);
            $item->sure3d = $options['Custom_EPS_download_link'];
            $item->save();
          }
          
          if (!file_exists(base_path() . '/public_html/assets/images/Sure3d/thumbs/' . $item->order->short_order . '-' . $item->id . '.jpg')) {
            $s = new Sure3d;
            $s->getImage ($item);
          }
        }
        
        return;
      }
      
      public function Sure3dBatches() {
        
        $batches = Item::whereNotNull('sure3d')
                          ->searchStatus('shippable')
                          ->selectRaw('DISTINCT batch_number')
                          ->get()
                          ->pluck('batch_number')
                          ->toArray();
                          
        $items = Item::with('order')->whereIn('batch_number', $batches)->get();
        
        foreach ($items as $item) {
          if (!file_exists(base_path() . '/public_html/assets/images/Sure3d/thumbs/' . $item->order->short_order . '-' . $item->id . '.jpg')) {
            $s = new Sure3d;
            $s->getImage ($item);
          }
        }
        
        return;
      }
      
      public function Sure3dItems() {
        $items = Item::with('order')
                        ->whereNotNull('sure3d')
                        ->searchStatus('shippable')
                        ->get();
        
        foreach ($items as $item) {
            $s = new Sure3d;
            $ext = substr($item->sure3d, strrpos($item->sure3d, '.'));
            $s->createThumb ('/media/RDrive/Sure3d/' . $item->order->short_order .'-'. $item->id . $ext, $item);
        }
        
        return;
      }
      
      public function recreateThumbs () {
        
        $images = array_diff(scandir('/media/RDrive/Sure3d/'), array('..', '.'));
        $items = Item::where('status', 1)->whereNotNull('sure3d')->get()->pluck('id')->toArray();
        
        foreach ($images as $image) {
          
          if (substr($image, -3) != 'dxf' && in_array(substr($image, strpos($image, '-') + 1, 6), $items)) {
            try {
    					$s = new Sure3d;
    					$s->createThumb(substr($image, strpos($image, '-') + 1, 6));
    				} catch (\Exception $e) {
    					Log::error('recreateThumbs: Exception');
    				}
          } 
        }
        
        echo 'done';
      }
      
      private function updateSkus () {
        
        $reader = Excel::load(storage_path() . '/skus_032918.xlsx');
        $results = $reader->noHeading()->get()->toArray();
        // dd($results);
        foreach ($results as $line) {
          
          $product = Product::where('product_model', $line[2])->first();
          
          if ($product) {
            
            $product->id_catalog = $line[4];
            $product->product_url = 'https://www.monogramonline.com/' . $line[5];
            
            if (substr($product->product_thumb, 0, 38) != 'http://order.monogramonline.com/assets') {
              $product->product_thumb = 'https://www.monogramonline.com/media/catalog/product/cache/1/thumbnail/90x112/9df78eab33525d08d6e5fb8d27136e95' . $line[3];
            }
            
            $product->product_price = $line[1];
            
              $product->save();
              // echo $line[0] . ' updated' . "\n";

          } else {
              Log::info('Adding ' . $line[2]);
              $product = new Product;
              $product->product_model = $line[2];
              $product->product_name = $line[0];
              $product->id_catalog = $line[4];
              $product->product_url = 'https://www.monogramonline.com/' . $line[5];
              $product->product_price = $line[1];
              $product->product_thumb = 'https://www.monogramonline.com/media/catalog/product/cache/1/thumbnail/90x112/9df78eab33525d08d6e5fb8d27136e95' . $line[3];

              $product->save();
          }
        }
        return;
      }
      
      private function fix_cancelled () {
        
        $active_batches = Batch::join('items', 'batches.batch_number', '=', 'items.batch_number')
                ->where('batches.status', 2)
                ->whereIn('items.item_status', [6])
                ->where('items.is_deleted', '0')
                ->selectRaw('GROUP_CONCAT(DISTINCT items.item_status) as status_profile,batches.*, count(*) as count')
                ->groupBy('batch_number')
                ->get(); 
        
        foreach($active_batches as $batch) {
          if ($batch->status_profile == '6') {
            $batch->status = 1;
            $batch->save();
            echo $batch->batch_number . ' updated' . "\n";
            // echo $batch->batch_number . ' - ' . $batch->status_profile .  "\n";
          }
        }
        echo 'done';
      }
      
      private function updateIdCatalog () {
        
        $reader = Excel::load(storage_path() . '/sku.xlsx');
        $results = $reader->noHeading()->get()->toArray();
        
        foreach ($results[0] as $line) {
          
          $product = Product::where('product_model', $line[0])->first();
          
          if ($product) {
            if ($product->id_catalog != $line[1]) {
              $product->id_catalog = $line[1];
              $product->save();
              // echo $line[0] . ' updated' . "\n";
            }
          } else {
              echo $line[0] . ' not found' . "\n";
          }
        }
        echo 'done';
      }
      
      private function fix_totals ($update, $start, $end) {
        
        $orders = Order::with('items', 'notes')
                        ->where('is_deleted', '0')
                        ->withinDate($start, $end)
                        ->whereIn('store_id', ['524339241', '52053152'])
                        ->get();
        
        $count = 0;
        
        foreach ($orders as $order) {
          $subtotal = 0;
          $note = null;
          
          foreach ($order->items as $item) {
            $subtotal += $item->item_quantity * $item->item_unit_price;
          }
          
          $total = $subtotal - $order->coupon_value - $order->promotion_value + $order->gift_wrap_cost +
                        $order->adjustments + $order->insurance + $order->shipping_charge + $order->tax_charge;
          
          
          $diff = round(($total - $order->total) * 100) / 100;
          
          if ($diff != 0 ) {
            
            $note = $order->notes->where('order_5p', $order->id)->where('note_text', 'Order Info Manually Updated')->all();
            
            if ($note) {
              $note_found = 'Manually Updated';
            } else {
              $note_found = ' ';
            }
            
            $count++;
            
            echo "$count, $order->id, $order->short_order, $order->coupon_id, $order->promotion_id, $total, $order->total, $diff, $note_found \n";
            
            if ($update) {
              $order->total = $total;
              $order->save();
            }
          }
        }
        
        
      }
      
      private function inventory_section ()
      {
        $routes = BatchRoute::with('production_station')->get();
        
        $inventory = Inventory::with('inventoryUnitRelation.options')
                                ->has('inventoryUnitRelation')
                                ->where('stock_no_unique', '!=', 'ToBeAssigned')
                                ->get(); 
        
        foreach ($inventory as $stock) {
          
          $route_list = array();
          $sections = array();
          $section_all = array();
          $section_count = array();
          
          // if (!isset($stock->inventoryUnitRelation) || !isset($stock->inventoryUnitRelation->items)) {
          //   echo $stock->stock_no_unique . " no inventory unit \n";
          //   continue;
          // }
        
          foreach ($stock->inventoryUnitRelation as $unit) {
            foreach ($unit->options as $option) {
                $route = $routes->where('id', $option->batch_route_id)->first();
                if ($route->production_station->first()) {
                  $section_all[] = $route->production_station->first()->section;
                }
            }
          }
          
          $sections = array_unique($section_all); 
          
          if (count($sections) == 1) {
            
            $keys = array_keys($sections);
            if (count($keys) > 0) {
              $stock->section_id = $sections[$keys[0]];
              $stock->save();
              continue;
            }
            
          } 
          
          foreach ($sections as $key => $section) {
            if ($section == 1 || $section == 13 || $section == 14 || $section == 7 || $section == 8 || $section == 9) {
              unset($sections[$key]);
            } 
          }
          
          if (count($sections) == 1) {
            
            $keys = array_keys($sections);
            if (count($keys) > 0) {
              $stock->section_id = $sections[$keys[0]];
              $stock->save();
              continue;
            }
          }
          
          $section_count = array_count_values($section_all);
          arsort($section_count);
          
          $keys = array_keys($section_count);
          if (count($keys) > 0) {
            $stock->section_id = $keys[0];
            $stock->save();
            continue;
          }
        
          echo $stock->stock_no_unique . ' ' . count($sections) . " sections \n";
          
          foreach ($sections as $section) {
            echo "\t" . $section . "\n";
          }
          
        }
      }
      
      private function inventory_cost () 
      {
        $csv = new CSV;
        $data = $csv->intoArray(storage_path() . '/inventory_cost.csv', ",");
        
        foreach ($data as $line) {
          $stock = Inventory::where('stock_no_unique', trim($line[0]))->first();
          
          if (!$stock) {
            echo 'Stock Number not found ' . $line[0] . "\n";
            continue;
          }
          
          $stock->last_cost = trim($line[1]);
          $stock->save();
        }
        
        echo 'done';
        
      }
      
      private function fix_adjustments ()
      {
        $adjustments = InventoryAdjustment::with('inventory')
                                  ->where('type', 1)
                                  ->get();
        
        foreach ($adjustments as $adjustment) {
          if (!$adjustment->inventory) {
            echo 'ERROR: Not stock number found ' . $adjustment->stock_no_unique . '<br>';
            continue;
          }
          
          $new_qty = $adjustment->inventory->qty_on_hand - ($adjustment->quantity * 2);
          
          if ($new_qty < 0) {
            $new_qty = 0;
          }
          
          $adjustment->inventory->qty_on_hand = $new_qty;
          $adjustment->inventory->save();
          
          if ($adjustment->created_at > $adjustment->inventory->qty_date && $adjustment->inventory->qty_date != null) {
            $adjustment->quantity = $adjustment->quantity * -1;
            $adjustment->save();
          } else if ($adjustment->inventory->qty_date == null || $adjustment->created_at <= $adjustment->inventory->qty_date) {
            $adjustment->delete();
          } else {
            echo 'Slipped through ' . $adjustment->id . '<br>';
          }
        }
        
        echo 'done';
      }
      
      private function reset_qty () 
      {        
        $inventory = Inventory::get();
        
        
        $adjustments = InventoryAdjustment::selectRaw('stock_no_unique, SUM(quantity) as qty')
                                          ->groupBy('stock_no_unique')
                                          ->get();
                                          
        foreach ($inventory as $stock) {
          
          if ($stock->qty_date != null) {
            
            if (!$stock) {
              echo 'Stock not found ' . $stock . "\n";
              continue;
            }
            
            $adj = $adjustments->where('stock_no_unique', $stock->stock_no_unique)->all();
            
            if (!isset($adj->qty)) {
              echo 'Adjustment not found ' . $stock->stock_no_unique . "\n";
              continue;
            }
            
            $stock->qty_on_hand = $adj->qty;
            $stock->save();
            
          } else {
            
            if ($stock->qty_on_hand != -1) {
              if ($stock->qty_on_hand != 0) {
                echo 'Non Zero Qty ' . $stock->stock_no_unique . ' - ' . $stock->qty_on_hand . "\n";
              }
              
              $stock->qty_on_hand = -1;
              $stock->save();
            }
          }
        }
        
        echo 'done';
      }
      
      private function assign () {
        
        $child_skus = Option::leftjoin('inventory_unit', 'parameter_options.child_sku', '=', 'inventory_unit.child_sku')
                              ->whereNull('inventory_unit.child_sku')
                              ->select('parameter_options.child_sku')
                              ->get();
        
        foreach ($child_skus as $child_sku) {
          
          $unit = new InventoryUnit;
          $unit->child_sku = $child_sku->child_sku;
          $unit->unit_qty = 1;
          $unit->stock_no_unique = 'ToBeAssigned';
          $unit->save();
        }
        
        echo 'done';
      }
      
      private function update_stock_numbers () {
        
        $options = Option::with('product')
                              ->leftjoin('products', 'parameter_options.parent_sku', '=', 'products.product_model')
                              ->leftjoin('inventory_unit', 'parameter_options.child_sku', '=', 'inventory_unit.child_sku')
                              ->leftjoin('inventories', 'inventory_unit.stock_no_unique', '=', 'inventories.stock_no_unique')
                              // ->whereNull('inventory_unit.child_sku')
                              // ->orWhere('inventory_unit.stock_no_unique', 'ToBeAssigned')
                              ->where('products.product_name', 'LIKE', '%shirt%')
                              ->where('products.product_name', 'LIKE', '% men%')
                              ->where('parameter_options.child_sku', 'LIKE', '%navy%')
                              ->where('parameter_options.child_sku', 'LIKE', '%xl%')
                              ->select('parameter_options.child_sku', 'products.product_name', 'inventory_unit.stock_no_unique', 'inventories.stock_name_discription')
                              ->get();
        
        foreach($options as $option) {
          
          $unit = InventoryUnit::where('child_sku', $option->child_sku)->get();
          
          if (count($unit) == 0) {
            
            $unit = new InventoryUnit;
            $unit->child_sku = $option->child_sku;
            
          } else {
            
            $count = count($unit);
            
            for ($i = 1; $i < $count; $i++) {
              
              $unit->last()->delete();
            }
            
            $unit = $unit->first();
            
          } 
          
          $unit->unit_qty = 1;
          $unit->stock_no_unique = '20450';
          $unit->save();
          
        }
        
        $options = Option::with('product')
                              ->leftjoin('products', 'parameter_options.parent_sku', '=', 'products.product_model')
                              ->leftjoin('inventory_unit', 'parameter_options.child_sku', '=', 'inventory_unit.child_sku')
                              ->leftjoin('inventories', 'inventory_unit.stock_no_unique', '=', 'inventories.stock_no_unique')
                              // ->whereNull('inventory_unit.child_sku')
                              // ->orWhere('inventory_unit.stock_no_unique', 'ToBeAssigned')
                              ->where('products.product_name', 'LIKE', '%shirt%')
                              ->where('products.product_name', 'LIKE', '% men%')
                              ->where('parameter_options.child_sku', 'LIKE', '%navy%')
                              ->select('parameter_options.child_sku', 'products.product_name', 'inventory_unit.stock_no_unique', 'inventories.stock_name_discription')
                              ->get();
                              
        foreach($options as $option) {
          echo str_replace(',', '', $option->child_sku) . ',' . str_replace(',', '', $option->product_name) . ',' . 
                    str_replace(',', '', $option->stock_no_unique) . ',' . str_replace(',' ,'', $option->stock_name_discription) . "\n";
        }
        
        return 'done';
      }
      
      private function coupon_report() {
        $orders = Order::with('items')
                 ->where('is_deleted', '0')
                 ->where('order_date' , '>=', '2017-11-01 00:00:00')
                 ->where('order_date' , '<', '2017-12-01 00:00:00')
                 ->latest()
                 ->get();
        
        $array = [];
        
        foreach ($orders as $order) {
          $array[] =[
                $order->order_id,
                $order->short_order,
                $order->items->sum('item_quantity'),
                $order->items->sum('item_total_price'),
                $order->coupon_id ,
                $order->coupon_value ,
                $order->promotion_id ,
                $order->promotion_value,
                $order->tax_charge ,
                $order->shipping_charge ,
                $order->total
              ];
        }
        
        $csv = new CSV;
        $pathToFile = $csv->createFile($array, 'assets/exports/');
      }
      
      private function testUpsell () {
        
        $order = Order::find(527672);
        $item = Item::find(661693);
        $options = (array) json_decode($item->item_option);
        $values = [
                  '1 - Yes : SB8950 Add an Apron (+$9.99)',
                  '1 - Yes : SB8951 Add a Bath Towel (+$14.99)',
                  '1 - Yes : SB8949 Add a Drawstring Bag (+$9.99)',
                  '1 - Yes : SB4754 Add a Tote Bag (+$9.99)'
                ];
        $quantity = 1;
        
        $this->upsellItems($values, $options, $order, $item);
      }
      
      private function upsellItems($values, $options, $order, $order_item)
    	{
    		
    		$total_price = 0;
    		
    		$parameters = Parameter::where('is_deleted', '0')
    															->selectRaw("LOWER(parameter_value) as parameter")
    															->get()
    															->toArray();
    		 
    		foreach ($options as $key => $value) {
    			if (in_array(strtolower($key), $parameters) && !strpos(strtolower($key), 'style') && !!strpos(strtolower($key), 'color')) {
    				unset($options[$key]);
    			} else if (strpos($value, '$')) {
    				unset($options[$key]);
    			}
    		}
    		
    		foreach ($values as $value) {
    			
    			if (!strpos(strtolower($value), 'yes')) {
    				continue;
    			}
    			
    			$price = substr($value, strrpos($value, '$') + 1, strrpos($value, '.', strrpos($value, '$')) -  strrpos($value, '$') + 2);
    			
    			$start = stripos($value, ':') + 1;
    			
    			$sku = trim(substr($value, $start, stripos($value, ' ', $start + 2) - $start));
    			
    			$desc = trim(substr($value, strpos($value, $sku) + strlen($sku) + 1));
    			
    			$product = Product::where('product_model', $sku)
    													->first();
    			
    			if (!$product) {
    				$product = new Product();
    				$product->id_catalog = str_replace(' ', '-', $desc);
    				$product->product_model = $sku;
    				$product->batch_route_id = Helper::getDefaultRouteId();
    				$product->product_name = $desc;
    				$product->product_price = $price;
    				$product->save();
    			} 
    			
    			$item = new Item();
    			$item->order_5p = $order->id;
    			$item->order_id = $order->order_id;
    			$item->store_id = $order->store_id;
    			$item->item_code = $sku;
    			$item->item_description = $desc;
    			$item->item_id = $sku;
    			$item->item_option = json_encode( str_replace("\u00a0", "", $options) );
    			$item->item_quantity = $order_item->item_quantity;
    			$item->item_thumb = isset($product->product_thumb) ? $product->product_thumb : 'http://order.monogramonline.com/assets/images/no_image.jpg';
    			$item->item_unit_price = $price;
    			$item->item_url = isset($product->product_url) ? $product->product_url : null;
    			$item->data_parse_type = 'hook';
    			$item->child_sku = Helper::getChildSku($item);
    			$item->save();
    			
    			$total_price += $price;
    		}
    		
    		return $total_price;
    	}
      
      public function note_orderID () {
        
        $notes = Note::with('new_order')
                        ->whereNull('order_5p')
                        ->where('is_deleted', '0')
                        ->limit(5000)
                        ->get();           
        
        foreach ($notes as $note) {
          if ($note->new_order) {
            $note->order_5p = $note->new_order->id;
            $note->save();
          } else {
            $note->is_deleted = '1';
            $note->save();
          }
        }
        
        return true;
      }
      
      private function fixREQUESTNUMBER () {
        
        $EDI_items = Walmart_EDI::whereNotNull('ORDERNUMBER')->get(); 
        
        foreach ($EDI_items as $item) {
          
          $req = $item->REQUESTNUMBER;
          
          Order::where('order_id', $item->ORDERNUMBER)
                ->update([
                    'order_id' => $req,
                    'short_order' => $req
                ]);
          
          Item::where('order_id', $item->ORDERNUMBER)
                ->update([
                    'order_id' => $req,
                ]);
                
          Note::where('order_id', $item->ORDERNUMBER)
                ->update([
                    'order_id' => $req,
                ]);
        }
        
        echo 'done';
      }
      
      private function fixChildSKU () {

          $options = Option::with('route')
                        ->leftjoin('inventory_unit', 'parameter_options.child_sku', '=', 'inventory_unit.child_sku')
                        ->where('inventory_unit.stock_no_unique')
                        ->select('parameter_options.child_sku', 'batch_route_id')
                        ->get();
          
          foreach ($options as $option) {
            if ($option->child_sku != null) {
              if (substr($option->route->batch_route_name, 0, 2) == 'J-' || substr($option->route->batch_route_name, 0, 8) == 'Patricio'
                    || substr($option->route->batch_route_name, 0, 7) == 'Patrico') {
                //assign to jewelry
                $inv = new InventoryUnit;
                $inv->child_sku = $option->child_sku;
                $inv->stock_no_unique = '1';
                $inv->unit_qty = 1;
                $inv->save();
              } else if (substr($option->route->batch_route_name, 0, 3) == 'RED') {
                //assign to leather
                $inv = new InventoryUnit;
                $inv->child_sku = $option->child_sku;
                $inv->stock_no_unique = '2';
                $inv->unit_qty = 1;
                $inv->save();
              } else if (substr($option->route->batch_route_name, 0, 5) == 'HOUSE' || substr($option->route->batch_route_name, 0, 2) == 'H-') {
                //assign to leather
                $inv = new InventoryUnit;
                $inv->child_sku = $option->child_sku;
                $inv->stock_no_unique = '3';
                $inv->unit_qty = 1;
                $inv->save();
              } else if (substr($option->route->batch_route_name, 0, 6) == 'Z-DROP') {
                //assign to leather
                $inv = new InventoryUnit;
                $inv->child_sku = $option->child_sku;
                $inv->stock_no_unique = '4';
                $inv->unit_qty = 1;
                $inv->save();
              }
            }
          }
      }
      
      private function updateChangeDate() {
        
        $batches = Batch::whereNotIn('status', [1,8])
                    ->get();
                    
        foreach ($batches as $batch) {
          $info = Batch::lastScan($batch->batch_number);
          
          if (isset($info['date'])) {
            $batch->change_date = $info['date'];
            $batch->save();
          } else { 
            $batch->change_date = $batch->creation_date;
            $batch->save();
          }
        }
        
        echo 'done';
      }
      
      private function fixRoutes() {
        //needs option namespace
        $batches = Batch::with('first_item')
                    ->searchStatus('all')
                    ->get();

        foreach ($batches as $batch) {
          
          if ($batch->first_item) {
            $option = Option::where('child_sku', $batch->first_item->child_sku)->first();
            
            if ($option) {
                $batch->batch_route_id = $option->batch_route_id;
                $batch->save();
            } 
          } else {
            echo $batch->batch_number . ' No items<br>';
          }
        }
        echo 'done';
      }
      
      private function badStations () {
        
        $batch_routes = BatchRoute::with('stations_list')
                      ->where('is_deleted', '0')
                      ->get();
        
        $route_lookup = array();
        
        foreach($batch_routes as $route) {
          
          foreach($route->stations_list as $station) {
            
            $route_lookup[$route->id][$station->station_id] = true;
            
          }
        }
        
        $batches = Batch::searchStatus('all')->get();
        
        foreach ($batches as $batch) {
          
          if (!isset($route_lookup[$batch->batch_route_id][$batch->station_id])) {
            
            echo $batch->batch_number . '<br>';
          } else {
            echo 'there';
          }
        }
      }
}
