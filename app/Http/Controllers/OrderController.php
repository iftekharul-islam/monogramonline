<?php

namespace App\Http\Controllers;

use App\Batch;
use App\Customer;
use App\Item;
use App\Note;
use App\Notification;
use App\Option;
use App\Order;
use App\Parameter;
use App\Product;
use App\Store;
use App\Wap;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Monogram\Batching;
use Monogram\CSV;
use Monogram\Helper;
use Monogram\ImageHelper;
use Ship\Shipper;
use function GuzzleHttp\Promise\all;

class OrderController extends Controller
{
    protected $archiveFilePath = "";
    protected $remotArchiveUrl = "https://order.monogramonline.com/media/archive/";
    protected $archivePath = "/media/RDrive/archive/";
    protected $sort_root = '/media/RDrive/';
    private $domain = "order.monogramonline.com";
    private $full_domain = "https://order.monogramonline.com";


    public function __construct()
    {
        $this->domain = App::environment('Development') ? 'dev.monogramonline.com' : 'order.monogramonline.com';
        $this->full_domain = App::environment('Development') ? 'http://dev.monogramonline.com' : 'https://order.monogramonline.com';
        $this->remotArchiveUrl = App::environment('Development') ? 'https://dev.monogramonline.com/media/archive/' : 'https://order.monogramonline.com/media/archive/';
    }

    public function show($id)
    {
        Log::error('Order Show called for id ' . $id . ' - ' . URL::previous());
    }

    public function getList(Request $request)
    {
        if($request->has('start_date')) {
            $start = $request->get('start_date');
        } else {
            if(!$request->has('search_for_first') && !$request->has('search_for_second') && !$request->has('store') && !$request->has('status')) {
                $start = date("Y-m-d");
            } else {
                $start = null;
            }
        }

        if($request->has('status') || $request->has('search_for_first') || $request->has('search_for_second') || $request->has('store')) {
            $status = $request->get('status');
        } else {
            $status = 'not_cancelled';
        }

        $orders = Order::with('store', 'customer', 'items')->where('is_deleted',
            '0')->storeId($request->get('store'))->status($status)->searchShipping($request->get('shipping_method'))->withinDate($start,
            $request->get('end_date'))->search($request->get('search_for_first'), $request->get('operator_first'),
            $request->get('search_in_first'))->search($request->get('search_for_second'),
            $request->get('operator_second'), $request->get('search_in_second'))
            //->groupBy('order_id')
            ->latest()->paginate(50);

        $duplicateData = Order::where('is_deleted',
            '0')->storeId($request->get('store'))->status($status)->searchShipping($request->get('shipping_method'))->withinDate($start,
            $request->get('end_date'))->search($request->get('search_for_first'), $request->get('operator_first'),
            $request->get('search_in_first'))->search($request->get('search_for_second'),
            $request->get('operator_second'), $request->get('search_in_second'))->select('short_order')
            ->groupBy('short_order')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $duplicates = [];
        foreach ($duplicateData as $duplicate) {
            $duplicates []= $duplicate->short_order;
        }

        $statuses = Order::statuses();

        $stores = Store::list('%', '%', 'none');

        $companies = Store::$companies;

        $total = Order::where('is_deleted',
            '0')->storeId($request->get('store'))->status($status)->searchShipping($request->get('shipping_method'))->withinDate($start,
            $request->get('end_date'))->search($request->get('search_for_first'), $request->get('operator_first'),
            $request->get('search_in_first'))->search($request->get('search_for_second'),
            $request->get('operator_second'),
            $request->get('search_in_second'))->selectRaw('SUM(total) as money, SUM(shipping_charge) as shipping, SUM(tax_charge) as tax')->first();

        $operators = [
            'in' => 'In',
            'not_in' => 'Not In',
            'starts_with' => 'Starts With',
            'ends_with' => 'Ends With',
            'equals' => 'Equals',
            'not_equals' => 'Not Equal',
            'less_than' => 'Less Than',
            'greater_than' => 'Greater Than',
            // 'blank' => 'Is Blank',
            // 'not_blank' => 'Is Not Blank'
        ];

        return view('orders.lists',
            compact('orders', 'stores', 'statuses', 'request', 'operators', 'total', 'companies', 'duplicates'))->with('search_in',
            Order::$search_in);
    }

    public function csvExport(Request $request)
    {
        if(count($request->all()) > 0) {

            $header = [
                'id',
                'short_order',
                'purchase_order',
                'store_id',
                'order_date',
                'item_count',
                'sub_total',
                'coupon_id',
                'coupon_value',
                'promotion_id',
                'promotion_value',
                'gift_wrap_cost',
                'adjustments',
                'insurance',
                'tax_charge',
                'shipping_charge',
                'total',
                'order_ip',
                'bill_email',
                'bill_full_name',
                'ship_state',
            ];
            //ini_set('memory_limit','16M');
            set_time_limit(0);

            $offset = 0;
            $filename = sprintf("orders_%s.csv", date("Y_m_d_His", strtotime('now')));

            while ($offset < $request->get('count')) {
                $orders = Order::join('customers', 'orders.customer_id', '=',
                    'customers.id')->where('orders.is_deleted',
                    '0')->storeId(unserialize($request->get('store')))->status(unserialize($request->get('status')))->searchShipping($request->get('shipping_method'))->withinDate($request->get('start_date'),
                    $request->get('end_date'))->search($request->get('search_for_first'),
                    $request->get('operator_first'),
                    $request->get('search_in_first'))->search($request->get('search_for_second'),
                    $request->get('operator_second'),
                    $request->get('search_in_second'))->groupBy('orders.id')->latest('orders.created_at')->limit(5000)->offset($offset)->get([
                    'orders.id',
                    'orders.short_order',
                    'orders.purchase_order',
                    'orders.store_id',
                    'order_date',
                    'item_count',
                    'sub_total',
                    'coupon_id',
                    'coupon_value',
                    'promotion_id',
                    'promotion_value',
                    'gift_wrap_cost',
                    'adjustments',
                    'insurance',
                    'tax_charge',
                    'shipping_charge',
                    'total',
                    'order_ip',
                    'bill_email',
                    'bill_full_name',
                    'customers.ship_state',
                ])->toArray();

                $csv = new CSV;
                $pathToFile = $csv->createFile($orders, 'assets/exports/', $header, $filename);
                $offset += 5000;
            }
            return response()->download($pathToFile)->deleteFileAfterSend(true);
        }
    }

    public function details($order_id)
    {
        $order = Order::with('customer', 'items.shipInfo', 'items.batch.station', 'items.product',
            'items.allChildSkus', 'items.parameter_option', 'notes.user')
            ->where('is_deleted', '0')
            ->find($order_id);

        if(\request()->has("debug")) {
            dd($order);
        }
        if(!$order) {
            return view('errors.404');
        }

        //noinspection JSUnusedGlobalSymbols
        $batched = $order->items->filter(function ($item) {
            return $item->batch_number != '0';
        })->count();

        $stores = Store::list();

        $statuses = Order::statuses();

        $status_selector = array();

        $status_selector[$order->order_status] = $statuses[$order->order_status];

        if($order->order_status == 4) {
            if(!$batched) {
                $status_selector['Prevent Production'] = ['13' => 'Payment / Fraud Hold', '23' => 'Other Hold'];

                if($order->ship_date != null) {
                    $status_selector['Prevent Shipping'] = ['7' => 'Shipping Hold', '12' => 'Hold until Ship Date'];
                } else {
                    $status_selector['Prevent Shipping'] = ['7' => 'Shipping Hold'];
                }
            } else {
                if($order->ship_date != null) {
                    $status_selector['Prevent Shipping'] = [
                        '13' => 'Payment / Fraud Hold',
                        '23' => 'Other Hold',
                        '7' => 'Shipping Hold',
                        '12' => 'Hold until Ship Date'
                    ];
                } else {
                    $status_selector['Prevent Shipping'] = [
                        '13' => 'Payment / Fraud Hold',
                        '23' => 'Other Hold',
                        '7' => 'Shipping Hold'
                    ];
                }
            }

            $status_selector['8'] = 'Cancel Order';

            if(auth()->user()->accesses->where('page', 'orders_admin')->all()) {
                $status_selector['X'] = 'Delete Order';
            }

        } else {
            if($order->order_status == 6 || $order->order_status == 8) {
                // do nothing
            } else {
                $status_selector['4'] = 'Release Hold';
                $status_selector['8'] = 'Cancel Order';
            }
        }

        $shipping_methods = Shipper::listMethods();

        return view('orders.details',
            compact('order', 'order_id', 'batched', 'statuses', 'status_selector', 'shipping_methods', 'stores'));
    }

    public function changeStatus(Request $request)
    {
        if($request->get('current_status') == 6) {
            return redirect()->back()->withErrors('Cannot unship and order');
        }

        if($request->get('new_status') == 'X') {
            $msg = $this->destroy($request->get('order'));
            return redirect()->action('OrderController@getList')->withSuccess($msg);
        }

        $order = Order::with('items')->where('id', $request->get('order'))->first();

        if($request->get('new_status') == 8) {
            $shipped_items = false;
            foreach ($order->items as $item) {
                if($item->item_status != 'shipped') {
                    if($item->batch_number != '0') {

                        $batch_number = $item->batch_number;
                        $item->batch_number = '0';
                        $item->save();

                        Order::note("Item $item->id Cancelled and removed from Batch $batch_number", $order->id,
                            $order->order_id);
                        Batch::note($batch_number, '', '4', "Item $item->id Cancelled and removed from Batch");
                        Batch::isFinished($batch_number);
                    }

                    if($item->item_status == 'wap') {
                        Wap::removeItem($item->id, $item->order_5p);
                    }

                    $item->item_status = 6;
                    $item->save();
                } else {
                    $shipped_items = true;
                }
            }

            if($shipped_items == false) {
                $order->order_status = 8;
                $order->save();
                Order::note('CS: Order Cancelled: ' . $request->get('status_note'), $order->id);
            } else {
                $order->order_status = 6;
                $order->save();
            }
        } else {
            $order->order_status = $request->get('new_status');
            $order->save();

            Order::note('OH: Reason - ' . $request->get('status_note'), $order->id);
        }
        return redirect()->action('OrderController@details', ['order_id' => $order->id]);
    }

    public function destroy($id)
    {
        $order = Order::find($id);
        if(!$order) {
            return view('errors.404');
        }

        foreach ($order->items as $item) {
            $item->is_deleted = '1';
            $item->save();

            if($item->item_status == 'wap') {
                Wap::removeItem($item->id, $item->order_5p);
            }

            if($item->batch_number != '0') {
                Batch::isFinished($item->batch_number);
            }
        }

        $order->is_deleted = '1';
        $order->save();

        return 'Order ' . $order->short_order . ' Deleted';
    }


    public function batchTest()
    {
        Batching::auto(0, [52053153], 1);
    }
    public function synOrderBetweenId(Request $request)
    {
        # https://order.monogramonline.com/synOrderBetweenId?since_id_from=2940557361315&since_id_to=2947019079843
        $shopifyOrdeIds = [];
        $ordersIn5p = [];

        if($request->get("since_id_from")) {
            $sinceIdFrom = $request->get("since_id_from");
        } else {
            dd('since_id_from = not exist ');
        }

        if($request->get("since_id_to")) {
            $sinceIdTo = $request->get("since_id_to");
        } else {
            dd('since_id_to = not exist ');
        }

//        for ($x = $sinceIdFrom; $x <= $sinceIdTo; $x++) {
//            echo "The number is: $x <br>";
//        }
        echo "The time is " . date("H");
//        "created_at_min" => $created_at_min . "T".date("H").":00:00-05:00", #2020-04-01T00:00:00-05:00
        $created_at_min = date("Y-m-d T H:i:s-05:00", strtotime('-2 hour'));
        $created_at_max = date("Y-m-d", strtotime('-0 days'));


        $array = array(
//            "created_at_min" => $created_at_min . "T16:00:00-05:00", #2020-04-01T00:00:00-05:00
//            "created_at_min" => $created_at_min . "T".date("H").":00:00-05:00", #2020-04-01T00:00:00-05:00
            "created_at_min" => $created_at_min, #2020-04-01T00:00:00-05:00
            "created_at_max" => $created_at_max . "T23:59:59-05:00", #2020-04-13T23:59:59-05:00
//                "created_at_max" => $created_at_max . "T23:59:59-05:00", #2020-04-13T23:59:59-05:00
            "limit" => 250,
            "fields" => "created_at,id,name,total-price"
        );

//dd("synOrderBetweenId", $array, $created_at_min, $sinceIdFrom, $sinceIdTo);

        if($request->get('since_id_from')) {

            $helper = new Helper;

            $array = array(
                "since_id" => 2942795514019,
                "fields" => "created_at,id,name,total-price"
            );

            $orderInfo = $helper->shopify_call("/admin/api/2023-01/orders.json", $array, 'GET');
            $orderInfo = json_decode($orderInfo['response'], JSON_PRETTY_PRINT);

            if(isset($orderInfo['errors'])) {
                dd($orderInfo['errors'], " Order not found");
            }

            Log::info("----------------" . $sinceIdFrom . "------------" . $sinceIdTo . "--------------------------");

            $shopifyOrdeIdsWithName = [];
            foreach ($orderInfo['orders'] as $key => $order) {
                $shopifyOrdeIds[$order['id']] = $order['id'];
                $shopifyOrdeIdsWithName[$order['name']] = $order['id'];
//                Log::info("Order_id from Shopify = ".$order['id']);
            }
            $shopifyOrdeIdsx = $shopifyOrdeIds;
//dd($array, $orderInfo, $shopifyOrdeIds,$shopifyOrdeIdsWithName);
            $created_at_min = "sdsd";
            $created_at_max = "sdsds";

            ########### Code for get list of orders numbers by Date ###################
            $existingOrders = Order::where('orders.is_deleted', '0')->where('orders.order_date', '>=',
                $created_at_min . ' 00:00:00')->where('orders.order_date', '<=',
                $created_at_max . ' 23:59:59')->where('orders.store_id', '=',
                '52053153')->latest('orders.created_at')->limit(5000)->get([
                'orders.short_order',
                'orders.order_id',
                'order_date',
            ])->toArray();

            foreach ($existingOrders as $key => $orderId) {
//                Log::info("Order_id from 5p = ".$orderId['short_order']);
                $ordersIn5p[] = $orderId['short_order'];
                if(isset($shopifyOrdeIds[$orderId['short_order']])) {
                    unset($shopifyOrdeIds[$orderId['short_order']]);
                }
            }
            ########### Code for get list of orders numbers by Date ###################
            if(empty($shopifyOrdeIds)) {
                dd("Nothing to insert",
                    "Number of orders in shopify= " . count($shopifyOrdeIdsx) . " - Number of orders in 5p= " . count($existingOrders) . " = diff = " . (count($shopifyOrdeIdsx) - count($existingOrders)),
                    "Missing Orders = ", $shopifyOrdeIds, "Following already inserted: ", $shopifyOrdeIdsWithName);
            }

            $ch = curl_init();
            foreach ($shopifyOrdeIds as $key => $orderId) {
                $url = "https://" . $this->domain . "/getshopifyorder?orderid=" . $orderId;
                curl_setopt($ch, CURLOPT_URL, $url);
                $result = curl_exec($ch);
                Log::info(print_r($result));
            }
            curl_close($ch);

            dd("Number of orders in shopify= " . count($shopifyOrdeIdsx) . " - Number of orders in 5p= " . count($existingOrders) . " = diff = " . (count($shopifyOrdeIdsx) - count($existingOrders)),
                "Missing Orders = ",
                $shopifyOrdeIds,
                $shopifyOrdeIdsx,
                $ordersIn5p,
                $shopifyOrdeIdsWithName
//                $existingOrders,
//                $orderInfo
            );
        } else {
            echo "synOrderBetweenId: Order Not found, https://order.monogramonline.com/synOrderBetweenId?since_id_from=2940557361315&since_id_to=2947019079843";
        }
    }

    public function lock()
    {
        logger('Shopify lock called..');
        touch(storage_path() . '/autoShopify.lock');
    }

    public function unlock ()
    {
        logger('Shopify unlock called..');
        if (file_exists(storage_path() . '/autoShopify.lock')) {
            unlink(storage_path() . '/autoShopify.lock');
        }
    }

    public function isLocked()
    {
        logger('Shopify isLocked called..');
        if (file_exists(storage_path() . '/autoShopify.lock')) {
            logger('Shopify isLocked called and file lock file exists..');
            if (filemtime(storage_path() . '/autoShopify.lock') > (time() - (60 * 60))) {
                logger('Shopify isLocked called file lock file is greater than time and keep this..');
                return true;
            } else {
                logger('Shopify isLocked called file lock file is greater than time and remove this..');
                $this->unlock();
            }
        }

        return false;
    }

    public function getShopifyOrdersForUpdateTNumbers()
    {
//        $array = array(
//            "ids" => 5364730560675,
//            'fulfillment_status'=> 'partial',
//            "fields" => "id,fulfillments,line_items,financial_status,fulfillment_status"
//        );

//        $helper = new Helper;
//        $orderInfo = $helper->shopify_call("/admin/api/2023-04/fulfillments.json", $array, 'POST');
//        $orderInfo = $helper->shopify_call("/admin/api/2024-01/orders/5291922915491/fulfillments.json");
//        $orderInfo = $helper->shopify_call("/admin/api/2024-01/fulfillments/5291922915491/update_tracking.json", $array, 'POST');
//        $orderInfo = $helper->shopify_call("/admin/api/2024-01/orders/5291693801635.json", $array, 'PUT');
//        $orderInfo = $helper->shopify_call("/admin/api/2024-01/orders.json", $array, 'GET');
//        $orderInfo = json_decode($orderInfo['response'] ?? [], JSON_PRETTY_PRINT);
//        $orderInfo = $orderInfo;

//        dd($orderInfo);
        logger('Shopify Orders For Update Tracking Numbers called..');
        // TODO :: Get orders where tracking number is null and created in last 30 days
//        $thirtyDaysAgo = Carbon::now()->subDays(30);
//        $items = Item::where('created_at', '>=', $thirtyDaysAgo)
//            ->where('tracking_number', null)
//            ->select('id','item_id','order_id', DB::raw("SUBSTRING_INDEX(order_id, '-', -1) as order_id_suffix"))
//            ->groupBy(DB::raw("SUBSTRING_INDEX(order_id, '-', -1)"))
//            ->get();

//        $orderIds = $items->pluck('order_id_suffix')->toArray();

//        if (is_array($orderIds)) {
//            $orderIdsString = implode(',', $orderIds);
//        } else {
//            logger('The variable $orderIds is not an array.');
//            return ;
//        }

        $array = array(
//            'ids' => 5337508937891,
            'fulfillment_status'=> 'partial,fulfilled',
            "fields" => "id,fulfillments,line_items,financial_status,fulfillment_status"
        );

        $helper = new Helper;
        $orderInfo = $helper->shopify_call("/admin/api/2024-01/orders.json", $array, 'GET');
        $orderInfo = json_decode($orderInfo['response'] ?? [], JSON_PRETTY_PRINT);
        if(isset($orderInfo['orders']) && count($orderInfo['orders'])) {
            $this->updateItemTNumbersForShopify($orderInfo['orders']);
        } else {
            logger('No orders found in shopify');
        }

        logger('Shopify Orders For Update Tracking Numbers ended..');
    }

    public function updateItemTNumbersForShopify($orders)
    {
        logger('update Item Tracking Numbers For Shopify called..');
        foreach ($orders as $key => $order) {
            if(isset($order['fulfillments'])) {
                foreach ($order['fulfillments'] as $fulfillment) {
                    if(isset($fulfillment['line_items'])) {
                        foreach ($fulfillment['line_items'] as $lineItem) {
                            logger('Order ID: ' . $order['id'] . ' & line item id :'. $lineItem['id']);
                            $item = Item::where('item_id', $lineItem['id'])->where('tracking_number', null)->first();
                            if ($item) {
                                logger('Order ID: ' . $order['id'] . ' - Tracking Number: ' . $fulfillment['tracking_number'] . ' updated on 5p item id: ' . $item->id);
                                $trackingNumber = $fulfillment['tracking_number'];
                                $item->tracking_number = $trackingNumber;
                                $item->tracking_synced = 1;
                                $item->save();
                            } else {
                                logger('Item already have tracking number / not found with item id: ' . $lineItem['id']);
                            }
                        }
                    }
                }
            } else {
                logger('Order ID: ' . $order['id'] . ' - No fulfillment found');
            }
        }

        logger('update Item Tracking Numbers For Shopify ended..');
    }

