<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class OccasionCreateRequest extends Request
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
			'occasion_code'          => 'required|unique:occasions,occasion_code',
			'occasion_description'   => 'required',
			'occasion_display_order' => 'required',
		];
	}
}
