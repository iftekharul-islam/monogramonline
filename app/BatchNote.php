<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BatchNote extends Model
{
  public function batch ()
  {
    return $this->belongsTo('App\Batch', 'batch_number', 'batch_number');
  }
  
  public function user ()
  {
    return $this->belongsTo('App\User', 'user_id', 'id');
  }
  
  public function station ()
  {
    return $this->belongsTo('App\Station', 'station_id', 'id');
  }

}
