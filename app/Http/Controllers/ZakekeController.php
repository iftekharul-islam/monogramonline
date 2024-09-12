<?php

namespace App\Http\Controllers;

use App\Batch;
use App\BatchNote;
use App\Note;
use App\Option;
use App\Order;
use App\Section;
use App\StoreItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Market\Dropship;
use Market\ShipStationImport;
use Monogram\Batching;
use Monogram\CSV;
use Monogram\Helper;
use App\Http\Controllers\ProductionController;

class ZakekeController extends Controller
{
    const SHIP_STATION_API_URL = "https://ssapi.shipstation.com/";
    const SHIP_STATION_API_KEY = "8f6fd3ba674246bea607af316e4cd311";
    const SHIP_STATION_API_SECRET = "12554651d87449c5acca216568a5d4e6";
    private $domain = "order.monogramonline.com";

    public static function hasSure3D(string $sku, Request $request)
    {

        parse_str("search_for_first=$sku&contains_first=in&search_in_first=child_sku&search_for_second=&contains_second=in&search_in_second=&search_for_third=&contains_third=in&search_in_third=&search_for_fourth=&contains_fourth=in&search_in_fourth=&active=0&sku_status=&batch_route_id=&sure3d=",
            $dt);
        $request->merge($dt);

        $options = Option::with('product', 'route.template', 'inventoryunit_relation.inventory', 'design')
            ->leftjoin('inventory_unit', 'inventory_unit.child_sku', '=', 'parameter_options.child_sku')
            ->searchIn($request->get('search_for_first'), $request->get('contains_first'),
                $request->get('search_in_first'), $request->get('stockno'))
            ->searchIn($request->get('search_for_second'), $request->get('contains_second'),
                $request->get('search_in_second'), $request->get('stockno'))
            ->searchIn($request->get('search_for_third'), $request->get('contains_third'),
                $request->get('search_in_third'), $request->get('stockno'))
            ->searchIn($request->get('search_for_fourth'), $request->get('contains_fourth'),
                $request->get('search_in_fourth'), $request->get('stockno'))
            ->searchRoute($request->get('batch_route_id'))
            ->searchActive($request->get('active'))
            ->searchStatus($request->get('sku_status'))
            ->searchSure3d($request->get('sure3d'))
            ->selectRaw('parameter_options.*, inventory_unit.stock_no_unique')
            ->groupBy('parameter_options.child_sku')
            ->orderBy('parameter_options.parent_sku', 'ASC')
            ->first();

        return (bool)$options->sure3d;
    }

    public static function getSkuPrice(string $sku, Request $request)
    {

        parse_str("search_for_first=$sku&contains_first=in&search_in_first=child_sku&search_for_second=&contains_second=in&search_in_second=&search_for_third=&contains_third=in&search_in_third=&search_for_fourth=&contains_fourth=in&search_in_fourth=&active=0&sku_status=&batch_route_id=&sure3d=",
            $dt);
        $request->merge($dt);

        $options = Option::with('product', 'route.template', 'inventoryunit_relation.inventory', 'design')
            ->leftjoin('inventory_unit', 'inventory_unit.child_sku', '=', 'parameter_options.child_sku')
            ->searchIn($request->get('search_for_first'), $request->get('contains_first'),
                $request->get('search_in_first'), $request->get('stockno'))
            ->searchIn($request->get('search_for_second'), $request->get('contains_second'),
                $request->get('search_in_second'), $request->get('stockno'))
            ->searchIn($request->get('search_for_third'), $request->get('contains_third'),
                $request->get('search_in_third'), $request->get('stockno'))
            ->searchIn($request->get('search_for_fourth'), $request->get('contains_fourth'),
                $request->get('search_in_fourth'), $request->get('stockno'))
            ->searchRoute($request->get('batch_route_id'))
            ->searchActive($request->get('active'))
            ->searchStatus($request->get('sku_status'))
            ->searchSure3d($request->get('sure3d'))
            ->selectRaw('parameter_options.*, inventory_unit.stock_no_unique')
            ->groupBy('parameter_options.child_sku')
            ->orderBy('parameter_options.parent_sku', 'ASC')
            ->first();

        return $options->product->product_price;
    }

