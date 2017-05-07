<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
  public function salesrecord()
  {
      return $this->hasMany('App\Model\SalesRecord');
  }
}
