<?php

namespace App\Http\Controllers;

use App\Batch;
use App\Item;
use App\Order;
use App\Ship;
use App\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Monogram\Ship\DHL;
use Ship\Shipper;

class ShippingController extends Controller
{
    public static $search_in = [
        'unique_order_id' => 'Package ID',
        'mail_class' => 'Shipped Via',
        'order_id' => 'Order',
        'batch_number' => 'Batch Number',
        'tracking_number' => 'Tracking number',
        'tracking_type' => 'Tracking Type',
        'item_id' => 'Item id',
        'name' => 'Name',
        'address_one' => 'Address 1',
        'company' => 'Company',
        'city' => 'City',
        'state' => 'State',
        'postal_code' => 'Postal code',
        'country' => 'Country',
        'email' => 'Email',
        'user' => 'Shipped By'
    ];


    public function index(Request $request)
    {
        $label = null;
        $error = null;
        $reminder = $request->get('reminder');

        if ($request->has('label')) {

            $label = $request->get('label');

        } elseif ($request->has('unique_order_id')) {

            $filename = 'assets/images/shipping_label/' . $request->get('unique_order_id') . '.zpl';
            if (file_exists($filename)) {

                $label = file_get_contents($filename);
                $label = trim(preg_replace('/\n+/', ' ', $label));
                $pattern= '/"/';
                $label = preg_replace($pattern, '', $label);
//                dd("Exist ",$filename, $label);
            } else {
//                dd("No Exist ",$filename);
                session()->flash('error', 'Label Not Found');
            }
        }

        if (!$request->has('search_for_first') && !$request->has('search_for_second')
            && !$request->has('start_date') && !$request->has('end_date')) {

            $start_date = date("Y-m-d");
        } else {
            $start_date = $request->get('start_date');
        }

        if ($request->has('unique_order_id')) {
            $ships = Ship::with('items.batch', 'user')
                ->searchStoreId($request->get('store_id'))
                ->where('is_deleted', 0)
                ->where('unique_order_id', $request->get('unique_order_id'))
                ->groupBy('tracking_number')
                ->latest('transaction_datetime')
                ->paginate(10);
        } else {
            $ships = Ship::with('items.batch', 'user')
                ->where('is_deleted', 0)
                ->searchCriteria($request->get('search_for_first'), $request->get('search_in_first'))
                ->searchCriteria($request->get('search_for_second'), $request->get('search_in_second'))
                ->searchStoreId($request->get('store_id'))
                ->searchWithinDate($start_date, $request->get('end_date'))
                // postmark_date transaction_datetime
                ->groupBy('tracking_number')
                ->latest('transaction_datetime')
                ->paginate(10);
        }

        $stores = Store::list('%', '%', 'none');

        $yesterday = $last30 = date("Y-m-d H:i:s", strtotime('-1 days'));

        return view('shipping.index', compact('ships', 'request', 'label', 'stores', 'reminder', 'yesterday'))
            ->with('search_in', static::$search_in);
    }


