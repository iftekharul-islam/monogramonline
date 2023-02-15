<?php

namespace App\Http\Controllers;

use App\BatchRoute;
use App\Inventory;
use App\Station;
use App\Template;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use League\Csv\Reader;
use Monogram\Helper;

class ImportController extends Controller
{
	public function importInventory (Request $request)
	{
		$file = $request->file('attached_csv');

		$mime_types = [
			'application/vnd.ms-excel',
			'application/octet-stream',
			'text/plain',
			'text/csv',
			'text/tsv',
		];
		if ( !$file || !in_array($file->getClientMimeType(), $mime_types) ) {
			return redirect()
				->back()
				->withErrors(new MessageBag([
					'error' => 'Either file is not given or imported file is not valid.',
				]));
		}
		$file_path = sprintf("%s/assets/imports/inventories/", public_path());
		$file_name = $file->getClientOriginalName();
		$fully_specified_file_name = sprintf("%s%s", $file_path, $file_name);

		$file->move($file_path, $file_name);
		$reader = Reader::createFromPath($fully_specified_file_name);
		$table_columns = Inventory::getTableColumns();
		$csv_columns = $reader->fetchOne();

		if ( count(array_intersect($table_columns, $csv_columns)) != count($table_columns) ) {
			return redirect()
				->back()
				->withErrors(new MessageBag([
					'error' => "Expected columns not found.",
				]));
		}

		if ( $request->get('todo') == 'validate' ) {
			session()->flash('success', 'Seems like a valid CSV file.');

			return redirect()->to('inventories');
		} elseif ( $request->get('todo') == 'upload' ) {
			// Trancket Table before update
			Inventory::truncate();
			$rows = $reader->setOffset(1)
						   ->fetchAssoc($csv_columns);
			set_time_limit(0);
			foreach ( $rows as $row ) {
				$table_field_values = array_intersect_key($row, array_flip($table_columns));
				$inventory = new Inventory();

				foreach ( $table_field_values as $key => $value ) {
					$inventory->$key = trim($value);
				}
				$inventory->save();
			}
			session()->flash('success', 'Inventory is successfully updated.');

			return redirect(url('inventories'));
		}
	}

	public function importBatchRoute (Request $request)
	{
		$file = $request->file('csv_file');

		$mimes = [
			'application/vnd.ms-excel',
			'application/octet-stream',
			'text/plain',
			'text/csv',
			'text/tsv',
		];
		if ( !in_array($file->getClientMimeType(), $mimes) ) {
			return redirect()
				->back()
				->withErrors(new MessageBag([ 'error' => 'Not a valid csv file' ]));
		}
		$file_path = sprintf("%s/assets/imports/batch_routes/", public_path());
		$file_name = $file->getClientOriginalName();
		$fully_specified_file_name = sprintf("%s%s", $file_path, $file_name);

		$file->move($file_path, $file_name);
		$reader = Reader::createFromPath($fully_specified_file_name);
		$table_columns = BatchRoute::getTableColumns();
		$extra_columns = [
			'stations',
		];
		$needed_columns = array_merge($table_columns, $extra_columns);
		$csv_columns = array_filter($reader->fetchOne());

		if ( count(array_intersect($needed_columns, $csv_columns)) != count($needed_columns) ) {
			return redirect()
				->back()
				->withErrors(new MessageBag([
					'error' => 'CSV file columns don\'t match. Import stopped.',
				]));
		}

		$rows = $reader->setOffset(1)
					   ->fetchAssoc($needed_columns);
		set_time_limit(0);
		$errors = [ ];
		foreach ( $rows as $row ) {
			$batch_code = trim($row['batch_code']);
			if ( empty( $batch_code ) ) {
				continue;
			}
			$batch_route = BatchRoute::where('batch_code', $batch_code)
									 ->first();
			if ( !$batch_route ) {
				$batch_route = new BatchRoute();
				$batch_route->batch_code = $batch_code;
			}
			foreach ( $table_columns as $column ) {
				if ( $column == 'batch_code' ) {
					continue;
				} elseif ( $column == 'batch_route_name' ) {
					$batch_route->batch_route_name = trim($row['batch_route_name']);
				} elseif ( $column == 'batch_max_units' ) {
					$batch_max_units = intval(trim($row['batch_max_units']));
					$batch_route->batch_max_units = $batch_max_units >= 0 ? $batch_max_units : 0;
				} elseif ( $column == 'export_template' ) {
					$template_name = trim($row['export_template']);
					$template = Template::where('template_name', $template_name)
										->first();
					if ( $template ) {
						$batch_route->export_template = $template->id;
					} else {
						$batch_route->export_template = 1; // assign default template
					}
				} elseif ( $column == 'batch_options' ) {
					$batch_options = trim($row['batch_options']);
					$batch_route->batch_options = $batch_options;
				} elseif ( $column == 'csv_extension' ) {
					$csv_extension = trim($row['csv_extension']);
					$batch_route->csv_extension = $csv_extension;
				} elseif ( $column == 'export_dir' ) {
					$export_dir = trim($row['export_dir']);
					$batch_route->export_dir = $export_dir;
				} elseif ( $column == 'graphic_dir' ) {
					$graphic_dir = trim($row['graphic_dir']);
					$batch_route->graphic_dir = $graphic_dir;
				} elseif ( $column == 'summary_msg_1' ) {
					$batch_route->summary_msg_1 = trim($row['summary_msg_1']);
				} elseif ( $column == 'summary_msg_2' ) {
					$batch_route->summary_msg_2 = trim($row['summary_msg_2']);
				}
			}
			$batch_route->save();
			foreach ( $extra_columns as $column ) {
				if ( $column == 'stations' ) {
					$stationsFromFile = trim($row['stations']);
					$stationsArray = array_filter(explode(",", str_replace(" ", "", $stationsFromFile)));
					$stationsFromTable = Station::whereIn("station_name", $stationsArray)
												->orderBy(DB::raw(sprintf("FIELD ( station_name, '%s' )", implode("','", $stationsArray))))
												->get();
// Helper::jewelDebug($row['batch_route_name']); Helper::jewelDebug($stationsFromTable->toArray()); Helper::jewelDebug($stationsArray);
					if ( $stationsFromTable->count() != count($stationsArray) ) {
						$differences = array_diff($stationsArray, $stationsFromTable->pluck('station_name')
																					->toArray());
						$errors[] = sprintf("%s has %d wrong stations code: %s", $row['batch_route_name'], ( count($stationsArray) - count($stationsFromTable) ), implode(", ", $differences));
					}
					// detach all the previous relations
					$batch_route->stations()
								->detach();

					// attach new relations
					$batch_route->stations()
								->attach($stationsFromTable->pluck('id')
														   ->toArray());
				}
			}
		}
		if ( count($errors) ) {
			session()->flash('success', "Batch routes update with stated errors.");

			return redirect()
				->to(url('prod_config/batch_routes'))
				->withErrors($errors);
		}
		// if there's no error on station name
		session()->flash('success', 'Batch routes are successfully updated.');

		return redirect(url('prod_config/batch_routes'));
	}
}
