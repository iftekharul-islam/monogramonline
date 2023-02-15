<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class SubCategoryCreateRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
		return [
			'sub_category_code'          => 'required',
			'sub_category_description'   => 'required',
			'sub_category_display_order' => 'required',
		];
    }
}