    public static function getShipStationCarriers(): array
    {

        $tag = "41195"; // Personalized;
        $username = self::SHIP_STATION_API_KEY;
        $password = self::SHIP_STATION_API_SECRET;


        $curl = curl_init();


        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://ssapi.shipstation.com/carriers",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_USERPWD => $username . ":" . $password
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $data = json_decode($response, true);

        return $data ?? [];
    }

    public static function setOrderAsImportedShipStation($orderId)
    {
        $curl = curl_init();

        $dt = ZakekeController::getShipStationOrders();

        /*
         * Processes/get the real real order ID from ship station
         */
        foreach ($dt['orders'] as $theOrder) {
            if($theOrder['orderNumber'] === $orderId) {
                $orderId = $theOrder['orderId'];
                break;
            }
        }


        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://ssapi.shipstation.com/orders/addtag",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode(
                [
                    "orderId" => (int)$orderId,
                    "tagId" => 52335
                ]
            ),
            CURLOPT_USERPWD => self::SHIP_STATION_API_KEY . ":" . self::SHIP_STATION_API_SECRET,
            CURLOPT_HTTPHEADER => array(
                "Host: ssapi.shipstation.com",
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        //  dd($response);
    }

    public static function getShipStationOrders(): array
    {

        $tag = "41195"; // Personalized;
        $username = self::SHIP_STATION_API_KEY;
        $password = self::SHIP_STATION_API_SECRET;


        $curl = curl_init();


        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://ssapi.shipstation.com/orders/listbytag?orderStatus=awaiting_shipment&tagId=$tag&page=1&pageSize=500&sortBy=233671",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_USERPWD => $username . ":" . $password
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $data = json_decode($response, true);

        return $data ?? [];
    }

    public function test1()
    {
        $order = Order::where("id", "1171393")
            ->with("items")
            ->get();

        $notes = BatchNote::where("batch_number", "637537")->get();

        foreach ($notes as $note) {
            if(stripos($note->note, "(automatically from link)") !== false) {

            }
        }

        dd($order);
    }

    public function test2(Request $request)
    {

        $order = Order::where("short_order", 100071)->first();

        $this->setOrderAsShippedShipStation($order->short_order, "9405509699939108473525");
    }

    public static function setOrderAsShippedShipStation($orderId, $trackingNumber)
    {
        $curl = curl_init();

        $dt = ZakekeController::getShipStationOrders();

        /*
         * Processes/get the real real order ID from ship station
         */
        foreach ($dt['orders'] as $theOrder) {
            if($theOrder['orderNumber'] === $orderId) {
                $orderId = $theOrder['orderId'];
                break;
            }
        }


        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://ssapi.shipstation.com/orders/markasshipped",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode(
                [
                    "orderId" => $orderId,
                    "carrierCode" => "usps",
                    "trackingNumber" => $trackingNumber,
                    "notifyCustomer" => true,
                    "notifySalesChannel" => true
                ]
            ),
            CURLOPT_USERPWD => self::SHIP_STATION_API_KEY . ":" . self::SHIP_STATION_API_SECRET,
            CURLOPT_HTTPHEADER => array(
                "Host: ssapi.shipstation.com",
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        // dd($response);
    }

    /*
     * This is being used by the cron job, to automatically
     * fetch the graphics for PWS & Axe n Co
     */

    public function customBatch()
    {

        $order = \request()->get("order");
        $order = Order::with('items', 'customer', 'store')
            ->where("id", $order)
            ->first();

        if(!$order) {
            return redirect()->back()->withErrors("The order cannot be found or an error happened");
        }

        Batching::auto(0, [$order->store->store_id], 1, $order->id);

        return redirect()->back()->withSuccess('Order has been successfully batched!');
    }

    public function skuInfo()
    {
        $data = self::getInventoryInformation(\request()->get("sku"));

        $sections = Section::where('is_deleted', '0')
            ->get()
            ->pluck('section_name', 'id');

        dd($data->inventoryunit_relation->first()->inventory, $sections->toArray());
        // dd($data->inventoryunit_relation->first()->inventory);
    }