    public function synOrderByDate(Request $request)
    {
        logger('shopify order sync called..');
        if ($this->islocked()) {
            logger('Auto Shopify fetch already executing .. ');
            return 'Auto Shopify fetch already executing .. ';
        }

        $this->lock();

        $shopifyOrdeIds = [];
        $ordersIn5p = [];

        if($request->get("created_at_min")) {
            $created_at_min = $request->get("created_at_min");
        } else {
            #$created_at_min = date("Y-m-d"); // 2020-03-01
            $created_at_min = date("Y-m-d TH:i:s", strtotime('-2 hour'));
        }

        if($request->get("created_at_max")) {
            $created_at_max = $request->get("created_at_max") . " T23:59:59-05:00";
        } else {
            $created_at_max = date("Y-m-d") . " T23:59:59-05:00"; // 2020-03-01
        }

        if($request->get('created_at_max')) {

            $array = array(
                "created_at_min" => $created_at_min, #2020-04-01T00:00:00-05:00
                "created_at_max" => $created_at_max, #2020-04-13T23:59:59-05:00
                "limit" => 250,
                "fields" => "created_at,id,name,total-price"
            );

            $helper = new Helper;
            $orderInfo = $helper->shopify_call("/admin/api/2023-01/orders.json", $array, 'GET');
            $orderInfo = json_decode($orderInfo['response'] ?? [], JSON_PRETTY_PRINT);
//            dd($array, $orderInfo);
            if(isset($orderInfo['errors'])) {
                dd($orderInfo['errors'], " Order not found");
            }

            Log::info("----------------" . $created_at_max . "------------" . $created_at_min . "--------------------------");

            $shopifyOrdeIdsWithName = [];

            foreach ($orderInfo['orders'] as $key => $order) {
                $shopifyOrdeIds[$order['id']] = $order['id'];
                $shopifyOrdeIdsWithName[$order['name']] = $order['id'];
            }

            $shopifyOrdeIdsx = $shopifyOrdeIds;
            $created_at_min = substr($created_at_min, 0, 10);
            $created_at_max = substr($created_at_max, 0, 10);

            $existingOrders = Order::where('orders.is_deleted', '0')->where('orders.order_date', '>=',
                $created_at_min . ' 00:00:00')->where('orders.order_date', '<=',
                $created_at_max . ' 23:59:59')->latest('orders.created_at')->limit(5000)->get([
                'orders.short_order',
                'orders.order_id',
                'order_date',
            ])->toArray();

            foreach ($existingOrders as $key => $orderId) {
                $ordersIn5p[] = $orderId['short_order'];
                if(isset($shopifyOrdeIds[$orderId['short_order']])) {
                    unset($shopifyOrdeIds[$orderId['short_order']]);
                }
            }
            ########### Code for get list of orders numbers by Date ###################
            if(empty($shopifyOrdeIds)) {
                dd("Nothing to insert",
                    "Number of orders in shopify= " . count($shopifyOrdeIdsx) . " - Number of orders in 5p= " . count($existingOrders) . " = diff = " . (count($shopifyOrdeIdsx) - count($existingOrders)),
                    "Missing Orders = ", $shopifyOrdeIds, "Following already inserted: ", $shopifyOrdeIdsWithName);
            }

            foreach ($shopifyOrdeIds as $key => $orderId) {
                $request = new Request();
                $request->request->add(['orderid' => $orderId]);
                $data = $this->getShopifyOrder($request);
            }

            Batching::auto(0, [52053153], 1);

            logger('shopify order sync completed..');

            $this->unlock();

            dd("Number of orders in shopify= " . count($shopifyOrdeIdsx) . " - Number of orders in 5p= " . count($existingOrders) . " = diff = " . (count($shopifyOrdeIdsx) - count($existingOrders)),
                "Missing Orders = ", $shopifyOrdeIds, $shopifyOrdeIdsx, $ordersIn5p, $shopifyOrdeIdsWithName, $array,
                count($existingOrders)
            );
        } else {
            echo "orderno: Order Not found, https://monogramonline.myshopify.com/admin/api/2023-01/orders.json?created_at_max=2020-04-13T23:59:59-05:00&created_at_min=2020-04-01T00:00:00-05:00&fields=id,created_at";
        }
    }

    public function getShopifyOrder(Request $request)
    {
        if($request->get('orderid')) {
            $ids = $request->get('orderid');
        } else {
            dd("orderid not found. http://dev.monogramonline.com/getshopifyorder?orderid=2112301105285 ");
        }

        // test purpose 5164781797539
        $array = ['ids' => $ids];
        $helper = new Helper;
        $orderInfo = $helper->shopify_call("/admin/api/2023-01/orders.json", $array, 'GET');
        try {
            $orderInfo = json_decode($orderInfo['response'], JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            Log::error('getShopifyOrderError = ' . $e->getMessage());

        }
        if(empty($orderInfo['orders'])) {
            dd("Order Id's'not found", [$ids]);
        }
        return $this->pushPurchaseToOms($orderInfo, $request);

    }

    public function pushPurchaseToOms($orderInfos, Request $request)
    {
        $helper = new Helper;

        foreach ($orderInfos as $orderIds) {
//            try {
                foreach ($orderIds as $orderId) {

                    if(!isset($orderId['billing_address'])) {
                        $billingAddress = $orderId['shipping_address'];
                    } else {
                        $billingAddress = $orderId['billing_address'];
                    }

                    if(isset($orderId['discount_codes'])) {
                        $discounts = $orderId['discount_codes'];
                    } else {
                        $discounts = '';
                    }

                    $customerDetails = $orderId['customer'];

                    $purchaseData['Bill-Address1'] = $billingAddress['address1'];
                    $purchaseData['Bill-Address2'] = $billingAddress['address2'];
                    $purchaseData['Bill-City'] = $billingAddress['city'];
                    $purchaseData['Bill-Company'] = '';
                    $purchaseData['Bill-Country'] = $billingAddress['country'];
                    $purchaseData['Bill-Email'] = $customerDetails['email'];
                    $purchaseData['Bill-Firstname'] = $billingAddress['first_name'];
                    $purchaseData['Bill-Lastname'] = $billingAddress['last_name'];
                    $purchaseData['Bill-Name'] = $billingAddress['first_name'] . " " . $billingAddress['last_name'];
                    $purchaseData['Bill-Phone'] = $billingAddress['phone'];
                    $purchaseData['Bill-State'] = $billingAddress['phone'];
                    $purchaseData['Bill-Zip'] = $billingAddress['phone'];
                    $purchaseData['Bill-maillist'] = 'no';
                    $purchaseData['Card-Expiry'] = 'xx/xxxx';
                    $purchaseData['Card-Name'] = 'PayPal';
                    $purchaseData['Comments'] = $orderId['note'];#
                    $purchaseData['Coupon-Description'] = (isset($discounts[0]['type'])) ? $discounts[0]['type'] : ""; //$discounts['type'];
                    $purchaseData['Coupon-Id'] = (isset($discounts[0]['code'])) ? $discounts[0]['code'] : "";//$discounts['code'];
                    $purchaseData['Coupon-Value'] = (isset($discounts[0]['amount'])) ? $discounts[0]['amount'] : 0; //$discounts['amount'];
                    $purchaseData['Date'] = $orderId['created_at'];
                    $purchaseData['ID'] = "52053153-" . $orderId['id'];
                    $purchaseData['Purchase-Order'] = $orderId['order_number']; // This is temp for 6p, in 5p not required. we will check later.
                    $purchaseData['IP'] = $orderId['browser_ip'];


                    $shippingMethod = (isset($orderId['shipping_lines'][0]['code'])) ? $orderId['shipping_lines'][0]['code'] : "";

                    $index = 1;
                    $orderItems = $orderId['line_items'];
                    foreach ($orderItems as $item) {

                        if(empty($item['sku'])) {
                            continue;
                        }
                        $purchaseData['Item-Code-' . $index] = $item['sku'];
                        $purchaseData['Item-Description-' . $index] = $item['name'];
                        $purchaseData['Item-Id-' . $index] = $item['id'];
                        $purchaseData['Item-Quantity-' . $index] = $item['quantity'];
                        $purchaseData['Item-Taxable-' . $index] = ($item['taxable']) ? 'Yes' : 'No';
                        $purchaseData['Item-Unit-Price-' . $index] = $item['price'];
                        $purchaseData['Item-Url-' . $index] = "https://monogramonline.myshopify.com/products/" . preg_replace('/\W+/',
                                '-', strtolower($item['name']));
                        $purchaseData['Item-Thumb-' . $index] = "<img border=0 width=70 height=70 src=" . $this->getImaeUrl($item['product_id']) . ">";
                        $itemOptions = $item['properties'];

                        // Start Code for get image from PDF file
                        if(count($itemOptions) > 0) {
                            foreach ($itemOptions as $value) {
                                ########## Add Sure3d url for Download image ##################
                                if(isset($value['name'])) {
                                    if($value['name'] == "_pdf") {
                                        $purchaseData['Item-Option-' . $index . '-' . trim(str_replace(":", "",
                                            "Custom_EPS_download_link"))] = $helper->getUrlWithoutParaMeter($value['value']);
                                    }
                                }
                                ########## Add Sure3d url for Download image ##################
                                $keyName = trim(ucwords(strtolower($value['name'])));

                                ##### Don't save following key in item option #####
                                if($helper->isKeyExist($item['sku'], $value['name'], $value['value'])) {
                                    continue;
                                }
                                ##### Don't save following key in item option #####
                                if($value['name'] == "Preview") {
                                    $purchaseData['Item-Option-' . $index . '-' . trim(str_replace(":", "", $keyName))] = $helper->getUrlWithoutParaMeter($value['value']);
                                } elseif($value['name'] == '_zakekeZip') {
                                    $purchaseData['Item-Option-' . $index . '-' . trim(str_replace(":", "", $keyName))] = $value['value'];
                                } else {
//                                    Log::info("---------------------------------------------------------------------------------");
                                    $purchaseData['Item-Option-' . $index . '-' . trim(str_replace(":", "", $keyName))] = $helper->optionsValuesFilter($value['value']);
                                }
                            }
                        }
                        $index++;
                    }

                    $purchaseData['Item-Count'] = ($index - 1);
                    $purchaseData['Numeric-Time'] = strtotime($orderId['created_at']);

                    #What to do for paypal
                    $purchaseData['PayPal-Address-Status'] = 'Confirmed';
                    $purchaseData['PayPal-Auth'] = '8F4701569X6000947';
                    $purchaseData['PayPal-Merchant-Email'] = 'pablo@dealtowin.com';
                    $purchaseData['PayPal-Payer-Status'] = 'Unverified';
                    $purchaseData['PayPal-TxID'] = '75692712YB5948433';
                    #-------------------------------------------------

                    $shippingAddress = $orderId['shipping_address'];
                    $purchaseData['Ship-Address1'] = $shippingAddress['address1'];
                    $purchaseData['Ship-Address2'] = $shippingAddress['address2'];
                    $purchaseData['Ship-City'] = $shippingAddress['city'];
                    $purchaseData['Ship-Company'] = '';
                    $purchaseData['Ship-Country'] = $shippingAddress['country'];
                    $purchaseData['Ship-Firstname'] = $shippingAddress['first_name'];
                    $purchaseData['Ship-Lastname'] = $shippingAddress['last_name'];
                    $purchaseData['Ship-Name'] = $shippingAddress['first_name'] . " " . $shippingAddress['last_name'];
                    $purchaseData['Ship-Phone'] = $shippingAddress['phone'];
                    $purchaseData['Ship-State'] = $shippingAddress['province'];
                    $purchaseData['Ship-Zip'] = $shippingAddress['zip'];
                    $purchaseData['Shipping'] = (isset($orderId['shipping_lines'][0]['code'])) ? $orderId['shipping_lines'][0]['code'] : "";
                    $purchaseData['Shipping-Charge'] = (isset($orderId['shipping_lines'][0]['code'])) ? $orderId['shipping_lines'][0]['price'] : 0; #$orderObject->getShippingAmount(); # Which Shipping Charge
                    $purchaseData['Space-Id'] = '';
                    $purchaseData['Store-Id'] = '52053153'; //$storeID
                    $purchaseData['Store-Name'] = 'www.monogramonline.myshopify.com'; // http://dev.monogramonline.com/stores/14/edit?
                    $purchaseData['Tax-Charge'] = $orderId['total_tax'];
                    $purchaseData['Total'] = $orderId['total_price'];

                    $request->merge($purchaseData);
                    $data = $this->hookNew($request);

//                    dd($data);

//
//                    $url = "http://" . $this->domain . "/hook";
//                    $response = $this->curlPost($url, $purchaseData);
//                    $result = json_decode($response, true);
//                    dd($result);
//
//                    $json = json_decode($result, TRUE);
//                    Log::info("------------ Insert status----------------  " . $orderId['id'] . " created_at= " . $orderId['created_at']);
//                    Log::info($result['message']);
                }
//            } catch (Exception $e) {
//                Log::info('Shopify Order push error = (' . $e->getMessage() . ') sent');
//            }

        }
    }

    public function hookNew(Request $request)
    {
        set_time_limit(0);
        $order_id = $request->get('ID');

        $previous_order = Order::with('items')->where('order_id', $order_id)->where('is_deleted',
            '0')->orderBy('created_at', 'DESC')->first();

        if($previous_order) {
            Log::info('Hook: Duplicate order can not inserted (status) = ' . $order_id);
            return response()->json([
                'error' => true,
                'message' => "Order in DB, status can not in process, data can't be inserted",
            ], 200);

            $batched = $previous_order->items->filter(function ($item) {
                return $item->batch_number != '0';
            })->count();

            if($batched > 0) {
                Log::info('Hook: Duplicate order not inserted (batched) ' . $order_id);
                return response()->json([
                    'error' => true,
                    'message' => "Batch exists data can't be inserted",
                ], 200);
            } else {
                if($previous_order->order_status != 4) {
                    Log::info('Hook: Duplicate order not inserted (status)' . $order_id);
                    return response()->json([
                        'error' => true,
                        'message' => "Order in DB, status not in process, data can't be inserted",
                    ], 200);
                } else {
                    Order::where('order_id', $order_id)->update(['is_deleted' => 1]);
                    Item::where('order_id', $order_id)->update(['is_deleted' => 1]);
                    Customer::where('order_id', $order_id)->update(['is_deleted' => 1]);
                }
            }
        }
        $this->jdbg(__LINE__, "----------------Start Hook Call for order = ".$request->get('ID')."--------------------");
        try {
            $exploded = explode("-", $order_id);
            if(isset($exploded[2])) {
                $short_order = $exploded[2];
            } else {
                $short_order = $exploded[1];
            }
        } catch (Exception $e) {
            Log::error('Undefined offset when trying to create short order. Order ' . $order_id);
            Log::error($request);
            if(strlen($order_id) < 1) {
                exit('no order');
            } else {
                $short_order = $order_id;
            }
        }
        // Don't insert if $short_order is already in the system
        $orderExist = Order::where('short_order', $short_order)->where('is_deleted', '0')->first();
        if ($orderExist) {
            Log::info('Hook: Duplicate order not inserted (short_order) ' . $short_order);
            return response()->json([
                'error' => true,
                'message' => "Order in DB, short_order already exists, data can't be inserted",
            ], 200);
        }

        // -------------- Customers table data insertion started ----------------------//
        $customer = new Customer();
        $customer->order_id = $request->get('ID');
        $customer->ship_full_name = str_replace('&', '+', $request->get('Ship-Name'));
        $customer->ship_first_name = $request->get('Ship-Firstname');
        $customer->ship_last_name = $request->get('Ship-Lastname');
        $customer->ship_company_name = $request->get('Ship-Company');
        $customer->ship_address_1 = $request->get('Ship-Address1');
        $customer->ship_address_2 = $request->get('Ship-Address2');
        $customer->ship_city = $request->get('Ship-City');
        $customer->ship_state = Helper::stateAbbreviation($request->get('Ship-State'));
        $customer->ship_zip = $request->get('Ship-Zip');
        $customer->ship_country = $request->get('Ship-Country');
        $customer->ship_phone = $request->get('Ship-Phone');
        $customer->ship_email = $request->get('Ship-Email');
        $customer->shipping = $request->get('Shipping', "N/A");
        $customer->bill_full_name = $request->get('Bill-Name');
        $customer->bill_first_name = $request->get('Bill-Firstname');
        $customer->bill_last_name = $request->get('Bill-Lastname');
        $customer->bill_company_name = $request->get('Bill-Company');
        $customer->bill_address_1 = $request->get('Bill-Address1');
        $customer->bill_address_2 = $request->get('Bill-Address2');
        $customer->bill_city = $request->get('Bill-City');
        $customer->bill_state = $request->get('Bill-State');
        $customer->bill_zip = $request->get('Bill-Zip');
        $customer->bill_country = $request->get('Bill-Country');
        $customer->bill_phone = $request->get('Bill-Phone');
        $customer->bill_email = $request->get('Bill-Email');
        $customer->bill_mailing_list = $request->get('Bill-maillist');
        $customer->save();

        $order = new Order();
        $order->order_id = $request->get('ID');
        try {
            $order->customer_id = $customer->id;
        } catch (Exception $exception) {
            Log::error('Failed to insert customer id in hook');
        }
        $purchase_order = null;
        if($request->has('Purchase-Order')) {
            $purchase_order = $request->get('Purchase-Order');
        }
        $order->short_order = $short_order;
        $order->purchase_order = $purchase_order;
        $order->item_count = $request->get('Item-Count');
        $order->coupon_description = $request->get('Coupon-Description');
        $order->coupon_id = $request->get('Promotions-Code');
        $order->coupon_value = abs($request->get('Promotions-Value'));
        $order->promotion_id = $request->get('Coupon-Id');
        $order->promotion_value = abs($request->get('Coupon-Value'));
        $order->shipping_charge = $request->get('Shipping-Charge');
        $order->tax_charge = $request->get('Tax-Charge');
        $order->total = $request->get('Total');
        $order->card_name = $request->get('Card-Name');
        $order->card_expiry = $request->get('Card-Expiry');
        $order->order_comments = $request->get('Comments');
        $order->order_date = date('Y-m-d H:i:s', strtotime($request->get('Date')));

        $order->order_numeric_time = ($request->get('Numeric-Time'));
        $order->order_ip = $request->get('IP');
        $order->paypal_merchant_email = $request->get('PayPal-Merchant-Email', '');
        $order->paypal_txid = $request->get('PayPal-TxID', '');
        $order->space_id = $request->get('Space-Id');
        $order->store_id = $request->get('Store-Id');
        $order->store_name = $request->get('Store-Name');
        $order->order_status = 4;

        if(empty($request->get('shipping')) && ($request->get('Store-Id') == "52053153")) {
            Log::info('xShipping77 = : ' . $request->get('shipping'));
            $order->carrier = "US";
            $order->method = "FIRST_CLASS";
        }
        Log::info('Inserted purchase order ' . $purchase_order);
        $order->save();  # Save Later
        try {
            $order_5p = $order->id;  # Save Later
        } catch (Exception $exception) {
            $order_5p = '0';
            Log::error('Failed to get 5p order id in hook');
        }

        $upsell = array();
        $upsell_price = 0;

        for ($item_count_index = 1; $item_count_index <= $request->get('Item-Count'); $item_count_index++) {
            $ItemOption = [];
            $pdfUrl = "";

            foreach ($request->all() as $key => $value) {
                if($item_count_index < 10) {
                    $len = 14;
                } else {
                    $len = 15;
                }

                if("Item-Option-" . $item_count_index . "-" == substr($key, 0, $len)) {
                    if(strpos($key, 'special_offer_') !== false) {
                        $upsell[substr($key, $len)] = $value;
                        if(strpos($value, 'Yes') !== false) {
                            Log::info('YES UPSELL ITEM ' . $value);
                        }
                        Order::note(substr($key, $len) . ' - ' . $value, $order->id, $order->order_id);

                    } else {
                        //$k = str_replace(['Choose ', 'Select '], '', substr($key, $len));

                        if (strpos(str_replace([' ', ','], '', strtolower($value)), 'nothankyou') === FALSE) {
                            $ItemOption [preg_replace('/[\x00-\x1F\x7F-\xFF\xA0]/ u ', '', substr($key, $len))] =
                                preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
                                    return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
                                }, str_replace(["\u00a0", "\u0081", "\u0091"], '', $value));
                        } else {
                            Log::info('Deleted option: ' . $value);
                        }
                    }

                    if (strpos($key, 'Custom_EPS_download_link') !== false) {
                        logger("Custom_EPS_download_link", [$value]);
                        $pdfUrl = $value;
                    } elseif (strpos($key, 'zakekezip') !== false) {
                        logger("zakekezip link", [$value]);
                        $pdfUrl = $this->_processZakekeZip($value);
                    }

                }
            }

            logger('final pdf file url', [$pdfUrl]);

            $matches = [];

            preg_match("~.*src\s*=\s*(\"|\'|)?(.*)\s?\\1.*~im", $request->get('Item-Thumb-' . $item_count_index),
                $matches);

            $manufacture_id = Product::where('product_model',
                $request->get('Item-Code-' . $item_count_index))->first()->manufacture_id ?? null;
            $item = new Item();
            $item->order_5p = $order_5p;
            $item->order_id = $request->get('ID');
            $item->store_id = $order->store_id;
            $item->manufacture_id = $manufacture_id;
            $item->item_code = $request->get('Item-Code-' . $item_count_index);
            $item->item_description = $request->get('Item-Description-' . $item_count_index);
            $item->item_id = $request->get('Item-Id-' . $item_count_index);
            $item->item_quantity = $request->get('Item-Quantity-' . $item_count_index);
            $item->item_unit_price = $request->get('Item-Unit-Price-' . $item_count_index);
            $item->item_url = $request->get('Item-Url-' . $item_count_index);
            $item->item_taxable = $request->get('Item-Taxable-' . $item_count_index);
            $item->data_parse_type = 'hook';
            $item->child_sku = Helper::getChildSku($item);
            $item->save();  # Save Later

            $item_thumb =  'https://' . $this->domain . '/assets/images/no_image.jpg';
            if(!$pdfUrl){
                Log::info('No PDF URL found for item : '. $item->item_id);
            } else {
                $context = stream_context_create([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ],
                ]);

                $file_contents = file_get_contents($pdfUrl, false, $context);

                if (!$file_contents) {
                    Log::error("Failed to download the file for item : ". $item->item_id);
                } else {
                    $name = $item->batch_number != null ? $item->batch_number : $item->item_id;
                    $destination = $this->archivePath . $name . '.pdf';
                    logger(['$destination' => $destination]);
                    file_put_contents($destination, $file_contents);

                    // Check if the PDF file exists
                    if (!file_exists($destination)) {
                        Log::error('PDF file not found for item : '. $item->item_id);
                    } else {

                        $helper = new Helper();
                        $jpgFile = $helper->pdfToJPGWIthProfile($destination);
                        if($jpgFile){
                            $savedFilePath = $jpgFile;
                            Log::info('1. PDF converted to JPG successfully for item with jpg: '. $jpgFile);
                        }
                        $fileName = basename($savedFilePath);
//                        $jpgFile = str_replace('.pdf', '', $destination);

                        // Use ImageMagick to convert the PDF to a JPG
//                        exec("pdftoppm -jpeg $destination $jpgFile -singlefile");
//                        $jpgFile = $jpgFile . '.jpg';

                        // Check if the JPG file was created successfully
                        if (!file_exists($this->archivePath . $fileName)) {
                            Log::error('Failed to convert PDF to JPG for item : '. $item->item_id);
                        } else {
                            $jpgFile = basename($jpgFile);
                            $savedFile = $this->remotArchiveUrl . $jpgFile;
                            $fileWithPath = $this->archivePath . $jpgFile;

                            $thumb = '/assets/images/template_thumbs/' . $item->order_id . "-" . $item->id . '.jpg';
                            ImageHelper::createThumb($fileWithPath, 0, base_path() . '/public_html' . $thumb, 350);
                            $item_thumb = $this->full_domain . $thumb;

                            if (!file_exists($fileWithPath)) {
                                Log::info('Failed to create thumb for item : '. $item->item_id);
                            } else {
                                $ItemOption['Internal_Zakeke_Fetch'] = Carbon::now()->toDateTimeString();
                                $ItemOption['Custom_EPS_download_link'] = $savedFile;
                            }
                        }
                    }
                }
            }

