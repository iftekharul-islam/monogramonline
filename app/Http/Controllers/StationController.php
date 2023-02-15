<?php

namespace App\Http\Controllers;

use App\StationLog;
use App\Section;
use Illuminate\Http\Request;
use App\Station;
use App\Http\Requests\StationCreateRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use League\Csv\Writer;

class StationController extends Controller {

	public function index() {

		$count = 1;
		$stations = Station::where ( 'is_deleted', 0 )->orderBy ( 'station_name', 'asc' )->get();

		$types = array( 'X' => 'Not Assigned', 'G' => 'Graphics', 'P' => 'Production', 'Q' => 'Quality Control');
		
		$sections = Section::where ( 'is_deleted', 0 )->lists ( 'section_name', 'id' )->prepend ( 'Select a Section', '0' );

		return view ( 'stations.index', compact ( 'stations', 'count', 'sections', 'types' ) );
	}


	public function create(Request $request) {
		
		$types = array( 'X' => 'Not Assigned', 'G' => 'Graphics', 'P' => 'Production', 'Q' => 'Quality Control');
		
		$sections = Section::where ( 'is_deleted', 0 )->lists ( 'section_name', 'id' )->prepend ( 'Select a Section', '0' );
		
		return view ( 'stations.create', compact ( 'sections', 'types' ) )
								->withInput($request);
	}


	public function store(StationCreateRequest $request) {
		$station = new Station ();
		$station->station_name = trim ( $request->get ( 'station_name' ) );
		$station->station_description = $request->get ( 'station_description' );
		$station->type = $request->get ( 'type' );
		$station->section = $request->get ( 'section' );
		$station->save ();

		return redirect()->action('StationController@index')->withSuccess('Station is successfully added');
	}


	public function show($id) {
		$station = Station::where ( 'is_deleted', 0 )->find ( $id );

		if (! $station) {
			return view ( 'errors.404' );
		}

		return view ( 'stations.show', compact ( 'station' ) );
	}


	public function edit($id) {
		$station = Station::where ( 'is_deleted', 0 )->find ( $id );

		if (! $station) {
			return view ( 'errors.404' );
		}

		return view ( 'stations.edit', compact ( 'station' ) );
	}


	public function update(Request $request, $id) {
		
		$validator = Validator::make($request->all(), [
        'station_name'        => 'required',
        'station_description' => 'required',
				'type'                => 'required',
				'section'             => 'required',
    ]);

    if ($validator->fails()) {
        return redirect('stations')->withErrors($validator);
    }

		$station = Station::where ( 'is_deleted', 0 )->find ( $id );

		if (! $station) {
			return redirect('stations')->withErrors('Station Not Found');
		}

		$station->station_name = trim ( $request->get ( 'station_name' ) );
		$station->station_description = $request->get ( 'station_description' );
		$station->station_status = $request->get ( 'station_status' );
		$station->type = $request->get ( 'type' );
		$station->section = $request->get ( 'section' );
		$station->save ();

		session ()->flash ( 'success', 'Station is successfully updated.' );
		
		return redirect()->action('StationController@index');
	}


	public function destroy($id) {
		$station = Station::where ( 'is_deleted', 0 )->find ( $id );

		if (! $station) {
			return view ( 'errors.404' );
		}

		$station->is_deleted = 1;
		$station->save ();

		return redirect ( url ( 'stations' ) );
	}
	
	

