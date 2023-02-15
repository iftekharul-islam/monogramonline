<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class MasterCategoryUpdateRequest extends Request
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
	 *
	 * @param \App\Http\Requests\Request|\Illuminate\Http\Request $request
	 *
	 * @return array
	 */
	public function rules (\Illuminate\Http\Request $request)
	{
		$id = $this->route()
				   ->parameter('master_categories', 0);
		#dd($request->all());
		if ( $request->has('master_category_code') ) {
			return [
				'master_category_code'          => sprintf("required|no_space_allowed|%s", \Monogram\Helper::getUniquenessRule("MasterCategory", $id, "master_category_code")),
				'master_category_description'   => 'required',
				'master_category_display_order' => 'required',
			];
		}

		return [
			'modified_code'   => sprintf("required|no_space_allowed|%s", \Monogram\Helper::getUniquenessRule("MasterCategory", $id, "master_category_code")),
			'modified_description'   => 'required',
			'modified_display_order' => 'required',
		];
	}
}