            $item->item_option = json_encode($ItemOption);
            $item->item_thumb = $item_thumb ;
            $item->save();

            try {
                $item_id = $item->id;
            } catch (Exception $exception) {
                $item_id = null;
                Log::error('Failed to get item id in hook');
            }

            if (isset($ItemOption['Custom_EPS_download_link']) &&
                (strpos(strtolower($item->item_description), 'photo') ||
                    Option::where('child_sku', $item->child_sku)->first()->sure3d == '1')) {
                $item->sure3d = html_entity_decode($ItemOption['Custom_EPS_download_link']);
                $item->save();  # Save Later
            }


            if(count($upsell) > 0) {
                $upsell_price = $this->upsellItems($upsell, $order, $item);
                $item->item_unit_price = $item->item_unit_price - $upsell_price;
            }

            $item->save();  # Save Later

            if($item->item_option == '[]' || $item->item_option == '0') {
                $data = [];
                $file = "/var/www/5p_oms/BypassOption.json";
                if(file_exists($file)) {
                    $data = json_decode(file_get_contents($file), true);
                }

                $bypass = false;

                if(isset($data[$item->child_sku])) {
                    if($data[$item->child_sku]) {
                        $bypass = true;
                    }
                }

                if($bypass) {
                    $order->order_status = 15;
                    $order->save();  # Save Later
                }
            }

            // -------------- Items table data insertion ended ---------------------- //

            $product = Product::where('product_model', $item->item_code)->first();

            if(!$product) {
                $product = new Product();
                $product->product_model = $item->item_code;
                $product->manufacture_id = 1;
            }

            if($product->id_catalog == null || $item->store_id == '52053152') {
                $product->id_catalog = $item->item_id;
            }

            if(isset($matches[2])) {
                if(!empty($matches[2])){
                    $product->product_thumb = trim($matches[2], ">");
                }
            }
            $product->product_url = $item->item_url;
            $product->product_name = $item->item_description;
            $product->product_price = $item->item_unit_price;
            $product->is_taxable = ($item->item_taxable == 'Yes' ? 1 : 0);
            $product->save();  # Save Later
        }
        try {
            $isVerified = Shipper::isAddressVerified($customer);
        } catch (Exception $exception) {
            $isVerified = 0;
        }

        if($isVerified) {
            $customer->is_address_verified = 1;
        } else {
            $customer->is_address_verified = 0;
            $order->order_status = 11;
            $order->save();  # Save Later
        }

        $customer->save();

        $this->jdbg(__LINE__, "----------------End Hook Called for order ".$request->get('ID')."--------------------");

        return response()->json([
            'error' => false,
            'message' => 'data inserted',
        ], 200);

//        } catch (Exception $e) {
//            Notification::orderFailure($order_id);
//            Log::error('Hook: ' . $e->getMessage());
//
//            return response()->json([
//                'error' => true,
//                'message' => 'error',
//            ], 200);
//
//        }
    }

    public function hook(Request $request)
    {
        try {
            $helper = new Helper;
//            if($request->get('ID') == '52053153-3655910359203') {
//                return $request->request->all();
//            }
            set_time_limit(0);
            $order_id = $request->get('ID');
//            Log::info(__METHOD__ . " ------------called ----------------  " . $order_id);

            $previous_order = Order::with('items')->where('order_id', $order_id)->where('is_deleted',
                '0')->orderBy('created_at', 'DESC')->first();

            if($previous_order) {
                ########
                Log::info('Hook: Duplicate order can not inserted (status) = ' . $order_id);
                return response()->json([
                    'error' => true,
                    'message' => "Order in DB, status can not in process, data can't be inserted",
                ], 200);
                ########
                $batched = $previous_order->items->filter(function ($item) {
                    return $item->batch_number != '0';
                })->count();

                if($batched > 0) {
                    Log::info('Hook: Duplicate order not inserted (batched) ' . $order_id);
                    return response()->json([
                        'error' => true,
                        'message' => "Batch exists data can't be inserted",
                    ], 200);
                } else {
                    if($previous_order->order_status != 4) {
                        Log::info('Hook: Duplicate order not inserted (status)' . $order_id);
                        return response()->json([
                            'error' => true,
                            'message' => "Order in DB, status not in process, data can't be inserted",
                        ], 200);
                    } else {
                        Order::where('order_id', $order_id)->update(['is_deleted' => 1]);
                        Item::where('order_id', $order_id)->update(['is_deleted' => 1]);
                        Customer::where('order_id', $order_id)->update(['is_deleted' => 1]);
                    }
                }
            }
            $this->jdbg(__LINE__, "----------------Start Hook Call for order = ".$request->get('ID')."--------------------");
            try {
                $exploded = explode("-", $order_id);
                if(isset($exploded[2])) {
                    $short_order = $exploded[2];
                } else {
                    $short_order = $exploded[1];
                }
            } catch (Exception $e) {
                Log::error('Undefined offset when trying to create short order. Order ' . $order_id);
                Log::error($request);
                if(strlen($order_id) < 1) {
                    exit('no order');
                } else {
                    $short_order = $order_id;
                }
            }

            // -------------- Customers table data insertion started ----------------------//
            $customer = new Customer();
            $customer->order_id = $request->get('ID');
            $customer->ship_full_name = str_replace('&', '+', $request->get('Ship-Name'));
            $customer->ship_first_name = $request->get('Ship-Firstname');
            $customer->ship_last_name = $request->get('Ship-Lastname');
            $customer->ship_company_name = $request->get('Ship-Company');
            $customer->ship_address_1 = $request->get('Ship-Address1');
            $customer->ship_address_2 = $request->get('Ship-Address2');
            $customer->ship_city = $request->get('Ship-City');
            $customer->ship_state = Helper::stateAbbreviation($request->get('Ship-State'));
            $customer->ship_zip = $request->get('Ship-Zip');
            $customer->ship_country = $request->get('Ship-Country');
            $customer->ship_phone = $request->get('Ship-Phone');
            $customer->ship_email = $request->get('Ship-Email');
            $customer->shipping = $request->get('Shipping', "N/A");
            $customer->bill_full_name = $request->get('Bill-Name');
            $customer->bill_first_name = $request->get('Bill-Firstname');
            $customer->bill_last_name = $request->get('Bill-Lastname');
            $customer->bill_company_name = $request->get('Bill-Company');
            $customer->bill_address_1 = $request->get('Bill-Address1');
            $customer->bill_address_2 = $request->get('Bill-Address2');
            $customer->bill_city = $request->get('Bill-City');
            $customer->bill_state = $request->get('Bill-State');
            $customer->bill_zip = $request->get('Bill-Zip');
            $customer->bill_country = $request->get('Bill-Country');
            $customer->bill_phone = $request->get('Bill-Phone');
            $customer->bill_email = $request->get('Bill-Email');
            $customer->bill_mailing_list = $request->get('Bill-maillist');
            $customer->save(); # Save Later
            // -------------- Customers table data insertion ended ----------------------//
            // -------------- Orders table data insertion started ----------------------//
            $order = new Order();
            $order->order_id = $request->get('ID');
            try {
                $order->customer_id = $customer->id;
            } catch (Exception $exception) {
                Log::error('Failed to insert customer id in hook');
            }
            $purchase_order = null;
            if($request->has('Purchase-Order')) {
                $purchase_order = $request->get('Purchase-Order');
            }
            $order->short_order = $short_order;
            $order->purchase_order = $purchase_order;
            $order->item_count = $request->get('Item-Count');
            $order->coupon_description = $request->get('Coupon-Description');
            $order->coupon_id = $request->get('Promotions-Code');
            $order->coupon_value = abs($request->get('Promotions-Value'));
            $order->promotion_id = $request->get('Coupon-Id');
            $order->promotion_value = abs($request->get('Coupon-Value'));
            $order->shipping_charge = $request->get('Shipping-Charge');
            $order->tax_charge = $request->get('Tax-Charge');
            $order->total = $request->get('Total');
            $order->card_name = $request->get('Card-Name');
            $order->card_expiry = $request->get('Card-Expiry');
            $order->order_comments = $request->get('Comments');
            $order->order_date = date('Y-m-d H:i:s', strtotime($request->get('Date')));
            //$order->order_numeric_time = strtotime($request->get('Numeric-Time'));
            // 06-22-2016 Change by Jewel
            $order->order_numeric_time = ($request->get('Numeric-Time'));
            $order->order_ip = $request->get('IP');
            $order->paypal_merchant_email = $request->get('PayPal-Merchant-Email', '');
            $order->paypal_txid = $request->get('PayPal-TxID', '');
            $order->space_id = $request->get('Space-Id');
            $order->store_id = $request->get('Store-Id');
            $order->store_name = $request->get('Store-Name');
            $order->order_status = 4;

            if(empty($request->get('shipping')) && ($request->get('Store-Id') == "52053153")) {
                Log::info('xShipping77 = : ' . $request->get('shipping'));
                $order->carrier = "US";
                $order->method = "FIRST_CLASS";
            }
            Log::info('Inserted purchase order ' . $purchase_order);
            $order->save();  # Save Later
            try {
                $order_5p = $order->id;  # Save Later
            } catch (Exception $exception) {
                $order_5p = '0';
                Log::error('Failed to get 5p order id in hook');
            }
            // -------------- Orders table data insertion ended ----------------------//
            // -------------- Items table data insertion started ------------------------//
            $upsell = array();
            $upsell_price = 0;

            for ($item_count_index = 1; $item_count_index <= $request->get('Item-Count'); $item_count_index++) {
                $ItemOption = [];
                $pdfUrl = "";

                foreach ($request->all() as $key => $value) {
                    if($item_count_index < 10) {
                        $len = 14;
                    } else {
                        $len = 15;
                    }

                    if("Item-Option-" . $item_count_index . "-" == substr($key, 0, $len)) {
                        if(substr(strtolower($key), $len, 14) == 'special_offer_') {
                            $upsell[substr($key, $len)] = $value;
                            if(strpos($value, 'Yes') !== false) {
                                Log::info('YES UPSELL ITEM ' . $value);
                            }
                            Order::note(substr($key, $len) . ' - ' . $value, $order->id, $order->order_id);

                        } elseif(substr(strtolower($key), $len, 14) == '_zakekezip') {
                            Log::info('Waiting for Zakeke personalization file ' . $value);
                            sleep(120);

                            $pdfUrl = $this->_processZakekeZip($value);

                            if(!is_string($pdfUrl)) {
                                while (!is_string($this->_processZakekeZip($value))) {
                                    Log::info('Processing to get _processZakekeZip' . $value);
                                    $pdfUrl = $this->_processZakekeZip($value);
                                    sleep(120);
                                }
                            }

                            Log::info('Zakeke PDF URL ' . $pdfUrl);
                            $ItemOption['zakekezip'] = addslashes($value);

                            //  $childSku = Helper::getChildSku($item);
//                            if(ZakekeController::hasSure3D($childSku, $request)) {
//                                $ItemOption['Custom_EPS_download_link'] = addslashes($pdfUrl);
//                            }
                            $ItemOption['Custom_EPS_download_link'] = $pdfUrl;
                        } else {
//                            Log::info('Processing without zakeke: ' . $value);
                            if(strpos(str_replace([' ', ','], '', strtolower($value)), 'nothankyou') === false) {
                                $ItemOption [preg_replace('/[\x00-\x1F\x7F-\xFF\xA0]/ u ', '',
                                    substr($key, $len))] = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/',
                                    function ($match) {
                                        return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
                                    }, str_replace(["\u00a0", "\u0081", "\u0091"], '', $value));
                            } else {
                                Log::info('Deleted option: ' . $value);
                            }
                        }
                    }
                }

                $manufacture_id = Product::where('product_model',
                    $request->get('Item-Code-' . $item_count_index))->first()->manufacture_id ?? null;
                $item = new Item();
                $item->order_5p = $order_5p;
                $item->order_id = $request->get('ID');
                $item->store_id = $order->store_id;
                $item->manufacture_id = $manufacture_id;
                $item->item_code = $request->get('Item-Code-' . $item_count_index);
                $item->item_description = $request->get('Item-Description-' . $item_count_index);
                $item->item_id = $request->get('Item-Id-' . $item_count_index);
//                    $item->item_option = json_encode($ItemOption);
                $item->item_quantity = $request->get('Item-Quantity-' . $item_count_index);
//                    $item->item_thumb = $item_thumb;
                $item->item_unit_price = $request->get('Item-Unit-Price-' . $item_count_index);
                $item->item_url = $request->get('Item-Url-' . $item_count_index);
                $item->item_taxable = $request->get('Item-Taxable-' . $item_count_index);
                $item->data_parse_type = 'hook';
                $item->child_sku = Helper::getChildSku($item);
                $item->save();  # Save Later

                ########## Create a New logic if Custom_EPS_download_link exist with valid url download the image in Archive file #######

                if(isset($ItemOption['Custom_EPS_download_link'])) {
                    $headers = @get_headers($ItemOption['Custom_EPS_download_link']);
                    if($headers && strpos($headers[0], '200') !== false) {
                        $fileName = basename(parse_url($ItemOption['Custom_EPS_download_link'], PHP_URL_PATH));
                        $fileName = $short_order . "_" . $item->id . "_" . $fileName;
                        $this->archiveFilePath = $this->sort_root . "archive/" . $fileName;
//$this->jdbg(__LINE__." ** ".$image_path." -- ".$ItemOption['Custom_EPS_download_link']." --> ", $ItemOption);
                        $fleSaveStatus = $helper->dowFileToDir($ItemOption['Custom_EPS_download_link'],
                            $this->archiveFilePath);
                        if($fleSaveStatus == 200) {
                            $ItemOption['Custom_EPS_download_link'] = $this->remotArchiveUrl . $fileName;
                        }
//$this->jdbg(__LINE__." -- After check  --", $headers[0]);
//$this->jdbg(__LINE__." -- fleSaveStatus --", $fleSaveStatus);

                    } else {
                        unset($ItemOption['Custom_EPS_download_link']);
                    }
                }
                #######################################################################################################################
                $matches = [];

                preg_match("~.*src\s*=\s*(\"|\'|)?(.*)\s?\\1.*~im", $request->get('Item-Thumb-' . $item_count_index),
                    $matches);

                ### Start code for create Image Thumbnail
                ####*********########
                if(file_exists($this->archiveFilePath)) {
                    try {
                        $thmFileName = basename($this->archiveFilePath);
                        $filenameWithoutExtension = pathinfo($thmFileName, PATHINFO_FILENAME);
//                        $thumb = '/assets/images/template_thumbs/' . $filenameWithoutExtension . '.jpg';
                        $thumb = '/assets/images/template_thumbs/' . $item->order_id . "-" . $item->id . '.jpg';
                        ImageHelper::createThumb($this->archiveFilePath, 0, base_path() . '/public_html' . $thumb, 350);
                        $item_thumb = 'https://order.monogramonline.com' . $thumb;
                    } catch (Exception $e) {
                        $item_thumb = 'https://' . $this->domain . '/assets/images/no_image.jpg';
                        Log::error(sprintf("Hook found undefinded offset 2 on item thumb %s Order# %s.",
                            $request->get('Item-Thumb-' . $item_count_index), $order_5p));
                        Log::error('Batch uploadFile createThumb: ' . $e->getMessage());
                    }
                } else {
                    if(isset($matches[2])) {
                        $item_thumb = trim($matches[2], ">");
                    } else {
                        $item_thumb = 'http://' . $this->domain . '/assets/images/no_image.jpg';
                        Log::error(sprintf("Hook found undefinded offset 2 on item thumb %s Order# %s.",
                            $request->get('Item-Thumb-' . $item_count_index), $order_5p));
                    }
                }
                $this->jdbg("Hook Thumb for order= ". $order->short_order, $item_thumb);
//                $this->jdbg("Hook Thumb from API = ". $order->short_order, $matches[2]);
                ####*********########

//                if(file_exists(base_path() . 'public_html/assets/images/product_thumb/' . $request->get('Item-Code-' . $item_count_index) . '.jpg')) {
//                    $item_thumb = 'http://' . $this->domain . '/assets/images/product_thumb/' . $request->get('Item-Code-' . $item_count_index) . '.jpg';
//                } else {
//                    if(file_exists(base_path() . 'public_html/assets/images/product_thumb/' . $request->get('Item-Code-' . $item_count_index) . '.png')) {
//                        $item_thumb = 'http://' . $this->domain . '/assets/images/product_thumb/' . $request->get('Item-Code-' . $item_count_index) . '.png';
//                    } else {
//                        if(isset($matches[2])) {
//                            $item_thumb = trim($matches[2], ">");
//                        } else {
//                            $item_thumb = 'http://' . $this->domain . '/assets/images/no_image.jpg';
//                            Log::error(sprintf("Hook found undefinded offset 2 on item thumb %s Order# %s.",
//                                $request->get('Item-Thumb-' . $item_count_index), $order_5p));
//                        }
//                    }
//                }
                ### End code for create Image Thumbnail

                $item->item_option = json_encode($ItemOption);
                $item->item_thumb = $item_thumb;
                $item->save();  # Save Later

                try {
                    $item_id = $item->id;
                } catch (Exception $exception) {
                    $item_id = null;
                    Log::error('Failed to get item id in hook');
                }

                $childSku = Helper::getChildSku($item);
//                if(ZakekeController::hasSure3D($childSku, $request)) {
//                    if(isset($ItemOption['Custom_EPS_download_link']) && (strpos(strtolower($item->item_description),
//                                'photo') || Option::where('child_sku', $item->child_sku)->first()->sure3d == '1')) {
//                        $item->sure3d = html_entity_decode($ItemOption['Custom_EPS_download_link']);
//                        $item->save();  # Save Later
//                    } else {
//                        if($pdfUrl !== "") {
//                            $ItemOption['Custom_EPS_download_link'] = $pdfUrl;
//                        }
//                    }
//                } else {
//                    if(isset($ItemOption['Custom_EPS_download_link'])) {
//                        unset($ItemOption['Custom_EPS_download_link']);
//                    }
//                }

                if(count($upsell) > 0) {
                    $upsell_price = $this->upsellItems($upsell, $order, $item);
                    $item->item_unit_price = $item->item_unit_price - $upsell_price;
                }

//                $item->item_option = json_encode($ItemOption);
//                $item->save();  # Save Later

                if($item->item_option == '[]' || $item->item_option == '0') {
                    $data = [];
                    $file = "/var/www/5p_oms/BypassOption.json";
                    if(file_exists($file)) {
                        $data = json_decode(file_get_contents($file), true);
                    }

                    $bypass = false;

                    if(isset($data[$item->child_sku])) {
                        if($data[$item->child_sku]) {
                            $bypass = true;
                        }
                    }

                    if($bypass) {
                        $order->order_status = 15;
                        $order->save();  # Save Later
                    }
                }

                // -------------- Items table data insertion ended ---------------------- //

                $product = Product::where('product_model', $item->item_code)->first();
                // where('id_catalog', $item->item_id)

                // no product found matching model
                if(!$product) {
                    $product = new Product();
                    $product->product_model = $item->item_code;
                    $product->manufacture_id = 1;
                }

                if($product->id_catalog == null || $item->store_id == '52053152') {
                    $product->id_catalog = $item->item_id;
                }
//$this->jdbg(__LINE__, $matches);
//$this->jdbg(__LINE__, $item->item_thumb);

                if(isset($matches[2])) {
                    if(!empty($matches[2])){
                        $product->product_thumb = trim($matches[2], ">");
                    }
                }
//                else {
//                    $product->product_url = 'https://' . $this->domain . '/assets/images/no_image.jpg';
//                }
                $product->product_url = $item->item_url;
                $product->product_name = $item->item_description;
                $product->product_price = $item->item_unit_price;
                $product->is_taxable = ($item->item_taxable == 'Yes' ? 1 : 0);
                $product->save();  # Save Later
            }

            // -------------- Order Confirmation email sent Start   ---------------------- //
            if(substr($item->item_code, 0, 3) != 'KIT') {
//                Notification::orderConfirm($order);
            }
            // -------------- Order Confirmation email sent End---------------------- //
            try {
                $isVerified = Shipper::isAddressVerified($customer);
            } catch (Exception $exception) {
                $isVerified = 0;
            }

            if($isVerified) {
                $customer->is_address_verified = 1;
            } else {
                $customer->is_address_verified = 0;
                $order->order_status = 11;
                $order->save();  # Save Later
            }

            $customer->save();  # Save Later

            // -------------- Hold Free Orders   ---------------------- //

            $this->jdbg(__LINE__, "----------------End Hook Called for order ".$request->get('ID')."--------------------");

            return response()->json([
                'error' => false,
                'message' => 'data inserted',
            ], 200);

        } catch (Exception $e) {
            Notification::orderFailure($order_id);
            Log::error('Hook: ' . $e->getMessage());

            return response()->json([
                'error' => true,
                'message' => 'error',
            ], 200);

        }
    }

    public function getItemPdfImageLink($orderId, $item_id, $batch_number)
    {
        //TODO::testing purpose of API check

        // Specify the API endpoint
//        $apiEndpoint = 'https://7papi.monogramonline.com/make-pdf-summary';
//
//        // Specify the parameters you want to send
//        $params = [
//            'img_dir' => 'img_dir',
//            'order' => 'order',
//            'batch' => 'batch',
//            'item_id' => 'item_id',
//            'customer' => 'customer',
//            'image_size' => 'image_size',
//            'image_dpi ' => 'image_dpi',
//            'file_size ' => 'file_size',
//            'file_type ' => 'file_type',
//            // add more parameters as needed
//        ];
//
//    // Initialize a Guzzle client
//        $client = new Client();
//
//    // Make a GET request to the API with the specified parameters
//        $response = $client->request('GET', $apiEndpoint, [
//            'query' => $params,
//        ]);
//
//    // Get the response body as a string
//        $responseBody = $response->getBody()->getContents();
//
//    // Parse the response if it is in JSON format
//        $data = json_decode($responseBody, true);

//        dd($data);


////        $url = '/media/RDrive/archive/692155.jpg';
////        dd(file_get_contents($url));
////        $url = 'https://order.monogramonline.com/media/archive/692155.jpg';
////        dd(file_get_contents($url));
//        $imagePath = '/media/RDrive/archive/692155.jpg';
//
//// Information lines
//        $infoText1 = 'Your Information Line 1';
//        $infoText2 = 'Your Information Line 2';
//        $infoText3 = 'Your Information Line 4';
//
//// Add information under the image using ImageMagick's convert command
//        $command = "convert $imagePath -gravity South -pointsize 24 ";
//        $command .= "-annotate +0+10 \"$infoText1\" ";
//        $command .= "-annotate +0+40 \"$infoText2\" ";
//        $command .= "-annotate +0+70 \"$infoText3\" ";
//        $command .= "$imagePath";
//
//        shell_exec($command);
//
//        $command = "convert $imagePath -gravity South -pointsize 24 -annotate +0+10 \"$infoText3\" $imagePath";
//        shell_exec($command);
//
//        dd('https://order.monogramonline.com/media/archive/692155.jpg');


        // test url fetch and save in local server

//        $url = "https://cdn.shopify.com/s/files/1/0304/9947/1493/uploads/3d6e5e137efcbe2e79d4a1d69d30a7b1.pdf";
//        $destination = "/media/RDrive/archive/zz-test-pdf.pdf"; // Save it as a PDF
//        $file_contents = file_get_contents($url);
//
//        if ($file_contents !== false) {
//            file_put_contents($destination, $file_contents);
//
//            // Check if the PDF file exists
//            if (file_exists($destination)) {
//                $jpgFile = str_replace('.pdf', '', $destination);
//                // Use ImageMagick to convert the PDF to a JPG
//                exec("pdftoppm -jpeg $destination $jpgFile -singlefile");
//
//                $jpgFile = $jpgFile . '.jpg';
//                // Check if the JPG file was created successfully
//                if (file_exists($jpgFile)) {
//                    $jpgFile = basename($jpgFile);
//                    dd("PDF converted to JPG successfully :" . $jpgFile);
//                } else {
//                    dd("Failed to convert PDF to JPG.");
//                }
//            } else {
//                dd("PDF file not found.");
//            }
//        } else {
//            dd("Failed to download the file.");
//        }



        $itemDetails = Item::where('batch_number', $batch_number)->first();
        if (!$itemDetails)
        {
            Log::error('item is not available');
            return redirect()->back()->with('error', 'item is not available');
        }
        $options = json_decode($itemDetails->item_option, true);
//        dd($itemDetails->batch_number);
        $destination = $this->archivePath . $batch_number. '.pdf';
        $data = $this->shopifyOrderById($orderId);
        if(empty($data['orders'])){
            Log::error('order is not available');
            return redirect()->back()->with('error', 'order is not available');
        }
        $order = $data['orders'][0];
        $helper = new Helper();
        $url = false;
        if (empty($order)) {
            Log::error('order is not available');
            return redirect()->back()->with('error', 'order is not available for item : '. $itemDetails->item_id);
        }
        $items = $order['line_items'];
        if(!count($items)) {
            Log::error('order line items is not available for item : '. $itemDetails->item_id);
            return redirect()->back()->with('error', 'order line items is not available');
        }

        foreach ($items as $item) {
            if ($item['id'] == $item_id) {
                foreach ($item['properties'] as $value) {
                    if ($value['name'] == '_zakekeZip') {
                        Log::info('Waiting for Zakeke personalization file ' . $value['value']);
                        $url = $this->_processZakekeZip($value['value']);
                        break;

                    } else if ($value['name'] == "_pdf") {
                        $url = $helper->getUrlWithoutParaMeter($value['value']);
                        break;
                    }
                }
            }
        }
        if(!$url){
            Log::error('Remote PDF is not found');
            return redirect()->back()->with('error', 'Remote PDF is not found');
        }

//        $serceStr = "https://order.monogramonline.com";
//        $replaceSrt = "/var/www/5p_oms/public_html";
//        dd($serceStr, $replaceSrt, $url, str_ireplace($serceStr, $replaceSrt, $url));

        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);

        $file_contents = file_get_contents($url, false, $context);

        if ($file_contents == false) {
            Log::error("Failed to download the file for item : ". $itemDetails->item_id);
            return redirect()->back()->with("error", "Failed to download the file for item : ". $itemDetails->item_id);
        }

        file_put_contents($destination, $file_contents);


        // Check if the PDF file exists
        if (!file_exists($destination)) {
            Log::error('PDF file not found for item with batch number: '. $batch_number);
            return redirect()->back()->with('error', 'PDF file not found for item  with batch number: '. $batch_number);
        }

        Log::info("Store file processing for Custom_EPS_download_link with : ". $destination);
        $savedFilePath = $destination;
        Log::info("File saved at " . $savedFilePath);
        $jpgFile = $helper->pdfToJPGWIthProfile($savedFilePath);

        if($jpgFile){
            $savedFilePath = $jpgFile;
            Log::info('2. PDF converted to JPG successfully for item with jpg: '. $jpgFile);
        }

        $fileName = basename($savedFilePath);

