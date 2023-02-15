<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PickingReport extends Model
{
    protected $table = 'picking_reports';
    
    public function batches ()
    {
      return $this->hasMany('App\Batch', 'picking_report_id');
    }
    
    public function picking_user ()
    {      
      return $this->belongsTo('App\User', 'picking_user_id', 'id');
    }
    
    
    public function picked_user ()
    {      
      return $this->belongsTo('App\User', 'picked_user_id', 'id');
    }
}
