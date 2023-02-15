<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class CollectionUpdateRequest extends Request
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
				   ->parameter('collections', 0);

		return [
			'collection_code'          => sprintf("required|no_space_allowed|%s", \Monogram\Helper::getUniquenessRule("Collection", $id, "collection_code")),
			'collection_description'   => 'required',
			'collection_display_order' => 'required',
		];
	}
}
