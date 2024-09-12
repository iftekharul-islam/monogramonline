<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Batch;
use App\BatchScan;
use App\Station;
use App\Order;
use App\Item;
use App\Parameter;
use Monogram\Sure3d;
use Monogram\Helper;
use Ship\Shipper;

class QcController extends Controller
{
    public function index(Request $request)
    {
      $batches = NULL;

      $qc_stations = Station::where('type', 'Q')
                        ->get()
                        ->pluck('id');
      
        $totals = Batch::with('section', 'station', 'route')
                    ->searchStatus('active')
                    ->whereIn('station_id', $qc_stations)
                    ->whereHas('store', function($q){
                        $q->where('permit_users', 'like', "%".auth()->user()->id ."%");
                    })
                    ->groupBy('station_id')
                    ->orderBy('section_id')
                    ->selectRaw('section_id, station_id, batch_route_id, COUNT(*) as count')
                    ->get();
        
        
      return view('quality_control.index', compact('totals'));
    }
    
    public function showStation (Request $request) 
    {
      if (!$request->has('station_id')) {
        return redirect()->action('QcController@index')->withErrors('Station not set');
      }

      session(['station_id' => $request->get('station_id')]);
      
      $station = Station::find($request->get('station_id'));
      
      if (!$station || $station->type != 'Q') {
        return redirect()->action('QcController@index')->withErrors('Invalid Station');
      }
      
      $batches = Batch::with('station', 'scanned_in.in_user', 'store') 
                 ->join('items', 'batches.batch_number', '=', 'items.batch_number') 
                 ->searchStatus('active') 
                 ->where('station_id', $request->get('station_id')) 
                 ->where('items.item_status', 1) 
                 ->groupBy('batches.batch_number') 
                 ->orderBy('batches.min_order_date', 'ASC') 
                 ->get(); 
      
      return view('quality_control.show_station', compact('batches', 'station')); 
    }
    
    public function scanIn (Request $request)
    {
//        dd($request->all());
//      if ($request->has('user_barcode')) {
//
//        $user = trim($request->get('user_barcode'));
//
//        try {
//          $user_id = intval( substr($user, 4, -1) / 8 );
//        } catch (\Exception $e) {
//          return redirect()->action('QcController@index')->withErrors(['error' => sprintf('Invalid User ID')]);
//        }
//
//        if (!Auth::onceUsingId($user_id)) {
//
//            return redirect()->action('QcController@index')->withErrors(['error' => sprintf('User not found')]);
//        }
//
//      } else {
//        return redirect()->action('QcController@index')->withErrors(['error' => sprintf('User not Entered')]);
//      }
      
      if ($request->has('batch_number')) {
        
        if (substr( trim( $request->get('batch_number')) , 0, 4) == 'BATC') {
          $batch_number = substr( trim( $request->get('batch_number')), 4);
        } else {
          $batch_number = trim( $request->get('batch_number'));
        }
        
        $batch = Batch::with('items', 'station')
                     ->where('batch_number', 'LIKE', $batch_number)
                     ->first();

        if (count($batch) == 0) {
                 
             return redirect()->action('QcController@index')->withErrors(['error' => sprintf('Batch %s not found', $batch_number)]);
         } else {
          
              if ($batch->status != 'active') {
                  
                  $related = Batch::related($batch_number);
                  
                  if ($related != false) {
                    return redirect()->action('QcController@scanIn', ['batch_number' => $related->batch_number, 'user_barcode' => $request->get('user_barcode')]);
                  } else {
                    return redirect()->action('QcController@index')->withErrors(['error' => sprintf('Problem with Batch %s', $batch_number)]);
                  }
              }
            $user_id =auth()->user()->id;

            if ($batch->station->type == 'P') {
//                dd($batch->station->type, $user_id);
                $graphicsController = new GraphicsController;
                $graphicsController->moveNext($batch_number, 'qc');
                Batch::note($batch_number,$batch->station_id,'1','Special Move to QC');
                $batch = Batch::with('items', 'station')
                    ->where('batch_number', 'LIKE', $batch_number)
                    ->first();
            }

              if ($batch->station->type != 'Q') {
                  return redirect()->action('QcController@index')->withErrors(['error' => sprintf('Batch %s not in QC station', $batch_number)]);
              }
         }
       } else {
         return redirect()->action('QcController@index')->withErrors(['error' => sprintf('Batch not entered')]);
       }
       
       $scan = new BatchScan;
       $scan->batch_number = $batch->batch_number;
       $scan->station_id = $batch->station_id;
       $scan->in_user_id = $user_id;
       $scan->in_date = date("Y-m-d H:i:s");
       $scan->save();      
       
       return redirect()->action('QcController@showBatch', ['id' => $batch->id, 'batch_number' => $batch->batch_number]);
      
    }
    
    
    public function scanOut ($batch_number) 
    {
      $scan = BatchScan::where('batch_number', $batch_number)->latest()->get();
              
      $scan = $scan[0];
      $scan->out_user_id = auth()->user()->id;
      $scan->out_date = date("Y-m-d H:i:s");
      $scan->save();      

    }
    