//        $jpgFile = str_replace('.pdf', '', $destination);
//        // Use ImageMagick to convert the PDF to a JPG
//
//        exec("pdftoppm -jpeg $destination $jpgFile -singlefile");

//        $jpgFile = $jpgFile . '.jpg';
        // Check if the JPG file was created successfully
        if (!file_exists($this->archivePath . $fileName)) {
            Log::error('Failed to convert PDF to JPG for item : '. $jpgFile);
            return redirect()->back()->with('error', 'Failed to convert PDF to JPG for item for : '. $jpgFile);
        }

        $jpgFile = basename($jpgFile);
        $savedFile = $this->remotArchiveUrl . $jpgFile;
        $fileWithPath = $this->archivePath . $jpgFile;
//        dd($fileWithPath);
        logger('order ID :'. $itemDetails->order_id . ' item id : ' . $itemDetails->id);
        $thumb = '/assets/images/template_thumbs/' . $itemDetails->order_id . "-" . $itemDetails->id . '.jpg';
//        dd($fileWithPath, 0, base_path() . '/public_html' . $thumb, 350);
        ImageHelper::createThumb($fileWithPath, 0, base_path() . '/public_html' . $thumb, 350);
        $item_thumb = 'https://order.monogramonline.com' . $thumb;
        logger('item_thumb: '. $item_thumb);
        if (!file_exists($fileWithPath)) {
            $item_thumb = 'https://' . $this->domain . '/assets/images/no_image.jpg';
        }
//
        $options['Internal_Zakeke_Fetch'] = Carbon::now()->toDateTimeString();
        $options['Custom_EPS_download_link'] = $savedFile;
        $itemDetails->item_option = json_encode($options);
        $itemDetails->item_thumb = $item_thumb;
        $itemDetails->save();

        $batches = Batch::with('items', 'route.stations_list')
            ->where('batch_number', $batch_number)
            ->get();


        foreach ($batches as $batch) {
            foreach ($batch->items as $item) {
                if($item->item_id == $itemDetails->item_id) {
                    if(empty($item->tracking_number)) {
                        $item->tracking_number = null;
                        $item->item_status = 1;
                        $item->reached_shipping_station = 0;
                        $item->save();


                        ###### Save in Batch Table ######
                        //TODO :: need to implement
//                        if(isset($batch->section) && $batch->section != 'Sublimation'){
//                            logger('section is not sublimation, section is :'. $batch->section->section_name. 'batch number is :'. $batch->batch_number);
//                            continue;
//                        }
                        $batch->status = 2;
                        $batch->section_id = 6;

                        $nextStationId = 92;
                        // move to next station
                        $index = -1;
                        foreach ($batch->route->stations_list as $key => $station) {
                            if($station->station_id === 264) {
                                $index = $key;
                                break;
                            }
//                            if ($station->station_id === $batch->station_id) {
//                                $index = $key;
//                                break;
//                            }
                        }
                        if ($index !== -1 && isset($batch->route->stations_list[$index])) {
                            // Get the next station's ID
                            $nextStationId = $batch->route->stations_list[$index]->station_id;
                            logger("Next Station ID: $nextStationId");
                        } else {
                            logger("Station with ID $batch->station_id not found or no next station and set default: S-GRPH");
                        }

                        $batch->station_id = $nextStationId;
                        $batch->prev_station_id = $batch->route->stations_list->first()->station_id;
                        $batch->export_count = 1;
                        $batch->csv_found = 0;
                        $batch->graphic_found = 1;
                        $batch->to_printer = 0;
                        $batch->to_printer_date = null;
                        $batch->archived = 1;
                        $batch->save();

                        Batch::note($batch->batch_number, $batch->station_id, '111',
                                'Graphic Uploaded to Main');
                    }
                }
            }

        }

        Log::info("PDF converted to JPG successfully :" . $savedFile);
        return redirect()->back()->with('success', "PDF converted to JPG successfully :". $savedFile);

    }

    public function shopifyOrderById($id)
    {
        $array = ['ids' => $id];
        $helper = new Helper;
        $orderInfo = $helper->shopify_call("/admin/api/2023-01/orders.json", $array, 'GET');
        try {
            $orderInfo = json_decode($orderInfo['response'], JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            Log::error('getShopifyOrderError = ' . $e->getMessage());
            dd($e->getMessage());
        }

        return $orderInfo;
    }

    public function updateShopifyThumb($orderId, $item_id)
    {
        $dummy_Image = 'https://' . $this->domain . '/assets/images/no_image.jpg';
        $item = Item::where('item_id', $item_id)->first();
        if($item){
            $product = Product::where('product_model', $item->item_code)->first();
            if($product){
                $thumb = $this->shopifyThumb($orderId, $item_id);
                $product->product_thumb = $thumb ? $thumb : $dummy_Image;
                $product->save();

                return redirect()->back()->withSuccess('Product thumb successfully Updated');
            }else {
                Log::error('product is not available');
                return redirect()->back()->withErrors('product is not available');
            }

            return redirect()->back()->withSuccess('Ship Date Updated');
        } else {
            Log::error('item is not available');
            return redirect()->back()->withErrors('item is not available');
        }
    }

    public function shopifyThumb($orderId, $item_id)
    {
        $data = $this->shopifyOrderById($orderId);
        if(empty($data['orders'])){
            Log::error('order is not available');
            return false;
        }
        $order = $data['orders'][0];
        if (!empty($order)){
            $items = $order['line_items'];
            if(count($items)){
                foreach ($items as $item){
                    if($item['id'] == $item_id){
                        $productInfo = $this->getShopifyproduct($item['product_id']);
                        return $productInfo['image']['src'];
                    }
                }
            }else{
                Log::error('order line items is not available');
                return false;
            }
        } else {
            Log::error('order is not available');
            return false;
        }
    }


    protected function getImaeUrl($productId)
    {
        $productInfo = $this->getShopifyproduct($productId);
//        dd($productInfo);
        return $productInfo['image']['src'];
    }

    public function getShopifyproduct($productId)
    {
//5551613214883
        $array = [];

        $helper = new Helper;
        $products = $helper->shopify_call("/admin/api/2024-01/products/" . $productId . ".json", $array, 'GET');
        $products = json_decode($products['response'], JSON_PRETTY_PRINT);
        if(isset($products['errors'])) {
//            dd("errors", $products);
            return false;
        } else {
//            dd("products", $products['product']);
            return $products['product'];
        }
    }

    public function curlPost($url, $purchaseData)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $purchaseData);
