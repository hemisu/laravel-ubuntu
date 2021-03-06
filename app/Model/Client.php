<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    public function salesrecord()
    {
        return $this->hasMany(SalesRecord::class, 'client_id');
    }
}
