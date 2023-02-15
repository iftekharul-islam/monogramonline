<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class StationCreateRequest extends Request
{
	protected $redirectAction = 'StationController@create'; 
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
			'station_name'        => 'required',
			'station_description' => 'required',
			'type'                => 'required',
			'section'             => 'required',
		];
	}
}