    public static function getInventoryInformation(string $sku)
    {

        $request = \request();
        parse_str("search_for_first=$sku&contains_first=in&search_in_first=child_sku&search_for_second=&contains_second=in&search_in_second=&search_for_third=&contains_third=in&search_in_third=&search_for_fourth=&contains_fourth=in&search_in_fourth=&active=0&sku_status=&batch_route_id=&sure3d=",
            $dt);
        $request->merge($dt);

        $options = Option::with('product', 'route.template', 'inventoryunit_relation.inventory', 'design')
            ->leftjoin('inventory_unit', 'inventory_unit.child_sku', '=', 'parameter_options.child_sku')
            ->searchIn($request->get('search_for_first'), $request->get('contains_first'),
                $request->get('search_in_first'), $request->get('stockno'))
            ->searchIn($request->get('search_for_second'), $request->get('contains_second'),
                $request->get('search_in_second'), $request->get('stockno'))
            ->searchIn($request->get('search_for_third'), $request->get('contains_third'),
                $request->get('search_in_third'), $request->get('stockno'))
            ->searchIn($request->get('search_for_fourth'), $request->get('contains_fourth'),
                $request->get('search_in_fourth'), $request->get('stockno'))
            ->searchRoute($request->get('batch_route_id'))
            ->searchActive($request->get('active'))
            ->searchStatus($request->get('sku_status'))
            ->searchSure3d($request->get('sure3d'))
            ->selectRaw('parameter_options.*, inventory_unit.stock_no_unique')
            ->groupBy('parameter_options.child_sku')
            ->orderBy('parameter_options.parent_sku', 'ASC')
            ->first();

        return $options;
    }

    public function fetchFromZakekeCLI($type, $order)
    {

        $response = null;

        if($type === "axe") {
            $response = shell_exec("zakeke -user 65580 -key zccXIpB1k2J-quu2BBbwuNZVpvussjoWgTJpCS1lYyM. -data " . $order->short_order);
        } else {
            if($type === "pws") {
                $response = shell_exec("zakeke -user 44121 -key 2d91PpFG6QJ0NmXsImWCXSzAMPCiRwMuX6D7DUHSIcM. -data " . $order->short_order);
            }
        }
        return $response;
    }

