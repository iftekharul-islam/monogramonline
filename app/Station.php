<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
	public function reject_reasons ()
	{
		return $this->hasMany('App\RejectionReason', 'station_id', 'id');
	}

	public function getCustomStationNameAttribute ()
	{
		return sprintf("%s => %s", $this->station_name, $this->station_description);
	}
	
	public function section_info () 
	{
		return $this->belongsTo('App\Section', 'section', 'id');
	}
	
	public function route_list () {
		
		return $this->belongsToMany('App\BatchRoute', 'batch_route_station', 'station_id', 'batch_route_id')
								->where('batch_routes.is_deleted', 0)
								->orderBy('batch_routes.batch_route_name');
	}

	public function scopeSearchSection ($query, $section)
	{
		if ( !$section) {
			return;
		}
		
		return $query->where('section', $section);

	}
}
