<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class ItemToBatchCreateRequest extends Request
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
			"batches" => 'required',
		];
	}

	public function messages ()
	{
		return [
			'batches.required' => 'No batch is selected',
		];
	}
}
