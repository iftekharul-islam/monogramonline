<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class ManualOrderCreateRequest extends Request
{
	public function authorize ()
	{
		return true;
	}

	public function rules ()
	{
		return [
			'store' => 'required|exists:stores,store_id',
			'item_sku' => 'required',
			'ship_full_name' => 'required',
			'bill_email' => 'required|email',
			// 'shipping' => 'required',
		];
	}

	public function messages ()
	{
		return [
			'item_id_catalog.required' => 'Items must be selected',
		];
	}
}
