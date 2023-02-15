<?php

namespace App\Http\Controllers;

use App\Occasion;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Requests\OccasionCreateRequest;
use App\Http\Requests\OccasionUpdateRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class OccasionController extends Controller
{
	public function index ()
	{
		$occasions = Occasion::where('is_deleted', 0)
							 ->orderBy(DB::raw('occasion_display_order + 0'), 'asc')
							 ->latest()
							 ->paginate(50);
		$count = 1;

		return view('occasions.index', compact('occasions', 'count'));
	}

	public function create ()
	{
		return view('occasions.create');
	}

	public function store (OccasionCreateRequest $request)
	{
		$occasion = new Occasion();
		$occasion->occasion_code = trim($request->get('occasion_code'));
		$occasion->occasion_description = trim($request->get('occasion_description'));
		$occasion->occasion_display_order = intval($request->get('occasion_display_order'));

		$occasion->save();

		return redirect()->action('OccasionController@index')->withSuccess('Occasion is added successfully.');
	}

	public function show ($id)
	{
		return redirect()->action('OccasionController@index');
	}

	public function edit ($id)
	{
		return redirect()->action('OccasionController@index');
	}

	public function update (OccasionUpdateRequest $request, $id)
	{
		$occasion = Occasion::where('is_deleted', 0)
								->find($id);
		if ( !$occasion ) {
			return view('errors.404');
		}

		$occasion->occasion_code = trim($request->get('occasion_code'));
		$occasion->occasion_description = trim($request->get('occasion_description'));
		$occasion->occasion_display_order = intval($request->get('occasion_display_order'));

		$occasion->save();

		return redirect()->action('OccasionController@index')->withSuccess('Occasion is updated successfully.');
	}

	public function destroy ($id)
	{
		$occasion = Occasion::where('is_deleted', 0)
								->find($id);
		if ( !$occasion ) {
			return view('errors.404');
		}

		$occasion->is_deleted = 1;
		$occasion->save();

		return redirect()->action('OccasionController@index')->withSuccess(sprintf('Occasion %s is deleted successfully.', $occasion->occasion_code));
	}
}
