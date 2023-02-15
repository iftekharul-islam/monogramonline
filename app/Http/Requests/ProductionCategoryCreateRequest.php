<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class ProductionCategoryCreateRequest extends Request
{
	/**
	 * Determine if the user is authorized to make this request.
	 * @return bool
	 */
	public function authorize ()
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 * @return array
	 */
	public function rules ()
	{
		return [
			'production_category_code'          => 'required|no_space_allowed|unique:production_categories,production_category_code',
			'production_category_description'   => 'required',
			'production_category_display_order' => 'required',
		];
	}
}