//        curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 0);
        $result = curl_exec($ch);

        curl_close($ch);
        return $result;
    }

    public function updateMethod(Request $request)
    {
        ######### Code for Bulk Shipping method Update ##########
//        $orderIds= [636670,643730,645954,651167,651507,684438,782835,782836,782837,782838,782839,786845,836223,847321,856331,857702,861921,862137,869245,871968,872871,872982,873636,874815,875425,875669,875673,875794,875898,876104,876614,876652,876719,876737,876761,876821,876828,876841,876852,876947,877018,877067,877080,877211,877219,877322,877430,877457,877464,877469,877471,877569,877578,877671,877673,877676,877732,877790,877800,877825,877904,877905,877907,877919,877942,877987,878021,878034,878063,878064,878068,878074,878075,878077,878096,878101,878102,878109,878120,878133,878171,878186,878200,878202,878209,878212,878235,878245,878246,878249,878251,878256,878271,878279,878280,878284,878285,878289,878291,878293,878296,878297,878298,878306,878309,878311,878312,878314,878320,878322,878325,878326,878327,878333,878334,878337,878343,878344,878347,878349,878350,878353,878354,878355,878357,878358,878360,878362,878364,878372,878374,878380,878381,878384,878387,878389,878405,878407,878408,878410,878413,878414,878416,878418,878419,878425,878426,878427,878428,878431,878432,878435,878436,878437,878443,878449,878450,878454,878456,878457,878458,878460,878468,878469,878472,878473,878474,878475,878477,878478,878480,878489,878490,878491,878492,878493,878494,878500,878504,878507,878511,878512,878513,878516,878519,878522,878526,878527,878529,878531,878532,878533,878536,878537,878553,878554,878556,878558,878563,878565,878567,878572,878575,878576,878578,878580,878582,878584,878585,878590,878592,878593,878594,878596,878597,878602,878604,878605,878607,878608,878609,878611,878613,878614,878615,878616,878619,878620,878623,878626,878630,878631,878637,878639,878640,878643,878644,878645,878647,878648,878649,878650,878651,878652,878653,878656,878657,878659,878660,878662,878665,878667,878668,878669,878672,878674,878676,878679,878680,878688,878690,878692,878693,878695,878696,878698,878699,878700,878702,878704,878706,878707,878708,878710,878711,878712,878714,878718,878719,878720,878723,878724,878726,878728,878729,878730,878732,878733,878741,878742,878747,878748,878752,878754,878758,878760,878761,878762,878763,878765,878767,878769,878771,878772,878775,878777,878779,878784,878787,878788,878789,878790,878792,878794,878802,878805,878807,878811,878812,878813,878814,878820,878821,878826,878827,878828,878829,878830,878831,878836,878837,878838,878841,878843,878845,878846,878847,878851,878853,878857,878861,878867,878869,878870,878952,878971,878985,878987,878990,878991,878994,878996,879001,879003,879005,879010,879021,879023,879026,879029,879030,879033,879034,879037,879042,879043,879044,879046,879048,879049,879050,879051,879057,879058,879062,879063,879064,879068,879069,879072,879074,879077,879079,879081,879086,879087,879091,879092,879099,879102,879104,879107,879109,879110,879113,879119,879121,879124,879125,879126,879128,879129,879131,879132,879139,879142,879144,879145,879146,879147,879151,879152,879153,879154,879157,879161,879163,879164,879166,879168,879174,879176,879180,879183,879189,879191,879192,879194,879201,879202,879203,879207,879213,879216,879218,879219,879221,879222,879223,879227,879229,879230,879231,879232,879235,879240,879246,879248,879249,879250,879252,879254,879260,879261,879264,879265,879268,879270,879274,879278,879279,879280,879281,879282,879290,879306,879330,879332,879333,879334,879336,879337,879339,879340,879343,879346,879347,879354,879355,879356,879357,879365,879366,879368,879369,879370,879383,879384,879390,879403,879404,879405,879408,879409,879411,879415,879416,879417,879420,879421,879422,879423,879425,879426,879427,879429,879431,879433,879434,879435,879437,879439,879440,879444,879445,879447,879449,879451,879452,879455,879456,879458,879467,879472,879483,879484,879485,879490,879491,879496,879499,879500,879503,879507,879512,879515,879517,879518,879519,879520,879521,879522,879527,879528,879529,879531,879534,879535,879536,879537,879538,879539,879540,879543,879547,879548,879553,879554,879556,879559,879565,879567,879568,879569,879570,879572,879575,879576,879580,879582,879584,879589,879590,879593,879594,879600,879603,879604,879606,879607,879608,879609,879610,879613,879614,879616,879617,879620,879623,879624,879625,879626,879627,879635,879637,879640,879643,879646,879648,879649,879650,879655,879660,879662,879663,879666,879667,879668,879669,879670,879671,879672,879673,879676,879678,879681,879683,879684,879686,879690,879691,879693,879694,879696,879700,879701,879703,879704,879705,879708,879709,879710,879716,879718,879719,879723,879724,879725,879726,879727,879728,879730,879732,879735,879737,879738,879739,879740,879741,879742,879743,879745,879746,879747,879749,879755,879757,879758,879760,879761,879762,879763,879764,879765,879766,879767,879769,879770,879771,879772,879776,879777,879780,879782,879783,879784,879788,879792,879793,879796,879798,879799,879800,879802,879804,879805,879806,879807,879809,879810,879813,879815,879818,879820,879821,879826,879831,879834,879835,879837,879839,879840,879841,879842,879843,879845,879848,879850,879852,879854,879855,879858,879859,879862,879863,879865,879867,879869,879876,879877,879879,879934,879936,879938,879940,879942,879943,879946,879948,879953,879957,879958,879959,879960,879961,879962,879964,879966,879968,879970,879971,879974,879976,879985,879987,879992,879993,879996,879998,880001,880004,880007,880011,880013,880014,880015,880016,880019,880021,880022,880023,880024,880026,880027,880029,880030,880032,880033,880035,880036,880037,880038,880039,880040,880043,880045,880047,880050,880051,880052,880054,880055,880057,880064,880066,880068,880069,880070,880071,880072,880073,880074,880075,880076,880077,880079,880082,880084,880089,880093,880094,880095,880096,880097,880098,880099,880101,880102,880107,880108,880109,880110,880111,880112,880113,880115,880117,880119,880120,880125,880127,880128,880129,880130,880132,880133,880141,880142,880143,880144,880147,880149,880151,880154,880157,880158,880159,880160,880165,880166,880168,880172,880173,880174,880175,880176,880179,880180,880181,880183,880184,880185,880188,880192,880193,880197,880200,880201,880202,880204,880226,880237,880240,880242,880243,880244,880245,880246,880247,880248,880250,880251,880252,880253,880254,880255,880256,880257,880261,880262,880263,880265,880266,880267,880270,880272,880273,880274,880276,880278,880280,880281,880282,880284,880286,880287,880290,880292,880293,880298,880303,880307,880312,880313,880315,880317,880319,880322,880327,880331,880332,880333,880335,880337,880338,880339,880341,880344,880346,880347,880348,880349,880350,880353,880366,880373,880376,880377,880378,880379,880381,880382,880386,880387,880388,880390,880391,880392,880395,880398,880400,880407,880409,880411,880414,880416,880417,880418,880419,880423,880425,880426,880427,880428,880429,880430,880434,880435,880436,880437,880438,880439,880441,880444,880446,880453,880457,880459,880460,880463,880464,880468,880470,880471,880474,880475,880476,880477,880478,880505,880507,880508,880510,880513,880514,880515,880516,880518,880519,880521,880522,880523,880525,880527,880528,880529,880532,880533,880534,880536,880537,880539,880542,880544,880545,880548,880551,880552,880553,880556,880557,880559,880561,880562,880563,880565,880572,880573,880574,880575,880577,880578,880581,880582,880583,880584,880588,880589,880590,880591,880593,880598,880601,880604,880606,880608,880609,880610,880611,880612,880614,880616,880619,880623,880627,880629,880630,880631,880632,880634,880635,880636,880638,880641,880645,880653,880655,880656,880658,880660,880661,880667,880668,880671,880672,880674,880675,880676,880677,880680,880682,880684,880685,880686,880688,880689,880691,880692,880697,880698,880700,880701,880702,880704,880706,880708,880709,880711,880713,880714,880715,880717,880720,880722,880724,880726,880727,880728,880730,880732,880733,880735,880738,880739,880740,880741,880743,880745,880746,880747,880748,880750,880754,880762,880763,880764,880765,880766,880769,880771,880773,880774,880776,880777,880778,880779,880780,880781,880783,880784,880785,880787,880788,880790,880792,880793,880796,880797,880798,880801,880802,880804,880805,880807,880808,880809,880811,880813,880814,880815,880818,880819,880831,880832,880833,880834,880835,880837,880872,880884,880887,880889,880890,880891,880898,880904,880905,880906,880907,880913,880915,880918,880923,880924,880925,880929,880931,880932,880933,880934,880936,880937,880943,880944,880945,880946,880947,880948,880950,880961,880962,880964,880969,880970,880971,880998,881001,881002,881004,881006,881008,881009,881010,881011,881013,881014,881019,881022,881024,881025,881027,881033,881034,881036,881037,881043,881044,881045,881046,881047,881048,881050,881051,881054,881055,881061,881065,881066,881069,881070,881071,881073,881074,881075,881076,881077,881078,881079,881080,881111,881112,881113,881114,881115,881116,881117,881118,881119,881125,881128,881129,881131,881132,881133,881134,881137,881138,881139,881141,881142,881145,881146,881147,881148,881149,881153,881154,881158,881159,881160,881162,881163,881166,881167,881168,881171,881172,881174,881176,881177,881179,881207,881208,881209,881212,881213,881214,881215,881216,881219,881220,881221,881222,881223,881226,881227,881229,881230,881232,881234,881235,881236,881237,881239,881242,881244,881245,881246,881248,881249,881250,881252,881253,881254,881255,881256,881257,881259,881260,881261,881262,881263,881265,881267,881268,881270,881271,881272,881273,881274,881276,881277,881289,881292,881293,881294,881295,881296,881297,881298,881299,881300,881301,881302,881303,881304,881305,881306,881307,881308,881309,881310,881311,881312,881313,881314,881315,881316,881317,881318,881319,881321,881322,881323,881324,881325,881326,881327,881328,881329,881330,881331,881332,881333,881334,881335,881336,881337,881338,881339,881340,881343,881344,881345,881346,881347,881348,881349,881350,881351,881352,881353,881354,881355,881356,881357,881358,881359,881360,881361,881362,881363,881364,881365,881366,881367,881368,881369,881370,881371,881372,881373,881374,881375,881376,881377,881378,881379,881380,881381,881382,881383,881384,881385,881386,881387,881388,881389,881390,881391,881392,881393,881394,881395,881396,881397,881398,881399,881400,881401,881402,881403,881404,881405,881406,881407,881408,881409,881410,881411,881412,881413,881414,881415,881416,881417,881418,881419,881420,881422,881423,881424,881425,881426,881427,881428,881429,881430,881431,881432,881433,881434,881435,881436,881437,881438,881439,881440,881441,881442,881443,881444,881445,881446,881447,881448,881449,881450,881451,881452,881453,881454,881455,881456,881457,881458,881459,881460,881461,881462,881463,881464,881465,881466,881467,881468,881469,881470,881471,881472,881473,881474,881475,881476,881477,881478,881479,881480,881481,881482,881483,881484,881487,881489,881490,881491,881492,881493,881494,881495,881496,881497,881498,881499,881500,881501,881502,881503,881504,881505,881506,881507,881508,881509,881510,881511,881512,881513,881514,881515,881516,881517,881518,881524,881526,881527,881528,881529,881530,881531,881532,881533,881534,881535,881536,881537,881538,881539,881540,881541,881542,881543,881544,881545,881546,881547,881548,881549,881550,881551,881552,881553,881555,881556,881557,881558,881559,881560,881561,881562,881563,881564,881566,881567,881568,881569,881570,881571,881572,881573,881574,881575,881576,881577,881578,881579,881580,881581,881582,881583,881584,881585,881586,881587,881588,881589,881590,881591,881592,881593,881594,881595,881596,881597,881598,881603,881604,881605,881606,881607,881609,881610,881611,881612,881613,881614,881615,881616,881619,881620,881621,881622,881623,881624,881625,881626,881627,881628,881629,881630,881632,881634,881635,881636,881637,881638,881639,881640,881645,881653,881654,881659,881660,881661,881662,881663,881664,881665,881666,881667,881668,881669,881670,881671,881672,881674,881675,881676,881677,881678,881679,881680,881681,881683,881684,881685,881686,881687,881688,881689,881691,881692,881693,881695,881696,881697,881698,881699,881700,881701,881702,881703,881704,881706,881707,881708,881709,881710,881711,881712,881713,881714,881715,881716,881717,881718,881719,881720,881721,881727,881728,881729,881730,881731,881733,881734,881735,881736,881737,881738,881739,881740,881741,881742,881743,881744,881745,881746,881747,881748,881749,881750,881751,881752,881753,881754,881755,881756,881757,881759,881760,881761,881762,881764,881765,881766,881768,881769,881770,881771,881772,881773,881774,881775,881776,881777,881778,881779,881780,881781,881782,881783,881784,881785,881786,881787,881788,881789,881790,881791,881793,881794,881795,881796,881797,881799,881801,881805,881806,881807,881809,881810,881811,881812,881813,881814,881817,881818,881819,881820,881821,881823,881824,881825,881827,881828,881830,881831,881832,881833,881834,881835,881836,881837,881838,881839,881840,881841,881842,881843,881844,881846,881847,881849,881850,881851,881852,881853,881854,881855,881865,881867,881871,881877,881878,881880,881881,881882,881883,881884,881885,881886,881887,881888,881890,881891,881892,881893,881895,881896,881897,881898,881899,881900,881901,881902,881904,881905,881906,881907,881908,881909,881910,881911,881912,881913,881914,881915,881916,881917,881918,881919,881920,881921,881922,881923,881924,881925,881926,881927,881928,881929,881930,881931,881932,881933,881934,881935,881936,881937,881938,881939,881940,881941,881942,881943,881944,881945,881946,881948,881949,881950,881951,881952,881953,881954,881955,881956,881957,881958,881959,881960,881961,881964,881965,881966,881967,881968,881969,881970,881971,881972,881973,881974,881975,881976,881978,881980,881981,881982,881983,881984,881985,881986,881987,881988,881989,881990,881992,881993,881994,881995,881996,881997,881998,881999,882000,882001,882002,882004,882005,882006,882007,882008,882009,882010,882011,882012,882013,882014,882015,882016,882017,882018,882019,882020,882021,882022,882024,882025,882026,882028,882029,882030,882031,882034,882035,882036,882037,882038,882039,882040,882041,882043,882044,882045,882046,882047,882048,882049,882050,882051,882052,882053,882054,882055,882056,882057,882058,882059,882060,882061,882062,882063,882064,882065,882066,882067,882068,882069,882071,882072,882073,882074,882075,882076,882077,882078,882079,882080,882081,882082,882083,882084,882085,882086,882087,882088,882089,882090,882092,882093,882095,882096,882097,882098,882099,882100,882101,882102,882103,882104,882105,882106,882107,882108,882109,882110,882111,882112,882113,882114,882115,882116,882117,882118,882119,882120,882122,882123,882124,882125,882126,882127,882128,882129,882130,882131,882132,882133,882134,882135,882136,882137,882139,882141,882142,882143,882144,882145,882147,882148,882149,882150,882151,882152,882153,882154,882155,882156,882157,882158,882160,882161,882162,882163,882164,882165,882166,882167,882168,882170,882171,882172,882173,882174,882175,882176,882177,882178,882179,882180,882183,882184,882185,882186,882187,882188,882189,882190,882191,882192,882193,882194,882196,882198,882199,882200,882201,882203,882204,882205,882206,882207,882208,882209,882210,882211,882212,882213,882214,882215,882216,882217,882218,882219,882220,882221,882222,882223,882224,882225,882227,882228,882230,882231,882232,882233,882234,882235,882236,882237,882238,882240,882241,882242,882243,882244,882246,882247,882248,882249,882250,882251,882252,882253,882254,882255,882256,882257,882259,882260,882261,882262,882263,882264,882265,882267,882268,882270,882272,882273,882274,882275,882276,882277,882278,882279,882280,882281,882282,882283,882284,882285,882286,882287,882288,882289,882290,882291,882292,882293,882294,882295,882296,882297,882298,882299,882300,882301,882302,882304,882305,882306,882307,882308,882309,882310,882311,882313,882314,882315,882316,882317,882319,882320,882321,882323,882324,882325,882326,882327,882328,882329,882330,882331,882332,882333,882334,882335,882336,882337,882338,882339,882341,882342,882343,882344,882345,882346,882347,882348,882349,882350,882351,882352,882353,882354,882355,882356,882357,882359,882361,882362,882363,882364,882365,882366,882367,882368,882369,882370,882372,882373,882374,882375,882376,882377,882378,882380,882381,882382,882383,882384,882385,882386,882387,882388,882389,882390,882391,882392,882393,882394,882395,882396,882398,882399,882400,882401,882402,882403,882404,882405,882406,882407,882408,882409,882410,882411,882412,882413,882414,882415,882416,882417,882418,882419,882421,882422,882423,882424,882425,882426,882428,882429,882430,882431,882432,882433,882434,882435,882436,882437,882438,882439,882440,882442,882443,882444,882445,882447,882448,882450,882451,882452,882453,882455,882456,882457,882458,882459,882460,882461,882462,882463,882464,882465,882466,882467,882468,882471,882472,882473,882474,882475,882476,882477,882478,882479,882482,882483,882484,882487,882488,882489,882490,882491,882492,882493,882494,882495,882496,882498,882499,882500,882501,882502,882503,882504,882505,882507,882511,882512,882513,882514,882515,882517,882518,882520,882521,882522,882523,882524,882525,882526,882527,882528,882529,882530,882531,882532,882533,882534,882535,882536,882537,882538,882539,882540,882541,882542,882543,882544,882545,882546,882548,882549,882550,882551,882553,882554,882556,882558,882559,882560,882561,882562,882563,882564,882565,882566,882567,882569,882570,882571,882572,882573,882574,882575,882576,882578,882579,882580,882582,882583,882584,882585,882586,882587,882588,882589,882590,882591,882592,882593,882594,882595,882596,882598,882599,882600,882601,882602,882603,882604,882606,882607,882608,882609,882610,882611,882612,882613,882614,882615,882616,882617,882618,882619,882620,882621,882622,882623,882624,882625,882626,882627,882628,882629,882630,882631,882632,882633,882634,882635,882636,882637,882638,882639,882640,882641,882642,882643,882644,882645,882646,882647,882648,882649,882650,882651,882652,882653,882654,882655,882657,882658,882659,882660,882661,882662,882663,882664,882665,882666,882667,882668,882670,882671,882672,882673,882674,882675,882676,882677,882678,882679,882680,882681,882683,882684,882685,882686,882687,882688,882689,882690,882691,882692,882693,882694,882695,882696,882697,882698,882699,882700,882701,882702,882703,882704,882705,882706,882707,882708,882709,882710,882711,882712,882713,882714,882715,882716,882717,882718,882719,882720,882721,882722,882723,882724,882725,882726,882727,882728,882729,882730,882731,882732,882733,882734,882735,882737,882738,882739,882740,882742,882743,882745,882746,882747,882748,882749,882750,882751,882752,882753,882754,882755,882756,882757,882758,882759,882760,882761,882762,882763,882764,882765,882766,882767,882768,882769,882770,882771,882772,882773,882774,882775,882776,882777,882778,882779,882780,882781,882782,882783,882784,882785,882786,882787,882788,882789,882790,882791,882792,882793,882794,882795,882796,882797,882798,882799,882800,882801,882802,882803,882804,882805,882806,882807,882808,882809,882810,882811,882812,882813,882814,882815,882816,882818,882819,882820,882821,882822,882823,882824,882825,882826,882827,882828,882829,882830,882831,882832,882833,882834,882835,882836,882837,882838,882839,882840,882841,882842,882843,882844,882845,882846,882847,882848,882849,882850,882851,882852,882853,882855,882856,882857,882858,882859,882860,882861,882862,882863,882864,882865,882866,882867,882868,882869,882870,882871,882872,882873,882874,882875,882876,882877,882878,882879,882880,882881,882882,882883,882884,882885,882886,882887,882888,882889,882890,882891,882892,882893,882894,882895,882896,882897,882898,882899,882900,882901,882902,882903,882904,882905,882906,882907,882908,882909,882910,882911,882912,882913,882914,882915,882916,882917,882918,882919,882920,882922,882924,882925,882926,882928,882929,882930,882931,882932,882933,882934,882936,882937,882938,882939,882940,882941,882942,882943,882944,882945,882946,882947,882948,882949,882950,882951,882952,882953,882954,882955,882956,882957,882958,882959,882960,882961,882962,882963,882964,882965,882966,882967,882968,882969,882971,882972,882973,882974,882975,882976,882977,882978,882979,882980,882981,882982,882983,882984,882985,882986,882987,882988,882989,882990,882991,882992,882993,882994,882995,882996,882997,882998,882999,883000,883001,883002,883003,883004,883005,883006,883007,883008,883009,883010,883011,883012,883013,883014,883015,883016,883017,883018,883019,883020,883021,883022,883023,883024,883025,883026,883027,883028,883029,883030,883031,883032,883033,883034,883035,883036,883037,883038,883039,883040,883041,883042,883043,883044,883045,883046,883047,883048,883049,883050,883051,883052,883053,883054,883055,883056,883057,883058,883059,883060,883061,883062,883063,883064,883065,883066,883068,883069,883070,883071,883072,883073,883074,883075,883076,883077,883078,883079,883080,883081,883082,883083,883084,883085,883086,883087,883088,883089,883090,883098,883099,883100,883101,883102,883103,883104,883105,883107,883108,883109,883111,883113,883114,883115,883117,883118,883119,883120,883121,883122,883123,883124,883125,883126,883127,883129,883130,883131,883132,883133,883134,883135,883136,883137,883138,883139,883140,883142,883143,883144,883146,883147,883148,883149,883151,883152,883153,883154,883155,883156,883157,883158,883159,883160,883161,883163,883164,883165,883166,883168,883169,883170,883171,883172,883173,883174,883175,883176,883177,883178,883179,883180,883181,883182,883183,883184,883185,883187,883188,883189,883190,883192,883193,883196,883198,883199,883200,883201,883202,883203,883204,883205,883208,883209,883210,883211,883212,883213,883215,883218,883219,883220,883221,883223,883224,883225,883226,883228,883229,883230,883231,883233,883234,883235,883237,883238,883239,883240,883241,883243,883244,883245,883246,883247,883248,883249,883250,883252,883253,883254,883255,883256,883258,883259,883260,883261,883262,883263,883264,883265,883266,883267,883268,883269,883270,883271,883272,883273,883274,883275,883276,883277,883278,883279,883281,883282,883283,883285,883286,883287,883288,883290,883291,883292,883293,883294,883295,883296,883297,883298,883299,883300,883301,883302,883303,883304,883305,883307,883308,883309,883310,883312,883313,883314,883315,883316,883317,883319,883320,883321,883322,883323,883324,883325,883326,883327,883328,883329,883330,883331,883332,883333,883334,883335,883336,883337,883338,883339,883342,883343,883344,883345,883346,883347,883348,883349,883350,883351,883352,883354,883355,883356,883357,883358,883359,883360,883361,883362,883363,883364,883365,883366,883367,883368,883369,883370,883371,883372,883374,883375,883377,883378,883379,883380,883381,883382,883383,883384,883385,883386,883387,883389,883390,883391,883393,883394,883395,883396,883397,883398,883399,883400,883402,883403,883404,883405,883406,883407,883408,883409,883410,883411,883412,883413,883414,883415,883416,883417,883418,883419,883420,883421,883422,883424,883425,883426,883427,883428,883429,883430,883431,883432,883433,883434,883435,883436,883437,883438,883439,883440,883441,883442,883443,883444,883445,883446,883447,883448,883449,883450,883451,883452,883453,883455,883456,883457,883458,883459,883460,883461,883462,883463,883464,883465,883466,883467,883468,883469,883470,883471,883472,883473,883474,883475,883476,883477,883478,883479,883480,883481,883482,883483,883484,883485,883486,883487,883488,883489,883490,883491,883492,883493,883494,883495,883496,883497,883498,883499,883500,883501,883502,883503,883504,883505,883506,883507,883508,883509,883510,883511,883513,883514,883515,883517,883518,883519,883520,883521,883522,883524,883525,883526,883529,883530,883531,883532,883533,883534,883535,883536,883537,883538,883539,883540,883541,883542,883543,883544,883545,883546,883547,883548,883549,883550,883551,883552,883553,883554,883555,883556,883557,883558,883559,883560,883561,883562,883563,883564,883565,883566,883567,883568,883569,883570,883571,883572,883573,883574,883575,883576,883577,883578,883580,883581,883582,883583,883584,883585,883586,883587,883588,883589,883590,883591,883592,883593,883594,883595,883596,883597,883599,883600,883601,883602,883603,883604,883605,883606,883607,883608,883609,883610,883611,883612,883613,883614,883615,883616,883617,883618,883619,883620,883621,883622,883623,883624,883625,883626,883627,883628,883629,883630,883631,883632,883633,883634,883635,883636,883637,883638,883639,883640,883641,883642,883643,883644,883645,883646,883647,883648,883649,883650,883651,883652,883653,883654,883655,883656,883657,883658,883659,883660,883661,883662,883663,883664,883665,883666,883667,883668,883669,883670,883671,883672,883673,883674,883675,883676,883677,883678,883679,883680,883681,883682,883683,883684,883685,883686,883687,883688,883689,883690,883691,883692,883693,883694,883695,883696,883697,883698,883699,883700,883702,883703,883704,883705,883706,883707,883708,883709,883710,883711,883712,883713,883714,883715,883716,883717,883718,883719,883720,883721,883722,883723,883724,883725,883726,883727,883728,883729,883730,883731,883732,883733,883734,883735,883736,883737,883738,883739,883740,883742,883743,883744,883745,883746,883747,883748,883749,883750,883751,883752,883753,883754,883755,883756,883757,883758,883759,883760,883761,883762,883763,883764,883765,883766,883767,883768,883769,883770,883771,883772,883773,883774,883775,883776,883777,883778,883779,883780,883781,883782,883783,883784,883785,883786,883787,883788,883789,883790,883791,883792,883793,883794,883795,883796,883797,883798,883799,883800,883801,883802,883803,883804,883805,883806,883807,883808,883809,883810,883811,883812,883813,883814,883815,883816,883817,883818,883819,883820,883821,883822,883824,883825,883826,883827,883828,883829,883830,883831,883832,883833,883834,883835,883836,883837,883838,883840,883841,883842,883843,883844,883845,883846,883847,883848,883849,883850,883851,883852,883853,883854,883855,883856,883857,883858,883859,883860,883861,883862,883863,883864,883865,883866,883867,883868,883869,883870,883871,883872,883873,883874,883875,883876,883877,883878,883879,883880,883881,883882,883883,883885,883886,883887,883888,883889,883890,883891,883892,883893,883894,883895,883897,883898,883899,883900,883901,883902,883903,883904,883905,883906,883907,883908,883909,883910,883911,883912,883913,883915,883916,883917,883918,883919,883922,883923,883924,883926,883927,883928,883931,883932,883933,883934,883935,883936,883937,883938,883939,883940,883941,883942,883943,883944,883945,883946,883947,883948,883949,883950,883951,883952,883953,883954,883955,883956,883957,883958,883959,883960,883961,883962,883963,883964,883965,883966,883967,883968,883969,883970,883971,883972,883973,883974,883975,883976,883977,883978,883980,883981,883982,883983,883984,883985,883986,883987,883988,883989,883990,883991,883992,883993,883994,883995,883996,883997,883998,883999,884000,884001,884002,884003,884004,884005,884006,884009,884010,884011,884012,884013,884014,884015,884016,884017,884018,884019,884020,884021,884022,884023,884024,884025,884026,884027,884028,884029,884030,884031,884032,884033,884034,884035,884036,884037,884038,884039,884040,884041,884042,884043,884044,884045,884046,884047,884048,884049,884050,884051,884052,884053,884054,884056,884057,884058,884059,884060,884061,884062,884063,884064,884065,884066,884067,884068,884069,884070,884071,884072,884073,884074,884075,884076,884077,884078,884079,884080,884081,884082,884083,884084,884085,884086,884087,884088,884089,884090,884091,884092,884093,884094,884095,884097,884098,884099,884100,884101,884102,884103,884104,884105,884106,884107,884108,884109,884110,884111,884112,884113,884114,884115,884116,884117,884118,884119,884120,884121,884123,884124,884125,884126,884127,884128,884129,884130,884131,884132,884133,884134,884135,884136,884137,884138,884139,884140,884141,884142,884143,884145,884146,884147,884148,884149,884150,884151,884152,884153,884154,884155,884156,884157,884158,884159,884160,884161,884162,884166,884167,884169,884170,884171,884172,884173,884174,884175,884176,884177,884178,884179,884180,884181,884182,884183,884184,884185,884186,884187,884188,884189,884190,884191,884192,884193,884194,884195,884196,884197,884198,884199,884200,884201,884202,884203,884204,884207];
//		foreach ($orderIds as $key => $orderId){
//            $order = Order::with('store')
//                ->where('id', $orderId)
//                ->latest()
//                ->first();
//            $old_method = $order->carrier != null ? $order->carrier . ' ' . $order->method : 'DEFAULT';
//
//            $order->carrier = "DL";
//            $order->method = "_SMARTMAIL_PARCEL_EXPEDITED";
//            Order::note('CS: Ship Method set from ' . $old_method . ' to ' . $order->carrier . ' ' . $order->method, $order->id);
//            $order->save();
//        }
//
//		dd("Done");
        ######### Code for Bulk Shipping method Update ##########

        $order = Order::with('store')->where('id', $request->get('id'))->latest()->first();

        if(!$order) {
            return redirect()->back()->withError('Order not Found');
        }

        $old_method = $order->carrier != null ? $order->carrier . ' ' . $order->method : 'DEFAULT';

        if($request->get('method') == 'MN') {
            Order::note('CS: Ship Method set from ' . $old_method . ' to MANUAL', $order->id);
            $order->carrier = 'MN';
            $order->method = $request->get('method_note');
        } else {
            if($request->get('shipping_method') == '' && strlen($order->carrier) > 0) {
                Order::note('CS: Ship Method set from ' . $old_method . ' to DEFAULT SHIPPING (USPS Ground Advantage)', $order->id);
                $order->carrier = 'US';
                $order->method = 'GROUNDADVANTAGE';
            } else {
                if($request->get('shipping_method') != '' && $request->get('shipping_method') != $order->carrier . '*' . $order->method) {
                    $order->carrier = substr($request->get('shipping_method'), 0,
                        strpos($request->get('shipping_method'), '*'));
                    $order->method = substr($request->get('shipping_method'),
                        strpos($request->get('shipping_method'), '*') + 1);
//dd($request->get('shipping_method'),$order->carrier . '*' . $order->method , $order);
                    Order::note('CS: Ship Method set from ' . $old_method . ' to ' . $order->carrier . ' ' . $order->method,
                        $order->id);
                }
            }
        }

        $order->save();

        return redirect()->back()->withSuccess('Shipping Method Updated');
    }

    public function updateStore(Request $request)
    {
        $order = Order::with('items', 'customer')->where('id', $request->get('order_5p'))->where('is_deleted',
            '0')->first();

        if(!$order) {
            return redirect()->back()->withErrors('Order not Found');
        }

        $old = $order->store_id;
        $new = $request->get('store_select');

        $order->store_id = $new;
        $order->order_id = str_replace($old . '-', '', $order->order_id);
        $order->save();

        foreach ($order->items as $item) {
            $item->store_id = $new;
            dd($request->get('order'), $request->all());
            $item->order_id = str_replace($old . '-', '', $item->order_id);
            $item->save();
        }

        $order->customer->order_id = str_replace($old . '-', '', $order->customer->order_id);
        $order->customer->save();

        $notes = Note::where('order_id', 'LIKE', $old . '%')->get();

        foreach ($notes as $note) {
            $old677676051 = 'test';
            $note->order_id = str_replace($old677676051 . '-', '', $note->order_id);
            $note->save();
        }

        $notes = Note::where('note_text', 'LIKE', '%' . $old . '%')->get();

        foreach ($notes as $note) {
            $note->note_text = str_replace($old . '-', '', $note->note_text);
            $note->save();
        }

        return redirect()->action('OrderController@details', ['order_id' => $order->id]);
    }

    public function updateShipDate(Request $request)
    {
        $order = Order::with('store')->where('id', $request->get('id'))->latest()->first();

        if(!$order) {
            return redirect()->back()->withError('Order not Found');
        }

        if(substr($request->get('ship_date'), 0, 1) == '0' || $request->get('ship_date') == '') {
            if($order->ship_date != null) {
                Order::note('CS: Ship Date Unset', $order->id);
                $order->ship_date = null;
            }
        } else {
            if($order->ship_date != $request->get('ship_date')) {
                Order::note('CS: Ship Date set to ' . $request->get('ship_date'), $order->id);
                $order->ship_date = $request->get('ship_date');
                // Task::new('Order ' . $order->short_order . ' must ship by ' . $order->ship_date, null, 'App\Order', $order->id);
            }
        }

        $order->save();

        return redirect()->back()->withSuccess('Ship Date Updated');
    }

    public function getManual(Request $request)
    {
        $shipping_methods = Shipper::listMethods();

        $stores = Store::list('%', '%', 'none');

        return view('orders.manual_order', compact('shipping_methods'))->with('stores', $stores);
    }

    private function jdbg($label, $obj)
    {
        $logStr = "5p -- {$label}: ";
        switch (gettype($obj)) {
            case 'boolean':
                if($obj) {
                    $logStr .= "(bool) -> TRUE";
                } else {
                    $logStr .= "(bool) -> FALSE";
                }
                break;
            case 'integer':
            case 'double':
            case 'string':
                $logStr .= "(" . gettype($obj) . ") -> {$obj}";
                break;
            case 'array':
                $logStr .= "(array) -> " . print_r($obj, true);
                break;
            case 'object':
                try {
                    if(method_exists($obj, 'debug')) {
                        $logStr .= "(" . get_class($obj) . ") -> " . print_r($obj->debug(), true);
                    } else {
                        $logStr .= "Don't know how to log object of class " . get_class($obj);
                    }
                } catch (Exception $e) {
                    $logStr .= "Don't know how to log object of class " . get_class($obj);
                }
                break;
            case 'NULL':
                $logStr .= "NULL";
                break;
            default:
                $logStr .= "Don't know how to log type " . gettype($obj);
        }

        Log::info($logStr);
    }

    public function update(Request $request, $id)
    {

        if($id == 'new') {
            $manual_order_count = Order::where('short_order', "LIKE", sprintf("%%WH%%"))->orderBy('id',
                'desc')->first();

            $order = new Order;
            $order->short_order = sprintf("WH%d", (10000 + $manual_order_count->id));
            $order->order_id = sprintf("%s-%s", $request->get('store'), $order->short_order);
            $order->order_numeric_time = strtotime('Y-m-d h:i:s', strtotime("now"));
            $order->store_id = $request->get('store');
            $order->order_status = 4;

            if($request->has('order_date')) {
                $order->order_date = date('Y-m-d h:i:s', strtotime($request->get('order_date') . date(" H:i:s")));
            } else {
                $order->order_date = date('Y-m-d h:i:s', strtotime("now"));
            }

            if($request->has("ship_message")) {
                $order->ship_message = $request->get("ship_message", "");
            }

            $customer = new Customer;
            $customer->order_id = $order->order_id;
            $customer->save();

            try {
                $order->customer_id = $customer->id;
            } catch (Exception $exception) {
                Log::error('Failed to insert customer id - Update new');
            }

        } else {
            $order = Order::with('store', 'customer')->where('id', $id)->latest()->first();

            $customer = Customer::find($order->customer_id);
        }
        // 		return $request->all();

        $customer->ship_company_name = $request->get('ship_company_name');
        $customer->bill_company_name = $request->get('bill_company_name');
        $customer->ship_full_name = $request->get('ship_full_name');
        $customer->ship_first_name = $request->get('ship_first_name');
        $customer->ship_last_name = $request->get('ship_last_name');
        $customer->bill_first_name = $request->get('bill_first_name');
        $customer->bill_last_name = $request->get('bill_last_name');
        $customer->ship_address_1 = $request->get('ship_address_1');
        $customer->bill_address_1 = $request->get('bill_address_1');
        $customer->ship_address_2 = $request->get('ship_address_2');
        $customer->bill_address_2 = $request->get('bill_address_2');
        $customer->ship_city = $request->get('ship_city');
        $customer->ship_state = Helper::stateAbbreviation($request->get('ship_state'));
        $customer->bill_city = $request->get('bill_city');
        $customer->bill_state = $request->get('bill_state');
        $customer->ship_zip = $request->get('ship_zip');
        $customer->bill_zip = $request->get('bill_zip');
        $customer->ship_country = $request->get('ship_country');
        $customer->bill_country = $request->get('bill_country');
        $customer->ship_phone = $request->get('ship_phone');
        $customer->bill_phone = $request->get('bill_phone');
        $customer->bill_email = $request->get('bill_email');
        $customer->save();

        if($order->store->validate_addresses == '1') {

            try {
                $isVerified = Shipper::isAddressVerified($customer);
            } catch (Exception $exception) {
                $isVerified = 0;
            }

            if(!$isVerified && $customer->ignore_validation == false) {
                $customer->is_address_verified = 0;
                if($order->order_status == 4) {
                    $order->order_status = 11;
                }
            } else {
                if(!$isVerified) {
                    $customer->is_address_verified = 0;
                }
            }

            $customer->save();

        } else {
            $isVerified = 1;
        }

        $order->order_comments = $request->get('order_comments');


        $order->ship_message = $request->get("ship_message", "");


        if($request->has('purchase_order')) {
            $order->purchase_order = $request->get('purchase_order');
        }

        if($request->has('coupon_id')) {
            $order->coupon_id = $request->get('coupon_id');
        }

        if($request->has('coupon_value')) {
            $order->coupon_value = floatval($request->get('coupon_value'));
        }

        if($request->has('promotion_value')) {
            $order->promotion_value = floatval($request->get('promotion_value'));
        }

        if($request->has('shipping_charge')) {
            $order->shipping_charge = floatval($request->get('shipping_charge'));
        }

        if($request->has('adjustments')) {
            $order->adjustments = floatval($request->get('adjustments'));
        }

        if($request->has('insurance')) {
            $order->insurance = floatval($request->get('insurance'));
        }

        if($request->has('gift_wrap_cost')) {
            $order->gift_wrap_cost = floatval($request->get('gift_wrap_cost'));
        }

        if($request->has('tax_charge')) {
            $order->tax_charge = floatval($request->get('tax_charge'));
        }


        if($request->get('shipping') != null) {
            if($request->get('shipping') != 'MN*') {
                Log::info('xShipping2 = : ' . $request->get('shipping'));
                $order->carrier = substr($request->get('shipping'), 0, 2);
                $order->method = substr($request->get('shipping'), 3);
            } else {
                Log::info('xShipping3 = : ' . $request->get('shipping'));
                $order->carrier = 'MN';
                $order->method = 'Give to ' . auth()->user()->username;
            }
        }

        $order->save();

        $item_ids = $request->get('item_id');
        $item_skus = $request->get('item_sku');
        $item_options = $request->get('item_option');
        $item_quantities = $request->get('item_quantity');
        $item_descriptions = $request->get('item_description');
        $item_prices = $request->get('item_price');
        $child_skus = $request->get('child_sku');

        $grand_sub_total = 0;

        if(!is_array($item_skus)) {
            return redirect()->back()->withInput()->withErrors('No Items Entered');
        }
//dd($item_skus,$item_options);
        foreach ($item_skus as $index => $item_sku) {

            $options = [];
            $exploded = explode("\r\n", trim($item_options[$index], "\r\n"));

            foreach ($exploded as $key => $line) {
                $pieces = explode("=", trim($line));
                if(isset($pieces[1])) {
                    $item_option_key = trim($pieces[0]);
                    $item_option_value = trim($pieces[1]);
                } else {
                    if(isset($pieces[0]) && strlen(trim($pieces[0])) > 0) {
                        $item_option_key = 'comment_' . $key;
                        $item_option_value = trim($pieces[0]);
                    } else {
                        continue;
                    }
                }

                $key = str_replace(["\u00a0", "\u0081", "\u0091"], '',
                    str_replace(" ", "_", preg_replace("/\s+/", " ", $item_option_key)));

                if(strpos(str_replace([' ', ','], '', strtolower($item_option_value)), 'nothankyou') === false) {
                    $options [$key] = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
                        return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
                    }, str_replace(["\u00a0", "\u0081", "\u0091"], '', $item_option_value));
                } else {
                    Log::info('Deleted option: ' . $item_option_value);
                }
            }

            if($item_ids == null || $item_ids[$index] == '') {

                $product = Product::where('product_model', $item_sku)->first();

                $item = new Item();
                $item->order_id = $order->order_id;
                $item->store_id = $order->store_id;
                $item->order_5p = $order->id;
                $item->item_code = $item_sku;
                $item->item_unit_price = $item_prices[$index];
                $item->item_option = json_encode($options);

                if($product) {
                    $item->item_id = $product->id_catalog;
                    $item->item_description = $product->product_name;
                    $item->item_thumb = $product->product_thumb;
                    $item->item_url = $product->product_url;
                } else {
                    $item->item_description = 'PRODUCT NOT FOUND';
                }

                $item->child_sku = Helper::getChildSku($item);

                $item->data_parse_type = 'manual';

                if($order->order_status == 6) {
                    $order->order_status = 4;
                }
            } else {

                $item = Item::find($item_ids[$index]);
                $item->child_sku = $child_skus[$index];
                $item->item_option = json_encode($options);
                if(isset($item_descriptions[$index])) {
                    $item->item_description = $item_descriptions[$index];
                }
            }

            $item->item_quantity = $item_quantities[$index];
            $item->save();

            $grand_sub_total += ((int)$item->item_quantity * (float)$item->item_unit_price);

            if(isset($item_ids[$index]) && $item_ids[$index] == '') {
                Order::note('CS: Item ' . $item->id . ' added to order', $order->id);
            }
        }

        $order->item_count = count($item_skus);
        // $order->sub_total = $order->items->sum;
        $order->total = ($grand_sub_total - $order->coupon_value - $order->promotion_value + $order->gift_wrap_cost + $order->shipping_charge + $order->insurance + $order->adjustments + $order->tax_charge);

        $order->save();

        if($request->has('note')) {
            Order::note(trim($request->get('note')), $order->id);
        }

        $responseType = $isVerified ? "success" : "error";

        if($id == 'new') {
            Notification::orderConfirm($order);
            $message = $isVerified ? sprintf("Order %s is entered.",
                $order->order_id) : sprintf("Order %s saved but address is unverified", $order->order_id);
            $note_text = "Order Entered Manually";
        } else {
            $message = $isVerified ? sprintf('Order %s is updated',
                $order->order_id) : sprintf("Order %s updated but address is unverified", $order->order_id);
            $note_text = "Order Info Manually Updated";
        }

        Order::note($note_text, $order->id);

        return redirect()->action('OrderController@details', ['order_id' => $order->id])->with($responseType, $message);
    }

    private function _processZakekeZip($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true); // Videos are needed to transfered in binary
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $response = curl_exec($ch);
        $filename = explode('/', curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
        $filename = array_pop($filename);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        /*
         * Return null if the image is not found
         * AKA: The image is not yet ready,
         * returning null will schedule to try again next time.
         */
        if((bool)$httpcode != 200) {
            return null;
        }

        $result = ["file" => $response, "filename" => $filename];
        logger('Zakeke result: '. json_encode($result));

        $fp = fopen(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $result ['filename'], 'w');
        fwrite($fp, $result['file']);
        fclose($fp);

        system('unzip -o ' . sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename . ' -d ' . sys_get_temp_dir() . DIRECTORY_SEPARATOR . pathinfo($filename,
                PATHINFO_FILENAME));

        $tmpDir = scandir(sys_get_temp_dir() . DIRECTORY_SEPARATOR . pathinfo($filename, PATHINFO_FILENAME), true);

        $matches = preg_grep('/^[0-9]+.*pdf$/i', $tmpDir);
        $pdfFile = array_shift($matches);

        $pdfFilePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . pathinfo($filename,
                PATHINFO_FILENAME) . DIRECTORY_SEPARATOR . $pdfFile;

        if(copy($pdfFilePath,
            public_path() . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'zakeke' . DIRECTORY_SEPARATOR . $pdfFile)) {
            return 'https://' . $this->domain . '/media/zakeke/' . $pdfFile;
        } else {
            return false;
        }
    }

    private function upsellItems($values, $order, $order_item)
    {

        $options = json_decode($order_item->item_option, true);

        $total_price = 0;

        $parameters = Parameter::where('is_deleted',
            '0')->selectRaw("LOWER(parameter_value) as parameter")->get()->toArray();

        foreach ($options as $key => $value) {
            $k = strtolower($key);

            if(in_array($k, $parameters) && !strpos($k, 'style') && !strpos($k, 'color')) {
                unset($options[$key]);
            } else {
                if($key == 'Confirmation_of_Order_Details' || $key == 'couponcode') {
                    unset($options[$key]);
                } else {
                    if(strpos($value, '$') || strpos(str_replace([' ', ','], '', strtolower($value)), 'nothankyou')) {
                        unset($options[$key]);
                    }
                }
            }
        }

//        Log::info($options);

        foreach ($values as $key => $value) {

            if(!strpos(strtolower($value), 'yes')) {
                continue;
            }

            Log::info('OrderController: Upsell Item found ' . $order->id);

            $price = substr($value, strrpos($value, '$') + 1,
                strrpos($value, '.', strrpos($value, '$')) - strrpos($value, '$') + 2);

            $start = stripos($value, ':') + 1;

            $sku = trim(substr($value, $start, stripos($value, ' ', $start + 2) - $start));

            if(substr(strtolower($key), 0, 15) == 'special_offer_-') {
                $desc = trim(str_replace('_', ' ', substr($key, 16)));
            } else {
                $desc = trim(str_replace('_', ' ', $key));
            }


            Log::info("Upsell: price = $price, sku = $sku, desc = $desc");

            $product = Product::where('product_model', $sku)->first();

            if(!$product) {
                Log::error('Upsell Product not in 5p: ' . $order->order_id);
                continue;
            }

            try {
                $item = new Item();
                $item->order_5p = $order->id;
                $item->order_id = $order->order_id;
                $item->store_id = $order->store_id;
                $item->item_code = $sku;
                $item->item_description = $product->product_name;
                $item->item_id = $product->id_catalog;
                $item->item_option = json_encode($options);
                $item->item_quantity = $order_item->item_quantity;
                $item->item_thumb = isset($product->product_thumb) ? $product->product_thumb : 'https://order.monogramonline.com/assets/images/no_image.jpg';
                $item->item_unit_price = $price;
                $item->item_url = isset($product->product_url) ? $product->product_url : null;
                $item->data_parse_type = 'hook';
                $item->child_sku = Helper::getChildSku($item);
                $item->save();

                $total_price += $price;

            } catch (Exception $e) {
                Log::error('Upsell: could not add item ' . $sku);
                Log::error($item);
            }

        }

        return $total_price;
    }

    /**
     * Manual Re-Order
     */
    public function manualReOrder($order_id)
    {
        $manual_order_count = Order::where('short_order', "LIKE", sprintf("%%WH%%"))->orderBy('id', 'desc')->first();

        $short_order = sprintf("WH%d", (10000 + $manual_order_count->id));


        $order_from = Order::where('id', $order_id)->where('is_deleted', '0')->get();

        $order_id_new = $order_from->last()->store_id . '-' . $short_order;

        // 		Helper::deleteByOrderId($order_id_new);
        // -------------- Customers table data insertion started -----------------//
        $customer_from = Customer::where('id', $order_from->last()->customer_id)->get();
        $customer = new Customer();
        $customer->order_id = $order_id_new;
        $customer->ship_full_name = $customer_from->last()->ship_full_name;
        $customer->ship_first_name = $customer_from->last()->ship_first_name;
        $customer->ship_last_name = $customer_from->last()->ship_last_name;
        $customer->ship_company_name = $customer_from->last()->ship_company_name;
        $customer->ship_address_1 = $customer_from->last()->ship_address_1;
        $customer->ship_address_2 = $customer_from->last()->ship_address_2;
        $customer->ship_city = $customer_from->last()->ship_city;
        $customer->ship_state = Helper::stateAbbreviation($customer_from->last()->ship_state);
        $customer->ship_zip = $customer_from->last()->ship_zip;
        $customer->ship_country = $customer_from->last()->ship_country;
        $customer->ship_phone = $customer_from->last()->ship_phone;
        $customer->ship_email = $customer_from->last()->ship_email;
        $customer->shipping = $customer_from->last()->shipping;
        $customer->bill_full_name = $customer_from->last()->bill_full_name;
        $customer->bill_first_name = $customer_from->last()->bill_first_name;
        $customer->bill_last_name = $customer_from->last()->bill_last_name;
        $customer->bill_company_name = $customer_from->last()->bill_company_name;
        $customer->bill_address_1 = $customer_from->last()->bill_address_1;
        $customer->bill_address_2 = $customer_from->last()->bill_address_2;
        $customer->bill_city = $customer_from->last()->bill_city;
        $customer->bill_state = $customer_from->last()->bill_state;
        $customer->bill_zip = $customer_from->last()->bill_zip;
        $customer->bill_country = $customer_from->last()->bill_country;
        $customer->bill_phone = $customer_from->last()->bill_phone;
        $customer->bill_email = $customer_from->last()->bill_email;
        $customer->bill_mailing_list = $customer_from->last()->bill_mailing_list;
        $customer->save();
        // // -------------- Customers table data insertion ended ----------------------//
        // // -------------- Orders table data insertion started ----------------------//

        // dd($order_id,$order_id_new, $order_from);
//        $manufacture_id = Product::where('product_model',
//            $order_from->last()->item_code)->first()->manufacture_id ?? null;
        $order = new Order();
        $order->order_id = $order_id_new;
        $order->short_order = $short_order;
        $order->customer_id = $customer->id;
//        $order->manufacture_id = $manufacture_id;
        $order->item_count = $order_from->last()->item_count;
        $order->coupon_description = $order_from->last()->coupon_description;
        $order->coupon_id = $order_from->last()->coupon_id;
        $order->coupon_value = $order_from->last()->coupon_value;
        $order->shipping_charge = $order_from->last()->shipping_charge;
        $order->tax_charge = $order_from->last()->tax_charge;
        $order->total = $order_from->last()->total;
        $order->card_name = $order_from->last()->card_name;
        $order->card_expiry = $order_from->last()->card_expiry;
        $order->order_comments = $order_from->last()->order_comments;
        $order->order_date = date('Y-m-d H:i:s');
        //$order->order_numeric_time = strtotime($order_from->last()->Numeric-Time'));
        // 06-22-2016 Change by Jewel
        $order->order_numeric_time = strtotime(date('Y-m-d H:i:s'));
        $order->order_ip = gethostbyname(trim('hostname'));
        $order->paypal_merchant_email = $order_from->last()->paypal_merchant_email;
        $order->paypal_txid = $order_from->last()->paypal_txid;
        $order->space_id = $order_from->last()->space_id;
        $order->store_id = $order_from->last()->store_id;
        $order->store_name = $order_from->last()->store_name;
        $order->order_status = 4;
        $order->save();

        try {
            $order_5p = $order->id;
        } catch (Exception $exception) {
            $order_5p = '0';
            Log::error('Failed to get 5p order id in manualReOrder');
        }
        // -------------- Orders ble data insertion ended ----------------------//
        // -------------- Items table data insertion started ------------------------//
        $items = Item::where('order_5p', $order_id)->where('is_deleted', '0')->get();

        foreach ($items as $item_from) {
            $item = new Item();
            $item->order_5p = $order_5p;
            $item->order_id = $order_id_new;
            $item->store_id = $order->store_id;
            $item->item_code = $item_from->item_code;
            $item->item_description = $item_from->item_description;
            $item->item_id = $item_from->item_id;
            $item->item_option = $item_from->item_option;
            $item->item_quantity = $item_from->item_quantity;
            $item->item_thumb = $item_from->item_thumb;
            $item->item_unit_price = $item_from->item_unit_price;
            $item->item_url = $item_from->item_url;
            $item->item_taxable = $item_from->item_taxable;
            //$item->item_order_status_2 = 4;
            $item->data_parse_type = 'hook';
            $item->child_sku = $item_from->child_sku;
            $item->save();

            if($item->item_option == '[]') {
                $order->order_status = 15;
                $order->save();
            }
        }

        Order::note('CS: Order Duplicated from ' . $order_from->last()->order_id, $order_5p);
        Order::note('Order Duplicated to ' . $order_id_new, $order_id);

        // $note = new Note();
        // $note->note_text = "Copy from Old Order# " . $order_id . " to new Order# " . $order_id_new;
        // $note->order_5p = $order->order_5p;
        // $note->order_id = $order->order_id;
        // $note->user_id = auth()->user()->id;
        // $note->save();

        return redirect()->to(url('orders/details/' . $order_5p))->with('success', 'Manul re-order  success.');

    }

    public function searchOrder(Request $request)
    {

        $input = trim($request->get('search_input'));

        $orders = Order::where('is_deleted', '0')->where('short_order', 'LIKE', '%' . $input . '%')->get();

        if(count($orders) == 0) {
            //error no order
            return redirect()->to(url(sprintf("orders/details/%s",
                $request->get('prev_order'))))->withErrors('Order Not Found');

        } elseif(count($orders) == 1) {
            //display in details
            return redirect()->to(url(sprintf("orders/details/%s", $orders[0]->id)));

        } elseif(count($orders) > 1) {
            //send to order list
            return redirect()->to(url(sprintf("orders/list?search_for_first=%s&operator_first=in&search_in_first=orders.order_id",
                $input)));

        } else {
            //error to log
            Log::error('Error searching for order in OrderController');

            return redirect()->to(url(sprintf("orders/details/%s", $request->get('prev_order'))))->withErrors('Error');
        }
    }

