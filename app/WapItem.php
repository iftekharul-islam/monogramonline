<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WapItem extends Model
{   
    protected $table = 'wap_items';
    
    public function items () {
      return $this->belongsTo('App\Item', 'item_id', 'id')
              ->orderBy('item_status');
    }
    
    public function bin () {
      return $this->belongsTo('App\Wap', 'bin_id', 'id');
    }

}