	public function getExportStationLog(Request $request) {
		$start = trim ( $request->get ( 'start_date' ) );
		$output = null;

		if (! empty ( $start )) {
			$month_starts = "01";
			$month_ends = date ( 't', strtotime ( $request->get ( 'start_date' ) ) );

			$first_day_of_the_month = sprintf ( "%s-%s", $start, $month_starts );
			$end_day_of_the_month = sprintf ( "%s-%s", $start, $month_ends );

			$station_logs = StationLog::with ( 'user', 'station' )->searchWithinMonthGroupLog ( $first_day_of_the_month, $end_day_of_the_month )->orderBy ( 'started_at' )->get ( [
					'started_at',
					'station_id',
					'user_id',
					DB::raw ( 'SUM(1) as item_count' )
			] );
			$dates = $this->range_date ( $first_day_of_the_month, $end_day_of_the_month );
			$header = array_merge ( [
					'station'
			],
					// uncomment user if user is required
					// 'user',
					$dates, [
							'total'
					] );
			$output [] = $header;

			foreach ( $station_logs as $log ) {
				$station_name = $log->station->station_name;
				// uncomment user if user is required
				// $user = $log->user->username;
				$row = [ ];
				$row [] = $station_name;
				// uncomment user if user is required
				// $row[] = $user;
				$month_total_task_per_station = 0;
				foreach ( $dates as $date ) {
					$per_day = 0;
					if ($date == $log->started_at) {
						$per_day = $log->item_count;
					}
					$row [] = $per_day;
					$month_total_task_per_station += $per_day;
				}
				$row [] = $month_total_task_per_station;
				$output [] = $row;
			}
		}

		return view ( 'stations.export_station' )->with ( 'request', $request )->with ( 'output', $output );
	}

	public function postExportStationLog(Request $request) {
		// grab the month
		$start = trim ( $request->get ( 'start_date' ) );
		// $end = trim($request->get('end_date'));

		if (empty ( $start )) {
			return redirect ()->back ()->withInput ()->withErrors ( [
					'error' => 'Date is not selected'
			] );
		}
		$month_starts = "01";
		$month_ends = date ( 't', strtotime ( $request->get ( 'start_date' ) ) );

		$first_day_of_the_month = sprintf ( "%s-%s", $start, $month_starts );
		$end_day_of_the_month = sprintf ( "%s-%s", $start, $month_ends );

		/*
		 * DB QUERY: SELECT station_id, started_at, SUM( 1 ) FROM `station_logs` WHERE started_at >= '2016-03-01' AND started_at <= '2016-03-31' GROUP BY station_id, started_at ORDER BY station_id
		 * SELECT station_id, started_at, user_id, sum(1) FROM `station_logs` where started_at >= '2016-03-01' and started_at <= '2016-03-31' group by station_id, user_id, started_at order by started_at
		 */

		$station_logs = StationLog::with ( 'user', 'station' )->searchWithinMonthGroupLog ( $first_day_of_the_month, $end_day_of_the_month )->orderBy ( 'started_at' )->get ( [
				'started_at',
				'station_id',
				'user_id',
				DB::raw ( 'SUM(1) as item_count' )
		] );
		$dates = $this->range_date ( $first_day_of_the_month, $end_day_of_the_month );
		$header = array_merge ( [
				'station'
		],
				// uncomment user if user is required
				// 'user',
				$dates, [
						'total'
				] );

		/*
		 * File write operation
		 */

		$file_path = sprintf ( "%s/assets/exports/station_log/", public_path () );
		$file_name = sprintf ( "station_log-%s-%s.csv", date ( "y-m-d", strtotime ( 'now' ) ), str_random ( 5 ) );
		$fully_specified_path = sprintf ( "%s%s", $file_path, $file_name );
		$csv = Writer::createFromFileObject ( new \SplFileObject ( $fully_specified_path, 'w+' ), 'w' );
		$csv->insertOne ( $header );
		foreach ( $station_logs as $log ) {
			$station_name = $log->station->station_name;
			// uncomment user if user is required
			// $user = $log->user->username;
			$row = [ ];
			$row [] = $station_name;
			// uncomment user if user is required
			// $row[] = $user;
			$month_total_task_per_station = 0;
			foreach ( $dates as $date ) {
				$per_day = 0;
				if ($date == $log->started_at) {
					$per_day = $log->item_count;
				}
				$row [] = $per_day;
				$month_total_task_per_station += $per_day;
			}
			$row [] = $month_total_task_per_station;
			$csv->insertOne ( $row );
		}

		return response ()->download ( $fully_specified_path );
	}

	private function range_date($first, $last) {
		$arr = array ();
		$now = strtotime ( $first );
		$last = strtotime ( $last );

		while ( $now <= $last ) {
			$arr [] = date ( 'Y-m-d', $now );
			$now = strtotime ( '+1 day', $now );
		}

		return $arr;
	}
	
}
