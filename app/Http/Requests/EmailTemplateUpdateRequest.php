<?php
namespace App\Http\Requests;

use App\Http\Requests\Request;
use Monogram\Helper;

class EmailTemplateUpdateRequest extends Request
{
	public function authorize ()
	{
		return true;
	}

	public function rules ()
	{
		$id = $this->route()
				   ->parameter('email_templates', 0);

		return [
			'message_type'  => sprintf('required|%s', Helper::getUniquenessRule("EmailTemplate", $id, "message_type")),
			'message_title' => 'required',
			#'message'       => 'required',
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
