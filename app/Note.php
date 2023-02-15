<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
	public function user ()
	{
		return $this->belongsTo('App\User', 'user_id', 'id');
	}
	
	public function order ()
	{
		return $this->belongsTo('App\Order', 'order_5p', 'id');
	}
	
	// 
	// public function new_order ()
	// {
	// 	return $this->hasOne('App\Order', 'order_id', 'order_id')
	// 											->where('is_deleted', '0')
	// 											->latest();
	// }
}
