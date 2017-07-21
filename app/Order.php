<?php

namespace App;

use App\Concert;
use App\Billing\Charge;
use Illuminate\Database\Eloquent\Model;
use App\Facades\OrderConfirmationNumber;

class Order extends Model
{
    protected $guarded = [];

    public static function forTickets($tickets, $email, Charge $charge)
    {
        $order = static::create([
            'confirmation_number'   => OrderConfirmationNumber::generate(),
            'email'                 => $email,
            'amount'                => $charge->amount(),
            'card_last_four'        => $charge->cardLastFour(),
        ]);

        foreach ($tickets as $ticket)
        {
            $order->tickets()->save($ticket);
        }

        return $order;
    }

    public static function findByConfirmationNumber($confirmationNumber)
    {
        return static::where('confirmation_number', $confirmationNumber)->firstOrFail();
    }

    public function toArray()
    {
        return [
            'email'                 => $this->email,
            'ticket_quantity'       => $this->ticketQuantity(),
            'amount'                => $this->amount,
            'confirmation_number'   => $this->confirmation_number
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
