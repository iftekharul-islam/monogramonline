<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class PurchasedInvProductsUpdateRequest extends Request
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
			"stock_no" 				 => 'required',
			"unit" 					 => 'required',
			"unit_price" 			 => 'required|numeric',
			"vendor_id" 			 => 'required',
			"vendor_sku" 			 => 'required',
			"lead_time_days" 		 => 'required|numeric',
		];
	}
}
