<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    public function tickets()
    {
    	return $this->hasMany(Ticket::class);
    }

    public function cancel()
    {
    	$this->tickets->each(function($ticket) {
    		$ticket->update(['order_id' => null]);
    	});

    	$this->delete();
    }	
}
