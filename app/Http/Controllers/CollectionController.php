<?php

namespace App\Http\Controllers;

use App\Collection;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Requests\CollectionCreateRequest;
use App\Http\Requests\CollectionUpdateRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CollectionController extends Controller
{
	public function index ()
	{
		$collections = Collection::where('is_deleted', 0)
								 ->orderBy(DB::raw('collection_display_order + 0'), 'asc')
								 ->latest()
								 ->paginate(50);
		$count = 1;

		return view('collections.index', compact('collections', 'count'));
	}

	public function create ()
	{
		return view('production_categories.create');
	}

	public function store (CollectionCreateRequest $request)
	{
		$collection = new Collection();
		$collection->collection_code = trim($request->get('collection_code'));
		$collection->collection_description = trim($request->get('collection_description'));
		$collection->collection_display_order = intval($request->get('collection_display_order'));

		$collection->save();

		session()->flash('success', 'Collection is added successfully.');

		return redirect(route('collections.index'));
	}

	public function show ($id)
	{
		return redirect(route('collections.index'));
		$production_categories = Collection::where('is_deleted', 0)
										   ->find($id);
		if ( !$production_categories ) {
			return view('errors.404');
		}

		return view('production_categories.show', compact('category'));
	}

	public function edit ($id)
	{
		return redirect(route('collections.index'));

		$production_categories = ProductionCategory::where('is_deleted', 0)
												   ->find($id);
		if ( !$production_categories ) {
			return view('errors.404');
		}

		return view('production_categories.edit', compact('category'));
	}

	public function update (CollectionUpdateRequest $request, $id)
	{
		$collection = Collection::where('is_deleted', 0)
								->find($id);
		if ( !$collection ) {
			return view('errors.404');
		}

		$collection->collection_code = trim($request->get('collection_code'));
		$collection->collection_description = trim($request->get('collection_description'));
		$collection->collection_display_order = intval($request->get('collection_display_order'));

		$collection->save();

		session()->flash('success', 'Collection is updated successfully.');

		return redirect(route('collections.index'));
	}

	public function destroy ($id)
	{
		$collection = Collection::where('is_deleted', 0)
								->find($id);
		if ( !$collection ) {
			return view('errors.404');
		}

		$collection->is_deleted = 1;
		$collection->save();

		session()->flash('success', sprintf('Collection %s is deleted successfully.', $collection->collection_code));

		return redirect(route('collections.index'));
	}
}
