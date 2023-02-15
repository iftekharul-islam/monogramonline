<?php

namespace App\Http\Controllers;

use App\Order;
use Illuminate\Http\Request;

class CustomController extends Controller
{
    public function searchOrder(Request $request)
    {
        $search = $request->get("search");

        $order = Order::where("short_order", "=", $search)
            ->orWhere("id", $search)
            ->orWhere("order_id", $search)

            ->first();


        $batch = Batch::where("batch_number", "=", $search)

            ->first();

        if($order) {
            return redirect()->away("https://order.monogramonline.com/orders/details/" . $order->id);
        } else {
            if($batch) {
                return redirect()->away("https://order.monogramonline.com/batches/details/" . $search);
            }
            return redirect()->back()->withErrors(["No order or batch found matching " . $search]);
        }
    }

    public function shipStation()
    {
        $path = \Market\Dropship::getDropShipOrders();

        return response()->download($path)->deleteFileAfterSend(true);
    }

    public function testOrder()
    {
        $order = Order::with("items")
            ->where("id", request()->get("id", "1138117"))
            ->first();

        dd($order);
    }

    public function conversionImage()
    {
        if(!request()->has("link")) {
            dd("I need an image");
        } else {
            $imageLink = request()->get("link");

            $pdfFile = file_get_contents($imageLink);

            $filename  = pathinfo(parse_url($imageLink, PHP_URL_PATH), PATHINFO_FILENAME);


            $path = "/home/jewel/conversions/$filename.pdf";

            file_put_contents("/home/jewel/conversions/$filename.pdf", $pdfFile);

            shell_exec("pdftoppm -jpeg $path $path");
            unlink($path);

            dd("done");
        }
    }

    public function conversionImage2()
    {
        if(!request()->has("link")) {
            dd("I need an image");
        } else {
            $imageLink = request()->get("link");

            $filename  = pathinfo(parse_url($imageLink, PHP_URL_PATH), PATHINFO_FILENAME);


            $path = "/home/jewel/conversions/$filename.pdf-1.jpg";


            $file_out = $path;

            if (file_exists($file_out)) {

                $image_info = getimagesize($file_out);

                //Set the content-type header as appropriate
                header('Content-Type: ' . $image_info['mime']);

                //Set the content-length header
                header('Content-Length: ' . filesize($file_out));

                //Write the image bytes to the client
                readfile($file_out);
            }
            else { // Image file not found

                dd("404 Not Found");

            }

        }
    }

    public function orderStatusHold($id)
    {
        $orders = Order::whereIn("id", explode(",", $id))
            ->get();

        if(count($orders) >= 1) {

            foreach ($orders as $order) {
                $order->order_status = 23;
                $order->save();
            }
            return response()->json(
                [
                    "Status" => true,
                    "Message" => "Successfully set order status to OTHER HOLD",
                ]
            );
        } else {
            return response()->json(
                [
                    "Status" => false,
                    "Message" => "Successfully set order status to OTHER HOLD",
                ]
            );
        }
    }

