<?php

namespace App\Http\Controllers;

use App\BatchRoute;
use App\Inventory;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use League\Csv\Writer;

class ExportController extends Controller
{
	public function inventory (Request $request)
	{
		$tableColumns = Inventory::getTableColumns();
		$file_path = sprintf("%s/assets/exports/inventories/", public_path());
		$file_name = sprintf("inventory-%s-%s.csv", date("y-m-d-h-i-s", strtotime('now')), str_random(5));
		$fully_specified_path = sprintf("%s%s", $file_path, $file_name);

		$csv = Writer::createFromFileObject(new \SplFileObject($fully_specified_path, 'a+'), 'w');
		$csv->insertOne($tableColumns);

		$inventories = Inventory::where('is_deleted', 0)
								->get($tableColumns)
								->toArray();
		$csv->insertAll($inventories);

		return response()->download($fully_specified_path);
	}

	public function batch_routes (Request $request)
	{
		$batch_routes = BatchRoute::with('stations_list', 'template')
								  ->where('is_deleted', 0)
								  ->orderBy('batch_route_name')
								  ->latest()
								  ->get();

		$table_columns = BatchRoute::getTableColumns();

		$extra_columns = [
			'stations',
		];
		$columns = array_merge($table_columns, $extra_columns);

// dd($columns, $table_columns, $extra_columns);

		$file_path = sprintf("%s/assets/exports/batch_routes/", public_path());
		$file_name = sprintf("routes-%s-%s.csv", date("y-m-d", strtotime('now')), str_random(5));
		$fully_specified_path = sprintf("%s%s", $file_path, $file_name);

		$csv = Writer::createFromFileObject(new \SplFileObject($fully_specified_path, 'w+'), 'w');

		$csv->insertOne($columns);

		foreach ( $batch_routes as $route ) {
			$row = [
				$route->batch_code,
				$route->batch_route_name,
				$route->batch_max_units,
				$route->template ? $route->template->template_name : "N/A",
				$route->batch_options,
				$route->csv_extension,
				$route->export_dir,
				$route->graphic_dir,
				$route->summary_msg_1,
				$route->summary_msg_2,
				count($route->stations_list) ? implode(",", $route->stations_list->pluck('station_name')
																				 ->toArray()) : "",
			];

			$csv->insertOne($row);
		}

		return response()->download($fully_specified_path);
	}
}
