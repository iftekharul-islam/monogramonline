<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Specification extends Model
{
	protected $table = "product_specifications";
	protected $hidden = [
		'id',
		'created_at',
		'updated_at',
	];
}