    public function shipItems(Request $request)
    {
        if ($request->has('order_id') && $request->has('origin')) {

            $shipper = new Shipper;

            $packages = array();

            $item_ids = $request->get("selected-items-json", null);
            if($item_ids !== null) {
                $item_ids = json_decode($item_ids, true);
            }


            $ounces = $request->get('ounces');
            $pounds = $request->get('pounds');

            foreach ($pounds as $index => $pounds) {

                $weight = $pounds;

                if (isset($ounces[$index]) && $ounces[$index] != null) {

                    if (!is_numeric($weight) || !is_numeric($ounces[$index])) {
                        return redirect()->back()->withInput()->withErrors('Weight must be a number');
                    }

                    $weight += $ounces[$index] / 16;
                }
                $packages[] = $weight;

            }

            if ($packages == []) {
                $packages[] = 0;
            }

            if($request->get('origin')=='QC' || $request->get('origin') == 'WAP') {
                $params = [];
                if ($request->get('location') === 'NY') {
                    $params = [
                        'from_address'=> [
                            "company" => 'ALL INCLUSIVE',
                            "street1" => '481 Johnson AVE',
                            "street2" => 'A',
                            "city"    => 'Bohemia',
                            "state"   => 'NY',
                            "zip"     => '11716',
                            "country" => 'US',
                            "phone"   => '8563203210'
                        ]
                    ];
                }
            }

            if ($request->get('origin') == 'QC' && $request->has('batch_number')) {

                $batch = Batch::find($request->get('id'));

                if ($batch->batch_number != $request->get('batch_number')) {
                    return redirect()->route('qcShow', ['id' => $request->get('id'), 'batch_number' => $request->get('batch_number')])
                        ->withErrors(['error' => 'Batch Number not correct']);
                }


                $ship_info = $shipper->createShipment($request->get('origin'), $request->get('order_id'), $request->get('batch_number'), $packages, $item_ids, $params);
	
                if (is_array($ship_info) && isset($ship_info['reminder'])) {

                    return redirect()->route('qcShow',
                        ['id' => $request->get('id'),
                            'batch_number' => $request->get('batch_number'),
                            'unique_order_id' => $ship_info['unique_order_id'],
                            'label_order' => $request->get('order_id'),
                            'reminder' => $ship_info['reminder']]);

                } else if (is_array($ship_info) && $ship_info[0] == 'ambiguous') {

                    // $ambiguousAddress = $label[1];
                    // $customer_id = $label[2];
                    // $order_id = $request->get('order_id');
                    // $batch_number = $request->get('batch_number');
                    // $origin = $request->get('origin');
                    //
                    // $order = Order::find($order_id);
                    // $customer = $order->customer;
                    //
                    // return view('shipping.choose_address', compact('customer_id', 'ambiguousAddress', 'order_id', 'origin', 'batch_number', 'customer'));

                    return redirect()->route('qcOrder', ['id' => $request->get('id'),
                        'batch_number' => $request->get('batch_number'),
                        'order_5p' => $request->get('order_id')
                    ])
                        ->withErrors(['error' => 'Address Validation Failed - Ambiguous address']);

                } else {
                    Log::info('1. ShipItems: ' . $ship_info);
                    return redirect()->route('qcOrder', ['id' => $request->get('id'),
                        'batch_number' => $request->get('batch_number'),
                        'order_5p' => $request->get('order_id')
                    ])
                        ->withErrors(['error' => $ship_info]);
                }

            } elseif ($request->get('origin') == 'WAP') {

                $ship_info = $shipper->createShipment($request->get('origin'), $request->get('order_id'), null, $packages, $item_ids, $params);

                if (is_array($ship_info) && isset($ship_info['reminder'])) {

                    return redirect()->route('wapShow', ['bin' => $request->get('bin'),
                        'order_id' => $request->get('order_id'),
                        'unique_order_id' => $ship_info['unique_order_id'],
                        'reminder' => $ship_info['reminder']]);

                } else if (is_array($ship_info) && $ship_info[0] == 'ambiguous') {

                    return redirect()->route('wapShow', ['bin' => $request->get('bin'), 'order_id' => $request->get('order_id')])
                        ->withErrors(['error' => 'Address Validation Failed - Ambiguous address']);

                } else {
                    Log::info('2. ShipItems: ' . $ship_info);
                    return redirect()->route('wapShow', ['bin' => $request->get('bin'), 'order_id' => $request->get('order_id')])
                        ->withErrors(['error' => $ship_info]);
                }

            } else if ($request->get('origin') == 'OR') {

                $ship_info = $shipper->createShipment(
                    $request->get('origin'), $request->get('order_id'), null, $packages, $item_ids, $params
                );

                if (is_array($ship_info) && isset($ship_info['reminder'])) {

                    return redirect()->route('orderShow', ['order_id' => $request->get('order_id'),
                        'unique_order_id' => $ship_info['unique_order_id'],
                        'reminder' => $ship_info['reminder']]);

                } else if (is_array($ship_info) && $ship_info[0] == 'ambiguous') {

                    return redirect()->route('orderShow', ['order_id' => $request->get('order_id')])
                        ->withErrors(['error' => 'Address Validation Failed - Ambiguous address']);

                } else {
                    Log::info('3. ShipItems: ' . $ship_info);
                    return redirect()->route('orderShow', ['order_id' => $request->get('order_id')])
                        ->withErrors(['error' => $ship_info]);
                }

            } else {
                Log::error('4. ShipItems: Parameter error');
                return 'Parameter error';
            }

        } else {
            Log::info('5. ShipItems: Origin or order_id not set');
            return 'Origin or order_id not set.';
        }
    }

    public function shipFromOrder(Request $request)
    {

        $order = Order::with('store', 'customer')
            ->find($request->get('order_id'));

        if (count($order) == 0) {
            return redirect()->back()->withErrors('ERROR: Order Not Found');
        }

        if (empty($order->customer)) {
            return redirect()->back()->withErrors('ERROR: Customer Not Found');
        }

        if (empty($order->store)) {
            return redirect()->back()->withErrors('ERROR: Store Not Found');
        }

        $shipper = new Shipper;
        $result = $shipper->shipOrder($order, $request->get('reship'));

        if (is_array($result)) {
            return redirect()->action('ShippingController@index', ['unique_order_id' => $result['unique_order_id'],
                'reminder' => $result['reminder']]);
        } else {
            return redirect()->back()->withErrors($result);
        }

    }

