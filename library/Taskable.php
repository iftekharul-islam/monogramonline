<?php

namespace Monogram;

use Illuminate\Database\Eloquent\Model;

abstract class Taskable extends Model
{   
    //return array model, id, url, title, line2, line 3
    abstract protected function outputArray();

    public function tasks()
    {
        return $this->morphMany('App\Task', 'taskable');
    }
}

?>