    public function showBatch(Request $request)
    { 
        $batch_number = $request->get('batch_number');
        $id = $request->get('id');
        $reminder = $request->get('reminder');
        
        if ($request->has('label') && $request->get('label') != 'session') {
          $label = $request->get('label');
        } else if ($request->get('label') == 'session') {
          $label = $request->session()->pull('label', 'default');
        } else {
          $label = null;
        }
        
        if ($request->has('unique_order_id')) {
    			
    			$filename = 'assets/images/shipping_label/' . $request->get('unique_order_id') . '.zpl';
    			
    			if (file_exists ( $filename )) {
    				$label = file_get_contents( $filename );
    				$label = trim(preg_replace('/\n+/', ' ', $label));
    			} else {
    				session()->flash('error', 'QC Label Not Found');
    			}
    		}
        
        if ($request->has('label_order')) {
          $label_order = $request->get('label_order');
        } else {
          $label_order = null;
        }
        
        if ($request->has('batch_number')) {
                            
          $qc_stations = Station::where('type', 'Q')
                            ->get()
                            ->pluck('id');

          $batch = Batch::with('items.order.customer', 'items.wap_item.bin', 'prev_station', 'station', 'scanned_in.in_user')
                  ->searchStatus('qc_view')
                  ->whereIn('station_id', $qc_stations)
                  ->where('batch_number', $batch_number)
                  ->where('batches.id', $id)
                  ->first(); 
          
          if (!$batch) {
            return redirect()->action('QcController@index')->withErrors(['error' => sprintf('Batch %s not found', $batch_number)]);
          }
          
          if (!$batch->scanned_in) {
            return redirect()->action('QcController@index')->withErrors(['error' => sprintf('Batch %s not Scanned Into QC', $batch_number)]);
          }
          
          if (isset($batch->items)) {
            
            //needs better logic to check order, other item statuses
            $complete = Item::searchStatus('production')
                              ->where('batch_number', $batch_number)
                              ->groupBy('batch_number')
                              ->where('is_deleted', '0')
                              ->count();

            if ($complete == 0) {
              $this->scanOut($batch->batch_number);
              
              if ($batch->status != 'empty' && $batch->status != 'complete') {
                $batch->status = 'complete';
                $batch->save();
              }
            }
          }
          
          $options = array();
          
          foreach ($batch->items as $item) {
            $options[$item->id] = Helper::optionTransformer($item->item_option, 0, 1, 1, 0, 0, '<br>');
          }
          
      } else {
        return redirect()->action('QcController@index')->withErrors(['error' => sprintf('No Batch Number Provided'),]);
      }
      
      $order_ids = array_unique($batch->items->pluck('order_5p')->toArray());
      
      if ($batch->status == 'active' && count($order_ids) == 1) {
        return redirect()->action('QcController@showOrder', ['batch_number' => $batch_number, 'id' => $id, 'order_5p' => $batch->items->first()->order_5p]);
      } else {
        return view('quality_control.batch', compact('id', 'batch', 'batch_number', 'options', 'label', 'label_order', 'user', 'reminder'));
      }
    }

