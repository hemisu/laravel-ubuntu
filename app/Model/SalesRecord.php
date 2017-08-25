<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class SalesRecord extends Model
{
    protected $fillable = ['stock_id', 'remarks', 'price', 'ispay', 'client_id',
     'staff_id', 'motor_serial_number', 'frame_number', 'bettery_type', 'created_at', 'updated_at'];
    public function stock()
    {
        return $this->belongsTo('App\Model\Stock');
    }
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
    public function staff()
    {
        return $this->belongsTo('App\Model\Staff');
    }
}
