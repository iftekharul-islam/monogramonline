<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class SalesCategoryUpdateRequest extends Request
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
		$id = $this->route()
				   ->parameter('sales_categories', 0);

		return [
			'sales_category_code'          => sprintf("required|no_space_allowed|%s", \Monogram\Helper::getUniquenessRule("SalesCategory", $id, "sales_category_code")),
			'sales_category_description'   => 'required',
			'sales_category_display_order' => 'required',
		];
	}
}
