<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Station;
use App\StationLog;
use App\BatchScan;
use App\User;
use Illuminate\Http\Request;
use Monogram\CSV;

class StationLogController extends Controller
{
	public function index (Request $request)
	{	
		$logs = array();
		
		if ($request->all() != null) {
			
			$request->has('start_date') ? $start_date = $request->get('start_date') : $start_date = '2016-06-01';
			$request->has('end_date') ? $end_date = $request->get('end_date') : $end_date = date("Y-m-d");
			$request->has('user_id') ? $user_id = $request->get('user_id') : $user_id = null;
			
			$station_logs = StationLog::selectRaw('"Move" as type, count(*) as item_count, station_id, user_id')
                                                                    ->with("item")
																	->searchStation($request->get('station'))
																	->searchUser($request->get('user_id'))
																	->withinDate($request->get('start_date'), $request->get('end_date'))
																	->groupBy('station_id', 'user_id')
																	->get();



            if($request->get("download_report", false)) {

                $batchNumbers = StationLog::searchStation($request->get('station'))
                    ->searchUser($request->get('user_id'))
                    ->withinDate($request->get('start_date'), $request->get('end_date'))
                    ->get();

                $csvData = [];
                $line = [
                    'batch_number',
                    'station_id',
                    'prev_station_id',
                    'created_at',
                ];

                $csvData[] = $line;

                foreach($batchNumbers as $batch) {
                    $csvData[] = [
                        $batch->batch_number,
                        $batch->station_id,
                        $batch->prev_station_id,
                        $batch->created_at,
                    ];
                }

                // Begin creating csv file
                $filename = 'Report_' . "Creation" . '_' . date('ymd_His') . '.' . uniqid() . '.csv';
                $csv = new CSV;

                $path = storage_path() . "/EDI/General/ShipStation/";

                $path = $csv->createFile($csvData, $path, null, $filename, ',');
                return response()->download($path);
            }

			$totals['logs'] = $station_logs->sum('item_count');
			
			foreach($station_logs as $scan) {
				$logs[$scan->station_id][$scan->user_id]['move'] = $scan->item_count;
			}
																	
			$in_scans = BatchScan::selectRaw('count(*) as item_count, station_id, in_user_id as user_id')
									->searchStation($request->get('station'))
									->SearchInUser($request->get('user_id'))
									->where('in_date', '>', $start_date . ' 00:00:00')
									->where('in_date', '<', $end_date . ' 23:59:59')
									->groupBy('station_id', 'in_user_id')
									->get();


			$totals['in_scans'] = $in_scans->sum('item_count');
			
			foreach($in_scans as $scan) {
				$logs[$scan->station_id][$scan->user_id]['in_scan'] = $scan->item_count;
			}
			
			$out_scans = BatchScan::selectRaw('count(*) as item_count, station_id, out_user_id as user_id')
									->searchStation($request->get('station'))
									->searchOutUser($request->get('user_id'))
									->where('out_date', '>', $start_date . ' 00:00:00')
									->where('out_date', '<', $end_date . ' 23:59:59')
									->groupBy('station_id', 'out_user_id')
									->get();
									
			$totals['out_scans'] = $out_scans->sum('item_count');
			
			foreach($out_scans as $scan) {
				$logs[$scan->station_id][$scan->user_id]['out_scan'] = $scan->item_count;
			}
				
		}
		
		$users = User::orderBy('username', 'ASC')	
							->get()
							->pluck('username', 'id')
					 		->prepend('Select a user', 0);

		$stations = Station::selectRaw('id, CONCAT(station_name, " - ", station_description) as station_title')
								->get()
						   ->pluck('station_title', 'id')
						   ->prepend('Select station', '0');
		$count = 1;

		return view('logs.index', compact('logs', 'request', 'count', 'stations', 'users', 'skus', 'totals'));
	}
}
