<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Manufacture extends Model
{
    protected $table = 'manufactures';

    protected $fillable = [
        'name',
        'description',
    ];
}
