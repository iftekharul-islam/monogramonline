<?php

namespace App\Http\Controllers;

use App\SalesCategory;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class SalesCategoryController extends Controller
{
	public function index ()
	{
		$sales_categories = SalesCategory::where('is_deleted', 0)
										 ->orderBy(DB::raw('sales_category_display_order + 0'), 'asc')
										 ->latest()
										 ->paginate(50);
		$count = 1;

		return view('sales_categories.index', compact('sales_categories', 'count'));
	}

	public function create ()
	{
		return view('sales_categories.create');
	}

	public function store (Requests\SalesCategoryCreateRequest $request)
	{
		$sales_categories = new SalesCategory();
		$sales_categories->sales_category_code = trim($request->get('sales_category_code'));
		$sales_categories->sales_category_description = trim($request->get('sales_category_description'));
		$sales_categories->sales_category_display_order = intval($request->get('sales_category_display_order'));

		$sales_categories->save();

		return redirect()->action('SalesCategoryController@index');
	}

	public function show ($id)
	{
		$sales_categories = SalesCategory::where('is_deleted', 0)
										 ->find($id);
		if ( !$sales_categories ) {
			return view('errors.404');
		}

		return '';

		return view('sales_categories.show', compact('category'));
	}

	public function edit ($id)
	{
		$sales_categories = SalesCategory::where('is_deleted', 0)
										 ->find($id);
		if ( !$sales_categories ) {
			return view('errors.404');
		}

		return '';

		return view('sales_categories.edit', compact('category'));
	}

	public function update (Requests\SalesCategoryUpdateRequest $request, $id)
	{
		$sales_categories = SalesCategory::where('is_deleted', 0)
										 ->find($id);
		if ( !$sales_categories ) {
			return view('errors.404');
		}

		$sales_categories->sales_category_code = trim($request->get('sales_category_code'));
		$sales_categories->sales_category_description = trim($request->get('sales_category_description'));
		$sales_categories->sales_category_display_order = intval($request->get('sales_category_display_order'));

		$sales_categories->save();

		return redirect()->action('SalesCategoryController@index');
	}

	public function destroy ($id)
	{
		$sales_categories = SalesCategory::where('is_deleted', 0)
										 ->find($id);
		if ( !$sales_categories ) {
			return view('errors.404');
		}

		$sales_categories->is_deleted = 1;
		$sales_categories->save();

		return redirect()->action('SalesCategoryController@index');
	}
}
