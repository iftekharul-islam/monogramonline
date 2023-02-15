<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class SalesCategoryCreateRequest extends Request
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
			'sales_category_code'          => 'required|no_space_allowed|unique:sales_categories,sales_category_code',
			'sales_category_description'   => 'required',
			'sales_category_display_order' => 'required',
		];
	}
}
