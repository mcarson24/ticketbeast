<?php

namespace App;

use Hashids\Hashids;

class HashIdsTicketCodeGenerator implements TicketCodeGenerator
{
	private $hashids;

	public function __construct($salt)
	{
		$this->hashids = new Hashids($salt, 6, 'ABCDEFGHIJKLMNOPQRSTUVXXYZ');
	}

	public function generateFor(Ticket $ticket)
	{
		return $this->hashids->encode($ticket->id);
	}
}