<?php

namespace App\Http\Controllers;

use App\Batch;
use App\BatchNote;
use App\BatchRoute;
use App\BatchScan;
use App\Item;
use App\Order;
use App\Station;
use App\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Monogram\ImageHelper;
use Monogram\Sure3d;

class BatchController extends Controller
{

    public function index(Request $request)
    {
        if ($request->all() != []) {
            $batch_numbers = Batch::where('is_deleted', 0)
                ->searchBatch($request->get('batch'))
                ->searchRoute($request->get('route'))
                ->searchStation($request->get('station'))
                ->searchStationType($request->get('type'))
                ->searchSection($request->get('section'))
                ->searchStore($request->get('store'))
                ->searchProductionStation($request->get('production_station'))
                ->searchGraphicDir($request->get('graphic_dir'))
                ->searchPrinted($request->get('printed'), $request->get('print_date'), $request->get('printed_by'))
                ->searchStatus($request->get('status'), $request->get('batch'))
                ->searchGraphic($request->get('graphic_found'))
                ->searchMinChangeDate($request->get('start_date'))
                ->searchMaxChangeDate($request->get('end_date'))
                ->searchOrderDate($request->get('order_start_date'), $request->get('order_end_date'))
//                ->limit(30000)
                ->get()
                ->pluck('batch_number');

            if ($request->get('status') == "complete") {
                foreach ($batch_numbers as $key => $val) {
                    $dir = "/media/RDrive/archive/" . $val;
                    $this->removeShipedImagePdf($dir);
                }

            }

            $batches = Batch::with('route', 'station', 'itemsCount', 'first_item.product', 'store')
                ->whereIn('batch_number', $batch_numbers)
                ->latest('created_at')
                ->where('is_deleted', 0)
                ->paginate(50);

            $total = Item::whereIn('batch_number', $batch_numbers)
                ->where('is_deleted', '0')
                ->selectRaw('SUM(item_quantity) as quantity, count(*) as count')
                ->get();

            $total = $total[0];

        } else {
            $batches = [];
            $total = [];
        }

        $routes = BatchRoute::where('is_deleted', 0)
            ->orderBy('batch_route_name')
            ->latest()
            ->get()
            ->pluck('batch_route_name', 'id')
            ->prepend('Select a route', 'all');

        $stationsList = Station::select(DB::raw('CONCAT(stations.station_name, " - ", stations.station_description) AS full_station'), 'stations.id')
            ->where('is_deleted', 0)
            ->orderBy('stations.station_name')
            ->get()
            ->pluck('full_station', 'id')
            ->prepend('Select a Station', 'all');

        $statuses = Batch::getStatusList();

        $stores = Store::list('%', '%', 'none');



        $filterUsername = $request->get('filter_username', null);
        $scans = [];


//        $first = Batch::where("batch_number", 650129)->first();
//        $second = Batch::where("batch_number", 649703)->first();
//        $trd = Batch::where("batch_number", 650320)->first();
//
//        dd($first, $second, $trd);





        $scans = [];
        if($filterUsername !== null) {


            foreach ($batches as $batch) {
                $data = Batch::lastScan($batch->batch_number);
                $scans[$batch->batch_number] = $data['username'];

                if($data['username'] == "robot") {
//                    $batch->prev_station_id = null;
//                    $batch->station_id = 89;
//                    $batch->save();
                }
            }

            foreach ($batches as $key => $batch) {
               if($filterUsername !== null or strlen($filterUsername) !== 0) {
                   if($scans[$batch->batch_number] !== $filterUsername) {
                       //unset($batches[$key]);
                   }
               }
            }
        } else {
            foreach ($batches as $batch) {
                $scans[$batch->batch_number] = "N/A";
            }
        }

        return view('batches.list', compact('batches', 'total', 'request', 'routes', 'stationsList', 'statuses', 'stores', 'scans'));
    }

    public function removeShipedImagePdf($dir)
    {
        if (file_exists($dir)) {
            if (is_dir($dir)) {
                $objects = scandir($dir);
                foreach ($objects as $object) {
                    if ($object != "." && $object != "..") {
                        if (is_dir($dir . "/" . $object) && !is_link($dir . "/" . $object))
                            rrmdir($dir . "/" . $object);
                        else
                            unlink($dir . "/" . $object);
                    }
                }
                rmdir($dir);
            } elseif (is_file($dir)) {
                unlink($dir);
            }
        }
    }

