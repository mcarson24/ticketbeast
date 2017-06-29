<?php

namespace App;

use App\Concert;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    public static function forTickets($tickets, $email, $amount)
    {
        $order = static::create([
            'email'         => $email,
            'amount'        => $amount 
        ]);

        foreach ($tickets as $ticket)
        {
            $order->tickets()->save($ticket);
        }

        return $order;
    }

    public function toArray()
    {
        return [
            'email'             => $this->email,
            'ticket_quantity'   => $this->ticketQuantity(),
            'amount'            => $this->amount
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
}