    public function fetchAll(string $type, GraphicsController $graphicsController)
    {

        logger("Zakeke Fetch All started ...");
        $helper = new Helper();
//
//        if (!Cache::get("ZAKEKE_" . strtoupper($type))) {
//
//            Log::info("Zakeke Status was off for  " . print_r(\request()->all(), true));
//
//            dd(
//                [
//                    "Status" => true,
//                    "Message" => "The cronjob has been turned off, cannot fetch"
//                ]
//            );
//        }

        /*
         * Turned on, can now continue
         */

//        if(strtolower($type) == "axe") {
        $orders = Order::with("items")
            ->where('is_deleted', '0')
            ->storeId("axe-co")
            ->whereIn("order_status", [23]) //23 = other hold, 4 = to be processed
//            ->where('short_order', '2901920865')
//            ->where('short_order', '5144490573987')
            ->get();


//        dd($orders);
//        } else {
//            if(\request()->has("switch_store")) {
//                $orders = Order::with("items")
//                    ->where('is_deleted', '0')
//                    ->storeId("axe-co")
//                    ->whereIn("order_status", [23]) //23 = other hold, 4 = to be processed
//                    ->get();
//            } else {
//                $orders = Order::with("items")
//                    ->where('is_deleted', '0')
//                    ->storeId("Etsy")
//                    ->whereIn("order_status", [23, 4])
//                    ->get();
//            }
//        }

        $filteredNum = count($orders);

        $zakekeData = [];
        foreach ($orders as $order) {
            $temp = (string)$order->short_order;
            $itemOptions = json_decode($order->items->first()->item_option, true);

            if(isset($itemOptions['PWS Zakeke'])) {
                if(strlen($temp) > 5) {
                    $zakekeData['pws'][$order->short_order] = $helper->zakekeOrderByOrderCode($order->short_order,
                        "pws");
                    if(isset($zakekeData['pws'][$order->short_order]['items'])) {
//                        dd(isset($zakekeData['pws'][$order->short_order]['items']));
                        $zakekeGetPdfFiles = $helper->zakekeGetPdfFiles($zakekeData['pws'][$order->short_order]['items']);
                        if(count($zakekeGetPdfFiles['pdfUrls']) > 0) {
                            /** Update Custom_EPS_download_link by Zakeke PDF URL */
                            $this->updateZakekePrintable($order, $zakekeGetPdfFiles);
                        }
                    }
                }
            } else {
                if(strlen($temp) > 5) {
                    $zakekeData['axe'][$order->short_order] = $helper->zakekeOrderByOrderCode($order->short_order,
                        "axe");
                    if(isset($zakekeData['axe'][$order->short_order]['items'])) {
                        $zakekeGetPdfFiles = $helper->zakekeGetPdfFiles($zakekeData['axe'][$order->short_order]['items']);

                        if(count($zakekeGetPdfFiles['pdfUrls']) > 0) {
                            /** Update Custom_EPS_download_link by Zakeke PDF URL */
                            $this->updateZakekePrintable($order, $zakekeGetPdfFiles);
                        }
                    }
                }
            }
        }


        logger("Zakeke Fetch All ended ...");
        dd("1", $orders, $filteredNum, $zakekeData);


//        $filteredNum = $filteredNum - count($before);
//        $zakekeFilters = implode(",", $before);
//
//        $hasGraphic = [];
//        $skipped = [];
//        $willNotUpdate = [];
//
//
//        if($type === "axe") {
//            $response = shell_exec("zakeke -user 65580 -key zccXIpB1k2J-quu2BBbwuNZVpvussjoWgTJpCS1lYyM. -data " . $zakekeFilters);
//        } else {
//            if($type === "pws") {
//                $response = shell_exec("zakeke -user 44121 -key 2d91PpFG6QJ0NmXsImWCXSzAMPCiRwMuX6D7DUHSIcM. -data " . $zakekeFilters);
//            } else {
//                $response = null;
//            }
//        }
//        $data = @json_decode($response, true);
//
//
//        $pdfUrlWithOrderNo = [];
//        foreach ($data as $shoerOrderNo => $links) {
//            set_time_limit(0);
//            foreach ($links['Links'] as $lineNo => $filesWithUrl) {
//                foreach ($filesWithUrl as $fileFormet => $fileUrl) {
//                    if($fileFormet == 'ZIP' && $fileUrl) {
//                        $pdfUrlWithOrderNo[$shoerOrderNo][$lineNo] = $this->_processZakekeZip($fileUrl);
////                        $pdfUrlWithOrderNo[$shoerOrderNo][$lineNo] = "url ".$lineNo;
//                        sleep(30);
//                    }
//                }
//            }
//        }
//
//
//        return response()->json(['Zakeke API seems to be down, try again later!']);
//        if($data === null && json_last_error() !== JSON_ERROR_NONE) {
//            Log::info("Data from zakeke returned null " . print_r(\request()->all(), true));
//            return response()->json(['Zakeke API seems to be down, try again later!']);
//        } else {
//            foreach ($orders as $order) {
//                foreach ($order->items as $index => $item) {
//                    // Make sure the batch is not empty
//                    if($item->batch_number !== "") {
//
//                        if(!isset($pdfUrlWithOrderNo[$order->short_order][$index])) {
//                            continue;
//                        } else {
//                            if(!$pdfUrlWithOrderNo[$order->short_order][$index]) {
//                                $this->addOrderNote($item);
//                                continue;
//                            }
//                            $link = $pdfUrlWithOrderNo[$order->short_order][$index];
//                        }
//                        $options = json_decode($item->item_option, true);
//
//                        $hasGraphic[$order->id] = "https://order.monogramonline.com/orders/details/" . $order->id;
//
//                        $options['Custom_EPS_download_link'] = $link;
//                        $options['Internal_Zakeke_Fetch'] = Carbon::now()->toDateTimeString();
//
//                        $item->item_option = json_encode($options);
//                        $item->save();
//
//                        // Create a new request with parameters
//                        $newRequest = [
//                            'batch_number' => $item->batch_number,
//                            'item_id' => $item->id,
//                            'short_order' => $order->short_order,
//                            'fetch_link_from_zakeke_cli' => true,
//                            'item_index' => $index . '' . isset($item->item_option['PWS Zakeke']),
//                        ];
//
//                        $request = Request::create('/', 'POST');
//                        $request->replace($newRequest);
//
//                        $graphicsController->uploadFileUsingLink($request, true);
//
//
//                        if($order->order_status !== 4) {
//                            $order->order_status = 4;
//                            $order->save();
//                        }
//                    }
//                }
//                // sleep(1);
//            }
//        }


//        Log::info("---------------------------------------");
//        Log::info("          ZAKEKE MASS                  ");
//        Log::info("Successfully fetched " . count($zakekeData) . " out of " . count($orders));
//        Log::info("Total Orders (did not match filter) " . abs($filteredNum));
//        Log::info("Total Graphic Updated" . count($hasGraphic));
//        Log::info("Orders Ids Updated" . print_r($hasGraphic, true));
//        Log::info("Orders that was in array " . implode(",", array_keys($before)));
//        Log::info("Skipped order (already updated) " . implode(",", array_keys($skipped)));
//        Log::info("---------------------------------------");
//
//        return response()->json(
//            [
//                "Status" => true,
//                "Message" => "Successfully fetched " . count($hasGraphic) . " out of " . count($orders),
//                "Total Orders (did not match filter)" => abs($filteredNum),
//                "Total Graphic Updated" => count($hasGraphic),
//                "Orders that was in array" => implode(",", array_values($before)),
//                "Orders Ids Updated" => implode(",", array_keys($hasGraphic)),
//                "Data" => $hasGraphic,
//                "Skipped order (already updated)" => $skipped,
//                "Will not update" => $willNotUpdate
//            ]
//        );
    }

