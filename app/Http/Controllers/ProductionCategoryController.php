<?php

namespace App\Http\Controllers;

use App\ProductionCategory;
use Illuminate\Http\Request;

use App\Http\Requests\ProductionCategoryCreateRequest;
use App\Http\Requests\ProductionCategoryUpdateRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;


class ProductionCategoryController extends Controller
{
	public function index ()
	{
		$production_categories = ProductionCategory::where('is_deleted', 0)
												   ->orderBy(DB::raw('production_category_display_order + 0'), 'asc')
												   ->latest()
												   ->paginate(50);
		$count = 1;

		return view('production_categories.index', compact('production_categories', 'count'));
	}

	public function create ()
	{
		return view('production_categories.create');
	}

	public function store (ProductionCategoryCreateRequest $request)
	{
		$production_categories = new ProductionCategory();
		$production_categories->production_category_code = trim($request->get('production_category_code'));
		$production_categories->production_category_description = trim($request->get('production_category_description'));
		$production_categories->production_category_display_order = intval($request->get('production_category_display_order'));

		$production_categories->save();

		return redirect()->action('ProductionCategoryController@index');
	}

	public function show ($id)
	{
		$production_categories = ProductionCategory::where('is_deleted', 0)
												   ->find($id);
		if ( !$production_categories ) {
			return view('errors.404');
		}

		return view('production_categories.show', compact('category'));
	}

	public function edit ($id)
	{
		$production_categories = ProductionCategory::where('is_deleted', 0)
												   ->find($id);
		if ( !$production_categories ) {
			return view('errors.404');
		}

		return view('production_categories.edit', compact('category'));
	}

	public function update (ProductionCategoryUpdateRequest $request, $id)
	{
		$production_categories = ProductionCategory::where('is_deleted', 0)
												   ->find($id);
		if ( !$production_categories ) {
			return view('errors.404');
		}

		$production_categories->production_category_code = trim($request->get('production_category_code'));
		$production_categories->production_category_description = trim($request->get('production_category_description'));
		$production_categories->production_category_display_order = intval($request->get('production_category_display_order'));

		$production_categories->save();

		return redirect()->action('ProductionCategoryController@index');
	}

	public function destroy ($id)
	{
		$production_categories = ProductionCategory::where('is_deleted', 0)
												   ->find($id);
		if ( !$production_categories ) {
			return view('errors.404');
		}

		$production_categories->is_deleted = 1;
		$production_categories->save();

		return redirect()->action('ProductionCategoryController@index');
	}
}
