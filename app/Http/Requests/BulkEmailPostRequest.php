<?php

namespace App\Http\Requests;

class BulkEmailPostRequest extends Request
{
	public function authorize ()
	{
		return true;
	}

	public function rules ()
	{
		return [ 'order_ids' => 'required', 'template' => 'required|exists:email_templates,id,is_deleted,0' ];
	}
}
