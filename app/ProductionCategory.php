<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductionCategory extends Model
{
	public function getDescriptionWithCodeAttribute ()
	{
		return sprintf("%s : %s", $this->production_category_code, $this->production_category_description);
	}
}
