<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $table = 'staffs';
    public function salesrecord()
    {
        return $this->hasMany('App\Model\SalesRecord');
    }
}
