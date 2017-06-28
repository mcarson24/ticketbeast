<?php

namespace App;

use App\Concert;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    public function toArray()
    {
        return [
            'email'             => $this->email,
            'ticket_quantity'   => $this->ticketQuantity(),
            'amount'            => $this->ticketQuantity() * $this->concert->ticket_price
        ];
    }

    public function tickets()
    {
    	return $this->hasMany(Ticket::class);
    }

    public function concert()
    {
        return $this->belongsTo(Concert::class);
    }

    public function ticketQuantity()
    {
        return $this->tickets()->count();
    }

    public function cancel()
    {
    	$this->tickets->each(function($ticket) {
    		$ticket->release();
    	});

    	$this->delete();
    }	
}
