<?php
namespace App\Http\Requests;

use App\Http\Requests\Request;

class EmailTemplateCreateRequest extends Request
{
	public function authorize ()
	{
		return true;
	}

	public function rules ()
	{
		return [
			'message_type'  => 'required|unique:email_templates,message_type',
			'message_title' => 'required',
		];
	}

	public function messages ()
	{
		return [
			'message_type.required'  => 'Message type must be given.',
			'message_type.unique'    => 'Message type was already used.',
			'message_title.required' => 'Message subject must be given.',
		];
	}
}
