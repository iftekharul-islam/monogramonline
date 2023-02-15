<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Store;
use Monogram\AppMailer;

class Notification extends Model
{
  public function setUpdatedAtAttribute($value)
  {
      // to Disable updated_at
  }

  public static function orderConfirm ($order) {
    
    $store = Store::where('store_id', $order->store_id)->first();
    
    if ($store->confirm == '2' || $store->confirm == '3') {
        if ( Appmailer::storeConfirmEmail($store, $order, 'emails.order_confirm') ) {
              Log::info(sprintf("Order Confirmation Email sent to %s Order# %s.", $order->customer->bill_email, $order->order_id));
              
              $record = new Notification;
              $record->type = 'Order Confirmation';
              $record->order_5p = $order->id;
              $record->save();
        } else {
          Order::note('Email Order Confirmation Failed to Send', $order->id, $order->order_id);
        }
    }
    
    if ($store->confirm == '1' || $store->confirm == '3') {
        $className =  'Market\\' . $store->class_name; 
        $controller =  new $className;
        $controller->orderConfirmation($store->store_id, $order);
    }
  }

    public static function orderFailure ($orderid) {
        $store = Store::where('store_id', "52053153")->first();
        Appmailer::storeFailureEmail($store, $orderid, 'emails.order_failure');
        Log::info(sprintf("Order insert fail  Order# %s", $orderid));

    }
}
