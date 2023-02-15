<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
	protected $table = 'templates';

	public function options ()
	{
		return $this->hasMany('App\TemplateOption', 'template_id', 'id')
					->orderBy('template_order', 'asc');
	}

	public function exportable_options ()
	{
		return $this->hasMany('App\TemplateOption', 'template_id', 'id')
					->where('line_item_field', 1)
					->orderBy('template_order', 'asc');
	}
}