    public function showOrder (Request $request)
    {
        $ship_from = '';
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
                $ship_from = 'NY';
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
                $ship_from = 'GA';
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

            $data = $this->comparePrice($shipment);
            if(count($data['rates'])) {
                $rates = $data['rates'];
                usort($rates, function ($a, $b) {
                    $rateA = floatval($a["rate"]);
                    $rateB = floatval($b["rate"]);

                    return $rateA - $rateB;
                });
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
        $weightLBS = 0;
        $remainingOZS = 0;
      $batch_number = $request->get('batch_number');
      $id = $request->get('id');
      $order_5p = $request->get('order_5p');
      
      if ($request->has('label') && $request->get('label') != 'session') {
        $label = $request->get('label');
      } else if ($request->get('label') == 'session') {
        $label = $request->session()->pull('label', 'default');
      } else {
        $label = null;
      }
      
      $qc_stations = Station::where('type', 'Q')
                        ->get()
                        ->pluck('id');

      $batch = Batch::with('scanned_in.in_user', 'items')
              ->where('batch_number', $batch_number)
              ->where('batches.id', $id)
              ->first(); 
      
      if (!$batch) {
        return redirect()->action('QcController@index')->withErrors(['error' => sprintf('Batch %s not found', $batch_number)]);
      }
      
      if ($batch->status != 'active') {
        return redirect()->action('QcController@index')->withErrors(['error' => sprintf('Batch %s status is %s', $batch_number, $batch->status)]);
      }
      
      if (!$batch->scanned_in) {
        return redirect()->action('QcController@index')->withErrors(['error' => sprintf('Batch %s not Scanned Into QC', $batch_number)]);
      }

      $order = Order::find($order_5p);
      
      if (!$order) {
        return redirect()->action('QcController@index')->withErrors(['error' => sprintf('Order %s not found', $order_5p)]);
      } 
      
      if ($order->order_status == 6 || $order->order_status == 8) {
        $statuses = Order::statuses();
        $order_ids = array_unique($batch->items->pluck('order_5p')->toArray());
        
        if (count($batch->items) > 1 && count($order_ids) > 1) {
          return redirect()->action('QcController@showBatch', ['batch_number' => $batch_number, 'id' => $id])
                         ->withErrors(['error' => sprintf('Order %s - %s', $order->short_order, $statuses[$order->order_status])]);
        } else {
          return redirect()->action('QcController@index')
                         ->withErrors(['error' => sprintf('Order %s - %s', $order->short_order, $statuses[$order->order_status])]);
        }
      }
      
      $items = Item::searchStatus('shippable')
                      ->where('order_5p', $order_5p)
                      ->where('batch_number', $batch_number)
                      ->where('is_deleted', '0')
                      ->get();
      
      if (!$items || count($items) == 0) {
        return redirect()->action('QcController@index')->withErrors(['error' => 'No Shippable Items found']);
      } 
      
      $order_count = Item::searchStatus('shippable')
                      ->where('order_5p', $order_5p)
                      ->where('is_deleted', '0')
                      ->count();
      
      if (count($items) == $order_count && $order->order_status == 4) {
          $dest = 'ship';
      } else {
          $dest = 'wap';
      }

      $item_options = array();
      $thumbs = array();
      
      foreach ($items as $item) {
        
        $item_options[$item->id] = Helper::optionTransformer($item->item_option, 1, 1, 1, 1, 0, '<br>');
        
        $thumbs[$item->id] = Sure3d::getThumb($item);
      }

        /**
         * get the OZS(ounces) weight of the inventory where item_status = wap
         * then convert the weight to pounds and ounces
         */
        $weightOZS = 0;
        foreach ($items as $item) {
                $total = 0;
                $weight = isset($item->inventory_unit->first()->inventory->sku_weight) ? $item->inventory_unit->first()->inventory->sku_weight : 0;
                if($weight > 0){
                    $total = $weight * $item->item_quantity;
                    $weightOZS += $total;
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

//        dd($weightLBS, $remainingOZS);

      return view('quality_control.order', compact('id', 'batch', 'batch_number', 'order', 'item_options', 'items',
                                                    'dest', 'btn_title', 'btn_text', 'btn_class', 'thumbs', 'label', 'ship_from', 'rates', 'weightLBS', 'remainingOZS'));
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
