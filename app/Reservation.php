<?php

namespace App;

use Illuminate\Support\Collection;

class Reservation
{
	protected $tickets, $email;

	public function __construct(Collection $tickets, $email)
	{
		$this->tickets = $tickets;
		$this->email = $email;
	}

	public function cancel()
	{
		$this->tickets->each(function($ticket) {
			$ticket->release();
		});
	}

	public function totalCost()
	{
		return $this->tickets->sum('price');
	}

	public function tickets()
	{
		return $this->tickets;
	}

	public function email()
	{
		return $this->email;
	}
}