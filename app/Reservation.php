<?php

namespace App;

use Illuminate\Support\Collection;

class Reservation
{
	protected $tickets;

	public function __construct(Collection $tickets)
	{
		$this->tickets = $tickets;
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
}