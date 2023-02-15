<?php

namespace App\Http\Controllers;

use App\SubCategory;
use Illuminate\Http\Request;

use App\Http\Requests\SubCategoryCreateRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class SubCategoryController extends Controller
{
	public function index ()
	{
		$sub_categories = SubCategory::where('is_deleted', 0)
									 ->orderBy(DB::raw('sub_category_display_order + 0'), 'asc')
									 ->paginate(50);
		$count = 1;

		return view('sub_categories.index', compact('sub_categories', 'count'));
	}

	public function create ()
	{
		//
	}

	public function store (SubCategoryCreateRequest $request)
	{
		$sub_category = new SubCategory();
		$sub_category->sub_category_code = $request->get('sub_category_code');
		$sub_category->sub_category_description = $request->get('sub_category_description');
		$sub_category->sub_category_display_order = intval($request->get('sub_category_display_order'));

		$sub_category->save();

		return redirect()->action('SubCategoryController@index');
	}

	public function show ($id)
	{
		//
	}

	public function edit ($id)
	{
		//
	}

	public function update (Request $request, $id)
	{
		$subcategory = SubCategory::where('is_deleted', 0)
								  ->find($id);
		if ( !$subcategory ) {
			return view('errors.404');
		}

		$subcategory->sub_category_code = $request->get('sub_category_code');
		$subcategory->sub_category_description = $request->get('sub_category_description');
		$subcategory->sub_category_display_order = intval($request->get('sub_category_display_order'));

		$subcategory->save();

		return redirect()->action('SubCategoryController@index');
	}

	public function destroy ($id)
	{
		$subcategory = SubCategory::where('is_deleted', 0)
								  ->find($id);
		if ( !$subcategory ) {
			return view('errors.404');
		}

		$subcategory->is_deleted = 1;
		$subcategory->save();

		return redirect()->action('SubCategoryController@index');
	}
}
