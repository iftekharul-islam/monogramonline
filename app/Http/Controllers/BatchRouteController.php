<?php namespace App\Http\Controllers;

use App\BatchRoute;
use App\Item;
use App\Batch;
use App\Station;
use App\Template;
use Illuminate\Http\Request;

use App\Http\Requests\BatchRouteCreateRequest;
use App\Http\Requests\BatchRouteUpdateRequest;
use App\Http\Controllers\Controller;

class BatchRouteController extends Controller
{
	public function index (Request $request)
	{
		$count = 1;
		$stations = Station::where('is_deleted', 0)
							 ->get()
						   ->pluck('station_description', 'id');
		$batch_routes = BatchRoute::with('stations_list')
								  ->where('is_deleted', 0)
								  ->searchEmptyStations($request->get('unassigned', 0))
								  ->orderBy('batch_code')
								  ->paginate(200);
		$templates = Template::where('is_deleted', 0)
							 ->get()
							 ->pluck('template_name', 'id')
							 ->prepend('Select template', '');

		return view('batch_routes.index', compact('batch_routes', 'count', 'stations', 'templates'));
	}

	public function create ()
	{
		// $stations = Station::where('is_deleted', 0)
		// 				   ->pluck('station_description', 'id');
		// 
		// return view('batch_routes.create', compact('stations'));
	}

	public function store (BatchRouteCreateRequest $request)
	{
		#return $request->all();
		$batch_route = new BatchRoute();
		$batch_route->batch_code = $request->get('batch_code');
		$batch_route->batch_route_name = $request->get('batch_route_name');
		$batch_route->batch_max_units = $request->get('batch_max_units');
		$batch_route->batch_options = $request->get('batch_options');
		$batch_route->save();
		$batch_route->stations()
					->attach($request->get('batch_route_order'));
		session()->flash('success', 'Route is successfully added');

		return redirect(url('prod_config/batch_routes'));
	}

	public function show ($id)
	{
		//
	}

	public function edit ($id)
	{
		//
	}

	public function update (BatchRouteUpdateRequest $request, $id)
	{
		#return $request->all();
		$batch_route = BatchRoute::find($id);
		$batch_route->batch_code = $request->get('batch_code');
		$batch_route->batch_route_name = $request->get('batch_route_name');
		$batch_route->summary_msg_1 = $request->get('summary_header_1');
		$batch_route->summary_msg_2 = $request->get('summary_header_2');
		$batch_route->batch_max_units = $request->get('batch_max_units');
		$batch_route->export_template = $request->get('batch_export_template');
        $batch_route->nesting = $request->get('batch_nesting');
		$batch_route->csv_extension = $request->get('csv_extension');
		$batch_route->export_dir = trim( $request->get('export_dir'), '/\\ ');
		$batch_route->graphic_dir = trim( $request->get('graphic_dir'), '/\\ ');
		$batch_route->batch_options = $request->get('batch_options');
		$batch_route->save();

		$updateStationText = preg_replace('/\s+/', '', $request->get('batch_stations'));
		$updatedStationsArray = explode(",", $updateStationText); 
		$newStations = Station::whereIn('station_name', $updatedStationsArray)
							  ->orderByRaw(sprintf("FIELD (station_name, '%s')", implode("', '", $updatedStationsArray)))
								->get()
							  ->pluck('id')
								->toArray();
		$batch_route->stations()
					->detach();
		$batch_route->stations()
					->attach($newStations);

		#return redirect(url('batch_routes'));
		// create a jumpable url
		$url = sprintf("%s#%s", redirect()
			->getUrlGenerator()
			->previous(), $batch_route->batch_code);

		return redirect($url);
	}

	public function destroy ($id)
	{
		$batch_route = BatchRoute::find($id);
		if ( !$batch_route ) {
			abort(404);
		}
		$batches = Batch::where('batch_route_id', $id)
						->whereNotIn('status', [1,8])
					 	->count();
		if ( $batches ) {
			return redirect()
				->back()
				->withErrors([
					'batches_assigned' => sprintf("Cannot delete. Batches are assigned to route %s .", $batch_route->batch_code),
				]);
		}
		$batch_route->is_deleted = 1;
		$batch_route->save();

		return redirect(url('prod_config/batch_routes'));
	}
}
