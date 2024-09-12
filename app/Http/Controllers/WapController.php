<?php

namespace App\Http\Controllers;

use EasyPost\EasyPost;
use EasyPost\Shipment;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Wap;
use App\WapItem;
use App\Item;
use App\Order;
use App\Batch;
use App\Station;
use App\Section;
use App\Store;
use Monogram\Helper;
use Monogram\Sure3d;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class WapController extends Controller
{
    protected $table = 'wap';

    public function index(Request $request)
    {
        if (!$request->has('end_date')) {
            $end_date = NULL;
            
            $bins = Wap::with('order.shippable_items', 'order.store', 'order.items')
                    ->whereHas('order', function ($q) {
                        $q->where('order_status', '!=', 6);
                    })
                    ->whereHas('order.store', function ($q){
                        $q->where('permit_users', 'like', "%".auth()->user()->id ."%")
                            ->where('is_deleted', '0')
                            ->where('invisible', '0');
                    })
                    ->whereNotNull('wap.order_id')
                    ->select('wap.*', \DB::raw('(SELECT MAX(created_at) FROM wap_items WHERE wap_items.bin_id = wap.id ) as last,
                                                (SELECT COUNT(*) FROM wap_items WHERE wap_items.bin_id = wap.id ) as item_count'))
                    ->orderBy('order_id', 'ASC')
                    ->get();
        } else {
            $end_date = $request->get('end_date');
            
            $bin_list = Order::join('wap', 'wap.order_id', '=', 'orders.id')
                        ->whereHas('store', function($q){
                            $q->where('permit_users', 'like', "%".auth()->user()->id ."%");
                        })
                        ->where('orders.order_date', '<', $end_date)
                        ->whereNotNull('wap.order_id')
                        ->selectRaw('wap.id')
                        ->where('order_status', '!=', 6)
                        ->get()
                        ->pluck('id')
                        ->toArray(); 
            
            $bins = Wap::with('order.shippable_items', 'order.items')
                        ->whereHas('order', function ($q) {
                            $q->where('order_status', '!=', 6);
                        })
                        ->whereIn('wap.id', $bin_list)
                        ->select('wap.*', \DB::raw('(SELECT MAX(created_at) FROM wap_items WHERE wap_items.bin_id = wap.id ) as last,
                                                    (SELECT COUNT(*) FROM wap_items WHERE wap_items.bin_id = wap.id ) as item_count'))
                        ->orderBy('order_id', 'ASC')
                        ->get();
        }
        
        $sorted_bins = $bins->sortBy('last');
        
        $statuses = Order::statuses();
        
        $stores = Store::list('%', '%', 'none');

//        dd($bins);


        return view('wap.index', compact('bins', 'sorted_bins', 'end_date', 'statuses', 'stores'));
    }


    public function addItems (Request $request)
    {   
        $batch = Batch::find($request->get('id'));
        
        if ($batch->batch_number != $request->get('batch_number')) {
          return redirect()->route('qcShow', ['id' => $request->get('id'), 'batch_number' => $request->get('batch_number')])
                            ->withErrors(['error' => 'Please scan user ID']);
        }
        
        if ( $request->get('action') == 'address') {
          
          $order = Order::find($request->get('order_id'));
          
          if ($order->order_status == 4) {
            
            Log::info('Order ' . $request->get('order_id') . ' added to WAP as address hold.');
            $order->order_status = 11;
            $order->save();
            
          } else {
            
            Log::info('Order ' . $request->get('order_id') . ' failed to add to WAP as address hold.');
            
            return redirect()->route('qcShow', ['id' => $request->get('id'), 'batch_number' => $request->get('batch_number')])
                              ->withErrors('Order status not IN PROGRESS, cannot change status');
          }
        }
        
        $items = Item::where('order_5p', $request->get('order_id'))
                  ->where('batch_number', $request->get('batch_number'))
                  ->searchStatus('production')
                  ->where('is_deleted', '0')
                  ->get();
        
        $label = '';
        
        $shippable = Item::where('order_5p', $request->get('order_id'))
                  ->searchStatus('shippable')
                  ->where('is_deleted', '0')
                  ->get();
        
        $item_codes = $shippable->pluck('item_code')->toArray(); 
        
        $size = 'A';
        
        // foreach ($item_codes as $sku) {
        //   if (substr($sku, 0, 2) == 'RW' || substr($sku, 0, 2) == 'RG' || substr($sku, 0, 2) == 'SD') {
        //     $size = 'B';
        //   }
        // }
      
        $bin = $this->findBin($request->get('order_id'), $size);      
                  
        $user = auth()->user()->id;
        
        $count = count($bin->items) + 1;
        
        foreach ($items as $item) {
          $wapitem = new WapItem;
          $wapitem->item_id = $item->id;
          $wapitem->bin_id = $bin->id;
          $wapitem->user_id = $user;
          $wapitem->item_count = $count++;
          $wapitem->save();
          
          $label .= $this->getLabel($bin, $item, count($shippable));
          
          $item->item_status = 'WAP';
          $item->save();
          
          Order::note('Item ' . $item->id . ' added to WAP Bin ' . $bin->name, $item->order_5p, $item->order_id);
        }
        
        $request->session()->put('label', $label);

        return redirect()->route('qcShow', 
              ['id' => $request->get('id'), 'batch_number' => $request->get('batch_number'), 'label' => 'session', 'label_order' => $request->get('order_id')]);
        
    }
    
    public function bad_address(Request $request) {
      
        $order = Order::find($request->get('order_id'));
        
        if (!$order) {
          return redirect()->back()->withErrors('ERROR: Order Not Found');
        }
        
        if ($order->order_status == 4) {
          Log::info('Order ' . $request->get('order_id') . ' updated in WAP to address hold.');
          $order->order_status = 11;
          $order->save();
          
          return redirect()->action('WapController@index')->withSuccess($order->short_order . ' Address Sent to Customer Service');
          
        } else {
          return redirect()->action('WapController@index')->withErrors($order->short_order . ' Cannot be placed in Address Hold, order is not in progress');
        }
    }
    
    public function reprintWapLabel(Request $request) {
      
      $bin = Wap::find($request->get('bin_id'));
      
      $item = Item::find($request->get('item_id'));
      
      $count = Item::where('order_5p', $bin->order_id)
                ->searchStatus('shippable')
                ->where('is_deleted', '0')
                ->count();
      
      $label = $this->getLabel($bin, $item, $count);
       
      return redirect()->action('WapController@showBin', ['show_ship' => '1', 'label' => $label, 'bin' => $request->get('bin_id')]);
    }
    
    
    private function getLabel($bin, $item, $count) {
      
      $date = date("Y-m-d H:i:s");
      $wap_item = WapItem::where('item_id', $item->id)->first();
      $order = $item->order;
      
      if ($wap_item && ($wap_item->item_count == $count && $order->order_status == 4)) {
        $box = "^FO150,50^GB475,180,120^FS";
      } else {
        $box = '';
      }
      
      if ($order->order_status == 4) {
        $title = '^CF0,200^FO200,60^FR^AC^FDWAP^FS';
      } else {
        $title = '^CF0,100^FO125,60^FR^AC^FDWAP HOLD^FS';
      }
      
      $label = "^XA^FX$box$title^FO50,230^GB700,1,3^FS" .
        "^FX^CF0,30^FO50,260^FDItem ID: $item->id^FS^FO350,260^FDOrder ID: $order->short_order^FS^FO50,300^FDBatch: $item->batch_number^FS" .
        "^FO350,300^FDOrder Date: $order->order_date^FS" .
        "^FO50,340^FDPrinted: $date^FS^FO50,370^GB700,1,3^FS^FO50,370^GB700,1,3^FS" .
        "^FO50,400^FDSKU: $item->child_sku^FS^FO50,440^FB750,3,,^FDQTY: $item->item_quantity^FS" .
        "^FO50,480^FB750,3,,^FD$item->item_description^FS" .
        "^FO75,520^FB550,6,,^FD" . Helper::optionTransformer($item->item_option, 1, 0, 0, 1, 0, ' , ') . "^FS" .
        "^FX^FO50,725^GB700,250,3^FS^CF0,100^FO100,750^FDBin $bin->name^FS^FO100,850^FD Item $wap_item->item_count of $count ^FS" .
        "^FX^BY4,4,100^FO100,1000^BC^FDORDR$item->order_5p^FS^XZ";
      
      return str_replace("'", " ", $label);
      
    }
    
    
    private function binSize ($order_id) 
    {
      
    }


    private function findBin ($order_id, $size = NULL) {
      //find order bin
      $bin = Wap::where('order_id', $order_id)->first();
              
      if (count($bin) < 1) {
        //determine size
        //$size = $this->binSize($order_id);
        // $size = 'A';
        //find empty bin
        $bin_assigned = Wap::whereNull('order_id')
              ->where('size', $size)
              ->orderBy('id', 'ASC')
              ->limit(1)
              ->update([
                        'order_id' => $order_id
                      ]);
        
        if ($bin_assigned == 1) {
          $bin = Wap::where('order_id', $order_id)->first();
        } else {
          //create bin
          $bin = $this->createBin($order_id, $size);
        }
      }
        
        return $bin;
    }
    
    
    private function createBin ($order_ID, $size = NULL)
    {
        //add a wap bin
        $last = Wap::where('size', $size)
                ->orderBy('id', 'DESC')
                ->first();
                
        if (count($last) > 0) {
          $num = $last->num + 1;
        } else {
          $num = 1000;
        }
        
        $bin = new Wap;
        $bin->size = $size;
        $bin->num = $num;
        $bin->name = sprintf('%s-%04d', $bin->size, $bin->num);
        $bin->order_ID = $order_ID;
        $bin->save();
        
        return $bin;
    }


    public function showBin (Request $request)
    {
//        return $request->all();
        $rates = [];
        if($request->has('action')) {
            $ounces = $request->get('ounces');
            $pounds = $request->get('pounds') ? $request->get('pounds') * 16 : 0 ;
            $weight = $pounds + $request->get('ounces');
            if(!$weight){
                return redirect()->back()->withErrors(['error' => 'Please enter accurate amount of weight']);
            }

            $shipment['shipment']['to_address'] = [
                'name' => $request->get('name'),
                'street1' => $request->get('street1'),
                'city' => $request->get('city'),
                'state' => $request->get('state'),
                'zip' => $request->get('zip'),
                'country' => $request->get('country'),
                'phone' => $request->get('phone'),
                'email' => $request->get('email')
            ];

//            $shipment['shipment']['to_address'] = [
//                                'name' => 'Dr. Steve Brule',
//                                'street1' => '4109 BLACKTAIL CT',
//                                'city' => 'CASTLE ROCK',
//                                'state' => 'CO',
//                                'zip' => '80109-7972',
//                                'country' => 'US',
//                                'phone' => '8573875756',
//                                'email' => 'dr_steve_brule@gmail.com'
//                            ];
            if($request->has('submit') && $request->get('submit') == 'Compare price from NY'){
                $shipment['shipment']['from_address'] = [
                    "company" => 'monogramonline',
                    "street1" => '481 Johnson AVE',
                    "street2" => 'A',
                    "city"    => 'Bohemia',
                    "state"   => 'NY',
                    "zip"     => '11716',
                    "country" => 'US',
                    "phone"   => '8563203210'
                ];
            } else {
                $shipment['shipment']['from_address'] = [
                    "company" => 'monogramonline',
                    "street1" => '1891 McFarland Parkway',
                    "street2" => 'A',
                    "city"    => 'Alpharetta',
                    "state"   => 'GA',
                    "zip"     => '30005',
                    "country" => 'US',
                    "phone"   => '631-867-2344'
                ];

                // deprecated address
//                $shipment['shipment']['from_address'] = [
//                    "company" => 'monogramonline',
//                    "street1" => '4050 NE 9th ave',
//                    "street2" => 'A',
//                    "city"    => 'Oakland Park',
//                    "state"   => 'FL',
//                    "zip"     => '33334',
//                    "country" => 'US',
//                    "phone"   => '631-867-2344'
//                ];
            }

            $shipment['shipment']['parcel'] = [
                                'length' => !empty($request->get('length')) ? $request->get('length') : '20',
                                'width' => !empty($request->get('width')) ? $request->get('width') : '10',
                                'height' => !empty($request->get('height')) ? $request->get('height') : '2',
                                'weight' => $weight //onz
                            ];
//            dd($shipment);
//            EasyPost::setApiKey('EZTKcf12d106bf264af5b027250ce8bcc958mjZpIf2maXevSVindGXYJw');
//            $shipment = Shipment::create($shipment);
//            $data = $shipment->get_rates();
//            dd($data->rates);

            $data = $this->comparePrice($shipment);
            if(count($data['rates'])) {
                $rates = $data['rates'];
                usort($rates, function ($a, $b) {
                    $rateA = floatval($a["rate"]);
                    $rateB = floatval($b["rate"]);

                    return $rateA - $rateB;
                });
//                dd($rates);
            } else if (count($data['messages'])){
                $msg = '';
                foreach($data['messages'] as $message) {
                    $msg .= $message['message'] . '<br>' ;
                }
                return redirect()->back()->withErrors(['error' => $msg]);
            } else {
                return redirect()->back()->withErrors(['error' => 'Unknown error on compare price']);
            }


        }


        $show_ship = null;
        $reminder = $request->get('reminder');
        $weightLBS = 0;
        $remainingOZS = 0;
        
        if ($request->has('label')) {
          
          $label = $request->get('label');
          
          if ($request->has('show_ship')) {
            $show_ship = $request->get('show_ship');
          }
                 
        } elseif ($request->has('unique_order_id')) {
    			
    			$filename = 'assets/images/shipping_label/' . $request->get('unique_order_id') . '.zpl';
    			
    			if (file_exists ( $filename )) {
    				$label = file_get_contents( $filename );
    				$label = trim(preg_replace('/\n+/', ' ', $label));
    			} else {
    				session()->flash('error', 'Label Not Found');
    			}
          
        } else {
          $label = null;
        }

        if ($request->has('bin')) {
          
          $bin = Wap::with('items.batch', 'order.shippable_items')
                  ->where('id', $request->get('bin'))
                  ->first();
          
          if (!$bin) {
            return redirect()->action('WapController@index')->withErrors(['error' => 'Bin not found']);
          }
          
          if ($bin->order) {
            $order = $bin->order;
          } else {
            $order = Order::where('id', $request->get('order_id'))->first();
          }
          
          if (!$order) {
            $order = null;
          }
        
        } elseif ($request->has('bin_name')) {
            
            $bin = Wap::with('items.batch', 'order.shippable_items')
                    ->where('name', 'LIKE',  $request->get('bin_name'))
                    ->first();
            
            if (!$bin) {
              return redirect()->back()->withInput()->withErrors(['error' => 'Bin ' . $request->get('bin_name') . ' not found']);
            }
            
            if ($bin->order) {
              $order = $bin->order;
            } else {
              $order = Order::where('id', $request->get('order_id'))->first();
            }
            
            if (!$order) {
              $order = null;
            }
            
        } elseif ($request->has('order_id')) {
          
          if (strtoupper(substr( trim($request->get('order_id')), 0, 4 )) == 'ORDR' ) {
            $order_id = substr( trim($request->get('order_id')), 4 ); 
          } else {
            $order_id = trim($request->get('order_id'));
          }
          
          $bin = Wap::with('items.batch', 'order.shippable_items')
                  ->where('order_id', $order_id)
                  ->first();
                  
          if (count($bin) == 0) {
            
            $order = Order::with('items.batch.station', 'items.shipInfo', 'items.rejections.rejection_reason_info', 'items.inventory_unit.inventory')
                        ->where('short_order', $order_id)
                        ->where('orders.is_deleted', '0')
                        ->first();
            
            if (count($order) > 0) {
              $order_id = $order->id;
              
              $bin = Wap::with('items.batch', 'order.shippable_items', 'order.items.inventory_unit.inventory')
                      ->where('order_id', $order->id)
                      ->first();
              
              if (count($bin) == 0) {
                return redirect()->action('WapController@index')->withErrors(['error' => 'Bin not found']);
              }
              
            } else {
              return redirect()->action('WapController@index')->withErrors(['error' => 'Order not found']);
            }
          } 
          
          $order = $bin->order;
          
        } else {
          return redirect()->action('WapController@index')->withErrors(['error' => 'Bin or Order not specified']);
        }
        
        if (!$order) {
          return redirect()->action('WapController@index')->withErrors(['error' => 'Order not found']);
        }
        
        $item_options = array();
        $thumbs = array();
        
        foreach ($order->items as $item) {
          
          $item_options[$item->id] = Helper::optionTransformer($item->item_option, 1, 1, 1, 1, 0, '<br>');
          
          $thumbs[$item->id] = Sure3d::getThumb($item);
        }

        /**
         * get the OZS(ounces) weight of the inventory where item_status = wap
         * then convert the weight to pounds and ounces
         */
        $weightOZS = 0;
        foreach ($order->items as $item) {
            if ($item->item_status == 'wap'){
                $weight = 0;
                $total = 0;
                $weight = $item->inventory_unit->first()->inventory->sku_weight;
                if($weight > 0){
                    $total = $weight * $item->item_quantity;
                    $weightOZS += $total;
                }
            }
        }
        if($weightOZS > 0){

            $ouncesInOnePound = 16;

            // Calculate pounds and remaining ounces
            if($weightOZS < $ouncesInOnePound) {
                $weightLBS = 0;
                $remainingOZS = $weightOZS;
            } else {
                $weightLBS = floor($weightOZS / $ouncesInOnePound); // Whole pounds
                $remainingOZS = $weightOZS % $ouncesInOnePound; // Remaining ounces
            }
        }
        
        return view('wap.details', compact('bin', 'order', 'label', 'show_ship', 'reminder', 'item_options', 'thumbs', 'rates', 'weightLBS', 'remainingOZS'));
    }
    
    public function missingReport(Request $request)
    {
      
      $batches = Batch::with('itemsCount', 'station', 'first_item', 'store')
                        ->join('items', 'batches.batch_number', '=', 'items.batch_number')
                        ->leftjoin('wap', 'items.order_5p', '=', 'wap.order_id')
                        ->where('items.is_deleted', '0')
                        ->whereNotIn('items.item_status', [2,9,6])
                        ->whereNotNull('wap.order_id')
                        ->searchStation($request->get('station'))
                        ->searchSection($request->get('section_id'))
                        ->searchStore($request->get('store_id'))
                        ->searchStatus($request->get('status'), $request->get('batch'))
                        ->searchMinChangeDate($request->get('start_date'))
                        ->searchMaxChangeDate($request->get('end_date'))
                        ->searchPrinted(3, $request->get('print_date'))
                        ->groupBy('batches.batch_number')
                        ->orderBy('batches.change_date', 'ASC')
                        ->paginate(100);

      $batchlist = array_unique($batches->pluck('batch_number')->toArray());

      // $missing_items = Item::where('is_deleted', '0')
      //                       ->leftjoin('wap', 'items.order_5p', '=', 'wap.order_id')
      //                       ->whereNotIn('items.item_status', [2,9,6])
      //                       ->whereNotNull('wap.order_id')
      //                       ->count();
                            
      $bins = Wap::with('items', 'order.shippable_items')
                  ->whereNotNull('order_id')
                  ->get();
      
      $summary = array();
      
      foreach ($bins as $bin) {
        
        if ($bin->order->order_status != 4) {
          $summary['hold'][$bin->name] = count($bin->items);
        } else if (count($bin->order->shippable_items) == count($bin->items)) {
          $summary['ready'][$bin->name] = count($bin->items);
        } else if (count($bin->order->shippable_items) > count($bin->items)) {
          $summary['incomplete'][$bin->name] = count($bin->items);
        } else {
          $summary['error'][$bin->name] = count($bin->items);
        }
      }
      
      $wap_items = WapItem::count();
      
      $stationsList = Station::select(DB::raw('CONCAT(stations.station_name, " - ", stations.station_description) AS full_station'), 'stations.id')
                ->where('is_deleted', 0)
                ->orderBy('stations.station_name')
                ->get()
                ->pluck('full_station', 'id')
                ->prepend('Select a Station', 'all');
                  
      $statuses = Batch::getStatusList();
      
      $stores = Store::list('1');
      
      $sections = Section::where('is_deleted', '0')
                  ->get()
                  ->pluck('section_name', 'id')
                  ->prepend('Select a Department', '');
                              
      return view('wap.missing_report', compact('batches', 'batchlist', 'missing_items', 'stationsList', 'statuses', 'stores', 'request', 
                                                  'bins', 'summary', 'wap_items', 'missing_items', 'sections'));
    }

    public function comparePrice($shipment)
    {
//        $EASYPOST_API_KEY = 'EZTKcf12d106bf264af5b027250ce8bcc958mjZpIf2maXevSVindGXYJw';
        //TODO::need to make active
//        $EASYPOST_API_KEY = 'EZAKcf12d106bf264af5b027250ce8bcc958jg55lbapz7Z4MNQuc9Z9DA';
        $client = new Client();

        $response = $client->post('https://api.easypost.com/beta/rates', [
            'auth' => [$EASYPOST_API_KEY, ''],
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $shipment
        ]);

// Check the response status code and content
        if ($response->getStatusCode() === 200) {
            $responseData = json_decode($response->getBody()->getContents(), true);
            return $responseData;
            // Handle the response data as needed
        } else {
            // Handle the error response
        }
    }

}
