<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Requests\ScanRequest;
use App\Http\Controllers\Controller;
use App\BatchRoute;
use App\Item;
use App\Batch;
use App\Section;
use App\BatchScan;
use App\Inventory;
use App\Station;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductionController extends Controller
{
    public function status(Request $request)
    {

        $stations_status = Batch::with('items')
            ->join('stations', 'batches.station_id', '=', 'stations.id')
            ->join('sections', 'batches.section_id', '=', 'sections.id')
            ->leftjoin('batch_scans', function ($join) {
                $join->on('batches.batch_number', '=', 'batch_scans.batch_number')
                    ->on('batch_scans.station_id', '=', DB::raw('batches.station_id'));
            })
            ->selectRaw('batches.section_id, batches.station_id, stations.station_name, stations.station_description,
																SUM(CASE WHEN (batches.inventory != "2" AND sections.inventory = "1") 
																							THEN 1 ELSE 0 END) as pick, 
																SUM(CASE WHEN (batches.inventory = "2" OR sections.inventory != "1") AND 
																							(batch_scans.batch_number IS NULL OR batch_scans.out_date IS NOT NULL) 
																							THEN 1 ELSE 0 END) as ready, 
																SUM(CASE WHEN (batches.inventory = "2" OR sections.inventory != "1") AND 
																							(batch_scans.in_date IS NOT NULL AND batch_scans.out_date IS NULL) 
																							THEN 1 ELSE 0 END) as scanned,
																COUNT(*) as batch_count, MIN(min_order_date) as min_date')
            ->searchStatus('active')
            ->where('stations.type', 'P')
            ->groupBy('batches.section_id')
            ->groupBy('batches.station_id')
            ->orderBy('batches.section_id')
            ->orderBy('stations.station_description')//->toSql(); dd($stations_status);
            ->get();//dd($stations_status);

        $sections = Section::where('is_deleted', '0')->get();

        $stations = Station::where('is_deleted', 0)
            ->where('type', 'P')
            ->selectRaw('id, CONCAT(station_name, " - ", station_description) as station')
            ->orderBy('station_name', 'ASC')
            ->latest()
            ->get()
            ->pluck('station', 'id')
            ->prepend('Select a station', '');

        $user_section = auth()->user()->section_id;

        $late = date('Y-m-d H:i:s', strtotime('-5 days'));
        $very_late = date('Y-m-d H:i:s', strtotime('-8 days'));

        return view('production.status', compact('stations', 'stations_status', 'sections', 'user_section', 'late', 'very_late'));
    }


    public function ajaxSection(Request $request)
    {
        $user = User::find(auth()->user()->id);

        if (!$user) {
            return 'User not found';
        }

        if ($request->has('data-id')) {
            $user->section_id = $request->get('data-id');
            $user->save();
            return 'success';
        } else {
            return 'No Section Provided';
        }

    }


    public function statusDetail(Request $request)
    {
        // dd($request->all());
        $active = null;
        $inactive = null;
        $activity = null;
        $station = null;

        if ($request->has('station') && $request->get('station') != NULL) {

            $station = $request->get('station');

            $user = User::find(auth()->user()->id);
            $user->station_id = $station;
            $user->save();

        } else {
            $station = auth()->user()->station_id;
        }

        if ($station == null) {
            return redirect()->action('ProductionController@status');
        }

        $ready = Batch::with('items.inventoryunit', 'store')
            ->join('items', 'batches.batch_number', '=', 'items.batch_number')
            ->join('sections', function ($join) {
                $join->on('batches.section_id', '=', 'sections.id')
                    ->where('sections.inventory', '!=', '1')
                    ->orWhere(DB::raw('batches.inventory'), '=', '2');
            })
            ->leftjoin('inventory_unit', 'items.child_sku', '=', 'inventory_unit.child_sku')
            ->leftjoin('batch_scans', function ($join) {
                $join->on('batches.batch_number', '=', 'batch_scans.batch_number')
                    ->on('batch_scans.station_id', '=', DB::raw('batches.station_id'))
                    ->whereNull('batch_scans.out_date');
            })
            ->searchStatus('active')
            ->where('batches.station_id', $station)
            ->whereNull('batch_scans.batch_number')
            ->selectRaw('GROUP_CONCAT(DISTINCT inventory_unit.stock_no_unique ORDER BY inventory_unit.stock_no_unique) as inventory_profile,
															batches.id,batches.batch_number,min_order_date,batch_route_id, items.item_thumb, batches.store_id, 
															sum(items.item_quantity) as quantity, items.item_code, items.child_sku, items.item_description')
            ->groupBy('batches.batch_number')
            ->get();

        // if ($inventory_filter != null) {
        // 	$ready = $ready;
        // }

        $inventory_totals = array();
        $inventory_date = array();
        $inventory_summary = array();

        foreach ($ready as $batch) {

            $totals = array();

            foreach ($batch->items as $item) {

                foreach ($item->inventoryunit as $unit) {

                    if (!isset($totals[$unit->stock_no_unique])) {
                        $totals[$unit->stock_no_unique] = $item->item_quantity * $unit->unit_qty;
                    } else {
                        $totals[$unit->stock_no_unique] += $item->item_quantity * $unit->unit_qty;
                    }

                    if (!isset($inventory_summary[$unit->stock_no_unique])) {
                        $inventory_summary[$unit->stock_no_unique] = $item->item_quantity * $unit->unit_qty;
                    } else {
                        $inventory_summary[$unit->stock_no_unique] += $item->item_quantity * $unit->unit_qty;
                    }
                }
            }

            $inventory_totals[$batch->batch_number] = $totals;

            if (!isset($inventory_date[$batch->inventory_profile]) || $batch->min_order_date < $inventory_date[$batch->inventory_profile]) {
                $inventory_date[$batch->inventory_profile] = $batch->min_order_date;
            }
        }

        asort($inventory_summary);
        asort($inventory_date);

        $inventory_date = array_keys($inventory_date);

        $stocknos = array_unique(array_keys($inventory_summary));

        $inventory_details = Inventory::whereIn('stock_no_unique', $stocknos)
            ->select('stock_no_unique', 'stock_name_discription', 'wh_bin', 'warehouse')
            // ->orderBy('wh_bin')
            ->orderBy('stock_name_discription')
            ->get();

        $in_progress = Batch::with('items', 'store')
            ->join('batch_scans', 'batches.batch_number', '=', 'batch_scans.batch_number')
            ->join('users', 'batch_scans.in_user_id', '=', 'users.id')
            ->join('sections', function ($join) {
                $join->on('batches.section_id', '=', 'sections.id')
                    ->where('sections.inventory', '!=', '1')
                    ->orWhere(DB::raw('batches.inventory'), '=', '2');
            })
            ->searchStatus('active')
            ->where('batches.station_id', $station)
            ->where('batch_scans.station_id', $station)
            ->whereNull('batch_scans.out_date')
            ->selectRaw('batches.id,batches.batch_number, min_order_date, batch_route_id, batches.store_id, batch_scans.id as scan_id,
															batch_scans.in_date, users.username, TIMEDIFF(NOW(), batch_scans.in_date) as elapsed_time')
            ->groupBy('batches.batch_number')
            ->orderBy('batch_scans.in_date', 'ASC')
            ->get();

        $routes = Batch::with('route')
            ->where('station_id', $station)
            ->select('batch_route_id')
            ->groupBy('batch_route_id')
            ->get();

        $next_in_route = array();

        foreach ($routes as $route) {
            $next_in_route[$route->batch_route_id] = Batch::getNextStation('name', $route->batch_route_id, $station);
        }


        $activity = BatchScan::with('station', 'in_user', 'out_user')
            ->where('station_id', $station)
            ->limit(100)
            ->orderBy('in_date', 'DESC')
            ->get();

        $station_name = Station::find($station)->station_name;

        $stations = Station::where('is_deleted', 0)
            ->where('type', 'P')
            ->selectRaw('id, CONCAT(station_name, " - ", station_description) as station')
            ->orderBy('station_name', 'ASC')
            ->latest()
            ->get()
            ->pluck('station', 'id')
            ->prepend('Select a station', '');

        return view('production.status_detail', compact('station', 'station_name', 'ready', 'in_progress', 'activity', 'next_in_route', 'stations',
            'inventory_date', 'inventory_totals', 'inventory_details', 'inventory_summary', 'inventory_filter'));
    }


    public function openScanWork(Request $request)
    {

        $messages = array();
        $batch = NULL;

        // $work = Batch::with('picking_report')
        // 							->join('stations', 'batches.station_id', '=', 'stations.id')
        // 							->searchSattus('active')
        // 							->whereNotNull('summary_date')
        // 							->where('stations.type', 'P')
        // 							->groupBy('station_id')
        // 							->get();

        return view('production.scan_work', compact('batch', 'messages'));
    }


    public function scanWork(ScanRequest $request)
    {
        $messages = array();
        $scan_errors = array();
        $user = NULL;
        $user_id = NULL;
        $batch = NULL;
        $prev_station = NULL;
        $status = NULL;
        $station_id = auth()->user()->station_id;

        if ($request->get('from') != '' && $request->get('from') != 'scanWork') {
            $action = 'ProductionController@' . $request->get('from');
        } else {
            $action = 'ProductionController@statusDetail';
        }

        $user = trim($request->get('user'));

        try {
            $user_id = intval(substr($user, 4, -1) / 8);
        } catch (\Exception $e) {
            $scan_errors[] = 'Invalid User ID';
        }

        if (!Auth::onceUsingId($user_id)) {

            $scan_errors[] = 'User Not Found';
        }

        $batch_number = trim($request->get('batch_number'));

        $batch = Batch::with('items.inventoryunit.inventory', 'items.spec_sheet', 'station', 'section')
            ->where('batch_number', 'LIKE', $batch_number)
            ->first();

        if (count((array)$batch) == 0) { #if (count($batch) == 0) {
            $scan_errors[] = "Batch $batch_number not Found";
        } else if ($batch->status != 'active') {
            $related = Batch::related($batch_number);

            if ($related == false) {
                $scan_errors[] = "Batch $batch_number not Active";
                $scan_errors[] = 'DO NOT PRODUCE';
            } else {
                $batch = $related;
            }
        } else if ($batch->station->type == 'Q') {
            $scan_errors[] = "Batch $batch_number is already in QC";
            $scan_errors[] = 'DO NOT PRODUCE';
        } else if ($batch->station->type != 'P') {
            $scan_errors[] = "Batch $batch_number not in a Production Station";
            $scan_errors[] = 'DO NOT PRODUCE';
            // } else if ($batch->section->inventory == '1' && $batch->inventory != '2') {
            // 				$scan_errors[] = "Inventory for Batch $batch_number has not been picked";
            // 				$scan_errors[] = 'Inventory must be picked before production';
        }

        if (count($scan_errors) != 0) {
            return redirect()->action($action)->withErrors($scan_errors);
        }

        if ($request->has('task')) {

            $scan = BatchScan::with('in_user')
                ->where('batch_number', $batch->batch_number)
                ->where('station_id', $batch->station_id)
                ->whereNull('out_date')
                ->selectRaw('batch_scans.*, TIMEDIFF(NOW(), batch_scans.in_date) as elapsed_time')
                ->latest()
                ->first();

            if ($request->get('task') == 'scan') {

                if (count((array)$scan) == 0) {

                    $scan = new BatchScan;
                    $scan->batch_number = $batch->batch_number;
                    $scan->station_id = $batch->station_id;
                    $scan->in_user_id = $user_id;
                    $scan->in_date = date("Y-m-d H:i:s");
                    $scan->save();
                    $status = 'IN';

                    $messages[] = ['Started:', $batch->station->station_name . ' - ' . $batch->station->station_description];

                    if ($batch->station_id != $station_id) {
                        $station_id = $batch->station_id;
                        $user = User::find(auth()->user()->id);
                        $user->station_id = $station_id;
                        $user->save();
                    }

                } elseif ($scan->in_date != NULL) {

                    if ($batch->section->same_user == '1' && $scan->in_user_id != $user_id) {

                        return redirect()->action($action)->withErrors('Batch ' . $batch_number .
                            ' is being worked on by ' . $scan->in_user->username);
                    }

                    if ($batch->section->start_finish == '0') {

                        $scan->out_user_id = $user_id;
                        $scan->out_date = date("Y-m-d H:i:s");
                        $scan->save();
                        $status = 'OUT';

                        $prev_station = $batch->station_id;

                        $next_station = Batch::getNextStation('object', $batch->batch_route_id, $batch->station_id);

                        if ($next_station == NULL) {

                            return redirect()->action($action)->withErrors('Batch ' . $batch_number . ': No more stations on the route');

                        } else {

                            $batch->prev_station_id = $batch->station_id;
                            $batch->station_id = $next_station->id;
                            $batch->save();

                            $messages[] = ['Moved to:', $next_station->station_name . ' - ' . $next_station->station_description];

                            $status = 'MOVED';
                        }
                    } else {

                        $messages[] = ['Work Time:', $scan->elapsed_time];
                        $status = 'IN';

                    }

                } elseif ($scan->in_date == NULL) {

                    $scan->in_user_id = $user_id;
                    $scan->in_date = date("Y-m-d H:i:s");
                    $scan->save();
                    $status = 'IN';

                    $messages[] = ['Started:', $batch->station->station_name . ' - ' . $batch->station->station_description];

                    Log::error('ScanWork: Batch Scan ' . $scan->id . ' with null in_date ' . $batch->batch_number);

                    if ($batch->station_id != $station_id) {
                        $station_id = $batch->station_id;
                        $user = User::find(auth()->user()->id);
                        $user->station_id = $station;
                        $user->save();
                    }

                } else {

                    return redirect()->action($action)->withErrors('Batch ' . $batch_number . ': Cannot determine scan status of batch');
                }

            } elseif ($request->get('task') == 'finish') {

                if (!$scan) {
                    return redirect()->action($action)->withErrors('Batch ' . $batch_number . ' is not in progress, it cannot be finished');
                }

                if ($batch->section && $batch->section->same_user == '1' && $scan->in_user_id != $user_id) {

                    return redirect()->action($action)->withErrors('Batch ' . $batch_number .
                        ' is being worked on by ' . $scan->in_user->username);
                }

                $scan->out_user_id = $user_id;
                $scan->out_date = date("Y-m-d H:i:s");
                $scan->save();
                $status = 'OUT';

                $prev_station = $batch->station_id;

                $next_station = Batch::getNextStation('object', $batch->batch_route_id, $batch->station_id);

                if ($next_station == NULL) {

                    return redirect()->action($action)->withErrors('Batch ' . $batch_number . ': No more stations on the route');

                } else {

                    $batch->prev_station_id = $batch->station_id;
                    $batch->station_id = $next_station->id;
                    $batch->save();

                    $messages[] = ['Moved to:', $next_station->station_name . ' - ' . $next_station->station_description];

                    $status = 'MOVED';
                }

            } elseif ($request->get('task') == 'undoScan') {

                if (count($scan) == 0) {

                    return redirect()->action($action)->withErrors('Batch ' . $batch_number . ': Could Not Locate Scan to Undo');

                } else {

                    $scan->out_user_id = $user_id;
                    $scan->out_date = date("Y-m-d H:i:s");
                    $scan->save();
                    $status = 'UNDO';
                }

                $messages[] = ["Batch $batch_number", 'Not Started'];

            } elseif ($request->get('task') == 'undoMove') {

                if ($request->has('prev_station')) {

                    $batch->station_id = $request->get('prev_station');
                    $station = Station::find($batch->station_id);
                    $messages[] = ['Moved back to:', $station->station_name . ' - ' . $station->station_description];
                    $status = 'MOVED BACK';

                } else {

                    return redirect()->action($action)->withErrors('Batch ' . $batch_number . ': Previous Station not found');
                }

            } elseif ($request->get('task') == 'force') {

                $scan = BatchScan::find($request->get('id'));

                if (!$scan) {
                    return redirect()->action($action)->withErrors('Batch ' . $batch_number . ': Scan not found');
                }

                $scan->out_date = date('Y-m-d H:i:s');
                $scan->out_user_id = $user_id;
                $scan->forced = '1';
                $scan->save();

                return redirect()->action($action)->withSuccess('Batch ' . $batch_number . ' Scanned out by supervisor');
            }
        }

        if (count((array)$batch) > 0) {
            $stations = BatchRoute::routeThroughStations($batch->batch_route_id, Station::find($batch->station_id)->station_name);
        } else {
            $stations = null;
        }

        if (count((array)$messages) > 1) {
            Log::error('ScanWork: too many messages in array');
            Log::error($messages);
        }

        $message = $messages[0];

        $station = Station::find($station_id);

        if (!$station) {
            $station = $batch->station;
        }

        $count = 1;

        return view('production.scan_work',
            compact('batch', 'message', 'user', 'stations', 'station', 'status', 'prev_station'));
    }

    public function openMoveNext()
    {
        $station = NULL;
        $route = NULL;
        $routes_in_station = NULL;
        $next_in_route = NULL;
        $next_type = NULL;
        $stations_in_route = NULL;
        $scan_batches = NULL;
        $scan_batches_image = NULL;

        $stations_list = Station::where('is_deleted', 0)
            ->orderBy('station_name', 'ASC')
            ->latest()
            ->get()
            ->pluck('custom_station_name', 'id')
            ->prepend('Or select a station', '');

        return view('production.moveNext', compact('batches', 'station', 'route', 'stations_list', 'scan_batches', 'scan_batches_image',
            'routes_in_station', 'next_in_route', 'next_type', 'stations_in_route'));
    }

    public function openExpoertImage(Request $request)
    {
        $success = array();
        $error = NULL;
        $station = NULL;
        $route = NULL;
        $routes_in_station = NULL;
        $next_in_route = NULL;
        $next_type = NULL;
        $stations_in_route = NULL;
        $scan_batches = NULL;
        $scan_batches_image = NULL;


        return view('production.moveNextExport', compact('batches', 'scan_batches'));
    }

    public function expoertImage(Request $request)
    {
        $success = array();
        $error = NULL;
        $scan_batches = NULL;
        $scan_batches_image = NULL;
        $dst = "/media/RDrive/tempDir/";

        ini_set('memory_limit', '256M');

        $scan_batches = str_replace(',,', ',', $request->get('scan_batches'));
        $scan_list = explode(',', rtrim(trim($request->get('scan_batches')), ','));
        $batch_array = array();

        foreach ($scan_list as $input) {
            if (substr(trim($input), 0, 4) == 'BATC') {
                $batch_array[] = substr(trim($input), 4);
            } else {
                $batch_array[] = trim($input);
            }
        }


        foreach ($batch_array as $batch_number) {
            $path = "/media/RDrive/archive/" . $batch_number . "*";
            $list = glob($path);

            if (!empty($list)) {
                foreach ($list as $src) {
                    set_time_limit(0);
//                    echo "\n$$$$$$$$$$ = ".$src;
                    // /media/RDrive/archive/454794-soft-pen-sub    /media/RDrive/tempDir
                    $dirName = basename($src);
                    if (is_dir($src)) {
                        $this->recurseCopy($src, $dst . $dirName);
                    } else {
                        copy($src, $dst . $dirName);
                    }

                }
            }

        }

        return view('production.moveNextExport', compact('batches', 'scan_batches'));

    }

    public function recurseCopy($src, $dst)
    {
//        echo "<br>".$dst;
//        dd($dst);


        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->recurseCopy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    public function moveNextStation(Request $request)
    {
        $success = array();
        $error = NULL;
        $scan_batches = NULL;
        $scan_batches_image = NULL;

        if ($request->get('task') == 'next') {

            $batch_update = Batch::with('route', 'station')
                ->whereIn('batch_number', $request->get('batch_number'))
                ->get();

            foreach ($batch_update as $batch) {
                $next_station = Batch::getNextStation('object', $batch->batch_route_id, $batch->station_id);
                if ($next_station && $next_station->id != '0') {
                    $batch->prev_station_id = $batch->station_id;
                    $batch->station_id = $next_station->id;
                    $batch->save();
                    $success[] = sprintf('Batch %s Successfully Moved to %s<br>', $batch->batch_number, $next_station->station_name);
                } else {
                    $error .= sprintf('Batch %s has no further stations on route <br>', $batch->batch_number);
                }
            }

        } elseif ($request->get('task') == 'move') {

            if ($request->has('station_change') && $request->get('station_change') != '' && $request->get('station_change') != '0') {

                $batch_update = Batch::with('route', 'station')
                    ->whereIn('batch_number', $request->get('batch_number'))
                    ->get();

                foreach ($batch_update as $batch) {
                    $batch->prev_station_id = $batch->station_id;
                    $batch->station_id = $request->get('station_change');;
                    $batch->save();
                    $success[] = sprintf('Batch %s Successfully Moved <br>', $batch->batch_number);
                }


            } else {
                $error .= 'No Station Selected';
            }
        }

        if ($request->has('scan_batches') && $request->get('scan_batches') != ',') {

            ini_set('memory_limit', '256M');

            $scan_batches = str_replace(',,', ',', $request->get('scan_batches'));
            $scan_list = explode(',', rtrim(trim($request->get('scan_batches')), ','));
            $batch_array = array();

            foreach ($scan_list as $input) {
                if (substr(trim($input), 0, 4) == 'BATC') {
                    $batch_array[] = substr(trim($input), 4);
                } else {
                    $batch_array[] = trim($input);
                }
            }

            $found = Batch::with('first_item.order', 'route', 'station', 'itemsCount')
                ->where('is_deleted', '0')
                ->whereIn('batch_number', $batch_array)
                ->searchRoute($request->get('route'))
                ->searchStatus('movable')
                ->get();

            $routes_in_station = array();
            $routes_in_station['all'] = 'Select Route';
            $next_in_route = array();
            $result = array();

            foreach ($batch_array as $batch_number) {

                $batch = $found->where('batch_number', $batch_number)->first();

                if (!$batch) {

                    $related = Batch::related($batch_number);

                    if ($related) {
                        $batch = $related;
                        $success[] = sprintf('Batch %s selected, Batch %s is inactive <br>', $batch->batch_number, $batch_number);
                    } else {
                        $error .= sprintf('Problem with Batch %s <br>', $batch_number);
                        continue;
                    }
                }

                $routes_in_station[$batch->batch_route_id] =
                    $batch->route->batch_route_name . " => " . $batch->route->batch_code;
                $next_station = Batch::getNextStation('object', $batch->batch_route_id, $batch->station_id);
                if ($next_station) {
                    $next_in_route[$batch->batch_route_id] = $next_station->station_name . ' - ' . $next_station->station_description;
                    $next_type[$batch->batch_route_id] = $next_station->type;
                }

                $batches[] = $batch;
            }

            unset($found);

            if (count($routes_in_station) < 2) {
                $routes_in_station = NULL;
            }

        } elseif ($request->has('station')) {

            $batches = Batch::with('first_item.order', 'route', 'itemsCount')
                ->join('stations', 'batches.station_id', '=', 'stations.id')
                ->where('batches.is_deleted', '0')
                ->searchRoute($request->get('route'))
                ->searchStatus('movable')
                //->where('stations.type', 'P')
                ->where('station_id', $request->get('station'))
                ->orderBy('batch_number', 'ASC')
                ->get();

            $routes = Batch::with('route')
                ->join('stations', 'batches.station_id', '=', 'stations.id')
                ->where('batches.is_deleted', '0')
                ->where('station_id', $request->get('station'))
                ->searchStatus('movable')
                //->where('stations.type', 'P')
                ->select('batch_route_id')
                ->groupBy('batch_route_id')
                ->get();

            $routes_in_station = array();
            $routes_in_station['all'] = 'Select Route';
            $next_in_route = array();

            foreach ($routes as $route) {
                $routes_in_station[$route->batch_route_id] =
                    $route->route->batch_route_name . " => " . $route->route->batch_code;
                $next_station = Batch::getNextStation('object', $route->batch_route_id, $request->get('station'));
                if ($next_station) {
                    $next_in_route[$route->batch_route_id] = $next_station->station_name . ' - ' . $next_station->station_description;
                    $next_type[$route->batch_route_id] = $next_station->type;
                }
            }

            if (count($routes_in_station) < 2) {
                $routes_in_station = NULL;
            }

            $station = Station::where('id', $request->get('station'))
                ->first();

        } else {
            $station = NULL;
        }

        $stations_list = Station::where('is_deleted', '0')
            ->orderBy('station_name', 'ASC')
            ->latest()
            ->get()
            ->pluck('custom_station_name', 'id')
            ->prepend('Or select a station', '');

        if ($request->has('route') || (isset($batches) && count($batches) == 1)) {

            if ($request->has('route')) {
                $route = $request->get('route');
            } else {
                $route = $batches[0]->batch_route_id;
            }

            $stations_in_route = array();
            $stations_in_route[0] = 'Move to any station in route';

            $all_route_stations = BatchRoute::with('stations_list')
                ->where('id', $route)
                ->get();

            foreach ($all_route_stations as $route_stations) {

                foreach ($route_stations->stations_list as $route_station) {

                    $stations_in_route[$route_station->station_id] = $route_station->station_name . ' => ' . $route_station->station_description;
                }
            }

        } else {
            $route = NULL;
        }


        return view('production.moveNext', compact('batches', 'station', 'route', 'stations_list', 'scan_batches', 'scan_batches_image',
            'routes_in_station', 'next_in_route', 'next_type', 'stations_in_route'))
            ->withSuccess($success)
            ->withErrors($error);
    }

    public function workConfig(Request $request)
    {

        $error = null;

        if ($request->has('station_id')) {

            $station = Station::find($request->get('station_id'));

            if ($station) {
                $station->start_finish = $request->get('start_finish');
                $station->same_user = $request->get('same_user');
                $station->print_label = $request->get('print_label');
                $station->graphic_type = $request->get('graphic_type');
                $station->save();
            } else {
                $error = 'Station Not Found';
            }
        }

        $section_id = $request->get('section_id');

        $stations = Station::with('section_info')
            ->searchSection($section_id)
            ->where('is_deleted', '0')
            ->where('type', 'P')
            ->orderBy('section')
            ->orderBy('station_description')
            ->get();

        $sections = Section::where('is_deleted', '0')->get()->pluck('section_name', 'id');

        return view('production.work_config', compact('stations', 'section_id', 'sections'))->withErrors($error);

    }

}
