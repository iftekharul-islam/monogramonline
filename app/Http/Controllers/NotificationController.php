<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

use App\Http\Requests\BulkEmailPostRequest;
use Monogram\AppMailer;
use App\Notification;
use Monogram\TemplateExtractor;
use App\Order;
use App\Item;
use App\Ship;
use App\Store;
use App\EmailTemplate;

class NotificationController extends Controller
{ 
  public function test ($order_id, $view) {
    
    $order = Order::find($order_id);
    $store = Store::where('store_id', $order->store_id)->first();
    
    return view('emails.' . $view, compact('order', 'store'));
  }
  
  public static function shipNotify () {

    $ships = Ship::with('order.customer', 'items')
          ->whereNull('shipping_unique_id')
          ->whereNotNull('tracking_number')
          ->groupBy('unique_order_id')
          ->orderBy('id', 'ASC')
          ->take(500)
          ->get();
    
    if (count($ships) < 1) {
      return;
    }
    
    $orders = array();
    
    $stores = array();
    
    foreach ($ships as $ship){
      Ship::where('order_number', $ship->order_number)
        ->update([
          'shipping_unique_id' => 'pro',
        ]);
        
      $stores[$ship->order->store_id][] = $ship;
    }
    
    foreach ($stores as $store_id => $shipments) {
      
      $store = Store::where('store_id', $store_id)->first();
    
      if ($store->ship == '2' || $store->ship == '3') {
          set_time_limit(0);
                      
          foreach ($shipments as $shipment) {
            
            $order = $shipment->order;
            
            if ( !$order->customer->bill_email ) {
              
              Log::error('No email address found for order ' . $order->id);
                Ship::where('id',  $shipment->id)
                ->update([
                  'shipping_unique_id' => 'No Email',
                ]);
                
            } else { // if (!substr($order->items->first()->item_code, 0, 3) == 'KIT') {
    
              if(AppMailer::storeConfirmEmail($store, $order, 'emails.ship_confirm')){
                Log::info( sprintf("Shipping Confirmation Email sent to %s Order %s.", $order->customer->bill_email, $order->id) );
    
                Ship::where('id',  $shipment->id)
                  ->update([
                    'shipping_unique_id' => 's',
                  ]);
                  
                  $record = new Notification;
                  $record->type = 'Shipment Notification ' . $shipment->unique_order_id;
                  $record->order_5p = $order->id;
                  $record->save();

              } else {
                
                Ship::where('id',  $shipment->id)
                ->update([
                  'shipping_unique_id' => 'Not',
                ]);
                
                Order::note('Email Shipping Confirmation Failed to Send', $order->id);
                Log::error('No shipping confirmation email sent for order# '.$order->id);
              }
            // } else {
            //     Log::info('No shipping confirmation email sent for order# '.$order->id);
             }
          }
      }
      
      if ($store->ship == '1' || $store->ship == '3') {
        $className =  'Market\\' . $store->class_name; 
        $controller =  new $className;
        
        try {
          $controller->shipmentNotification($store->store_id, $shipments);
        } catch (\Exception $e) {
          Log::error('Shipment Notification failure ' . $store->store_name);
        }
        
      }
    }

  }
  
  public function getMailMessage (Request $request)
  {
    $message_type = $request->get('message_type');

    $order = Order::with('items', 'customer', 'store')
              ->where('id', $request->get('order_id'))
              ->where('is_deleted', '0')
              ->first();
    
    $subject = '';
    $message_body = '';
    
    if ( $message_type != 'Email' ) {
      
      $extractor = new TemplateExtractor;
      
      if ($request->has('param_1')) {
        $param_1 = $request->get('param_1');
      } else {
        $param_1 = null;
      }
      
      if ($request->has('param_2')) {
        $param_2 = $request->get('param_2');
      } else {
        $param_2 = null;
      }
      
      $msg = $extractor->getMessage($order, $message_type, $param_1, $param_2);
    }
  
    return response()->json([
      'error'   => false,
      'subject' => $msg['subject'],
      'message' => $msg['message'],
    ]);
  }
  
