<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Parameter extends Model
{
	public function options ()
	{
		return $this->hasMany('App\Option');
	}

	/*public function options_paginated ()
	{
		return $this->options()
					->paginate(10);
	}*/

	public function getOptionsPaginatedAttribute ()
	{
		return $this->options()
					->paginate(10);
	}
}
