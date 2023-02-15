<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterCategory extends Model
{
	public function children()
	{
		return $this->hasMany('App\MasterCategory', 'parent');
	}

	// recursive, loads all descendants
	public function childrenRecursive()
	{
		return $this->children()->with('childrenRecursive');
		// which is equivalent to:
		// return $this->hasMany('App\Category', 'parent')->with('childrenRecursive);
	}

	// parent
	public function parent()
	{
		return $this->belongsTo('App\MasterCategory','parent');
	}

	// all ascendants
	public function parentRecursive()
	{
		return $this->parent()->with('parentRecursive');
	}
}