  public function send_mail (Request $request)
  {
    $message_type = $request->get('message_types');
    $subject = $request->get('subject');
    $recipient = $request->get('recipient');
    $order_id = $request->get('order_5p');
    
    $message = '<html><head><style>p{margin-bottom:2em;}</style><body>' . 
              $request->get('message') . 
              '</body></html>';

    $order = Order::with('customer', 'store')
            ->where('id', $order_id)
            ->where('is_deleted', '0')
            ->first();
      
    if ( !$order ) {
      return response()->json([ ], 404);
    }
    
    $from = $order->store->email;
    $from_name = $order->store->store_name;
    
    
    if (AppMailer::sendMessage($from, $from_name, $recipient, $subject, $message)) {
      
      Order::note("Email Subj:". $subject, $order->id, $order->order_id);
      
      $record = new Notification;
      $record->type = $subject;
      $record->order_5p = $order->id;
      $record->save();
      
      return 'E-mail sent to customer';
      
    } else {
      
      Log::error('Message Failed to Send:' . $subject . ' - ' . $order->order_id);
      Order::note("Message Failed to Send:". $subject, $order->id, $order->order_id);
      
      return 'E-mail failed to send';
    }
    
  }
  
  public function bulk_email (Request $request)
  {
    $templates = EmailTemplate::where('is_deleted', 0)->get()->pluck('message_type', 'id');
    
    if ($request->has('batch_number')) {
      
      if (!is_array($request->get('batch_number'))) {
        $batch_numbers = [$request->get('batch_number')];
      } else {
        $batch_numbers = array_unique($request->get('batch_number'));
      }
      
      $order_ids = '';
      
      $items = Item::whereIn('batch_number', $batch_numbers)
                      ->where('is_deleted', '0')
                      ->selectRaw('DISTINCT order_5p')
                      ->get()
                      ->pluck('order_5p')
                      ->toArray();
                      
      $order_ids .= ',' . implode(',', $items);
      
      $order_ids = trim($order_ids, ',');
      $id_type = '5p';
      
    } else {
      $id_type = $request->get('id_type');
      $order_ids = $request->get('order_ids');
      
      if (is_array($order_ids)) {
        $tmp = ',' . implode(',', array_unique($order_ids));
        
        $order_ids = trim($tmp, ',');
      }
    }
    
    return view('email_templates.bulk_email', compact('id_type', 'order_ids'))->withTemplates($templates);
  }	
  
  public function bulk_email_post (BulkEmailPostRequest $request)
	{
		//$order_ids = array_filter(array_map('intval', explode(",", $request->get('order_ids'))));
		// Jewel change on 20170125
		$order_ids = array_filter( explode(",", trim ( preg_replace ( '/\s+/', ',', $request->get('order_ids')))));
		
    if ($request->get('id_type') != '5p') {
  		$order_ids_in_database = Order::where('is_deleted', '0')->whereIn('short_order', $order_ids)->get();
    } else {
      $order_ids_in_database = Order::where('is_deleted', '0')->whereIn('id', $order_ids)->get();
    }
    
		if ( count($order_ids) != count($order_ids_in_database) ) {
      
      $order_ids_in_database = $order_ids_in_database->pluck('short_order')->all();
      
      $diff = array_diff($order_ids, $order_ids_in_database);
      
			return redirect()
				->back()
				->withInput()
				->withErrors(['order_ids' => implode (',', $diff) . " order ids are invalid"]);
		}

    $success = array();
    $error = array();
    
		foreach ( $order_ids_in_database as $order ) {
			set_time_limit(0);
			
      $extractor = new TemplateExtractor;
      
      if ($request->has('param_1')) {
        $param_1 = $request->get('param_1');
      } else {
        $param_1 = null;
      }
      
      if ($request->has('param_2')) {
        $param_2 = $request->get('param_2');
      } else {
        $param_2 = null;
      }
      
      $msg = $extractor->getMessage($order, $request->get('template'), $param_1, $param_2);
      
      if (AppMailer::sendMessage($order->store->email, $order->store->store_name, $order->customer->bill_email, $msg['subject'], $msg['message'])) {
  			Order::note("E-mail Sent ". $msg['message_type'], $order->id, $order->order_id);
        
        $record = new Notification;
        $record->type = $msg['message_type'];
        $record->order_5p = $order->id;
        $record->save();
        
        $success[] = "E-mail Sent for " . $order->short_order;
        
      } else {
        Order::note("Message Failed to Send ". $msg['message_type'], $order->id, $order->order_id);
        
        $error[] = "E-mail Failed for " . $order->short_order;
      }
      
		}
    
		return redirect()->back()->withSuccess($success)->withErrors($error);
	}
  
}