    public function option_mass()
    {
        $file = "/var/www/order.monogramonline.com/BypassOption.json";

        $data = [];
        if(file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
        }
        foreach (\request()->get("list") as $sku) {
            $data[$sku] = (bool) trim(\request()->get('status'), '');
        }
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));

        //dd(\request()->all());
        return redirect()->back()->with('success', "Bypass Option turned to " .\request()->get("status") . " for a total of " . count(\request()->get("list")) . " child sku(s)!");
    }

    public function zakeke_switch_type($type, $status)
    {
        $file = "/var/www/order.monogramonline.com/BypassOption.json";

        $data = [];
        if(file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
        }
        foreach (\request()->get("list") as $sku) {
            $data[$sku] = (bool) trim(\request()->get('status'), '');
        }
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));

        //dd(\request()->all());
        return redirect()->back()->with('success', "Bypass Option turned to " .\request()->get("status") . " for a total of " . count(\request()->get("list")) . " child sku(s)!");
    }

    public function orderShippingUpdate()
    {
        $order = Order::findOrFail(\request()->get("order_id"));

        $order->carrier = "US";
        $order->method = "FIRST_CLASS";
        $order->save();
    }

    public function documentationShipping()
    {
        $output = array_values(\Market\Dropship::$shippingConversion);
    dd($output);
    }

    public function testMassDelete2()
    {
        $file = "/var/www/order.monogramonline.com/import2.csv";

    $csv = new CSV;
    $data = $csv->intoArray($file, ",");


    /*
     * If have ugly line items
     */
    // Remove csv does not have row entry that tells wich is what. Ex:   Name | Type | ID | etc
    unset($data[0]);

    $ids = [];
    foreach ($data as $what) {
        $ids[] = $what[0];
    }
    $orders = Order::whereIn("short_order", $ids)
        ->get();
    foreach ($orders as $order) {
     //   $order->delete();
       // $order->order_status = 8;
      //  $order->save();
   }

    dd("Deleted a total of " . count($orders) . " orders!");
    }

    public function testMassDelete3()
    {
        $batchNumbers =
        [

        ];


    $orders = Order::whereIn("short_order", $ids)
        ->get();
    foreach ($orders as $order) {
        //   $order->delete();
        // $order->order_status = 8;
        //  $order->save();
    }

    dd("Deleted a total of " . count($orders) . " orders!");
    }

    public function estMassSetOtherHold()
    {

    $file = "/var/www/order.monogramonline.com/import2.csv";

    $csv = new CSV;
    $data = $csv->intoArray($file, ",");


    /*
     * If have ugly line items
     */
    unset($data[0]);

    $ids = [];
    foreach ($data as $what) {
        $ids[] = $what[1];
    }
    $orders = Order::whereIn("short_order", $ids)
        ->get();
//    foreach ($orders as $order) {
//        $order->order_status = 23;
//        $order->save();
//    }

    dd("Deleted a total of " . count($orders) . " orders!");
    }

    public function testMassDelete()
    {

    $file = "/var/www/order.monogramonline.com/import.csv";

    $csv = new CSV;
    $data = $csv->intoArray($file, ",");

    $test = [];

    foreach ($data as $short_order) {
        if($short_order !== null) {
            $test[] = $short_order;
        }
    }

    $orders = Order::whereIn("short_order", $test)
        ->get();


    foreach ($orders as $order) {
        if($short_order !== null) {

            if (isset($order->item)) {
                foreach ($order->items as $item) {
                    if ($item->batch_number == 0) {
                        $order->delete();
                    }
                }
            } else {
                $order->delete();
            }
        }
    }

    dd("DONE");
    }

    public function shipmentCacheAll()
    {
        $cache = Cache::get("SHIPMENT_CACHE");
         dd(in_array(1154274, $cache), $cache);
    }

    public function storeCache()
    {

    $storesNew =  Cache::remember("stores_all", 1, function() {
        return Store::all();
    });

    $data = [];
    $file = "/var/www/order.monogramonline.com/Store.json";
    if(file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
    }


    $total = [];

    foreach ($storesNew as $storeD) {


        if (isset($data[$storeD->store_name]) && $data[$storeD->store_name]['DROPSHIP']) {
            $id = $storeD->store_id;
            $name = $storeD->store_name;




                    $toAdd =  \App\Order::with("items", "customer", "items.shipInfo")
                        ->whereHas("items", function ($query) use ($id) {
                            return $query->where("store_id", $id)
                                ->whereIn("child_sku", Cache::get('SKU_TO_INVENTORY_ID')['ALL'])
                                ->withinDate(Carbon::createFromDate(2021, 11, 10)->toDateString(), Carbon::now()->addMonth(5)->toDateString());
                        })
                        ->where("order_status", "<=", "4")
                        ->whereNotIn("id", Cache::get("SHIPMENT_CACHE"))
                        ->get();

                    if(count($toAdd) != 0) {
                        Cache::forget("stores_items_$id");
                        Cache::add("stores_items_$id", $toAdd, 60 * 24);
                        $total[] = $toAdd;
                    }
        }
    }

    return response()->json(
        [
            "Status" => true,
            "Message" => "Successfully cache data a total of " . count($total)
        ]
    );
    }

    public function batchInfo()
    {
    $batch_number = "610436";
    $batch = Batch::where('batch_number', $batch_number)->first();

      dd($batch->items[0]->item_quantity);
    }

    public function orderLayout()
    {
        $order = Order::where("id", 1132710)
        ->get();

        dd($order);
    }

    public function shipmentCache()
    {
    $file = "/var/www/order.monogramonline.com/Shipment.json";

    $data = [];
    if(file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
    }

    Cache::put("SHIPMENT_CACHE", $data, 60 * 8);
    }

    public function orderLayout2()
    {
            $order = Order::where("id", 1132814)
        ->get();

    dd($order);
    }

    public function testStore()
    {
        $stores = Store::with('store_items')
        ->where('is_deleted', '0')
        ->orderBy('sort_order')
        ->get();

    $companies = Store::$companies;

    dd($stores[0]);
    }

    public function testExport()
    {
            $stores =  Store::all();
    $file = "/var/www/order.monogramonline.com/Store.json";

    $data = [];
    if(file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
    }

    foreach ($stores as $store) {
        if(isset($data[$store->id]) && $data[$store->id]['DROPSHIP']) {
            dd($store);
        }
    }
    }

    public function testStoreInventory()
    {

    $file = storage_path("app/test.json");

    if(!file_exists($file)) {
        file_put_contents($file, json_encode(['test']));
        dd("created");
    }
    dd("existed", file_get_contents($file));
    }

    public function testOrder1()
    {

            // 7898
        //    "store_id" => "yhst-128796189915726"
        //        "store_name" => "monogramonline.com"

    $test = \App\Order::query()->where("short_order", "1047664")->first();
    $test->store_id = "7898";
    $test->store_name = "DevTest";
    $test->save();
    dd($test);
    }

    public function orderTest33()
    {
        $test = \App\Order::where("id", 1168888)
    ->with("items")
    ->get();
    dd($test);
    }

    public function testOrderTest()
    {

    // 7898
//    "store_id" => "yhst-128796189915726"
//        "store_name" => "monogramonline.com"

    $test = \App\Order::query()->
    with("items")
    ->where("short_order", "1125252")
    ->get();

    $another = Inventory::where("id", 2443)
        ->get();

  //  dd($another);
  //  dd($test);

    foreach ($test[0]->items as $item) {
        $sku = $item->child_sku; // child_sku

        $another = \App\InventoryUnit::with("inventory")
            ->where("child_sku", $sku)
            ->get(); // Now use the id field to see if in file. boom!

        dd($another);

    }
    dd($test);
    }

    public function storeInventoryChildSku()
    {
        //
//    /*
//    * ** TESTING.... **
//     * Use id of inventory of 219 to get the child_sku
//     * Should be LETHER of stock number 12200 as return
//     */
//
//
    $inventoryData = [];
    $file2 = "/var/www/order.monogramonline.com/Inventories.json";
    if(file_exists($file2)) {
        $inventoryData = json_decode(file_get_contents($file2), true);
    }

    $data = [
        'ALL' => []
    ];

    foreach ($inventoryData as $id => $datum) {
        if ($datum['DROPSHIP']) {
            $inventory = Inventory::with('inventoryUnitRelation')
                ->where("id", $id)
                ->first();

//            if($inventory->id === 3549) {
//                dd($inventory);
//            }


//                if(stripos($sku, "60-252-BK-Black-One Size") !== false) {
//                    dd($inventory, $inventory->inventoryUnitRelation[0]);
//                }



                foreach ($inventory->inventoryUnitRelation as $it) {
                    $sku = $it->child_sku;
                    $data[$sku] = $id;
                    $data['ALL'][] = $sku;
                }
        }
    }

    Cache::put("SKU_TO_INVENTORY_ID", $data, 60 * 3);
    dd(Cache::get("SKU_TO_INVENTORY_ID"));
    }

    public function testOrderTest2()
    {
    $test = \App\Order::query()->
    with("items")
        ->where("short_order", "1125252")
        ->get();


//    $test2 = \App\Item::where("child_sku", "NE101465-silver-22inches")
//        ->take(10)
//        ->get();
//    dd($test2)

    /*
    * ** TESTING.... **
     * Use id of inventory of 219 to get the child_sku
     * Should be LETHER of stock number 12200 as return
     */

    $inventory = Inventory::with([
        'inventoryUnitRelation' => function($query) {
        $query->orderBy("created_at", "desc")->first();
    }])
    ->where("id", 219)
    ->get();

    dd($inventory);

    foreach ($test[0]->items as $item) {
        $sku = $item->child_sku; // child_sku

        $another = \App\InventoryUnit::with("inventory")
            ->where("child_sku", $sku)
            ->get(); // Now use the id field to see if in file. boom!

        dd($another);

    }
    dd($test);
    }

    public function dropshipInventory()
    {
            $storesNew =  Cache::remember("stores_all", 1, function() {
        return Store::all();
    });

    $file = "/var/www/order.monogramonline.com/Store.json";

    $data = [];
    if(file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
    }


//    foreach ($storesNew as $storeD) {
//        if (isset($data[$storeD->store_name]) && $data[$storeD->store_name]['DROPSHIP']) {
//
//            $id = $storeD->store_id;
//
//            $temp = Cache::remember("stores_items_$id", 1, function () use ($id) {
//                return \App\Order::with("items")
//                    ->whereHas("items", function ($query) use ($id) {
//                        $query->where("store_id", $id);
//                    })
//                    ->whereMonth('created_at', '=', \Carbon\Carbon::now()->month)
//                    ->get();
//            });
//
//            $temp->filter(function (Order $order) {
//                foreach ($order->items as $item) {
//                    $s = \App\SpecificationSheet::query()->where("product_sku", $item->item_code)->get();
//                    dd($s);
//                }
//            });
//
//        }
//    }



    $s = \App\SpecificationSheet::where("id", 1);
    dd($s);
    }

    public function fetchOrder()
    {
        $id = request()->get("id", 1125305);
    $orders = \App\Order::with("items")
        ->where("short_order", $id)
        ->get();

    dd($orders, count($orders));
    }
    public function testOrder2()
    {
        //
    // 7898
//    "store_id" => "yhst-128796189915726"
//        "store_name" => "monogramonline.com"

//    $test = \App\Order::query()->with("items")->where("short_order", "1047664")->first();
//    $item = $test->items[0];
//
//    $here = Inventory::find($item->item_code);

//    $orders = \App\Order::with("items")
//        ->whereMonth('created_at', '=', \Carbon\Carbon::now()->month)
//        ->take(10)->get();
//
//    $itemUsing = $orders[0]->items;
//
//    foreach ($itemUsing as $item) {
//        $s = \App\SpecificationSheet::query()->where("product_sku", $item->item_code)->get();
//        dd($item, $s);
//    }


        $id = 7898;
        $orders = \App\Order::with("items")
            ->whereHas("items", function ($query) use ($id) {
                $query->where("store_id", $id);
            })
        ->whereMonth('created_at', '=', \Carbon\Carbon::now()->month)
        ->get();
        dd($orders, count($orders));
    }

    public function shippingTest()
    {
        $file = "/var/www/order.monogramonline.com/import.csv";

    $csv = new CSV;
    $data = $csv->intoArray($file, ",");

    $methods = [
        "GROUND" => "S_GROUND",
        "AIR" => "S_AIR_2DAY",
    ];

    $totalTouched = 0;
    $stats = [];
    foreach ($data as $inside) {
        $method = strtolower($inside[9]);

        $order = \App\Order::query()->where("short_order", $inside[0])->first();

        if($method == "2 day" or $method == "2 day air") {
            $order->carrier = "UP";
            $order->method = "S_AIR_2DAY";
            $order->save();
            $totalTouched++;
        } else {
            if($method == "ground") {
                $order->carrier = "UP";
                $order->method = "S_GROUND";
                $order->save();
                $totalTouched++;
            }
        }
    }

    return response()->json(
        [
            "total shipping fixed" => $totalTouched
        ]
    );
    }

    public function pricesTest()
    {
        $file = "/var/www/order.monogramonline.com/import.csv";

    $csv = new CSV;
    $data = $csv->intoArray($file, ",");


    $totalTouched = 0;
    foreach ($data as $inside) {
        $price = strtolower($inside[14]);

        $order = \App\Order::query()->where("short_order", $inside[0])->first();

        $order->total = str_replace(
            [
                '$',
                " "
            ],
            [
                "",
                ""
            ],
            $price
        );
        $order->save();
        $totalTouched++;
    }

    return response()->json(
        [
            "total" => $totalTouched
        ]
    );
    }

    public function codeTest()
    {
           $order = \App\Order::with("items.shipInfo")
           ->where("id", 1110369)
           ->get()[0];

   if(request()->has("add")) {
       $order->ship_message = "TEST TEST ANDRE TEST TEST TEST";
       $order->save();
   }
   if(request()->has("remove")) {
       $order->ship_message = "";
       $order->save();
   }
   dd($order);
    }

    public function filtersAdd($name)
    {
        $filters = [];

    if(Cache::has("REPORT_FILTERS")) {
        $filters = Cache::get("REPORT_FILTERS");
    }

   foreach (request()->all() as $key => $value) {
       if($key === "filters") continue;
       if($key === "filter_name") continue;
       if($key === "selected") continue;
       $filters[$name][$key] = $value;
   }
    Cache::forever("REPORT_FILTERS", $filters);

    return redirect()->back()->with('success', 'Filter has been saved as ' . $name);

//    return response()->json(
//        [
//            "Status" => true,
//            "Message" => "Saved filter",
//            "Data" => request()->all()
//        ]
//    );
    }

    public function filtersDelete($name)
    {
        $filters = [];

    if(Cache::has("REPORT_FILTERS")) {
        $filters = Cache::get("REPORT_FILTERS");
    }

    $status = false;

    if(isset($filters[$name])) {
        $status = true;
        unset($filters[$name]);
        Cache::forever("REPORT_FILTERS", $filters);
    }

    return redirect()->back()->with('success', 'Filter has been removed with name ' . $name);

//    return response()->json(
//        [
//            "Status" => $status,
//            "Message" => $status ? "Successfully deleted filter $name" : "No filters found with name $name",
//            "Data" => request()->all()
//        ]
//    );
    }
    public function filtersView($nameFilter)
    {
        $filters = [];

    if(Cache::has("REPORT_FILTERS")) {
        $filters = Cache::get("REPORT_FILTERS");
    }

    $array = '%5B%5D';

    if(isset($filters[$nameFilter])) {
        $baseURL = "https://order.monogramonline.com/prod_report/summaryfilter";

        $first = true;

        foreach ($filters[$nameFilter] as $name => $filter) {
            /*
             * Check if it's a string
             */
            if(!is_array($filter)) {
               if($first) {
                   $baseURL .= "?" . $name . '=' . $filter;
                   $first = false;
               } else {
                   $baseURL .= "&" . $name . '=' . $filter;
               }
            } else {
                foreach ($filter as $filterValue) {
                    if($first) {
                        $baseURL .= "?" . $name . $array . '=' . $filterValue;
                        $first = false;
                    } else {
                        $baseURL .= "&" . $name .  $array . "=" . $filterValue;
                    }
                }
            }
        }

        $baseURL .= "&selected=" . $nameFilter;

        return redirect($baseURL);
    } else {
        return redirect()->back()->with('success', 'The ' . $nameFilter . " filter is no longer available!");
    }
//    return response()->json(
//        [
//            "Status" => true,
//            "Message" => "Fetched filters",
//            "Data" => $filters
//        ]
//    );
    }

    public function filtersAll()
    {
        $filters = [];

    if(Cache::has("REPORT_FILTERS")) {
        $filters = Cache::get("REPORT_FILTERS");
    }

    return response()->json(
        [
            "Status" => true,
            "Message" => "Fetched filters",
            "Data" => $filters
        ]
    );
    }

    public function filtersClear()
    {
        $filters = [];

    if(Cache::has("REPORT_FILTERS")) {
        Cache::forget("REPORT_FILTERS");
    }

    return response()->json(
        [
            "Status" => true,
            "Message" => "Removed all filters",
        ]
    );
    }

    public function fixImageLoadLink($batch_number)
    {
        /*
//     * Temp removed 7/12/2022 3:51 PM
//     */
//

    $batch = Batch::with('items') ->where('batch_number', $batch_number)
        ->first();

    foreach ($batch->items as $item) {
        @file_get_contents("http://order.monogramonline.com/lazy/upload-download/{$item->id}?batch_number={$batch_number}&item_id={$item->id}");
    }
    return response()->json(['Status' => true, "Message" => "Fetched graphic successful"]);
    }




}