    public function indeGraphic(Request $request)
    {
        if ($request->all() != []) {
            $batch_numbers = Batch::where('is_deleted', 0)
                ->searchBatch($request->get('batch'))
                ->searchRoute($request->get('route'))
                ->searchStation($request->get('station'))
                ->searchStationType($request->get('type'))
                ->searchSection($request->get('section'))
                ->searchStore($request->get('store_id'))
                ->searchProductionStation($request->get('production_station'))
                ->searchGraphicDir($request->get('graphic_dir'))
                ->searchPrinted($request->get('printed'), $request->get('print_date'), $request->get('printed_by'))
                ->searchStatus($request->get('status'), $request->get('batch'))
                ->searchGraphic($request->get('graphic_found'))
                ->searchMinChangeDate($request->get('start_date'))
                ->searchMaxChangeDate($request->get('end_date'))
                ->searchOrderDate($request->get('order_start_date'), $request->get('order_end_date'))
                ->orderBy('id', 'DESC')
                ->limit(3000)
                ->get()
                ->pluck('batch_number');

            if($request->get('status') == "complete"){
                foreach ($batch_numbers as $key => $val){
                    $dir = "/var/www/".Sure3d::getEnv()."/public_html/media/graphics/archive/".$val;
                    $this->removeShipedImagePdf($dir);
//echo "<br>1. ".$dir;
                    $dir = "/media/graphics/MAIN/".$val;
                    $this->removeShipedImagePdf($dir);

//echo "<br>2. ".$dir;
                    $files = "/var/www/".Sure3d::getEnv()."/public_html/media/graphics/summaries/".$val.".pdf";
                    $this->removeShipedImagePdf($files);

                    $result = glob("/media/".env("GRAPHICS_ENV")."/Sure3d/*".$val."-*");
//echo "<br>3."."/media/graphics/Sure3d/*".$val."-*";
                    if(!empty($result)){
                        foreach ($result as $filePathName){
//                            echo "<br>".$filePathName;
                            $this->removeShipedImagePdf($filePathName);
                        }
                    }
                }
            }

            $batches = Batch::with('route', 'station', 'itemsCount', 'first_item.product', 'store')
                ->whereIn('batch_number', $batch_numbers)
                ->latest('created_at')
                ->paginate(500);


//            foreach ($batch_numbers as $bn) {
//                shell_exec("curl http://order.monogramonline.com/fix/image-load/link/$bn > /dev/null 2>/dev/null &");
//            }

            // Todo: check additional curl error and all.
            // store in var[], return array[] if field supplied in url bar, &test=true
            // if not continue as usual.

            $total = Item::whereIn('batch_number', $batch_numbers)
                ->where('is_deleted', '0')
                ->selectRaw('SUM(item_quantity) as quantity, count(*) as count')
                ->get();

            $total = $total[0];

        } else {
            $batches = [];
            $total = [];
        }

        $routes = BatchRoute::where('is_deleted', 0)
            ->orderBy('batch_route_name')
            ->latest()
            ->get()
            ->pluck('batch_route_name', 'id')
            ->prepend('Select a route', 'all');

        $stationsList = Station::select(DB::raw('CONCAT(stations.station_name, " - ", stations.station_description) AS full_station'), 'stations.id')
            ->where('is_deleted', 0)
            ->orderBy('stations.station_name')
            ->get()
            ->pluck('full_station', 'id')
            ->prepend('Select a Station', 'all');

        $statuses = Batch::getStatusList();

        $stores = Store::list('1');

        return view('batches.list_graphic', compact('batches', 'total', 'request', 'routes', 'stationsList', 'statuses', 'stores'));
    }

    public function moveStation($stage, $batch_number)
    {
        $batch = Batch::with('route.stations_list')->where('batch_number', $batch_number)
            ->where('is_deleted', 0)
            ->first();

        if (empty($batch)) {
            return redirect()->back()->withErrors('Batch not found');
        }
//        $batch->station_list->pluck('station_name')->toArray();

        $index = -1;
        foreach ($batch->route->stations_list as $key => $station) {
            if($station->station_id === $batch->station_id) {
                $index = $key;
                break;
            }
        }
        if($index !== -1 && isset($batch->route->stations_list[$index])) {
            // Get the next station's ID
            if($stage === "next") {
                $index++;
            } else if($stage === "prev") {
                $index--;
            }
            if ($index < 0) {
                $index = 0;
            }
            //if max
            if ($index >= count($batch->route->stations_list)) {
                $index = count($batch->route->stations_list) - 1;
            }
            $nextStationId = $batch->route->stations_list[$index]->station_id;
            $name = $batch->route->stations_list[$index]->station_name;
            logger("Next Station ID: $nextStationId");

            $batch->station_id = $nextStationId;
            $batch->save();

            return redirect()->back()->with('success', 'Batch moved to station :' . $name);

        } else {
            logger("Station with ID $batch->station_id not found or no next station and set default :(");
            return redirect()->back()->withErrors('Station not found');
        }



    }

