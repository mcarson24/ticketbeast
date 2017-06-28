<?php

namespace App;

class Reservation
{
	protected $tickets;

	public function __construct($tickets)
	{
		$this->tickets = $tickets;
	}

	public function totalCost()
	{
		return $this->tickets->sum('price');
	}
}