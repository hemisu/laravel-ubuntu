<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class SalesRecord extends Model
{
    public function stock()
    {
        return $this->belongsTo('App\Model\Stock');
    }
}
