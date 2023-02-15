<?php

namespace App\Http\Controllers;

use App\MasterCategory;
use App\Product;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class MasterCategoryController extends Controller
{
	public function getChildCategories ($id = 0, $is_ajax = false)
	{
		$id = intval($id);

		$categories = null;
		$categories = MasterCategory::where('parent', $id)
									->where('is_deleted', 0)
									->orderBy(DB::raw('master_category_display_order + 0'), 'asc')
									->get();
		if ( $is_ajax ) {
			if ( $categories->count() > 0 ) {
				$count = 1;
				$select_form_data = view('master_categories.ajax_category_response', compact('categories', 'id'))->render();
				$tabular_view = view('master_categories.table_view', compact('count'))
					->with('master_categories', $categories)
					->render();
				$data = [
					'select_form_data' => $select_form_data,
					'tabular_data'     => $tabular_view,
				];

				return response()->json($data, 200);

			}

			return response()->json();
		}

		return $categories;
	}

	public function index ()
	{
		$master_categories = MasterCategory::where('parent', 0)
										   ->where('is_deleted', 0)
										   ->orderBy(DB::raw('master_category_display_order + 0'), 'asc')
										   ->paginate(50);
		$count = 1;
		$id = 0;
		$categories = $this->getChildCategories();

		return view('master_categories.new_index', compact('master_categories', 'count', 'categories', 'id'));
	}

	public function create ()
	{
		return view('master_categories.create');
	}

	public function store (Requests\MasterCategoryCreateRequest $request)
	{
		$master_category = new MasterCategory();
		$master_category->master_category_code = trim($request->get('master_category_code'));
		$master_category->master_category_description = trim($request->get('master_category_description'));
		$master_category->master_category_display_order = intval($request->get('master_category_display_order'));
		$master_category->parent = intval($request->get('parent_category'));

		$master_category->save();

		return redirect()->action('MasterCategoryController@index');
	}

	public function show ($id)
	{
		$master_category = MasterCategory::where('is_deleted', 0)
										 ->find($id);
		if ( !$master_category ) {
			return view('errors.404');
		}

		return view('master_categories.show', compact('master_category'));
	}

	public function edit ($id)
	{
		$master_category = Category::where('is_deleted', 0)
								   ->find($id);
		if ( !$master_category ) {
			return view('errors.404');
		}

		return view('master_categories.edit', compact('master_category'));
	}

	public function update (Requests\MasterCategoryUpdateRequest $request, $id)
	{
		$master_category = MasterCategory::where('is_deleted', 0)
										 ->find($id);
		if ( !$master_category ) {
			return view('errors.404');
		}

		if ( $request->has('modified_code') ) {
			$master_category->master_category_code = trim($request->get('modified_code'));
			$master_category->master_category_description = trim($request->get('modified_description'));
			$master_category->master_category_display_order = intval($request->get('modified_display_order'));
		} else {
			$master_category->master_category_code = $request->get('master_category_code');
			$master_category->master_category_description = $request->get('master_category_description');
			$master_category->master_category_display_order = intval($request->get('master_category_display_order'));
		}

		$master_category->save();

		return redirect()->action('MasterCategoryController@index');
	}

	public function destroy ($id)
	{
		$master_category = MasterCategory::where('is_deleted', 0)
										 ->find($id);
		if ( !$master_category ) {
			return view('errors.404');
		}
		$products_count = Product::where('product_master_category', $master_category->id)
								 ->count();
		if ( $products_count ) {
			return redirect()
				->back()
				->withErrors([
					'error' => sprintf("Cannot delete category. %d products have this category assigned.", $products_count),
				]);
		}

		$children_count = $master_category->children->count();

		if ( $children_count ) {
			return redirect()
				->back()
				->withErrors([
					'error' => sprintf("Cannot delete category. %d children categories available.", $children_count),
				]);
		}

		$master_category->is_deleted = 1;
		$master_category->save();

		return redirect()->action('MasterCategoryController@index');
	}

	public function getNext (Request $request, $parent_category_id)
	{
		if ( !$request->ajax() ) {
			return view('errors.404');
		}
		if ( intval($parent_category_id) == 0 ) {
			return;
		}

		return $this->getChildCategories($parent_category_id, true);
	}
}