    public function manualShip(Request $request)
    {

        /*
         * Stops orders from being duplicated
         */
        $track = $request->get('track_number', "ERR");

        if(Cache::has("TRACKING_DUPLICATE_$track")) {
            dd("...");
        } else {
            Cache::add("TRACKING_DUPLICATE_$track", $track, 60 * 3);
        }

        if (strlen($request->get('track_number')) > 0) {

            $shipper = new Shipper;

            $info = $shipper->enterTracking($request->get('track_item_id'), $request->get('track_order_id'),
                $request->get('track_number'), $request->get('method'));


            if (is_array($info)) {
                $shipper->setOrderFulfillment($request->get('track_shopify_order_id'), $request->get('track_shopify_item_line_id'), $request->get('track_shopify_item_quantity'), $request->get('track_number'), $request->get('method')); // method = $trackingCompany
                return redirect()->action('ShippingController@index', ['unique_order_id' => $info['unique_order_id'],
                    'reminder' => $info['reminder']
                ]);
            } else {
                return redirect()->back()->withErrors($info);
            }

        } else {
            return redirect()->back()->withErrors(['error' => "Tracking number not set"]);
        }
    }


    public function shipmentReturned(Request $request)
    {

        $items = Item::where('tracking_number', $request->get('tracking_number'))
            ->where('is_deleted', '0')
            ->get();

        foreach ($items as $item) {
            $item->tracking_number = NULL;
            $item->item_status = 'reshipment';
            $item->save();
        }

        $shipment = Ship::where('tracking_number', $request->tracking_number)
            ->where('is_deleted', '0')
            ->first();

        if (!$shipment) {
            return redirect()->back()->withErrors('Shipment not found');
        }

        $order_id = $shipment->order_number;

        $shipment->is_deleted = '1';
        $shipment->save();

        $order = Order::find($order_id);

        if (!$order) {
            $order = Order::where('order_id', $order_id)->where('is_deleted', '0')->first();
        }

        if ($order) {
            $order->order_status = 10;
            $order->save();

            Order::note("Shipment returned. Tracking number " . $shipment->tracking_number, $order->id, $order->order_id);
        }

        return redirect()->action('OrderController@details', ['order_id' => $order->id]);
    }

    public function shippingAddressUpdate(Request $request)
    {

        if (!$request->has('customer_id')) {
            return redirect()
                ->back()
                ->withErrors([
                    'error' => 'No Customer Id found',
                ]);
        }


        Customer::where('id', $request->get('customer_id'))
            ->where('is_deleted', 0)
            ->update([
                'ship_address_1' => $request->get('address1'),
                'ship_city' => $request->get('city'),
                'ship_state' => $request->get('state_city'),
                'ship_zip' => $request->get('postal_code'),
                'ship_country' => $request->get('country'),
            ]);

        $shipper = new Shipper;

        $weight = 0;

        if ($request->has('pounds')) {
            $weight = $request->get('pounds');
        }

        if ($request->has('ounces')) {
            $weight += $request->get('ounces') / 16;
        }

        $shipper->createShipment($request->get('origin'), $request->get('order_id'), $request->get('batch_number'), $packages);

    }

    public function void($shipment_id, $order_5p)
    {
        if ($shipment_id == null) {
            return redirect()->back()->withInput()->withErrors('Shipment ID Required');
        }

        $shipper = new Shipper;
        $response = $shipper->voidShipment($shipment_id);

        return redirect()->action('OrderController@details', ['order_id' => $order_5p]);

    }

    public function dhlTrack($tracking)
    {
        $dhl = new DHL();
        $dhl->trackByNumber($tracking);
    }

    public function getDhlManifest(Request $request)
    {
//        dd($request->all(), $request->get("dhlManifest_date"));
//        $dhlManifestDate = $request->get("dhlManifest_date");
        $dhl = new DHL();
        $dhl->getDhlManifest($request->get("dhlManifest_date"));
    }

    public function getDhlInternationalManifest(Request $request)
    {
//        dd($request->all(), $request->get("dhlInternationalManifest_date"));
//        $dhlManifestDate = $request->get("dhlManifest_date");
        $dhl = new DHL();
        $dhl->getDhlInternationalManifest($request->get("dhlInternationalManifest_date"));
    }

}