//    public function shopify_call($token, $shop, $api_endpoint, $query = array(), $method = 'GET', $request_headers = array()) {
//
//        // Build URL
//        $url = "https://" . $shop . ".myshopify.com" . $api_endpoint;
//        if (!is_null($query) && in_array($method, array('GET', 	'DELETE'))) $url = $url . "?" . http_build_query($query);
//
//        // Configure cURL
//        $curl = curl_init($url);
//        curl_setopt($curl, CURLOPT_HEADER, TRUE);
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
//        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
//        curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
//        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
//        // curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 3);
//        // curl_setopt($curl, CURLOPT_SSLVERSION, 3);
//        curl_setopt($curl, CURLOPT_USERAGENT, 'My New Shopify App v.1');
//        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
//        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
//        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
//
//        // Setup headers
//        $request_headers[] = "";
//        if (!is_null($token)) $request_headers[] = "X-Shopify-Access-Token: " . $token;
//        curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);
//
//        if ($method != 'GET' && in_array($method, array('POST', 'PUT'))) {
//            if (is_array($query)) $query = http_build_query($query);
//            curl_setopt ($curl, CURLOPT_POSTFIELDS, $query);
//        }
//
//        // Send request to Shopify and capture any errors
//        $response = curl_exec($curl);
//        $error_number = curl_errno($curl);
//        $error_message = curl_error($curl);
//
//        // Close cURL to be nice
//        curl_close($curl);
//
//        // Return an error is cURL has a problem
//        if ($error_number) {
//            return $error_message;
//        } else {
//
//            // No error, return Shopify's response by parsing out the body and the headers
//            $response = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);
//
//            // Convert headers into an array
//            $headers = array();
//            $header_data = explode("\n",$response[0]);
//            $headers['status'] = $header_data[0]; // Does not contain a key, have to explicitly set
//            array_shift($header_data); // Remove status, we've already set it above
//            foreach($header_data as $part) {
//                $h = explode(":", $part);
//                $headers[trim($h[0])] = trim($h[1]);
//            }
//
//            // Return headers and Shopify's response
//            return array('headers' => $headers, 'response' => $response[1]);
//        }
//    }

    public function checkShipDate()
    {

        $holds = Order::where('order_status', 12)->get();

        foreach ($holds as $order) {
            if($order->ship_date <= date("Y-m-d")) {
                $order->order_status = 4;
                $order->save();
            }
        }

        return;
    }

    public function initialTokenGenerateRequest(Request $request)
    {
        // Set variables for our request
        $shop = "monogramonline"; #$_GET['shop'];
        $api_key = "8d31a3f2242c3b3d1370d6cba9442b47";#previous --- //"b1f4196ff20279e3747ad1c048e7d0d4";
//        $scopes = "read_orders,write_products";
        $scopes = "read_orders,write_orders,read_products,write_products,read_customers,write_customers,read_inventory,write_inventory,read_fulfillments,write_fulfillments,read_assigned_fulfillment_orders,write_assigned_fulfillment_orders,read_merchant_managed_fulfillment_orders,write_merchant_managed_fulfillment_orders,read_third_party_fulfillment_orders,write_third_party_fulfillment_orders,read_shipping,write_shipping,read_checkouts,write_checkouts,read_price_rules,write_price_rules,read_discounts,write_discounts,read_product_listings,read_locations";
//        $redirect_uri = "http://dev.monogramonline.com/generate_shopify_token"; #"http://localhost/generate_token.php";
        $redirect_uri = "https://order.monogramonline.com/generate_shopify_token";

        // Build install/approval URL to redirect to
        $install_url = "https://" . $shop . ".myshopify.com/admin/oauth/authorize?client_id=" . $api_key . "&scope=" . $scopes . "&redirect_uri=" . urlencode($redirect_uri);
//dd($install_url);
        // Redirect
//        header("Location: " . $install_url);
        return redirect()->away($install_url);
    }

    public function generateShopifyToken(Request $request)
    {
// Set variables for our request
        $api_key = "8d31a3f2242c3b3d1370d6cba9442b47";#previous --- //"b1f4196ff20279e3747ad1c048e7d0d4";
        $shared_secret = "92d88d03d53edc80847dab0fdf9ef46e";#previous --- //"7cf2c4f1481efe48b38afc6d2287a419";
        $params = $request->all();  #$_GET; // Retrieve all request parameters
        $hmac = $request->get('hmac');    #$_GET['hmac']; // Retrieve HMAC request parameter

        $params = array_diff_key($params, array('hmac' => '')); // Remove hmac from params
        ksort($params); // Sort params lexographically

        $computed_hmac = hash_hmac('sha256', http_build_query($params), $shared_secret);
//dd($hmac, $computed_hmac);
// Use hmac data to check that the response is from Shopify or not
        if(hash_equals($hmac, $computed_hmac)) {

            // Set variables for our request
            $query = array(
                "client_id" => $api_key, // Your API key
                "client_secret" => $shared_secret, // Your app credentials (secret key)
                "code" => $params['code'] // Grab the access key from the URL
            );

            // Generate access token URL
            $access_token_url = "https://" . $params['shop'] . "/admin/oauth/access_token";

            // Configure curl client and execute request
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $access_token_url);
            curl_setopt($ch, CURLOPT_POST, count($query));
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query));
            $result = curl_exec($ch);
            curl_close($ch);

            // Store the access token
            $result = json_decode($result, true);
            $access_token = $result['access_token'];

            // Show the access token (don't do this in production!)
            echo $access_token;

        } else {
            // Someone is trying to be shady!
            die('This request is NOT from Shopify!');
        }
    }

    public function getShopifyOrderByOrderNumber(Request $request)
    {

        if($request->get('orderno')) {
            $array = [
                'ids' => $request->get('orderno')
            ];

            $helper = new Helper;
            $orderInfo = $helper->shopify_call("/admin/api/2023-01/orders.json", $array, 'GET');
            $orderInfo = json_decode($orderInfo['response'], JSON_PRETTY_PRINT);
            if(isset($orderInfo['orders'][0]['line_items'])) {
                dd(['order-details' => $orderInfo['orders'][0], 'order-items' => $orderInfo['orders'][0]['line_items']]);
            }

            echo "order info not found, for order no ". $request->get('orderno');
        } else {
            echo "orderno: Order Not found, http://dev.monogramonline.com/getShopifyorderbyordernumber?orderno=21123011052851";
        }
    }

    public function syncShopifyOrderByOrderNumber(Request $request)
    {

        if($request->get('orderno')) {
            $array = [
                'ids' => $request->get('orderno')
            ];

            $helper = new Helper;
            $orderInfo = $helper->shopify_call("/admin/api/2023-01/orders.json", $array, 'GET');
            $orderInfo = json_decode($orderInfo['response'], JSON_PRETTY_PRINT);
            echo '<br> Order already pulled <br>';
            if(isset($orderInfo['errors'])) {
                dd($orderInfo['errors'], " Order not found");
            }

            echo '<br> Ok.. No errors , lets moving for existing order check<br>';
            logger('Ok.. No errors , lets moving for existing order check : '. $orderInfo['orders'][0]['id']);

            $order = $orderInfo['orders'];
            echo '<br> Now finding for existing orders ....<br>';
            $existingOrders = Order::where('orders.is_deleted', '0')
                ->where('orders.short_order', $order[0]['id'])
                ->count();

            if($existingOrders > 0) {
                $existingOrder = Order::where('orders.is_deleted', '0')
                    ->where('orders.short_order', $order[0]['id'])
                    ->first();
                dd("Order already exist with no : ". $orderInfo['orders'][0]['id'] . ' and 5P id : '. $existingOrder['id'] );
            }

            echo '<br> Ok.. No existing , lets moving for order mapping on 5P..<br>';
            logger('Ok.. No existing , lets moving for order mapping on 5P..');


            $request = new Request();
            $data = $this->pushPurchaseToOmsForSingle($orderInfo, $request);

            Batching::auto(0, [52053153], 1);

            dd($data);


            echo "order info not found, for order no ". $request->get('orderno');
        } else {
            echo "orderno: Order Not found, http://dev.monogramonline.com/getShopifyorderbyordernumber?orderno=21123011052851";
        }
    }

    public function pushPurchaseToOmsForSingle($orderInfos, Request $request)
    {
        $helper = new Helper;

        foreach ($orderInfos as $orderIds) {
//            try {
            foreach ($orderIds as $orderId) {

                if(!isset($orderId['billing_address'])) {
                    $billingAddress = $orderId['shipping_address'];
                } else {
                    $billingAddress = $orderId['billing_address'];
                }

                if(isset($orderId['discount_codes'])) {
                    $discounts = $orderId['discount_codes'];
                } else {
                    $discounts = '';
                }

                $customerDetails = $orderId['customer'];

                $purchaseData['Bill-Address1'] = $billingAddress['address1'];
                $purchaseData['Bill-Address2'] = $billingAddress['address2'];
                $purchaseData['Bill-City'] = $billingAddress['city'];
                $purchaseData['Bill-Company'] = '';
                $purchaseData['Bill-Country'] = $billingAddress['country'];
                $purchaseData['Bill-Email'] = $customerDetails['email'];
                $purchaseData['Bill-Firstname'] = $billingAddress['first_name'];
                $purchaseData['Bill-Lastname'] = $billingAddress['last_name'];
                $purchaseData['Bill-Name'] = $billingAddress['first_name'] . " " . $billingAddress['last_name'];
                $purchaseData['Bill-Phone'] = $billingAddress['phone'];
                $purchaseData['Bill-State'] = $billingAddress['phone'];
                $purchaseData['Bill-Zip'] = $billingAddress['phone'];
                $purchaseData['Bill-maillist'] = 'no';
                $purchaseData['Card-Expiry'] = 'xx/xxxx';
                $purchaseData['Card-Name'] = 'PayPal';
                $purchaseData['Comments'] = $orderId['note'];#
                $purchaseData['Coupon-Description'] = (isset($discounts[0]['type'])) ? $discounts[0]['type'] : ""; //$discounts['type'];
                $purchaseData['Coupon-Id'] = (isset($discounts[0]['code'])) ? $discounts[0]['code'] : "";//$discounts['code'];
                $purchaseData['Coupon-Value'] = (isset($discounts[0]['amount'])) ? $discounts[0]['amount'] : 0; //$discounts['amount'];
                $purchaseData['Date'] = $orderId['created_at'];
                $purchaseData['ID'] = "52053153-" . $orderId['id'];
                $purchaseData['Purchase-Order'] = $orderId['order_number']; // This is temp for 6p, in 5p not required. we will check later.
                $purchaseData['IP'] = $orderId['browser_ip'];


                $shippingMethod = (isset($orderId['shipping_lines'][0]['code'])) ? $orderId['shipping_lines'][0]['code'] : "";

                $index = 1;
                $orderItems = $orderId['line_items'];
                foreach ($orderItems as $item) {

                    if(empty($item['sku'])) {
                        continue;
                    }
                    $purchaseData['Item-Code-' . $index] = $item['sku'];
                    $purchaseData['Item-Description-' . $index] = $item['name'];
                    $purchaseData['Item-Id-' . $index] = $item['id'];
                    $purchaseData['Item-Quantity-' . $index] = $item['quantity'];
                    $purchaseData['Item-Taxable-' . $index] = ($item['taxable']) ? 'Yes' : 'No';
                    $purchaseData['Item-Unit-Price-' . $index] = $item['price'];
                    $purchaseData['Item-Url-' . $index] = "https://monogramonline.myshopify.com/products/" . preg_replace('/\W+/',
                            '-', strtolower($item['name']));
                    $purchaseData['Item-Thumb-' . $index] = "<img border=0 width=70 height=70 src=" . $this->getImaeUrl($item['product_id']) . ">";
                    $itemOptions = $item['properties'];

                    // Start Code for get image from PDF file
                    if(count($itemOptions) > 0) {
                        foreach ($itemOptions as $value) {
                            ########## Add Sure3d url for Download image ##################
                            if(isset($value['name'])) {
                                if($value['name'] == "_pdf") {
                                    $purchaseData['Item-Option-' . $index . '-' . trim(str_replace(":", "",
                                        "Custom_EPS_download_link"))] = $helper->getUrlWithoutParaMeter($value['value']);
                                }
                            }
                            ########## Add Sure3d url for Download image ##################
                            $keyName = trim(ucwords(strtolower($value['name'])));

                            ##### Don't save following key in item option #####
                            if($helper->isKeyExist($item['sku'], $value['name'], $value['value'])) {
                                continue;
                            }
                            ##### Don't save following key in item option #####
                            if($value['name'] == "Preview") {
                                $purchaseData['Item-Option-' . $index . '-' . trim(str_replace(":", "", $keyName))] = $helper->getUrlWithoutParaMeter($value['value']);
                            } elseif($value['name'] == '_zakekeZip') {
                                $purchaseData['Item-Option-' . $index . '-' . trim(str_replace(":", "", $keyName))] = $value['value'];
                            } else {
//                                    Log::info("---------------------------------------------------------------------------------");
                                $purchaseData['Item-Option-' . $index . '-' . trim(str_replace(":", "", $keyName))] = $helper->optionsValuesFilter($value['value']);
                            }
                        }
                    }
                    $index++;
                }

                $purchaseData['Item-Count'] = ($index - 1);
                $purchaseData['Numeric-Time'] = strtotime($orderId['created_at']);

                #What to do for paypal
                $purchaseData['PayPal-Address-Status'] = 'Confirmed';
                $purchaseData['PayPal-Auth'] = '8F4701569X6000947';
                $purchaseData['PayPal-Merchant-Email'] = 'pablo@dealtowin.com';
                $purchaseData['PayPal-Payer-Status'] = 'Unverified';
                $purchaseData['PayPal-TxID'] = '75692712YB5948433';
                #-------------------------------------------------

                $shippingAddress = $orderId['shipping_address'];
                $purchaseData['Ship-Address1'] = $shippingAddress['address1'];
                $purchaseData['Ship-Address2'] = $shippingAddress['address2'];
                $purchaseData['Ship-City'] = $shippingAddress['city'];
                $purchaseData['Ship-Company'] = '';
                $purchaseData['Ship-Country'] = $shippingAddress['country'];
                $purchaseData['Ship-Firstname'] = $shippingAddress['first_name'];
                $purchaseData['Ship-Lastname'] = $shippingAddress['last_name'];
                $purchaseData['Ship-Name'] = $shippingAddress['first_name'] . " " . $shippingAddress['last_name'];
                $purchaseData['Ship-Phone'] = $shippingAddress['phone'];
                $purchaseData['Ship-State'] = $shippingAddress['province'];
                $purchaseData['Ship-Zip'] = $shippingAddress['zip'];
                $purchaseData['Shipping'] = (isset($orderId['shipping_lines'][0]['code'])) ? $orderId['shipping_lines'][0]['code'] : "";
                $purchaseData['Shipping-Charge'] = (isset($orderId['shipping_lines'][0]['code'])) ? $orderId['shipping_lines'][0]['price'] : 0; #$orderObject->getShippingAmount(); # Which Shipping Charge
                $purchaseData['Space-Id'] = '';
                $purchaseData['Store-Id'] = '52053153'; //$storeID
                $purchaseData['Store-Name'] = 'www.monogramonline.myshopify.com'; // http://dev.monogramonline.com/stores/14/edit?
                $purchaseData['Tax-Charge'] = $orderId['total_tax'];
                $purchaseData['Total'] = $orderId['total_price'];

                $request->merge($purchaseData);
                logger('Prepared the data for order : '. $orderId['id']);
                return $this->hookNewForSingle($request);

//                    dd($data);

//
//                    $url = "http://" . $this->domain . "/hook";
//                    $response = $this->curlPost($url, $purchaseData);
//                    $result = json_decode($response, true);
//                    dd($result);
//
//                    $json = json_decode($result, TRUE);
//                    Log::info("------------ Insert status----------------  " . $orderId['id'] . " created_at= " . $orderId['created_at']);
//                    Log::info($result['message']);
            }
//            } catch (Exception $e) {
//                Log::info('Shopify Order push error = (' . $e->getMessage() . ') sent');
//            }

        }
    }


    public function hookNewForSingle(Request $request)
    {
        set_time_limit(0);
        $order_id = $request->get('ID');

        $previous_order = Order::with('items')->where('order_id', $order_id)->where('is_deleted',
            '0')->orderBy('created_at', 'DESC')->first();

        if($previous_order) {
            Log::info('Hook: Duplicate order can not inserted (status) = ' . $order_id);
            return response()->json([
                'error' => true,
                'message' => "Order in DB, status can not in process, data can't be inserted",
            ], 200);

            $batched = $previous_order->items->filter(function ($item) {
                return $item->batch_number != '0';
            })->count();



            if($batched > 0) {
                Log::info('Hook: Duplicate order not inserted (batched) ' . $order_id);
                return response()->json([
                    'error' => true,
                    'message' => "Batch exists data can't be inserted",
                ], 200);
            } else {
                if($previous_order->order_status != 4) {
                    Log::info('Hook: Duplicate order not inserted (status)' . $order_id);
                    return response()->json([
                        'error' => true,
                        'message' => "Order in DB, status not in process, data can't be inserted",
                    ], 200);
                } else {
                    Order::where('order_id', $order_id)->update(['is_deleted' => 1]);
                    Item::where('order_id', $order_id)->update(['is_deleted' => 1]);
                    Customer::where('order_id', $order_id)->update(['is_deleted' => 1]);
                }
            }
        }
        $this->jdbg(__LINE__, "----------------Start Hook Call for order = ".$request->get('ID')."--------------------");
        try {
            $exploded = explode("-", $order_id);
            if(isset($exploded[2])) {
                $short_order = $exploded[2];
            } else {
                $short_order = $exploded[1];
            }
        } catch (Exception $e) {
            Log::error('Undefined offset when trying to create short order. Order ' . $order_id);
            Log::error($request);
            if(strlen($order_id) < 1) {
                exit('no order');
            } else {
                $short_order = $order_id;
            }
        }

        // -------------- Customers table data insertion started ----------------------//
        $customer = new Customer();
        $customer->order_id = $request->get('ID');
        $customer->ship_full_name = str_replace('&', '+', $request->get('Ship-Name'));
        $customer->ship_first_name = $request->get('Ship-Firstname');
        $customer->ship_last_name = $request->get('Ship-Lastname');
        $customer->ship_company_name = $request->get('Ship-Company');
        $customer->ship_address_1 = $request->get('Ship-Address1');
        $customer->ship_address_2 = $request->get('Ship-Address2');
        $customer->ship_city = $request->get('Ship-City');
        $customer->ship_state = Helper::stateAbbreviation($request->get('Ship-State'));
        $customer->ship_zip = $request->get('Ship-Zip');
        $customer->ship_country = $request->get('Ship-Country');
        $customer->ship_phone = $request->get('Ship-Phone');
        $customer->ship_email = $request->get('Ship-Email');
        $customer->shipping = $request->get('Shipping', "N/A");
        $customer->bill_full_name = $request->get('Bill-Name');
        $customer->bill_first_name = $request->get('Bill-Firstname');
        $customer->bill_last_name = $request->get('Bill-Lastname');
        $customer->bill_company_name = $request->get('Bill-Company');
        $customer->bill_address_1 = $request->get('Bill-Address1');
        $customer->bill_address_2 = $request->get('Bill-Address2');
        $customer->bill_city = $request->get('Bill-City');
        $customer->bill_state = $request->get('Bill-State');
        $customer->bill_zip = $request->get('Bill-Zip');
        $customer->bill_country = $request->get('Bill-Country');
        $customer->bill_phone = $request->get('Bill-Phone');
        $customer->bill_email = $request->get('Bill-Email');
        $customer->bill_mailing_list = $request->get('Bill-maillist');
        $customer->save();

        $order = new Order();
        $order->order_id = $request->get('ID');
        try {
            $order->customer_id = $customer->id;
        } catch (Exception $exception) {
            Log::error('Failed to insert customer id in hook');
        }
        $purchase_order = null;
        if($request->has('Purchase-Order')) {
            $purchase_order = $request->get('Purchase-Order');
        }
        $order->short_order = $short_order;
        $order->purchase_order = $purchase_order;
        $order->item_count = $request->get('Item-Count');
        $order->coupon_description = $request->get('Coupon-Description');
        $order->coupon_id = $request->get('Promotions-Code');
        $order->coupon_value = abs($request->get('Promotions-Value'));
        $order->promotion_id = $request->get('Coupon-Id');
        $order->promotion_value = abs($request->get('Coupon-Value'));
        $order->shipping_charge = $request->get('Shipping-Charge');
        $order->tax_charge = $request->get('Tax-Charge');
        $order->total = $request->get('Total');
        $order->card_name = $request->get('Card-Name');
        $order->card_expiry = $request->get('Card-Expiry');
        $order->order_comments = $request->get('Comments');
        $order->order_date = date('Y-m-d H:i:s', strtotime($request->get('Date')));

        $order->order_numeric_time = ($request->get('Numeric-Time'));
        $order->order_ip = $request->get('IP');
        $order->paypal_merchant_email = $request->get('PayPal-Merchant-Email', '');
        $order->paypal_txid = $request->get('PayPal-TxID', '');
        $order->space_id = $request->get('Space-Id');
        $order->store_id = $request->get('Store-Id');
        $order->store_name = $request->get('Store-Name');
        $order->order_status = 4;

        if(empty($request->get('shipping')) && ($request->get('Store-Id') == "52053153")) {
            Log::info('xShipping77 = : ' . $request->get('shipping'));
            $order->carrier = "US";
            $order->method = "FIRST_CLASS";
        }
        Log::info('Inserted purchase order ' . $purchase_order);
        $order->save();  # Save Later
        try {
            $order_5p = $order->id;  # Save Later
        } catch (Exception $exception) {
            $order_5p = '0';
            Log::error('Failed to get 5p order id in hook');
        }

        $upsell = array();
        $upsell_price = 0;

        for ($item_count_index = 1; $item_count_index <= $request->get('Item-Count'); $item_count_index++) {
            $ItemOption = [];
            $pdfUrl = "";

            foreach ($request->all() as $key => $value) {
                if($item_count_index < 10) {
                    $len = 14;
                } else {
                    $len = 15;
                }

                if("Item-Option-" . $item_count_index . "-" == substr($key, 0, $len)) {
                    if(strpos($key, 'special_offer_') !== false) {
                        $upsell[substr($key, $len)] = $value;
                        if(strpos($value, 'Yes') !== false) {
                            Log::info('YES UPSELL ITEM ' . $value);
                        }
                        Order::note(substr($key, $len) . ' - ' . $value, $order->id, $order->order_id);

                    } else {
                        //$k = str_replace(['Choose ', 'Select '], '', substr($key, $len));

                        if (strpos(str_replace([' ', ','], '', strtolower($value)), 'nothankyou') === FALSE) {
                            $ItemOption [preg_replace('/[\x00-\x1F\x7F-\xFF\xA0]/ u ', '', substr($key, $len))] =
                                preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
                                    return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
                                }, str_replace(["\u00a0", "\u0081", "\u0091"], '', $value));
                        } else {
                            Log::info('Deleted option: ' . $value);
                        }
                    }

                    if (strpos($key, 'Custom_EPS_download_link') !== false) {
                        logger("Custom_EPS_download_link", [$value]);
                        $pdfUrl = $value;
                    } elseif (strpos($key, 'zakekezip') !== false) {
                        logger("zakekezip link", [$value]);
                        $pdfUrl = $this->_processZakekeZip($value);
                    }

                }
            }

            logger('final pdf file url', [$pdfUrl]);

            $matches = [];

            preg_match("~.*src\s*=\s*(\"|\'|)?(.*)\s?\\1.*~im", $request->get('Item-Thumb-' . $item_count_index),
                $matches);

            $manufacture_id = Product::where('product_model',
                $request->get('Item-Code-' . $item_count_index))->first()->manufacture_id ?? null;
            $item = new Item();
            $item->order_5p = $order_5p;
            $item->order_id = $request->get('ID');
            $item->store_id = $order->store_id;
            $item->manufacture_id = $manufacture_id;
            $item->item_code = $request->get('Item-Code-' . $item_count_index);
            $item->item_description = $request->get('Item-Description-' . $item_count_index);
            $item->item_id = $request->get('Item-Id-' . $item_count_index);
            $item->item_quantity = $request->get('Item-Quantity-' . $item_count_index);
            $item->item_unit_price = $request->get('Item-Unit-Price-' . $item_count_index);
            $item->item_url = $request->get('Item-Url-' . $item_count_index);
            $item->item_taxable = $request->get('Item-Taxable-' . $item_count_index);
            $item->data_parse_type = 'hook';
            $item->child_sku = Helper::getChildSku($item);
            $item->save();  # Save Later

            $item_thumb =  'https://' . $this->domain . '/assets/images/no_image.jpg';
            if(!$pdfUrl){
                Log::info('No PDF URL found for item : '. $item->item_id);
            } else {
                $context = stream_context_create([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ],
                ]);
                $file_contents = file_get_contents($pdfUrl, false, $context);

                if (!$file_contents) {
                    Log::error("Failed to download the file for item : ". $item->item_id);
                } else {
                    $name = $item->batch_number != null ? $item->batch_number : $item->item_id;
                    $destination = $this->archivePath . $name . '.pdf';
                    logger(['$destination' => $destination]);
                    file_put_contents($destination, $file_contents);

                    // Check if the PDF file exists
                    if (!file_exists($destination)) {
                        Log::error('PDF file not found for item : '. $item->item_id);
                    } else {

                        $helper = new Helper();
                        $jpgFile = $helper->pdfToJPGWIthProfile($destination);
                        if($jpgFile){
                            $savedFilePath = $jpgFile;
                            Log::info('3. PDF converted to JPG successfully for item with jpg: '. $jpgFile);
                        }
                        $fileName = basename($savedFilePath);
//                        $jpgFile = str_replace('.pdf', '', $destination);

                        // Use ImageMagick to convert the PDF to a JPG
//                        exec("pdftoppm -jpeg $destination $jpgFile -singlefile");
//                        $jpgFile = $jpgFile . '.jpg';

                        // Check if the JPG file was created successfully
                        if (!file_exists($this->archivePath . $fileName)) {
                            Log::error('Failed to convert PDF to JPG for item : '. $item->item_id);
                        } else {
                            $jpgFile = basename($jpgFile);
                            $savedFile = $this->remotArchiveUrl . $jpgFile;
                            $fileWithPath = $this->archivePath . $jpgFile;

                            $thumb = '/assets/images/template_thumbs/' . $item->order_id . "-" . $item->id . '.jpg';
                            ImageHelper::createThumb($fileWithPath, 0, base_path() . '/public_html' . $thumb, 350);
                            $item_thumb = $this->full_domain . $thumb;

                            if (!file_exists($fileWithPath)) {
                                Log::info('Failed to create thumb for item : '. $item->item_id);
                            } else {
                                $ItemOption['Internal_Zakeke_Fetch'] = Carbon::now()->toDateTimeString();
                                $ItemOption['Custom_EPS_download_link'] = $savedFile;
                            }
                        }
                    }
                }
            }

            $item->item_option = json_encode($ItemOption);
            $item->item_thumb = $item_thumb ;
            $item->save();

            try {
                $item_id = $item->id;
            } catch (Exception $exception) {
                $item_id = null;
                Log::error('Failed to get item id in hook');
            }

            if (isset($ItemOption['Custom_EPS_download_link']) &&
                (strpos(strtolower($item->item_description), 'photo') ||
                    Option::where('child_sku', $item->child_sku)->first()->sure3d == '1')) {
                $item->sure3d = html_entity_decode($ItemOption['Custom_EPS_download_link']);
                $item->save();  # Save Later
            }


            if(count($upsell) > 0) {
                $upsell_price = $this->upsellItems($upsell, $order, $item);
                $item->item_unit_price = $item->item_unit_price - $upsell_price;
            }

            $item->save();  # Save Later

            if($item->item_option == '[]' || $item->item_option == '0') {
                $data = [];
                $file = "/var/www/5p_oms/BypassOption.json";
                if(file_exists($file)) {
                    $data = json_decode(file_get_contents($file), true);
                }

                $bypass = false;

                if(isset($data[$item->child_sku])) {
                    if($data[$item->child_sku]) {
                        $bypass = true;
                    }
                }

                if($bypass) {
                    $order->order_status = 15;
                    $order->save();  # Save Later
                }
            }

            // -------------- Items table data insertion ended ---------------------- //

            $product = Product::where('product_model', $item->item_code)->first();

            if(!$product) {
                $product = new Product();
                $product->product_model = $item->item_code;
                $product->manufacture_id = 1;
            }

            if($product->id_catalog == null || $item->store_id == '52053152') {
                $product->id_catalog = $item->item_id;
            }

            if(isset($matches[2])) {
                if(!empty($matches[2])){
                    $product->product_thumb = trim($matches[2], ">");
                }
            }
            $product->product_url = $item->item_url;
            $product->product_name = $item->item_description;
            $product->product_price = $item->item_unit_price;
            $product->is_taxable = ($item->item_taxable == 'Yes' ? 1 : 0);
            $product->save();  # Save Later
        }
        try {
            $isVerified = Shipper::isAddressVerified($customer);
        } catch (Exception $exception) {
            $isVerified = 0;
        }

        if($isVerified) {
            $customer->is_address_verified = 1;
        } else {
            $customer->is_address_verified = 0;
            $order->order_status = 11;
            $order->save();  # Save Later
        }

        $customer->save();

        $this->jdbg(__LINE__, "----------------End Hook Called for order ".$request->get('ID')."--------------------");

        return response()->json([
            'error' => false,
            'message' => 'data inserted',
        ], 200);