    public function show($batch_number, Request $request)
    {
        if ($request->has('label')) {
            $label = $request->get('label');
        } else {
            $label = null;
        }

        Batch::isFinished($batch_number);

        $batch = Batch::with('items.order.store', 'items.rejections.user', 'items.rejections.rejection_reason_info',
            'items.spec_sheet', 'items.product', 'items.parameter_option', 'station', 'route', 'section', 'store', 'summary_user')
            ->where('is_deleted', 0)
            ->where('batch_number', $batch_number)
            ->get();

        if (count($batch) == '0') {
            return view('errors.404');
        }

        $batch = $batch[0];

        if ($batch->station) {
            $station_name = $batch->station->station_name;
        } else {
            $station_name = 'Station not Found';
        }

        $original = Batch::getOriginalNumber($batch_number);

        $related = Batch::where('batch_number', 'LIKE', '%' . $original)
            ->where('batch_number', '!=', $batch_number)
            ->get()
            ->pluck('batch_number');

        if ($request->has('batch_note')) {
            Batch::note($batch_number, $batch->station_id, '2', $request->get('batch_note'));
        }

        $notes = BatchNote::with('station', 'user')
            ->where('batch_number', $batch_number)
            ->get();

        $scans = BatchScan::with('in_user', 'out_user', 'station')
            ->where('batch_number', $batch_number)
            ->get();

        $stations = BatchRoute::routeThroughStations($batch->batch_route_id, $station_name);

        $count = 1;

        $last_scan = Batch::lastScan($batch_number);


        $index = 0;
        if(count($batch->items) === 1) {
            $itemId = $batch->items[0]->id;
            $orderId = $batch->items[0]->order_5p;

            $order = Order::with("items")
                ->where("id", $orderId)
            ->get();

            if(count($order) === 1) {
                $order = $order[0];

                foreach ($order->items as $itIndex => $item) {
                    if($item->id === $itemId) {
                        $index = $itIndex;
                    }
                }
            }
        }

        return view('batches.show', compact('batch', 'batch_number', 'last_scan',
            'stations', 'count', 'related', 'notes', 'label', 'scans', 'index', 'request'));

    }


    public function export_bulk(Request $request)
    {
        $batch_numbers = $request->get('batch_number');

        $success = array();
        $error = array();

        if (is_array($batch_numbers)) {

            if ($request->has('force')) {
                $force = $request->get('force');
            } else {
                $force = 0;
            }

            foreach ($batch_numbers as $batch_number) {

                $msg = Batch::export($batch_number, $force);

                if (isset($msg['success'])) {
                    $success[] = $msg['success'];
                }

                if (isset($msg['error'])) {
                    $error[] = $msg['error'];
                }
            }

            //$message = sprintf("Batches: %s are exported.", implode(", ", $batch_numbers));

            return redirect()->back()
                ->with('success', $success)
                ->withErrors($error);

        } else {
            return redirect()->back()->withErrors('No Batches Selected');
        }
    }


    public function export_batch($id, $force = '0', $format = 'CSV')
    {
        $exported = Batch::export($id, $force, $format);

        isset($exported['success']) ? $success = $exported['success'] : $success = null;
        isset($exported['error']) ? $error = $exported['error'] : $error = null;

        return redirect()->back()
            ->with('success', $success)
            ->withErrors($error);

    }

    public function releaseBulk(Request $request)
    {
        $batch_numbers = $request->get('batch_number');

        $success = array();
        $error = array();

        if (is_array($batch_numbers)) {

            foreach ($batch_numbers as $batch_number) {

                $msg = $this->releaseBatch($batch_number);

                if ($msg['success'] != null) {
                    $success[] = $msg['success'];
                }

                if ($msg['error'] != null) {
                    $error[] = $msg['error'];
                }
            }

            return redirect()->back()
                ->with('success', $success)
                ->withErrors($error);

        } else {
            return redirect()->back()->withErrors('No Batches Selected');
        }
    }

    private function releaseBatch($batch_number)
    {
        $batch = Batch::with('pending_items')
            ->where('batch_number', $batch_number)
            ->searchStatus('active')
            ->first();

        $success = null;
        $error = null;

        if (count($batch) == 0) {
            Log::error('Release: Batch not found ' . $batch_number);
            $error = 'Release: Batch not found ' . $batch_number;
        } else {

            foreach ($batch->pending_items as $item) {
                Order::note('Item ' . $item->id . ' released from batch ' . $item->batch_number, $item->order_5p);
                $item->batch_number = '0';
                $item->save();
            }

            $finished = Batch::isFinished($batch_number);

            if (!$finished) {
                Log::error('Release: Batch is finished error ' . $batch_number);
                $error = 'Release: Batch is finished error ' . $batch_number;
            } else {
                $success = $batch_number . ' items released';
            }
        }

        return ['error' => $error, 'success' => $success];

    }

    public function release($batch_number)
    {
        $msg = $this->releaseBatch($batch_number);
        return redirect()->back()
            ->with('success', $msg['success'])
            ->withErrors($msg['error']);
    }
}
