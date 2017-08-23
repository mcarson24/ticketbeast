<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AttendeeMessage extends Model
{
    protected $guarded = [];

    public function recipients()
    {
    	return $this->concert->orders()->pluck('email');
    }

    public function concert()
    {
    	return $this->belongsTo(Concert::class);
    }
}