//        } catch (Exception $e) {
//            Notification::orderFailure($order_id);
//            Log::error('Hook: ' . $e->getMessage());
//
//            return response()->json([
//                'error' => true,
//                'message' => 'error',
//            ], 200);
//
//        }
    }

    public function bulkChangeStatus(Request $request)
    {
        dd($request->get('order'), $request->all());

        $myRequesrs['order'] = [
            895009,
            895005,
            895003,
            895000,
            876534,
            876248,
            876246,
            876244,
            875876
        ];
        $myRequesrs['new_status'] = 8;
        $myRequesrs['status_note'] = "Bulk manual cancel";

        foreach ($myRequesrs['order'] as $orderId) {
            $order = Order::with('items')->where('id', $orderId)->first();
            if($myRequesrs['new_status'] == 8) {
                $shipped_items = false;
                foreach ($order->items as $item) {
                    if($item->item_status != 'shipped') {
                        if($item->batch_number != '0') {

                            $batch_number = $item->batch_number;

                            $item->batch_number = '0';
                            $item->save();

                            Order::note("Item $item->id Balk Cancelled and removed from Batch $batch_number",
                                $order->id, $order->order_id);
                            Batch::note($batch_number, '', '4', "Item $item->id Balk Cancelled and removed from Batch");
                            Batch::isFinished($batch_number);
                        }

                        if($item->item_status == 'wap') {
                            Wap::removeItem($item->id, $item->order_5p);
                        }

                        $item->item_status = 6;
                        $item->save();
                    } else {
                        $shipped_items = true;
                    }
                }

                if($shipped_items == false) {
                    $order->order_status = 8;
                    $order->save();
                    Order::note('CS: Balk Order Cancelled: ' . $myRequesrs['status_note'], $order->id);
                } else {
                    $order->order_status = 6;
                    $order->save();
                }
            }
        }
    }

    public function imageMirror($image, $thumb)
    {

        $imagePath = '/media/RDrive/archive/'. $image;

        if (!file_exists($imagePath)) {
            return redirect()->back()->withErrors('Image not found');
        }
        $thumbPath = '/var/www/5p_oms/public_html/assets/images/template_thumbs/'. $thumb;

        if (!file_exists($thumbPath)) {
            $thumbPath =  '/media/RDrive/archive/'. $thumb;
            if (!file_exists($thumbPath)) {
                return redirect()->back()->withErrors('Thumb image not found');
            }
        }


        // Specify the API endpoint
        $apiEndpoint = 'https://7papi.monogramonline.com/make-image-mirror';

        // Specify the parameters you want to send
        $params = [
            'image' => $imagePath,
            'thumb' => $thumbPath,
            // add more parameters as needed
        ];

        // Initialize a Guzzle client
        $client = new Client();

        // Make a GET request to the API with the specified parameters
        $response = $client->request('GET', $apiEndpoint, [
            'query' => $params,
        ]);

        // Get the response body as a string
        $responseBody = $response->getBody()->getContents();

        // Parse the response if it is in JSON format
        $data = json_decode($responseBody, true);

        if($data['success'] == true) {
            return redirect()->back()->with('success', 'Image and thumb mirrored successfully');
        } else {
            return redirect()->back()->withErrors('Image not mirrored');
        }
    }

    public function imageRotate($image, $thumb)
    {

        $image = '/media/RDrive/archive/'. $image;

        if (!file_exists($image)) {
            return redirect()->back()->withErrors('Image not found');
        }
        $thumb = '/var/www/5p_oms/public_html/assets/images/template_thumbs/'. $thumb;

        if (!file_exists($thumb)) {
            return redirect()->back()->withErrors('Thumb image not found');
        }

        // Specify the API endpoint
        $apiEndpoint = 'https://7papi.monogramonline.com/make-image-rotate';

        // Specify the parameters you want to send
        $params = [
            'image' => $image,
            'thumb' => $thumb,
            // add more parameters as needed
        ];

        // Initialize a Guzzle client
        $client = new Client();

        // Make a GET request to the API with the specified parameters
        $response = $client->request('GET', $apiEndpoint, [
            'query' => $params,
        ]);

        // Get the response body as a string
        $responseBody = $response->getBody()->getContents();

        // Parse the response if it is in JSON format
        $data = json_decode($responseBody, true);

        if($data['success'] == true) {
            return redirect()->back()->with('success', 'Image and thumb rotate 90 degree successfully');
        } else {
            return redirect()->back()->withErrors('Image not rotated');
        }
    }

    public function imageResize(Request $request)
    {
        $image = '/media/RDrive/archive/'. $request->get('image');

        if (!file_exists($image)) {
            return redirect()->back()->withErrors('Image not found');
        }

        // Specify the API endpoint
        $apiEndpoint = 'https://7papi.monogramonline.com/make-image-resize';

        // Specify the parameters you want to send
        $params = [
            'image' => $image,
            'height' => $request->get('height'),
            'width' => $request->get('width'),
            'dpi' => $request->get('dpi'),
            // add more parameters as needed
        ];

//        Image Size 39 X 46.50 (Inch)
//        Image DPI 96

        // Initialize a Guzzle client
        $client = new Client();

        // Make a GET request to the API with the specified parameters
        $response = $client->request('GET', $apiEndpoint, [
            'query' => $params,
        ]);

        // Get the response body as a string
        $responseBody = $response->getBody()->getContents();

        // Parse the response if it is in JSON format
        $data = json_decode($responseBody, true);

        if($data['success'] == true) {
            return redirect()->back()->with('success', 'Image updated successfully');
        } else {
            return redirect()->back()->withErrors('Image not resized');
        }
    }
}
