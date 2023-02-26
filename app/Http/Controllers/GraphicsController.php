<?php

namespace App\Http\Controllers;

use App\BatchNote;
use App\Customer;
use App\Order;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Batch;
use App\BatchRoute;
use App\Option;
use App\Design;
use App\Rejection;
use App\RejectionReason;
use App\Store;
use App\Station;
use App\Section;
use App\Item;
use App\Printer;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Classes\Cache;
use Monogram\ApiClient;
use Monogram\FileHelper;
use Monogram\Helper;
use Monogram\Wasatch;
use Monogram\ImageHelper;
use Monogram\Sure3d;


class GraphicsController extends Controller
{
    protected $main_dir = '/media/RDrive/MAIN/';
    protected $sort_root = '/media/RDrive/';
    protected $old_sort_root = '/media/RDrive/';

    protected $csv_dir = '/media/RDrive/5p_batch_csv_export/';
    protected $error_dir = '/media/RDrive/5p_batch_csv_export/Jobs_Error/';
    protected $finished_dir = '/media/RDrive/5p_batch_csv_export/Jobs_Finished/';
    public static $manual_dir = '/media/RDrive/5p_batch_csv_export/MANUAL/';

    // protected $sub_dir = '/media/RDrive/sublimation/'; 
    public static $archive = '/media/RDrive/archive/';
    protected $old_archive = '/media/RDrive/archive';

    protected $printers = [
        'SOFT-1' => 'SOFT-1',
        'SOFT-2' => 'SOFT-2',
        'SOFT-3' => 'SOFT-3',
        'SOFT-4' => 'SOFT-4',
        'SOFT-5' => 'SOFT-5',
        'SOFT-6' => 'SOFT-6',
        'SOFT-7' => 'SOFT-7',
        'SOFT-8' => 'SOFT-8',
        'HARD-1' => 'HARD-1',
        'HARD-2' => 'HARD-2',
        'HARD-3' => 'HARD-3'];

