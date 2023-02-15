<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class ProductUpdateRequest extends Request
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
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return array
	 */
	public function rules (\Illuminate\Http\Request $request)
	{
		// if ( $request->has('update_batch') ) {
		// 	return [
		// 		'batch_route_id' => 'required',
		// 	];
		// }

		return [
			// 'product_master_category' => 'required',
			'id_catalog'              => 'sometimes|required',
			// 'store_id'                => 'sometimes|required',
			'product_model'           => 'required',
		];
	}

	public function messages ()
	{
		return [
		// 	'product_master_category.required' => 'Product category is required',
		];
	}
}
