<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;

use App\Http\Requests\CategoryCreateRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
	public function index ()
	{
		$categories = Category::where('is_deleted', 0)
							  ->orderBy(DB::raw('category_display_order + 0'), 'asc')
							  ->latest()
							  ->paginate(50);
		$count = 1;

		return view('categories.index', compact('categories', 'count'));
	}

	public function create ()
	{
		return view('categories.create');
	}

	public function store (CategoryCreateRequest $request)
	{
		$category = new Category();
		$category->category_code = $request->get('category_code');
		$category->category_description = $request->get('category_description');
		$category->category_display_order = intval($request->get('category_display_order'));

		$category->save();

		return redirect()->action('CategoryController@index');
	}

	public function show ($id)
	{
		$category = Category::where('is_deleted', 0)
							->find($id);
		if ( !$category ) {
			return view('errors.404');
		}

		return view('categories.show', compact('category'));
	}

	public function edit ($id)
	{
		$category = Category::where('is_deleted', 0)
							->find($id);
		if ( !$category ) {
			return view('errors.404');
		}

		return view('categories.edit', compact('category'));
	}

	public function update (CategoryUpdateRequest $request, $id)
	{
		$category = Category::where('is_deleted', 0)
							->find($id);
		if ( !$category ) {
			return view('errors.404');
		}

		$category->category_code = $request->get('category_code');
		$category->category_description = $request->get('category_description');
		$category->category_display_order = intval($request->get('category_display_order'));

		$category->save();

		return redirect()->action('CategoryController@index');
	}

	public function destroy ($id)
	{
		$category = Category::where('is_deleted', 0)
							->find($id);
		if ( !$category ) {
			return view('errors.404');
		}

		$category->is_deleted = 1;
		$category->save();

		return redirect()->action('CategoryController@index');
	}
}