    public function updateZakekePrintable($order, $zakekePdfs)
    {
        $updateOrderStatus = false;
        if(empty($zakekePdfs['pdfUrls'])) {
            return false;
        }
        if($order->item_count != count($zakekePdfs['pdfUrls'])) {
            return false;
        }
        $helper = new Helper();
        $productionController = new ProductionController();

        $i = 0;
        set_time_limit(0);
        foreach ($order->items as $key => $item) {
            logger('the image link from zakeke '. $zakekePdfs['pdfUrls'][$key]);
            $ch = curl_init($zakekePdfs['pdfUrls'][$key]);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

            $headers = curl_exec($ch);
            if($headers) {
                $zakekeFileUrl = $zakekePdfs['pdfUrls'][$key];
                if($item->batch_number != '0') {
                    logger('updateZakekePrintable when batch !0 with file :'. $zakekeFileUrl);

                    $pdfUrl = $helper->savePdfToArchive($item->batch_number, $zakekeFileUrl);
                    logger('pdf url :'. $pdfUrl);
//                    if(!empty($pdfUrl) && $productionController->moveTonextStation([$item->batch_number])) {
                        if(!empty($pdfUrl)) {
                        $ItemOption = json_decode($item->item_option, true);
                        $ItemOption['Custom_EPS_download_link'] = $pdfUrl;
                        $ItemOption['Internal_Zakeke_Fetch'] = Carbon::now()->toDateTimeString();
                        $item->item_thumb = $zakekePdfs['thumbnailUrls'][$key];
                        $item->item_option = json_encode($ItemOption);
                        $item->save();
                        $updateOrderStatus = true;
                        $helper->jdbg($i . " Batch# = " . $item->batch_number . " -   index= " . $key,
                            $zakekeFileUrl);
                    }
                } else {
                    logger('updateZakekePrintable when batch !0 else part with file :'. $zakekeFileUrl);
                    $ItemOption = json_decode($item->item_option, true);
                    $ItemOption['Custom_EPS_download_link'] = $zakekeFileUrl;
                    $ItemOption['Internal_Zakeke_Fetch'] = Carbon::now()->toDateTimeString();
                    $item->item_thumb = $zakekePdfs['thumbnailUrls'][$key];
                    $item->item_option = json_encode($ItemOption);
                    $item->save();
                    $updateOrderStatus = true;
                    $helper->jdbg($i . " No Batch== " . $key, $zakekeFileUrl);
                }
                $batches = Batch::with('items', 'route.stations_list')
                    ->where('batch_number', $item->batch_number)
                    ->get();


                foreach ($batches as $batch) {
                    foreach ($batch->items as $SingleItem) {
                        if($SingleItem->item_id == $item->item_id) {
                            if(empty($SingleItem->tracking_number)) {
                                $SingleItem->tracking_number = null;
                                $SingleItem->item_status = 1;
                                $SingleItem->reached_shipping_station = 0;
                                $SingleItem->save();


                                ###### Save in Batch Table ######
                                //TODO :: need to implement
//                                if(isset($batch->section) && $batch->section != 'Sublimation'){
//                                    logger('section is not sublimation, section is :'. $batch->section->section_name. 'batch number is :'. $batch->batch_number);
//                                    continue;
//                                }
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
            }
            $i++;
        }
        if($order->order_status !== 4 && $updateOrderStatus) {
            $order->order_status = 4;
            $order->save();
        }
    }

    public function _processZakekeZip($fileUrl)
    {
        ##############
        $filename = basename($fileUrl);
        $lastHyphenPosition = strrpos($filename, '-');
        $filenameWithoutExtension = substr($filename, 0, -4); // Remove the ".zip" extension
        $parts = explode('_', $filenameWithoutExtension);
        $designId = implode('_', array_slice($parts, 0, -1));
        ###############

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fileUrl);
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


        $fp = fopen(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $result ['filename'], 'w');
        fwrite($fp, $result['file']);
        fclose($fp);

        system(
            'unzip -o ' . sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename . ' -d ' .
            sys_get_temp_dir() . DIRECTORY_SEPARATOR .
            pathinfo($filename, PATHINFO_FILENAME)
        );

        $tmpDir = scandir(
            sys_get_temp_dir() . DIRECTORY_SEPARATOR .
            pathinfo($filename, PATHINFO_FILENAME), true
        );

        $matches = preg_grep('/^[0-9]+.*pdf$/i', $tmpDir);
        $pdfFile = array_shift($matches);


        $pdfFilePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR .
            pathinfo($filename, PATHINFO_FILENAME) . DIRECTORY_SEPARATOR .
            $pdfFile;

        $extension = pathinfo($pdfFile, PATHINFO_EXTENSION);
        $pdfFile = $designId . '.' . $extension;

        $copyStatus = copy(
            $pdfFilePath,
            public_path() . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'zakeke' .
            DIRECTORY_SEPARATOR . $pdfFile
        );

        if(!$copyStatus) {
            Log::info(" ZAKEKE ZIP Copy Fail " . public_path() . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'zakeke' .
                DIRECTORY_SEPARATOR . $pdfFile);
            return false;
        }

        unlink($pdfFilePath);

        Log::info(" ZAKEKE ZIP DONE" . public_path() . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'zakeke' .
            DIRECTORY_SEPARATOR . $pdfFile);

//        dd($pdfFilePath, public_path() . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'zakeke' .
//            DIRECTORY_SEPARATOR . $pdfFile, $pdfFile, $copyStatus, 'http://' . $this->domain . '/media/zakeke/' . $pdfFile);

        return 'http://' . $this->domain . '/media/zakeke/' . $pdfFile;

    }

    public function require_all_files()
    {
        require("/var/www/order.monogramonline.com/library/LaravelShipStation/ShipStation.php");
    }

    public function shipStationCheckOrder()
    {

        logger("ShipStation Check Order started ...");

        $data = ZakekeController::getShipStationOrders();
//        dd($data['orders'][0]);
//        foreach($data['orders'] as $order) {
//            if($order['orderNumber'] == "3127493307") {
//                dd($order);
//            }
//        }

        if($data !== null && isset($data['orders'])) {
            $csvData = [];
            $line = [
                'order',
                'name',
                'address1',
                'address2',
                'city',
                'state',
                'zip',
                'country',
                'phone',
                'comment',
                'color',
                'sku',
                'child_sku',
                'qty',
                'price',
                'thumbnail',
                'graphic',
                // new entries
                'ship via',
                'Ship By Date',
                'pws_zakeke',
                'Personalization',
                'orderTotal',
                'taxAmount',
                'shippingAmount',
            ];

            $csvData[] = $line;

            /*
             * Loop through the order now
             */

            $helper = new Helper();
            foreach ($data['orders'] as $order) {

                // For testing only
                //  if($order['orderNumber'] !== "2539606068") continue;

                foreach ($order['items'] as $item) {
                    $personalizationValue = null;
                    foreach ($item['options'] as $option) {
                        if ($option['name'] === 'Personalization') {
                            $personalizationValue = $option['value'];
                            break;
                        }
                    }

                    /*
                     * Weird bug with ship station, inserts blank line items
                     */
                    if($item['sku'] == null) {
                        continue;
                    }

                    if(empty($item['sku'])) {
                        continue;
                    }

//                    $helper->jdbg("ShipStation Raw order Data: ", $order);
//                    $helper->jdbg("ShipStation Raw Item Data: ", $item);
                    $zipcode = $order['shipTo']['postalCode'];


                    if(stripos($zipcode, "-") !== false) {
                        $zipcode = explode("-", $zipcode)[0];
                    }

                    $itemInfo = StoreItem::searchStore("axe-co")
                        ->where('is_deleted', '0')
                        ->where("vendor_sku", $item['sku'])
                        ->first();

                    // TODO::deprecated concept
//                    $price = $itemInfo['cost'];
//                    if(!$price || $price == 0) {
//                        $price = $item['price'] ?? 0;
//                    }

                    $price = $item['price'] ?? 0;

                    $shipDate = Dropship::getShipDateFromStarting(Carbon::parse($order['createDate']))->toDateTimeString();

                    $status = false;
                    foreach ($order['tagIds'] as $id) {
                        if($id == '64962') {
                            $status = true;
                        }
                    }

                    if((bool)$status === true) {
                        $line = [
                            $order['orderNumber'],
                            $order['shipTo']['name'],
                            $order['shipTo']['street1'],
                            $order['shipTo']['street2'],
                            $order['shipTo']['city'],
                            $order['shipTo']['state'],
                            (string)$zipcode,
                            $order['shipTo']['country'],
                            $order['shipTo']['phone'] ?? "",
                            "", // $order['customerNotes'] ?? "", // he (Sholomi said remove it
                            "",
                            $item['sku'],
                            $item['sku'],
                            1, //$item["quantity"],  /* Quantity should always be 1, if >= 1, add another line item
                            $price,
                            $item['imageUrl'],
                            $item['imageUrl'],
                            $order['serviceCode'],
                            $shipDate,
                            'true',// PWS ESTY
                            $personalizationValue,
                            $order['orderTotal'],
                            $order['taxAmount'],
                            $order['shippingAmount'],
                        ];
                    } else {
                        $line = [
                            $order['orderNumber'],
                            $order['shipTo']['name'],
                            $order['shipTo']['street1'],
                            $order['shipTo']['street2'],
                            $order['shipTo']['city'],
                            $order['shipTo']['state'],
                            (string)$zipcode,
                            $order['shipTo']['country'],
                            $order['shipTo']['phone'] ?? "",
                            "", // $order['customerNotes'] ?? "", // he (Sholomi said remove it
                            "",
                            $item['sku'],
                            $item['sku'],
                            1, //$item["quantity"],  /* Quantity should always be 1, if >= 1, add another line item
                            $price,
                            $item['imageUrl'],
                            $item['imageUrl'],
                            $order['serviceCode'],
                            $shipDate,
                            false,
                            $personalizationValue,
                            $order['orderTotal'],
                            $order['taxAmount'],
                            $order['shippingAmount'],
                        ];
                    }


                    /* -----------------------------------------------------------------------------------
                     * FYI                                                                               -
                     * In Excel you cannot have leading zeros in numbers, so it will ignore it           -
                     * -----------------------------------------------------------------------------------
                     */


                    if($item["quantity"] > 1) {
                        /*
                         * Duplicate line items until it maxes the quantity
                         */
                        for ($i = 0; $i < $item['quantity']; $i++) {
                            $csvData[] = $line;
                        }
                    } else {
                        $csvData[] = $line;
                    }
                    unset($line);
                    unset($zipcode);
                    unset($status);
                }
            }


            $filename = 'ShipStation_' . "Axe" . '_' . date('ymd_His') . '.' . uniqid() . '.csv';
            $csv = new CSV;

            $path = storage_path() . "/EDI/General/ShipStation/";
            $path = $csv->createFile($csvData, $path, null, $filename, ',');
            $import = new ShipStationImport();
            $import->importCsv($path);

            logger('ShipStation Check Order ended successfully  ...');
            return response()->json(
                [
                    "Status" => true,
                    "Messages" => [
                        "Successfully pushed orders if there were any",
                        $import
                    ],
                    "Order Dump" => $path
                ]
            );
        } else {
            logger('ShipStation Check Order ended with no data ...');
            return response()->json(
                [
                    "Status" => false,
                    "Message" => "No orders were found, or we're being rate-limited by Ship Station"
                ]
            );
        }
    }

    private function addOrderNote($item)
    {
        // Add note history by order id
        $note = new Note();
        $note->note_text = 'Batch ' . $item->batch_number . ' for Item ' . $item->id .
            "Unable to download file from Zakeke, Please take action ";
        $note->order_id = $item->order_id;
        $note->order_5p = $item->order_5p;
        if(auth()->user()) {
            $note->user_id = auth()->user()->id;
        } else {
            $note->user_id = 87;
        }
        $note->save();
    }

}
