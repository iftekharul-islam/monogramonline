<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class ProductionCategoryUpdateRequest extends Request
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
				   ->parameter('production_categories', 0);

		return [
			'production_category_code'          => sprintf("required|no_space_allowed|%s", \Monogram\Helper::getUniquenessRule("ProductionCategory", $id, "production_category_code")),
			'production_category_description'   => 'required',
			'production_category_display_order' => 'required',
		];
	}
}