    public function index(Request $request)
    {
        if (!file_exists($this->csv_dir)) {
            return redirect()->back()->withErrors('Cannot find csv directory on M: drive');
        }

        if (!file_exists($this->sort_root)) {
            return redirect()->back()->withErrors('Cannot find Graphics Directory');
        }

        ini_set('memory_limit', '256M');

        $request->has('tab') ? $tab = $request->get('tab') : $tab = 'summary';

        if ($tab == 'summary') {
            $dates = array();
            $date[] = date("Y-m-d");
            $date[] = date("Y-m-d", strtotime('-3 days'));
            $date[] = date("Y-m-d", strtotime('-4 days'));
            $date[] = date("Y-m-d", strtotime('-7 days'));
            $date[] = date("Y-m-d", strtotime('-8 days'));

            $items = Item::join('batches', 'batches.batch_number', '=', 'items.batch_number')
                ->join('orders', 'items.order_5p', '=', 'orders.id')
                ->join('stations', 'batches.station_id', '=', 'stations.id')
                ->join('sections', 'stations.section', '=', 'sections.id')
                ->where('batches.status', 2)
                ->where('items.item_status', 1)
                ->where('stations.type', 'G')
                //->where('orders.order_status', 4)
                ->groupBy('stations.station_name')
                //->groupBy ( 'orders.order_status' )
                ->orderBy('sections.section_name')
                ->orderBy('stations.station_description', 'ASC')
                ->selectRaw("
                            SUM(items.item_quantity) as items_count, 
                            count(items.id) as lines_count, 
                            stations.station_name,
                            stations.station_description,
                            stations.type,
                            batches.station_id,
                            stations.section as section_id,
                            sections.section_name,
                            DATE(MIN(orders.order_date)) as earliest_order_date,
                            DATE(MIN(batches.change_date)) as earliest_scan_date,
                            COUNT(IF(orders.order_date >= '{$date[1]} 00:00:00', items.id, NULL)) as order_1,
                            COUNT(IF(orders.order_date >= '{$date[3]} 00:00:00' AND orders.order_date <= '{$date[2]} 23:59:59', items.id, NULL)) as order_2,
                            COUNT(IF(orders.order_date <= '{$date[4]} 23:59:59', items.id, NULL)) as order_3,
                            COUNT(IF(batches.change_date >= '{$date[1]} 00:00:00', items.id, NULL)) as scan_1,
                            COUNT(IF(batches.change_date >= '{$date[3]} 00:00:00' AND batches.change_date <= '{$date[2]} 23:59:59', items.id, NULL)) as scan_2,
                            COUNT(IF(batches.change_date <= '{$date[4]} 23:59:59', items.id, NULL)) as scan_3
                            ")
                ->get();

            $rejects = Item::join('rejections', 'items.id', '=', 'rejections.item_id')
                ->join('orders', 'items.order_5p', '=', 'orders.id')
                ->join('batches', 'items.batch_number', '=', 'batches.batch_number')
                ->join('sections', 'batches.section_id', '=', 'sections.id')
                ->where('items.is_deleted', '0')
                ->where('rejections.complete', '0')
                ->whereNotIn('rejections.graphic_status', [4, 5]) // exclude CS rejects
                ->searchStatus('rejected')
                ->groupBy('batches.section_id', 'rejections.graphic_status')
                ->selectRaw("
                           SUM(items.item_quantity) as items_count, 
                           count(items.id) as lines_count, 
                           rejections.graphic_status,
                           batches.section_id,
                           sections.section_name,
                           DATE(MIN(orders.order_date)) as earliest_order_date,
                           COUNT(IF(orders.order_date >= '{$date[1]} 00:00:00', items.id, NULL)) as order_1,
                           COUNT(IF(orders.order_date >= '{$date[3]} 00:00:00' AND orders.order_date <= '{$date[2]} 23:59:59', items.id, NULL)) as order_2,
                           COUNT(IF(orders.order_date <= '{$date[4]} 23:59:59', items.id, NULL)) as order_3,
                           COUNT(IF(batches.change_date >= '{$date[1]} 00:00:00', items.id, NULL)) as scan_1,
                           COUNT(IF(batches.change_date >= '{$date[3]} 00:00:00' AND batches.change_date <= '{$date[2]} 23:59:59', items.id, NULL)) as scan_2,
                           COUNT(IF(batches.change_date <= '{$date[4]} 23:59:59', items.id, NULL)) as scan_3
                           ")
                ->get(); //dd($rejects);

            $unbatched = Item::join('orders', 'items.order_5p', '=', 'orders.id')
                ->whereNull('items.tracking_number')
                ->where('items.batch_number', '=', '0')
                ->where('items.item_status', '=', '1')
                ->whereIn('orders.order_status', [4, 11, 12, 7, 9])
                ->where('orders.is_deleted', '0')
                ->where('items.is_deleted', '0')
                ->selectRaw("
                              items.id, orders.order_date, items.item_quantity,
                              SUM(items.item_quantity) as items_count, 
                              count(items.id) as lines_count,
                              DATE(MIN(orders.order_date)) as earliest_order_date,
                              COUNT(IF(orders.order_date >= '{$date[1]} 00:00:00', items.id, NULL)) as order_1,
                              COUNT(IF(orders.order_date >= '{$date[3]} 00:00:00' AND orders.order_date <= '{$date[2]} 23:59:59', items.id, NULL)) as order_2,
                              COUNT(IF(orders.order_date <= '{$date[4]} 23:59:59', items.id, NULL)) as order_3
                              ")
                ->first();

            $total = $items->sum('items_count') + $rejects->sum('items_count') + $unbatched->items_count;

        } else {
            $items = $unbatched = $rejects = [];
            $total = 0;
        }

        $graphic_statuses = Rejection::graphicStatus();

        $section = 'start';

        $now = date("F j, Y, g:i a");

        $count = array();

        if ($tab == 'to_export') {
            $to_export = $this->toExport();
            $count['to_export'] = count($to_export);
        } else {
            $count['to_export'] = $this->toExport('count');
        }

        $manual = $this->getManual();
        $count['manual'] = count($manual);

        if ($tab == 'exported') {
            $exported = $this->exported($manual->pluck('batch_number')->all());
            $count['exported'] = count($exported);
        } else {
            $count['exported'] = $this->exported($manual->pluck('batch_number')->all(), 'count');
        }

        if ($tab == 'error') {
            $error_list = $this->graphicErrors();
            $count['error'] = count($error_list);
        } else {
            $count['error'] = $this->graphicErrors('count');
        }

        // $found = $this->graphicFound();

        $sections = Section::get()->pluck('section_name', 'id');

        return view('graphics.index', compact('to_export', 'exported', 'error_list', 'manual', 'found', 'sections',
            'count', 'total', 'date', 'items', 'rejects', 'unbatched', 'now',
            'section', 'graphic_statuses', 'tab'));
    }

    private function toExport($action = 'get')
    {
        if ($action == 'get') {
            $batches = Batch::with('itemsCount', 'first_item')
                ->join('stations', 'batches.station_id', '=', 'stations.id')
                ->whereIn('batches.status', [2, 4])
                ->where('stations.type', 'G')
                ->whereNull('export_date')
                ->where('graphic_found', '0')
                ->orderBy('min_order_date')
                ->paginate(50);
        } else if ($action == 'count') {
            $batches = Batch::join('stations', 'batches.station_id', '=', 'stations.id')
                ->whereIn('batches.status', [2, 4])
                ->where('stations.type', 'G')
                ->whereNull('export_date')
                ->where('graphic_found', '0')
                ->count();
        }

        return $batches;
    }

    private function exported($manual, $action = 'get')
    {

        if ($action == 'get') {
            $this->findFiles('exports');

            $batches = Batch::with('itemsCount', 'first_item')
                ->join('stations', 'batches.station_id', '=', 'stations.id')
                ->whereIn('batches.status', [2, 4])
                ->where('stations.type', 'G')
                ->whereNotNull('export_date')
                ->where('graphic_found', '0')
                ->whereNotIn('batch_number', $manual)
                ->orderBy('min_order_date')
                ->get();

        } else if ($action == 'count') {
            $batches = Batch::join('stations', 'batches.station_id', '=', 'stations.id')
                ->whereIn('batches.status', [2, 4])
                ->where('stations.type', 'G')
                ->whereNotNull('export_date')
                ->where('graphic_found', '0')
                ->whereNotIn('batch_number', $manual)
                ->count();

        }

        return $batches;
    }

    private function graphicErrors($action = 'get')
    {

        if ($action == 'get') {

            $error_files = $this->findErrorFiles();

            $batch_numbers = Batch::join('stations', 'batches.station_id', '=', 'stations.id')
                ->whereIn('batches.status', [2, 4])
                ->where('stations.type', 'G')
                ->where('graphic_found', '>', 1)
                ->select('batch_number')
                ->get()
                ->pluck('batch_number')
                ->toArray();

            $batch_numbers = $this->removeSure3d($batch_numbers);

            $batches = Batch::with('items.parameter_option.design')
                ->whereIn('batch_number', $batch_numbers)
                ->orderBy('batches.min_order_date')
                ->get();

            $errors = array();

            foreach ($batches as $batch) {

                $error = array();

                $error['batch'] = $batch;

                $graphic_skus = array();

                if (count($batch->items) == 0) {
                    Log::error('graphicErrors: Batch with zero items ' . $batch->batch_number);
                }

                foreach ($batch->items as $item) {

                    $graphic = array();

                    if ($item->parameter_option && !in_array($item->parameter_option->graphic_sku, $graphic_skus)) {

                        $graphic_skus[] = $item->parameter_option->graphic_sku;

                        $graphic['child_sku'] = $item->child_sku;

                        $graphic['sku'] = $item->parameter_option->graphic_sku;

                        if (!$item->parameter_option->design) {
                            Design::check($item->parameter_option->graphic_sku);
                        }

                        if ($item->parameter_option->design->xml == '1') {
                            $graphic['xml'] = 'Found';
                        } else {
                            $graphic['xml'] = 'Not Found';
                        }

                        if ($item->parameter_option->design->template == '1') {
                            $graphic['template'] = 'Found';
                        } else {
                            $graphic['template'] = 'Not Found';
                        }

                        $error['graphics'][] = $graphic;

                    } else if (!$item->parameter_option) {
                        Log::error('Parameter option not found ' . $batch->batch_number . ',' . $item->id);
                    }
                }

                if (array_key_exists($batch->batch_number, $error_files)) {
                    $error['in_dir'] = 'Found';
                } else {
                    $error['in_dir'] = 'Not Found';
                }

                $errors[] = $error;
                $error = null;
            }

        } else if ($action == 'count') {

            $errors = Batch::join('stations', 'batches.station_id', '=', 'stations.id')
                ->whereIn('batches.status', [2, 4])
                ->where('stations.type', 'G')
                ->where('graphic_found', '>', 1)
                ->count();
        }

        return $errors;
    }

    private function getManual($return_type = 'batches')
    {
        $manual_list = array_diff(scandir(self::$manual_dir), array('..', '.'));

        $batch_numbers = array();

        if ($return_type == 'list') {

            foreach ($manual_list as $dir) {

                $batch_numbers[$this->getBatchNumber($dir)] = self::$manual_dir . $dir;

            }

            $batch_numbers = $this->removeSure3d($batch_numbers);

            return $batch_numbers;

        } else {

            foreach ($manual_list as $dir) {

                $batch_numbers[] = $this->getBatchNumber($dir);

            }

            $batch_numbers = $this->removeSure3d($batch_numbers);

            $batches = Batch::with('itemsCount', 'first_item', 'items')
                ->join('stations', 'batches.station_id', '=', 'stations.id')
                ->whereIn('batches.status', [2, 4])
                ->where('stations.type', 'G')
                ->whereIn('batch_number', $batch_numbers)
                ->orderBy('min_order_date')
                ->get();


            return $batches;
        }
    }

    private function removeSure3d($batch_numbers)
    {

        $items = Item::where('item_status', 1)
            ->whereNull('sure3d')
            ->where('is_deleted', '0')
            ->whereIn('batch_number', $batch_numbers)
            ->where('item_option', 'LIKE', '%Custom_EPS_download_link%')
            ->get();

        $sure3d_batches = array();

        foreach ($items as $item) {

            if (!in_array(substr($item->batch_number, 0, 1), ['R', 'X'])) {

                $options = json_decode($item->item_option, true);

                if (isset($options["Custom_EPS_download_link"]) && $item->sure3d == null) {
                    $item->sure3d = $options["Custom_EPS_download_link"];
                    $item->save();
                    $sure3d_batches[] = $item->batch_number;
                }
            }
        }

        $sure3d_batches = array_unique($sure3d_batches);

        foreach ($sure3d_batches as $batch) {

            $result = Batch::export($batch);

            if (!isset($result['error'])) {
                unset($batch_numbers[array_search($batch, $batch_numbers)]);
            }
        }

        return $batch_numbers;
    }

    private function graphicFound()
    {

        $batches = Batch::with('itemsCount', 'first_item')
            ->join('stations', 'batches.station_id', '=', 'stations.id')
            ->whereIn('batches.status', [2, 4])
            ->where('stations.type', 'G')
            ->where('graphic_found', '1')
            ->where('to_printer', '0')
            ->orderBy('min_order_date')
            ->get();

        return $batches;
    }

    public function sentToPrinter(Request $request)
    {
        $printers = $this->printers;

        $dates = array();
        $date[] = date("Y-m-d");
        $date[] = date("Y-m-d", strtotime('-3 days'));
        $date[] = date("Y-m-d", strtotime('-4 days'));
        $date[] = date("Y-m-d", strtotime('-7 days'));
        $date[] = date("Y-m-d", strtotime('-8 days'));

        if ($request->all() == []) {

            $summary = Batch::join('stations', 'batches.station_id', '=', 'stations.id')
                ->whereIn('batches.status', [2, 4])
                ->whereHas('store', function($q){
                    $q->where('permit_users', 'like', "%".auth()->user()->id ."%");
                })
                ->where('stations.type', 'G')
                ->where('graphic_found', '1')
                ->where('to_printer', '!=', '0')
                ->selectRaw("
  											to_printer,
  											count(DISTINCT batches.id) as batch_count,
                        COUNT(IF(to_printer_date >= '{$date[1]} 00:00:00', batches.id, NULL)) as group_1,
  											COUNT(IF(to_printer_date >= '{$date[3]} 00:00:00' AND to_printer_date <= '{$date[2]} 23:59:59', batches.id, NULL)) as group_2,
  											COUNT(IF(to_printer_date <= '{$date[4]} 23:59:59', batches.id, NULL)) as group_3
  											")
                ->groupBy('to_printer')
                ->get();

            return view('graphics.sent_printer', compact('summary', 'printers'));

        } else {

            $op = '!=';
            $printer = '0';

            if ($request->has('printer') && $request->get('printer') != '') {
                $op = '=';
                $printer = $request->get('printer');
            }

            $date_1 = '2016-06-01';
            $date_2 = $date[0];

            if ($request->has('date')) {
                if ($request->get('date') == 1) {
                    $date_1 = $date[1];
                } else if ($request->get('date') == 2) {
                    $date_1 = $date[3];
                    $date_2 = $date[2];
                } else if ($request->get('date') == 3) {
                    $date_2 = $date[4];
                } else {
                    Log::error('Sent to Printer: Error unrecognized date ' . $request->get('date'));
                }
            }

            $to_printer = Batch::with('itemsCount', 'first_item')
                ->join('stations', 'batches.station_id', '=', 'stations.id')
                ->whereHas('store', function($q){
                    $q->where('permit_users', 'like', "%".auth()->user()->id ."%");
                })
                ->whereIn('batches.status', [2, 4])
                ->where('stations.type', 'G')
                ->where('graphic_found', '1')
                ->where('to_printer', $op, $printer)
                ->where('to_printer_date', '>=', $date_1 . ' 00:00:00')
                ->where('to_printer_date', '<=', $date_2 . ' 23:59:59')
                ->selectRaw('batches.*, stations.*, datediff(CURDATE(), to_printer_date) as days')
                ->orderBy('to_printer_date', 'ASC')
                ->get();

            $batch_numbers = $to_printer->pluck('batch_number');

            $w = new Wasatch;
            $batch_queue = array();

            foreach ($batch_numbers as $batch_number) {
                $batch_queue[$batch_number] = $w->notInQueue($batch_number);
            }

            $total_items = Item::where('is_deleted', '0')
                ->whereIn('batch_number', $batch_numbers)
                ->count();

            $scans = [];
            foreach ($to_printer as $batch) {
                $data = Batch::lastScan($batch->batch_number);

                $scans[$batch->batch_number] = $data['username'];
            }
            return view('graphics.sent_printer', compact('to_printer', 'printers', 'total_items', 'batch_queue', 'scans'));
        }
    }

    public function completeManual(Request $request)
    {

        $success = array();
        $error = array();

        $batch_numbers = $request->get('batch_number');

        if (is_array($batch_numbers)) {

            $batches = Batch::with('route')
                ->whereIn('batch_number', $batch_numbers)
                ->get();

            foreach ($batches as $batch) {

                $batch->graphic_found = '1';
                $batch->save();

                $result = $this->moveNext($batch, 'graphics');

                if ($result['success'] != null) {
                    $success[] = $result['success'];
                }

                if ($result['error'] != null) {
                    $error[] = $result['error'];
                }

                $list = glob($this->csv_dir . 'MANUAL/' . $batch->batch_number . "*");

                if (count($list) > 1) {
                    $error[] = 'More than one CSV file in Manual Directory for batch ' . $batch->batch_number . ' - Files Not Moved';
                }

                foreach ($list as $file) {

                    $to_file = $this->uniqueFilename($this->finished_dir, substr($file, strrpos($file, '/') + 1));

                    try {
                        $moved = @rename($file, $this->finished_dir . $to_file);
                        if (!$moved) {
                            $this->recurseCopy($file, $this->finished_dir . $to_file);
                            $this->removeFile($file);
                        }
                    } catch (\Exception $e) {
                        Log::error('completeManual: Error moving manual csv file ' . $to_file . ' - ' . $e->getMessage());
                        $error[] = 'Error moving manual csv file - ' . $e->getMessage();
                    }
                }
            }

            $success[] = sprintf("Batches: %s Graphics processed.", implode(", ", $batch_numbers));

            return redirect()->action('GraphicsController@index', ['tab' => 'manual'])
                ->withSuccess($success)
                ->withErrors($error);

        } else {
            return redirect()->action('GraphicsController@index', ['tab' => 'manual'])->withErrors('No Batches Selected');
        }

    }

    public function selectSummaries()
    {

        // if (auth()->user()->id != 83) {
        //   return 'Please try again later';
        // }

        $production = Batch::with('production_station', 'store')
            // ->join('sections', function($join)
            //         {
            //             $join->on('batches.section_id', '=', 'sections.id')
            //                   ->where('sections.inventory', '!=', '1')
            //                   ->orWhere(DB::raw('batches.inventory'), '=', '2');
            //         })
            ->where('batches.is_deleted', '0')
            ->selectRaw('production_station_id, section_id, store_id, if(substr(batch_number,1,1) = "R", "Reject", "") as type, count(batches.id) as count')
            ->searchStatus('active')
            ->searchPrinted('0')
            ->groupBy('section_id')
            ->groupBy('production_station_id')
            ->groupBy('store_id')
            ->groupBy('type')//->toSql();
            ->get();
        // dd($production);
        $graphics = Batch::with('store')
            ->join('batch_routes', 'batches.batch_route_id', '=', 'batch_routes.id')
            ->where('batches.is_deleted', '0')
            ->selectRaw('batch_route_id, batch_routes.graphic_dir, store_id, if(substr(batch_number,1,1) = "R", "Reject", "") as type, count(batches.id) as count')
            ->searchStatus('active')
            ->searchPrinted('2')
            ->groupBy('batch_routes.graphic_dir')
            ->groupBy('store_id')
            ->groupBy('type')
            ->get();

        $date = date("Y-m-d") . ' 00:00:00';

        $today = Batch::with('production_station', 'section', 'summary_user')
            ->selectRaw('summary_date, summary_user_id, production_station_id, section_id, count(batch_number) as count')
            ->searchStatus('active')
            ->where('summary_date', '>', $date)
            ->groupBy('section_id')
            ->groupBy('production_station_id')
            ->groupBy('summary_date')
            ->groupBy('summary_user_id')
            ->orderBy('summary_date', 'DESC')
            ->get();

        return view('graphics.print_summaries', compact('production', 'graphics', 'today'));
    }

    public function selectToMoveQc(Request $request)
    {

        $request->has('store_id') ? $store_id = $request->get('store_id') : $store_id = null;

        $to_move = Batch::with('section', 'production_station')
            ->join('stations', 'batches.station_id', '=', 'stations.id')
            ->where('batches.is_deleted', '0')
            ->searchStatus('movable')
            ->where('graphic_found', '1')
////->whereNotNull('summary_date')
            ->where('stations.type', 'P')
            ->searchStore($store_id)
            ->selectRaw('section_id, production_station_id, count(*) as total')
            ->groupBy('section_id')
            ->groupBy('production_station_id')
            ->orderBy('section_id')
            ->get();

        $last_scan = Batch::with('section', 'production_station', 'items')
            ->join('stations', 'batches.station_id', '=', 'stations.id')
            ->where('batches.is_deleted', '0')
            ->where('stations.type', 'Q')
            ->latest('batches.change_date')
            ->take(5)
            ->get();

        for ($i = 0; $i < 5; $i++) {
            $username[$i] = Batch::lastScan($last_scan[$i]->batch_number);
            $name[$i] = $username[$i]['username'];
        }


        $sections = Section::get()->pluck('section_name', 'id');

        $stores = Store::list('%', '%', 'none');


        return view('graphics.move_qc', compact('last_scan', 'name', 'to_move', 'sections', 'stores', 'store_id', 'label'));
    }

    public function selectToMove(Request $request)
    {

        $request->has('store_id') ? $store_id = $request->get('store_id') : $store_id = null;

        $to_move = Batch::with('section', 'production_station')
            ->join('stations', 'batches.station_id', '=', 'stations.id')
            ->where('batches.is_deleted', '0')
            ->searchStatus('movable')
            ->where('graphic_found', '1')
            ////->whereNotNull('summary_date')
            ->where('stations.type', 'G')
            ->searchStore($store_id)
            ->selectRaw('section_id, production_station_id, count(*) as total')
            ->groupBy('section_id')
            ->groupBy('production_station_id')
            ->orderBy('section_id')
            ->get();

        $sections = Section::get()->pluck('section_name', 'id');

        $stores = Store::list('%', '%', 'none');

        return view('graphics.move_production', compact('to_move', 'sections', 'stores', 'store_id'));
    }

    public function showBatch(Request $request)
    {

        if (!$request->has('scan_batches')) {
            return redirect()->action('GraphicsController@selectToMove')->withErrors('No Batch Selected');
        }

        $scan_batches = trim($request->get('scan_batches'));

        if (substr($scan_batches, 0, 4) == 'BATC') {
            $batch_number = $this->getBatchNumber(substr($scan_batches, 4));
        } else {
            $batch_number = $this->getBatchNumber($scan_batches);
        }

        if ($batch_number == null) {
            return redirect()->action('GraphicsController@selectToMove')->withErrors('No Batch Selected');
        }

        $result = $this->moveNext($batch_number, 'production');

        if ($result['error'] != null) {
            return redirect()->action('GraphicsController@selectToMove')->withErrors($result['error']);
        }

        $to_move = Batch::with('items', 'route', 'station', 'summary_user')
            ->where('batch_number', $result['batch_number'])
            ->first();

         return $this->showBatchQc($request);
     //   return view('graphics.show_batch', compact('to_move'));

    }

    public function showBatchQc(Request $request)
    {

        if (!$request->has('scan_batches')) {
            return redirect()->action('GraphicsController@selectToMoveQc')->withErrors('No Batch Selected');
        }

        $scan_batches = trim($request->get('scan_batches'));

        if (substr($scan_batches, 0, 4) == 'BATC') {
            $batch_number = $this->getBatchNumber(substr($scan_batches, 4));
        } else {
            $batch_number = $this->getBatchNumber($scan_batches);
        }

        if ($batch_number == null) {
            return redirect()->action('GraphicsController@selectToMoveQc')->withErrors('No Batch Selected');
        }

        $result = $this->moveNext($batch_number, 'qc');

        if ($result['error'] != '') {
            Batch::note($batch_number, 4, '6', 'Production - ' . $result['error']);
            return redirect()->action('GraphicsController@selectToMoveQc')->withErrors($result['error']);
        }


        $items = Item::where('items.batch_number', $batch_number)
            ->where('items.is_deleted', '0')
            ->first();

//        $customer = Customer::where('order_id', $items->order_id)
//            ->where('is_deleted', '0')
//            ->first();

        $parts = parse_url($items->item_thumb);

// /assets/images/Sure3d/thumbs/1217029-13-Image.jpg


//
//        $filename = "^XA";
//        $filename .= "^CF0,60";
//        $filename .= "^FO100,50^FD Batch Number^FS";
//        $filename .= "^FX for barcode.";
//        $filename .= "^BY5,2,270";
//        $filename .= "^FO50,100";
//        $filename .= "^BCN,100,Y,N,N";
//        $filename .= "^FD$batch_number^FS";
//        $filename .= "^CF0,40";
//        $filename .= "^FO40,245^FDCustomer name: $customer->ship_full_name^FS";
//        $filename .= "^FO40,280^FDStyle Number: $items->item_code ^FS";
//        $filename .= "^FO40,320^FDQTY: $items->item_quantity^FS";
//        $filename .= $zplImage;
//        $filename .= "^XZ";



        $to_move = Batch::with('items', 'route', 'station', 'summary_user')
            ->where('batch_number', $result['batch_number'])
            ->first();


        $format = 'Qty: ' . $items->item_quantity . ' - #[COUNT]';
        $filename = "^XA~TA000~JSN^LT0^MNW^MTT^PON^PMN^LH0,0^JMA^PR2,2~SD30^JUS^LRN^CI0^XZ";
        $filename .= "^XA";
        $filename .= "^MMT";
        $filename .= "^PW305";
        $filename .= "^LL0203";
        $filename .= "^LS0";
        $filename .= "^FO55,35^A0,40^FB220,1,0,CH^FD{$format}^FS";
        $filename .= "^FO55,70^A0,30^FB220,1,0,CH^FD[UNIQUE_ID]^FS";

        if(stripos($batch_number, "-") !== false) {
            $filename .= "^FO25,100^BY2.3^BCN,60,,,,A^FD{$batch_number}^FS";
        } else {
            $filename .= "^FO100,100^BY2.3^BCN,60,,,,A^FD{$batch_number}^FS";
        }

        $filename .= "^PQ1,0,1,Y^XZ";

        $created =  $to_move->items[0]->created_at ?? \Carbon\Carbon::now();
        $date = $created->toDateString();

        $filename = str_replace("[UNIQUE_ID]", $date, $filename);
        $label = trim(preg_replace('/\n+/', ' ', $filename));

        return view('graphics.show_batch_qc', compact('to_move', 'label'));

    }

    public function showSublimation(Request $request)
    {

        set_time_limit(0);

        if (!file_exists($this->sort_root)) {
            return redirect()->back()->withErrors('Cannot find Graphics Directory');
        }

        $request->has('from_date') ? $from_date = $request->get('from_date') . ' 00:00:00' : $from_date = '2016-06-01 00:00:00';
        $request->has('to_date') ? $to_date = $request->get('to_date') . ' 23:59:59' : $to_date = date("Y-m-d H:i:s");
        $request->has('store_id') ? $store_id = $request->get('store_id') : $store_id = null;
        $request->has('production_station_id') ? $production_station_id = $request->get('production_station_id') : $production_station_id = null;
        $request->has('type') ? $type = $request->get('type') : $type = null;
        $request->has('select_batch') ? $select_batch = $request->get('select_batch') : $select_batch = null;


        if ($request->all() != []) {

            $batches = Batch::with('items', 'production_station', 'route')
                ->join('stations', 'batches.station_id', '=', 'stations.id')
                ->where('batches.section_id', 6)
                ->searchStatus('active')
                ->where('stations.type', 'G')
                ->where('stations.id', 92)
                ->where('graphic_found', '1')
                ->where('to_printer', '0')
                ->searchBatch($select_batch)
                ->where('min_order_date', '>', $from_date)
                ->where('min_order_date', '<', $to_date)
                ->searchStore($store_id)
                ->searchProductionStation($production_station_id)
                // ->where('to_printer', '0')
                ->select('batch_number', 'status', 'station_id', 'batch_route_id', 'store_id',
                    'to_printer', 'to_printer_date', 'min_order_date', 'production_station_id')
                ->orderBy('min_order_date')
                ->get();
//dd($batches);

            $summary = [];

        } else {

            $batches = [];

            $summary = Batch::with('production_station', 'items.rejections.user', 'items.rejections.rejection_reason_info')
                ->join('stations', 'batches.station_id', '=', 'stations.id')
                ->where('batches.section_id', 6)
                ->searchStatus('active')
                ->searchStore($store_id)
                ->where('stations.type', 'G')
                ->where('stations.id', 92)
                ->where('graphic_found', '1')
                ->where('to_printer', '0')
                ->selectRaw('production_station_id, MIN(min_order_date) as date, count(*) as count')
                ->groupBy('production_station_id')
                ->orderBy('date', 'ASC')
                ->get();
        }

        $w = new Wasatch;
        $queues = $w->getQueues();

        // $station_ids = array_unique($batches->pluck('production_station_id')->toArray());

        $stations = Station::where('is_deleted', '0')
            ->whereIn('type', ['P', 'Q'])
            ->where('section', 6)
            ->get()
            ->pluck('station_description', 'id');

        if (count($batches) > 0) {
            $store_ids = array_unique($batches->pluck('store_id')->toArray());

            $stores = Store::where('permit_users', 'like', "%".auth()->user()->id ."%")
                ->where('is_deleted', '0')
                ->where('invisible', '0')
                ->whereIn('store_id', $store_ids)
                ->orderBy('sort_order')
                ->get()
                ->pluck('store_name', 'store_id');
        } else {
            $stores = Store::list('%', '%', 'none');
        }

        $printers = $this->printers;

        $config = Printer::configurations();

        if (isset($from_date) && $from_date == '2016-06-01 00:00:00') {
            $from_date = null;
        }

        return view('graphics.print_sublimation', compact('batches', 'printers', 'queues', 'stores', 'store_id', 'config', 'select_batch',
            'stations', 'production_station_id', 'from_date', 'to_date', 'summary', 'from_date', 'to_date'));
    }

    public function printSublimation(Request $request)
    {

        if($request->get('pdf') == 1)
        {
            return $this->printSubFile(
                null, $request->get('printer'), $request->get('batch_number'),
                100, null, 0, $request->get('pdf'), false
            );
        }

        if (!file_exists($this->sort_root)) {
            return 'Cannot find Graphics Directory';
        }


        if (!$request->has('printer')) {
            Log::error('printSublimation: Printer not provided');
            return 'You did not select a printer!';
        }

        if ($request->has('batch_number') && $request->get('batch_number') != '') {
            $batch_number = $request->get('batch_number');
        }


        $before = microtime(true);
        $file = $this->getArchiveGraphic($batch_number);

        if (substr($file, 0, 5) == 'ERROR') {
            return $file;
        }

        $printer = $request->get('printer');

        $x = $this->printSubFile($file, $printer, $batch_number,
            $request->get('scale'), $request->get('minsize'), $request->get('mirror'), false, false);
        // flock($f, LOCK_UN);
        // fclose($f);

        return $x;
    }

    public function printAllSublimation(Request $request)
    {
        if (!file_exists($this->sort_root)) {
            return redirect()->back()->withErrors('Cannot find Graphics Directory');
        }

        // if (!file_exists($this->sub_dir . 'lock')) {
        //   touch($this->sub_dir . 'lock');
        // }
        //
        // $f = fopen($this->sub_dir . 'lock', 'r');
        //
        // if (!flock($f, LOCK_EX)) {
        //   Log::info('Print sublimation - Sublimation is locked');
        //   return 'Sublimation Directory Busy... Retry';
        // }

        if (!$request->has('print_batches') || !$request->has('printer')) {
            Log::error('printAllSublimation: Batches or Printer not provided');
            return 'Batches or Printer not provided';
        }

        $print_batches = $request->get('print_batches');
        $printer = $request->get('printer');

        $error = array();
        $success = array();

        foreach ($print_batches as $batch_number) {
            $file = $this->getArchiveGraphic($batch_number);

            if (substr($file, 0, 5) != 'ERROR') {
                $x = $this->printSubFile($file, $printer, $batch_number);
                if ($x == 'success') {
                    $success[] = $file . ' sent to ' . $printer;
                } else {
                    $error[] = $file . ' - ' . $x;
                }
            } else {
                $error[] = $batch_number . ' - ' . $file;
            }
        }

        // flock($f, LOCK_UN);
        // fclose($f);

        return redirect()->action('GraphicsController@showSublimation')
            ->withError($error)
            ->withSuccess($success);
    }

    public function printerConfig(Request $request)
    {
        if (!$request->has('number')) {
            return 'ERROR: Printer not specified';
        }

        if (Printer::config($request->get('number'), $request->get('station'))) {
            return 'success';
        } else {
            return 'ERROR: Update Failed';
        }
    }

    public function autoPrint()
    {
        set_time_limit(0);

        $w = new Wasatch;
        $queues = $w->getQueues();

        $config = Printer::configurations('A');

        foreach ($config as $number => $station) {
            if (isset($queues['PRINTER_' . $number]) && count($queues['PRINTER_' . $number]['STAGED_XML']) > 6) {
                unset($config[$number]);
            }
        }

        if (count($config) < 1) {
            return;
        }

        if (!file_exists($this->sort_root)) {
            Log::error('AutoPrint: Sublimation Directory Not Found');
            return;
        }

        $batches = Batch::with('route')
            ->join('stations', 'batches.station_id', '=', 'stations.id')
            ->where('batches.section_id', 6)
            ->searchStatus('active')
            ->where('stations.type', 'G')
            ->where('stations.id', 92)
            ->where('graphic_found', '1')
            ->where('to_printer', '0')
            // ->where('min_order_date', '>', $from_date)
            // ->where('min_order_date', '<', $to_date)
            ->whereIn('production_station_id', $config)
            ->select('batch_number', 'batch_route_id', 'min_order_date', 'production_station_id')
            ->orderBy('min_order_date')
            ->get();

        $counts = array();

        foreach ($config as $number => $station) {
            $counts[$station] = 0;
        }

        $stations = array_flip($config);


        foreach ($batches as $batch) {

            if ($counts[$batch->production_station_id] < 10) {

                $notInQueue = $w->notInQueue($batch->batch_number);

                if ($notInQueue != '1') {
                    continue;
                }

                if (strpos(strtolower($batch->route->csv_extension), 'soft')) {
                    $type = 'SOFT-';
                } elseif (strpos(strtolower($batch->route->csv_extension), 'hard')) {
                    $type = 'HARD-';
                } else {
                    continue;
                }

                $file = $this->getArchiveGraphic($batch->batch_number);

                $printer = $type . $stations[$batch->production_station_id];

                $result = $this->printSubFile($file, $printer, $batch->batch_number);

                if ($result == 'success') {
                    $counts[$batch->production_station_id]++;
                }
            }

            if (array_sum($counts) == (count($counts) * 10)) {
                return;
            }

        }

        return;

    }
    public function getDPIImageMagick($filename){
        $cmd = 'identify -quiet -format "%x" '.$filename;
        @exec(escapeshellcmd($cmd), $data);
        if($data && is_array($data)){
            $data = explode(' ', $data[0]);

            if($data[1] == 'PixelsPerInch'){
                return $data[0];
            }elseif($data[1] == 'PixelsPerCentimeter'){
                $x = ceil($data[0] * 2.54);
                return $x;
            }elseif($data[1] == 'Undefined'){
                return $data[0];
            }
        }
        return 72;
    }

    protected function createJsonPayload($batchNumber) {
        $batchToProcess = Batch::with('items.parameter_option.design')
            ->where('batch_number', $batchNumber)
            ->first();
        $batch = Batch::where('batch_number', $batchNumber)->first();


        $childSku = $batchToProcess->items[0]->child_sku;
        $batchHeader= strtoupper($batchToProcess->items[0]->item_description);
        $seedPageSize = substr($childSku, -4);
        $doubleSided = substr($childSku, 0 ,1) == 'D';

        $pdfParams = [
            'doubleSided' => false,
            'pageWidth' => 1570,
            'marginTop' => 0,
            'imgDpi' => 100,
            'columnLayout' => 1
        ];

        if(stripos($batchToProcess->items[0]->child_sku, "5060") !== false) {
            $pdfParams['pageHeight'] = 1740;
            $pdfParams['columnLayout'] = 1;
        } elseif (stripos($batchToProcess->items[0]->child_sku, "30") !== false) {
            $pdfParams['pageHeight'] = 1215;
            $pdfParams['columnLayout'] = 2;
        } else {
            if($batchToProcess->items[0]->parameter_option->frame_size === 0) {
                $pdfParams['pageHeight'] = 1300;
            } else {
                 $pdfParams['pageHeight'] = ($batchToProcess->items[0]->parameter_option->frame_size + 4) * 25.4 + 500;
                $pdfParams['columnLayout'] = 2;
            }

            //            Log::error('Unsupported layout ' . $batchToProcess->items[0]->child_sku);
//            return 'Unsupported Layout, unselect Pdf option';
        }

        $ordersToProcess = [];
        $dpi = $pdfParams['imgDpi'];
        foreach ($batchToProcess->items as $item) {
            if ($item->item_status !== 'shipped') {
                $options = json_decode($item->item_option, true);
                $itemImages = [];

                if(file_exists("/var/www/order.monogramonline.com/assets/images/template_thumbs/" . $item->order_id . "-" . $item->id . '.jpg')) {
                    $itemImages[] = 'http://order.monogramonline.com/assets/images/template_thumbs/' . $item->order_id . "-" . $item->id . '.jpg';
                    $dpi = $this->getDPIImageMagick("/var/www/order.monogramonline.com/assets/images/template_thumbs/" . $item->order_id . "-" . $item->id . '.jpg');
                } else {
                    if ($batch->section_id == 6 || $batch->section_id == 15 || $batch->section_id == 18) {
                        $flop = 1;
                    } else if ($batch->section_id == 3 || $batch->section_id == 10) {
                        $flop = 0;
                    } else {
                        $flop = 0;
                    }

                    $file = $this->getArchiveGraphic($batchNumber);

                    if (is_dir($file)) {
                        $graphic_path = $file . '/';
                        $file_names = array_diff(scandir($file), array('..', '.'));
                    } else {
                        $graphic_path = '';
                        $file_names[] = $file;
                    }

                    $thumb_path = base_path() . '/public_html/assets/images/graphics/';

                    foreach ($file_names as $file_name) {

                        $name = substr($file_name, 0, strpos($file_name, '.'));

                        try {
                            ImageHelper::createThumb($graphic_path . $file_name, $flop, $thumb_path . $name . '.jpg', 750);
                        } catch (\Exception $e) {
                            return "Cannot find image at all";
                        }
                    }

                    if(isset($file_names)) {
                        foreach ($file_names as $thumb) {
                            $itemImages[] = "http://order.monogramonline.com/assets/images/graphics/" . substr($thumb, 0, strpos($thumb, '.')) . '.jpg';
                            $dpi = $this->getDPIImageMagick($thumb_path . substr($thumb, 0, strpos($thumb, '.')) . '.jpg');
                            break;
                        }
                    }

                }
                $pdfParams['imgDpi'] = $dpi;
                $itemsToProcess = [
                    "id" => $item->id,
                    "sku" => $item->child_sku,
                    "quantity" => $item->item_quantity,
                    "metadata" => [
                        "image" => $itemImages
                    ]
                ];

                $ordersToProcess[] = [
                    'id' => $item->order_id,
                    'po' => $item->order_5p,
                    'creationDate' => (string) $item->created_at,
                    'items' => [$itemsToProcess]
                ];
            }
        }


        return [
            'reference' => $batchToProcess->batch_number,
            'jsonData' => [
                'pdfParams' => $pdfParams,
                'batchInfo' => [
                    'batchNumber' => $batchToProcess->batch_number,
                    'productSKU' => $childSku,
                    'batchHeader' => $batchHeader
                ],
                'orders' => $ordersToProcess,
            ],
        ];
    }

    private function printSubFile($file, $printer, $batch_number = null, $scale = 100, $minsize = null, $mirror = 0, $pdf = false, $normal = true)
    {

        if ($pdf) {
            $jsonPayLoad = $this->createJsonPayload($batch_number);

            $api = new ApiClient(null, null, null, "none");

//            echo "<pre>" .json_encode($jsonPayLoad, JSON_PRETTY_PRINT) . "</pre>";die;

            // right here
            $token = $api->getAuthenticationToken();

            $createResponse = $api->postPayload('/api/printing_batches', $token, $jsonPayLoad);



            if($createResponse->getStatusCode() == 201) {
                $createResponseData = json_decode($createResponse->getBody()->getContents(),true);
                $fileResponse = $api->getPayload(
                    $createResponseData['pdfFile'],
                    $token
                );

                if($fileResponse->getStatusCode() == 200) {
                    $fileResponseData = json_decode($fileResponse->getBody()->getContents(),true);
                    $printerNumber = explode("-", $printer)[1];

                    $stagingBaseDir = '/var/www/order.monogramonline.com/storage';

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, ApiClient::API_SERVER . '/' . $fileResponseData['contentUrl']);
                    curl_setopt($ch, CURLOPT_VERBOSE, 0);

                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HEADER, false);
                    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true); // Videos are needed to transfered in binary
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

                    $response = curl_exec($ch);
                    $filename = explode('/', curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
                    $filename = array_pop($filename);

                    curl_close($ch);
                    $result = ["file" => $response, "filename" => $filename];


                    $pdfFilePath = $stagingBaseDir . DIRECTORY_SEPARATOR . 'wasatch/staging-'. $printerNumber;

                    $fp = fopen($pdfFilePath . DIRECTORY_SEPARATOR . $batch_number . ".pdf", 'w');
                    fwrite($fp, $result['file']);
                    fclose($fp);

                    $folderPath = "/media/RDrive/" . 'SOFT-' . $printerNumber . "/";

                    shell_exec("mv " . $pdfFilePath . $batch_number . ".pdf" . " " . $folderPath . $batch_number . ".pdf");
                    $batch = Batch::where('batch_number', $batch_number)->first();

                    if (!$batch) {
                        Log::error('printSubFile: Batch not found ' . $batch_number);
                        return 'Batch not found ' . $batch_number;
                    }

                    if ($batch->to_printer != '0') {
                        Log::error('printSubFile: Batch already printed ' . $batch_number);
                        Batch::note($batch->batch_number, $batch->station_id, '6', 'Batch already printed - printSublimation');
                        return 'Batch marked as printed';
                    }

                    if ($batch) {
                        try {

                            $msg = $this->moveNext($batch, 'print', false, $normal);

                            if ($msg['error'] != '') {
                                Log::info('printSubFile: ' . $msg['error'] . ' - ' . $file);
                                Batch::note($batch->batch_number, $batch->station_id, '6', 'printSublimation - ' . $msg['error']);
                                return 'Error: ' . $msg['error'] . ' - ' . $batch_number;
                            }

                            $batch->to_printer = $printer;
                            $batch->to_printer_date = date("Y-m-d H:i:s");
                            $batch->change_date = date("Y-m-d H:i:s");
                            $batch->save();

                        } catch (\Exception $e) {
                            Log::error('printSubFile: Error moving batch ' . $file . ' - ' . $e->getMessage());
                            Batch::note($batch->batch_number, $batch->station_id, '6', 'Exception moving Batch - printSublimation');
                            return 'Error: Error moving batch ' . $batch_number;
                        }

                        Batch::note($batch->batch_number, $batch->station_id, '6', 'Graphics Sent to Printer');
                    } else {
                        Log::error('printSubFile: Batch not found ' . $batch_number);
                        return 'Error: Batch not found ' . $batch_number;
                    }


                    return "success";
                } else {
                    return "API Error " . $fileResponse->getBody()->getContents();
                }
            } else {
                return "API Error " . $createResponse->getBody()->getContents();
            }
        } else {
            $helper = new Helper();

            if (!file_exists($file)) {
                Log::error('printSubFile: File not found ' . $file);
                return 'File not found ' . $file;
            }

            if ($batch_number == null) {
                $batch_number = $this->getBatchNumber($file);
            }

            $batch = Batch::where('batch_number', $batch_number)->first();

            if (!$batch) {
                Log::error('printSubFile: Batch not found ' . $batch_number);
                return 'Batch not found ' . $batch_number;
            }

            if ($batch->to_printer != '0') {
                Log::error('printSubFile: Batch already printed ' . $batch_number);
                Batch::note($batch->batch_number, $batch->station_id, '6', 'Batch already printed - printSublimation');
                return 'Batch marked as printed';
            }

            $w = new Wasatch;
            $notInQueue = $w->notInQueue($batch_number);

            if ($notInQueue != '1') {
                return $notInQueue;
            }

            $summary_file = $this->createSummary($batch_number);
            if (!file_exists($summary_file)) {
                sleep(20);
            }
            $file_list = FileHelper::getContents($file);

            if (!is_array($file_list) || count($file_list) < 1) {
                Log::error('printSubFile: No Files Found - ' . $file);
                return 'Error: No Files Found';
            }

            #####################################
            $frameSize = null;
            $parameterOptions = Item::join('parameter_options', 'items.child_sku', '=', 'parameter_options.child_sku')
                ->where('items.is_deleted', '0')
                ->where('items.batch_number', '=', $batch_number)
                ->first();
//        if($parameterOptions->frame_size) {
            $frameSize = $parameterOptions->frame_size;

//        if($frameSize === 65) {
//            $frameSize = 0;
//        }
            // I removed this to test


            $mirror = $parameterOptions->mirror;
//        }
//        dd($file, $printer, $batch_number, $parameterOptions, $frameSize, $mirror );
            ###################################


            $list = array();
            foreach ($file_list as $path) {
//          $this->helper->jdbg("path", $path);
                $info = ImageHelper::getImageSize($path, $scale);
//          $this->helper->jdbg("info", $info);
//dd($info);
                $info['frameSize'] = $frameSize;
                $info['mirror'] = $mirror;

                if (is_array($info)) {
                    if (strpos($path, "RDrive")) {
                        $info['source'] = 'R';
                        $list[str_replace($this->sort_root, '/', $path)] = $info;
                    } else if (strpos($path, 'graphics')) {
                        $info['source'] = 'P';
                        $list[str_replace($this->old_sort_root, '/', $path)] = $info;
                    }
                } else {
                    Log::error('printSubFile: Imagesize Error - ' . $path);
                    $batch->graphic_found = '7';
                    $batch->save();
                    self::removeFile($path);
                    return 'Imagesize Error: ' . $path;
                }
            }

            if ($summary_file != false && file_exists($summary_file)) {
                $info = ImageHelper::getImageSize($summary_file);
            } else {
                Log::error('printSubFile: Batch Summary Creation Error - ' . $batch_number);
                return 'Batch Summary Creation Error';
            }

            if (is_array($info)) {
                $info['source'] = 'R';
                $info['frameSize'] = $frameSize;
//         $info['mirror'] = $mirror;
                $list[str_replace($this->sort_root, '/', $summary_file)] = $info;
            } else {
                Log::error('printSubFile: Batch Summary Imagesize Error - ' . $batch_number);
                return 'Batch Summary Imagesize Error';
            }

//dd($list, $batch_number, substr($printer,-1), substr($printer,0,4));
            $w = new Wasatch;
            $w->printJob($list, $batch_number, substr($printer, -1), substr($printer, 0, 4), null, $batch->items[0]->item_quantity);
//      dd($list, $batch_number, substr($printer,-1), substr($printer,0,4));
            Batch::note($batch->batch_number, '', '6', 'Sent to ' . $printer);

            if ($batch) {
                try {

                    $msg = $this->moveNext($batch, 'print', false, $normal);

                    if ($msg['error'] != '') {
                        Log::info('printSubFile: ' . $msg['error'] . ' - ' . $file);
                        Batch::note($batch->batch_number, $batch->station_id, '6', 'printSublimation - ' . $msg['error']);
                        return 'Error: ' . $msg['error'] . ' - ' . $batch_number;
                    }

                    $batch->to_printer = $printer;
                    $batch->to_printer_date = date("Y-m-d H:i:s");
                    $batch->change_date = date("Y-m-d H:i:s");
                    $batch->save();

                } catch (\Exception $e) {
                    Log::error('printSubFile: Error moving batch ' . $file . ' - ' . $e->getMessage());
                    Batch::note($batch->batch_number, $batch->station_id, '6', 'Exception moving Batch - printSublimation');
                    return 'Error: Error moving batch ' . $batch_number;
                }

                Batch::note($batch->batch_number, $batch->station_id, '6', 'Graphics Sent to Printer');
            } else {
                Log::error('printSubFile: Batch not found ' . $batch_number);
                return 'Error: Batch not found ' . $batch_number;
            }

            return 'success';
        }
    }

    public function printWasatch()
    {

        $w = new Wasatch;
        return $w->stagedXml();

    }

    private function findFiles($type)
    {
        if ($type = 'export') {
            $dir = $this->csv_dir;
            $field = 'csv_found';
        }

        $export_dirs = BatchRoute::where('export_dir', '!=', '')
            ->selectRaw('DISTINCT export_dir')
            ->get()
            ->toArray();

        $batches = array();

        foreach ($export_dirs as $export) {

            if (!is_dir($dir . $export['export_dir'])) {
                continue;
            }

            $directory = new \RecursiveDirectoryIterator($dir . $export['export_dir']);
            $iterator = new \RecursiveIteratorIterator($directory);

            foreach ($iterator as $info) {
                $name = $info->getFilename();

                if ($name[0] != '.') {

                    $batch_num = $this->getBatchNumber($name);

                    if ($batch_num != null) {
                        $batches[] = $batch_num;
                    }
                }
            }
        }

        if (count($batches) > 0) {
            Batch::whereIn('batch_number', $batches)
                ->where($field, '0')
                ->update([
                    $field => '1'
                ]);
        }

        return true;
    }

    public function moveNext($batch, $type, $canLook = false, $normal = true)
    {



        if($batch instanceof Batch && $normal) {
//            $ns = Batch::getNextStation('object', $batch->batch_route_id, $batch->station_id);

//            if (is_object($ns)) {
//                if (stripos($ns->station_name, "S-GGR-INDIA") !== false) return ['error' => null, 'success' => sprintf('Warning: Batch %s cannot be moved', $batch->batch_number), 'batch_number' => $batch->batch_number];
//            }



            if ($batch->station) {
                $station_name = $batch->station->station_name;
            } else {
                $station_name = 'Station not Found';
            }

            $batch = Batch::with("route")->where("batch_number", $batch->batch_number)->first();
            $stations = BatchRoute::routeThroughStations($batch->batch_route_id, $station_name);

            if(stripos($stations, "S-GGR-INDIA") !== false) {
             if(!$canLook) {
                 $batch->prev_station_id = null;
                 $batch->station_id = 264;
             } else {
                 $batch->prev_station_id = null;
                 $batch->station_id = 92;
             }
            } else {
                $batch->prev_station_id = null;
                $batch->station_id = 92;
            }

            $batch->save();

            return [
                'success' => sprintf('Batch %s Successfully Moved to %s<br>', $batch->batch_number, "station"),
                'batch_number' => $batch->batch_number,
                'error' => null
            ];
        }

        $success = null;
        $error = null;

        if (!($batch instanceof Batch)) {

            $num = $batch;

            $batch = Batch::with('route.stations_list', 'station')
                ->where('batch_number', $num)
                ->searchstatus('active')
                ->first();


            if (!$batch || count($batch) == 0) {

                $related = Batch::related($num);

                if ($related == false) {
                    return [
                        'error' => sprintf('Batch not found'),
                        'success' => $success,
                        'batch_number' => $num
                    ];
                } else {
                    $batch = $related;
                }
            }

        }

        $next_station = Batch::getNextStation('object', $batch->batch_route_id, $batch->station_id);

        if (is_object($next_station)) {
            if (stripos($next_station->station_name, "S-GGR-INDIA") !== false) return ['error' => $error, 'success' => sprintf('Warning: Batch %s cannot be moved', $batch->batch_number), 'batch_number' => $batch->batch_number];
        }

//        if(is_object($next_station) && $next_station->station_name === "S-GRPH" && Auth::user() === null) {
//            return ['error' => $error,
//                'success' => sprintf('Warning: Batch %s cannot be moved', $batch->batch_number),
//                'batch_number' => $batch->batch_number
//            ];
//        }

        if ($type == 'graphics') {
            // test if it is the first graphics station in route
            if (!($batch->route->stations_list->first()->station_id == $batch->station_id && $batch->station->type == 'G')) {
                return ['error' => $error,
                    'success' => sprintf('Warning: Batch %s not in first graphics station', $batch->batch_number),
                    'batch_number' => $batch->batch_number
                ];
            }

        } else if ($type == 'production') {

            if (!($batch->station->type == 'G' && $next_station->type == 'P')) {
                return [
                    'error' => sprintf('Batch %s not moving from graphics to production - ' .
                        $batch->station->station_name . ' ' . $batch->change_date . '<br>',
                        $batch->batch_number),
                    'success' => $success,
                    'batch_number' => $batch->batch_number
                ];
            }

            if ($batch->status != 'active' && $batch->status != 'back order') {
                return [
                    'error' => sprintf('Batch %s status is %s', $batch->batch_number, $batch->status),
                    'success' => $success,
                    'batch_number' => $batch->batch_number
                ];
            }

        } else if ($type == 'print') {

            if ($next_station == null || $next_station->station_name != 'S-GRP') {
                return ['error' => 'Batch not moved, next station not printer station',
                    'success' => $success,
                    'batch_number' => $batch->batch_number
                ];
            }
        }

        if ($next_station && $next_station->id != '0') {
            $batch->prev_station_id = $batch->station_id;
            $batch->station_id = $next_station->id;
            $batch->save();
            $success = sprintf('Batch %s Successfully Moved to %s<br>', $batch->batch_number, $next_station->station_name);
        } else {
            $error = sprintf('Batch %s has no further stations on route <br>', $batch->batch_number);
        }

        return ['error' => $error, 'success' => $success, 'batch_number' => $batch->batch_number];
    }

    private function getBatchNumber($filename)
    {
        // if (substr($filename, -4, 1) == '.') {
        //   $filename = substr($filename, 0, -4);
        // }

        $ex = explode('-', $filename);

        if (is_numeric($ex[0])) {
            return $ex[0];
        } elseif (isset($ex[1])) {
            return $ex[0] . '-' . $ex[1];
        } else {
            return null;
        }
    }

    private function recurseCopy($src, $dst, $rename = 0)
    {

        if (is_dir($src)) {

            $dir = opendir($src);

            mkdir($dst);

            while (false !== ($file = readdir($dir))) {

                if (($file != '.') && ($file != '..')) {

                    if (is_dir($src . '/' . $file)) {

                        $this->recurseCopy($src . '/' . $file, $dst . '/' . $file, $rename);

                    } else {

                        try {

                            if (substr($file, -4) == '.tmp' || substr($file, -3) == '.db') {
                                unlink($src . '/' . $file);
                                continue;
                            }

                            if ($rename) {
                                $new_file = $this->uniqueFileName($dst, $file, null, 1);
                            } else {
                                $new_file = $file;
                            }

                            copy($src . '/' . $file, $dst . '/' . $new_file);

                        } catch (\Exception $e) {
                            Log::error('recurseCopy: Cannot copy file ' . $dir . ' - ' . $e->getMessage());
                        }
                    }
                }
            }
            closedir($dir);
        } else {

            if ($rename) {
                if (strrpos($dst, '/')) {
                    $file = substr($dst, strrpos($dst, '/') + 1);
                    $dir = substr($dst, 0, strlen($dst) - strlen($file));
                } else {
                    $file = $dst;
                    $dir = '/';
                }

                $new_file = $this->uniqueFileName($dir, $file, null, 1);

            } else {
                $new_file = $dst;
                $dir = '';
            }

            try {
                copy($src, $dir . $new_file);
            } catch (\Exception $e) {
                Log::error('recurseCopy: Cannot copy file ' . $dir . ' - ' . $e->getMessage());
            }
        }
    }

    private function recurseAppend($src, $dst)
    {
        $start = microtime(true);
        if (is_dir($src)) {
            $dir = opendir($src);
            if (!is_dir($dst)) {
                mkdir($dst);
            }
            while (false !== ($file = readdir($dir))) {
                if (($file != '.') && ($file != '..')) {
                    if (is_dir($src . '/' . $file)) {
                        $this->recurseAppend($src . '/' . $file, $dst . '/' . $file);
                    } else {
                        try {
                            copy($src . '/' . $file, $dst . '/' . $file);
                        } catch (\Exception $e) {
                            Log::error('recurseAppend: Cannot copy file ' . $dir . ' - ' . $e->getMessage());
                        }
                    }
                }
            }
            closedir($dir);

        } else {
            if (file_exists($dst)) {
                $file = substr($dst, strrpos($dst, '/') + 1);
                $dir = substr($dst, 0, strlen($file));

                $file = $this->uniqueFilename($dir, $file);
            } else {
                $file = $dst;
            }
            try {
                copy($src, $file);
            } catch (\Exception $e) {
                Log::error('recurseAppend: Cannot copy file ' . $src . ' - ' . $e->getMessage());
            }
        }

    }

    public static function removeFile($path)
    {

        if (!file_exists($path)) {
            return true;
        }

        if (!is_dir($path)) {
            try {
                return unlink($path);
            } catch (\Exception $e) {
                Log::error('Graphics removeFile: cannot remove directory ' . $path);
                return false;
            }
        } else {

            if (substr($path, strlen($path) - 1, 1) != '/') {
                $path .= '/';
            }

            $files = glob($path . '*', GLOB_MARK);
            foreach ($files as $file) {
                if (is_dir($file)) {
                    self::removeFile($file);
                } else {
                    try {
                        unlink($file);
                    } catch (\Exception $e) {
                        Log::error('Graphics removeFile: cannot remove file ' . $file);
                        return false;
                    }
                }
            }

            return rmdir($path);
        }

    }

    public function uniqueFilename($dir, $filename, $suffix = '-COPY', $batch_rename = 0)
    {

        if (strpos($filename, '.')) {
            $file = substr($filename, 0, strpos($filename, '.'));
            $ext = substr($filename, strpos($filename, '.'));
        } else {
            $file = $filename;
            $ext = '';
        }

        if ($batch_rename) {
            $batch = $this->getBatchNumber($file);
            if ($batch) {
                $file = $batch;
            }
        } else if (strpos($filename, $suffix)) {
            $file = substr($filename, 0, strpos($filename, $suffix));
        }

        try {
            $num = count(glob($dir . $file . '*'));

            if ($num > 0) {

                return $file . $suffix . '-' . $num . $ext;

            } else {

                return $file . $ext;
            }

        } catch (\Exception $e) {
            Log::error('uniqueFilename: Error creating name ' . $filename . ' - ' . $e->getMessage());
            return false;
        }
    }

    private function findErrorFiles()
    {

        if (!file_exists($this->error_dir)) {
            mkdir($this->error_dir);
        }

        $error_list = array_diff(scandir($this->error_dir), array('..', '.'));

        $error_files = array();

        foreach ($error_list as $dir) {

            $batch_number = $this->getBatchNumber($dir);

            $error_files[$batch_number][] = $this->error_dir . $dir;

            $batch = Batch::where('batch_number', $batch_number)->first();

            if (count($batch) > 0 && $batch->graphic_found != 'Pendant Error') {

                $batch->graphic_found = '4';
                $batch->save();

                Batch::note($batch->batch_number, $batch->station_id, '6', 'Graphics Error File Found');
            }
        }

        $this->removeSure3d(array_keys($error_files));

        return $error_files;
    }

    public function sortFiles(Request $request)
    {
        $helper = new Helper();
        if (!file_exists($this->sort_root)) {
            Log::error('Sortfiles: Cannot find Graphics Directory');
            return;
        }

        $error_files = $this->findErrorFiles();
//        $helper->jdbg("error_files", $error_files);
        $manual_files = $this->getManual('list');
//        $helper->jdbg("manual_files", $manual_files);
        $dir_list = array_diff(scandir($this->main_dir), array('..', '.', 'lock'));
//        $helper->jdbg("dir_list", $dir_list);

        $id = rand(1000, 9999);


        /* TEMP REMOVED
        if ($request->has("location")) {
            Log::info("------- SORT WAS CALLED BY: " . $request->get("location"));
        }
        */

        if (count($dir_list) > 0) {
//          echo "<pre>";
//              print_r($dir_list);
//          echo "</pre>";
        //    Log::info('STARTING SORTFILES ' . $id);
        } else {
          //  Log::info("Nothing to sort from " . $this->main_dir);
         //   echo "Nothing to sort from " . $this->main_dir;
        }


        foreach ($dir_list as $dir) {

            $result = shell_exec("lsof | grep " . $this->main_dir . $dir . "*");

            if ($result != null || substr($dir, -5) == '.lock' || !file_exists($this->main_dir . $dir) ||
                !is_writable($this->main_dir . $dir) || file_exists($this->main_dir . $dir . "/lock") ||
                file_exists($this->main_dir . $dir . '.lock')) {
                Log::info('1.Sortfiles Locked -------sortFiles ' . $id . ': ' . $this->main_dir . $dir);
                continue;
            }

            touch($this->main_dir . $dir . '.lock');

            $batch_number = $this->getBatchNumber($dir);

            if (isset($error_files[$batch_number])) {
                foreach ($error_files[$batch_number] as $error_file) {
                    Log::info('sortFiles ' . $id . ':  Remove Error File ' . $error_file);
                    if (!$this->removeFile($error_file)) {
                        unlink($this->main_dir . $dir . '.lock');
                        continue;
                    }
                }
            }

            if (isset($manual_files[$batch_number])) {
                Log::info('sortFiles ' . $id . ':  Remove Manual File ' . $manual_files[$batch_number]);
                if (!$this->removeFile($manual_files[$batch_number])) {
                    unlink($this->main_dir . $dir . '.lock');
                    continue;
                }
            }
            $batch = Batch::with('route')
                ->where('batch_number', $batch_number)
                ->first();

            try {
                $to_file = $this->uniqueFilename(self::$archive, $dir);

                if (file_exists(self::$archive . $to_file)) {
                    Log::error('sortFiles ' . $id . ': File already in archive ' . self::$archive . $to_file);
                }
                $this->recurseAppend($this->main_dir . $dir, self::$archive . $to_file);

                if ($batch) {
                    Batch::note($batch->batch_number, $batch->station_id, '6', 'Moving to archived');


                    $batch->archived = '1';
                    $batch->save();
                    Batch::note($batch->batch_number, $batch->station_id, '6', 'Graphics Archived');
                }
            } catch (\Exception $e) {
                Log::error('sortFiles ' . $id . ': Error archiving file ' . $to_file . ' - ' . $e->getMessage());
                if ($batch) {
                    $batch->archived = '2';
                    $batch->save();
                    Batch::note($batch->batch_number, $batch->station_id, '6', 'Graphics Archiving Error');
                }
                continue;
            }

            if ($batch && $batch->route) {
                $graphic_dir = $batch->route->graphic_dir . '/';
            } else {
                $graphic_dir = 'MISC/';
                Log::error('sortFiles ' . $id . ':  Graphic Directory Not Set ' . $dir . "in route name: ");
                if ($batch) {
                    Batch::note($batch->batch_number, $batch->station_id, '6', 'Graphic Directory Not Set');
                }
            }


            if (!file_exists($this->sort_root . $graphic_dir)) {
                mkdir($this->sort_root . $graphic_dir);
            }

            $result = shell_exec("lsof | grep " . $this->sort_root . $graphic_dir . $dir . "*");

            if ($result != null) {
                Log::info('sortFiles ' . $id . ':  Destination File in use ' . $graphic_dir . $dir);
                if ($batch) {
                    Batch::note($batch->batch_number, $batch->station_id, '6', 'Error moving file to graphic directory - Destination File in use');
                }
                continue;
            }

            try {

                //$this->removeFile($this->sort_root . $graphic_dir . $dir);
                //$moved = @rename($this->main_dir . $dir, $this->sort_root . $graphic_dir . $dir);

                if ($graphic_dir != '/') {
                    $this->recurseAppend($this->main_dir . $dir, $this->sort_root . $graphic_dir . $dir);
                }
                $this->removeFile($this->main_dir . $dir);

                if ($batch) {
                    $batch->graphic_found = '1';
                    $batch->to_printer = '0';
                    $batch->save();

                    Batch::note($batch->batch_number, $batch->station_id, '6', "Graphic $dir moved to $graphic_dir directory");
                    $msg = $this->moveNext($batch, 'graphics');
                }

            } catch (\Exception $e) {
                Log::error('sortFiles ' . $id . ':  Error moving file to graphic directory ' . $dir . ' ' . $e->getMessage());

                if ($batch) {
                    $batch->graphic_found = '2';
                    $batch->save();

                    Batch::note($batch->batch_number, $batch->station_id, '6', 'Error moving file to graphic directory');
                }
                continue;
            }

            if ($batch) {
                Batch::note($batch->batch_number, $batch->station_id, '6', 'Moved to next station');

                try {
                    Log::info("2.Sortfiles " . $batch->batch_number . ' Now -------sortFiles ' . $id . ': ' . $this->main_dir . $dir);

                    if ($msg['error'] != '') {
                        Log::info('sortFiles ' . $id . ': ' . $msg['error'] . ' - ' . $dir);
                        Batch::note($batch->batch_number, $batch->station_id, '6', 'Graphics Sort - ' . $msg['error']);
                    }

                } catch (\Exception $e) {
                    Log::error('sortFiles ' . $id . ': Error moving batch ' . $dir . ' - ' . $e->getMessage());
                    Batch::note($batch->batch_number, $batch->station_id, '6', 'Exception moving Batch - Graphics Sort');
                }
            } else {
                Log::error('sortFiles ' . $id . ': Batch not found ' . $dir);
            }

            try {
                unlink($this->main_dir . $dir . '.lock');
            } catch (\Exception $e) {
                Log::error('sortFiles ' . $id . ': Lock could not be unlinked ' . $dir);
            }
        }

        if (count($dir_list) > 0) {
            Log::info('ENDING SORTFILES ' . $id);
        }

        Design::updateGraphicInfo();

        // FileHelper::removeEmptySubFolders($this->sub_dir);

//      return ;
    }

    public function cleanUp()
    {
        $directories = BatchRoute::groupBy('graphic_dir')->get()->pluck('graphic_dir');

        foreach ($directories as $directory) {
            $file_list = null;

            if ($directory != '') {
                $file_list = array_diff(scandir($this->sort_root . $directory), array('..', '.'));

                foreach ($file_list as $file) {
                    $batch_numbers[$this->getBatchNumber($file)] = $file;
                }

                $batches = Batch::whereIn('batch_number', $batch_numbers)->get();

                foreach ($batches as $batch) {
                    if ($batch && ($batch->status == 'complete' || $batch->status == 'empty')) {
                        $this->removeFile($this->sort_root . $directory . $file);
                    }
                }
            }
        }
    }

    public function reprintGraphic(Request $request)
    {

        if (!$request->has('name')) {
            Log::error('reprintGraphic: Name not Set');
            return 'ERROR name not Set';
        }

        $result = $this->reprint($request->get('name'), $request->get('directory'));

        if ($result != 'success' || !$request->has('goto')) {
            return $result;
        } else {
            return redirect()->action('GraphicsController@showSublimation', ['select_batch' => $request->get('name')]);
        }
    }

    public function reprint($name, $directory = null)
    {

        if (!file_exists($this->sort_root)) {
            return 'ERROR Cannot find Graphics Directory';
        }

        $name = trim($name);

        $filename = $this->getArchiveGraphic($name);

        $batch_number = $this->getBatchNumber($name);

        $batch = Batch::with('route.stations_list')
            ->where('batch_number', $batch_number)
            ->first();

        if ($batch) {
            Batch::note($batch->batch_number, $batch->station_id, '10', 'Graphic retrieved from archive and sent to graphic directory');
            $batch->to_printer = '0';
            $batch->save();
        }

        /*                   NOTE TO SELF
         * The directory is being retrieved by the request URL,
         * so if that does not exist, it does not move the graphic to the right folder
         *
         * WHICH causes it to return a `success` and not moving on to the next stage
         */
        if ($directory != '') {
            try {
                Log::info('Reprint: ' . $this->sort_root . $directory . substr($filename, strrpos($filename, '/')));
                $this->recurseCopy($filename, $this->sort_root . $directory . '/' . substr($filename, strrpos($filename, '/')));
                return 'success';
            } catch (\Exception $e) {
                Log::error('reprintGraphic: Could not copy File from Archive - ' . $e->getMessage());
                return 'ERROR not copied from Archive';
            }
        } else {
            $batch->station_id = $batch->route->stations_list->first()->station_id;
            $batch->save();
            $this->moveNext($batch, 'graphics', true);
            return 'success';
        }
    }

    public function reprintBulk(Request $request)
    {

        set_time_limit(0);

        $batch_numbers = $request->get('batch_number');

        $success = array();
        $error = array();

        if (is_array($batch_numbers)) {

            foreach ($batch_numbers as $batch_number) {

                $msg = $this->reprint($batch_number, $request->get('directory'));

                if ($msg == 'success') {
                    $success[] = $batch_number . ' Graphic in Print Sublimation';
                }

                if (substr($msg, 0, 5) == 'ERROR') {
                    $error[] = $batch_number . ' - ' . $msg;
                }
            }

            return redirect()->back()
                ->with('success', $success)
                ->withErrors($error);

        } else {
            return redirect()->back()->withErrors('No Batches Selected');
        }

    }

    public function getArchiveGraphic($name)
    {


        $list = glob(self::$archive . $name . "*");
        $files = array();

            if (count($list) < 1) {
                /*
                 * Note, can be solved by uploading the graphic again
                 */


                $batch = Batch::with('items.order.store', 'items.rejections.user', 'items.rejections.rejection_reason_info',
                    'items.spec_sheet', 'items.product', 'station', 'route', 'section', 'store', 'summary_user')
                    ->where('is_deleted', 0)
                    ->where('batch_number', $name)
                    ->get()[0];
                foreach ($batch->items as $item) {
                    $item_name = $item->order_id . "-" . $item->id . '.jpg';
                //    $path = "/var/www/order.monogramonline.com" . '/public_html/assets/images/template_thumbs/' . $item->order_id . "-" . $item->id . '.jpg';
                    $path = "/var/www/order.monogramonline.com" . '/public_html/assets/images/product_thumb/' . $item->item_sku . '.jpg';

                    if (file_exists($path)) {
                        if (copy($path, self::$archive . $name)) {
                            // Smoothly);
                            $list2 = glob(self::$archive . $name . "*");
                            if (count($list2) >= 1) {
                                foreach ($list2 as $file) {
                                    $files[filemtime($file)] = $file;
                                }

                                ksort($files);

                                return array_pop($files);
                            } else {
                                $msg = "reprintGraphic: Error file was not found.... after checking twice";
                                Log::error($msg);
                                return "Not found after trying to fix archive lost.";
                            }
                        }
                    } else {
                        $msg = "reprintGraphic: No thumb exist for " . $item->order_id . "-" . $item->id;
                        Log::error($msg);
                        return $msg;
                    }
                }

//          Log::error('reprintGraphic: File not found in Archive ' . $name);
//          return 'ERROR not found in Archive!';

                Log::error('reprintGraphic: File not found in Archive/could not save ' . $name);
                return 'ERROR not found in Archive/could not get at all!';
            }

        foreach ($list as $file) {
            $files[filemtime($file)] = $file;
        }

        ksort($files);

        return array_pop($files);

    }

    public function resizeSure3d($item_id)
    {

        //get item
        $item = Item::with('order')->find($item_id);

        if (!$item) {
            return 'Item not Found';
        }

        //find sure3d file
        $filename = '/media/RDrive/Sure3d/' . $item->order->short_order . '-' . $item->id . '.eps';
        if (!file_exists($filename)) {
            $filename = '/media/RDrive/Sure3d/' . $item->order->short_order . '-' . $item->id . '.eps';
            if (!file_exists($filename)) {
                return 'Sure3d File not found';
            }
        }

        //load into imagick & resize
        set_time_limit(0);
        $image = new \Imagick($filename);
        $geo = $image->getImageGeometry();

        $sizex = $geo['width'] * 1.15;
        $sizey = $geo['height'] * 1.15;

        $image->scaleImage($sizex, $sizey);

        //save to sublimation directory
        $fp = '/media/RDrive/sublimation/' . $item->batch_number . '-' . $item->id . '.eps';
        $image->writeImage($fp);

        return 'done';
    }

    public function resizeBatch($batch_id)
    {

        //find graphic file in archive
        $name = trim($batch_id);

        $list = glob(self::$archive . $name . "*");
        $files = array();

        if (count($list) < 1) {
            $list = glob($this->old_archive . $name . "*");
            if (count($list) < 1) {
                return 'ERROR not found in Archive';
            }
        }

        foreach ($list as $file) {
            $files[filemtime($file)] = $file;
        }

        ksort($files);

        $file = array_pop($files);

        if (is_dir($file)) {
            $filelist = glob($file . "/*");
        } else {
            $filelist = [$file];
        }

        $count = 0;

        foreach ($filelist as $filename) {
            //load into imagick & resize
            if (substr($filename, -3) != 'eps') {
                continue;
            }
            set_time_limit(0);
            $image = new \Imagick($filename);
            $geo = $image->getImageGeometry();

            $sizex = $geo['width'] * 1.15;
            $sizey = $geo['height'] * 1.15;

            $image->scaleImage($sizex, $sizey);

            //save to sublimation directory
            $fp = '/media/RDrive/sublimation/' . $batch_id . '-' . $count . '.eps';
            $image->writeImage($fp);

            $count++;
        }
        return 'done';
    }

    public function resizeBatchMaxSize($batch_id, $max_size, $flop = 1)
    {

        $result = false;

        //find graphic file in archive
        $name = trim($batch_id);

        $list = glob(self::$archive . $name . "*");
        $files = array();


        if (count($list) < 1) {
            return 'ERROR not found in Archive';
        }

        foreach ($list as $file) {
            $files[filemtime($file)] = $file;
        }

        ksort($files);

        $file = array_pop($files);

        if (is_dir($file)) {
            $filelist = glob($file . "/*");
        } else {
            $filelist = [$file];
        }

        $dir = $this->uniqueFilename(self::$archive, $batch_id, '-RESIZED');
        mkdir(self::$archive . $dir);

        foreach ($filelist as $filename) {

            $info = ImageHelper::getImageSize($filename);

            if ($info['width'] > $info['height']) {
                $factor = $max_size / $info['width'];
            } else if ($info['height'] > 0) {
                $factor = $max_size / $info['height'];
            } else {
                continue;
            }

            if ($factor == 1) {
                continue;
            }

            set_time_limit(0);
            $image = new \Imagick($filename);
            $geo = $image->getImageGeometry();

            $sizex = $geo['width'] * $factor;
            $sizey = $geo['height'] * $factor;

            $image->scaleImage($sizex, $sizey);

            if ($flop == true) {
                $image->flopImage();
            }

            $fp = self::$archive . $dir . substr($filename, strrpos($filename, '/'));
            $image->writeImage($fp);
            $result = true;
        }

        @Batch::note($batch_id, 0, '11', 'Graphics Resized to ' . $max_size);
        return $result;
    }

    public function resizeNaticoImages()
    {

        $s = new Sure3d;

        $batches = Batch::with('items')
            ->where('store_id', 'natico-wholesale')
            ->searchStatus('active')
            ->get();

        foreach ($batches as $batch) {
            $list = glob(self::$archive . $batch->batch_number . "*");

            if (count($list) < 1) {
                Log::info('No Natico Batch Found: ' . $batch->batch_number);
                continue;
            }

            foreach ($list as $file) {
                if (!strpos($file, 'RESIZED')) {
                    $this->removeFile($file);
                }
            }
            // $list = glob(self::$archive . $batch->batch_number . '-RESIZED' . "*");
            //
            // if (count($list) > 0) {
            //   continue;
            // }
            //
            // Log::info('Resize: ' . $batch->batch_number);
            //
            // $resized = $this->resizeBatchMaxSize($batch->batch_number, 17);
            //
            // if ($resized == 'ERROR not found in Archive') {
            //   if (!$batch || !$batch->items || count($batch->items) < 1) {
            //     continue;
            //   }
            //
            //   foreach($batch->items as $item) {
            //     $s->getImage($item->id, 2);
            //   }
            //
            //   $created = $s->exportBatch($batch);
            //
            //   // if ($created) {
            //   //   $resized = $this->resizeBatchMaxSize($batch->batch_number, 17);
            //   // }
            // }
        }

        return 'done';
    }

    private function createSummary($batch_number)
    {

        if (auth()->user()) {
            $url = url("/graphics/sub_summary/" . $batch_number);
        } else {
            $url = url("/graphics/sub_screenshot/" . $batch_number);
        }
        
        if (!file_exists($this->sort_root . 'summaries/')) {
            mkdir($this->sort_root . 'summaries/');
        }

        $file = $this->sort_root . 'summaries/' . $batch_number . '.pdf';

        set_time_limit(0);

        //$count = 1;
/**
        do {
            try {
                $x = shell_exec('xvfb-run --server-args="-screen 0, 1024x768x24" wkhtmltopdf ' . $url . ' ' . $file . " > /dev/null 2>&1 &");
            } catch (\Exception $e) {
                Log::error('Error creating sublimation summary for batch ' . $batch_number);
                Log::error($e->getMessage());
            }
            $count++;
        } while (!file_exists($file) && $count < 3);
**/

        try{
            $x = shell_exec('xvfb-run --server-args="-screen 0, 1024x768x24" wkhtmltopdf ' . $url . ' ' . $file . " > /dev/null 2>&1");
        }catch (\Exception $e){
            Log::error('Error creating sublimation summary for batch ' . $batch_number);
            Log::error($e->getMessage());
        }
        

        try {
            $y = shell_exec("pdfcrop $file $file > /dev/null 2>&1");
        } catch (\Exception $e) {
            Log::error('Error cropping sublimation summary for batch ' . $batch_number);
            Log::error($e->getMessage());
            Log::error($y);
        }

//        try{
//            $processFileExists = false;
//            $commandRunning = false;
//
//            do {
//                if (!file_exists($file . '.pid') && $commandRunning == false){
//                    $x = shell_exec('touch '. $file . '.pid'. ' && ' . 'xvfb-run --server-args="-screen 0, 1024x768x24" wkhtmltopdf ' . $url . ' ' . $file . '> /dev/null 2>&1 &' . ' && ' . 'rm -f' . $file . '.pid');
//                    $processFileExists = true;
//                    $commandRunning = true;
//                } elseif (!file_exists($file . '.pid') && $commandRunning == true) {
//                    $processFileExists = false;
//                    $commandRunning = false;
//                    break;
//                }
//            } while ($processFileExists === true);
//        } catch(\Exception $e) {
//            Log::error('Error creating sublimation summary for batch ' . $batch_number);
//            Log::error($e->getMessage());
//        }
//
//	    try{
//	        $processFileExists = false;
//            $commandRunning = false;
//
//            do {
//		        if (!file_exists($file . '.pid') && $commandRunning == false){
//        			$y = shell_exec('touch '. $file . '.pid' . ' && ' . 'pdfcrop ' . $file . ' ' . $file . '> /dev/null 2>&1 &' . ' && ' . 'rm -f' . $file . '.pid');
//		        	$processFileExists = true;
//        			$commandRunning = true;
//		        } elseif (!file_exists($file . '.pid') && $commandRunning == true) {
//    			    $processFileExists = false;
//	    	    	$commandRunning = false;
//    		    	break;
//		        }
//    		} while ($processFileExists === true);
//        } catch(\Exception $e) {
//	        Log::error('Error cropping sublimation summary for batch ' . $batch_number);
//        	Log::error($e->getMessage());
//        	Log::error($y);
//        }
/**
        $count = 1;
        do {
            try {
                $y = shell_exec("pdfcrop $file $file > /dev/null 2>&1 &");
            } catch (\Exception $e) {
                Log::error('Error cropping sublimation summary for batch ' . $batch_number);
                Log::error($e->getMessage());
                Log::error($y);
            }
            $count++;
        } while (!strpos($y, 'page written') && $count < 3);
**/
        if (!file_exists($file)) {
            Log::error('Error sublimation summary does not exist for batch ' . $batch_number);
            return false;
        }

        return $file;
    }

    public function deleteFile($graphic_dir, $file)
    {
        $removed = $this->removeFile($this->sort_root . $graphic_dir . '/' . $file);
        if ($removed) {
            return redirect()->back()->withSuccess($file . ' Removed from ' . $graphic_dir);
        } else {
            return redirect()->back()->withErrors('Error Removing ' . $file . ' from ' . $graphic_dir);
        }
    }

    public function downloadSure3d()
    {
        try {
            $s = new Sure3d;
            $s->download();
        } catch (\Exception $e) {
            Log::error('downloadSure3d: ' . $e->getMessage());
        }

        return;
    }

    public function downloadSure3dByItemId(Request $request)
    {
//        dd($request->all(), $request->item_id);
        try {
            $s = new Sure3d;
            $s->downloadSure3dByItemId($request->item_id);
        } catch (\Exception $e) {
            Log::error('downloadSure3d: ' . $e->getMessage());
        }

        return;
    }

    public function viewGraphic(Request $request)
    {

        $batch_number = $request->get('batch_number');

        $batch = Batch::where('batch_number', $batch_number)->first();

        if (!$batch) {
            Log::error('viewGraphic: Batch not found ' . $batch_number);
            return redirect()->back()->withErrors('ERROR Batch not found');
        }

        if ($batch->section_id == 6 || $batch->section_id == 15 || $batch->section_id == 18) {
            $flop = 1;
        } else if ($batch->section_id == 3 || $batch->section_id == 10) {
            $flop = 0;
        } else {
            $flop = 0;
            //return redirect()->back()->withErrors('ERROR Graphics format cannot be rendered');
        }

        $file = $this->getArchiveGraphic($batch_number);

        if (is_dir($file)) {
            $graphic_path = $file . '/';
            $file_names = array_diff(scandir($file), array('..', '.'));
        } else {
            $graphic_path = '';
            $file_names[] = $file;
        }

        $thumb_path = base_path() . '/public_html/assets/images/graphics/';

        foreach ($file_names as $file_name) {

            $name = substr($file_name, 0, strpos($file_name, '.'));

            try {
                ImageHelper::createThumb($graphic_path . $file_name, $flop, $thumb_path . $name . '.jpg', 750);
            } catch (\Exception $e) {
                Log::error('viewgraphic: ' . $e->getMessage());
                return view('graphics.view_graphic', compact('batch_number', 'file', 'file_names', 'files'))
                    ->withErrors('Could not create image: ' . $e->getMessage());
            }
        }

        return view('graphics.view_graphic', compact('file_names', 'files', 'file', 'batch_number'));
    }

    public function uploadFile(Request $request)
    {
        #1. Get Batch current station.
        #2. Check is item shipped.
        #3. If not Move batch to graphic station.
        #4. Upload Graphic to Archive.

        if ($request->has('item_id') && $request->has('batch_number')) {
            $request->has('batch_number') ? $select_batch = $request->get('batch_number') : $select_batch = null;
            $request->has('item_id') ? $item_id = $request->get('item_id') : $item_id = null;

//            $batche = Batch::with('items', 'production_station', 'route', 'route.stations_list')
//                ->join('stations', 'batches.station_id', '=', 'stations.id')
//                ->searchStatus('active')
//                ->where('stations.type', 'G')
//                ->searchBatch($select_batch)
//                ->get();
            $batche = Batch::with('items', 'route.stations_list')
                ->where('batch_number', $select_batch)
                ->get();
//dd($item_id, $select_batch, $batche);
            foreach ($batche as $batch) {
                foreach ($batch->items as $items) {
                    if ($items->id == $item_id) {
                        if (empty($items->tracking_number)) {
                            $thumb = '/assets/images/template_thumbs/' . $items->order_id . "-" . $items->id . '.jpg';
                            ###### Save in Item Table ######
                            $items->tracking_number = NULL;
                            $items->item_status = 1;
                            $items->reached_shipping_station = 0;
                            $items->item_thumb = 'http://order.monogramonline.com' . $thumb;
                            $items->save();

                            ###### Save in Batch Table ######
                            //  dd($batch->route->stations_list->first()->station_id);
                            $batch->status = 2;
                            $batch->section_id = 6;
                            $batch->station_id = 92;
                            $batch->prev_station_id = $batch->route->stations_list->first()->station_id;
                            $batch->export_count = 1;
                            $batch->csv_found = 0;
                            $batch->graphic_found = 1;
                            $batch->to_printer = 0;
                            $batch->to_printer_date = null;
                            $batch->archived = 1;
                            $batch->save();
//dd($batch);
                            ###### Upload File #####
                            $filename = $batch->batch_number . '.' . $request->file('upload_file')->getClientOriginalExtension();
                            $filename = $this->uniqueFilename($this->sort_root . "archive/", $filename);
                            if (move_uploaded_file($request->file('upload_file'), $this->sort_root . "archive/" . $filename)) {
                                try {
                                    ImageHelper::createThumb($this->sort_root . "archive/" . $filename, 0, base_path() . '/public_html' . $thumb, 350);
                                } catch (\Exception $e) {
                                    Log::error('Batch uploadFile createThumb: ' . $e->getMessage());
                                }

                                Batch::note($batch->batch_number, $batch->station_id, '111', 'Graphic Uploaded to Main');
                                return redirect()->back()->withInput()->withSuccess('Graphic Uploaded for Batch ' . $batch->batch_number);
                            }
                        }
                    }
                }
            }
//            dd("Before",$request->all(), $batche, $batche[0]->items[0],$batche[0]->items[0]->id);
            return redirect()->back()->withInput()->withErrors('Batch number required...');
        }
//dd("After",$request->all());
#########################################
        if (!$request->has('batch_number')) {
            return redirect()->back()->withInput()->withErrors('Batch number required');
        }

        $batch = Batch::where('batch_number', $request->get('batch_number'))->first();

        if (!$batch) {
            return redirect()->back()->withInput()->withErrors('Batch not found');
        }

        $filename = $batch->batch_number . '.' . $request->file('upload_file')->getClientOriginalExtension();

        $filename = $this->uniqueFilename($this->main_dir, $filename);

        if (move_uploaded_file($request->file('upload_file'), $this->main_dir . $filename)) {
            Batch::note($batch->batch_number, $batch->station_id, '11', 'Graphic Uploaded to Main');
            return redirect()->back()->withInput()->withSuccess('Graphic Uploaded for Batch ' . $batch->batch_number);
        }

        Batch::note($batch->batch_number, $batch->station_id, '11', 'Error uploading graphic');
        return redirect()->back()->withInput()->withErrors('Error uploading graphic for Batch ' . $batch->batch_number);

    }

    public function uploadFileUsingLink(Request $request)
    {

        $this->uploadFileFromLink($request);

//        return response()->json(
//            [
//                "Status" => true,
//                "Message" => "Successful",
//                "Data" => $request->all(),
//                "Link_Decoded" => urldecode($request->get("link"))
//            ]
//        );
        return redirect()->back()->withInput()->withSuccess("Successfully fetched graphic from Zakeke");
    }

    public function uploadFileFromLinkMass(Request $request)
    {

        $failed = 0;
        foreach (request()->get("batch_number") as $batch_number) {
            $batche = Batch::with('items', 'route.stations_list')
                ->where('batch_number', $batch_number)
                ->get();

            foreach ($batche as $batch) {
                foreach ($batch->items as $items) {
                    $what = $this->uploadFileFromLink($request, $items->id, $batch->batch_number);
                    if ($what === null) {
                        $failed++;
                    }
                }
            }
        }

        if (($failed) >= 1) {
            return redirect()->back()->withInput()->withSuccess('Successfully downloaded and uploaded graphic for batches ' . implode(", ", $request->get("batch_number"))
                . "  The following batches had no images to be fetched: " . implode(", ", $request->get("batch_number")));
        } else {
            return redirect()->back()->withInput()->withSuccess('Successfully downloaded and uploaded graphic for batches ' . implode(", ", $request->get("batch_number")));
        }

    }

    public function uploadFileFromLink(Request $request, $itemId = null, $batchNumber = null)
    {
        #1. Get Batch current station.
        #2. Check is item shipped.
        #3. If not Move batch to graphic station.
        #4. Upload Graphic to Archive.


//        $options = json_decode($item->item_option, true);
//
//        $imageLink = $options['Custom_EPS_download_link'] ?? 0;


        if($request->has("fetch_link_from_zakeke_cli")) {
            $shortOrder = $request->get("short_order");


            if(!$request->has("pws")) {
                $response = shell_exec("zakeke -user 44121 -key 2d91PpFG6QJ0NmXsImWCXSzAMPCiRwMuX6D7DUHSIcM. -data " . $request->get("short_order"));
            } else {
                $response = shell_exec("zakeke -user 65580 -key zccXIpB1k2J-quu2BBbwuNZVpvussjoWgTJpCS1lYyM. -data " . $request->get("short_order"));
            }

            $data = @json_decode($response, true);

            if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
                Log::error("Error with json: " . print_r($request->all(), true));
                return redirect()->back()->withErrors('Zakeke API seems to be down, try again later!');
            } else {

                $itemIndex = $request->get("item_index", 0);

                if (isset($data[$shortOrder]['Links'][$itemIndex]['PDF']) && $data[$shortOrder]['Links'][$itemIndex]['PDF'] !== "") {
                    $link = $data[$shortOrder]['Links'][$itemIndex]['PDF'];
                    $request->request->add(['link' => base64_encode($link)]);
                }
            }
        }


        if ($request->has('item_id') or $itemId != null && $request->has('batch_number') or $batchNumber != null) {
            $request->has('batch_number') ? $select_batch = $request->get('batch_number') : $select_batch = $batchNumber == null ? null : $batchNumber;
            $request->has('item_id') ? $item_id = $request->get('item_id') : $item_id = $itemId == null ? null : $itemId;

            if (is_array($select_batch)) {
                $select_batch = $batchNumber;
            }

//            $batche = Batch::with('items', 'production_station', 'route', 'route.stations_list')
//                ->join('stations', 'batches.station_id', '=', 'stations.id')
//                ->searchStatus('active')
//                ->where('stations.type', 'G')
//                ->searchBatch($select_batch)
//                ->get();
            $batche = Batch::with('items', 'route.stations_list', 'route')
                ->where('batch_number', $select_batch)
                ->get();
//dd($item_id, $select_batch, $batche);

            $isJob = false;

            foreach ($batche as $batch) {
                foreach ($batch->items as $items) {
                    if ($items->id == $item_id) {
                        if (empty($items->tracking_number)) {


                            /*
                             * Check if the request provides a link
                             * $request->get("link")
                             */
                            if ($request->has("link")) {
                                $imageLink = base64_decode($request->get("link"));
                            } else {
                                $options = json_decode($items->item_option, true);

                                // The link of the image
                                $imageLink = $options['Custom_EPS_download_link'] ?? null;
                                $isJob = isset($options['Internal_Zakeke_Fetch']);

                                if ($imageLink === null) {
                                    if (is_array($request->get("batch_number"))) {
                                        return null;
                                    } else {
                                        return redirect()->back()->withInput()->withErrors('This item does not have any image that has to be downloaded');
                                    }
                                }
                            }


                            $thumb = '/assets/images/template_thumbs/' . $items->order_id . "-" . $items->id . '.jpg';
                            ###### Save in Item Table ######
                            $items->tracking_number = NULL;
                            $items->item_status = 1;
                            $items->reached_shipping_station = 0;
                            $items->item_thumb = 'http://order.monogramonline.com' . $thumb;
                            $items->save();

                            $order = Order::where("id", $items->order_5p)->first();
                            $order->order_status = 4;
                            $order->save();

                            ###### Save in Batch Table ######
                            //  dd($batch->route->stations_list->first()->station_id);
                            $batch->status = 2;

                            if ($batch->station) {
                                $station_name = $batch->station->station_name;
                            } else {
                                $station_name = 'Station not Found';
                            }

                            $stations = BatchRoute::routeThroughStations($batch->batch_route_id, $station_name);

                            if(stripos($stations, "S-GGR-INDIA") !== false) {
                                $batch->station_id = 264;
                            } else {
                                $batch->station_id = 92;
                            }


//                            $batch->section_id = 6;
//                            $batch->station_id = 92;
                            $batch->prev_station_id = $batch->route->stations_list->first()->station_id;
                            $batch->export_count = 1;
                            $batch->csv_found = 0;
                            $batch->graphic_found = 1;
                            $batch->to_printer = 0;
                            $batch->to_printer_date = null;
                            $batch->archived = 1;
                            $batch->save();
                            ###### Upload File #####

                            /*
                             * Now download the file and put it in that specific directory
                             */
                            $arrContextOptions = array(
                                "ssl" => array(
                                    "verify_peer" => false,
                                    "verify_peer_name" => false,
                                ),
                            );

                            /*
                             * Check if it's a local file
                             * If it is, then use that path instead lmao
                             */

                            if(!file_exists($imageLink)) {
                                Log::error("FILE DID NOT EXIST " . $imageLink);
                            }

                            if (stripos($imageLink, "order.monogramonline.com/media") === false) {
                                $file = null;
                                $try = 3;
                                while ($try > 0 && $file === null) {
                                    $file = @file_get_contents($imageLink, false, stream_context_create($arrContextOptions));
                                    $try--;
                                }
                            } else {
                                $file = file_get_contents(str_replace("http://order.monogramonline.com/", "/var/www/order.monogramonline.com/public_html/", $imageLink));
                            }

                            $array = explode("/", $imageLink);
                            $name = end($array);
                            $afterPath = explode(".", $name);

                            /*
                             * Use end in case there are multiple . in the file name, so the last one should be the extension
                             */
                            $extension = end($afterPath);


                            $filename = $batch->batch_number . '.' . $extension;
                            $filename = $this->uniqueFilename($this->sort_root . "archive/", $filename);


                            if (file_put_contents($this->sort_root . "archive/" . $filename, $file)) {

                                /*
                                 * Check if it's 30X40 then resize
                                 */

                                try {
                                    ImageHelper::createThumb($this->sort_root . "archive/" . $filename, 0, base_path() . '/public_html' . $thumb, 350);



                                } catch (\Exception $e) {
                                    Log::error('Batch uploadFile createThumb: ' . $e->getMessage());
                                }

                                $image_path = $this->sort_root . "archive/" . $filename;
                                $thumb_nail = base_path() . '/public_html' . $thumb;
                                $image_path2 = $this->sort_root . "archive/" . str_replace(".pdf", ".jpg", $filename);


                                if(stripos($imageLink, ".pdf") !== false) {
                                    file_put_contents($image_path2, file_get_contents($thumb_nail));
                                    unlink($image_path);
                                }

                                if(stripos($items->child_sku, "30X40") !== false) {

                                    shell_exec("convert -density 150 -resize 4650x6150! " . $image_path2 . " " . $image_path2);
                                    Log::info("CONVERTING PATH " . $image_path2 . " to be converted");
                                }

                                if($request->has("updated_by") or $isJob) {
                                    Batch::note($batch->batch_number, $batch->station_id, '111', 'Graphic Uploaded to Main (automatically from ' . $request->get("updated_by") . ')');
                                } else {
                                    Batch::note($batch->batch_number, $batch->station_id, '111', 'Graphic Uploaded to Main (Fetched manually using button)');
                                }
                                return redirect()->back()->withInput()->withSuccess('Successfully downloaded and uploaded graphic for batch ' . $select_batch);
                            }
                        }
                    }
                }
            }

            return redirect()->back()->withInput()->withErrors('Batch number required...');
        }
//dd("After",$request->all());
#########################################
        if (!$request->has('batch_number')) {
            return redirect()->back()->withInput()->withErrors('Batch number required');
        }

        $batch = Batch::where('batch_number', $request->get('batch_number'))->first();

        if (!$batch) {
            return redirect()->back()->withInput()->withErrors('Batch not found');
        }

        $filename = $batch->batch_number . '.' . $request->file('upload_file')->getClientOriginalExtension();

        $filename = $this->uniqueFilename($this->main_dir, $filename);

        if (move_uploaded_file($request->file('upload_file'), $this->main_dir . $filename)) {
            Batch::note($batch->batch_number, $batch->station_id, '11', 'Graphic Uploaded to Main');
            return redirect()->back()->withInput()->withSuccess('Graphic Uploaded for Batch ' . $batch->batch_number);
        }

        Batch::note($batch->batch_number, $batch->station_id, '11', 'Error uploading graphic');
        return redirect()->back()->withInput()->withErrors('Error uploading graphic for Batch ' . $batch->batch_number);

    }

    public function uploadFileFromLinkWithoutBatch(Request $request, $itemId = null)
    {
        #1. Get Batch current station.
        #2. Check is item shipped.
        #3. If not Move batch to graphic station.
        #4. Upload Graphic to Archive.


//        $options = json_decode($item->item_option, true);
//
//        $imageLink = $options['Custom_EPS_download_link'] ?? 0;


        if ($request->has('item_id') or $itemId != null) {
            $request->has('item_id') ? $item_id = $request->get('item_id') : $item_id = $itemId == null ? null : $itemId;


            $items = Item::where("id", $item_id)
                ->first();


            /*
             * Check if the request provides a link
             * $request->get("link")
             */
            if ($request->has("link")) {
                $imageLink = urldecode($request->get("link"));
            } else {
                $options = json_decode($items->item_option, true);

                // The link of the image
                $imageLink = $options['Custom_EPS_download_link'] ?? null;

                if ($imageLink === null) {
                    if (is_array($request->get("batch_number"))) {
                        return null;
                    } else {
                        return redirect()->back()->withInput()->withErrors('This item does not have any image that has to be downloaded');
                    }
                }
            }


            $thumb = '/assets/images/template_thumbs/' . $items->order_id . "-" . $items->id . '.jpg';
            ###### Save in Item Table ######
            $items->tracking_number = NULL;
            $items->item_status = 1;
            $items->reached_shipping_station = 0;
            $items->item_thumb = 'http://order.monogramonline.com' . $thumb;
            $items->save();


            /*
             * Now download the file and put it in that specific directory
             */
            $arrContextOptions = array(
                "ssl" => array(
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                ),
            );

            /*
             * Check if it's a local file
             * If it is, then use that path instead lmao
             */
            $file = null;
            if (stripos($imageLink, "order.monogramonline.com/media") === false) {
                $try = 3;
                while ($try > 0 && $file !== null) {
                    $file = file_get_contents($imageLink, false, stream_context_create($arrContextOptions));
                    $try--;
                }
            } else {
                $file = file_get_contents(str_replace("http://order.monogramonline.com/", "/var/www/order.monogramonline.com/public_html/", $imageLink));
            }

            $array = explode("/", $imageLink);
            $name = end($array);
            $afterPath = explode(".", $name);

            /*
             * Use end in case there are multiple . in the file name, so the last one should be the extension
             */
            $extension = end($afterPath);


            $filename = $batch->batch_number . '.' . $extension;
            $filename = $this->uniqueFilename($this->sort_root . "archive/", $filename);

            if (file_put_contents($this->sort_root . "archive/" . $filename, $file)) {
                try {
                    ImageHelper::createThumb($this->sort_root . "archive/" . $filename, 0, base_path() . '/public_html' . $thumb, 350);
                } catch (\Exception $e) {
                    Log::error('Batch uploadFile createThumb: ' . $e->getMessage());
                }

                Batch::note($batch->batch_number, $batch->station_id, '111', 'Graphic Uploaded to Main (automatically from link)');
                return redirect()->back()->withInput()->withSuccess('Successfully downloaded and uploaded graphic for batch ' . $select_batch);
            }
        }
        return redirect()->back()->withInput()->withSuccess('Successfully downloaded and uploaded graphic for batch ' . $select_batch);
    }
}


