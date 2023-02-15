<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class UserUpdateRequest extends Request
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
			'username'    => 'required',
			'email'       => 'sometimes|email|unique:users,email',
			'password'    => 'sometimes|min:8',
			// 'vendor_id'   => 'required',
			// 'zip_code'    => 'required',
			// 'state'       => 'required',
			'user_access' => 'required',
		];
	}
}
