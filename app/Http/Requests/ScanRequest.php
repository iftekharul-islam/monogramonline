<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class ScanRequest extends Request
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
			'batch_number'    => 'required',
			'user'            => 'required',
			'from'            => 'required',
		];
	}
	
	public function messages()
	{
	    return [
	        'user.required'         => 'Please Scan User ID',
	        'batch_number.required' => 'Enter Batch Number',
	    ];
	}
}
