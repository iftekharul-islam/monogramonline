<?php namespace Monogram;

use Mail;
use Illuminate\Support\Facades\Log;

class AppMailer
{
	public static function sendMessage($from, $from_name, $to, $subject, $email_body)
	{
				$httpClient = new \GuzzleHttp\Client();
				$response = null;
				$url = 'https://api.mailgun.net/v3/'.env('MAILGUN_DOMAIN').'/massages';
				$mailGunApi = ['api' => env('MAILGUN_SECRET')];
				
				try {
					Mail::send('emails.all_email_placeholder', compact('email_body'),
						function ($m) use ($from, $from_name, $to, $subject) {
									$m->from($from, $from_name);
									$m->to($to)
									->subject($subject)
									->setContentType('text/html');
								});
				} catch (\Exception $e) {
					Log::error('AppMailer sendMessage: ' . $e->getMessage());
					return false;
				}
				
				return true;
	}
	
	public static function storeConfirmEmail ($store, $order, $view)
	{
		if ( ! $order->customer->bill_email ) {
			Log::error('No Billing email address found for order# ' . $order->id . ' storeConfirmEmail.');
			return false;
		}
		
		$from = $store->email;
		$sender_name = $store->store_name;
		$subject = $order->customer->bill_full_name . " - Your Order Status with " . $store->store_name . " (Order # " . $order->short_order . ")";
		$to = $order->customer->bill_email;
		
		$httpClient = new \GuzzleHttp\Client();
		$response = null;
		$url = 'https://api.mailgun.net/v3/'.env('MAILGUN_DOMAIN').'/massages';
		$mailGunApi = ['api' => env('MAILGUN_SECRET')];
		
		try {
			Mail::send($view, compact('order', 'store'), function ($m) use ($from, $sender_name, $to, $subject) {
							$m->from($from, $sender_name);
							$m->to($to)
							->subject($subject)
							->setContentType('text/html');
			});
		} catch (\Exception $e) {
			Log::error('AppMailer storeConfirmEmail: ' . $e->getMessage());
			return false;
		}
		
		return true;
	}


    public static function storeFailureEmail ($store, $orderid, $view)
    {

        $from = $store->email;
        $sender_name = $store->store_name;
        $subject = "Order insert fail  Order# ". $orderid;
        #$to = "shlomi@monogramonline.com";
        $to = "tarikuli@yahoo.com";
        $cc = "cs@monogramonline.com";
        try {
            Mail::send($view, compact('orderid', 'store'), function ($m) use ($from, $sender_name, $to, $cc,$subject) {
                $m->from($from, $sender_name);
                $m->to($to)
                    ->cc($cc)
                    ->subject($subject)
                    ->setContentType('text/html');
            });
        } catch (\Exception $e) {
            Log::error('AppMailer storeFailureEmail: ' . $e->getMessage());
            return false;
        }

        return true;
    }
	
}