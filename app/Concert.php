<?php

namespace App;

use App\Reservation;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\NotEnoughTicketsRemainException;

class Concert extends Model
{
    protected $guarded = [];
    protected $dates = ['date'];

    public function addTickets($quantity)
    {
        foreach (range(1, $quantity) as $i)
        {
            $this->tickets()->create([]);
        }

        return $this;
    }

    public function reserveTickets($quantity, $email)
    {
        $tickets = $this->findTickets($quantity)->each(function($ticket) {
            $ticket->reserve();
        });

        return new Reservation($tickets, $email);
    }

    public function findTickets($quantity)
    {
        $tickets = $this->tickets()->available()->take($quantity)->get();
        
        if ($tickets->count() < $quantity)
        {
            throw new NotEnoughTicketsRemainException;
        }

        return $tickets;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ticketsRemaining()
    {
        return $this->tickets()->available()->count();
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    public function isPublished()
    {
        return $this->published_at !== null;
    }

    public function publish()
    {
        $this->update(['published_at' => $this->freshTimestamp()]);

        return $this;
    }

    public function getFormattedDateAttribute()
    {
        return $this->date->format('F j, Y');
    }

    public function getFormattedDateWithDayAttribute()
    {
        return $this->date->format('l, F jS, Y');
    }

    public function getFormattedStartTimeAttribute()
    {
        return $this->date->format('g:ia');
    }

    public function getTicketPriceInDollarsAttribute()
    {
        return number_format($this->ticket_price / 100, 2);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'tickets');
    }

    public function ordersFor($email)
    {
        return $this->orders()->where('email', $email)->get();
    }

    public function hasOrderFor($email)
    {   
        return $this->orders()->where('email', $email)->count() > 0;
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
