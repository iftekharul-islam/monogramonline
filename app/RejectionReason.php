<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RejectionReason extends Model
{
    public static function getReasons() 
    {
      return RejectionReason::where('is_deleted', 0)
                        ->orderBy('sort_order')
                        ->get()
                        ->pluck('rejection_message', 'id')
                        ->prepend('Select a reason', 0);
    }                 
    
}
