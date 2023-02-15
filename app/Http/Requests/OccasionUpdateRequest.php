<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class OccasionUpdateRequest extends Request
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
				   ->parameter('occasions', 0);

		return [
			'occasion_code'          => sprintf("required|no_space_allowed|%s", \Monogram\Helper::getUniquenessRule("Occasion", $id, "occasion_code")),
			'occasion_description'   => 'required',
			'occasion_display_order' => 'required',
		];
	}
}
